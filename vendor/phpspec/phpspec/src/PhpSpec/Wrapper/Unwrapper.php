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

use Prophecy\Prophecy\RevealerInterface;
use Prophecy\Prophecy\ProphecyInterface;

class Unwrapper implements RevealerInterface
{
    
    public function unwrapAll(array $arguments): array
    {
        return array_map(array($this, 'unwrapOne'), $arguments);
    }

    
    public function unwrapOne($argument)
    {
        if (\is_array($argument)) {
            return array_map(array($this, 'unwrapOne'), $argument);
        }

        if (!\is_object($argument)) {
            return $argument;
        }

        if ($argument instanceof ObjectWrapper) {
            return $argument->getWrappedObject();
        }

        if ($argument instanceof ProphecyInterface) {
            $argument = $argument->reveal();
        }

        return $argument;
    }

    
    public function reveal($value)
    {
        return $this->unwrapOne($value);
    }
}
