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
use Phing\Io\IOException;

/**
 * Moves a file or directory to a new file or directory.
 *
 * By default, the destination file is overwritten if it
 * already exists.  When overwrite is turned off, then files
 * are only moved if the source file is newer than the
 * destination file, or when the destination file does not
 * exist.
 *
 * Source files and directories are only deleted when the file or
 * directory has been copied to the destination successfully.
 */
class MoveTask extends CopyTask
{
    public function __construct()
    {
        parent::__construct();
        $this->overwrite = true;
    }

    /**
     * Validates attributes coming in from XML.
     *
     * @throws BuildException
     */
    protected function validateAttributes()
    {
        if (null !== $this->file && $this->file->isDirectory()) {
            if (
                (null !== $this->destFile
                && null !== $this->destDir)
                || (null === $this->destFile
                && null === $this->destDir)
            ) {
                throw new BuildException('One and only one of tofile and todir must be set.');
            }

            if (null === $this->destFile) {
                $this->destFile = new File($this->destDir, $this->file->getName());
            }

            if (null === $this->destDir) {
                $this->destDir = $this->destFile->getParentFile();
            }

            $this->completeDirMap[$this->file->getAbsolutePath()] = $this->destFile->getAbsolutePath();

            $this->file = null;
        } else {
            parent::validateAttributes();
        }
    }

    protected function doWork()
    {
        if (count($this->completeDirMap) > 0) {
            foreach ($this->completeDirMap as $from => $to) {
                $f = new File($from);
                $d = new File($to);

                try { // try to rename
                    $this->log("Attempting to rename {$from} to {$to}", $this->verbosity);
                    if (!empty($this->filterChains)) {
                        $this->fileUtils->copyFile(
                            $f,
                            $d,
                            $this->getProject(),
                            $this->overwrite,
                            $this->preserveLMT,
                            $this->filterChains,
                            $this->mode,
                            $this->preservePermissions,
                            $this->granularity
                        );
                        $f->delete(true);
                    } else {
                        $this->fileUtils->renameFile($f, $d, $this->overwrite);
                    }
                } catch (IOException $ioe) {
                    $this->logError("Failed to rename {$from} to {$to}: " . $ioe->getMessage());
                }
            }
        }

        $copyMapSize = count($this->fileCopyMap);
        if ($copyMapSize > 0) {
            // files to move
            $this->log("Moving {$copyMapSize} files to " . $this->destDir->getAbsolutePath());

            foreach ($this->fileCopyMap as $from => $to) {
                if ($from == $to) {
                    $this->log("Skipping self-move of {$from}", $this->verbosity);

                    continue;
                }

                $f = new File($from);
                $d = new File($to);

                try { // try to move
                    $this->log("Moving {$from} to {$to}", $this->verbosity);

                    $this->fileUtils->copyFile(
                        $f,
                        $d,
                        $this->getProject(),
                        $this->overwrite,
                        $this->preserveLMT,
                        $this->filterChains,
                        $this->mode,
                        $this->preservePermissions,
                        $this->granularity
                    );

                    $f->delete();
                } catch (IOException $ioe) {
                    $this->logError("Failed to move {$from} to {$to}: " . $ioe->getMessage(), $this->getLocation());
                }
            } // foreach fileCopyMap
        } // if copyMapSize

        // handle empty dirs if appropriate
        if ($this->includeEmpty) {
            $count = 0;
            foreach ($this->dirCopyMap as $srcDir => $destDir) {
                $d = new File((string) $destDir);
                if (!$d->exists()) {
                    if (!$d->mkdirs()) {
                        $this->logError('Unable to create directory ' . $d->getAbsolutePath());
                    } else {
                        ++$count;
                    }
                }
            }
            if ($count > 0) {
                $this->log(
                    "moved {$count} empty director" . (1 == $count ? 'y' : 'ies') . ' to ' . $this->destDir->getAbsolutePath(
                    )
                );
            }
        }

        if (count($this->filesets) > 0) {
            // process filesets
            foreach ($this->filesets as $fs) {
                $dir = $fs->getDir($this->project);
                if ($this->okToDelete($dir)) {
                    $this->deleteDir($dir);
                }
            }
        }

        $dirsets = $this->getDirSets();
        if (count($dirsets) > 0) {
            // process dirsets
            foreach ($dirsets as $ds) {
                $dir = $ds->getDir($this->project);
                if ($this->okToDelete($dir)) {
                    $this->deleteDir($dir);
                }
            }
        }
    }

    /**
     * Its only ok to delete a dir tree if there are no files in it.
     *
     * @throws IOException
     *
     * @return bool
     */
    private function okToDelete(File $dir)
    {
        $list = $dir->listDir();
        if (null === $list) {
            return false; // maybe io error?
        }

        foreach ($list as $s) {
            $f = new File($dir, $s);
            if ($f->isDirectory()) {
                if (!$this->okToDelete($f)) {
                    return false;
                }
            } else {
                // found a file
                return false;
            }
        }

        return true;
    }

    /**
     * Go and delete the directory tree.
     *
     * @throws BuildException
     * @throws IOException
     */
    private function deleteDir(File $dir)
    {
        $list = $dir->listDir();
        if (null === $list) {
            return; // on an io error list() can return null
        }

        foreach ($list as $fname) {
            $f = new File($dir, $fname);
            if ($f->isDirectory()) {
                $this->deleteDir($f);
            } else {
                throw new BuildException('UNEXPECTED ERROR - The file ' . $f->getAbsolutePath() . ' should not exist!');
            }
        }

        $this->log('Deleting directory ' . $dir->getPath(), $this->verbosity);

        try {
            $dir->delete();
        } catch (Exception $e) {
            $this->logError('Unable to delete directory ' . $dir->__toString() . ': ' . $e->getMessage());
        }
    }
}
