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
 * @see \PHPSpec\Matcher\BeTrue
 */
use PHPSpec\Matcher\BeTrue;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Predicate extends BeTrue
{
   
    /**
     * the object holding the method
     * 
     * @var object
     */
    protected $_object = null;

    /**
     * The name of the method being inflected
     * 
     * @var string
     */
    protected $_method = null;

    /**
     * The name used in the matching to verify the 
     * 
     * @var unknown_type
     */
    protected $_predicateCall = null;
    
    /**
     * Matcher is usually constructed with the expected value
     * 
     * @param unused $expected
     */
    public function __construct($expected)
    {
        parent::__construct($expected);
    }

    /**
     * Sets the object with the method's name being inflected as a matcher
     * 
     * @param object $object
     * @throws \PHPSpec\Exception
     */
    public function setObject($object)
    {
        if (!is_object($object)) {
            throw new \PHPSpec\Exception('not an object');
        }
        $this->_object = $object;
    }

    /**
     * Sets the name of the method which is being inflected as a matcher
     * 
     * @param unknown_type $method
     */
    public function setMethodName($method)
    {
        $this->_method = $method;
    }

    /**
     * Sets the inflected name
     * 
     * @param string $callName
     */
    public function setPredicateCall($callName)
    {
        $this->_predicateCall = $callName;
    }

    /**
     * Checks whether the value returned by the method is true
     * 
     * @param mixed $actual
     * @return boolean
     */
    public function matches($unusedParamSoIgnore)
    {
        $this->_actual = $this->_object->{$this->_method}();
        return $this->_expected === $this->_actual;
    }

    /**
     * Returns failure message in case we are using should
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        return 'expected TRUE, got FALSE or non-boolean (using ' .
               $this->_predicateCall . '())';
    }

    /**
     * Returns failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return 'expected FALSE or non-boolean not TRUE (using ' .
               $this->_predicateCall . '())';
    }

    /**
     * Returns the matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        $call = $this->_predicateCall;
        $terms = preg_split(
            "/(?=[[:upper:]])/", $call, -1, PREG_SPLIT_NO_EMPTY
        );
        $termsLowercase = array_map('strtolower', $terms);
        return implode(' ', $termsLowercase);
    }
}