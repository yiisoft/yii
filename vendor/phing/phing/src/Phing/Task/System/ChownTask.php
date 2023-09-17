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

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\DirSetAware;
use Phing\Type\Element\FileSetAware;

/**
 * Task that changes the permissions on a file/directory.
 *
 * @author  Mehmet Emre Yilmaz <mehmety@gmail.com>
 */
class ChownTask extends Task
{
    use DirSetAware;
    use FileSetAware;

    private $file;

    private $user;
    private $group;

    private $quiet = false;
    private $failonerror = true;
    private $verbose = true;

    /**
     * This flag means 'note errors to the output, but keep going'.
     *
     * @see   setQuiet()
     */
    public function setFailonerror(bool $failOnError)
    {
        $this->failonerror = $failOnError;
    }

    /**
     * Set quiet mode, which suppresses warnings if chown() fails.
     *
     * @see   setFailonerror()
     */
    public function setQuiet(bool $quiet)
    {
        $this->quiet = $quiet;
        if ($this->quiet) {
            $this->failonerror = false;
        }
    }

    /**
     * Set verbosity, which if set to false surpresses all but an overview
     * of what happened.
     */
    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    /**
     * Sets a single source file to touch.  If the file does not exist
     * an empty file will be created.
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    /**
     * Sets the user.
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * Sets the group.
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * Execute the touch operation.
     */
    public function main()
    {
        // Check Parameters
        $this->checkParams();
        $this->chown();
    }

    /**
     * Ensure that correct parameters were passed in.
     *
     * @throws BuildException
     */
    private function checkParams(): void
    {
        if (null === $this->file && empty($this->filesets) && empty($this->dirsets)) {
            throw new BuildException('Specify at least one source - a file or a fileset.');
        }

        if (null === $this->user && null === $this->group) {
            throw new BuildException('You have to specify either an owner or a group for chown.');
        }
    }

    /**
     * Does the actual work.
     */
    private function chown(): void
    {
        $userElements = explode('.', $this->user);

        $user = $userElements[0];

        if (count($userElements) > 1) {
            $group = $userElements[1];
        } else {
            $group = $this->group;
        }

        // counters for non-verbose output
        $total_files = 0;
        $total_dirs = 0;

        // one file
        if (null !== $this->file) {
            $total_files = 1;
            $this->chownFile($this->file, $user, $group);
        }

        $this->filesets = array_merge($this->filesets, $this->dirsets);

        // filesets
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $fromDir = $fs->getDir($this->project);

            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            $filecount = count($srcFiles);
            $total_files += $filecount;
            for ($j = 0; $j < $filecount; ++$j) {
                $this->chownFile(new File($fromDir, $srcFiles[$j]), $user, $group);
            }

            $dircount = count($srcDirs);
            $total_dirs += $dircount;
            for ($j = 0; $j < $dircount; ++$j) {
                $this->chownFile(new File($fromDir, $srcDirs[$j]), $user, $group);
            }
        }

        if (!$this->verbose) {
            $this->log('Total files changed to ' . $user . ($group ? '.' . $group : '') . ': ' . $total_files);
            $this->log('Total directories changed to ' . $user . ($group ? '.' . $group : '') . ': ' . $total_dirs);
        }
    }

    /**
     * Actually change the mode for the file.
     *
     * @param string $user
     * @param string $group
     *
     * @throws BuildException
     * @throws Exception
     */
    private function chownFile(File $file, $user, $group = ''): void
    {
        if (!$file->exists()) {
            throw new BuildException('The file ' . $file->__toString() . ' does not exist');
        }

        try {
            if (!empty($user)) {
                $file->setUser($user);
            }

            if (!empty($group)) {
                $file->setGroup($group);
            }

            if ($this->verbose) {
                $this->log(
                    "Changed file owner on '" . $file->__toString() . "' to " . $user . ($group ? '.' . $group : '')
                );
            }
        } catch (Exception $e) {
            if ($this->failonerror) {
                throw $e;
            }

            $this->log($e->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
        }
    }
}
