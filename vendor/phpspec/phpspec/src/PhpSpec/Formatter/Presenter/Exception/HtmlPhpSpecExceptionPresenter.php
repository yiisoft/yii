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

final class HtmlPhpSpecExceptionPresenter extends AbstractPhpSpecExceptionPresenter implements PhpSpecExceptionPresenter
{
    
    protected function presentFileCode(string $file, int $lineno, int $context = 6): string
    {
        $lines  = explode(PHP_EOL, file_get_contents($file));
        $offset = (int)max(0, $lineno - ceil($context / 2));
        $lines  = \array_slice($lines, $offset, $context);

        $text = PHP_EOL;

        foreach ($lines as $line) {
            $offset++;

            $text .= sprintf(
                '<span class="linenum">%d</span><span class="%s">%s</span>' . PHP_EOL,
                $offset,
                $offset == $lineno ? 'offending' : 'normal',
                $line
            );
        }

        return $text;
    }
}
