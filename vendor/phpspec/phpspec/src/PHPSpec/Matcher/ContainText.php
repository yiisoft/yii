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
 *                                    Luis Cordova
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
 *                                     Luis Cordova
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class ContainText implements Matcher
{
   /**
    * The expected value
    *
    * @var mixed
    */
    protected $_expected = null;

   /**
    * The actual value
    *
    * @var object
    */
    protected $_actual = null;

    /**
     * Matcher is usually constructed with the expected value
     *
     * @param array $expected
     */
    public function __construct($expected)
    {
        if (!is_string($expected)) {
            throw new \InvalidArgumentException(
                'Expected value for contain text matcher should be string'
            );
        }
        $this->_expected = $expected;
    }

    /**
     * Checks whether actual text exists inside string
     *
     * @param mixed $actual
     * @return boolean
     */
    public function matches($actual)
    {
        $this->_actual = $actual;
        return strpos($actual, $this->_expected) !== false;
    }

    /**
     * Returns failure message in case we are using should
     *
     * @return string
     */
    public function getFailureMessage()
    {
        return 'expected to contain:' . PHP_EOL . '\'' . $this->_expected .
               '\', got:' . PHP_EOL . var_export($this->_actual, true) .
               ' (using containText())';
    }

    /**
     * Returns failure message in case we are using should not
     *
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return 'expected text:' . PHP_EOL .
               var_export($this->_expected, true) .
               ' to not be contained in:' . PHP_EOL .
               var_export($this->_actual, true) .
               ' (using containText())';
    }

    /**
     * Returns the matcher description
     *
     * @return string
     */
    public function getDescription()
    {
        return 'contain text ' . var_export($this->_actual, true);
    }
}