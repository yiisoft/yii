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
use Phing\Type\Element\ResourceAware;

/**
 * Deletes a file or directory, or set of files defined by a fileset.
 */
class DeleteTask extends Task
{
    use ResourceAware;

    protected $file;
    protected $dir;
    protected $includeEmpty = false;

    protected $quiet = false;
    protected $failonerror = false;
    protected $verbosity = Project::MSG_VERBOSE;

    /**
     * Set the name of a single file to be removed.
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * Set the directory from which files are to be deleted.
     */
    public function setDir(File $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Used to force listing of all names of deleted files.
     *
     * @param bool $verbosity
     */
    public function setVerbose($verbosity)
    {
        if ($verbosity) {
            $this->verbosity = Project::MSG_INFO;
        } else {
            $this->verbosity = Project::MSG_VERBOSE;
        }
    }

    /**
     * If the file does not exist, do not display a diagnostic
     * message or modify the exit status to reflect an error.
     * This means that if a file or directory cannot be deleted,
     * then no error is reported. This setting emulates the
     * -f option to the Unix rm command. Default is false
     * meaning things are verbose.
     *
     * @param bool $bool
     */
    public function setQuiet($bool)
    {
        $this->quiet = $bool;
        if ($this->quiet) {
            $this->failonerror = false;
        }
    }

    /**
     * this flag means 'note errors to the output, but keep going'.
     *
     * @param bool $bool
     * @retujrn void
     */
    public function setFailOnError($bool)
    {
        $this->failonerror = $bool;
    }

    /**
     * Used to delete empty directories.
     *
     * @param bool $includeEmpty
     */
    public function setIncludeEmptyDirs($includeEmpty)
    {
        $this->includeEmpty = (bool) $includeEmpty;
    }

    /**
     * Delete the file(s).
     *
     * @throws BuildException
     */
    public function main()
    {
        if (
            null === $this->file
            && null === $this->dir
            && 0 === count($this->dirsets)
            && 0 === count($this->filesets)
            && 0 === count($this->filelists)
        ) {
            throw new BuildException(
                'At least one of the file or dir attributes, or a fileset, filelist or dirset element must be set.'
            );
        }

        if ($this->quiet && $this->failonerror) {
            throw new BuildException('quiet and failonerror cannot both be set to true', $this->getLocation());
        }

        // delete a single file
        if (null !== $this->file) {
            if ($this->file->exists()) {
                if ($this->file->isDirectory()) {
                    $this->log(
                        'Directory ' . $this->file->__toString() . ' cannot be removed using the file attribute. Use dir instead.'
                    );
                } else {
                    $this->log('Deleting: ' . $this->file->__toString());

                    try {
                        $this->file->delete();
                    } catch (Exception $e) {
                        $message = 'Unable to delete file ' . $this->file->__toString() . ': ' . $e->getMessage();
                        if ($this->failonerror) {
                            throw new BuildException($message);
                        }

                        $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                    }
                }
            } else {
                $message = 'Could not find file ' . $this->file->getAbsolutePath() . ' to delete.';

                if ($this->failonerror) {
                    throw new BuildException($message);
                }

                $this->log($message, ($this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN));
            }
        }

        if (null !== $this->dir) {
            $this->dirsets[] = $this->dir;
        }
        foreach ($this->dirsets as $dirset) {
            if (!$dirset instanceof File) {
                $ds = $dirset->getDirectoryScanner($this->getProject());
                $dirs = $ds->getIncludedDirectories();
                $baseDir = $ds->getBasedir();
            } else {
                $dirs[0] = $dirset;
            }
            foreach ($dirs as $dir) {
                if (!$dir instanceof File) {
                    $dir = new File($baseDir, $dir);
                }
                if ($dir->exists() && $dir->isDirectory()) {
                    if (Project::MSG_VERBOSE === $this->verbosity) {
                        $this->log('Deleting directory ' . $dir->__toString());
                    }
                    $this->removeDir($dir);
                } else {
                    $message = 'Directory ' . $dir->getAbsolutePath() . ' does not exist or is not a directory.';

                    if ($this->failonerror) {
                        throw new BuildException($message);
                    }

                    $this->log($message, ($this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN));
                }
            }
        }

        // delete the files in the filelists
        foreach ($this->filelists as $fl) {
            try {
                $files = $fl->getFiles($this->project);
                $empty = [];
                $this->removeFiles($fl->getDir($this->project), $files, $empty);
            } catch (BuildException $be) {
                // directory doesn't exist or is not readable
                if ($this->failonerror) {
                    throw $be;
                }

                $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
            }
        }

        // delete the files in the filesets
        foreach ($this->filesets as $fs) {
            try {
                $ds = $fs->getDirectoryScanner($this->project);
                $files = $ds->getIncludedFiles();
                $dirs = $ds->getIncludedDirectories();
                $this->removeFiles($fs->getDir($this->project), $files, $dirs);
            } catch (BuildException $be) {
                // directory doesn't exist or is not readable
                if ($this->failonerror) {
                    throw $be;
                }

                $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
            }
        }
    }

    /**
     * Recursively removes a directory.
     *
     * @param File $d the directory to remove
     *
     * @throws BuildException
     */
    private function removeDir($d)
    {
        $list = $d->listDir();
        if (null === $list) {
            $list = [];
        }

        foreach ($list as $s) {
            $f = new File($d, $s);
            if ($f->isDirectory()) {
                $this->removeDir($f);
            } else {
                $this->log('Deleting ' . $f->__toString(), $this->verbosity);

                try {
                    $f->delete();
                } catch (Exception $e) {
                    $message = 'Unable to delete file ' . $f->__toString() . ': ' . $e->getMessage();
                    if ($this->failonerror) {
                        throw new BuildException($message);
                    }

                    $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                }
            }
        }

        try {
            $d->delete();
        } catch (Exception $e) {
            $message = 'Unable to delete directory ' . $d->__toString() . ': ' . $e->getMessage();
            if ($this->failonerror) {
                throw new BuildException($message);
            }

            $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
        }
    }

    /**
     * remove an array of files in a directory, and a list of subdirectories
     * which will only be deleted if 'includeEmpty' is true.
     *
     * @param File  $d     directory to work from
     * @param array $files array of files to delete; can be of zero length
     * @param array $dirs  array of directories to delete; can of zero length
     *
     * @throws BuildException
     */
    private function removeFiles(File $d, &$files, &$dirs)
    {
        if (count($files) > 0) {
            $this->log('Deleting ' . count($files) . ' files from ' . $d->__toString());
            for ($j = 0, $_j = count($files); $j < $_j; ++$j) {
                $f = new File($d, $files[$j]);
                $this->log('Deleting ' . $f->getAbsolutePath(), $this->verbosity);

                try {
                    $f->delete();
                } catch (Exception $e) {
                    $message = 'Unable to delete file ' . $f->__toString() . ': ' . $e->getMessage();
                    if ($this->failonerror) {
                        throw new BuildException($message);
                    }

                    $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                }
            }
        }

        if (count($dirs) > 0 && $this->includeEmpty) {
            $dirCount = 0;
            for ($j = count($dirs) - 1; $j >= 0; --$j) {
                $dir = new File($d, $dirs[$j]);
                $dirFiles = $dir->listDir();
                if (null === $dirFiles || 0 === count($dirFiles)) {
                    $this->log('Deleting ' . $dir->__toString(), $this->verbosity);

                    try {
                        $dir->delete();
                        ++$dirCount;
                    } catch (Exception $e) {
                        $message = 'Unable to delete directory ' . $dir->__toString();
                        if ($this->failonerror) {
                            throw new BuildException($message);
                        }

                        $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                    }
                }
            }
            if ($dirCount > 0) {
                $this->log("Deleted {$dirCount} director" . (1 == $dirCount ? 'y' : 'ies') . ' from ' . $d->__toString());
            }
        }
    }
}
