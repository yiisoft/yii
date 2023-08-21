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

namespace PhpSpec\Wrapper\Subject\Expectation;

use PhpSpec\Matcher\Matcher;

final class Negative implements Expectation
{
    /**
     * @var Matcher
     */
    private $matcher;

    
    public function __construct(Matcher $matcher)
    {
        $this->matcher = $matcher;
    }

    
    public function match(string $alias, $subject, array $arguments = array())
    {
        return $this->matcher->negativeMatch($alias, $subject, $arguments);
    }
}
