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

namespace PhpSpec\Exception;

use ReflectionFunctionAbstract;

/**
 * PhpSpec base exception
 */
class Exception extends \Exception
{
    /**
     * @var ReflectionFunctionAbstract
     */
    private $cause;

    
    public function getCause(): ReflectionFunctionAbstract
    {
        return $this->cause;
    }

    
    public function setCause(ReflectionFunctionAbstract $cause): void
    {
        $this->cause = $cause;
    }
}
