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

namespace Phing;

use Exception;
use Phing\Exception\BuildException;
use Phing\Exception\ConfigurationException;
use Phing\Exception\ExitStatusException;
use Phing\Input\ConsoleInputHandler;
use Phing\Io\File;
use Phing\Io\FileOutputStream;
use Phing\Io\FileParserFactory;
use Phing\Io\FileReader;
use Phing\Io\FileSystem;
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Io\OutputStream;
use Phing\Io\PrintStream;
use Phing\Listener\BuildLogger;
use Phing\Listener\DefaultLogger;
use Phing\Listener\SilentLogger;
use Phing\Listener\StreamRequiredBuildLogger;
use Phing\Parser\ProjectConfigurator;
use Phing\Util\DefaultClock;
use Phing\Util\Diagnostics;
use Phing\Util\Properties;
use Phing\Util\SizeHelper;
use Phing\Util\StringHelper;
use SebastianBergmann\Version;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

use function array_filter;
use function array_map;
use function array_reduce;
use function implode;
use function method_exists;
use function sprintf;
use function strlen;
use function strval;
use function trim;

use const PHP_EOL;

/**
 * Entry point into Phing.  This class handles the full lifecycle of a build -- from
 * parsing & handling commandline arguments to assembling the project to shutting down
 * and cleaning up in the end.
 *
 * If you are invoking Phing from an external application, this is still
 * the class to use.  Your application can invoke the start() method, passing
 * any commandline arguments or additional properties.
 *
 * @author Andreas Aderhold <andi@binarycloud.com>
 * @author Hans Lellelid <hans@xmpl.org>
 */
class Phing
{
    /**
     * Alias for phar file.
     */
    public const PHAR_ALIAS = 'phing.phar';

    /**
     * The default build file name.
     */
    public const DEFAULT_BUILD_FILENAME = 'build.xml';
    public const DEFAULT_BUILD_CONTENT = <<<'XML'
        <?xml version="1.0" encoding="UTF-8" ?>

        <project name="" description="" default="">
            
            <target name="" description="">
                
            </target>
            
        </project>
        XML;
    public const PHING_HOME = 'phing.home';
    public const PHP_VERSION = 'php.version';
    public const PHP_INTERPRETER = 'php.interpreter';

    /**
     * Our current message output status. Follows Project::MSG_XXX.
     */
    private static $msgOutputLevel = Project::MSG_INFO;
    /**
     * Set of properties that are passed in from commandline or invoking code.
     *
     * @var Properties
     */
    private static $definedProps;
    /**
     * Used by utility function getResourcePath().
     */
    private static $importPaths;
    /**
     * System-wide static properties (moved from System).
     */
    private static $properties = [];
    /**
     * Static system timer.
     */
    private static $timer;
    /**
     * The current Project.
     */
    private static $currentProject;
    /**
     * Whether to capture PHP errors to buffer.
     */
    private static $phpErrorCapture = false;
    /**
     * Array of captured PHP errors.
     */
    private static $capturedPhpErrors = [];
    /**
     * @var OUtputStream stream for standard output
     */
    private static $out;
    /**
     * @var OutputStream stream for error output
     */
    private static $err;
    /**
     * @var bool whether we are using a logfile
     */
    private static $isLogFileUsed = false;
    /**
     * Array to hold original ini settings that Phing changes (and needs
     * to restore in restoreIni() method).
     *
     * @var array Struct of array(setting-name => setting-value)
     *
     * @see restoreIni()
     */
    private static $origIniSettings = [];
    /**
     * PhingFile that we are using for configuration.
     */
    private $buildFile;
    /**
     * The build targets.
     */
    private $targets = [];
    /**
     * Names of classes to add as listeners to project.
     */
    private $listeners = [];
    /**
     * keep going mode.
     *
     * @var bool
     */
    private $keepGoingMode = false;
    private $loggerClassname;
    /**
     * The class to handle input (can be only one).
     */
    private $inputHandlerClassname;
    /**
     * Whether or not log output should be reduced to the minimum.
     *
     * @var bool
     */
    private $silent = false;
    /**
     * Indicates whether phing should run in strict mode.
     */
    private $strictMode = false;
    /**
     * Indicates if this phing should be run.
     */
    private $readyToRun = false;
    /**
     * Indicates we should only parse and display the project help information.
     */
    private $projectHelp = false;
    /**
     * Whether to values in a property file should override existing values.
     */
    private $propertyFileOverride = false;
    /**
     * Whether or not output to the log is to be unadorned.
     */
    private $emacsMode = false;

    /**
     * @var string
     */
    private $searchForThis;
    private $propertyFiles = [];

    /**
     * Gets the stream to use for standard (non-error) output.
     *
     * @return OutputStream
     */
    public static function getOutputStream()
    {
        return self::$out;
    }

    /**
     * Gets the stream to use for error output.
     *
     * @return OutputStream
     */
    public static function getErrorStream()
    {
        return self::$err;
    }

    /**
     * Command line entry point. This method kicks off the building
     * of a project object and executes a build using either a given
     * target or the default target.
     *
     * @param array $args command line args
     *
     * @throws Exception
     */
    public static function fire($args): void
    {
        self::start($args);
    }

    /**
     * Entry point allowing for more options from other front ends.
     *
     * This method encapsulates the complete build lifecycle.
     *
     * @param array $args                     the commandline args passed to phing shell script
     * @param array $additionalUserProperties Any additional properties to be passed to Phing (alternative front-end might implement this).
     *                                        These additional properties will be available using the getDefinedProperty() method and will
     *                                        be added to the project's "user" properties
     *
     * @throws Exception - if there is an error during build
     *
     * @see    runBuild()
     * @see    execute()
     */
    public static function start($args, array $additionalUserProperties = null)
    {
        try {
            $m = new self();
            $m->execute($args);
        } catch (Exception $exc) {
            self::handleLogfile();
            self::printMessage($exc);
            self::statusExit(1);

            return;
        }

        if (null !== $additionalUserProperties) {
            foreach ($additionalUserProperties as $key => $value) {
                $m::setDefinedProperty($key, $value);
            }
        }

        // expect the worst
        $exitCode = 1;

        try {
            try {
                $m->runBuild();
                $exitCode = 0;
            } catch (ExitStatusException $ese) {
                $exitCode = $ese->getCode();
                if (0 !== $exitCode) {
                    self::handleLogfile();

                    throw $ese;
                }
            }
        } catch (BuildException $exc) {
            // avoid printing output twice: self::printMessage($exc);
        } catch (Throwable $exc) {
            self::printMessage($exc);
        } finally {
            self::handleLogfile();
        }
        self::statusExit($exitCode);
    }

    /**
     * Setup/initialize Phing environment from commandline args.
     *
     * @param array $args commandline args passed to phing shell
     *
     * @throws ConfigurationException
     */
    public function execute($args): void
    {
        self::$definedProps = new Properties();
        $this->searchForThis = null;

        // 1) First handle any options which should always
        // Note: The order in which these are executed is important (if multiple of these options are specified)

        if (in_array('-help', $args) || in_array('-h', $args)) {
            static::printUsage();

            return;
        }

        if (in_array('-version', $args) || in_array('-v', $args)) {
            static::printVersion();

            return;
        }

        if (in_array('-init', $args) || in_array('-i', $args)) {
            $key = array_search('-init', $args) ?: array_search('-i', $args);
            $path = $args[$key + 1] ?? null;

            self::init($path);

            return;
        }

        if (in_array('-diagnostics', $args)) {
            Diagnostics::doReport(new PrintStream(self::$out));

            return;
        }

        // 2) Next pull out stand-alone args.
        // Note: The order in which these are executed is important (if multiple of these options are specified)

        if (
            false !== ($key = array_search('-quiet', $args, true))
            || false !== ($key = array_search(
                '-q',
                $args,
                true
            ))
        ) {
            self::$msgOutputLevel = Project::MSG_WARN;
            unset($args[$key]);
        }

        if (
            false !== ($key = array_search('-emacs', $args, true))
            || false !== ($key = array_search('-e', $args, true))
        ) {
            $this->emacsMode = true;
            unset($args[$key]);
        }

        if (false !== ($key = array_search('-verbose', $args, true))) {
            self::$msgOutputLevel = Project::MSG_VERBOSE;
            unset($args[$key]);
        }

        if (false !== ($key = array_search('-debug', $args, true))) {
            self::$msgOutputLevel = Project::MSG_DEBUG;
            unset($args[$key]);
        }

        if (
            false !== ($key = array_search('-silent', $args, true))
            || false !== ($key = array_search('-S', $args, true))
        ) {
            $this->silent = true;
            unset($args[$key]);
        }

        if (false !== ($key = array_search('-propertyfileoverride', $args, true))) {
            $this->propertyFileOverride = true;
            unset($args[$key]);
        }

        // 3) Finally, cycle through to parse remaining args
        //
        $keys = array_keys($args); // Use keys and iterate to max(keys) since there may be some gaps
        $max = $keys ? max($keys) : -1;
        for ($i = 0; $i <= $max; ++$i) {
            if (!array_key_exists($i, $args)) {
                // skip this argument, since it must have been removed above.
                continue;
            }

            $arg = $args[$i];

            if ('-logfile' == $arg) {
                try {
                    // see: http://phing.info/trac/ticket/65
                    if (!isset($args[$i + 1])) {
                        $msg = "You must specify a log file when using the -logfile argument\n";

                        throw new ConfigurationException($msg);
                    }

                    $logFile = new File($args[++$i]);
                    $out = new FileOutputStream($logFile); // overwrite
                    self::setOutputStream($out);
                    self::setErrorStream($out);
                    self::$isLogFileUsed = true;
                } catch (IOException $ioe) {
                    $msg = 'Cannot write on the specified log file. Make sure the path exists and you have write permissions.';

                    throw new ConfigurationException($msg, $ioe);
                }
            } elseif ('-buildfile' == $arg || '-file' == $arg || '-f' == $arg) {
                if (!isset($args[$i + 1])) {
                    $msg = 'You must specify a buildfile when using the -buildfile argument.';

                    throw new ConfigurationException($msg);
                }

                $this->buildFile = new File($args[++$i]);
            } elseif ('-listener' == $arg) {
                if (!isset($args[$i + 1])) {
                    $msg = 'You must specify a listener class when using the -listener argument';

                    throw new ConfigurationException($msg);
                }

                $this->listeners[] = $args[++$i];
            } elseif (StringHelper::startsWith('-D', $arg)) {
                // Evaluating the property information //
                // Checking whether arg. is not just a switch, and next arg. does not starts with switch identifier
                if (('-D' == $arg) && (!StringHelper::startsWith('-', $args[$i + 1]))) {
                    $name = $args[++$i];
                } else {
                    $name = substr($arg, 2);
                }

                $value = null;
                $posEq = strpos($name, '=');
                if (false !== $posEq) {
                    $value = substr($name, $posEq + 1);
                    $name = substr($name, 0, $posEq);
                } elseif ($i < count($args) - 1 && !StringHelper::startsWith('-D', $args[$i + 1])) {
                    $value = $args[++$i];
                }
                self::$definedProps->setProperty($name, $value);
            } elseif ('-logger' == $arg) {
                if (!isset($args[$i + 1])) {
                    $msg = 'You must specify a classname when using the -logger argument';

                    throw new ConfigurationException($msg);
                }

                $this->loggerClassname = $args[++$i];
            } elseif ('-no-strict' == $arg) {
                $this->strictMode = false;
            } elseif ('-strict' == $arg) {
                $this->strictMode = true;
            } elseif ('-inputhandler' == $arg) {
                if (null !== $this->inputHandlerClassname) {
                    throw new ConfigurationException('Only one input handler class may be specified.');
                }
                if (!isset($args[$i + 1])) {
                    $msg = 'You must specify a classname when using the -inputhandler argument';

                    throw new ConfigurationException($msg);
                }

                $this->inputHandlerClassname = $args[++$i];
            } elseif ('-propertyfile' === $arg) {
                $i = $this->handleArgPropertyFile($args, $i);
            } elseif ('-keep-going' === $arg || '-k' === $arg) {
                $this->keepGoingMode = true;
            } elseif ('-longtargets' == $arg) {
                self::$definedProps->setProperty('phing.showlongtargets', 1);
            } elseif ('-projecthelp' == $arg || '-targets' == $arg || '-list' == $arg || '-l' == $arg || '-p' == $arg) {
                // set the flag to display the targets and quit
                $this->projectHelp = true;
            } elseif ('-find' == $arg) {
                // eat up next arg if present, default to build.xml
                if ($i < count($args) - 1) {
                    $this->searchForThis = $args[++$i];
                } else {
                    $this->searchForThis = self::DEFAULT_BUILD_FILENAME;
                }
            } elseif ('-' == substr($arg, 0, 1)) {
                // we don't have any more args
                self::printUsage();
                self::$err->write(PHP_EOL);

                throw new ConfigurationException('Unknown argument: ' . $arg);
            } else {
                // if it's no other arg, it may be the target
                $this->targets[] = $arg;
            }
        }

        // if buildFile was not specified on the command line,
        if (null === $this->buildFile) {
            // but -find then search for it
            if (null !== $this->searchForThis) {
                $this->buildFile = $this->findBuildFile(self::getProperty('user.dir'), $this->searchForThis);
            } else {
                $this->buildFile = new File(self::DEFAULT_BUILD_FILENAME);
            }
        }

        try {
            // make sure buildfile (or buildfile.dist) exists
            if (!$this->buildFile->exists()) {
                $distFile = new File($this->buildFile->getAbsolutePath() . '.dist');
                if (!$distFile->exists()) {
                    throw new ConfigurationException(
                        'Buildfile: ' . $this->buildFile->__toString() . ' does not exist!'
                    );
                }
                $this->buildFile = $distFile;
            }

            // make sure it's not a directory
            if ($this->buildFile->isDirectory()) {
                throw new ConfigurationException('Buildfile: ' . $this->buildFile->__toString() . ' is a dir!');
            }
        } catch (IOException $e) {
            // something else happened, buildfile probably not readable
            throw new ConfigurationException('Buildfile: ' . $this->buildFile->__toString() . ' is not readable!');
        }

        $this->loadPropertyFiles();

        $this->readyToRun = true;
    }

    /**
     * Prints the usage of how to use this class.
     */
    public static function printUsage()
    {
        $msg = <<<'TEXT'
            phing [options] [target [target2 [target3] ...]]
            Options:
              -h -help                 print this message
              -l -list                 list available targets in this project
              -i -init [file]          generates an initial buildfile
              -v -version              print the version information and exit
              -q -quiet                be extra quiet
              -S -silent               print nothing but task outputs and build failures
                 -verbose              be extra verbose
                 -debug                print debugging information
              -e -emacs                produce logging information without adornments
                 -diagnostics          print diagnostics information
                 -strict               runs build in strict mode, considering a warning as error
                 -no-strict            runs build normally (overrides buildfile attribute)
                 -longtargets          show target descriptions during build
                 -logfile <file>       use given file for log
                 -logger <classname>   the class which is to perform logging
                 -listener <classname> add an instance of class as a project listener
              -f -buildfile <file>     use given buildfile
                 -D<property>=<value>  use value for given property
              -k -keep-going           execute all targets that do not depend on failed target(s)
                 -propertyfile <file>  load all properties from file
                 -propertyfileoverride values in property file override existing values
                 -find <file>          search for buildfile towards the root of the filesystem and use it
                 -inputhandler <file>  the class to use to handle user input

            Report bugs to https://github.com/phingofficial/phing/issues

            TEXT;

        self::$err->write($msg);
    }

    /**
     * Prints the current Phing version.
     */
    public static function printVersion()
    {
        self::$out->write(self::getPhingVersion() . PHP_EOL);
    }

    /**
     * Gets the current Phing version based on VERSION.TXT file.
     *
     * @throws ConfigurationException
     */
    public static function getPhingVersion(): string
    {
        $versionPath = self::getResourcePath('phing/etc/VERSION.TXT');
        if (null === $versionPath) {
            $versionPath = self::getResourcePath('etc/VERSION.TXT');
        }
        if (null === $versionPath) {
            throw new ConfigurationException('No VERSION.TXT file found; try setting phing.home environment variable.');
        }

        try { // try to read file
            $file = new File($versionPath);
            $reader = new FileReader($file);
            $phingVersion = trim($reader->read());
        } catch (IOException $iox) {
            throw new ConfigurationException("Can't read version information file");
        }

        $basePath = dirname(__DIR__, 2);

        $version = new Version($phingVersion, $basePath);

        return 'Phing ' . (method_exists($version, 'asString') ? $version->asString() : $version->getVersion());
    }

    /**
     * Looks on include path for specified file.
     *
     * @param string $path
     *
     * @return null|string file found (null if no file found)
     */
    public static function getResourcePath($path): ?string
    {
        if (null === self::$importPaths) {
            self::$importPaths = self::explodeIncludePath();
        }

        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

        foreach (self::$importPaths as $prefix) {
            $testPath = $prefix . DIRECTORY_SEPARATOR . $path;
            if (file_exists($testPath)) {
                return $testPath;
            }
        }

        // Check for the property phing.home
        $homeDir = self::getProperty(self::PHING_HOME);
        if ($homeDir) {
            $testPath = $homeDir . DIRECTORY_SEPARATOR . $path;
            if (file_exists($testPath)) {
                return $testPath;
            }
        }

        // Check for the phing home of phar archive
        if (0 === strpos(self::$importPaths[0], 'phar://')) {
            $testPath = self::$importPaths[0] . '/../' . $path;
            if (file_exists($testPath)) {
                return $testPath;
            }
        }

        // Do one additional check based on path of current file (Phing.php)
        $maybeHomeDir = realpath(__DIR__ . DIRECTORY_SEPARATOR);
        $testPath = $maybeHomeDir . DIRECTORY_SEPARATOR . $path;
        if (file_exists($testPath)) {
            return $testPath;
        }

        return null;
    }

    /**
     * Explode an include path into an array.
     *
     * If no path provided, uses current include_path. Works around issues that
     * occur when the path includes stream schemas.
     *
     * Pulled from Zend_Loader::explodeIncludePath() in ZF1.
     *
     * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * @param null|string $path
     *
     * @return array
     */
    public static function explodeIncludePath($path = null)
    {
        if (null === $path) {
            $path = get_include_path();
        }

        if (PATH_SEPARATOR === ':') {
            // On *nix systems, include_paths which include paths with a stream
            // schema cannot be safely explode'd, so we have to be a bit more
            // intelligent in the approach.
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }

        return $paths;
    }

    /**
     * Returns property value for a System property.
     * System properties are "global" properties like application.startdir,
     * and user.dir.  Many of these correspond to similar properties in Java
     * or Ant.
     *
     * @param string $propName
     *
     * @return string value of found property (or null, if none found)
     */
    public static function getProperty($propName)
    {
        // some properties are detemined on each access
        // some are cached, see below

        // default is the cached value:
        $val = self::$properties[$propName] ?? null;

        // special exceptions
        switch ($propName) {
            case 'user.dir':
                $val = getcwd();

                break;
        }

        return $val;
    }

    /**
     * Creates generic buildfile.
     *
     * @param string $path
     */
    public static function init($path)
    {
        if ($buildfilePath = self::initPath($path)) {
            self::initWrite($buildfilePath);
        }
    }

    /**
     * Sets the stream to use for standard (non-error) output.
     *
     * @param OutputStream $stream the stream to use for standard output
     */
    public static function setOutputStream(OutputStream $stream)
    {
        self::$out = $stream;
    }

    /**
     * Sets the stream to use for error output.
     *
     * @param OutputStream $stream the stream to use for error output
     */
    public static function setErrorStream(OutputStream $stream): void
    {
        self::$err = $stream;
    }

    /**
     * Making output level a static property so that this property
     * can be accessed by other parts of the system, enabling
     * us to display more information -- e.g. backtraces -- for "debug" level.
     *
     * @return int
     */
    public static function getMsgOutputLevel()
    {
        return self::$msgOutputLevel;
    }

    /**
     * Prints the message of the Exception if it's not null.
     */
    public static function printMessage(Throwable $t): void
    {
        if (null === self::$err) { // Make sure our error output is initialized
            self::initializeOutputStreams();
        }
        if (self::getMsgOutputLevel() >= Project::MSG_VERBOSE) {
            self::$err->write((string) $t . PHP_EOL);
        } else {
            self::$err->write($t->getMessage() . PHP_EOL);
        }
    }

    /**
     * Performs any shutdown routines, such as stopping timers.
     *
     * @throws IOException
     */
    public static function shutdown(): void
    {
        FileSystem::getFileSystem()::deleteFilesOnExit();
        self::$msgOutputLevel = Project::MSG_INFO;
        self::restoreIni();
        self::getTimer()->stop();
    }

    /**
     * Returns reference to DefaultClock object.
     */
    public static function getTimer(): DefaultClock
    {
        if (null === self::$timer) {
            self::$timer = new DefaultClock();
        }

        return self::$timer;
    }

    /**
     * This sets a property that was set via command line or otherwise passed into Phing.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed value of found property (or null, if none found)
     */
    public static function setDefinedProperty($name, $value)
    {
        return self::$definedProps->setProperty($name, $value);
    }

    /**
     * Executes the build.
     *
     * @throws IOException
     * @throws Throwable
     */
    public function runBuild(): void
    {
        if (!$this->readyToRun) {
            return;
        }

        $project = new Project();

        self::setCurrentProject($project);
        set_error_handler(['Phing\Phing', 'handlePhpError']);

        $error = null;

        try {
            $this->addBuildListeners($project);
            $this->addInputHandler($project);

            // set this right away, so that it can be used in logging.
            $project->setUserProperty('phing.file', $this->buildFile->getAbsolutePath());
            $project->setUserProperty('phing.dir', dirname($this->buildFile->getAbsolutePath()));
            $project->setUserProperty('phing.version', static::getPhingVersion());
            $project->fireBuildStarted();
            $project->init();
            $project->setKeepGoingMode($this->keepGoingMode);

            $e = self::$definedProps->keys();
            while (count($e)) {
                $arg = (string) array_shift($e);
                $value = (string) self::$definedProps->getProperty($arg);
                $project->setUserProperty($arg, $value);
            }
            unset($e);

            // first use the Configurator to create the project object
            // from the given build file.

            ProjectConfigurator::configureProject($project, $this->buildFile);

            // Set the project mode
            $project->setStrictMode(StringHelper::booleanValue($this->strictMode));

            // make sure that minimum required phing version is satisfied
            $this->comparePhingVersion($project->getPhingVersion());

            if ($this->projectHelp) {
                $this->printDescription($project);
                $this->printTargets($project);

                return;
            }

            // make sure that we have a target to execute
            if (0 === count($this->targets)) {
                $this->targets[] = $project->getDefaultTarget();
            }

            $project->executeTargets($this->targets);
        } catch (Throwable $t) {
            $error = $t;

            throw $t;
        } finally {
            if (!$this->projectHelp) {
                try {
                    $project->fireBuildFinished($error);
                } catch (Exception $e) {
                    self::$err->write('Caught an exception while logging the end of the build.  Exception was:' . PHP_EOL);
                    self::$err->write($e->getTraceAsString());
                    if (null !== $error) {
                        self::$err->write('There has been an error prior to that:' . PHP_EOL);
                        self::$err->write($error->getTraceAsString());
                    }

                    throw new BuildException($error);
                }
            } elseif (null !== $error) {
                $project->log($error->getMessage(), Project::MSG_ERR);
            }

            restore_error_handler();
            self::unsetCurrentProject();
        }
    }

    /**
     * Import a class, supporting the following conventions:
     * - PEAR style (@see http://pear.php.net/manual/en/standards.naming.php)
     * - PSR-0 (@see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).
     *
     * @param string $classname Name of class
     * @param mixed  $classpath String or object supporting __toString()
     *
     * @throws BuildException - if cannot find the specified file
     *
     * @return string the unqualified classname (which can be instantiated)
     */
    public static function import($classname, $classpath = null)
    {
        // first check to see that the class specified hasn't already been included.
        if (class_exists($classname)) {
            return $classname;
        }

        $filename = strtr($classname, ['_' => DIRECTORY_SEPARATOR, '\\' => DIRECTORY_SEPARATOR]) . '.php';

        Phing::importFile($filename, $classpath);

        return $classname;
    }

    /**
     * Import a PHP file.
     *
     * This used to be named __import, however PHP has reserved all method names
     * with a double underscore prefix for future use.
     *
     * @param string $path      Path to the PHP file
     * @param mixed  $classpath String or object supporting __toString()
     *
     * @throws ConfigurationException
     */
    public static function importFile($path, $classpath = null)
    {
        if ($classpath) {
            // Apparently casting to (string) no longer invokes __toString() automatically.
            if (is_object($classpath)) {
                $classpath = $classpath->__toString();
            }

            // classpaths are currently additive, but we also don't want to just
            // indiscriminantly prepand/append stuff to the include_path.  This means
            // we need to parse current incldue_path, and prepend any
            // specified classpath locations that are not already in the include_path.
            //
            // NOTE:  the reason why we do it this way instead of just changing include_path
            // and then changing it back, is that in many cases applications (e.g. Propel) will
            // include/require class files from within method calls.  This means that not all
            // necessary files will be included in this import() call, and hence we can't
            // change the include_path back without breaking those apps.  While this method could
            // be more expensive than switching & switching back (not sure, but maybe), it makes it
            // possible to write far less expensive run-time applications (e.g. using Propel), which is
            // really where speed matters more.

            $curr_parts = Phing::explodeIncludePath();
            $add_parts = Phing::explodeIncludePath($classpath);
            $new_parts = array_diff($add_parts, $curr_parts);
            if ($new_parts) {
                set_include_path(implode(PATH_SEPARATOR, array_merge($new_parts, $curr_parts)));
            }
        }

        $ret = include_once $path;

        if (false === $ret) {
            $msg = "Error importing {$path}";
            if (self::getMsgOutputLevel() >= Project::MSG_DEBUG) {
                $x = new Exception('for-path-trace-only');
                $msg .= $x->getTraceAsString();
            }

            throw new ConfigurationException($msg);
        }
    }

    /**
     * Print the project description, if any.
     *
     * @throws IOException
     */
    public function printDescription(Project $project): void
    {
        if (null !== $project->getDescription()) {
            $project->log($project->getDescription());
        }
    }

    /**
     * Print out a list of all targets in the current buildfile.
     */
    public function printTargets(Project $project)
    {
        $visibleTargets = array_filter($project->getTargets(), function (Target $target) {
            return !$target->isHidden() && !empty($target->getName());
        });
        $padding = array_reduce($visibleTargets, function (int $carry, Target $target) {
            return max(strlen($target->getName()), $carry);
        }, 0);
        $categories = [
            'Default target:' => array_filter($visibleTargets, function (Target $target) use ($project) {
                return trim(strval($target)) === $project->getDefaultTarget();
            }),
            'Main targets:' => array_filter($visibleTargets, function (Target $target) {
                return !empty($target->getDescription());
            }),
            'Subtargets:' => array_filter($visibleTargets, function (Target $target) {
                return empty($target->getDescription());
            }),
        ];
        foreach ($categories as $title => $targets) {
            $targetList = $this->generateTargetList($title, $targets, $padding);
            $project->log($targetList, Project::MSG_WARN);
        }
    }

    /**
     * Unsets the current Project.
     */
    public static function unsetCurrentProject(): void
    {
        self::$currentProject = null;
    }

    /**
     * Error handler for PHP errors encountered during the build.
     * This uses the logging for the currently configured project.
     *
     * @param $level
     * @param string $message
     * @param $file
     * @param $line
     */
    public static function handlePhpError($level, $message, $file, $line)
    {
        // don't want to print suppressed errors
        if (error_reporting() > 0) {
            if (self::$phpErrorCapture) {
                self::$capturedPhpErrors[] = [
                    'message' => $message,
                    'level' => $level,
                    'line' => $line,
                    'file' => $file,
                ];
            } else {
                $message = '[PHP Error] ' . $message;
                $message .= ' [line ' . $line . ' of ' . $file . ']';

                switch ($level) {
                    case E_USER_DEPRECATED:
                    case E_DEPRECATED:
                    case E_STRICT:
                    case E_NOTICE:
                    case E_USER_NOTICE:
                        self::log($message, Project::MSG_VERBOSE);

                        break;

                    case E_WARNING:
                    case E_USER_WARNING:
                        self::log($message, Project::MSG_WARN);

                        break;

                    case E_ERROR:
                    case E_USER_ERROR:
                    default:
                        self::log($message, Project::MSG_ERR);
                } // switch
            } // if phpErrorCapture
        } // if not @
    }

    /**
     * A static convenience method to send a log to the current (last-setup) Project.
     * If there is no currently-configured Project, then this will do nothing.
     *
     * @param string $message
     * @param int    $priority project::MSG_INFO, etc
     */
    public static function log($message, $priority = Project::MSG_INFO): void
    {
        $p = self::getCurrentProject();
        if ($p) {
            $p->log($message, $priority);
        }
    }

    /**
     * Gets the current Project.
     *
     * @return Project current Project or NULL if none is set yet/still
     */
    public static function getCurrentProject()
    {
        return self::$currentProject;
    }

    /**
     * Sets the current Project.
     *
     * @param Project $p
     */
    public static function setCurrentProject($p): void
    {
        self::$currentProject = $p;
    }

    /**
     * Begins capturing PHP errors to a buffer.
     * While errors are being captured, they are not logged.
     */
    public static function startPhpErrorCapture(): void
    {
        self::$phpErrorCapture = true;
        self::$capturedPhpErrors = [];
    }

    /**
     * Stops capturing PHP errors to a buffer.
     * The errors will once again be logged after calling this method.
     */
    public static function stopPhpErrorCapture(): void
    {
        self::$phpErrorCapture = false;
    }

    /**
     * Clears the captured errors without affecting the starting/stopping of the capture.
     */
    public static function clearCapturedPhpErrors(): void
    {
        self::$capturedPhpErrors = [];
    }

    /**
     * Gets any PHP errors that were captured to buffer.
     *
     * @return array array('message' => message, 'line' => line number, 'file' => file name, 'level' => error level)
     */
    public static function getCapturedPhpErrors()
    {
        return self::$capturedPhpErrors;
    }

    /**
     * This gets a property that was set via command line or otherwise passed into Phing.
     * "Defined" in this case means "externally defined".  The reason this method exists is to
     * provide a public means of accessing commandline properties for (e.g.) logger or listener
     * scripts.  E.g. to specify which logfile to use, PearLogger needs to be able to access
     * the pear.log.name property.
     *
     * @param string $name
     *
     * @return string value of found property (or null, if none found)
     */
    public static function getDefinedProperty($name)
    {
        return self::$definedProps->getProperty($name);
    }

    /**
     * Retuns reference to all properties.
     */
    public static function &getProperties()
    {
        return self::$properties;
    }

    /**
     * Start up Phing.
     * Sets up the Phing environment but does not initiate the build process.
     *
     * @throws exception - If the Phing environment cannot be initialized
     */
    public static function startup(): void
    {
        // setup STDOUT and STDERR defaults
        self::initializeOutputStreams();

        // some init stuff
        self::getTimer()->start();

        self::setSystemConstants();
        self::setIncludePaths();
        self::setIni();
    }

    /**
     * @param $propName
     * @param $propValue
     *
     * @return string
     */
    public static function setProperty($propName, $propValue)
    {
        $propName = (string) $propName;
        $oldValue = self::getProperty($propName);
        self::$properties[$propName] = $propValue;

        return $oldValue;
    }

    /**
     * Returns buildfile's path.
     *
     * @param $path
     *
     * @throws ConfigurationException
     *
     * @return string
     */
    protected static function initPath($path)
    {
        // Fallback
        if (empty($path)) {
            $defaultDir = self::getProperty('application.startdir');
            $path = $defaultDir . DIRECTORY_SEPARATOR . self::DEFAULT_BUILD_FILENAME;
        }

        // Adding filename if necessary
        if (is_dir($path)) {
            $path .= DIRECTORY_SEPARATOR . self::DEFAULT_BUILD_FILENAME;
        }

        // Check if path is available
        $dirname = dirname($path);
        if (is_dir($dirname) && !is_file($path)) {
            return $path;
        }

        // Path is valid, but buildfile already exists
        if (is_file($path)) {
            throw new ConfigurationException('Buildfile already exists.');
        }

        throw new ConfigurationException('Invalid path for sample buildfile.');
    }

    /**
     * Writes sample buildfile.
     *
     * If $buildfilePath does not exist, the buildfile is created.
     *
     * @param $buildfilePath buildfile's location
     *
     * @throws ConfigurationException
     */
    protected static function initWrite($buildfilePath): void
    {
        // Overwriting protection
        if (file_exists($buildfilePath)) {
            throw new ConfigurationException('Cannot overwrite existing file.');
        }

        file_put_contents($buildfilePath, self::DEFAULT_BUILD_CONTENT);
    }

    /**
     * This operation is expected to call `exit($int)`, which
     * is what the base version does.
     * However, it is possible to do something else.
     *
     * @param int $exitCode code to exit with
     */
    protected static function statusExit($exitCode): void
    {
        Phing::shutdown();

        exit($exitCode);
    }

    /**
     * Handle the -propertyfile argument.
     *
     * @throws ConfigurationException
     * @throws IOException
     */
    private function handleArgPropertyFile(array $args, int $pos): int
    {
        if (!isset($args[$pos + 1])) {
            throw new ConfigurationException('You must specify a filename when using the -propertyfile argument');
        }

        $this->propertyFiles[] = $args[++$pos];

        return $pos;
    }

    /**
     * Search parent directories for the build file.
     *
     * Takes the given target as a suffix to append to each
     * parent directory in search of a build file.  Once the
     * root of the file-system has been reached an exception
     * is thrown.
     *
     * @param string $start  start file path
     * @param string $suffix suffix filename to look for in parents
     *
     * @throws ConfigurationException
     *
     * @return File A handle to the build file
     */
    private function findBuildFile($start, $suffix)
    {
        if (self::getMsgOutputLevel() >= Project::MSG_INFO) {
            self::$out->write('Searching for ' . $suffix . ' ...' . PHP_EOL);
        }

        $parent = new File((new File($start))->getAbsolutePath());
        $file = new File($parent, $suffix);

        // check if the target file exists in the current directory
        while (!$file->exists()) {
            // change to parent directory
            $parent = $parent->getParentFile();

            // if parent is null, then we are at the root of the fs,
            // complain that we can't find the build file.
            if (null === $parent) {
                throw new ConfigurationException('Could not locate a build file!');
            }
            // refresh our file handle
            $file = new File($parent, $suffix);
        }

        return $file;
    }

    /**
     * @throws IOException
     */
    private function loadPropertyFiles(): void
    {
        foreach ($this->propertyFiles as $filename) {
            $fileParserFactory = new FileParserFactory();
            $fileParser = $fileParserFactory->createParser(pathinfo($filename, PATHINFO_EXTENSION));
            $p = new Properties(null, $fileParser);

            try {
                $p->load(new File($filename));
            } catch (IOException $e) {
                self::$out->write('Could not load property file ' . $filename . ': ' . $e->getMessage());
            }
            foreach ($p->getProperties() as $prop => $value) {
                self::$definedProps->setProperty($prop, $value);
            }
        }
    }

    /**
     * Close logfiles, if we have been writing to them.
     *
     * @since Phing 2.3.0
     */
    private static function handleLogfile(): void
    {
        if (self::$isLogFileUsed) {
            self::$err->close();
            self::$out->close();
        }
    }

    /**
     * Sets the stdout and stderr streams if they are not already set.
     */
    private static function initializeOutputStreams()
    {
        if (null === self::$out) {
            if (!defined('STDOUT')) {
                self::$out = new OutputStream(fopen('php://stdout', 'w'));
            } else {
                self::$out = new OutputStream(STDOUT);
            }
        }
        if (null === self::$err) {
            if (!defined('STDERR')) {
                self::$err = new OutputStream(fopen('php://stderr', 'w'));
            } else {
                self::$err = new OutputStream(STDERR);
            }
        }
    }

    /**
     * Restores [most] PHP INI values to their pre-Phing state.
     *
     * Currently the following settings are not restored:
     *  - max_execution_time (because getting current time limit is not possible)
     *  - memory_limit (which may have been increased by Phing)
     */
    private static function restoreIni(): void
    {
        foreach (self::$origIniSettings as $settingName => $settingValue) {
            switch ($settingName) {
                case 'error_reporting':
                    error_reporting($settingValue);

                    break;

                default:
                    ini_set($settingName, $settingValue);
            }
        }
    }

    /**
     * Bind any registered build listeners to this project.
     *
     * This means adding the logger and any build listeners that were specified
     * with -listener arg.
     *
     * @throws BuildException
     * @throws ConfigurationException
     */
    private function addBuildListeners(Project $project)
    {
        // Add the default listener
        $project->addBuildListener($this->createLogger());

        foreach ($this->listeners as $listenerClassname) {
            try {
                $clz = Phing::import($listenerClassname);
            } catch (Exception $e) {
                $msg = 'Unable to instantiate specified listener '
                    . 'class ' . $listenerClassname . ' : '
                    . $e->getMessage();

                throw new ConfigurationException($msg);
            }

            $listener = new $clz();

            if ($listener instanceof StreamRequiredBuildLogger) {
                throw new ConfigurationException('Unable to add ' . $listenerClassname . ' as a listener, since it requires explicit error/output streams. (You can specify it as a -logger.)');
            }
            $project->addBuildListener($listener);
        }
    }

    /**
     * Creates the default build logger for sending build events to the log.
     *
     * @throws BuildException
     *
     * @return BuildLogger The created Logger
     */
    private function createLogger()
    {
        if ($this->silent) {
            $logger = new SilentLogger();
            self::$msgOutputLevel = Project::MSG_WARN;
        } elseif (null !== $this->loggerClassname) {
            self::import($this->loggerClassname);
            // get class name part
            $classname = self::import($this->loggerClassname);
            $logger = new $classname();
            if (!($logger instanceof BuildLogger)) {
                throw new BuildException($classname . ' does not implement the BuildLogger interface.');
            }
        } else {
            $logger = new DefaultLogger();
        }
        $logger->setMessageOutputLevel(self::$msgOutputLevel);
        $logger->setOutputStream(self::$out);
        $logger->setErrorStream(self::$err);
        $logger->setEmacsMode($this->emacsMode);

        return $logger;
    }

    /**
     * Creates the InputHandler and adds it to the project.
     *
     * @param Project $project the project instance
     *
     * @throws ConfigurationException
     */
    private function addInputHandler(Project $project): void
    {
        if (null === $this->inputHandlerClassname) {
            $handler = new ConsoleInputHandler(STDIN, new ConsoleOutput());
        } else {
            try {
                $clz = Phing::import($this->inputHandlerClassname);
                $handler = new $clz();
                if (null !== $project && method_exists($handler, 'setProject')) {
                    $handler->setProject($project);
                }
            } catch (Exception $e) {
                $msg = 'Unable to instantiate specified input handler '
                    . 'class ' . $this->inputHandlerClassname . ' : '
                    . $e->getMessage();

                throw new ConfigurationException($msg);
            }
        }
        $project->setInputHandler($handler);
    }

    /**
     * @param string $version
     *
     * @throws BuildException
     * @throws ConfigurationException
     */
    private function comparePhingVersion($version): void
    {
        $current = strtolower(self::getPhingVersion());
        $current = trim(str_replace('phing', '', $current));

        // make sure that version checks are not applied to trunk
        if ('dev' === $current) {
            return;
        }

        if (-1 == version_compare($current, $version)) {
            throw new BuildException(
                sprintf('Incompatible Phing version (%s). Version "%s" required.', $current, $version)
            );
        }
    }

    /**
     * Returns a formatted list of target names with an optional description.
     *
     * @param string   $title   Title for this list
     * @param Target[] $targets Targets in this list
     * @param int      $padding Padding for name column
     */
    private function generateTargetList(string $title, array $targets, int $padding): string
    {
        usort($targets, function (Target $a, Target $b) {
            return $a->getName() <=> $b->getName();
        });

        $header = <<<HEADER
            {$title}
            -------------------------------------------------------------------------------

            HEADER;

        $getDetails = function (Target $target) use ($padding): string {
            $details = [];
            if (!empty($target->getDescription())) {
                $details[] = $target->getDescription();
            }
            if (!empty($target->getDependencies())) {
                $details[] = ' - depends on: ' . implode(', ', $target->getDependencies());
            }
            if (!empty($target->getIf())) {
                $details[] = ' - if property: ' . $target->getIf();
            }
            if (!empty($target->getUnless())) {
                $details[] = ' - unless property: ' . $target->getUnless();
            }
            $detailsToString = function (?string $name, ?string $detail) use ($padding): string {
                return sprintf(" %-{$padding}s  %s", $name, $detail);
            };

            return implode(PHP_EOL, array_map($detailsToString, [$target->getName()], $details));
        };

        return $header . implode(PHP_EOL, array_map($getDetails, $targets)) . PHP_EOL;
    }

    /**
     * Set System constants which can be retrieved by calling Phing::getProperty($propName).
     */
    private static function setSystemConstants(): void
    {
        /*
         * PHP_OS returns on
         *   WindowsNT4.0sp6  => WINNT
         *   Windows2000      => WINNT
         *   Windows ME       => WIN32
         *   Windows 98SE     => WIN32
         *   FreeBSD 4.5p7    => FreeBSD
         *   Redhat Linux     => Linux
         *   Mac OS X         => Darwin
         */
        self::setProperty('host.os', PHP_OS);

        // this is used by some tasks too
        self::setProperty('os.name', PHP_OS);

        // it's still possible this won't be defined,
        // e.g. if Phing is being included in another app w/o
        // using the phing.php script.
        if (!defined('PHP_CLASSPATH')) {
            define('PHP_CLASSPATH', get_include_path());
        }

        self::setProperty('php.classpath', PHP_CLASSPATH);

        // try to determine the host filesystem and set system property
        // used by Fileself::getFileSystem to instantiate the correct
        // abstraction layer

        if (PHP_OS_FAMILY === 'Windows') {
            self::setProperty('host.fstype', 'WINDOWS');
            self::setProperty('user.home', getenv('HOMEDRIVE') . getenv('HOMEPATH'));
        } else {
            self::setProperty('host.fstype', 'UNIX');
            self::setProperty('user.home', getenv('HOME'));
        }
        self::setProperty(self::PHP_INTERPRETER, PHP_BINARY);
        self::setProperty('file.separator', FileUtils::getSeparator());
        self::setProperty('line.separator', PHP_EOL);
        self::setProperty('path.separator', FileUtils::getPathSeparator());
        self::setProperty(self::PHP_VERSION, PHP_VERSION);
        self::setProperty('php.tmpdir', sys_get_temp_dir());
        self::setProperty('application.startdir', getcwd());
        self::setProperty('phing.startTime', gmdate('D, d M Y H:i:s', time()) . ' GMT');

        // try to detect machine dependent information
        $sysInfo = [];
        if (function_exists('posix_uname') && 0 !== stripos(PHP_OS, 'WIN')) {
            $sysInfo = posix_uname();
        } else {
            $sysInfo['nodename'] = php_uname('n');
            $sysInfo['machine'] = php_uname('m');
            //this is a not so ideal substition, but maybe better than nothing
            $sysInfo['domain'] = $_SERVER['SERVER_NAME'] ?? 'unknown';
            $sysInfo['release'] = php_uname('r');
            $sysInfo['version'] = php_uname('v');
        }

        self::setProperty('host.name', $sysInfo['nodename'] ?? 'unknown');
        self::setProperty('host.arch', $sysInfo['machine'] ?? 'unknown');
        self::setProperty('host.domain', $sysInfo['domain'] ?? 'unknown');
        self::setProperty('host.os.release', $sysInfo['release'] ?? 'unknown');
        self::setProperty('host.os.version', $sysInfo['version'] ?? 'unknown');
        unset($sysInfo);
    }

    /**
     * Sets the include path to PHP_CLASSPATH constant (if this has been defined).
     *
     * @throws ConfigurationException - if the include_path could not be set (for some bizarre reason)
     */
    private static function setIncludePaths(): void
    {
        if (defined('PHP_CLASSPATH')) {
            $result = set_include_path(PHP_CLASSPATH);
            if (false === $result) {
                throw new ConfigurationException('Could not set PHP include_path.');
            }
            self::$origIniSettings['include_path'] = $result; // save original value for setting back later
        }
    }

    /**
     * Sets PHP INI values that Phing needs.
     */
    private static function setIni(): void
    {
        self::$origIniSettings['error_reporting'] = error_reporting(E_ALL);

        // We won't bother storing original max_execution_time, since 1) the value in
        // php.ini may be wrong (and there's no way to get the current value) and
        // 2) it would mean something very strange to set it to a value less than time script
        // has already been running, which would be the likely change.

        set_time_limit(0);

        self::$origIniSettings['short_open_tag'] = ini_set('short_open_tag', 'off');
        self::$origIniSettings['default_charset'] = ini_set('default_charset', 'iso-8859-1');

        $mem_limit = (int) SizeHelper::fromHumanToBytes(ini_get('memory_limit'));
        if ($mem_limit < (32 * 1024 * 1024) && $mem_limit > -1) {
            // We do *not* need to save the original value here, since we don't plan to restore
            // this after shutdown (we don't trust the effectiveness of PHP's garbage collection).
            ini_set('memory_limit', '32M'); // nore: this may need to be higher for many projects
        }
    }
}
