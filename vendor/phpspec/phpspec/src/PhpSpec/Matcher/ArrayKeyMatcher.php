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
use ArrayAccess;

final class ArrayKeyMatcher extends BasicMatcher
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
        return 'haveKey' === $name
            && 1 == \count($arguments)
            && (\is_array($subject) || $subject instanceof ArrayAccess)
        ;
    }

    
    protected function matches($subject, array $arguments): bool
    {
        $key = $arguments[0];

        if ($subject instanceof ArrayAccess) {
            return $subject->offsetExists($key);
        }

        return isset($subject[$key]) || array_key_exists($arguments[0], $subject);
    }

    
    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected %s to have %s key, but it does not.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentString($arguments[0])
        ));
    }

    
    protected function getNegativeFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected %s not to have %s key, but it does.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentString($arguments[0])
        ));
    }
}
