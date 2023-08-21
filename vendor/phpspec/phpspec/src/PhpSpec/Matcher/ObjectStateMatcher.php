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
use PhpSpec\Exception\Example\MethodFailureException;
use PhpSpec\Exception\Fracture\MethodNotFoundException;
use PhpSpec\Wrapper\DelayedCall;

final class ObjectStateMatcher implements Matcher
{
    /**
     * @var string
     */
    private static $regex = '/(be|have)(.+)/';
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
        return \is_object($subject) && !is_callable($subject)
            && (0 === strpos($name, 'be') || 0 === strpos($name, 'have'))
        ;
    }

    /**
     * @throws \PhpSpec\Exception\Example\MethodFailureException
     * @throws \PhpSpec\Exception\Fracture\MethodNotFoundException
     */
    public function positiveMatch(string $name, $subject, array $arguments) : ?DelayedCall
    {
        preg_match(self::$regex, $name, $matches);
        $method   = ('be' === $matches[1] ? 'is' : 'has').ucfirst($matches[2]);
        $callable = array($subject, $method);

        if (!method_exists($subject, $method)) {
            throw new MethodNotFoundException(sprintf(
                'Method %s not found.',
                $this->presenter->presentValue($callable)
            ), $subject, $method, $arguments);
        }

        if (true !== $result = \call_user_func_array($callable, $arguments)) {
            throw $this->getMethodFailureExceptionFor($callable, true, $result);
        }

        return null;
    }

    /**
     * @throws \PhpSpec\Exception\Example\MethodFailureException
     * @throws \PhpSpec\Exception\Fracture\MethodNotFoundException
     */
    public function negativeMatch(string $name, $subject, array $arguments) : ?DelayedCall
    {
        preg_match(self::$regex, $name, $matches);
        $method   = ('be' === $matches[1] ? 'is' : 'has').ucfirst($matches[2]);
        $callable = array($subject, $method);

        if (!method_exists($subject, $method)) {
            throw new MethodNotFoundException(sprintf(
                'Method %s not found.',
                $this->presenter->presentValue($callable)
            ), $subject, $method, $arguments);
        }

        if (false !== $result = \call_user_func_array($callable, $arguments)) {
            throw $this->getMethodFailureExceptionFor($callable, false, $result);
        }

        return null;
    }

    
    public function getPriority(): int
    {
        return 50;
    }

    private function getMethodFailureExceptionFor(array $callable, bool $expectedBool, $result): MethodFailureException
    {
        return new MethodFailureException(sprintf(
            "Expected %s to return %s, but got %s.",
            $this->presenter->presentValue($callable),
            $this->presenter->presentValue($expectedBool),
            $this->presenter->presentValue($result)
        ), $expectedBool, $result, $callable[0], $callable[1]);
    }
}
