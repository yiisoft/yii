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

namespace Phing\Task\Ext\Archive;

use Phing\Type\FileSet;

/**
 * This is a FileSet with the to specify permissions.
 *
 * Permissions are currently not implemented by PEAR Archive_Tar,
 * but hopefully they will be in the future.
 *
 * @package phing.tasks.ext
 */
class ZipFileSet extends FileSet
{
    private $files = null;

    /**
     *  Get a list of files and directories specified in the fileset.
     *
     * @param bool $includeEmpty
     * @param array ...$options
     *
     * @return array a list of file and directory names, relative to
     *               the baseDir for the project.
     *
     * @throws \Exception
     */
    protected function getFiles($includeEmpty = true, ...$options)
    {
        if ($this->files === null) {
            $ds = $this->getDirectoryScanner($this->getProject());
            $this->files = $ds->getIncludedFiles();

            // build a list of directories implicitly added by any of the files
            $implicitDirs = [];
            foreach ($this->files as $file) {
                $implicitDirs[] = dirname($file);
            }

            $incDirs = $ds->getIncludedDirectories();

            // we'll need to add to that list of implicit dirs any directories
            // that contain other *directories* (and not files), since otherwise
            // we get duplicate directories in the resulting tar
            foreach ($incDirs as $dir) {
                foreach ($incDirs as $dircheck) {
                    if (!empty($dir) && $dir == dirname($dircheck)) {
                        $implicitDirs[] = $dir;
                    }
                }
            }

            $implicitDirs = array_unique($implicitDirs);

            $emptyDirectories = [];

            if ($includeEmpty) {
                // Now add any empty dirs (dirs not covered by the implicit dirs)
                // to the files array.

                foreach ($incDirs as $dir) { // we cannot simply use array_diff() since we want to disregard empty/. dirs
                    if ($dir != "" && $dir !== "." && !in_array($dir, $implicitDirs)) {
                        // it's an empty dir, so we'll add it.
                        $emptyDirectories[] = $dir;
                    }
                }
            } // if $includeEmpty

            $this->files = array_merge($implicitDirs, $emptyDirectories, $this->files);
            sort($this->files);
        } // if ($this->files===null)

        return $this->files;
    }
}
