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

use Phing\Exception\BuildException;
use Phing\Io\FileSystem;
use Phing\Io\File;
use Phing\Project;
use Phing\Task\System\MatchingTask;
use Phing\Type\Element\FileSetAware;

/**
 * Base class for extracting tasks such as Unzip and Untar.
 *
 * @author  Joakim Bodin <joakim.bodin+phing@gmail.com>
 * @package phing.tasks.ext
 * @since   2.2.0
 */
abstract class ExtractBaseTask extends MatchingTask
{
    use FileSetAware;

    /**
     * @var File $file
     */
    protected $file;
    /**
     * @var File $todir
     */
    protected $todir;
    protected $removepath;

    /**
     * Set to true to always extract (and possibly overwrite)
     * all files from the archive
     *
     * @var boolean
     */
    protected $forceExtract = false;

    /**
     * Set the name of the zip file to extract.
     *
     * @param  File $file zip file to extract
     * @return void
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * This is the base directory to look in for things to zip.
     *
     * @param  File $todir
     * @return void
     */
    public function setToDir(File $todir)
    {
        $this->todir = $todir;
    }

    /**
     * @param $removepath
     * @return void
     */
    public function setRemovePath($removepath)
    {
        $this->removepath = $removepath;
    }

    /**
     * Sets the forceExtract attribute
     *
     * @param  boolean $forceExtract
     * @return void
     */
    public function setForceExtract(bool $forceExtract)
    {
        $this->forceExtract = $forceExtract;
    }

    /**
     * do the work
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->validateAttributes();

        $filesToExtract = [];
        if ($this->file !== null) {
            if ($this->forceExtract || !$this->isDestinationUpToDate($this->file)) {
                $filesToExtract[] = $this->file;
            } else {
                $this->log(
                    'Nothing to do: ' . $this->todir->getAbsolutePath() . ' is up to date for ' . $this->file->getCanonicalPath(),
                    Project::MSG_INFO
                );
            }
        }

        foreach ($this->filesets as $compressedArchiveFileset) {
            $compressedArchiveDirScanner = $compressedArchiveFileset->getDirectoryScanner($this->project);
            $compressedArchiveFiles = $compressedArchiveDirScanner->getIncludedFiles();
            $compressedArchiveDir = $compressedArchiveFileset->getDir($this->project);

            foreach ($compressedArchiveFiles as $compressedArchiveFilePath) {
                $compressedArchiveFile = new File($compressedArchiveDir, $compressedArchiveFilePath);
                if ($compressedArchiveFile->isDirectory()) {
                    throw new BuildException(
                        $compressedArchiveFile->getAbsolutePath() . ' compressed archive cannot be a directory.'
                    );
                }

                if ($this->forceExtract || !$this->isDestinationUpToDate($compressedArchiveFile)) {
                    $filesToExtract[] = $compressedArchiveFile;
                } else {
                    $this->log(
                        'Nothing to do: ' . $this->todir->getAbsolutePath() . ' is up to date for ' . $compressedArchiveFile->getCanonicalPath(),
                        Project::MSG_INFO
                    );
                }
            }
        }

        foreach ($filesToExtract as $compressedArchiveFile) {
            $this->extractArchive($compressedArchiveFile);
        }
    }

    /**
     * @param File $compressedArchiveFile
     * @return mixed
     */
    abstract protected function extractArchive(File $compressedArchiveFile);

    /**
     * @param File $compressedArchiveFile
     * @return boolean
     *@throws BuildException
     * @internal param PhingFile $dir
     * @internal param array $files array of filenames
     */
    protected function isDestinationUpToDate(File $compressedArchiveFile)
    {
        if (!$compressedArchiveFile->exists()) {
            throw new BuildException("Could not find file " . $compressedArchiveFile->__toString() . " to extract.");
        }

        $compressedArchiveContent = $this->listArchiveContent($compressedArchiveFile);
        if (is_array($compressedArchiveContent)) {
            $fileSystem = FileSystem::getFileSystem();
            foreach ($compressedArchiveContent as $compressArchivePathInfo) {
                $compressArchiveFilename = $compressArchivePathInfo['filename'];
                if (!empty($this->removepath) && strlen($compressArchiveFilename) >= strlen($this->removepath)) {
                    $compressArchiveFilename = preg_replace(
                        '/^' . $this->removepath . '/',
                        '',
                        $compressArchiveFilename
                    );
                }
                $compressArchivePath = new File($this->todir, $compressArchiveFilename);

                if (
                    !$compressArchivePath->exists()
                    || $fileSystem->compareMTimes(
                        $compressedArchiveFile->getCanonicalPath(),
                        $compressArchivePath->getCanonicalPath()
                    ) == 1
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param File $compressedArchiveFile
     * @return mixed
     */
    abstract protected function listArchiveContent(File $compressedArchiveFile);

    /**
     * Validates attributes coming in from XML
     *
     * @return void
     * @throws BuildException
     */
    protected function validateAttributes()
    {
        if ($this->file === null && count($this->filesets) === 0) {
            throw new BuildException("Specify at least one source compressed archive - a file or a fileset.");
        }

        if ($this->todir === null) {
            throw new BuildException("todir must be set.");
        }

        if ($this->todir !== null && $this->todir->exists() && !$this->todir->isDirectory()) {
            throw new BuildException("todir must be a directory.");
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Compressed archive file cannot be a directory.");
        }

        if ($this->file !== null && !$this->file->exists()) {
            throw new BuildException(
                "Could not find compressed archive file " . $this->file->__toString() . " to extract."
            );
        }
    }
}
