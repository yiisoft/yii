<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpSpec\Wrapper;

use Prophecy\Prophecy\ObjectProphecy;

final class Collaborator implements ObjectWrapper
{
    /**
     * @var ObjectProphecy
     */
    private $prophecy;

    
    public function __construct(ObjectProphecy $prophecy)
    {
        $this->prophecy  = $prophecy;
    }

    
    public function beADoubleOf(string $classOrInterface): void
    {
        if (interface_exists($classOrInterface)) {
            $this->prophecy->willImplement($classOrInterface);
        } else {
            $this->prophecy->willExtend($classOrInterface);
        }
    }

    /**
     * @param array $arguments
     */
    public function beConstructedWith(array $arguments = null): void
    {
        $this->prophecy->willBeConstructedWith($arguments);
    }

    
    public function implement(string $interface): void
    {
        $this->prophecy->willImplement($interface);
    }

    
    public function __call(string $method, array $arguments)
    {
        return \call_user_func_array(array($this->prophecy, '__call'), array($method, $arguments));
    }

    
    public function __set(string $parameter, $value)
    {
        $this->prophecy->$parameter = $value;
    }

    
    public function __get(string $parameter)
    {
        return $this->prophecy->$parameter;
    }

    /**
     * @return object
     */
    public function getWrappedObject()
    {
        return $this->prophecy->reveal();
    }
}
