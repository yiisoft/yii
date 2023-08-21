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

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Formatter\Presenter\Presenter;

final class TraversableContainMatcher extends BasicMatcher
{
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
        return 'contain' === $name
            && 1 === \count($arguments)
            && $subject instanceof \Traversable
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 101;
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($subject, array $arguments): bool
    {
        foreach ($subject as $value) {
            if ($value === $arguments[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected %s to contain %s, but it does not.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentValue($arguments[0])
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getNegativeFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected %s not to contain %s, but it does.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentValue($arguments[0])
        ));
    }
}
