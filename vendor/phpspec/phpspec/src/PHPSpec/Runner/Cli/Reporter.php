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

use PHPSpec\Runner\Formatter;
use PHPSpec\Runner\ReporterEvent;
use PHPSpec\Runner\Reporter as BaseReporter;
 
use PHPSpec\Specification\Result\Failure;
use PHPSpec\Specification\Result\Error;
use PHPSpec\Specification\Result\Exception;
use PHPSpec\Specification\Result\Pending;
use PHPSpec\Specification\Result\DeliberateFailure;
use PHPSpec\Example;

use PHPSpec\Util\Backtrace;

use PHPSpec\DeprecatedNotice;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Reporter extends BaseReporter
{
    /**
     * Message to be printed 
     * 
     * @var string
     */
    protected $_message = '';
    
    /**
     * Whether to fail fast
     *
     * @var boolean
     */
    protected $_failFast = false;
    
    /**
     * Adds a failure to the formatters
     * 
     * @param \PHPSpec\Specification\Example        $example
     * @param \PHPSpec\Specification\Result\Failure $failure
     */
    public function addFailure(Example $example, Failure $failure)
    {
        $this->_failures->attach($failure, $example);
        $this->notify(
            new ReporterEvent(
                'status', 'F', $example->getSpecificationText(),
                $example->getExecutionTime(), $example->getFile(),
                $example->getLine(), $example->getNoOfAssertions(),
                $failure->getMessage(),
                Backtrace::pretty($failure->getTrace()), $failure
            )
        );
        
        $this->checkFailFast();
    }
    
    /**
     * Adds a pass to the formatters
     * 
     * @param \PHPSpec\Specification\Example $example
     */
    public function addPass(Example $example)
    {
        $this->_passing[] = $example;
        $this->notify(
            new ReporterEvent(
                'status', '.', $example->getSpecificationText(),
                $example->getExecutionTime(), $example->getFile(),
                $example->getLine(), $example->getNoOfAssertions()
            )
        );
    }
    
    /**
     * Adds a deliberate failure to the formatters
     * 
     * @param \PHPSpec\Specification\Example                  $example
     * @param \PHPSpec\Specification\Result\DeliberateFailure $failure
     */
    public function addDeliberateFailure(Example $example,
                                         DeliberateFailure $failure)
    {
        $this->_failures->attach($failure, $example);
        $this->notify(
            new ReporterEvent(
                'status', 'F', $example->getSpecificationText(),
                $failure->getMessage(),
                Backtrace::pretty($failure->getTrace()), $failure,
                $example->getExecutionTime(), $example->getNoOfAssertions()
            )
        );
        $this->checkFailFast();
    }
    
    /**
     * Adds an error to the formatters
     * 
     * @param \PHPSpec\Specification\Example      $example
     * @param \PHPSpec\Specification\Result\Error $error
     */
    public function addError(Example $example, Error $error)
    {
        $this->getErrors()->attach($error, $example);
        $this->notify(
            new ReporterEvent(
                'status', 'E', $example->getSpecificationText(),
                $example->getExecutionTime(), $example->getFile(),
                $example->getLine(), $example->getNoOfAssertions(),
                $error->getMessage(), Backtrace::pretty($error->getTrace()),
                $error
            )
        );
        $this->checkFailFast();
    }
    
    /**
     * Adds an exception to the formatters
     * 
     * @param \PHPSpec\Specification\Example      $example
     * @param \Exception                          $e
     */
    public function addException(Example $example, \Exception $e)
    {
        $this->getExceptions()->attach($e, $example);
        $this->notify(
            new ReporterEvent(
                'status', 'E', $example->getSpecificationText(),
                $example->getExecutionTime(), $example->getFile(),
                $example->getLine(), $example->getNoOfAssertions(),
                $e->getMessage(), Backtrace::pretty($e->getTrace()), $e
            )
        );
        $this->checkFailFast();
    }
    
    /**
     * Adds a pending to the formatters
     * 
     * @param \PHPSpec\Specification\Example        $example
     * @param \PHPSpec\Specification\Result\Pending $pending
     */
    public function addPending(Example $example, Pending $pending)
    {
        $this->_pendingExamples->attach($pending, $example);
        $this->notify(
            new ReporterEvent(
                'status', '*', $example->getSpecificationText(),
                $example->getExecutionTime(), $example->getFile(),
                $example->getLine(), $example->getNoOfAssertions(),
                $pending->getMessage(), null, null
            )
        );
    }
    
    /**
     * Sets the message
     * 
     * @param string $string
     * @param boolean $newLine
     */
    public function setMessage($string, $newLine = true)
    {
        $this->_message .= $string . ($newLine ? PHP_EOL : '');
    }
    
    /**
     * Gets the message
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * Whether there is a message set
     * 
     * @param boolean
     */
    public function hasMessage()
    {
        return (bool)strlen($this->_message);
    }
    
    /**
     * Adds a formatter
     * 
     * @param \PHPSpec\Runner\Formatter $formatter
     */
    public function addFormatter(Formatter $formatter)
    {
        $this->_formatters[] = $formatter;
    }
    
    /**
     * Sets the formatters
     * 
     * @param array $formatters
     */
    public function setFormatters(array $formatters)
    {
        $this->_formatters = $formatters;
    }
    
    /**
     * Set the formatter
     * 
     * @deprecated
     * @param \PHPSpec\Runner\Formatter $formatter
     */
    public function setFormatter(Formatter $formatter)
    {
        $this->_formatters = array($formatter);
        throw new DeprecatedNotice(
            "setFormatter is deprecate, please use addFormatter"
        );
    }
    
    /**
     * Gets the fail fast flag value
     * 
     * @return boolean
     */
    public function getFailFast()
    {
        return $this->_failFast;
    }
    
    /**
     * Set fail fast flag
     * 
     * @param boolean $failFast
     */
    public function setFailFast($failFast)
    {
        $this->_failFast = $failFast;
    }
    
    /**
     * Checks whether fails fast is set, and sends a message to the formatter
     * to exit the output
     */
    private function checkFailFast()
    {
        if ($this->getFailFast() === true) {
            $this->notify(new ReporterEvent('exit', '', ''));
        }
    }
    
    /**
     * Get the formatters
     * 
     * @return SplObjectStorage
     */
    public function getFormatters()
    {
        return $this->_formatters;
    }
}