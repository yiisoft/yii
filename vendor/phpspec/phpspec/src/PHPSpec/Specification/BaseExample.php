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
namespace PHPSpec\Specification;

use PHPSpec\Specification\ExampleGroup;
use PHPSpec\Specification\Result\Exception;
use PHPSpec\Specification\Result\Error;
use PHPSpec\Specification\Result\Pending;
use PHPSpec\Specification\Result\DeliberateFailure;
use PHPSpec\Specification\Result\Failure;

use PHPSpec\Example as ExampleInterface;

use PHPSpec\Util\Filter;
use PHPSpec\Util\ReflectionMethod;

use PHPSpec\Runner\Reporter;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
abstract class BaseExample implements ExampleInterface
{
    /**
     * The example method name
     *
     * @var string
     */
    protected $_methodName;
    
    /**
     * The example group
     *
     * @var PHPSpec\Specification\ExampleGroup
     */
    protected $_exampleGroup;
    
    /**
     * Represents the execution time of the example
     * 
     * @var integer
     */
    protected $_executionTime;
    /**
     * The number assertions made when the example is run
     * 
     * @var integer
     */
    protected $_noOfAssertions;
    
    /**
     * Example keeps a reference to the example group and is created with the
     * example as a reflected method
     * 
     * @param PHPSpec\Specification\ExampleGroup $exampleGroup
     * @param string                             $methodName
     */
    public function __construct(ExampleGroup $exampleGroup, $methodName)
    {
        $this->_methodName = $methodName;
        $this->_exampleGroup = $exampleGroup;
    }
    
    /**
     * Runs the example
     * 
     * @param PHPSpec\Runner\Reporter $reporter
     */
    public function run(Reporter $reporter)
    {
        try {
            $startTime = microtime(true);
            call_user_func(array($this->_exampleGroup, 'before'));
            $this->markExampleAsPendingIfItIsEmpty();
            $this->runExample($reporter);
            $this->closeExample($startTime, $reporter);
        } catch (Failure $failure) {
            $reporter->addFailure($this, $failure);
            $this->closeExample($startTime, $reporter);
            return;
        } catch (Pending $pending) {
            $reporter->addPending($this, $pending);
            $this->closeExample($startTime, $reporter);
            return;
        } catch (Error $error) {
            $reporter->addError($this, $error);
            $this->closeExample($startTime, $reporter);
            return;
        } catch (\Exception $e) {
            $reporter->addException($this, new Exception($e));
            $this->closeExample($startTime, $reporter);
            return;
        }
        $this->_noOfAssertions = $this->_exampleGroup->getNumberOfAssertions();
        $reporter->addPass($this);
    }
    
    /**
     * Gets the description in the following format:
     * 
     * DescribeStringCalculator::itReturnZeroWithNoArguments
     * becomes
     * StringCalculator returns zero with no argument
     * 
     * @return string
     */
    public function getDescription()
    {
        $class = str_replace('Describe', '', get_class($this->_exampleGroup));
        return "$class " . $this->getSpecificationText();
    }
    
    /**
     * Return the specification text taken from method name
     * 
     * itReturnZeroWithNoArguments
     * becomes
     * returns zero with no argument
     * 
     * @param string $methodName
     * @return string
     */
    public function getSpecificationText()
    {
        $methodName = substr($this->_methodName, 2);
        return Filter::camelCaseToSpace($methodName);
    }
    
    /**
     * Returns the method name of the testcase. This method is used in the
     * junit formatter.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->_methodName;
    }
    
    /**
     * Returns the example group. This method is used in the junit formatter.
     * 
     * @return ExampleGroup|PHPSpec\Specification\ExampleGroup
     */
    public function getExampleGroup()
    {
        return $this->_exampleGroup;
    }
    
    /**
     * Returns the execution time for this example.
     *
     * @return float
     */
    public function getExecutionTime()
    {
        return $this->_executionTime;
    }
        
    /**
     * Returns the number of assertions made in this run
     * 
     * @return integer
     */
    public function getNoOfAssertions()
    {
        return $this->_noOfAssertions;
    }

    /**
     * Returns the file name which contains the Spec
     *
     * @return string
     */
    public function getFile()
    {
        $classRefl = new \ReflectionClass($this->_exampleGroup);
        return $classRefl->getFileName();
    }

    /**
     * Returns the line number at which the example starts
     *
     * @return int
     */
    public function getLine()
    {
        $methodRefl = new \ReflectionMethod(
            $this->_exampleGroup, $this->_methodName
        );
        return $methodRefl->getStartLine();
    }

    /**
     * Closes example
     *
     */
    private function closeExample($startTime, $reporter = null)
    {
        call_user_func(array($this->_exampleGroup, 'after'));
        $endTime = microtime(true);
        $this->_executionTime = $endTime - $startTime;
        if (class_exists('Mockery')) {
            try {
                \Mockery::close();
            } catch (\Mockery\CountValidator\Exception $e) {
                $failure = new Failure($e->getMessage());
                if ($reporter->getFailures() === null) {
                    $reporter->setFailures(new \SplObjectStorage);
                }
                $reporter->addFailure($this, $failure);
            }

        }
    }
    
    /**
     * Marks example as pending if it is empty
     */
    protected function markExampleAsPendingIfItIsEmpty()
    {
        $method = new ReflectionMethod(
            $this->_exampleGroup, $this->_methodName
        );
        if ($method->isEmpty()) {
            throw new Pending('empty example');
        }
    }
    
    /**
     * Runs the example
     *
     * @param Reporter $reporter Here for the Interface
     */
    abstract protected function runExample(Reporter $reporter);
}