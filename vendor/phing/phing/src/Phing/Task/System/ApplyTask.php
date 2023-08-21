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
use Phing\Io\DirectoryScanner;
use Phing\Io\File;
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Io\SourceFileScanner;
use Phing\Phing;
use Phing\Project;
use Phing\Type\Commandline;
use Phing\Type\CommandlineMarker;
use Phing\Type\DirSet;
use Phing\Type\Element\ResourceAware;
use Phing\Type\FileList;
use Phing\Type\Mapper;
use UnexpectedValueException;

/**
 * Executes a command on the (filtered) file list/set.
 * (Loosely based on the "Ant Apply" task - http://ant.apache.org/manual/Tasks/apply.html).
 *
 * @author  Utsav Handa <handautsav at hotmail dot com>
 *
 * @todo Add support for mapper, targetfile expressions
 */
class ApplyTask extends ExecTask
{
    use ResourceAware;

    public const SOURCEFILE_ID = '__SOURCEFILE__';
    protected $currentdirectory;

    /**
     * Whether output should be appended to or overwrite an existing file.
     *
     * @var bool
     */
    protected $appendoutput = false;

    /**
     * Runs the command only once, appending all files as arguments
     * else command will be executed once for every file.
     *
     * @var bool
     */
    protected $parallel = false;

    /**
     * Whether source file name should be added to the end of command automatically.
     *
     * @var bool
     */
    protected $addsourcefile = true;

    /**
     * Whether the filenames should be passed on the command line as relative pathnames (relative to the base directory of the corresponding fileset/list).
     *
     * @var bool
     */
    protected $relative = false;

    protected $currentos;
    protected $osvariant;

    /**
     * Logging level for status messages.
     *
     * @var int
     */
    protected $loglevel;

    /**
     * Whether to use forward-slash as file-separator on the file names.
     *
     * @var bool
     */
    protected $forwardslash = false;

    /**
     * Limit the amount of parallelism by passing at most this many sourcefiles at once
     * (Set it to <= 0 for unlimited).
     *
     * @var int
     */
    protected $maxparallel = 0;

    protected static $types = [
        'FILE' => 'file',
        'DIR' => 'dir',
        'BOTH' => 'both',
    ];

    protected $type = 'file';
    /**
     * @var CommandlineMarker
     */
    protected $targetFilePos;
    /**
     * @var CommandlineMarker
     */
    protected $srcFilePos;
    protected $srcIsFirst = true;

    protected $skipEmpty = false;
    private $force = false;
    private $mapper;
    private $destDir;

    /**
     * @var Mapper
     */
    private $mapperElement;
    private $additionalCmds;

    /**
     * Set whether empty filesets will be skipped.  If true and
     * no source files have been found or are newer than their
     * corresponding target files, the command will not be run.
     *
     * @param bool $skip whether to skip empty filesets
     */
    public function setSkipEmptyFilesets(bool $skip): void
    {
        $this->skipEmpty = $skip;
    }

    /**
     * Specify the directory where target files are to be placed.
     *
     * @param File $dest the File object representing the destination directory
     */
    public function setDest(File $dest): void
    {
        $this->destDir = $dest;
    }

    public function setAppend(bool $append): void
    {
        $this->appendoutput = $append;
    }

    /**
     * Run the command only once, appending all files as arguments.
     *
     * @param bool $parallel Identifier for files as arguments appending
     */
    public function setParallel(bool $parallel)
    {
        $this->parallel = $parallel;
    }

    /**
     * To add the source filename at the end of command of automatically.
     *
     * @param bool $addsourcefile Identifier for adding source file at the end of command
     */
    public function setAddsourcefile(bool $addsourcefile)
    {
        $this->addsourcefile = $addsourcefile;
    }

    /**
     * Whether the filenames should be passed on the command line as relative
     * pathnames (relative to the base directory of the corresponding fileset/list).
     */
    public function setRelative(bool $relative): void
    {
        $this->relative = $relative;
    }

    /**
     * Fail on command exits with a returncode other than zero.
     *
     * @param bool $failonerror Indicator to fail on error
     */
    public function setFailonerror(bool $failonerror): void
    {
        $this->checkreturn = $failonerror;
    }

    /**
     * Whether to use forward-slash as file-separator on the file names.
     *
     * @param bool $forwardslash Indicator to use forward-slash
     */
    public function setForwardslash(bool $forwardslash): void
    {
        $this->forwardslash = $forwardslash;
    }

    /**
     * Limit the amount of parallelism by passing at most this many sourcefiles at once.
     */
    public function setMaxparallel(int $max)
    {
        $this->maxparallel = $max;
    }

    public function setForce(bool $force): void
    {
        $this->force = $force;
    }

    /**
     * Set whether the command works only on files, directories or both.
     *
     * @param string $type a FileDirBoth EnumeratedAttribute
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Supports embedded <targetfile> element.
     *
     * @throws BuildException
     *
     * @return CommandlineMarker
     */
    public function createTargetfile()
    {
        if (null !== $this->targetFilePos) {
            throw new BuildException(
                $this->getTaskType() . " doesn\\'t support multiple "
                . 'targetfile elements.',
                $this->getLocation()
            );
        }

        $this->targetFilePos = $this->commandline->createMarker();
        $this->srcIsFirst = (null !== $this->srcFilePos);

        return $this->targetFilePos;
    }

    /**
     * Supports embedded <srcfile> element.
     *
     * @throws BuildException
     *
     * @return CommandlineMarker
     */
    public function createSrcfile()
    {
        if (null !== $this->srcFilePos) {
            throw new BuildException(
                $this->getTaskType() . " doesn\\'t support multiple "
                . 'srcfile elements.',
                $this->getLocation()
            );
        }

        $this->srcFilePos = $this->commandline->createMarker();

        return $this->srcFilePos;
    }

    /**
     * @throws BuildException
     *
     * @return Mapper
     */
    public function createMapper()
    {
        if (null !== $this->mapperElement) {
            throw new BuildException(
                'Cannot define more than one mapper',
                $this->getLocation()
            );
        }
        $this->mapperElement = new Mapper($this->getProject());

        return $this->mapperElement;
    }

    // T A S K  M E T H O D S

    /**
     * Do work.
     *
     * @throws BuildException
     */
    public function main()
    {
        try {
            // Log
            $this->log('Started ', $this->loglevel);
            // Initialize //
            $this->prepare();
            $haveExecuted = false;
            $totalFiles = 0;
            $totalDirs = 0;
            $fileNames = [];

            // Validate O.S. applicability
            if ($this->isValidOs()) {
                // Build the command //
                $this->buildCommand();
                // Process //
                // - FileSets
                foreach ($this->filesets as $fs) {
                    $currentType = $this->type;
                    if ($fs instanceof DirSet) {
                        if ($this->type !== self::$types['DIR']) {
                            $this->log(
                                "Found a nested dirset but type is {$this->type} . "
                                . 'Temporarily switching to type="dir" on the'
                                . ' assumption that you really did mean'
                                . ' <dirset> not <fileset>.',
                                Project::MSG_DEBUG
                            );
                            $currentType = 'dir';
                        }
                    }
                    $base = $fs->getDir($this->project);
                    $ds = $fs->getDirectoryScanner($this->project);
                    if ($currentType !== self::$types['DIR']) {
                        $s = $this->getFiles($base, $ds);
                        foreach ($s as $fileName) {
                            ++$totalFiles;
                            $fileNames[] = $fileName;
                            $baseDirs[] = $base;
                        }
                    }
                    if ($currentType !== self::$types['FILE']) {
                        $s = $this->getDirs($base, $ds);
                        foreach ($s as $fileName) {
                            ++$totalDirs;
                            $fileNames[] = $fileName;
                            $baseDirs[] = $base;
                        }
                    }
                    if (0 === count($fileNames) && $this->skipEmpty) {
                        $this->logSkippingFileset($currentType, $ds, $base);

                        continue;
                    }
                    $this->process(
                        $fs->getDirectoryScanner($this->project)->getIncludedFiles(),
                        $fs->getDir($this->project)
                    );
                    $haveExecuted = true;
                }
                unset($this->filesets);
                // - FileLists
                /**
                 * @var FileList $fl
                 */
                foreach ($this->filelists as $fl) {
                    ++$totalFiles;
                    $this->process($fl->getFiles($this->project), $fl->getDir($this->project));
                    $haveExecuted = true;
                }
                unset($this->filelists);
            }
            if ($haveExecuted) {
                $this->log(
                    'Applied ' . $this->commandline->getExecutable() . ' to '
                    . $totalFiles . ' file'
                    . (1 !== $totalFiles ? 's' : '') . ' and '
                    . $totalDirs . ' director'
                    . (1 !== $totalDirs ? 'ies' : 'y') . '.',
                    $this->loglevel
                );
            }
            /// Cleanup //
            $this->cleanup();
            // Log
            $this->log('End ', $this->loglevel);
        } catch (IOException | \InvalidArgumentException | UnexpectedValueException $e) {
            throw new BuildException('Execute failed: ' . $e, $e, $this->getLocation());
        }
    }

    /**
     * Prepares the filename per base directory and relative path information.
     *
     * @param array|string $filename
     * @param string       $basedir
     * @param string       $relative
     *
     * @throws IOException
     *
     * @return mixed processed filenames
     */
    public function getFilePath($filename, $basedir, $relative)
    {
        // Validating the 'file' information
        $files = (array) $filename;

        // Processing the file information
        foreach ($files as $index => $file) {
            $absolutefilename = ((false === $relative) ? ($basedir . FileUtils::getSeparator()) : '');
            $absolutefilename .= $file;
            if (false === $relative) {
                $files[$index] = (new FileUtils())->normalize($absolutefilename);
            } else {
                $files[$index] = $absolutefilename;
            }
        }

        return is_array($filename) ? $files : $files[0];
    }

    // T A S K  C O R E  M E T H O D S

    protected function getFiles(File $baseDir, DirectoryScanner $ds)
    {
        return $this->restrict($ds->getIncludedFiles(), $baseDir);
    }

    protected function getDirs(File $baseDir, DirectoryScanner $ds)
    {
        return $this->restrict($ds->getIncludedDirectories(), $baseDir);
    }

    protected function restrict($s, File $baseDir)
    {
        $sfs = new SourceFileScanner($this);

        return (null === $this->mapper || $this->force)
            ? $s
            : $sfs->restrict($s, $baseDir, $this->destDir, $this->mapper);
    }

    /**
     * Initializes the task operations, i.e.
     * - Required information validation
     * - Working directory.
     *
     * @throws BuildException
     * @throws IOException
     */
    protected function prepare()
    {
        // Log
        $this->log('Initializing started ', $this->loglevel);

        ///// Validating the required parameters /////

        if (!in_array($this->type, self::$types)) {
            throw new BuildException('Type must be one of \'file\', \'dir\' or \'both\'.');
        }

        // Executable
        if (null === $this->commandline->getExecutable()) {
            $this->throwBuildException('Please provide "executable" information');
        }

        // Retrieving the current working directory
        $this->currentdirectory = getcwd();

        // Directory (in which the command should be executed)
        if (null !== $this->dir) {
            // Try expanding (any) symbolic links
            if (!$this->dir->getCanonicalFile()->isDirectory()) {
                $this->throwBuildException("'" . $this->dir . "' is not a valid directory");
            }

            // Change working directory
            $dirchangestatus = @chdir($this->dir->getPath());

            // Log
            $this->log(
                'Working directory change ' . ($dirchangestatus ? 'successful' : 'failed') . ' to ' . $this->dir->getPath(),
                $this->loglevel
            );
        }

        ///// Preparing the task environment /////

        // Getting current operationg system
        $this->currentos = Phing::getProperty('os.name');

        // Log
        $this->log('Operating System identified : ' . $this->currentos, $this->loglevel);

        // Getting the O.S. type identifier
        // Validating the 'filesystem' for determining the OS type [UNIX, WINNT and WIN32]
        // (Another usage could be with 'os.name' for determination)
        if ('WIN' === strtoupper(substr(Phing::getProperty('host.fstype'), 0, 3))) {
            $this->osvariant = 'WIN'; // Probable Windows flavour
        } else {
            $this->osvariant = 'LIN'; // Probable GNU/Linux flavour
        }

        // Log
        $this->log('Operating System variant identified : ' . $this->osvariant, $this->loglevel);

        if (0 === count($this->filesets) && 0 === count($this->filelists) && 0 === count($this->getDirSets())) {
            throw new BuildException(
                'no resources specified',
                $this->getLocation()
            );
        }
        if (null !== $this->targetFilePos && null === $this->mapperElement) {
            throw new BuildException(
                'targetfile specified without mapper',
                $this->getLocation()
            );
        }
        if (null !== $this->destDir && null === $this->mapperElement) {
            throw new BuildException(
                'dest specified without mapper',
                $this->getLocation()
            );
        }

        if (null !== $this->mapperElement) {
            $this->mapper = $this->mapperElement->getImplementation();
            $this->log('Mapper identified : ' . get_class($this->mapper), $this->loglevel);
        }

        $this->commandline->setEscape($this->escape);

        // Log
        $this->log('Initializing completed ', $this->loglevel);
    }

    /**
     * Builds the full command to execute and stores it in $realCommand.
     *
     * @throws BuildException
     */
    protected function buildCommand()
    {
        // Log
        $this->log('Command building started ', $this->loglevel);

        // Building the executable
        $this->realCommand = (string) $this->commandline;

        $this->additionalCmds = '';

        // Adding the source filename at the end of command, validating the existing
        // sourcefile position explicit mentioning
        if (true === $this->addsourcefile) {
            $this->realCommand .= ' ' . self::SOURCEFILE_ID;
        }

        // Setting command output redirection with content appending
        if (null !== $this->output) {
            $this->additionalCmds .= sprintf(
                ' 1>%s %s',
                $this->appendoutput ? '>' : '',
                escapeshellarg($this->output->getPath())
            );
        } elseif ($this->spawn) { // Validating the 'spawn' configuration, and redirecting the output to 'null'
            $this->additionalCmds .= sprintf(' %s', 'WIN' === $this->osvariant ? '> NUL' : '1>/dev/null');

            $this->log('For process spawning, setting Output nullification ', $this->loglevel);
        }

        // Setting command error redirection with content appending
        if (null !== $this->error) {
            $this->additionalCmds .= sprintf(
                ' 2>%s %s',
                $this->appendoutput ? '>' : '',
                escapeshellarg($this->error->getPath())
            );
        }

        // Setting the execution as a background process
        if ($this->spawn) {
            // Validating the O.S. variant
            if ('WIN' === $this->osvariant) {
                $this->additionalCmds = 'start /b ' . $this->additionalCmds; // MS Windows background process forking
            } else {
                $this->additionalCmds .= ' &'; // GNU/Linux background process forking
            }
        }

        $this->additionalCmds = rtrim($this->additionalCmds);

        // Log
        $this->log('Command built : ' . $this->realCommand . $this->additionalCmds, $this->loglevel);

        // Log
        $this->log('Command building completed ', $this->loglevel);
    }

    /**
     * Runs cleanup tasks post execution
     * - Restore working directory.
     *
     * @param null|mixed $return
     * @param null|mixed $output
     */
    protected function cleanup($return = null, $output = null): void
    {
        // Restore working directory
        if (null !== $this->dir) {
            @chdir($this->currentdirectory);
        }
    }

    private function logSkippingFileset($currentType, DirectoryScanner $ds, File $base)
    {
        $includedCount = (
            ($currentType !== self::$types['DIR']) ? $ds->getIncludedFilesCount() : 0
        ) + (
            ($currentType !== self::$types['FILES']) ? $ds->getIncludedDirectoriesCount() : 0
        );
        $this->log(
            'Skipping fileset for directory ' . $base . '. It is '
            . (($includedCount > 0) ? 'up to date.' : 'empty.'),
            $this->loglevel
        );
    }

    /**
     * Processes the files list with provided information for execution.
     *
     * @param array  $srcFiles File list for processing
     * @param string $basedir  Base directory of the file list
     *
     * @throws BuildException
     * @throws IOException
     * @throws \InvalidArgumentException
     */
    private function process($srcFiles, $basedir)
    {
        // Log
        $this->log("Processing files with base directory ({$basedir}) ", $this->loglevel);
        $targets = [];
        if (null !== $this->targetFilePos) {
            $addedFiles = [];
            foreach ($srcFiles as $count => $file) {
                if (null !== $this->mapper) {
                    $subTargets = $this->mapper->main($file);
                    if (null !== $subTargets) {
                        foreach ($subTargets as $subTarget) {
                            if ($this->relative) {
                                $name = $subTarget;
                            } else {
                                $name = (new File($this->destDir, $subTarget))->getAbsolutePath();
                            }
                            if ($this->forwardslash && '/' !== FileUtils::getSeparator()) {
                                $name = str_replace(FileUtils::getSeparator(), '/', $name);
                            }
                            if (!isset($addedFiles[$name])) {
                                $targets[] = $name;
                                $addedFiles[] = $name;
                            }
                        }
                    }
                }
            }
        }
        $targetFiles = $targets;

        if (!$this->addsourcefile) {
            $srcFiles = [];
        }
        $orig = $this->commandline->getCommandline();
        $result = []; // range(0,count($orig) + count($srcFiles) + count($targetFiles));

        $srcIndex = count($orig);
        if (null !== $this->srcFilePos) {
            $srcIndex = $this->srcFilePos->getPosition();
        }
        if (null !== $this->targetFilePos) {
            $targetIndex = $this->targetFilePos->getPosition();

            if ($srcIndex < $targetIndex || ($srcIndex === $targetIndex && $this->srcIsFirst)) {
                // 0 --> srcIndex
                $result[] = $orig;

                // srcIndex --> targetIndex
                $result += array_slice($orig, $srcIndex + count($srcFiles), $targetIndex - $srcIndex, true);

                $result[] = $orig;
                $result[] = $targetFiles;
                $result = array_merge(...$result);

                // targetIndex --> end
                $result = array_merge(
                    array_slice(
                        $orig,
                        $targetIndex + count($srcFiles) + count($targetFiles),
                        count($orig) - $targetIndex,
                        true
                    ),
                    $result
                );
            } else {
                // 0 --> targetIndex
                $result[] = $orig;
                $result[] = $targetFiles;
                $result = array_merge(...$result);

                // targetIndex --> srcIndex
                $result = array_merge(
                    array_slice(
                        $orig,
                        $targetIndex + count($targetFiles),
                        $srcIndex - $targetIndex,
                        true
                    ),
                    $result
                );

                // srcIndex --> end
                $result = array_merge(
                    array_slice(
                        $orig,
                        $srcIndex + count($srcFiles) + count($targetFiles),
                        count($orig) - $srcIndex,
                        true
                    ),
                    $result
                );
                $srcIndex += count($targetFiles);
            }
        } else { // no targetFilePos
            // 0 --> srcIndex
            $result = array_merge(array_slice($orig, 0, $srcIndex, true), $result);
            // srcIndex --> end
            $result = array_merge(
                array_slice($orig, $srcIndex + count($srcFiles), count($orig) - $srcIndex, true),
                $result
            );
        }
        // fill in source file names
        foreach ($srcFiles as $i => $file) {
            if ($this->relative) {
                $src = $file;
            } else {
                $src = (new File($basedir, $file))->getAbsolutePath();
            }
            if ($this->forwardslash && '/' !== FileUtils::getSeparator()) {
                $src = str_replace(FileUtils::getSeparator(), '/', $src);
            }
            if (
                null !== $this->srcFilePos
                && ('' !== $this->srcFilePos->getPrefix()
                    || '' !== $this->srcFilePos->getSuffix())
            ) {
                $src = $this->srcFilePos->getPrefix() . $src . $this->srcFilePos->getSuffix();
            }
            $result[$srcIndex + $i] = $src;
        }

        $this->commandline = new Commandline(implode(' ', $result));
        $this->commandline->setEscape($this->escape);
        $this->realCommand = (string) $this->commandline . $this->additionalCmds;

        [$returncode, $output] = $this->executeCommand();

        $this->maybeSetReturnPropertyValue($returncode);

        // Sets the output property
        if ($this->outputProperty) {
            $previousValue = $this->project->getProperty($this->outputProperty);
            if (!empty($previousValue)) {
                $previousValue .= "\n";
            }
            $this->project->setProperty($this->outputProperty, $previousValue . implode("\n", $output));
        }

        // Validating the 'return-code'
        if ($this->checkreturn && (0 !== $returncode)) {
            $this->throwBuildException("Task exited with code ({$returncode})");
        }
    }

    /**
     * Throws the exception with specified information.
     *
     * @param string $information Exception information
     *
     * @throws BuildException
     */
    private function throwBuildException($information): void
    {
        throw new BuildException('ApplyTask: ' . (string) $information);
    }
}
