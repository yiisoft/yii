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

use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\Exception\Example\FailureException;

final class ArrayCountMatcher extends BasicMatcher
{
    /**
     * @var Presenter
     */
    private $presenter;

    
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    
    public function supports(string $name, $subject, array $arguments): bool
    {
        return 'haveCount' === $name
            && 1 == \count($arguments)
            && (\is_array($subject) || $subject instanceof \Countable)
        ;
    }

    
    protected function matches($subject, array $arguments): bool
    {
        return $arguments[0] === \count($subject);
    }

    
    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected %s to have %s items, but got %s.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentString((string)\intval($arguments[0])),
            $this->presenter->presentString((string)\count($subject))
        ));
    }

    
    protected function getNegativeFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected %s not to have %s items, but got it.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentString((string)\intval($arguments[0]))
        ));
    }
}
