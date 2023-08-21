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

namespace PhpSpec\Runner;

use Error;
use PhpSpec\Exception\ErrorException;
use PhpSpec\Runner\Maintainer\Maintainer;
use PhpSpec\Util\DispatchTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PhpSpec\Runner\Maintainer\LetAndLetgoMaintainer;
use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\Specification;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Exception\Exception as PhpSpecException;
use PhpSpec\Exception\Example as ExampleException;
use Prophecy\Exception as ProphecyException;
use Exception;

class ExampleRunner
{
    use DispatchTrait;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var Presenter
     */
    private $presenter;
    /**
     * @var Maintainer[]
     */
    private $maintainers = array();

    
    public function __construct(EventDispatcherInterface $dispatcher, Presenter $presenter)
    {
        $this->dispatcher = $dispatcher;
        $this->presenter  = $presenter;
    }

    
    public function registerMaintainer(Maintainer $maintainer): void
    {
        $this->maintainers[] = $maintainer;

        @usort($this->maintainers, function ($maintainer1, $maintainer2) {
            return $maintainer2->getPriority() - $maintainer1->getPriority();
        });
    }

    
    public function run(ExampleNode $example): int
    {
        $startTime = microtime(true);
        $this->dispatch(
            $this->dispatcher,
            new ExampleEvent($example),
            'beforeExample'
        );

        try {
            $this->executeExample(
                $example->getSpecification()->getClassReflection()->newInstance(),
                $example
            );

            $status    = ExampleEvent::PASSED;
            $exception = null;
        } catch (ExampleException\PendingException $e) {
            $status    = ExampleEvent::PENDING;
            $exception = $e;
        } catch (ExampleException\SkippingException $e) {
            $status    = ExampleEvent::SKIPPED;
            $exception = $e;
        } catch (ProphecyException\Prediction\PredictionException $e) {
            $status    = ExampleEvent::FAILED;
            $exception = $e;
        } catch (ExampleException\FailureException $e) {
            $status    = ExampleEvent::FAILED;
            $exception = $e;
        } catch (Exception $e) {
            $status    = ExampleEvent::BROKEN;
            $exception = $e;
        } catch (Error $e) {
            $status    = ExampleEvent::BROKEN;
            $exception = new ErrorException($e);
        }

        if ($exception instanceof PhpSpecException) {
            $exception->setCause($example->getFunctionReflection());
        }

        $runTime = microtime(true) - $startTime;
        $this->dispatch(
            $this->dispatcher,
            $event = new ExampleEvent($example, $runTime, $status, $exception),
            'afterExample'
        );

        return $event->getResult();
    }

    /**
     * @throws \PhpSpec\Exception\Example\PendingException
     * @throws \Exception
     */
    protected function executeExample(Specification $context, ExampleNode $example): void
    {
        if ($example->isPending()) {
            throw new ExampleException\PendingException();
        }

        $matchers      = new MatcherManager($this->presenter);
        $collaborators = new CollaboratorManager($this->presenter);
        $maintainers   = array_filter($this->maintainers, function (Maintainer $maintainer) use ($example) {
            return $maintainer->supports($example);
        });

        // run maintainers prepare
        foreach ($maintainers as $maintainer) {
            $maintainer->prepare($example, $context, $matchers, $collaborators);
        }

        // execute example
        $reflection = $example->getFunctionReflection();

        try {
            if ($reflection instanceof \ReflectionMethod) {
                $reflection->invokeArgs($context, $collaborators->getArgumentsFor($reflection));
            }
            elseif ($reflection instanceof \ReflectionFunction)  {
                $reflection->invokeArgs($collaborators->getArgumentsFor($reflection));
            }
            else {
                throw new \RuntimeException('Not able to invoke example');
            }
        } catch (\Exception $e) {
            $this->runMaintainersTeardown(
                $this->searchExceptionMaintainers($maintainers),
                $example,
                $context,
                $matchers,
                $collaborators
            );
            throw $e;
        }

        $this->runMaintainersTeardown($maintainers, $example, $context, $matchers, $collaborators);
    }

    /**
     * @param Maintainer[] $maintainers
     */
    private function runMaintainersTeardown(
        array $maintainers,
        ExampleNode $example,
        Specification $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ): void {
        foreach (array_reverse($maintainers) as $maintainer) {
            $maintainer->teardown($example, $context, $matchers, $collaborators);
        }
    }

    /**
     * @param Maintainer[] $maintainers
     *
     * @return Maintainer[]
     */
    private function searchExceptionMaintainers(array $maintainers)
    {
        return array_filter(
            $maintainers,
            function ($maintainer) {
                return $maintainer instanceof LetAndLetgoMaintainer;
            }
        );
    }
}
