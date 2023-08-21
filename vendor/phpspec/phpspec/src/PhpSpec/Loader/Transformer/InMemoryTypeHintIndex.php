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

namespace PhpSpec\Loader\Transformer;

final class InMemoryTypeHintIndex implements TypeHintIndex
{
    /**
     * @var array
     */
    private $typehints = array();

    
    public function add(string $class, string $method, string $argument, string $typehint): void
    {
        $this->store($class, $method, $argument, $typehint);
    }

    
    public function addInvalid(string $class, string $method, string $argument, \Exception $exception): void
    {
        $this->store($class, $method, $argument, $exception);
    }

    
    private function store(string $class, string $method, string $argument, $typehint): void
    {
        $class = strtolower($class);
        $method = strtolower($method);
        $argument = strtolower($argument);

        if (!array_key_exists($class, $this->typehints)) {
            $this->typehints[$class] = array();
        }
        if (!array_key_exists($method, $this->typehints[$class])) {
            $this->typehints[$class][$method] = array();
        }

        $this->typehints[$class][$method][$argument] = $typehint;
    }

    /**
     * @return ?string
     */
    public function lookup(string $class, string $method, string $argument)
    {
        $class = strtolower($class);
        $method = strtolower($method);
        $argument = strtolower($argument);

        if (!isset($this->typehints[$class][$method][$argument])) {
            return null;
        }

        if ($this->typehints[$class][$method][$argument] instanceof \Exception) {
            throw $this->typehints[$class][$method][$argument];
        };

        return $this->typehints[$class][$method][$argument];
    }
}
