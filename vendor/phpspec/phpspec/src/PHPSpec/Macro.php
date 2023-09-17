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
namespace PHPSpec;

use PHPSpec\Specification\ExampleGroup;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
abstract class Macro
{
    /**
     * The example Group
     *
     * @var ExampleGroup
     */
    protected $_exampleGroup;
    
    /**
     * Decorates the example group
     *
     * @param ExampleGroup $exampleGroup 
     */
    public function setExampleGroup(ExampleGroup $exampleGroup)
    {
        $this->_exampleGroup = $exampleGroup;
    }
    
    /**
     * Proxies all methods to example group
     *
     * @param string $method 
     * @param array $args 
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(
            array($this->_exampleGroup, $method), $args
        );
    }
    
    /**
     * Proxies all calls to public properties
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->_exampleGroup->$property;
    }
    
    /**
     * Proxies all calls to public properties
     *
     * @param string $property
     * @param mixed  $value
     * @return mixed
     */
    public function __set($property, $value)
    {
        return $this->_exampleGroup->$property = $value;
    }
}