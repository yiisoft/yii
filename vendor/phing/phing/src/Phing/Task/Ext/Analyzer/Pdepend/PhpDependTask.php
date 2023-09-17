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

namespace Phing\Task\Ext\Analyzer\Pdepend;

use PDepend\Application;
use PDepend\Util\Log;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Task;
use Phing\Type\Element\FileSetAware;
use Phing\Util\StringHelper;

/**
 * Runs the PHP_Depend software analyzer and metric tool.
 * Performs static code analysis on a given source base.
 *
 * @package phing.tasks.ext.pdepend
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @since   2.4.1
 */
class PhpDependTask extends Task
{
    use FileSetAware;

    /**
     * A php source code filename or directory
     *
     * @var File
     */
    protected $file = null;

    /**
     * List of allowed file extensions. Default file extensions are <b>php</b>
     * and <p>php5</b>.
     *
     * @var array<string>
     */
    protected $allowedFileExtensions = ['php', 'php5'];

    /**
     * List of exclude directories. Default exclude dirs are <b>.git</b>,
     * <b>.svn</b> and <b>CVS</b>.
     *
     * @var array<string>
     */
    protected $excludeDirectories = ['.git', '.svn', 'CVS'];

    /**
     * List of exclude packages
     *
     * @var array<string>
     */
    protected $excludePackages = [];

    /**
     * Should the parse ignore doc comment annotations?
     *
     * @var boolean
     */
    protected $withoutAnnotations = false;

    /**
     * Should PHP_Depend treat <b>+global</b> as a regular project package?
     *
     * @var boolean
     */
    protected $supportBadDocumentation = false;

    /**
     * Flag for enable/disable debugging
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * PHP_Depend configuration file
     *
     * @var File
     */
    protected $configFile = null;

    /**
     * Logger elements
     *
     * @var PhpDependLoggerElement[]
     */
    protected $loggers = [];

    /**
     * Analyzer elements
     *
     * @var PhpDependAnalyzerElement[]
     */
    protected $analyzers = [];

    /**
     * Flag that determines whether to halt on error
     *
     * @var boolean
     */
    protected $haltonerror = false;

    /**
     * @var bool
     */
    private $oldVersion = false;

    /**
     * @var string
     */
    protected $pharLocation = "";

    /**
     * Load the necessary environment for running PHP_Depend
     *
     * @throws BuildException
     */
    protected function requireDependencies()
    {
        if (!empty($this->pharLocation)) {
            // nasty but necessary: reorder the autoloaders so the one in the PHAR gets priority
            $autoloadFunctions = spl_autoload_functions();
            $composerAutoloader = null;
            if (get_class($autoloadFunctions[0][0]) === 'Composer\Autoload\ClassLoader') {
                $composerAutoloader = $autoloadFunctions[0];
                spl_autoload_unregister($composerAutoloader);
            }

            $GLOBALS['_SERVER']['SCRIPT_NAME'] = '-';
            ob_start();
            include_once 'phar://' . $this->pharLocation . '/vendor/autoload.php';
            ob_end_clean();

            if ($composerAutoloader !== null) {
                spl_autoload_register($composerAutoloader);
            }
        }

        // check 2.x version (composer/phar)
        if (class_exists('PDepend\\TextUI\\Runner')) {
            return;
        }

        throw new BuildException("This task requires PDepend 2.x", $this->getLocation());
    }

    /**
     * Set the input source file or directory
     *
     * @param File $file The input source file or directory
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * Sets a list of filename extensions for valid php source code files
     *
     * @param string $fileExtensions List of valid file extensions
     */
    public function setAllowedFileExtensions($fileExtensions)
    {
        $this->allowedFileExtensions = [];

        $token = ' ,;';
        $ext = strtok($fileExtensions, $token);

        while ($ext !== false) {
            $this->allowedFileExtensions[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * Sets a list of exclude directories
     *
     * @param string $excludeDirectories List of exclude directories
     */
    public function setExcludeDirectories($excludeDirectories)
    {
        $this->excludeDirectories = [];

        $token = ' ,;';
        $pattern = strtok($excludeDirectories, $token);

        while ($pattern !== false) {
            $this->excludeDirectories[] = $pattern;
            $pattern = strtok($token);
        }
    }

    /**
     * Sets a list of exclude packages
     *
     * @param string $excludePackages Exclude packages
     */
    public function setExcludePackages($excludePackages)
    {
        $this->excludePackages = [];

        $token = ' ,;';
        $pattern = strtok($excludePackages, $token);

        while ($pattern !== false) {
            $this->excludePackages[] = $pattern;
            $pattern = strtok($token);
        }
    }

    /**
     * Should the parser ignore doc comment annotations?
     *
     * @param boolean $withoutAnnotations
     */
    public function setWithoutAnnotations($withoutAnnotations)
    {
        $this->withoutAnnotations = StringHelper::booleanValue($withoutAnnotations);
    }

    /**
     * Should PHP_Depend support projects with a bad documentation. If this
     * option is set to <b>true</b>, PHP_Depend will treat the default package
     * <b>+global</b> as a regular project package.
     *
     * @param boolean $supportBadDocumentation
     */
    public function setSupportBadDocumentation($supportBadDocumentation)
    {
        $this->supportBadDocumentation = StringHelper::booleanValue($supportBadDocumentation);
    }

    /**
     * Set debugging On/Off
     *
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = StringHelper::booleanValue($debug);
    }

    /**
     * Set halt on error
     *
     * @param boolean $haltonerror
     */
    public function setHaltonerror($haltonerror)
    {
        $this->haltonerror = StringHelper::booleanValue($haltonerror);
    }

    /**
     * Set the configuration file
     *
     * @param File $configFile The configuration file
     */
    public function setConfigFile(File $configFile)
    {
        $this->configFile = $configFile;
    }

    /**
     * Create object for nested logger element
     *
     * @return PhpDependLoggerElement
     */
    public function createLogger()
    {
        $num = array_push($this->loggers, new PhpDependLoggerElement());

        return $this->loggers[$num - 1];
    }

    /**
     * Create object for nested analyzer element
     *
     * @return PhpDependAnalyzerElement
     */
    public function createAnalyzer()
    {
        $num = array_push($this->analyzers, new PhpDependAnalyzerElement());

        return $this->analyzers[$num - 1];
    }

    /**
     * @param string $pharLocation
     */
    public function setPharLocation($pharLocation)
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * Executes PHP_Depend_TextUI_Runner against PhingFile or a FileSet
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->requireDependencies();

        if (!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException('Missing either a nested fileset or attribute "file" set');
        }

        if (count($this->loggers) == 0) {
            throw new BuildException('Missing nested "logger" element');
        }

        $this->validateLoggers();
        $this->validateAnalyzers();

        $filesToParse = $this->getFilesToParse();

        $runner = $this->createRunner();
        $runner->setSourceArguments($filesToParse);

        foreach ($this->loggers as $logger) {
            // Register logger
            if ($this->oldVersion) {
                $runner->addLogger(
                    $logger->getType(),
                    $logger->getOutfile()->__toString()
                );
            } else {
                $runner->addReportGenerator(
                    $logger->getType(),
                    $logger->getOutfile()->__toString()
                );
            }
        }

        foreach ($this->analyzers as $analyzer) {
            // Register additional analyzer
            $runner->addOption(
                $analyzer->getType(),
                $analyzer->getValue()
            );
        }

        // Disable annotation parsing
        if ($this->withoutAnnotations) {
            $runner->setWithoutAnnotations();
        }

        // Enable bad documentation support
        if ($this->supportBadDocumentation) {
            $runner->setSupportBadDocumentation();
        }

        // Check for suffix
        if (count($this->allowedFileExtensions) > 0) {
            $runner->setFileExtensions($this->allowedFileExtensions);
        }

        // Check for ignore directories
        if (count($this->excludeDirectories) > 0) {
            $runner->setExcludeDirectories($this->excludeDirectories);
        }

        // Check for exclude packages
        if (count($this->excludePackages) > 0) {
            $runner->setExcludePackages($this->excludePackages);
        }

        $runner->run();

        if ($runner->hasParseErrors() === true) {
            $this->log('Following errors occurred:');

            foreach ($runner->getParseErrors() as $error) {
                $this->log($error);
            }

            if ($this->haltonerror === true) {
                throw new BuildException('Errors occurred during parse process');
            }
        }
    }

    /**
     * Validates the available loggers
     *
     * @throws BuildException
     */
    protected function validateLoggers()
    {
        foreach ($this->loggers as $logger) {
            if ($logger->getType() === '') {
                throw new BuildException('Logger missing required "type" attribute');
            }

            if ($logger->getOutfile() === null) {
                throw new BuildException('Logger requires "outfile" attribute');
            }
        }
    }

    /**
     * Validates the available analyzers
     *
     * @throws BuildException
     */
    protected function validateAnalyzers()
    {
        foreach ($this->analyzers as $analyzer) {
            if ($analyzer->getType() === '') {
                throw new BuildException('Analyzer missing required "type" attribute');
            }

            if (count($analyzer->getValue()) === 0) {
                throw new BuildException('Analyzer missing required "value" attribute');
            }
        }
    }

    /**
     * @return array
     */
    private function getFilesToParse()
    {
        $filesToParse = [];

        if ($this->file instanceof File) {
            $filesToParse[] = $this->file->__toString();
            return $filesToParse;
        }

        // append any files in filesets
        foreach ($this->filesets as $fs) {
            $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();

            foreach ($files as $filename) {
                $f = new File($fs->getDir($this->project), $filename);
                $filesToParse[] = $f->getAbsolutePath();
            }
        }
        return $filesToParse;
    }

    /**
     * @return object
     */
    private function createRunner()
    {
        $application = new Application();

        if (!empty($this->configFile)) {
            if (file_exists($this->configFile->__toString()) === false) {
                throw new BuildException(
                    'The configuration file "' . $this->configFile->__toString() . '" doesn\'t exist.'
                );
            }

            $application->setConfigurationFile($this->configFile);
        }

        $runner = $application->getRunner();

        if ($this->debug) {
            // Enable debug logging
            Log::setSeverity(1);
        }

        return $runner;
    }
}
