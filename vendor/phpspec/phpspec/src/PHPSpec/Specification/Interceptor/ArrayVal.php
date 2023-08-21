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

use PHPSpec\Specification\Interceptor;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class ArrayVal extends Interceptor implements \ArrayAccess
{
    /**
     * Checks whether actual value has a key
     *
     * @param mixed $offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        if (isset($this->_actualValue[$offset])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Gets the value of existing offset in the actual value
     *
     * @param mixed $offset
     *
     * @return PHPSpec\Specification\Interceptor
     */
    public function offsetGet($offset)
    {
        if (! isset($this->_actualValue[$offset])) {
            trigger_error("Undefined index: $offset", E_USER_NOTICE);
        }
        
        return InterceptorFactory::create($this->_actualValue[$offset]);
    }
    
    /**
     * Sets the value for a offset in the actual value
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->_actualValue[$offset] = $value;
    }
    
    /**
     * Unsets a position in the actual value array
     *
     * @param mixed $offset 
     */
    public function offsetUnset($offset)
    {
        if (isset($this->_actualValue[$offset])) {
            unset($this->_actualValue[$offset]);
        }
    }
}