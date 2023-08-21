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

namespace Phing\Task\Ext\PhpUnit;

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\FileWriter;
use Phing\Io\LogWriter;
use Phing\Io\File;
use Phing\Task;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use ReflectionException;
use ReflectionClass;

/**
 * Runs PHPUnit tests.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phpunit
 * @see     BatchTest
 * @since   2.1.0
 */
class PHPUnitTask extends Task
{
    private $batchtests = [];
    /**
     * @var FormatterElement[] $formatters
     */
    private $formatters = [];
    private $bootstrap = "";
    private $haltonerror = false;
    private $haltonfailure = false;
    private $haltonincomplete = false;
    private $haltonskipped = false;
    private $haltondefect = false;
    private $haltonwarning = false;
    private $haltonrisky = false;
    private $errorproperty;
    private $failureproperty;
    private $incompleteproperty;
    private $skippedproperty;
    private $warningproperty;
    private $riskyproperty;
    private $printsummary = false;
    private $testfailed = false;
    private $testfailuremessage = "";
    private $codecoverage = null;
    private $groups = [];
    private $excludeGroups = [];
    private $processIsolation = false;
    private $usecustomerrorhandler = true;
    private $listeners = [];

    /**
     * @var string
     */
    private $pharLocation = "";

    /**
     * @var File
     */
    private $configuration = null;

    /**
     * @var \PHPUnit\TextUI\XmlConfiguration\CodeCoverage\CodeCoverage
     */
    private $codeCoverageConfig = null;

    /**
     * Initialize Task.
     * This method includes any necessary PHPUnit libraries and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     */
    public function init()
    {
    }

    private function loadPHPUnit()
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
            @include $this->pharLocation;
            ob_end_clean();

            if ($composerAutoloader !== null) {
                spl_autoload_register($composerAutoloader);
            }
        }

        if (!class_exists('PHPUnit\Runner\Version')) {
            throw new BuildException("PHPUnitTask requires PHPUnit to be installed", $this->getLocation());
        }

        if (
            class_exists('\PHPUnit\Runner\Version', false) &&
            version_compare(\PHPUnit\Runner\Version::id(), '9.0.0', '<')
        ) {
            throw new BuildException("Phing only supports PHPUnit 9+");
        }
    }

    /**
     * Sets the name of a bootstrap file that is run before
     * executing the tests
     *
     * @param string $bootstrap the name of the bootstrap file
     */
    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @param $value
     */
    public function setErrorproperty($value)
    {
        $this->errorproperty = $value;
    }

    /**
     * @param $value
     */
    public function setFailureproperty($value)
    {
        $this->failureproperty = $value;
    }

    /**
     * @param $value
     */
    public function setIncompleteproperty($value)
    {
        $this->incompleteproperty = $value;
    }

    /**
     * @param $value
     */
    public function setSkippedproperty($value)
    {
        $this->skippedproperty = $value;
    }

    /**
     * @param $value
     */
    public function setRiskyproperty($value)
    {
        $this->riskyproperty = $value;
    }

    /**
     * @param $value
     */
    public function setWarningproperty($value)
    {
        $this->riskyproperty = $value;
    }

    /**
     * @return bool
     */
    public function getHaltondefect(): bool
    {
        return $this->haltondefect;
    }

    /**
     * @param bool $haltondefect
     */
    public function setHaltondefect(bool $haltondefect): void
    {
        $this->haltondefect = $haltondefect;
    }

    /**
     * @return bool
     */
    public function getHaltonwarning(): bool
    {
        return $this->haltonwarning;
    }

    /**
     * @param bool $haltonwarning
     */
    public function setHaltonwarning(bool $haltonwarning): void
    {
        $this->haltonwarning = $haltonwarning;
    }

    /**
     * @return bool
     */
    public function getHaltonrisky(): bool
    {
        return $this->haltonrisky;
    }

    /**
     * @param bool $haltonrisky
     */
    public function setHaltonrisky(bool $haltonrisky): void
    {
        $this->haltonrisky = $haltonrisky;
    }

    /**
     * @param $value
     */
    public function setHaltonerror($value)
    {
        $this->haltonerror = $value;
    }

    /**
     * @param $value
     */
    public function setHaltonfailure($value)
    {
        $this->haltonfailure = $value;
    }

    /**
     * @return bool
     */
    public function getHaltonfailure()
    {
        return $this->haltonfailure;
    }

    /**
     * @param $value
     */
    public function setHaltonincomplete($value)
    {
        $this->haltonincomplete = $value;
    }

    /**
     * @return bool
     */
    public function getHaltonincomplete()
    {
        return $this->haltonincomplete;
    }

    /**
     * @param $value
     */
    public function setHaltonskipped($value)
    {
        $this->haltonskipped = $value;
    }

    /**
     * @return bool
     */
    public function getHaltonskipped()
    {
        return $this->haltonskipped;
    }

    /**
     * @param $printsummary
     */
    public function setPrintsummary($printsummary)
    {
        $this->printsummary = $printsummary;
    }

    /**
     * @param $codecoverage
     */
    public function setCodecoverage($codecoverage)
    {
        $this->codecoverage = $codecoverage;
    }

    /**
     * @param $processIsolation
     */
    public function setProcessIsolation($processIsolation)
    {
        $this->processIsolation = $processIsolation;
    }

    /**
     * @param $usecustomerrorhandler
     */
    public function setUseCustomErrorHandler($usecustomerrorhandler)
    {
        $this->usecustomerrorhandler = $usecustomerrorhandler;
    }

    /**
     * @param $groups
     */
    public function setGroups($groups)
    {
        $token = ' ,;';
        $this->groups = [];
        $tok = strtok($groups, $token);
        while ($tok !== false) {
            $this->groups[] = $tok;
            $tok = strtok($token);
        }
    }

    /**
     * @param $excludeGroups
     */
    public function setExcludeGroups($excludeGroups)
    {
        $token = ' ,;';
        $this->excludeGroups = [];
        $tok = strtok($excludeGroups, $token);
        while ($tok !== false) {
            $this->excludeGroups[] = $tok;
            $tok = strtok($token);
        }
    }

    /**
     * Add a new formatter to all tests of this task.
     *
     * @param FormatterElement $fe formatter element
     */
    public function addFormatter(FormatterElement $fe)
    {
        $fe->setParent($this);
        $this->formatters[] = $fe;
    }

    /**
     * Add a new listener to all tests of this taks
     *
     * @param $listener
     */
    private function addListener($listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param File $configuration
     */
    public function setConfiguration(File $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $pharLocation
     */
    public function setPharLocation($pharLocation)
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * Load and processes the PHPUnit configuration
     *
     * @param  $configuration
     * @return mixed
     * @throws ReflectionException
     * @throws BuildException
     */
    protected function handlePHPUnitConfiguration(File $configuration)
    {
        if (!$configuration->exists()) {
            throw new BuildException("Unable to find PHPUnit configuration file '" . (string) $configuration . "'");
        }

        $config = (new Loader())->load($configuration->getAbsolutePath());
        $phpunit = $config->phpunit();

        if ($phpunit->hasBootstrap()) {
            $this->setBootstrap($phpunit->bootstrap());
        }
        $this->setHaltonfailure($phpunit->stopOnFailure());
        $this->setHaltonerror($phpunit->stopOnError());
        $this->setHaltonskipped($phpunit->stopOnSkipped());
        $this->setHaltonincomplete($phpunit->stopOnIncomplete());
        $this->setHaltondefect($phpunit->stopOnDefect());
        $this->setHaltonwarning($phpunit->stopOnWarning());
        $this->setHaltonrisky($phpunit->stopOnRisky());
        $this->setProcessIsolation($phpunit->processIsolation());

        foreach ($config->listeners() as $listener) {
            if (
                !class_exists($listener->className(), false)
                && $listener->hasSourceFile()
            ) {
                include_once $listener->sourceFile();
            }

            if (class_exists($listener->className())) {
                if ($listener->hasArguments()) {
                    $listener = (new $listener->className())();
                } else {
                    $listenerClass = new ReflectionClass(
                        $listener->className()
                    );
                    $listener = $listenerClass->newInstanceArgs(
                        $listener->arguments()
                    );
                }

                if ($listener instanceof \PHPUnit\Framework\TestListener) {
                    $this->addListener($listener);
                }
            }
        }

        $this->codeCoverageConfig = $config->codeCoverage();
        return $phpunit;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        if ($this->codecoverage && !extension_loaded('xdebug')) {
            throw new BuildException("PHPUnitTask depends on Xdebug being installed to gather code coverage information.");
        }

        $this->loadPHPUnit();
        $suite = new \PHPUnit\Framework\TestSuite('AllTests');
        $autoloadSave = spl_autoload_functions();

        if ($this->bootstrap) {
            include $this->bootstrap;
        }

        if ($this->configuration) {
            $phpunit = $this->handlePHPUnitConfiguration($this->configuration);

            if ($phpunit->backupGlobals() === false) {
                $suite->setBackupGlobals(false);
            }

            if ($phpunit->backupStaticAttributes() === true) {
                $suite->setBackupStaticAttributes(true);
            }
        }

        if ($this->printsummary) {
            $fe = new FormatterElement();
            $fe->setParent($this);
            $fe->setType("summary");
            $fe->setUseFile(false);
            $this->formatters[] = $fe;
        }

        foreach ($this->batchtests as $batchTest) {
            $this->appendBatchTestToTestSuite($batchTest, $suite);
        }

        $this->execute($suite);

        if ($this->testfailed) {
            throw new BuildException("Test(s) failed: " . $this->testfailuremessage);
        }

        $autoloadNew = spl_autoload_functions();
        if (is_array($autoloadNew)) {
            foreach ($autoloadNew as $autoload) {
                spl_autoload_unregister($autoload);
            }
        }

        if (is_array($autoloadSave)) {
            foreach ($autoloadSave as $autoload) {
                spl_autoload_register($autoload);
            }
        }
    }

    /**
     * @param $suite
     * @throws BuildException
     * @throws ReflectionException
     */
    protected function execute($suite)
    {
        $runner = new PHPUnitTestRunner9(
            $this->project,
            $this->groups,
            $this->excludeGroups,
            $this->processIsolation
        );

        if ($this->codecoverage) {
            /**
             * Add some defaults to the PHPUnit filter
             */
            $pwd = __DIR__;
            $path = realpath($pwd . '/../../../');

            if (class_exists('\SebastianBergmann\CodeCoverage\Filter')) {
                $filter = new \SebastianBergmann\CodeCoverage\Filter();
                if (method_exists($filter, 'addDirectoryToBlacklist')) {
                    $filter->addDirectoryToBlacklist($path);
                }
                if (class_exists('\SebastianBergmann\CodeCoverage\CodeCoverage')) {
                    if (null !== $this->codeCoverageConfig) {
                        // Update filters
                        foreach ($this->codeCoverageConfig->files()->asArray() as $file) {
                            $filter->includeFile($file->path());
                        }
                        foreach ($this->codeCoverageConfig->directories()->asArray() as $dir) {
                            $filter->includeDirectory($dir->path(), $dir->suffix(), $dir->prefix());
                        }
                        foreach ($this->codeCoverageConfig->excludeFiles()->asArray() as $file) {
                            $filter->excludeFile($file->path());
                        }
                        foreach ($this->codeCoverageConfig->excludeDirectories()->asArray() as $dir) {
                            $filter->excludeDirectory($dir->path(), $dir->suffix(), $dir->prefix());
                        }
                    }

                    if (null !== $this->codeCoverageConfig && $this->codeCoverageConfig->pathCoverage()) {
                        $driver = (new \SebastianBergmann\CodeCoverage\Driver\Selector())->forLineAndPathCoverage($filter);
                    } else {
                        $driver = (new \SebastianBergmann\CodeCoverage\Driver\Selector())->forLineCoverage($filter);
                    }

                    $driver = (new \SebastianBergmann\CodeCoverage\Driver\Selector())->forLineCoverage($filter);
                    $codeCoverage = new \SebastianBergmann\CodeCoverage\CodeCoverage($driver, $filter);

                    if (null !== $this->codeCoverageConfig) {
                        // Set code coverage configuration
                        if ($this->codeCoverageConfig->hasCacheDirectory()) {
                            $codeCoverage->cacheStaticAnalysis($this->codeCoverageConfig->cacheDirectory()->path());
                        }
                        if ($this->codeCoverageConfig->ignoreDeprecatedCodeUnits()) {
                            $codeCoverage->ignoreDeprecatedCode();
                        } else {
                            $codeCoverage->doNotIgnoreDeprecatedCode();
                        }
                        if ($this->codeCoverageConfig->includeUncoveredFiles()) {
                            $codeCoverage->includeUncoveredFiles();
                        } else {
                            $codeCoverage->doNotProcessUncoveredFiles();
                        }
                    }

                    $runner->setCodecoverage($codeCoverage);
                }
            }
        }

        $runner->setUseCustomErrorHandler($this->usecustomerrorhandler);

        foreach ($this->listeners as $listener) {
            $runner->addListener($listener);
        }

        foreach ($this->formatters as $fe) {
            $formatter = $fe->getFormatter();

            if ($fe->getUseFile()) {
                try {
                    $destFile = new File($fe->getToDir(), $fe->getOutfile());
                } catch (Exception $e) {
                    throw new BuildException('Unable to create destination.', $e);
                }

                $writer = new FileWriter($destFile->getAbsolutePath());

                $formatter->setOutput($writer);
            } else {
                $formatter->setOutput($this->getDefaultOutput());
            }

            $runner->addFormatter($formatter);

            $formatter->startTestRun();
        }

        $runner->run($suite);

        foreach ($this->formatters as $fe) {
            $formatter = $fe->getFormatter();
            $formatter->endTestRun();
        }

        if ($runner->hasErrors()) {
            if ($this->errorproperty) {
                $this->project->setNewProperty($this->errorproperty, true);
            }
            if ($this->haltonerror || $this->haltondefect) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastErrorMessage();
            }
        }

        if ($runner->hasFailures()) {
            if ($this->failureproperty) {
                $this->project->setNewProperty($this->failureproperty, true);
            }

            if ($this->haltonfailure || $this->haltondefect) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastFailureMessage();
            }
        }

        if ($runner->hasIncomplete()) {
            if ($this->incompleteproperty) {
                $this->project->setNewProperty($this->incompleteproperty, true);
            }

            if ($this->haltonincomplete) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastIncompleteMessage();
            }
        }

        if ($runner->hasSkipped()) {
            if ($this->skippedproperty) {
                $this->project->setNewProperty($this->skippedproperty, true);
            }

            if ($this->haltonskipped) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastSkippedMessage();
            }
        }

        if ($runner->hasWarnings()) {
            if ($this->warningproperty) {
                $this->project->setNewProperty($this->warningproperty, true);
            }

            if ($this->haltonwarning || $this->haltondefect) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastWarningMessage();
            }
        }

        if ($runner->hasRisky()) {
            if ($this->riskyproperty) {
                $this->project->setNewProperty($this->riskyproperty, true);
            }

            if ($this->haltonrisky) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastRiskyMessage();
            }
        }
    }

    /**
     * Add the tests in this batchtest to a test suite
     *
     * @param BatchTest $batchTest
     * @param TestSuite $suite
     * @throws BuildException
     * @throws ReflectionException
     */
    protected function appendBatchTestToTestSuite(BatchTest $batchTest, $suite)
    {
        foreach ($batchTest->elements() as $element) {
            $testClass = new $element();
            if (!($testClass instanceof TestSuite)) {
                $testClass = new ReflectionClass($element);
            }
            try {
                $suite->addTestSuite($testClass);
            } catch (\PHPUnit\Framework\Exception $e) {
                throw new BuildException('Unable to add TestSuite ' . get_class($testClass), $e);
            }
        }
    }

    /**
     * @return LogWriter
     */
    protected function getDefaultOutput()
    {
        return new LogWriter($this);
    }

    /**
     * Adds a set of tests based on pattern matching.
     *
     * @return BatchTest a new instance of a batch test.
     */
    public function createBatchTest()
    {
        $batchtest = new BatchTest($this->getProject());

        $this->batchtests[] = $batchtest;

        return $batchtest;
    }
}
