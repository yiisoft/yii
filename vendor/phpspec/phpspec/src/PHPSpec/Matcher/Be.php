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
 * @see \PHPSpec\Matcher\Equal
 */
use \PHPSpec\Matcher\Equal;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Be extends Equal implements Matcher
{

    /**
     * Returns failure message in case we are using should
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        return 'expected ' . var_export($this->_expected, true) .
               ', got ' . var_export($this->_actual, true) . ' (using be())';
    }

    /**
     * Returns failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return 'expected ' . var_export($this->_actual, true) . ' not to be ' .
               var_export($this->_expected, true) . ' (using be())';
    }

    /**
     * Returns the matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'be ' . var_export($this->_expected, true);
    }
}