<?php

namespace spec\PHPSpec\Specification\Interceptor;

class Calculator
{
    public function __call($method, $args)
    {
        if ($method === 'add') {
            return array_sum($args);
        }
    }
}