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

namespace PhpSpec\Formatter\Presenter\Value;

final class ArrayTypePresenter implements TypePresenter
{
    
    public function supports($value): bool
    {
        return 'array' === strtolower(\gettype($value));
    }

    
    public function present($value): string
    {
        return sprintf('[array:%d]', \count($value));
    }

    
    public function getPriority(): int
    {
        return 20;
    }
}
