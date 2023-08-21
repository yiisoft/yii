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

namespace PhpSpec\Runner;

use PhpSpec\Matcher\Matcher;
use PhpSpec\Exception\Wrapper\MatcherNotFoundException;
use PhpSpec\Formatter\Presenter\Presenter;

class MatcherManager
{
    /**
     * @var Presenter
     */
    private $presenter;
    /**
     * @var Matcher[]
     */
    private $matchers = array();

    
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    
    public function add(Matcher $matcher): void
    {
        $this->matchers[] = $matcher;
        @usort($this->matchers, function (Matcher $matcher1, Matcher $matcher2) {
            return $matcher2->getPriority() - $matcher1->getPriority();
        });
    }

    /**
     * Replaces matchers with an already-sorted list
     *
     * @param Matcher[] $matchers
     */
    public function replace(array $matchers): void
    {
        $this->matchers = $matchers;
    }

    /**
     * @throws \PhpSpec\Exception\Wrapper\MatcherNotFoundException
     */
    public function find(string $keyword, $subject, array $arguments): Matcher
    {
        foreach ($this->matchers as $matcher) {
            if (true === $matcher->supports($keyword, $subject, $arguments)) {
                return $matcher;
            }
        }

        throw new MatcherNotFoundException(
            sprintf(
                'No %s(%s) matcher found for %s.',
                $this->presenter->presentString($keyword),
                $this->presenter->presentValue($arguments),
                $this->presenter->presentValue($subject)
            ),
            $keyword,
            $subject,
            $arguments
        );
    }
}
