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
namespace PHPSpec\Matcher;

/**
 * @see \PHPSpec\Specification\Result\Failure
 */
use PHPSpec\Specification\Result\Failure as FailedMatcherException;

/**
 * @see \PHPSpec\Matcher\MatcherRepository
 */
use \PHPSpec\Matcher\MatcherRepository;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class UserDefined
{
    /**
     * Name of the user defined matcher
     * 
     * @var string
     */
    protected $_matcher;
    
    /**
     * The expected value.
     * 
     * @var mixed
     */
    protected $_expected = null;

    /**
     * The actual value
     * 
     * @var mixed
     */
    protected $_actual = null;
    
    /**
     * Created with the name of the matcher and the expected value. Expected
     * value is passed as an array of expected values (arguments)
     * 
     * @param string $matcher
     * @param array  $expected
     */
    public function __construct($matcher, $expected)
    {
        $this->_matcher = $matcher;
        $this->_expected = $expected;
    }
    
    /**
     * Performs the matching between expected and actual
     * 
     * @param mixed $actual
     * @throws PHPSpec\Specification\Result\Failure
     * @return boolean
     */
    public function matches($actual)
    {
        $userExpect = MatcherRepository::get($this->_matcher);
        $match = call_user_func_array($userExpect, $this->_expected);
        $result = $match['match']($actual);
        $this->_actual = $actual;
        return $result;
    }
    
    /**
     * Returns user defined message in case of failure
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        $matcher = MatcherRepository::get($this->_matcher);
        $match = call_user_func_array($matcher, $this->_expected);
        return $match['failure_message_for_should']($this->_actual);
    }
    
   /**
     * Returns user defined failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        $matcher = MatcherRepository::get($this->_matcher);
        $match = call_user_func_array($matcher, $this->_expected);
        return $match['failure_message_for_should_not']($this->_actual);
    }
    
   /**
     * Returns the user defined matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        $matcher = MatcherRepository::get($this->_matcher);
        $match = call_user_func_array($matcher, $this->_expected);
        return $match['description']($this->_actual);
    }
}