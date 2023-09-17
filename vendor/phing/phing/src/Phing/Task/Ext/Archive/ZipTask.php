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

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\IOException;
use Phing\Io\File;
use Phing\Io\SourceFileScanner;
use Phing\Mapper\MergeMapper;
use Phing\Project;
use Phing\Task\System\MatchingTask;
use Phing\Type\FileSet;
use ZipArchive;

/**
 * Creates a zip archive using PHP ZipArchive extension/
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext
 * @since   2.1.0
 */
class ZipTask extends MatchingTask
{
    /**
     * @var File
     */
    private $zipFile;

    /**
     * @var File
     */
    private $baseDir;

    /**
     * Whether to include empty dirs in the archive.
     */
    private $includeEmpty = true;

    private $filesets = [];

    private $ignoreLinks = false;

    /**
     * File path prefix in zip archive
     *
     * @var string
     */
    private $prefix = null;

    /**
     * Comment for zip archive.
     *
     * @var string $comment
     */
    private $comment = '';

    /**
     * Add a new fileset.
     *
     * @return ZipFileSet
     */
    public function createFileSet()
    {
        $this->fileset = new ZipFileSet();
        $this->filesets[] = $this->fileset;

        return $this->fileset;
    }

    /**
     * Add a new fileset.
     *
     * @param ZipFileSet $fileset
     */
    public function addZipFileSet(ZipFileSet $fileset)
    {
        $this->filesets[] = $fileset;
    }

    /**
     * Set is the name/location of where to create the zip file.
     *
     * @param File $destFile The output of the zip
     */
    public function setDestFile(File $destFile)
    {
        $this->zipFile = $destFile;
    }

    /**
     * This is the base directory to look in for things to zip.
     *
     * @param File $baseDir
     */
    public function setBasedir(File $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * Sets the file path prefix for file in the zip file.
     *
     * @param string $prefix Prefix
     *
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Set the include empty dirs flag.
     *
     * @param  boolean  Flag if empty dirs should be tarred too
     * @return void
     */
    public function setIncludeEmptyDirs($bool)
    {
        $this->includeEmpty = (bool) $bool;
    }

    /**
     * Set the ignore symlinks flag.
     *
     * @param  boolean $bool Flag if symlinks should be ignored
     * @return void
     */
    public function setIgnoreLinks($bool)
    {
        $this->ignoreLinks = (bool) $bool;
    }

    /**
     * Add a comment to the zip archive.
     *
     * @param string $text
     *
     * @return void
     */
    public function setComment($text)
    {
        $this->comment = $text;
    }

    /**
     * do the work
     *
     * @throws BuildException
     */
    public function main()
    {
        if (!extension_loaded('zip')) {
            throw new BuildException("Zip extension is required");
        }

        if ($this->zipFile === null) {
            throw new BuildException("zipfile attribute must be set!", $this->getLocation());
        }

        if ($this->zipFile->exists() && $this->zipFile->isDirectory()) {
            throw new BuildException("zipfile is a directory!", $this->getLocation());
        }

        if ($this->zipFile->exists() && !$this->zipFile->canWrite()) {
            throw new BuildException("Can not write to the specified zipfile!", $this->getLocation());
        }

        try {
            if ($this->baseDir !== null) {
                if (!$this->baseDir->exists()) {
                    throw new BuildException(
                        "basedir '" . (string) $this->baseDir . "' does not exist!",
                        $this->getLocation()
                    );
                }

                if (empty($this->filesets)) {
                    // add the main fileset to the list of filesets to process.
                    $mainFileSet = new ZipFileSet($this->fileset);
                    $mainFileSet->setDir($this->baseDir);
                    $this->filesets[] = $mainFileSet;
                }
            }

            if (empty($this->filesets)) {
                throw new BuildException(
                    "You must supply either a basedir "
                    . "attribute or some nested filesets.",
                    $this->getLocation()
                );
            }

            // check if zip is out of date with respect to each
            // fileset
            if ($this->areFilesetsUpToDate()) {
                $this->log("Nothing to do: " . $this->zipFile->__toString() . " is up to date.", Project::MSG_INFO);

                return;
            }

            $this->log("Building zip: " . $this->zipFile->__toString(), Project::MSG_INFO);

            $zip = new ZipArchive();
            $res = $zip->open($this->zipFile->getAbsolutePath(), ZipArchive::CREATE);

            if ($res !== true) {
                throw new Exception("ZipArchive::open() failed with code " . $res);
            }

            if ($this->comment !== '') {
                $isCommented = $zip->setArchiveComment($this->comment);
                if ($isCommented === false) {
                    $this->log("Could not add a comment for the Archive.", Project::MSG_INFO);
                }
            }

            $this->addFilesetsToArchive($zip);

            $zip->close();
        } catch (IOException $ioe) {
            $msg = "Problem creating ZIP: " . $ioe->getMessage();
            throw new BuildException($msg, $ioe, $this->getLocation());
        }
    }

    /**
     * @param  array $files array of filenames
     * @param  File $dir
     * @return boolean
     */
    private function archiveIsUpToDate($files, $dir)
    {
        $sfs = new SourceFileScanner($this);
        $mm = new MergeMapper();
        $mm->setTo($this->zipFile->getAbsolutePath());

        return count($sfs->restrict($files, $dir, null, $mm)) == 0;
    }

    /**
     * @return array
     * @throws BuildException
     */
    public function areFilesetsUpToDate()
    {
        /**
         * @var FileSet $fs
         */
        foreach ($this->filesets as $fs) {
            $files = $fs->getIterator($this->includeEmpty);
            if (!$this->archiveIsUpToDate($files, $fs->getDir($this->project))) {
                return false;
            }
            for ($i = 0, $fcount = count($files); $i < $fcount; $i++) {
                if ($this->zipFile->equals(new File($fs->getDir($this->project), $files[$i]))) {
                    throw new BuildException("A zip file cannot include itself", $this->getLocation());
                }
            }
        }
        return true;
    }

    /**
     * @param $zip
     */
    private function addFilesetsToArchive($zip)
    {
        foreach ($this->filesets as $fs) {
            $fsBasedir = (null != $this->baseDir) ? $this->baseDir :
                $fs->getDir($this->project);

            $files = $fs->getIterator($this->includeEmpty);

            foreach ($files as $file) {
                $f = new File($fsBasedir, $file);

                $pathInZip = $this->prefix
                    . $f->getPathWithoutBase($fsBasedir);

                $pathInZip = str_replace('\\', '/', $pathInZip);

                if ($this->ignoreLinks && $f->isLink()) {
                    continue;
                }

                if ($f->isDirectory()) {
                    if ($pathInZip != '.') {
                        $zip->addEmptyDir($pathInZip);
                    }
                } else {
                    $zip->addFile($f->getAbsolutePath(), $pathInZip);
                }
                $this->log("Adding " . $f->getPath() . " as " . $pathInZip . " to archive.", Project::MSG_VERBOSE);
            }
        }
    }
}
