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
use PhpSpec\Matcher\Iterate\IterablesMatcher;
use PhpSpec\Wrapper\DelayedCall;

final class IterateAsMatcher implements Matcher
{
    /**
     * @var IterablesMatcher
     */
    private $iterablesMatcher;

    
    public function __construct(Presenter $presenter)
    {
        $this->iterablesMatcher = new IterablesMatcher($presenter);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $name, $subject, array $arguments): bool
    {
        return \in_array($name, ['iterateAs', 'yield'])
            && 1 === \count($arguments)
            && ($subject instanceof \Traversable || \is_array($subject))
            && ($arguments[0] instanceof \Traversable || \is_array($arguments[0]))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function positiveMatch(string $name, $subject, array $arguments) : ?DelayedCall
    {
        try {
            $this->iterablesMatcher->match($subject, $arguments[0]);
        } catch (Iterate\SubjectHasFewerElementsException $exception) {
            throw new FailureException('Expected subject to have the same number of elements than matched value, but it has fewer.', 0, $exception);
        } catch (Iterate\SubjectHasMoreElementsException $exception) {
            throw new FailureException('Expected subject to have the same number of elements than matched value, but it has more.', 0, $exception);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function negativeMatch(string $name, $subject, array $arguments) : ?DelayedCall
    {
        try {
            $this->positiveMatch($name, $subject, $arguments);
        } catch (FailureException $exception) {
            return null;
        }

        throw new FailureException('Expected subject not to iterate the same as matched value, but it does.');
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 100;
    }
}
