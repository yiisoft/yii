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

use PHPSpec\Specification\Interceptor\InterceptorFactory;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class SpecIterator implements \Iterator
{
    
    /**
     * The value to be iterated upon
     *
     * @var array|\Iterator
     */
    protected $_value;
    
    /**
     * Creates the iterator
     * 
     * @param array|\Iterator $value
     */
    public function __construct($value)
    {
        $this->_value = $value;
    }
    
    /**
     * Moves the internal pointer forward, returns its value
     * 
     * @return \PHPSpec\Specification\Interceptor
     */
    public function next()
    {
        return InterceptorFactory::create(next($this->_value));
    }
    
    /**
     * Gets current value
     * 
     * @return \PHPSpec\Specification\Interceptor
     */
    public function current()
    {
        return InterceptorFactory::create(current($this->_value));
    }
    
    /**
     * Whether the current position is still valie
     * 
     * return boolean
     */
    public function valid()
    {
        return !@is_null($this->_value[key($this->_value)]);
    }
    
    /**
     * Gets the current key
     * 
     * @return \PHPSpec\Specification\Interceptor
     */
    public function key()
    {
        return InterceptorFactory::create(key($this->_value));
    }
    
    /**
     * Rewinds the pointer
     */
    public function rewind()
    {
        reset($this->_value);
    }

    /**
     * Aplies a closure to each of the elements of the iterator
     * 
     * @param \Closure $yield
     * @throws \PHPSpec\Exception
     * 
     * return array
     */
    public function withEach(\Closure $yield)
    {
        $elements = array();
        if (is_array($this->_value) || $this->_value instanceof \Iterator) {
            foreach ($this->_value as $key => $value) {
                $elements[] = $yield(InterceptorFactory::create($value));
            }
            return $elements;
        }
        throw new \PHPSpec\Exception('Not an traversable item');
    }
}