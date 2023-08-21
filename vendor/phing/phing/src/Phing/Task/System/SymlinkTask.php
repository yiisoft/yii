<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Project;
use Phing\Task;
use Phing\Type\FileSet;

/**
 * Generates symlinks based on a target / link combination.
 * Can also symlink contents of a directory, individually.
 *
 * Single target symlink example:
 * <code>
 *     <symlink target="/some/shared/file" link="${project.basedir}/htdocs/my_file" />
 * </code>
 *
 * Symlink entire contents of directory
 *
 * This will go through the contents of "/my/shared/library/*"
 * and create a symlink for each entry into ${project.basedir}/library/
 * <code>
 *     <symlink link="${project.basedir}/library">
 *         <fileset dir="/my/shared/library">
 *             <include name="*" />
 *         </fileset>
 *     </symlink>
 * </code>
 *
 * @author  Andrei Serdeliuc <andrei@serdeliuc.ro>
 */
class SymlinkTask extends Task
{
    /**
     * What we're symlinking from.
     *
     * (default value: null)
     *
     * @var string
     */
    private $linkTarget;

    /**
     * Symlink location.
     *
     * (default value: null)
     *
     * @var string
     */
    private $link;

    /**
     * Collection of filesets
     * Used when linking contents of a directory.
     *
     * (default value: array())
     *
     * @var array
     */
    private $filesets = [];

    /**
     * Whether to override the symlink if it exists but points
     * to a different location.
     *
     * (default value: false)
     *
     * @var bool
     */
    private $overwrite = false;

    /**
     * Whether to create relative symlinks.
     *
     * @var bool
     */
    private $relative = false;

    /**
     * setter for linkTarget.
     *
     * @param string $linkTarget
     */
    public function setTarget($linkTarget)
    {
        $this->linkTarget = $linkTarget;
    }

    /**
     * setter for _link.
     *
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * creator for _filesets.
     *
     * @return FileSet
     */
    public function createFileset()
    {
        $num = array_push($this->filesets, new FileSet());

        return $this->filesets[$num - 1];
    }

    /**
     * setter for _overwrite.
     *
     * @param bool $overwrite
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;
    }

    /**
     * @param bool $relative
     */
    public function setRelative($relative)
    {
        $this->relative = $relative;
    }

    /**
     * getter for linkTarget.
     *
     * @throws BuildException
     *
     * @return string
     */
    public function getTarget()
    {
        if (null === $this->linkTarget) {
            throw new BuildException('Target not set');
        }

        return $this->linkTarget;
    }

    /**
     * getter for _link.
     *
     * @throws BuildException
     *
     * @return string
     */
    public function getLink()
    {
        if (null === $this->link) {
            throw new BuildException('Link not set');
        }

        return $this->link;
    }

    /**
     * getter for _filesets.
     *
     * @return array
     */
    public function getFilesets()
    {
        return $this->filesets;
    }

    /**
     * getter for _overwrite.
     *
     * @return bool
     */
    public function getOverwrite()
    {
        return $this->overwrite;
    }

    /**
     * @return bool
     */
    public function isRelative()
    {
        return $this->relative;
    }

    /**
     * Given an existing path, convert it to a path relative to a given starting path.
     *
     * @param string $endPath   Absolute path of target
     * @param string $startPath Absolute path where traversal begins
     *
     * @return string Path of target relative to starting path
     */
    public function makePathRelative($endPath, $startPath)
    {
        // Normalize separators on Windows
        if ('\\' === DIRECTORY_SEPARATOR) {
            $endPath = str_replace('\\', '/', $endPath);
            $startPath = str_replace('\\', '/', $startPath);
        }

        // Split the paths into arrays
        $startPathArr = explode('/', trim($startPath, '/'));
        $endPathArr = explode('/', trim($endPath, '/'));

        // Find for which directory the common path stops
        $index = 0;
        while (isset($startPathArr[$index], $endPathArr[$index]) && $startPathArr[$index] === $endPathArr[$index]) {
            ++$index;
        }

        // Determine how deep the start path is relative to the common path (ie, "web/bundles" = 2 levels)
        $depth = count($startPathArr) - $index;

        // Repeated "../" for each level need to reach the common path
        $traverser = str_repeat('../', $depth);

        $endPathRemainder = implode('/', array_slice($endPathArr, $index));

        // Construct $endPath from traversing to the common path, then to the remaining $endPath
        $relativePath = $traverser . ('' !== $endPathRemainder ? $endPathRemainder . '/' : '');

        return '' === $relativePath ? './' : $relativePath;
    }

    /**
     * Main entry point for task.
     *
     * @return bool
     */
    public function main()
    {
        $map = $this->getMap();

        // Single file symlink
        if (is_string($map)) {
            return $this->symlink($map, $this->getLink());
        }

        // Multiple symlinks
        foreach ($map as $name => $targetPath) {
            $this->symlink($targetPath, $this->getLink() . DIRECTORY_SEPARATOR . $name);
        }

        return true;
    }

    /**
     * Generates an array of directories / files to be linked
     * If _filesets is empty, returns getTarget().
     *
     * @throws BuildException
     *
     * @return array|string
     */
    protected function getMap()
    {
        $fileSets = $this->getFilesets();

        // No filesets set
        // We're assuming single file / directory
        if (empty($fileSets)) {
            return $this->getTarget();
        }

        $targets = [];

        foreach ($fileSets as $fs) {
            if (!($fs instanceof FileSet)) {
                continue;
            }

            // We need a directory to store the links
            if (!is_dir($this->getLink())) {
                throw new BuildException('Link must be an existing directory when using fileset');
            }

            $fromDir = $fs->getDir($this->getProject())->getAbsolutePath();

            if (!is_dir($fromDir)) {
                $this->log('Directory doesn\'t exist: ' . $fromDir, Project::MSG_WARN);

                continue;
            }

            $fsTargets = [];

            $ds = $fs->getDirectoryScanner($this->getProject());

            $fsTargets = array_merge(
                $fsTargets,
                $ds->getIncludedDirectories(),
                $ds->getIncludedFiles()
            );

            // Add each target to the map
            foreach ($fsTargets as $target) {
                if (!empty($target)) {
                    $targets[$target] = $fromDir . DIRECTORY_SEPARATOR . $target;
                }
            }
        }

        return $targets;
    }

    /**
     * Create the actual link.
     *
     * @param string $target
     * @param string $link
     *
     * @return bool
     */
    protected function symlink($target, $link)
    {
        $fs = FileSystem::getFileSystem();

        if ($this->isRelative()) {
            $link = (new File($link))->getAbsolutePath();
            $target = rtrim($this->makePathRelative($target, dirname($link)), '/');
        }

        if (is_link($link) && @readlink($link) == $target) {
            $this->log('Link exists: ' . $link, Project::MSG_INFO);

            return true;
        }

        if (file_exists($link) || is_link($link)) {
            if (!$this->getOverwrite()) {
                $this->log('Not overwriting existing link ' . $link, Project::MSG_ERR);

                return false;
            }

            if (is_link($link) || is_file($link)) {
                $fs->unlink($link);
                $this->log('Link removed: ' . $link, Project::MSG_INFO);
            } else {
                $fs->rmdir($link, true);
                $this->log('Directory removed: ' . $link, Project::MSG_INFO);
            }
        }

        $this->log('Linking: ' . $target . ' to ' . $link, Project::MSG_INFO);

        $fs->symlink($target, $link);

        return true;
    }
}
