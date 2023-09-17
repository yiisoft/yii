<?php
/**
 * PHPSpec
 *
 * LICENSE
 *
 * This file is subject to the GNU Lesser General Public License Version 3
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/lgpl-3.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phpspec.net so we can send you a copy immediately.
 *
 * @category  PHPSpec
 * @package   PHPSpec
 * @copyright Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                    Marcello Duarte
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
namespace PHPSpec\Runner\Cli;

use PHPSpec\World;

use PHPSpec\Loader\Loader;

use PHPSpec\Runner\Error;
use PHPSpec\Runner\Cli\Error as CliError;
use PHPSpec\Runner\Formatter\Factory as FormatterFactory;

use PHPSpec\Specification\ExampleGroup;
use PHPSpec\Specification\ExampleRunner;
use PHPSpec\Specification\Example;
use PHPSpec\Specification\Result\Error as SpecificationError;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Runner implements \PHPSpec\Runner\Runner
{
    /**
     * Version
     * 
     * @var string
     */
    const VERSION = '1.4.2';
    
    /**
     * Usage message
     */
    const USAGE = "Usage: phpspec (FILE|DIRECTORY) + [options]
    
    -b, --backtrace          Enable full backtrace
    -c, --colour, --color    Enable color in the output
    -e, --example STRING     Run examples whose full nested names include STRING
    -f, --formater FORMATTER Choose a formatter
                              [p]rogress (default - dots)
                              [d]ocumentation (group and example names)
                              [h]tml
                              [j]unit
                              custom formatter class name
    --bootstrap FILENAME     Specify a bootstrap file to run before the tests
    -h, --help               You're looking at it
    --fail-fast              Abort the run on first failure.
    --include-matchers PATHS Specify a : separated list of PATHS to matchers 
    --version                Show version
";
    
    /**
     * The loader
     *
     * @var \PHPSpec\Loader\Loader
     */
    protected $_loader;
    
    
    /**
     * The formatter factory
     *
     * @var \PHPSpec\Runner\Formatter\Factory
     */
    protected $_formatterFactory;
    
    /**
     * The example runner
     *
     * @var \PHPSpec\Specification\ExampleRunner
     */
    protected $_exampleRunner;
    
    /**
     * The pattern of the example to run if -e or --example EXAMPLE is passed
     *
     * @var string
     */
    protected $_runOnly;

    /**
     * The error handler callback
     *
     * @var array
     */
     protected $_errorHandler = array (
         '\PHPSpec\Specification\Result', 'errorHandler'
    );
    
    /**
     * Sets options and runs examples; or prints version/help
     * 
     * @param \PHPSpec\World $world
     */
    public function run(World $world)
    {
        if ($this->printVersionOrHelp($world)) {
            return;
        }
        
        $this->bootstrap($world);
        
        $this->setColor($world);
        $this->setBacktrace($world);
        $this->setFailFast($world);
        $this->setExampleIntoRunner($world);
        
        $this->startErrorHandler();
        $world->getReporter()->setRuntimeStart();
        
        $this->runExamples($world);
        
        $world->getReporter()->setRuntimeEnd();
        restore_error_handler();
    }
    
    /**
     * Runs examples
     * 
     * @param \PHPSpec\World $world
     */
    protected function runExamples(World $world)
    {
        $examples = $this->getExamples($world);
        foreach ($examples as $exampleGroup) {
            if (!$this->isExampleGroup($world, $exampleGroup)) {
                continue;
            }
            $exampleGroup->setMatcherFactory($world->getMatcherFactory());
            $exampleGroup->setMacros($world->getMacros());
            $exampleGroup->beforeAll();
            $this->getExampleRunner($exampleGroup)->run(
                $world->getReporter()
            );
            $exampleGroup->afterAll();
        }
    }
    
    /**
     * Checks if example group extends ExampleGroup
     *
     * @param object $exampleGroup
     * @param World  $world
     * @return boolean
     */
    private function isExampleGroup(World $world, $exampleGroup)
    {
        if (!$exampleGroup instanceof ExampleGroup) {
            $this->tellUserClassMustExtendContext($world, $exampleGroup);
            return false;
        }
        return true;
    }

    /**
     * Adds the message "FooSpec must extend \PHPSpec\Context" to reporter
     *
     * @param object $exampleGroup
     * @param World  $world 
     */
    private function tellUserClassMustExtendContext(World $world, $exampleGroup)
    {
        $class = get_class($exampleGroup);
        $world->getReporter()->addError(
            new Example(new ExampleGroup, "  class:$class"),
            new SpecificationError("$class must extend \PHPSpec\Context")
        );        
    }
    
    /**
     * Sets the color into the formatter
     * 
     * @param \PHPSpec\World $world
     */
    protected function setColor(World $world)
    {
        $formatters = $world->getReporter()->getFormatters();
        foreach ($formatters as $formatter) {
            if ($world->getOption('c')) {
                $formatter->setShowColors(true);
            }
        }
    }
    
    /**
     * Sets the backtrace into the formatter
     * 
     * @param \PHPSpec\World $world
     */
    protected function setBacktrace(World $world)
    {
        foreach ($world->getReporter()->getFormatters() as $formatter) {
            if ($world->getOption('b')) {
                $formatter->setEnableBacktrace(true);
            }
        }
    }
    
    /**
     * Sets fail fast into the reporter
     * 
     * @param \PHPSpec\World $world
     */
    protected function setFailFast(World $world)
    {
        if ($world->getOption('failfast')) {
            $world->getReporter()->setFailFast(true);
        }
    }
    
    /**
     * Sets one example into the example runner
     * 
     * @param \PHPSpec\World $world
     */
    protected function setExampleIntoRunner(World $world)
    {
        if ($world->getOption('example')) {
            $this->_runOnly = $world->getOption('example');
        }
    }
    
    /**
     * Starts error handler
     */
    protected function startErrorHandler()
    {
        set_error_handler($this->_errorHandler);
    }
    
    /**
     * Sets the error handler
     *
     * @param array|Closure|callback $errorHandler
     */
    public function setErrorHandler($errorHandler)
    {
        $this->_errorHandler = $errorHandler;
    }
    
    /**
     * Sends a message to reporter to print help or version if needed.
     * Returns true if message was sent and false otherwise
     * 
     * @param \PHPSpec\World $world
     * @return boolean
     */
    private function printVersionOrHelp(World $world)
    {
        if ($world->getOption('version')) {
            $world->getReporter()->setMessage(self::VERSION);
            return true;
        }
        
        if ($world->getOption('h') || $world->getOption('help')) {
            $world->getReporter()->setMessage(self::USAGE);
            return true;
        }
        
        return false;
    }
    
    /**
     * Loads and returns example groups
     * 
     * @param \PHPSpec\World $world
     * @throws \PHPSpec\Runner\Cli\Error if no spec file is given
     * @return array<\PHPSpec\Specification\ExampleGroup>
     */
    private function getExamples(World $world)
    {
        if ($world->getOption('specFile')) {
            $exampleGroups = $this->loadExampleGroups(
                $world->getOption('specFile')
            );
        } else {
            throw new CliError('No spec file given');
        }
        return $exampleGroups;
    }
    
    /**
     * Loads example groups
     * 
     * @param string $spec
     * @throws \PHPSpec\Runner\Cli\Error propagate cli errors up
     * @return array<\PHPSpec\Specification\ExampleGroup>
     */
    private function loadExampleGroups($spec)
    {
        try {
            $exampleGroups = array();
            $loader = $this->getLoader()->factory($spec);
            return $loader->load($spec);
        } catch (Error $e) {
            throw new CliError($e->getMessage());
        }
    }
    
    /**
     * Gets formatter
     * 
     * @param \PHPSpec\World $world
     */
    public function getFormatter(World $world)
    {
        return $this->getFormatterFactory()->create(
            $world->getOption('f'), $world->getReporter()
        );
    }
    
    /**
     * Gets the formatter factory
     * 
     * @return \PHPSpec\Runner\Formatter\Factory
     */
    public function getFormatterFactory()
    {
        if ($this->_formatterFactory === null) {
            $this->_formatterFactory = new FormatterFactory;
        }
        return $this->_formatterFactory;
    }
    /**
     * Sets the formatter factory
     * 
     * @param \PHPSpec\Runner\Formatter\Factory $factory
     */
    public function setFormatterFactory (FormatterFactory $factory)
    {
        $this->_formatterFactory = $factory;
    }
    /**
     * Gets the example runner
     * 
     * @param ExampleGroup $exampleGroups
     * @return \PHPSpec\Specification\ExampleRunner
     */
    public function getExampleRunner (ExampleGroup $exampleGroup)
    {
        if (!isset($this->_exampleRunner)) {
            $this->_exampleRunner = new ExampleRunner($exampleGroup);
        }
        
        $this->_exampleRunner->setExampleGroup($exampleGroup);
        
        if ($this->_runOnly) {
            $this->_exampleRunner->runOnly($this->_runOnly);
        }
        
        return $this->_exampleRunner;
    }
    
    /**
     * Sets the example runner
     * 
     * @param \PHPSpec\Specification\ExampleRunner $exampleRunner
     */
    public function setExampleRunner(ExampleRunner $exampleRunner)
    {
        $this->_exampleRunner = $exampleRunner;
    }
    
    /**
     * Gets the loader
     * 
     * @return \PHPSpec\Loader\Loader
     */
    public function getLoader()
    {
        if ($this->_loader === null) {
            $this->_loader = new Loader;
        }
        return $this->_loader;
    }
    
    /**
     * Sets the loader
     *
     * @param \PHPSpec\Loader\Loader $loader
     */
    public function setLoader(Loader $loader)
    {
        $this->_loader = $loader;
    }
    
    /**
     * Loads the bootstrap file if specified in the options
     */
    public function bootstrap(World $configure)
    {
        $bootstrapFile = $configure->getOption('bootstrap');
        
        if (empty($bootstrapFile)) {
            return;
        }
        
        if (!file_exists($bootstrapFile) || !is_readable($bootstrapFile)) {
            throw new CliError(
                'Cannot load specified bootstrap file: ' . $bootstrapFile
            );
        }
        
        include $bootstrapFile;
    }
    
    /**
     * Gets usage
     * 
     * @return string
     */
    public function getUsage()
    {
        return self::USAGE;
    }
    
    /**
     * Gets version
     * 
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }
}
