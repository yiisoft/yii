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
use Phar;
use PharData;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Project;
use Phing\Type\FileSet;
use UnexpectedValueException;

/**
 * Data task for {@link http://php.net/manual/en/class.phardata.php PharData class}.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class PharDataTask extends MatchingTask
{
    /**
     * @var File
     */
    private $destinationFile;

    /**
     * @var int
     */
    private $compression = Phar::NONE;

    /**
     * Base directory, from where local package paths will be calculated.
     *
     * @var File
     */
    private $baseDirectory;

    /**
     * @var FileSet[]
     */
    private $filesets = [];

    /**
     * @return FileSet
     */
    public function createFileSet()
    {
        $this->fileset = new FileSet();
        $this->filesets[] = $this->fileset;

        return $this->fileset;
    }

    /**
     * Compression type (gzip, bzip2, none) to apply to the packed files.
     *
     * @param string $compression
     */
    public function setCompression($compression)
    {
        // If we don't support passed compression, leave old one.
        switch ($compression) {
            case 'gzip':
                $this->compression = Phar::GZ;

                break;

            case 'bzip2':
                $this->compression = Phar::BZ2;

                break;

            default:
                break;
        }
    }

    /**
     * Destination (output) file.
     */
    public function setDestFile(File $destinationFile)
    {
        $this->destinationFile = $destinationFile;
    }

    /**
     * Base directory, which will be deleted from each included file (from path).
     * Paths with deleted basedir part are local paths in archive.
     */
    public function setBaseDir(File $baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * @throws BuildException
     */
    public function main()
    {
        $this->checkPreconditions();

        try {
            $this->log(
                'Building archive: ' . $this->destinationFile->__toString(),
                Project::MSG_INFO
            );

            // Delete old archive, if exists.
            if ($this->destinationFile->exists()) {
                $isDeleted = $this->destinationFile->delete();
                if (!$isDeleted) {
                    $this->log("Could not delete destination file {$this->destinationFile}", Project::MSG_WARN);
                }
            }

            $pharData = new PharData($this->baseDirectory->getPath() . '/' . $this->destinationFile->getName());

            foreach ($this->filesets as $fileset) {
                $this->log(
                    'Adding specified files in ' . $fileset->getDir($this->project) . ' to archive',
                    Project::MSG_VERBOSE
                );

                $pharData->buildFromIterator($fileset->getIterator(), $fileset->getDir($this->project));
            }

            if (Phar::NONE !== $this->compression && $pharData->canCompress($this->compression)) {
                try {
                    $pharData->compress($this->compression);
                } catch (UnexpectedValueException $uve) {
                    $pharData->compressFiles($this->compression);
                }

                unset($pharData);
            }
        } catch (Exception $e) {
            throw new BuildException(
                'Problem creating archive: ' . $e->getMessage(),
                $e,
                $this->getLocation()
            );
        }
    }

    /**
     * @throws BuildException
     */
    private function checkPreconditions()
    {
        if (!extension_loaded('phar')) {
            throw new BuildException(
                "PharDataTask require either PHP 5.3 or better or the PECL's Phar extension"
            );
        }

        if (null === $this->destinationFile) {
            throw new BuildException('destfile attribute must be set!', $this->getLocation());
        }

        if ($this->destinationFile->exists() && $this->destinationFile->isDirectory()) {
            throw new BuildException('destfile is a directory!', $this->getLocation());
        }

        if (!$this->destinationFile->canWrite()) {
            throw new BuildException('Can not write to the specified destfile!', $this->getLocation());
        }

        if (null === $this->baseDirectory) {
            throw new BuildException('basedir cattribute must be set', $this->getLocation());
        }

        if (!$this->baseDirectory->exists()) {
            throw new BuildException(
                "basedir '" . (string) $this->baseDirectory . "' does not exist!",
                $this->getLocation()
            );
        }
    }
}
