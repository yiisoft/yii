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
class ReflectionMethod
{
    /**
     * Object or class name
     *
     * @var object|string
     */
    protected $_objectOrClassName;
    
    /**
     * The name of the method
     *
     * @var string
     */
    protected $_methodName;
    
    /**
     * Constructs a reflection method object
     *
     * @param string|object $objectOrClassName 
     * @param string        $methodName 
     */
    public function __construct($objectOrClassName, $methodName)
    {
        $this->_objectOrClassName = $objectOrClassName;
        $this->_methodName = $methodName;
    }
    
    /**
     * Checks whether method is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        $method = new \ReflectionMethod(
            $this->_objectOrClassName,
            $this->_methodName
        );
        $methodString = explode("\n", (string)$method);
        preg_match('/(@@ )(.*\.php)( )(\d+)(\D*)(\d+)/', $methodString[ count($methodString) - 3 ], $matches);
        list ($path, $start, $end) = array(
            $matches[2], $matches[4], $matches[6]
        );
        
        $code = '';
        for ($i = $start; $i <= $end; $i++) {
            $code .= Backtrace::readLine($path, $i);
        }

        $methodBodyPattern = "/function.*(?:{(.*)}|;)/sxU";
        preg_match($methodBodyPattern, $code, $matches);
        $extract = isset($matches[1]) ? $matches[1] : '';
        return empty($extract);
    }
}