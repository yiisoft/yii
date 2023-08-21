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

namespace PhpSpec\Wrapper\Subject\Expectation;

use PhpSpec\Event\ExpectationEvent;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Matcher\Matcher;
use PhpSpec\Util\DispatchTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Exception;

final class DispatcherDecorator extends Decorator implements Expectation
{
    use DispatchTrait;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var Matcher
     */
    private $matcher;
    /**
     * @var ExampleNode
     */
    private $example;

    
    public function __construct(
        Expectation $expectation,
        EventDispatcherInterface $dispatcher,
        Matcher $matcher,
        ExampleNode $example
    ) {
        $this->setExpectation($expectation);
        $this->dispatcher = $dispatcher;
        $this->matcher = $matcher;
        $this->example = $example;
    }

    /**
     * @throws \Exception
     * @throws \PhpSpec\Exception\Example\FailureException
     * @throws \Exception
     */
    public function match(string $alias, $subject, array $arguments = array())
    {
        $this->dispatch(
            $this->dispatcher,
            new ExpectationEvent($this->example, $this->matcher, $subject, $alias, $arguments),
            'beforeExpectation'
        );

        try {
            $result = $this->getExpectation()->match($alias, $subject, $arguments);
            $this->dispatch(
                $this->dispatcher,
                new ExpectationEvent(
                    $this->example,
                    $this->matcher,
                    $subject,
                    $alias,
                    $arguments,
                    ExpectationEvent::PASSED
                ),
                'afterExpectation'
            );
        } catch (FailureException $e) {
            $this->dispatch(
                $this->dispatcher,
                new ExpectationEvent(
                    $this->example,
                    $this->matcher,
                    $subject,
                    $alias,
                    $arguments,
                    ExpectationEvent::FAILED,
                    $e
                ),
                'afterExpectation'
            );

            throw $e;
        } catch (Exception $e) {
            $this->dispatch(
                $this->dispatcher,
                new ExpectationEvent(
                    $this->example,
                    $this->matcher,
                    $subject,
                    $alias,
                    $arguments,
                    ExpectationEvent::BROKEN,
                    $e
                ),
                'afterExpectation'
            );

            throw $e;
        }

        return $result;
    }
}
