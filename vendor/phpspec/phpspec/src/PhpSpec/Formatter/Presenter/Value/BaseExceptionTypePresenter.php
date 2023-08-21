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

use PhpSpec\Exception\ErrorException;

final class BaseExceptionTypePresenter implements ExceptionTypePresenter
{
    
    public function supports($value): bool
    {
        return $value instanceof \Exception;
    }

    
    public function present($value): string
    {
        $label = 'exc';
        $message = $value->getMessage();

        if ($value instanceof ErrorException) {
            $value = $value->getPrevious();
            $label = 'err';
        }

        if ($value instanceof \ParseError) {
            $message = sprintf(
                '%s in "%s" on line %d',
                $value->getMessage(),
                $value->getFile(),
                $value->getLine()
            );
        }

        return sprintf(
            '[%s:%s("%s")]',
            $label,
            \get_class($value),
            $message
        );
    }

    
    public function getPriority(): int
    {
        return 60;
    }
}
