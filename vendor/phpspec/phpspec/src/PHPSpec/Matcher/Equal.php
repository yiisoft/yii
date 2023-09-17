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

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Equal implements Matcher
{

    /**
     * The expected value.
     * 
     * @var unused
     */
    protected $_expected = null;

    /**
     * The actual value
     * 
     * @var mixed
     */
    protected $_actual = null;

    /**
     * The tolerance margin
     * 
     * @var float
     */
    protected $_epsilon = null;

    /**
     * Matcher is usually constructed with the expected value
     * 
     * @param unused $expected
     */
    public function __construct($expected)
    {
        $this->_expected = $expected;
    }

    /**
     * Checks whether actual value is equal to the expected
     * 
     * @param mixed $actual
     * @return boolean
     */
    public function matches($actual, $epsilon = null)
    {
        if (!is_null($epsilon)) {
            $this->_epsilon = $epsilon;
        }

        $this->_actual = $actual;
        $type = gettype($actual);

        // are they both arrays or objects?
        if (is_array($this->_expected) XOR is_array($this->_actual)) {
            return false;
        }
        if (is_object($this->_expected) XOR is_object($this->_actual)) {
            return false;
        }
        if (is_object($this->_expected) && is_object($this->_actual) &&
            (get_class($this->_expected) !== get_class($this->_actual))) {
            return false;
        }
        if (is_object($this->_expected) && is_object($this->_actual) &&
            (get_class($this->_expected) === get_class($this->_actual))) {
            if ($epsilon === true) {
                return $this->_expected === $this->_actual;
            } else {
                return $this->_expected == $this->_actual;
            }
        }

        if (is_array($this->_actual) && is_array($this->_expected)) {
            // compare arrays - we'll curently enforce key equality
            if ($epsilon === true) {
                return $this->_expected === $this->_actual;
            } else {
                return $this->_expected == $this->_actual;
            }
        }
    
        if (!is_array($this->_expected) && !is_array($this->_actual) &&
            !is_object($this->_expected) && !is_object($this->_actual)) {
            $type = $this->_ensureFloatIsDetectedCorrectly($type);
            // scalar comparisons
            switch ($type) {
                case 'integer':
                case 'float':
                    if (is_null($this->_epsilon)) {
                        return ($this->_expected === $this->_actual);
                    } else {
                        // float comparison using expected epsilon
                        return (abs($this->_expected - $this->_actual) <=
                                $this->_epsilon);
                    }
                    break;
            }
        }
        return $this->_expected === $this->_actual;
    }

    /**
     * Ensures that type is detected correctly in case of floats
     * gettype returns integer when you pass a float to it
     * 
     * @param string $type
     * @return string
     */
    private function _ensureFloatIsDetectedCorrectly($type)
    {
        if (is_float($this->_actual)) {
            $type = 'float';
        }
        return $type;
    }

    /**
     * Returns failure message in case we are using should
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        return 'expected ' . var_export($this->_expected, true) . ', got ' .
               var_export($this->_actual, true) . ' (using equal())';
    }

    /**
     * Returns failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return 'expected ' . var_export($this->_actual, true) . ' not to equal '
               . var_export($this->_expected, true) . ' (using equal())';
    }

    /**
     * Returns the matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'equal ' . var_export($this->_expected, true);
    }
}