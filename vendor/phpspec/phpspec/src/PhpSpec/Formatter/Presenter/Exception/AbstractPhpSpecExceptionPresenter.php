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

namespace PhpSpec\Formatter\Presenter\Exception;

use PhpSpec\Exception\Exception;

abstract class AbstractPhpSpecExceptionPresenter
{
    
    public function presentException(Exception $exception): string
    {
        list($file, $line) = $this->getExceptionExamplePosition($exception);

        return $this->presentFileCode($file, $line);
    }

    
    private function getExceptionExamplePosition(Exception $exception): array
    {
        $cause = $exception->getCause();

        foreach ($exception->getTrace() as $call) {
            if (!isset($call['file'])) {
                continue;
            }

            if ($cause->getFileName() === $call['file']) {
                return array($call['file'], $call['line']);
            }
        }

        return array($exception->getFile(), $exception->getLine());
    }

    
    abstract protected function presentFileCode(string $file, int $lineno, int $context = 6): string;
}
