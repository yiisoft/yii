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
use Phing\Io\IOException;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\FileListAware;
use Phing\Type\Element\FileSetAware;
use Phing\Type\Mapper;

/**
 * Touch a file and/or fileset(s); corresponds to the Unix touch command.
 *
 * If the file to touch doesn't exist, an empty one is created.
 */
class TouchTask extends Task
{
    use FileListAware;
    use FileSetAware;

    /**
     * @var File
     */
    private $file;
    private $seconds = -1;
    private $dateTime;
    private $mkdirs = false;
    private $verbose = true;

    /** @var Mapper */
    private $mapperElement;

    /**
     * Sets a single source file to touch.  If the file does not exist
     * an empty file will be created.
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    /**
     * The new modification time of the file in milliseconds since midnight
     * Jan 1 1970. Negative values are not accepted nor are values less than
     * 1000. Note that PHP is actually based on seconds so the value passed
     * in will be divided by 1000.
     *
     * Optional, default=now
     */
    public function setMillis(int $millis): void
    {
        if ($millis >= 0) {
            if ($millis >= 1000) {
                $this->seconds = (int) $millis / 1000;
            } else {
                throw new BuildException('Millis less than 1000 would be treated as 0');
            }
        } else {
            throw new BuildException('Millis attribute cannot be negative');
        }
    }

    /**
     * the new modification time of the file
     * in seconds since midnight Jan 1 1970.
     * Optional, default=now.
     *
     * @param int $seconds
     */
    public function setSeconds($seconds): void
    {
        if ($seconds >= 0) {
            $this->seconds = (int) $seconds;
        } else {
            throw new BuildException('Seconds attribute cannot be negative');
        }
    }

    /**
     * the new modification time of the file
     * in the format MM/DD/YYYY HH:MM AM or PM;
     * Optional, default=now.
     *
     * @param string $dateTime
     */
    public function setDatetime($dateTime): void
    {
        $timestmap = strtotime($dateTime);
        if (false !== $timestmap) {
            $this->dateTime = (string) $dateTime;
            $this->setSeconds($timestmap);
        } else {
            throw new BuildException(
                "Date of {$dateTime} cannot be parsed correctly. "
                . "It should be in a format parsable by PHP's strtotime() function."
                . PHP_EOL
            );
        }
    }

    /**
     * Set whether nonexistent parent directories should be created
     * when touching new files.
     *
     * @param bool $mkdirs whether to create parent directories
     */
    public function setMkdirs($mkdirs): void
    {
        $this->mkdirs = $mkdirs;
    }

    /**
     * Set whether the touch task will report every file it creates;
     * defaults to <code>true</code>.
     *
     * @param bool $verbose flag
     */
    public function setVerbose($verbose): void
    {
        $this->verbose = $verbose;
    }

    /**
     * Execute the touch operation.
     *
     * @throws BuildException
     * @throws IOException
     */
    public function createMapper(): Mapper
    {
        if (null !== $this->mapperElement) {
            throw new BuildException('Cannot define more than one mapper', $this->getLocation());
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * Execute the touch operation.
     *
     * @throws BuildException
     * @throws IOException
     */
    public function main()
    {
        $this->checkConfiguration();
        $this->touch();
    }

    /**
     * @throws IOException
     */
    protected function checkConfiguration(): void
    {
        $savedSeconds = $this->seconds;

        if (null === $this->file && 0 === count($this->filesets) && 0 === count($this->filelists)) {
            throw new BuildException('Specify at least one source - a file, a fileset or a filelist.');
        }

        if (null !== $this->file && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException('Use a fileset to touch directories.');
        }

        $this->log(
            'Setting seconds to ' . $savedSeconds . ' from datetime attribute',
            ($this->seconds < 0 ? Project::MSG_DEBUG : Project::MSG_VERBOSE)
        );

        $this->seconds = $savedSeconds;
    }

    /**
     * Does the actual work.
     *
     * @throws IOException
     * @throws \Exception
     */
    private function touch(): void
    {
        if (null !== $this->file) {
            if (!$this->file->exists()) {
                $this->log(
                    'Creating ' . $this->file,
                    $this->verbose ? Project::MSG_INFO : Project::MSG_VERBOSE
                );

                try { // try to create file
                    $this->file->createNewFile($this->mkdirs);
                } catch (IOException  $ioe) {
                    throw new BuildException(
                        'Error creating new file ' . $this->file,
                        $ioe,
                        $this->getLocation()
                    );
                }
            }
        }

        $resetSeconds = false;
        if ($this->seconds < 0) {
            $resetSeconds = true;
            // Note: this function actually returns seconds, not milliseconds (e.g. 1606505920.2657)
            $this->seconds = microtime(true);
        }

        if (null !== $this->file) {
            $this->touchFile($this->file);
        }

        $this->processFileSets();
        $this->processFileLists();

        if ($resetSeconds) {
            $this->seconds = -1;
        }
    }

    /**
     * @param $file
     */
    private function getMappedFileNames($file): array
    {
        if (null !== $this->mapperElement) {
            $mapper = $this->mapperElement->getImplementation();
            $results = $mapper->main($file);
            if (null === $results) {
                return [];
            }
            $fileNames = $results;
        } else {
            $fileNames = [$file];
        }

        return $fileNames;
    }

    /**
     * @throws \Exception
     */
    private function touchFile(File $file): void
    {
        if (!$file->canWrite()) {
            throw new BuildException('Can not change modification date of read-only file ' . (string) $file);
        }
        $file->setLastModified($this->seconds);
    }

    /**
     * @throws IOException
     * @throws \Exception
     */
    private function processFileSets(): void
    {
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->getProject());
            $fromDir = $fs->getDir($this->getProject());

            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            for ($j = 0, $_j = count($srcFiles); $j < $_j; ++$j) {
                foreach ($this->getMappedFileNames((string) $srcFiles[$j]) as $fileName) {
                    $this->touchFile(new File($fromDir, $fileName));
                }
            }

            for ($j = 0, $_j = count($srcDirs); $j < $_j; ++$j) {
                foreach ($this->getMappedFileNames((string) $srcDirs[$j]) as $fileName) {
                    $this->touchFile(new File($fromDir, $fileName));
                }
            }
        }
    }

    /**
     * @throws IOException
     * @throws \Exception
     */
    private function processFileLists(): void
    {
        foreach ($this->filelists as $fl) {
            $fromDir = $fl->getDir($this->getProject());
            $srcFiles = $fl->getFiles($this->getProject());
            foreach ($srcFiles as $jValue) {
                foreach ($this->getMappedFileNames((string) $jValue) as $fileName) {
                    $this->touchFile(new File($fromDir, $fileName));
                }
            }
        }
    }
}
