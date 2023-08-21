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

final class StringStartMatcher extends BasicMatcher
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
        return 'startWith' === $name
            && \is_string($subject)
            && 1 == \count($arguments)
        ;
    }

    
    protected function matches($subject, array $arguments): bool
    {
        return 0 === strpos($subject, $arguments[0]);
    }

    
    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected %s to start with %s, but it does not.',
            $this->presenter->presentString($subject),
            $this->presenter->presentString($arguments[0])
        ));
    }

    
    protected function getNegativeFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected %s not to start with %s, but it does.',
            $this->presenter->presentString($subject),
            $this->presenter->presentString($arguments[0])
        ));
    }
}
