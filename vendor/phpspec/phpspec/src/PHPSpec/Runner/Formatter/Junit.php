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

use PHPSpec\Util\Backtrace;
use PHPSpec\Specification\Result\DeliberateFailure;
use PHPSpec\Runner\Reporter;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @author     Mario Mueller <mario.mueller@xenji.com>
 * @author     Amjad Mohamed <amjad@alliedinsure.net>
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 * @since      File available since release 1.3.0
 */
class Junit extends Progress
{
    
    /**
     * @var \SimpleXMLElement
     */
    private $_xml;
    
    /**
     * Final output
     *
     * @var string
     */
    private $_result;
    
    /**
     * Example elements
     *
     * @var string
     */
    private $_examples;
    
    /**
     * Current example group
     *
     * @var string
     */
    private $_currentGroup;
    
    /**
     * Number of errors
     *
     * @var integer
     */
    private $_errors = 0;
    
    /**
     * Number of failures
     *
     * @var integer
     */
    private $_failures = 0;
    
    /**
     * Number of pending
     *
     * @var integer
     */
    private $_pending = 0;
    
    /**
     * Total of examples
     *
     * @var integer
     */
    private $_total = 0;

    /**
     * Number of examples completed
     *
     * @var integer
     */
    private $_complete = 0;

    /**
     * Number of assertions made
     *
     * @var integer
     */
    private $_assertions = 0;
    
    /**
     * Tell building tool to error with status > 0
     *
     * @var boolean
     */
    private $_errorOnExit = false;

    /**
     * Creates the formatter adding a testsuites root to the xml
     */
    public function __construct (Reporter $reporter)
    {
        parent::__construct($reporter);
        $this->_xml = new \SimpleXMLElement("<testsuites></testsuites>");
    }

    /**
     * Prints the report in a specific format
     */
    public function output ()
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhitespace = false;
        $dom->formatOutput = true;
        $dom->loadXml($this->_xml->asXml());
        echo $dom->saveXML();
    }
    
    /**
     * Opens the testsuite tag
     * @see FormatterAbstract::_startRenderingExampleGroup()
     *
     * @param PHPSpec\Runnner\ReporterEvent $reporterEvent
     */
    protected function _startRenderingExampleGroup($reporterEvent)
    {
        $this->_testSuite = $this->_xml->addChild('testsuite');
        $this->_testSuite->addAttribute('name', $reporterEvent->example);
        $this->_testSuite->addAttribute('file', $reporterEvent->file);
        
        $this->_suiteTime = 0;
        
        $this->_currentGroup = $reporterEvent->example;
    }
    
    /**
     * Finishes rendering an example group
     */
    protected function _finishRenderingExampleGroup()
    {
        $this->_testSuite->addAttribute('tests', $this->_total);
        $this->_testSuite->addAttribute('assertions', $this->_assertions);
        $this->_testSuite->addAttribute('failures', $this->_failures);
        $this->_testSuite->addAttribute('errors', $this->_errors);
        $this->_testSuite->addAttribute('time', $this->_suiteTime);
        
        if ($this->_errors > 0 || $this->_failures > 0) {
            $this->_errorOnExit = true;
        }
        
        $this->_total = 0;
        $this->_failures = 0;
        $this->_errors = 0;
        $this->_pending = 0;
        $this->_complete = 0;
        $this->_assertions = 0;
    }
    
    /**
     * Render examples
     *
     * @param PHPSpec\Runnner\ReporterEvent $reporterEvent
     */
    protected function _renderExamples($reporterEvent)
    {
        $this->_total++;
        $this->_suiteTime += $reporterEvent->time;
        $this->_assertions += $reporterEvent->assertions;
        
        $status = $reporterEvent->status;
        
        $case = $this->_testSuite->addChild('testcase');
        $case->addAttribute('name', $reporterEvent->example);
        $case->addAttribute('class', $this->_currentGroup);
        $case->addAttribute('file', $reporterEvent->file);
        $case->addAttribute('line', $reporterEvent->line);
        $case->addAttribute('assertions', $reporterEvent->assertions);
        $case->addAttribute('time', $reporterEvent->time);
        
        switch ($status) {
        case '.':
            $this->_complete++;
            break;
        case '*':
            $failureMsg = PHP_EOL . $reporterEvent->example
                        . ' (PENDING)' . PHP_EOL;
            $failureMsg .= $reporterEvent->message . PHP_EOL;
            
            $failure = $case->addChild('failure', $failureMsg);
            $failure->addAttribute(
                'type',
                get_class($reporterEvent->exception)
            );
            
            $this->_failures++;
            break;
        case 'E':
            $failureMsg = PHP_EOL . $reporterEvent->example
                        . ' (ERROR)' . PHP_EOL;
            $failureMsg .= $reporterEvent->message . PHP_EOL;
            $failureMsg .= $reporterEvent->backtrace . PHP_EOL;
            
            $error = $case->addChild('error', $failureMsg);
            $error->addAttribute(
                'type',
                get_class($reporterEvent->exception)
            );
            
            $this->_errors++;
            break;
        case 'F':
            $failureMsg = PHP_EOL . $reporterEvent->example
            . ' (FAILED)' . PHP_EOL;
            $failureMsg .= $reporterEvent->message . PHP_EOL;
            $failureMsg .= $reporterEvent->backtrace . PHP_EOL;
            
            $failure = $case->addChild('failure', $failureMsg);
            $failure->addAttribute(
                'type',
                get_class($reporterEvent->exception)
            );
            
            $this->_failures++;
            break;
        }
    }
    
    /**
     * Gets the code based on the exception backtrace
     * 
     * @param \Exception $e
     * @return string
     */
    protected function getCode($e)
    {
        if (!$e instanceof \Exception) {
            return '';
        }
        
        if (!$e instanceof \PHPSpec\Specification\Result\DeliberateFailure) {
            $traceline = Backtrace::getFileAndLine($e->getTrace(), 1);
        } else {
            $traceline = Backtrace::getFileAndLine($e->getTrace());
        }
        $lines = '';
        
        if (!empty($traceline)) {
            $lines .= $this->getLine($traceline, -2);
            $lines .= $this->getLine($traceline, -1);
            $lines .= $this->getLine($traceline, 0, 'offending');
            $lines .= $this->getLine($traceline, 1);
        }
        
        return $lines;
    }

    /**
     * Cleans and returns a line. Removes php tag added to make
     * highlight-string work
     * 
     * @param array   $traceline
     * @param integer $relativePosition
     * @param string  $style
     * @return string
     */
    protected function getLine($traceline, $relativePosition,
                               $style = 'normal')
    {
        $code = Backtrace::readLine(
            $traceline['file'],
            $traceline['line'] + $relativePosition
        );
        return '    ' . $code . PHP_EOL;
    }
    
    /**
     * Exits with status > 0 if there were errors in the specs 
     */
    protected function _onExit()
    {
        if ($this->_errorOnExit) {
            exit(1);
        }
        exit(0);
    } 
    
    /**
     * Creates a testcase
     *
     * @param \SimpleXMLElement $suite
     * @param string $name
     * @param string $class
     * @param string $file
     * @param integer $line
     * @param string $assertions
     * @param integer $executionTime
     *
     * @return SimpleXMLElement
     * <testcase name="testNothing" class="TestDummy"
     * file="/home/mmueller/dev/trivago-php/tests/unit/TestDummy.php"
     * line="12" assertions="1" time="0.005316"/>
     */
    private function createCase (\SimpleXMLElement $suite, $name, $class,
                                 $file, $line, $assertions, $executionTime)
    {
        $child = $suite->addChild('testcase');
        $child->addAttribute('name', $name);
        $child->addAttribute('class', $class);
        $child->addAttribute('file', $file);
        $child->addAttribute('line', $line);
        $child->addAttribute('assertions', $assertions);
        $child->addAttribute('executionTime', $executionTime);
        return $child;
    }

    /**
     * Creates suite 
     *
     * @param $name
     * @param $file
     * @param $testcount
     * @param $assertions
     * @param $failures
     * @param $errors
     * @param $executionTime
     *
     * @return SimpleXMLElement
     * <testsuite name="TestDummy"
     *   file="/home/mmueller/dev/trivago-php/tests/unit/TestDummy.php"
     *   tests="1" assertions="1" failures="0" errors="0" time="0.005316">
     */
    private function createSuite ($name, $file, $testcount, $assertions, 
                                  $failures, $errors, $executionTime)
    {
        $testSuite = $this->_xml->addChild("testsuite");
        $testSuite->addAttribute("name", $name);
        $testSuite->addAttribute("file", $file);
        $testSuite->addAttribute("tests", $testcount);
        $testSuite->addAttribute("assertions", $assertions);
        $testSuite->addAttribute("failures", $failures);
        $testSuite->addAttribute("errors", $errors);
        $testSuite->addAttribute("time", $executionTime);
        return $testSuite;
    }
}
