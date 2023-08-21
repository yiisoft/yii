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

final class CallbackMatcher extends BasicMatcher
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var callable
     */
    private $callback;
    /**
     * @var Presenter
     */
    private $presenter;

    
    public function __construct(string $name, callable $callback, Presenter $presenter)
    {
        $this->name      = $name;
        $this->callback  = $callback;
        $this->presenter = $presenter;
    }

    
    public function supports(string $name, $subject, array $arguments): bool
    {
        return $name === $this->name;
    }

    
    protected function matches($subject, array $arguments): bool
    {
        array_unshift($arguments, $subject);

        return (Boolean) \call_user_func_array($this->callback, $arguments);
    }

    
    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            '%s expected to %s(%s), but it is not.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentString($name),
            implode(', ', array_map(array($this->presenter, 'presentValue'), $arguments))
        ));
    }

    
    protected function getNegativeFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            '%s not expected to %s(%s), but it did.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentString($name),
            implode(', ', array_map(array($this->presenter, 'presentValue'), $arguments))
        ));
    }
}
