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
use Phing\Io\IOException;
use Phing\Phing;
use Phing\Project;
use Phing\Task;
use Phing\Task\System\Condition\Condition;
use Phing\Type\Path;

/**
 * <available> task.
 *
 * Note: implements condition interface (see condition/Condition.php)
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 */
class AvailableTask extends Task implements Condition
{
    /**
     * Property to check for.
     */
    private $property;

    /**
     * Value property should be set to.
     */
    private $value = 'true';

    /**
     * File/directory to check existence.
     */
    private $file;

    /**
     * Resource to check for.
     */
    private $resource;

    /**
     * Extension to check if is loaded.
     */
    private $extension;

    private $type;
    private $filepath;

    private $followSymlinks = false;

    public function setProperty(string $property)
    {
        $this->property = $property;
    }

    public function setValue(string $value)
    {
        $this->value = $value;
    }

    public function setFile(File $file)
    {
        $this->file = $file;
    }

    public function setResource(string $resource)
    {
        $this->resource = $resource;
    }

    public function setExtension(string $extension)
    {
        $this->extension = $extension;
    }

    public function setType(string $type)
    {
        $this->type = strtolower($type);
    }

    public function setFollowSymlinks(bool $followSymlinks)
    {
        $this->followSymlinks = $followSymlinks;
    }

    /**
     * Set the path to use when looking for a file.
     *
     * @param Path $filepath a Path instance containing the search path for files
     */
    public function setFilepath(Path $filepath)
    {
        if (null === $this->filepath) {
            $this->filepath = $filepath;
        } else {
            $this->filepath->append($filepath);
        }
    }

    /**
     * Creates a path to be configured.
     *
     * @return Path
     */
    public function createFilepath()
    {
        if (null === $this->filepath) {
            $this->filepath = new Path($this->project);
        }

        return $this->filepath->createPath();
    }

    public function main()
    {
        if (null === $this->property) {
            throw new BuildException('property attribute is required', $this->getLocation());
        }
        if ($this->evaluate()) {
            $this->project->setProperty($this->property, $this->value);
        }
    }

    /**
     * @throws BuildException
     *
     * @return bool
     */
    public function evaluate()
    {
        if (null === $this->file && null === $this->resource && null === $this->extension) {
            throw new BuildException('At least one of (file|resource|extension) is required', $this->getLocation());
        }

        if (null !== $this->type && ('file' !== $this->type && 'dir' !== $this->type)) {
            throw new BuildException('Type must be one of either dir or file', $this->getLocation());
        }

        if ((null !== $this->file) && !$this->checkFile()) {
            $this->log(
                'Unable to find ' . $this->file->__toString() . ' to set property ' . $this->property,
                Project::MSG_VERBOSE
            );

            return false;
        }

        if ((null !== $this->resource) && !$this->checkResource($this->resource)) {
            $this->log(
                'Unable to load resource ' . $this->resource . ' to set property ' . $this->property,
                Project::MSG_VERBOSE
            );

            return false;
        }

        if (null !== $this->extension && !extension_loaded($this->extension)) {
            $this->log(
                'Unable to load extension ' . $this->extension . ' to set property ' . $this->property,
                Project::MSG_VERBOSE
            );

            return false;
        }

        return true;
    }

    // this is prepared for the path type

    /**
     * @return bool
     */
    private function checkFile()
    {
        if (null === $this->filepath) {
            return $this->checkFile1($this->file);
        }

        $paths = $this->filepath->listPaths();
        foreach ($paths as $path) {
            $this->log('Searching ' . $path, Project::MSG_VERBOSE);
            $tmp = new File($path, $this->file->getName());
            if ($tmp->isFile()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws IOException
     *
     * @return bool
     */
    private function checkFile1(File $file)
    {
        // Resolve symbolic links
        if ($this->followSymlinks && $file->isLink()) {
            $linkTarget = new File($file->getLinkTarget());
            if ($linkTarget->isAbsolute()) {
                $file = $linkTarget;
            } else {
                $fs = FileSystem::getFileSystem();
                $file = new File(
                    $fs->resolve(
                        $fs->normalize($file->getParent()),
                        $fs->normalize($file->getLinkTarget())
                    )
                );
            }
        }

        if (null !== $this->type) {
            if ('dir' === $this->type) {
                return $file->isDirectory();
            }

            if ('file' === $this->type) {
                return $file->isFile();
            }
        }

        return $file->exists();
    }

    /**
     * @return bool
     */
    private function checkResource(string $resource)
    {
        if (null != ($resourcePath = Phing::getResourcePath($resource))) {
            return $this->checkFile1(new File($resourcePath));
        }

        return false;
    }
}
