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

namespace PhpSpec\Matcher;

use PhpSpec\Wrapper\DelayedCall;

interface Matcher
{
    /**
     * Checks if matcher supports provided subject and matcher name.
     */
    public function supports(string $name, $subject, array $arguments): bool;

    /**
     * Evaluates positive match.
     */
    public function positiveMatch(string $name, $subject, array $arguments) : ?DelayedCall;

    /**
     * Evaluates negative match.
     */
    public function negativeMatch(string $name, $subject, array $arguments) : ?DelayedCall;

    /**
     * Returns matcher priority.
     */
    public function getPriority(): int;
}
