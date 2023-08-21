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

class DelayedCall
{
    /**
     * @var callable
     */
    private $callable;

    
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    
    public function __call(string $method, array $arguments)
    {
        return \call_user_func($this->callable, $method, $arguments);
    }
}
