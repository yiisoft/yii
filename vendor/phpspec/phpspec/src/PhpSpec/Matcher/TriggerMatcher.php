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

use PhpSpec\Wrapper\Unwrapper;
use PhpSpec\Wrapper\DelayedCall;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Exception\Example\MatcherException;
use PhpSpec\Exception\Fracture\MethodNotFoundException;

final class TriggerMatcher implements Matcher
{
    /**
     * @var Unwrapper
     */
    private $unwrapper;


    public function __construct(Unwrapper $unwrapper)
    {
        $this->unwrapper = $unwrapper;
    }


    /**
     * @param mixed $subject
     */
    public function supports(string $name, $subject, array $arguments): bool
    {
        return 'trigger' === $name;
    }

    /**
     * @param mixed $subject
     */
    public function positiveMatch(string $name, $subject, array $arguments): DelayedCall
    {
        return $this->getDelayedCall(array($this, 'verifyPositive'), $subject, $arguments);
    }

    /**
     * @param mixed $subject
     */
    public function negativeMatch(string $name, $subject, array $arguments): DelayedCall
    {
        return $this->getDelayedCall(array($this, 'verifyNegative'), $subject, $arguments);
    }

    /**
     * @throws \PhpSpec\Exception\Example\FailureException
     * @return void
     */
    public function verifyPositive(callable $callable, array $arguments, int $level = null, string $message = null)
    {
        $triggered = 0;

        $prevHandler = set_error_handler(function ($type, $str, $file, $line, $context=[]) use (&$prevHandler, $level, $message, &$triggered) {
            if (null !== $level && $level !== $type) {
                return null !== $prevHandler && \call_user_func($prevHandler, $type, $str, $file, $line, $context);
            }

            if (null !== $message && false === strpos($str, $message)) {
                return null !== $prevHandler && \call_user_func($prevHandler, $type, $str, $file, $line, $context);
            }

            ++$triggered;
        });

        \call_user_func_array($callable, $arguments);

        restore_error_handler();

        if ($triggered === 0) {
            throw new FailureException('Expected to trigger errors, but got none.');
        }
    }

    /**
     * @throws \PhpSpec\Exception\Example\FailureException
     * @return void
     */
    public function verifyNegative(callable $callable, array $arguments, int $level = null, string $message = null)
    {
        $triggered = 0;

        $prevHandler = set_error_handler(function ($type, $str, $file, $line, $context) use (&$prevHandler, $level, $message, &$triggered) {
            if (null !== $level && $level !== $type) {
                return null !== $prevHandler && \call_user_func($prevHandler, $type, $str, $file, $line, $context);
            }

            if (null !== $message && false === strpos($str, $message)) {
                return null !== $prevHandler && \call_user_func($prevHandler, $type, $str, $file, $line, $context);
            }

            ++$triggered;
        });

        \call_user_func_array($callable, $arguments);

        restore_error_handler();

        if ($triggered > 0) {
            /** @psalm-suppress NoValue */
            throw new FailureException(
                sprintf(
                    'Expected to not trigger errors, but got %d.',
                    $triggered
                )
            );
        }
    }


    public function getPriority(): int
    {
        return 1;
    }

    /**
     * @param mixed $subject
     */
    private function getDelayedCall(callable $check, $subject, array $arguments): DelayedCall
    {
        $unwrapper = $this->unwrapper;
        list($level, $message) = $this->unpackArguments($arguments);

        return new DelayedCall(
            /**
             * @param mixed $method
             * @param mixed $arguments
             * @return mixed
             */
            function ($method, $arguments) use ($check, $subject, $level, $message, $unwrapper) {
                $arguments = $unwrapper->unwrapAll($arguments);

                $methodName = $arguments[0];
                $arguments = $arguments[1] ?? array();
                $callable = array($subject, $methodName);

                list($class, $methodName) = array($subject, $methodName);
                if (!method_exists($class, $methodName) && !method_exists($class, '__call')) {
                    throw new MethodNotFoundException(
                        sprintf('Method %s::%s not found.', \get_class($class), $methodName),
                        $class,
                        $methodName,
                        $arguments
                    );
                }

                return \call_user_func($check, $callable, $arguments, $level, $message);
            }
        );
    }


    private function unpackArguments(array $arguments): array
    {
        $count = \count($arguments);

        if (0 === $count) {
            return array(null, null);
        }

        if (1 === $count) {
            return array($arguments[0], null);
        }

        if (2 !== $count) {
            throw new MatcherException(
                sprintf(
                    "Wrong argument count provided in trigger matcher.\n".
                    "Up to two arguments expected,\n".
                    "Got %d.",
                    $count
                )
            );
        }

        return array($arguments[0], $arguments[1]);
    }
}
