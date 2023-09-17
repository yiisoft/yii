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
class BeTrue implements Matcher
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
     * Matcher is usually constructed with the expected value
     * but beTrue() is itself the expectation
     * 
     * @param unused $expected
     */
    public function __construct($expected = true)
    {
        $this->_expected = true;
    }

    /**
     * Checks whether actual value is true
     * 
     * @param mixed $actual
     * @return boolean
     */
    public function matches($actual)
    {
        $this->_actual = $actual;
        return $this->_expected === $this->_actual;
    }

    /**
     * Returns failure message in case we are using should
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        return 'expected true, got false or non-boolean (using beTrue())';
    }

    /**
     * Returns failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return 'expected false or non-boolean not true (using beTrue())';
    }

    /**
     * Returns the matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'be true';
    }
}