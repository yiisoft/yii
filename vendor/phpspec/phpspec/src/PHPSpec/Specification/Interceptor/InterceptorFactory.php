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
namespace PHPSpec\Specification\Interceptor;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class InterceptorFactory
{
    /**
     * Creates an interceptor
     * 
     * @return \PHPSpec\Specification\Interceptor
     */
    public static function create()
    {
        $args = func_get_args();
        $value = array_shift($args);
        $interceptor = array_shift($args);
        
        if (is_callable($value)) {
            $spec = new Closure($value);
            
        } elseif ((is_string($value) && class_exists($value, true)) ||
                   is_object($value)) {
            $spec = new Object($value);
        } elseif (is_array($value)) {
            $spec = new ArrayVal($value);
        } else {
            $spec = new Scalar($value);
        }

        if (!is_null($interceptor)) {
            $interceptor->addSubInterceptor($spec);
        }
        
        return $spec;
    }
}