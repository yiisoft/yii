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

use ArrayAccess;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Formatter\Presenter\Presenter;

final class ArrayKeyValueMatcher extends BasicMatcher
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
        return
            (\is_array($subject) || $subject instanceof \ArrayAccess) &&
            'haveKeyWithValue' === $name &&
            2 == \count($arguments)
        ;
    }

    /**
     * @param array|ArrayAccess $subject
     */
    protected function matches($subject, array $arguments): bool
    {
        $key = $arguments[0];
        $value  = $arguments[1];

        if ($subject instanceof ArrayAccess) {
            return $subject->offsetExists($key) && $subject->offsetGet($key) === $value;
        }

        return (isset($subject[$key]) || array_key_exists($arguments[0], $subject)) && $subject[$key] === $value;
    }

    
    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        $key = $arguments[0];

        if (!$this->offsetExists($key, $subject)) {
            return new FailureException(sprintf('Expected %s to have key %s, but it didn\'t.',
                $this->presenter->presentValue($subject),
                $this->presenter->presentString($key)
            ));
        }

        return new FailureException(sprintf(
            'Expected %s to have value %s for %s key, but found %s.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentValue($arguments[1]),
            $this->presenter->presentString($key),
            $this->presenter->presentValue($subject[$key])
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

    private function offsetExists($key, $subject): bool
    {
        if ($subject instanceof ArrayAccess && $subject->offsetExists($key)) {
            return true;
        }
        if (is_array($subject) && array_key_exists($key, $subject)) {
            return true;
        }
        return false;
    }
}
