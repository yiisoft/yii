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

namespace PhpSpec\Util;

final class ReservedWordsMethodNameChecker implements NameChecker
{
    private $reservedWords = [
        '__halt_compiler',
    ];

    /**
     * {@inheritdoc}
     */
    public function isNameValid(string $name): bool
    {
        return !\in_array(strtolower($name), $this->reservedWords);
    }
}
