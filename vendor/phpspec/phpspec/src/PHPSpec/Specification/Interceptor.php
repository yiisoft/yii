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

use PHPSpec\Specification\Result\Failure;
use PHPSpec\Specification\Result\Error;
use PHPSpec\Specification\Interceptor\InterceptorFactory;
use PHPSpec\Matcher\MatcherRepository;
use PHPSpec\Matcher\UserDefined as UserDefinedMatcher;
use PHPSpec\Matcher\InvalidMatcher;
use PHPSpec\Matcher\InvalidMatcherType;
use PHPSpec\Matcher\InvalidMatchingResult;
use PHPSpec\Matcher\MatcherFactory;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
abstract class Interceptor
{
    /**
     * Expectation forces matcher result to fail if it returns false
     */
    const SHOULD = 'should';
    
    /**
     * Expectation forces matcher result to fail if it returns true
     */
    const SHOULD_NOT = 'should not';
    
    /**
     * The actual value
     *
     * @var mixed
     */
    protected $_actualValue;
    
    /**
     * The expectation
     *
     * @var mixed
     */
    protected $_expectation;
    
    /**
     * The expected value
     *
     * @var mixed
     */
    protected $_expectedValue;
    
    /**
     * Holds the interceptors created from within this Interceptor
     *
     * array <PHPSpec\Specification\Interceptor>
     */
    protected $_subInterceptors = array();
    
    /**
     * Number of assertions made
     *
     * @var integer
     */
    protected $_noOfAssertions = 0;
    
    /**
     * The matcher factory
     *
     * @var PHPSpec\Matcher\MatcherFactory
     */
    protected $_matcherFactory;
    
    protected $_matcher;
    
    /**
     * Creates an interceptor with the intercepted actual value
     * 
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->_actualValue = $value;
    }
    
    /**
     * Checks whether a expectation is being invoked
     * 
     * @param string $attribute
     * 
     * @return \PHPSpec\Specification\Interceptor|mixed
     */
    public function __get($attribute)
    {
        switch ($attribute) {
            case 'should' :
                $this->_expectation = self::SHOULD;
                return $this;
            case 'shouldNot' :
                $this->_expectation = self::SHOULD_NOT;
                return $this;
            default :
                if (method_exists($this->_actualValue, '__get')) {
                    $parentInterceptor = new \ReflectionMethod(
                        $this->_actualValue, '__get'
                    );
                    $value = $parentInterceptor->invokeArgs(
                        $this->_actualValue, array($attribute)
                    );

                    return InterceptorFactory::create($value, $this);
                }
        }
    }
    
    /**
     * Invokes a matcher or proxies the method call to the intercepted object
     * magic call method, if one exists
     * 
     * @param string $method
     * @param array $args
     * @return boolean|mixed
     */
    public function __call($method, $args)
    {
        if (MatcherRepository::has($method)) {
            $this->performMatchingWithUserDefinedMatcher($method, $args);
            return true;
        }
        
        try {
            $this->setExpectedValue($args);
            $this->_matcher = $this->getMatcherFactory()
                                   ->create($method, $args);
            $this->performMatching();
            return true;
        } catch (InvalidMatcherType $e) {
            throw new InvalidMatcher($e->getMessage());
        } catch (InvalidMatcher $e) {

        }
                
        if ($this->callingExpectationsAsMethods($method)) {
            $this->throwErrorExpectationsAreProperties();
        }
        
        if ($this->interceptedIsNotAnObject() &&
            $this->anExpectationHasBeenUsed()) {
            $this->throwNotAMatcherException($method);
        }
    }
    
    /**
     * Sets an Expected value with which to instantiate any new Matcher
     *
     * @param mixed $value
     * @return null
     */
    public function setExpectedValue($value)
    {
        $this->_expectedValue = $value;
    }
    
    /**
     * Gets an Expected value with which to instantiate any new Matcher
     *
     * @return mixed
     */
    public function getExpectedValue()
    {
        return $this->_expectedValue;
    }
    
    /**
     * Sets the Actual value
     *
     * @param mixed $value
     * @return null
     */
    public function setActualValue($value)
    {
        $this->_actualValue = $value;
    }
    
    /**
     * Gets an Expected value with which to instantiate any new Matcher
     *
     * @return mixed
     */
    public function getActualValue()
    {
        return $this->_actualValue;
    }
    
    /**
     * Returns the Expectation ruling how Matcher results are
     * interpreted (whether a matcher boolean result counts as a pass
     * or a fail).
     *
     * @return string (self::SHOULD | self::SHOULD_NOT)
     */
    public function getExpectation()
    {
        return $this->_expectation;
    }
    
    /**
     * Returns the number of assertions made in this spec
     * 
     * @return integer
     */
    public function getNumberOfAssertions()
    {
        $assertions = $this->_noOfAssertions;
        
        foreach ($this->_subInterceptors as $interceptors) {
            $assertions += $interceptors->getNumberOfAssertions();
        }
        
        return $assertions;
    }
    
    /**
     * Stores the interceptors sent from InterceptorFactory
     * 
     * @param Interceptor $interceptor 
     */
    public function addSubInterceptor(Interceptor $interceptor)
    {
        $this->_subInterceptors[] = $interceptor;
    }
    
    /**
     * Adds a matcher
     * 
     * @param string $matcher
     */
    public function addMatcher($matcher)
    {
        $this->_matchers[] = $matcher;
    }
    
    /**
     * Adds many matcher at once
     * 
     * @param array $matchers
     */
    public function addMatchers(array $matchers)
    {
        $this->_matchers = array_merge($this->_matchers, $matchers);
    }
    
    /**
     * Sets the matchers. Replaces existing ones (!)
     *
     * @param array $matchers
     */
    public function setMatchers(array $matchers)
    {
        $this->_matchers = $matchers;
    }
    
    /**
     * Checks whether a matcher is registered with the interceptor
     *
     * @param string $matcher 
     * @return boolean
     */
    protected function matcherIsRegistered($matcher)
    {
        return in_array($matcher, $this->_matchers);
    }
    
    /**
     * Performs matching against registered matcher
     *
     * @param string $matcher 
     * @param mixed $expected 
     */
    protected function performMatchingWithRegisteredMatcher($matcher, $expected)
    {
        $this->setExpectedValue($expected);
        $this->createMatcher($matcher);
        $this->performMatching();
    }
    
    /**
     * Performs matching against user defined matcher
     *
     * @param string $matcher 
     * @param mixed $expected 
     */
    protected function performMatchingWithUserDefinedMatcher($matcher,
        $expected)
    {
        $this->setExpectedValue($expected);
        $expected = !is_array($this->getExpectedValue()) ?
                    array($this->getExpectedValue()) :
                    $this->getExpectedValue();
        $this->_matcher = new UserDefinedMatcher($matcher, $expected);
        $this->performMatching();
    }
    
    /**
     * Checks if intercepted is an object
     *
     * @return boolean
     */
    protected function interceptedIsNotAnObject()
    {
        return !$this instanceof Interceptor\Object;
    }
    
    /**
     * Checks if an expectation has been used
     *
     * @return boolean
     */
    protected function anExpectationHasBeenUsed()
    {
        return $this->_expectation !== null;
    }
    
    /**
     * Throws an InvalidMatcher exception
     *
     * @param string $method 
     * @throws InvalidMatcher
     */
    protected function throwNotAMatcherException($method)
    {
        throw new InvalidMatcher(
            "Call to undefined method $method"
        );
    }
    
    /**
     * Checks weather we are using should and shouldNot as methods
     *
     * @param string $method 
     * @return boolean  
     */
    protected function callingExpectationsAsMethods($method)
    {
        return $method === 'should' || $method === 'shouldNot';
    }
    
    /**
     * Informs the users should and shouldNot are used as properties not
     * methods
     *
     * @throws Error
     */
    protected function throwErrorExpectationsAreProperties()
    {
        throw new Error(
            'Missing expectation "should" or "shouldNot". ' .
            'Make sure you use them as properties and not as methods. ' .
            'e.g. $result->should->be(42) and note $result->should()->be(42)'
        );
    }
    
    /**
     * Performs a Matcher operation and set the returned boolean result for
     * further analysis, e.g. comparison to the boolean Expectation.
     *
     * @param array $args
     * @return null
     */
    protected function performMatching()
    {
        $this->_noOfAssertions++;
        
        $actual = $this->getActualValue();

        if (is_array($actual) && $this->_composedActual) {
            $args = $actual;
        } else {
            $args = array($actual);
        }

        $result = call_user_func_array(
            array($this->_matcher, 'matches'), $args
        );
        
        if ($this->getExpectation() === self::SHOULD) {
            if ($result === false) {
                throw new Failure($this->_matcher->getFailureMessage());
            } elseif ($result !== true) {
                $matcher = get_class($this->_matcher) . ":matches()";
                throw new InvalidMatchingResult("$matcher must return boolean");
            }
        } elseif ($this->getExpectation() === self::SHOULD_NOT) {
            if ($result === true) {
                throw new Failure($this->_matcher->getNegativeFailureMessage());
            } elseif ($result !== false) {
                $matcher = get_class($this->_matcher) . ":matches()";
                throw new InvalidMatchingResult("$matcher must return boolean");
            }
        } elseif (empty($this->_expectation)) {
            throw new Error(
                'Missing expectation "should" or "shouldNot". ' .
                'Make sure you use them as properties and not as methods.'
            );
        }
    }
    
    /**
     * Sets the matcher factory
     *
     * @param PHPSpec\Matcher\MatcherFactory $matcherFactory
     */
     public function setMatcherFactory(MatcherFactory $matcherFactory)
     {
         $this->_matcherFactory = $matcherFactory;
     }

     /**
      * Returns the Matcher Factory
      *
      *  @return PHPSpec\Matcher\MatcherFactory
      */
      public function getMatcherFactory()
      {
          if ($this->_matcherFactory === null) {
              $this->_matcherFactory = new MatcherFactory;
          }
          return $this->_matcherFactory;
      }

}