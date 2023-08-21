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
namespace PHPSpec\Runner\Formatter;

use PHPSpec\Runner\Formatter;
use PHPSpec\Runner\Reporter;

use PHPSpec\Specification\Result\DeliberateFailure;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Progress extends FormatterAbstract
{
    
    /**
     * The reporter
     *
     * @var \PHPSpec\Runner\Reporter
     */
    protected $_reporter;
    
    /**
     * Shows colors
     * 
     * @var boolean
     */
    protected $_showColors = false;

   /**
    * Enable backtrace
    * 
    * @var boolean
    */
    protected $_enableBacktrace = false;
    
    private $_errorOnExit = false;
    
    /**
     * Creates a progress formatter, decorates a reporter
     * 
     * @param \PHPSpec\Runner\Reporter $reporter
     */
    public function __construct(Reporter $reporter)
    {
        $this->_reporter = $reporter;
    }
    
    /**
     * Prints formatted results
     */
    public function output()
    {
        if ($this->justShowAMessage()) {
            return;
        }
        
        $this->printLines(1);
        $this->printLineInProgressFormatter();
        
        $this->printPending();
        $this->printFailures();
        $this->printErrors();
        $this->printExceptions();
        
        $this->printRuntime();
        $this->printTotals();
    }
    
    /**
     * Adds a new line to the output
     */
    public function printLineInProgressFormatter()
    {
        if (get_class($this) === 'PHPSpec\Runner\Formatter\Progress') {
            $this->putln("");
        }
    }
    
    /**
     * Sets show colors
     * 
     * @param boolean $showColors
     */
    public function setShowColors($showColors)
    {
        $this->_showColors = $showColors;
    }
    
    /**
     * Sets enable backtrace
     * 
     * @param boolean $enableBacktrace
     */
    public function setEnableBacktrace($enableBacktrace)
    {
        $this->_enableBacktrace = $enableBacktrace;
    }
    
    /**
     * Show colors
     * 
     * @return boolean
     */
    public function showColors()
    {
        return $this->_showColors;
    }
    
    /**
     * Print as many new lines as required
     * 
     * @param integer $linesToPrint
     */
    protected function printLines($linesToPrint)
    {
        $this->put(str_repeat(PHP_EOL, $linesToPrint));
    }
    
    /**
     * Prints pending examples
     */
    protected function printPending()
    {
        if ($this->_reporter->hasPendingExamples()) {
            $this->printIncrementalResult(
                'Pending', $this->_reporter->getPendingExamples(), 1
            );
        }
    }
    
    /**
     * Prints failed examples
     */
    protected function printFailures()
    {
        if ($this->_reporter->hasFailures()) {
            $this->printIncrementalResult(
                'Failures', $this->_reporter->getFailures()
            );
        }
    }
    
    /**
     * Prints examples with errors
     */
    protected function printErrors()
    {
        if ($this->_reporter->hasErrors()) {
            $this->printIncrementalResult(
                'Errors', $this->_reporter->getErrors()
            );
        }
    }
    
    /**
     * Prints exemplos with exceptions
     */
    protected function printExceptions()
    {
        if ($this->_reporter->hasExceptions()) {
            $this->printIncrementalResult(
                'Exceptions', $this->_reporter->getExceptions()
            );
        }
    }
    
    /**
     * Prints the results of a particular type
     * 
     * @param string            $type
     * @param \SplObjectStorage $items
     * @param integer           $space
     */
    protected function printIncrementalResult($type, $items, $space = 2)
    {
        $this->put("$type:");
        $this->printLines($space);
        $increment = 1;
        $items->rewind();
        while ($items->valid()) {
            $item = $items->current();
            $example = $items->getInfo();
            $method = "getMessageFor$type";
            $message = $this->$method(
                $increment, $item, $example, $this->_enableBacktrace
            );
            $this->putln($message);
            $items->next();
            $increment++;
        } 
    }
    
    /**
     * Gets a message for a failed example
     * 
     * @param integer $increment
     * @param \PHPSpec\Specification\Result\Failure $failure
     * @param \PHPSpec\Specification\Example $example
     * @param boolean $backtrace
     * @return string
     */
    protected function getMessageForFailures($increment, $failure, $example,
                                             $backtrace)
    {
        $snippet = 1;
        if ($failure instanceof DeliberateFailure) {
            $snippet = 0;
        }
         $trace = $backtrace ? null : 3;
        return <<<MESSAGE
  $increment) {$example->getDescription()}
     {$this->red('Failure\Error: ' . $failure->getSnippet($snippet))}
     {$this->red($failure->getMessage())}
{$this->grey($failure->prettyTrace($trace))}
MESSAGE;
    }
    
    /**
     * Gets a message for an example with errors
     * 
     * @param integer $increment
     * @param \PHPSpec\Specification\Result\Error $error
     * @param \PHPSpec\Specification\Example $example
     * @param boolean $backtrace
     * @return string
     */
    protected function getMessageForErrors($increment, $error, $example,
                                           $backtrace)
    {
        $trace = $backtrace ? null : 3;
        return <<<MESSAGE
  $increment) {$example->getDescription()}
     {$this->red(get_class($error) . ': ' . $error->getSnippet(1))}
     {$this->red($error->getErrorType() . ': ' . $error->getMessage())}
{$this->grey($error->prettyTrace($trace))}
MESSAGE;
    }
    
    /**
     * Gets a message for an example with exceptions
     * 
     * @param integer $increment
     * @param \Exception $exception
     * @param \PHPSpec\Specification\Example $example
     * @param boolean $backtrace
     * @return string
     */
    protected function getMessageForExceptions($increment, $exception, $example,
                                               $backtrace)
    {
        $trace = $backtrace ? null : 3;
        return <<<MESSAGE
  $increment) {$example->getDescription()}
     {$this->red( 'Failure\Exception: ' . $exception->getSnippet(1))}
     {$this->red($exception->getExceptionClass() . ': ' .
     $exception->getMessage())}
{$this->grey($exception->prettyTrace($trace))}
MESSAGE;
    }
    
    /**
     * Gets a message for a pending example
     * 
     * @param integer $increment
     * @param \PHPSpec\Specification\Result\Pending $pending
     * @param \PHPSpec\Specification\Example $example
     * @param boolean $backtrace
     * @return string
     */
    protected function getMessageForPending($increment, $pending, $example,
                                            $backtrace)
    {
        return <<<MESSAGE
  {$this->yellow($example->getDescription())}
     {$this->grey('# ' . $pending->getMessage())}
{$this->grey($pending->prettyTrace(1))}
MESSAGE;
    }
    
    /**
     * Checks if reporter has a message, shows it and returns true. Returns
     * false if there is nothing to show 
     * 
     * @return boolean
     */
    protected function justShowAMessage()
    {
        if ($this->_reporter->hasMessage()) {
            $this->put($this->_reporter->getMessage());
            return true;
        }
        return false;
    }
    
    /**
     * Prints runtime
     */
    protected function printRuntime()
    {
        $this->putln(
            "Finished in " . $this->_reporter->getRuntime() . " seconds"
        );
    }
    
    /**
     * Prints totals
     */
    protected function printTotals()
    {
        $this->putln($this->getTotals());
    }
    
    /**
     * Gets totals to print
     * 
     * @return string
     */
    protected function getTotals()
    {
        $failures = $this->_reporter->getFailures()->count();
        $errors = $this->_reporter->getErrors()->count();
        $pending = $this->_reporter->getPendingExamples()->count();
        $exceptions = $this->_reporter->getExceptions()->count();
        $passing = count($this->_reporter->getPassing());
        
        $total = $failures + $errors + $pending + $exceptions + $passing;
        
        if (($failures + $errors + $pending + $exceptions) > 0) {
            $this->_errorOnExit = true;
        }
        
        $totals = "$total example" . ($total !== 1 ? "s" : "");
        if ($failures) {
            $plural = $failures !== 1 ? "s" : "";
            $totals .= ", $failures failure$plural";
        }
        if ($errors) {
            $plural = $errors !== 1 ? "s" : "";
            $totals .= ", $errors error$plural";
        }
        if ($exceptions) {
            $plural = $exceptions !== 1 ? "s" : "";
            $totals .= ", $exceptions exception$plural";
        }
        if ($pending) {
            $plural = $pending !== 1 ? "s" : "";
            $totals .= ", $pending pending$plural";
        }
        if ($failures || $errors || $exceptions) {
            $totals = $this->red($totals);
        } elseif ($pending) {
            $totals = $this->yellow($totals);
        } elseif ($passing) {
            $totals = $this->green($totals);
        }
        return $totals;
    }
    
    /**
     * Not required for this formatter
     * @param unknown_type $reporterEvent
     */
    protected function _startRenderingExampleGroup($reporterEvent)
    {
    }
    
    /**
     * Not required for this formatter
     */
    protected function _finishRenderingExampleGroup()
    {
    }
    
    /**
     * Prints a single status
     * 
     * @param string $status
     */
    protected function _renderExamples($reporterEvent)
    {
        $status = $reporterEvent->status;
        
        switch($status) {
            case '.':
                $this->put($this->green($status));
                break;
            case '*':
                $this->put($this->yellow($status));
                break;
            case 'E':
            case 'F':
                $this->put($this->red($status));
                break;
        }
    }
    
    /**
     * Decorates output with green ascii if --color is set
     * 
     * @param string $output
     * @return string
     */
    public function green($output)
    {
        if ($this->showColors()) {
            $output = Color::green($output);
        }
        return $output;
    }
    
    /**
     * Decorates output with red ascii if --color is set
     * 
     * @param string $output
     * @return string
     */
    public function red($output)
    {
        if ($this->showColors()) {
            $output = Color::red($output);
        }
        return $output;
    }
    
    /**
     * Decorates output with grey ascii if --color is set
     * 
     * @param string $output
     * @return string
     */
    public function grey($output)
    {
        if ($this->showColors()) {
            $output = Color::grey($output);
        }
        return $output;
    }
    
    /**
     * Decorates output with yellow ascii if --color is set
     * 
     * @param string $output
     * @return string
     */
    public function yellow($output)
    {
        if ($this->showColors()) {
            $output = Color::yellow($output);
        }
        return $output;
    }
    
    /**
     * Exists with the right status
     */
    protected function _onExit()
    {
        if ($this->_errorOnExit) {
            exit(1);
        }
        exit(0);
    }
    
    /**
     * Outputs to standard output
     * 
     * @param string $output
     */
    public function put($output)
    {
        Stdout::put($output);
    }
    
    /**
     * Outputs to standard output and adds a new line in the end
     * 
     * @param string $output
     */
    public function putln($output)
    {
        Stdout::put($output . PHP_EOL);
    }
}