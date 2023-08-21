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

namespace Phing\Task\Ext\Coverage;

use Phing\Project;
use Phing\Task;
use Phing\Type\Element\ClasspathAware;
use Phing\Type\Element\FileListAware;
use Phing\Type\Element\FileSetAware;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Util\Properties;

/**
 * Initializes a code coverage database
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.coverage
 * @since   2.1.0
 */
class CoverageSetupTask extends Task
{
    use ClasspathAware;
    use FileListAware;
    use FileSetAware;

    /**
     * the filename of the coverage database
     */
    private $database = "coverage.db";

    /**
     * Sets the filename of the coverage database to use
     *
     * @param string the filename of the database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * Iterate over all filesets and return the filename of all files.
     *
     * @return array an array of (basedir, filenames) pairs
     */
    private function getFilenames()
    {
        $files = [];

        foreach ($this->filelists as $fl) {
            try {
                $list = $fl->getFiles($this->project);
                foreach ($list as $file) {
                    $fs = new File((string) $fl->getDir($this->project), $file);
                    $files[] = ['key' => strtolower($fs->getAbsolutePath()), 'fullname' => $fs->getAbsolutePath()];
                }
            } catch (BuildException $be) {
                $this->log($be->getMessage(), Project::MSG_WARN);
            }
        }

        foreach ($this->filesets as $fileset) {
            $ds = $fileset->getDirectoryScanner($this->project);
            $ds->scan();

            $includedFiles = $ds->getIncludedFiles();

            foreach ($includedFiles as $file) {
                $fs = new File(realpath($ds->getBaseDir()), $file);

                $files[] = ['key' => strtolower($fs->getAbsolutePath()), 'fullname' => $fs->getAbsolutePath()];
            }
        }

        return $files;
    }

    public function main()
    {
        $files = $this->getFilenames();

        $this->log("Setting up coverage database for " . count($files) . " files");

        $props = new Properties();

        foreach ($files as $file) {
            $fullname = $file['fullname'];
            $filename = $file['key'];

            $props->setProperty($filename, serialize(['fullname' => $fullname, 'coverage' => []]));
        }

        $dbfile = new File($this->database);

        $props->store($dbfile);

        $this->project->setProperty('coverage.database', $dbfile->getAbsolutePath());
    }
}
