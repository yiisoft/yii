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

namespace PhpSpec\Wrapper;

use PhpSpec\CodeAnalysis\AccessInspector;
use PhpSpec\Exception\ExceptionFactory;
use PhpSpec\Runner\MatcherManager;
use PhpSpec\Formatter\Presenter\Presenter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Wrapper\Subject\WrappedObject;
use PhpSpec\Wrapper\Subject\Caller;
use PhpSpec\Wrapper\Subject\SubjectWithArrayAccess;
use PhpSpec\Wrapper\Subject\ExpectationFactory;

class Wrapper
{
    /**
     * @var MatcherManager
     */
    private $matchers;
    /**
     * @var Presenter
     */
    private $presenter;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var ExampleNode
     */
    private $example;
    /**
     * @var AccessInspector
     */
    private $accessInspector;

    /**
     * @param AccessInspector $accessInspector
     */
    public function __construct(
        MatcherManager $matchers,
        Presenter $presenter,
        EventDispatcherInterface $dispatcher,
        ExampleNode $example,
        AccessInspector $accessInspector = null
    ) {
        $this->matchers = $matchers;
        $this->presenter = $presenter;
        $this->dispatcher = $dispatcher;
        $this->example = $example;
        $this->accessInspector = $accessInspector;
    }

    /**
     * @param object $value
     */
    public function wrap($value = null): Subject
    {
        $wrappedObject = new WrappedObject($value, $this->presenter);
        $caller = $this->createCaller($wrappedObject);
        $arrayAccess = new SubjectWithArrayAccess($caller, $this->presenter, $this->dispatcher);
        $expectationFactory = new ExpectationFactory($this->example, $this->dispatcher, $this->matchers);

        return new Subject(
            $value,
            $this,
            $wrappedObject,
            $caller,
            $arrayAccess,
            $expectationFactory
        );
    }

    
    private function createCaller(WrappedObject $wrappedObject): Caller
    {
        $exceptionFactory = new ExceptionFactory($this->presenter);

        return new Caller(
            $wrappedObject,
            $this->example,
            $this->dispatcher,
            $exceptionFactory,
            $this,
            $this->accessInspector
        );
    }
}
