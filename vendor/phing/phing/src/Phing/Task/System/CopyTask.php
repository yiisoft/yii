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
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Io\SourceFileScanner;
use Phing\Mapper\FileNameMapper;
use Phing\Mapper\FlattenMapper;
use Phing\Mapper\IdentityMapper;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\FilterChainAware;
use Phing\Type\Element\ResourceAware;
use Phing\Type\Mapper;
use Phing\Util\RegisterSlot;

/**
 * A phing copy task.  Copies a file or directory to a new file
 * or directory.  Files are only copied if the source file is newer
 * than the destination file, or when the destination file does not
 * exist. It is possible to explicitly overwrite existing files.
 *
 * @author Andreas Aderhold, andi@binarycloud.com
 */
class CopyTask extends Task
{
    use ResourceAware;
    use FilterChainAware;

    /**
     * @var File
     */
    protected $file; // the source file (from xml attribute)

    /**
     * @var File
     */
    protected $destFile; // the destiantion file (from xml attribute)

    /**
     * @var File
     */
    protected $destDir; // the destination dir (from xml attribute)

    protected $overwrite = false; // overwrite destination (from xml attribute)
    protected $preserveLMT = false; // sync timestamps (from xml attribute)
    protected $preservePermissions = true; // sync permissions (from xml attribute)
    protected $includeEmpty = true; // include empty dirs? (from XML)
    protected $flatten = false; // apply the FlattenMapper right way (from XML)

    /**
     * @var Mapper
     */
    protected $mapperElement;

    protected $fileCopyMap = []; // asoc array containing mapped file names
    protected $dirCopyMap = []; // asoc array containing mapped file names
    protected $completeDirMap = []; // asoc array containing complete dir names

    /**
     * @var FileUtils
     */
    protected $fileUtils; // a instance of fileutils

    protected $verbosity = Project::MSG_VERBOSE;

    /**
     * @var int
     */
    protected $mode = 0; // mode to create directories with

    /**
     * @var bool
     */
    protected $haltonerror = true; // stop build on errors

    protected $enableMultipleMappings = false;

    /** @var int */
    protected $granularity = 0;

    /**
     * Sets up this object internal stuff.
     * i.e. the Fileutils instance and default mode.
     */
    public function __construct()
    {
        parent::__construct();
        $this->fileUtils = new FileUtils();
        $this->mode = 0777 - umask();
    }

    /**
     * Set the number of seconds leeway to give before deciding a
     * target is out of date.
     *
     * @param int $granularity the granularity used to decide if a target is out of date
     */
    public function setGranularity(int $granularity): void
    {
        $this->granularity = $granularity;
    }

    /**
     * Set the overwrite flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (bool primitive) is coming in here.
     *
     * @param bool $bool Overwrite the destination file(s) if it/they already exist
     */
    public function setOverwrite($bool)
    {
        $this->overwrite = (bool) $bool;
    }

    /**
     * Set whether files copied from directory trees will be "flattened"
     * into a single directory.  If there are multiple files with
     * the same name in the source directory tree, only the first
     * file will be copied into the "flattened" directory, unless
     * the forceoverwrite attribute is true.
     *
     * @param bool $flatten if true flatten the destination directory. Default
     *                      is false.
     */
    public function setFlatten($flatten)
    {
        $this->flatten = $flatten;
    }

    /**
     * Used to force listing of all names of copied files.
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
     * @see CopyTask::setPreserveLastModified
     */
    public function setTstamp(bool $preserveLastModified)
    {
        $this->setPreserveLastModified($preserveLastModified);
    }

    /**
     * Set the preserve timestamp flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (bool primitive) is coming in here.
     */
    public function setPreserveLastModified(bool $preserveLastModified)
    {
        $this->preserveLMT = $preserveLastModified;
    }

    /**
     * Set the preserve permissions flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (bool primitive) is coming in here.
     */
    public function setPreservepermissions(bool $preservePermissions)
    {
        $this->preservePermissions = $preservePermissions;
    }

    public function setPreservemode(bool $preserveMode)
    {
        $this->setPreservepermissions($preserveMode);
    }

    /**
     * Set the include empty dirs flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (bool primitive) is coming in here.
     */
    public function setIncludeEmptyDirs(bool $includeEmptyDirs)
    {
        $this->includeEmpty = $includeEmptyDirs;
    }

    /**
     * Set the file. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if necessary.
     *
     * @param File $file The source file. Either a string or an PhingFile object
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * Set the toFile. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if necessary.
     *
     * @param File $file The dest file. Either a string or an PhingFile object
     */
    public function setTofile(File $file)
    {
        $this->destFile = $file;
    }

    /**
     * Sets the mode to create destination directories with (ignored on Windows).
     * Default mode is taken from umask().
     *
     * @param int $mode Octal mode
     */
    public function setMode($mode)
    {
        $this->mode = (int) base_convert($mode, 8, 10);
    }

    /**
     * Set the toDir. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if necessary.
     *
     * @param File $dir The directory, either a string or an PhingFile object
     */
    public function setTodir(File $dir)
    {
        $this->destDir = $dir;
    }

    public function setEnableMultipleMappings($enableMultipleMappings)
    {
        $this->enableMultipleMappings = (bool) $enableMultipleMappings;
    }

    public function isEnabledMultipleMappings()
    {
        return $this->enableMultipleMappings;
    }

    /**
     * Set the haltonerror attribute - when true, will
     * make the build fail when errors are detected.
     *
     * @param bool $haltonerror Flag if the build should be stopped on errors
     */
    public function setHaltonerror($haltonerror)
    {
        $this->haltonerror = (bool) $haltonerror;
    }

    /**
     * Nested creator, creates one Mapper for this task.
     *
     * @throws BuildException
     *
     * @return Mapper The created Mapper type object
     */
    public function createMapper()
    {
        if (null !== $this->mapperElement) {
            throw new BuildException('Cannot define more than one mapper', $this->getLocation());
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * The main entry point where everything gets in motion.
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->validateAttributes();

        if (null !== $this->file) {
            if ($this->file->exists()) {
                if (null === $this->destFile) {
                    $this->destFile = new File($this->destDir, (string) $this->file->getName());
                }
                if (
                    true === $this->overwrite
                    || ($this->file->lastModified() - $this->granularity > $this->destFile->lastModified())
                ) {
                    $this->fileCopyMap[$this->file->getAbsolutePath()] = $this->destFile->getAbsolutePath();
                } else {
                    $this->log($this->file->getName() . ' omitted, ' . $this->destFile->getName() . ' is up to date');
                }
            } else {
                // terminate build
                $this->logError('Could not find file ' . $this->file->__toString() . ' to copy.');
            }
        }

        $project = $this->getProject();

        // process filelists
        foreach ($this->filelists as $fl) {
            $fromDir = $fl->getDir($project);
            $srcFiles = $fl->getFiles($project);
            $srcDirs = [$fl->getDir($project)];

            if (!$this->flatten && null === $this->mapperElement) {
                $this->completeDirMap[$fromDir->getAbsolutePath()] = $this->destDir->getAbsolutePath();
            }

            $this->scan($fromDir, $this->destDir, $srcFiles, $srcDirs);
        }

        foreach ($this->dirsets as $dirset) {
            try {
                $ds = $dirset->getDirectoryScanner($project);
                $fromDir = $dirset->getDir($project);
                $srcDirs = $ds->getIncludedDirectories();

                $srcFiles = [];
                foreach ($srcDirs as $srcDir) {
                    $srcFiles[] = $srcDir;
                }

                if (
                    !$this->flatten
                    && null === $this->mapperElement
                    && $ds->isEverythingIncluded()
                ) {
                    $this->completeDirMap[$fromDir->getAbsolutePath()] = $this->destDir->getAbsolutePath();
                }

                $this->scan($fromDir, $this->destDir, $srcFiles, $srcDirs);
            } catch (BuildException $e) {
                if (true === $this->haltonerror) {
                    throw $e;
                }

                $this->logError($e->getMessage());
            }
        }

        // process filesets
        foreach ($this->filesets as $fs) {
            try {
                $ds = $fs->getDirectoryScanner($project);
                $fromDir = $fs->getDir($project);
                $srcFiles = $ds->getIncludedFiles();
                $srcDirs = $ds->getIncludedDirectories();

                if (
                    !$this->flatten
                    && null === $this->mapperElement
                    && $ds->isEverythingIncluded()
                ) {
                    $this->completeDirMap[$fromDir->getAbsolutePath()] = $this->destDir->getAbsolutePath();
                }

                $this->scan($fromDir, $this->destDir, $srcFiles, $srcDirs);
            } catch (BuildException $e) {
                if (true == $this->haltonerror) {
                    throw $e;
                }

                $this->logError($e->getMessage());
            }
        }

        // go and copy the stuff
        $this->doWork();

        if (null !== $this->destFile) {
            $this->destDir = null;
        }
    }

    /**
     * Validates attributes coming in from XML.
     *
     * @throws BuildException
     */
    protected function validateAttributes()
    {
        if (null === $this->file && 0 === count($this->dirsets) && 0 === count($this->filesets) && 0 === count($this->filelists)) {
            throw new BuildException('CopyTask. Specify at least one source - a file, fileset or filelist.');
        }

        if (null !== $this->destFile && null !== $this->destDir) {
            throw new BuildException('Only one of destfile and destdir may be set.');
        }

        if (null === $this->destFile && null === $this->destDir) {
            throw new BuildException('One of destfile or destdir must be set.');
        }

        if (null !== $this->file && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException('Use a fileset to copy directories.');
        }

        if (null !== $this->destFile && (count($this->filesets) > 0 || count($this->dirsets) > 0)) {
            throw new BuildException('Cannot concatenate multiple files into a single file.');
        }

        if (null !== $this->destFile) {
            $this->destDir = new File($this->destFile->getParent());
        }
    }

    /**
     * Actually copies the files.
     *
     * @throws BuildException
     */
    protected function doWork()
    {
        // These "slots" allow filters to retrieve information about the currently-being-process files
        $fromSlot = $this->getRegisterSlot('currentFromFile');
        $fromBasenameSlot = $this->getRegisterSlot('currentFromFile.basename');

        $toSlot = $this->getRegisterSlot('currentToFile');
        $toBasenameSlot = $this->getRegisterSlot('currentToFile.basename');

        $mapSize = count($this->fileCopyMap);
        $total = $mapSize;

        // handle empty dirs if appropriate
        if ($this->includeEmpty) {
            $count = 0;
            foreach ($this->dirCopyMap as $srcdir => $destdir) {
                $s = new File((string) $srcdir);
                $d = new File((string) $destdir);
                if (!$d->exists()) {
                    // Setting source directory permissions to target
                    // (On permissions preservation, the target directory permissions
                    // will be inherited from the source directory, otherwise the 'mode'
                    // will be used)
                    $dirMode = ($this->preservePermissions ? $s->getMode() : $this->mode);

                    // Directory creation with specific permission mode
                    if (!$d->mkdirs($dirMode)) {
                        $this->logError('Unable to create directory ' . $d->__toString());
                    } else {
                        if ($this->preserveLMT) {
                            $d->setLastModified($s->lastModified());
                        }

                        ++$count;
                    }
                }
            }
            if ($count > 0) {
                $this->log(
                    'Created ' . $count . ' empty director' . (1 == $count ? 'y' : 'ies') . ' in ' . $this->destDir->getAbsolutePath()
                );
            }
        }

        if (0 == $mapSize) {
            return;
        }

        $this->log(
            'Copying ' . $mapSize . ' file' . (($mapSize) === 1 ? '' : 's') . ' to ' . $this->destDir->getAbsolutePath()
        );
        // walks the map and actually copies the files
        $count = 0;
        foreach ($this->fileCopyMap as $from => $toFiles) {
            if (is_array($toFiles)) {
                foreach ($toFiles as $to) {
                    $this->copyToSingleDestination(
                        $from,
                        $to,
                        $fromSlot,
                        $fromBasenameSlot,
                        $toSlot,
                        $toBasenameSlot,
                        $count,
                        $total
                    );
                }
            } else {
                $this->copyToSingleDestination(
                    $from,
                    $toFiles,
                    $fromSlot,
                    $fromBasenameSlot,
                    $toSlot,
                    $toBasenameSlot,
                    $count,
                    $total
                );
            }
        }
    }

    /**
     * @param string $message
     * @param null   $location
     *
     * @throws BuildException
     */
    protected function logError($message, $location = null)
    {
        if ($this->haltonerror) {
            throw new BuildException($message, $location);
        }

        $this->log($message, Project::MSG_ERR);
    }

    /**
     * Compares source files to destination files to see if they
     * should be copied.
     *
     * @param string $fromDir
     * @param string $toDir
     * @param array  $files
     * @param array  $dirs
     */
    private function scan(&$fromDir, &$toDir, &$files, &$dirs)
    {
        /* mappers should be generic, so we get the mappers here and
        pass them on to builMap. This method is not redundan like it seems */
        $mapper = $this->getMapper();

        $this->buildMap($fromDir, $toDir, $files, $mapper, $this->fileCopyMap);

        if ($this->includeEmpty) {
            $this->buildMap($fromDir, $toDir, $dirs, $mapper, $this->dirCopyMap);
        }
    }

    private function getMapper()
    {
        $mapper = null;
        if (null !== $this->mapperElement) {
            $mapper = $this->mapperElement->getImplementation();
        } elseif ($this->flatten) {
            $mapper = new FlattenMapper();
        } else {
            $mapper = new IdentityMapper();
        }

        return $mapper;
    }

    /**
     * Builds a map of filenames (from->to) that should be copied.
     *
     * @param string         $fromDir
     * @param string         $toDir
     * @param array          $names
     * @param FileNameMapper $mapper
     * @param array          $map
     */
    private function buildMap($fromDir, $toDir, &$names, $mapper, &$map)
    {
        $toCopy = null;
        if ($this->overwrite) {
            $v = [];
            foreach ($names as $name) {
                $result = $mapper->main($name);
                if (null !== $result) {
                    $v[] = $name;
                }
            }
            $toCopy = $v;
        } else {
            $ds = new SourceFileScanner($this);
            $toCopy = $ds->restrict($names, $fromDir, $toDir, $mapper);
        }

        for ($i = 0, $_i = count($toCopy); $i < $_i; ++$i) {
            $src = new File($fromDir, $toCopy[$i]);
            $mapped = $mapper->main($toCopy[$i]);
            if (!$this->enableMultipleMappings) {
                $dest = new File($toDir, $mapped[0]);
                $map[$src->getAbsolutePath()] = $dest->getAbsolutePath();
            } else {
                $mappedFiles = [];

                foreach ($mapped as $mappedFile) {
                    if (null === $mappedFile) {
                        continue;
                    }
                    $dest = new File($toDir, $mappedFile);
                    $mappedFiles[] = $dest->getAbsolutePath();
                }
                $map[$src->getAbsolutePath()] = $mappedFiles;
            }
        }
    }

    /**
     * @param string       $from
     * @param string       $to
     * @param RegisterSlot $fromSlot
     * @param RegisterSlot $fromBasenameSlot
     * @param RegisterSlot $toSlot
     * @param RegisterSlot $toBasenameSlot
     * @param int          $count
     * @param int          $total
     */
    private function copyToSingleDestination(
        $from,
        $to,
        $fromSlot,
        $fromBasenameSlot,
        $toSlot,
        $toBasenameSlot,
        &$count,
        &$total
    ) {
        if ($from === $to) {
            $this->log('Skipping self-copy of ' . $from, $this->verbosity);
            --$total;

            return;
        }
        $this->log('From ' . $from . ' to ' . $to, $this->verbosity);

        try { // try to copy file
            $fromFile = new File($from);
            $toFile = new File($to);

            $fromSlot->setValue($fromFile->getPath());
            $fromBasenameSlot->setValue($fromFile->getName());

            $toSlot->setValue($toFile->getPath());
            $toBasenameSlot->setValue($toFile->getName());

            $this->fileUtils->copyFile(
                $fromFile,
                $toFile,
                $this->getProject(),
                $this->overwrite,
                $this->preserveLMT,
                $this->filterChains,
                $this->mode,
                $this->preservePermissions,
                $this->granularity
            );

            ++$count;
        } catch (IOException $ioe) {
            $this->logError('Failed to copy ' . $from . ' to ' . $to . ': ' . $ioe->getMessage());
        }
    }
}
