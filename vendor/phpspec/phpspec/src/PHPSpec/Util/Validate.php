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
namespace PHPSpec\Util;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Validate
{
    /**
     * Validates argument is a closure
     *
     * @param mixed   $argument
     * @param integer $order
     * @param string  $caller
     * @return \Closure
     * @throws \PHPSpec\Exception
     */
    public static function isClosure($argument, $order, $caller)
    {
        if ($argument instanceof \Closure) {
            return $argument;
        }
        self::message($order, "closure", $argument, $caller);
    }
    
    /**
     * Validates argument is an array
     *
     * @param mixed   $argument
     * @param integer $order
     * @param string  $caller
     * @return array
     * @throws \PHPSpec\Exception
     */
    public static function isArray($argument, $order, $caller)
    {
        if (is_array($argument)) {
            return $argument;
        }
        self::message($order, "array", $argument, $caller);
    }
    
    /**
     * Throws an exception when argument is not the expected type
     *
     * @param integer $order
     * @param string  $type
     * @param mixed   $argument
     * @param string  $caller
     * @throws \PHPSpec\Exception
     */
    public static function message($order, $type, $argument, $caller)
    {
        throw new \PHPSpec\Exception(
            "$caller $order argument must be a $type. " .
            ucfirst(
                is_object($argument) ?
                get_class($argument) : gettype($argument)
            ) . ' was passed instead.'
        );
    }
}