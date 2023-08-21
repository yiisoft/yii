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
use PhpSpec\Wrapper\DelayedCall;

final class TraversableCountMatcher implements Matcher
{
    const LESS_THAN = 0;
    const EQUAL = 1;
    const MORE_THAN = 2;

    /**
     * @var Presenter
     */
    private $presenter;


    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $name, $subject, array $arguments): bool
    {
        return 'haveCount' === $name
            && 1 === \count($arguments)
            && $subject instanceof \Traversable
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function positiveMatch(string $name, $subject, array $arguments) : ?DelayedCall
    {
        $countDifference = $this->countDifference($subject, (int) $arguments[0]);

        if (self::EQUAL !== $countDifference) {
            throw new FailureException(sprintf(
                'Expected %s to have %s items, but got %s than that.',
                $this->presenter->presentValue($subject),
                $this->presenter->presentString((string) $arguments[0]),
                self::MORE_THAN === $countDifference ? 'more' : 'less'
            ));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function negativeMatch(string $name, $subject, array $arguments) : ?DelayedCall
    {
        $count = $this->countDifference($subject, (int) $arguments[0]);

        if (self::EQUAL === $count) {
            throw new FailureException(sprintf(
                'Expected %s not to have %s items, but got it.',
                $this->presenter->presentValue($subject),
                $this->presenter->presentString((string) $arguments[0])
            ));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 101;
    }

    /**
     * @return int self::*
     */
    private function countDifference(\Traversable $subject, int $expected): int
    {
        $count = 0;
        foreach ($subject as $value) {
            ++$count;

            if ($count > $expected) {
                return self::MORE_THAN;
            }
        }

        return $count === $expected ? self::EQUAL : self::LESS_THAN;
    }
}
