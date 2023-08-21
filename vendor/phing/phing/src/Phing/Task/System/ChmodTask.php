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
 * @author  Manuel Holtgrewe <grin@gmx.net>
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class ChmodTask extends Task
{
    use DirSetAware;
    use FileSetAware;

    private $file;

    private $mode;

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
     * Set quiet mode, which suppresses warnings if chmod() fails.
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
    public function setVerbose(bool $verbose)
    {
        $this->verbose = $verbose;
    }

    /**
     * Sets a single source file to touch.  If the file does not exist
     * an empty file will be created.
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    public function setMode(string $str)
    {
        $this->mode = $str;
    }

    /**
     * Execute the touch operation.
     */
    public function main()
    {
        // Check Parameters
        $this->checkParams();
        $this->chmod();
    }

    /**
     * Ensure that correct parameters were passed in.
     *
     * @throws BuildException
     */
    private function checkParams()
    {
        if (null === $this->file && empty($this->filesets) && empty($this->dirsets)) {
            throw new BuildException(
                'Specify at least one source - a file, dirset or a fileset.'
            );
        }

        if (null === $this->mode) {
            throw new BuildException('You have to specify an octal mode for chmod.');
        }

        // check for mode to be in the correct format
        if (!preg_match('/^([0-7]){3,4}$/', $this->mode)) {
            throw new BuildException('You have specified an invalid mode.');
        }
    }

    /**
     * Does the actual work.
     */
    private function chmod()
    {
        if (4 === strlen($this->mode)) {
            $mode = octdec($this->mode);
        } else {
            // we need to prepend the 0 before converting
            $mode = octdec('0' . $this->mode);
        }

        // counters for non-verbose output
        $total_files = 0;
        $total_dirs = 0;

        // one file
        if (null !== $this->file) {
            $total_files = 1;
            $this->chmodFile($this->file, $mode);
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
                $this->chmodFile(new File($fromDir, $srcFiles[$j]), $mode);
            }

            $dircount = count($srcDirs);
            $total_dirs += $dircount;
            for ($j = 0; $j < $dircount; ++$j) {
                $this->chmodFile(new File($fromDir, $srcDirs[$j]), $mode);
            }
        }

        if (!$this->verbose) {
            $this->log('Total files changed to ' . vsprintf('%o', [$mode]) . ': ' . $total_files);
            $this->log('Total directories changed to ' . vsprintf('%o', [$mode]) . ': ' . $total_dirs);
        }
    }

    /**
     * Actually change the mode for the file.
     *
     * @param int $mode
     *
     * @throws BuildException
     * @throws Exception
     */
    private function chmodFile(File $file, $mode)
    {
        if (!$file->exists()) {
            throw new BuildException('The file ' . $file->__toString() . ' does not exist');
        }

        try {
            $file->setMode($mode);
            if ($this->verbose) {
                $this->log("Changed file mode on '" . $file->__toString() . "' to " . vsprintf('%o', [$mode]));
            }
        } catch (Exception $e) {
            if ($this->failonerror) {
                throw $e;
            }

            $this->log($e->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
        }
    }
}
