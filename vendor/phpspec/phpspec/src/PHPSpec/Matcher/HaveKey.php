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
 * @see \PHPSpec\Matcher
 */
use \PHPSpec\Matcher;

class HaveKey implements Matcher
{
    /**
     * Matcher is usually constructed with the expected value
     * 
     * @param array $expected
     */
    public function __construct($expected)
    {
        $this->_expected = $expected;
    }
    
    /**
     * Checks whether actual value exists as a key in the array
     * 
     * @param mixed $actual
     * @return boolean
     */
    public function matches($actual)
    {
        $this->_actual = $actual;
        if (!is_array($actual)) {
            throw new \InvalidArgumentException(
                'Actual value for have key matcher should be array'
            );
        }
        return array_key_exists($this->_expected, $actual);
    }
    
    /**
     * Returns failure message in case we are using should
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        return 'expected to have key ' . var_export($this->_expected, true) .
               ', got key does not exist (using haveKey())';
    }
    
    /**
     * Returns failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return 'expected key ' . var_export($this->_expected, true) .
               ' not to exist (using haveKey())';
    }
    
    /**
     * Returns the matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'have key ' . var_export($this->_expected, true);
    }
}