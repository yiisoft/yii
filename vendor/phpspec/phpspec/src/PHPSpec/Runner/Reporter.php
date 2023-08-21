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
namespace PHPSpec\Runner;

use PHPSpec\Runner\Formatter;
use PHPSpec\Runner\ReporterEvent;

use PHPSpec\Specification\Result\Failure;
use PHPSpec\Specification\Result\Error;
use PHPSpec\Specification\Result\Exception;
use PHPSpec\Specification\Result\Pending;
use PHPSpec\Specification\Result\DeliberateFailure;

use PHPSpec\Example;
use PHPSpec\Specification\ExampleGroup;
use PHPSpec\Specification\SharedExample;

abstract class Reporter implements \SPLSubject
{
    
    /**
     * The formatters
     *
     * @var array
     */
    protected $_formatters;
    
    /**
     * The failures
     *
     * @var \SplObjectStorage
     */
    protected $_failures;
    
    /**
     * The errors
     *
     * @var \SplObjectStorage
     */
    protected $_errors;
    
    /**
     * The pending examples
     *
     * @var \SplObjectStorage
     */
    protected $_pendingExamples;
    
    /**
     * The exceptions
     *
     * @var \SplObjectStorage
     */
    protected $_exceptions;
    
    /**
     * Starting time
     *
     * @var float
     */
    protected $_startTime;
    
    /**
     * End time
     *
     * @var float
     */
    protected $_endTime;
    
    /**
     * The passing examples
     * 
     * @var array
     */
    protected $_passing = array();
    
    protected $_shared = array();
    
    /**
     * Sets the message to be printed by the formatter
     * 
     * @param string $string
     */
    abstract public function setMessage($string);
    
    /**
     * Whether the reporter has a message
     * 
     * @param boolean
     */
    abstract public function hasMessage();
    
    /**
     * Gets the message
     * 
     * @return string
     */
    abstract public function getMessage();
    
    /**
     * Gets the formatter
     * 
     * @return array<\PHPSpec\Runner\Formatter>
     */
    abstract public function getFormatters();
    
    /**
     * Adds a deliberate failure to the report
     * 
     * @param \PHPSpec\Specification\Example                  $example
     * @param \PHPSpec\Specification\Result\DeliberateFailure $failure
     */
    abstract public function addDeliberateFailure(Example $example,
                                                  DeliberateFailure $failure);
    
    /**
     * Adds a failure to the report
     * 
     * @param \PHPSpec\Specification\Example        $example
     * @param \PHPSpec\Specification\Result\Failure $failure
     */
    abstract public function addFailure(Example $example, Failure $failure);
    
    /**
     * Adds an error to the report
     * 
     * @param \PHPSpec\Specification\Example      $example
     * @param \PHPSpec\Specification\Result\Error $error
     */
    abstract public function addError(Example $example, Error $error);
    
    /**
     * Adds an exception to the report
     * 
     * @param \PHPSpec\Specification\Example $example
     * @param \Exception                     $e
     */
    abstract public function addException(Example $example, \Exception $e);
    
    /**
     * Adds a pending example to the report
     * 
     * @param \PHPSpec\Specification\Example        $example
     * @param \PHPSpec\Specification\Result\Pending $pending
     */
    abstract public function addPending(Example $example, Pending $pending);
    
    /**
     * Adds a passing example to the report
     * 
     * @param \PHPSpec\Specification\Example $example
     */
    abstract public function addPass(Example $example);
    
    /**
     * Creates a report initialising all the counters as spl object storages
     */
    public function __construct()
    {
        $this->_failures           = new \SplObjectStorage;
        $this->_errors             = new \SplObjectStorage;
        $this->_pendingExamples    = new \SplObjectStorage;
        $this->_exceptions         = new \SplObjectStorage;
    }
    
    /**
     * Attaches formatters to the report
     * 
     * @param \SPLObserver $formatter
     */
    public function attach(\SPLObserver $formatter)
    {
        $this->_formatters[] = $formatter;
    }
    
    /**
     * Detaches formatters from the report
     * 
     * @param \SPLObserver $formatter
     */
    public function detach(\SPLObserver $formatter)
    {
        $remainingObservers = array();
        foreach ($this->_formatters as $observer) {
            if ($observer === $formatter) {
                continue;
            }
            $remainingObservers[] = $formatter;
        }
        $this->formatters = $remainingObservers;
    }
    
    /**
     * Sends a start event to the formatter
     * 
     * @param \PHPSpec\Specification\ExampleGroup $exampleGroup
     */
    public function exampleGroupStarted(ExampleGroup $exampleGroup)
    {
        $name = preg_replace(
            '/Describe(?!.*Describe)/', '', get_class($exampleGroup)
        );
        $classRefl = new \ReflectionClass($exampleGroup);
        $filename = $classRefl->getFileName();
        $time = microtime(true);
        $this->notify(
            new ReporterEvent('start', '', $name, $time, $filename)
        );
    }
    
    /**
     * Sends a finish event to the formatter
     * 
     * @param \PHPSpec\Specification\ExampleGroup $exampleGroup
     */
    public function exampleGroupFinished(ExampleGroup $exampleGroup)
    {
        $name = preg_replace(
            '/Describe(?!.*Describe)/', '', get_class($exampleGroup)
        );
        $time = microtime(true);
        $this->notify(
            ReporterEvent::newWithTimeAndName('finish', $time, $name)
        );
    }
    
    public function sharedExampleStarted(SharedExample $sharedExample)
    {
        $name = preg_replace(
            '/SharedExample(s?)$/', '', get_class($sharedExample)
        );
        if (!$this->startedShared($name)) {
            $this->startShared($name);
            $time = microtime(true);
            $this->notify(
                ReporterEvent::newWithTimeAndName('startShared', $time, $name)
            );
        }
    }
    
    public function sharedExampleFinished(SharedExample $sharedExample)
    {
        $name = preg_replace(
            '/SharedExample(s?)$/', '', get_class($sharedExample)
        );
        $this->finishShared($name);
        $time = microtime(true);
        $this->notify(
            ReporterEvent::newWithTimeAndName('finishShared', $time, $name)
        );
    }
    
    protected function startedShared($name)
    {
        return in_array($name, $this->_shared);
    }
    
    protected function startShared($name)
    {
        $this->_shared[] = $name;
    }
    
    protected function finishShared($name)
    {
        $sharedToKeep = array();
        foreach ($this->_shared as $shared) {
            if (!$shared !== $name) {
                $sharedToKeep[] = $name;
            }
        }
        $this->_shared = $sharedToKeep;
    }
    
    /**
     * Iterates through all formatters and call update once an event happens
     */
    public function notify()
    {
        foreach ($this->getFormatters() as $observer) {
            $observer->update($this, func_get_arg(0));
        }
    }
    
    /**
     * Sets the runtime start
     * 
     * @param float $time
     */
    public function setRuntimeStart($time = null)
    {
        $this->_startTime = $time ?: microtime(true);
    }
    
    /**
     * Sets the runtime end
     * 
     * @param float $time
     */
    public function setRuntimeEnd($time = null)
    {
        $this->_endTime = $time ?: microtime(true);
    }
    
    /**
     * Gets the runtime start
     * 
     * @return float
     */
    public function getRuntimeStart()
    {
        return $this->_startTime;
    }
    
    /**
     * Gets the runtime end
     * 
     * @return float
     */
    public function getRuntimeEnd()
    {
        return $this->_endTime;
    }
    
    /**
     * Gets the runtime
     * 
     * @return float
     */
    public function getRuntime()
    {
        if (!$this->_endTime) {
            $this->_endTime = microtime(true);
        }
        return sprintf("%.6F", $this->_endTime - $this->_startTime);
    }
    
    /**
     * Whether there are failures
     * 
     * @return boolean
     */
    public function hasFailures()
    {
        return (bool)$this->_failures->count();
    }
    
    /**
     * Get the failures
     * 
     * @return SplObjectStorage
     */
    public function getFailures()
    {
        return $this->_failures;
    }
    
    /**
     * Get an example of a failure
     * 
     * @param \PHPSpec\Specification\Result\Failure $failure
     * @return \PHPSpec\Specification\Example
     */
    public function getFailure(Failure $failure)
    {
        return $this->_failures[$failure];
    }
    
    /**
     * Whether there are errors
     * 
     * @return boolean
     */
    public function hasErrors()
    {
        return (bool)$this->_errors->count();
    }
    
    /**
     * Get the errors
     * 
     * @return SplObjectStorage
     */
    public function getErrors()
    {
        return $this->_errors;
    }
    
    /**
     * Get a the example of an error
     * 
     * @param \PHPSpec\Specification\Result\Error $error
     * @return \PHPSpec\Specification\Example
     */
    public function getError(Error $error)
    {
        return $this->_errors[$error];
    }
    
    /**
     * Whether there are exceptions
     * 
     * @return boolean
     */
    public function hasExceptions()
    {
        return (bool)$this->_exceptions->count();
    }
    
    /**
     * Get the exceptions
     * 
     * @return SplObjectStorage
     */
    public function getExceptions()
    {
        if ($this->_exceptions === null) {
            return $this->_exceptions = new \SplObjectStorage;
        }
        return $this->_exceptions;
    }
    
    /**
     * Get a the example of an exception
     * 
     * @param \PHPSpec\Specification\Result\Exception $exception
     * @return \PHPSpec\Specification\Example
     */
    public function getException(Exception $exception)
    {
        return $this->_exceptions[$exception];
    }
    
    /**
     * Whether there are pending examples
     * 
     * @return boolean
     */
    public function hasPendingExamples()
    {
        return (bool)$this->_pendingExamples->count();
    }
    
    /**
     * Get the pendings examples
     * 
     * @return SplObjectStorage
     */
    public function getPendingExamples()
    {
        return $this->_pendingExamples;
    }
    
    /**
     * Get a the example of a pending spec
     * 
     * @param \PHPSpec\Specification\Result\Pending $pending
     * @return \PHPSpec\Specification\Example
     */
    public function getPending(Pending $pending)
    {
        return $this->_pendingExamples[$pending];
    }
    
    /**
     * Get passing
     * 
     * @return array
     */
    public function getPassing()
    {
        return $this->_passing;
    }
}