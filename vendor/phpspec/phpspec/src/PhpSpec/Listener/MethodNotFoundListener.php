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

namespace PhpSpec\Listener;

use PhpSpec\Util\NameChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\Locator\ResourceManager;
use PhpSpec\CodeGenerator\GeneratorManager;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Exception\Fracture\MethodNotFoundException;

final class MethodNotFoundListener implements EventSubscriberInterface
{
    private $io;
    private $resources;
    private $generator;
    private $methods = array();
    private $wrongMethodNames = array();
    /**
     * @var NameChecker
     */
    private $nameChecker;

    
    public function __construct(
        ConsoleIO $io,
        ResourceManager $resources,
        GeneratorManager $generator,
        NameChecker $nameChecker
    ) {
        $this->io        = $io;
        $this->resources = $resources;
        $this->generator = $generator;
        $this->nameChecker = $nameChecker;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'afterExample' => array('afterExample', 10),
            'afterSuite'   => array('afterSuite', -10),
        );
    }

    public function afterExample(ExampleEvent $event): void
    {
        if (null === $exception = $event->getException()) {
            return;
        }

        if (!$exception instanceof MethodNotFoundException) {
            return;
        }

        $classname = \get_class($exception->getSubject());
        $methodName = $exception->getMethodName();
        $this->methods[$classname .'::'.$methodName] = $exception->getArguments();
        $this->checkIfMethodNameAllowed($methodName);
    }

    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->io->isCodeGenerationEnabled()) {
            return;
        }

        foreach ($this->methods as $call => $arguments) {
            list($classname, $method) = explode('::', $call);

            if (\in_array($method, $this->wrongMethodNames)) {
                continue;
            }

            $message = sprintf('Do you want me to create `%s()` for you?', $call);

            try {
                $resource = $this->resources->createResource($classname);
            } catch (\RuntimeException $e) {
                continue;
            }

            if ($this->io->askConfirmation($message)) {
                $this->generator->generate($resource, 'method', array(
                    'name'      => $method,
                    'arguments' => $arguments
                ));
                $event->markAsWorthRerunning();
            }
        }

        if ($this->wrongMethodNames) {
            $this->writeWrongMethodNameMessage();
            $event->markAsNotWorthRerunning();
        }
    }

    private function checkIfMethodNameAllowed($methodName): void
    {
        if (!$this->nameChecker->isNameValid($methodName)) {
            $this->wrongMethodNames[] = $methodName;
        }
    }

    private function writeWrongMethodNameMessage(): void
    {
        foreach ($this->wrongMethodNames as $methodName) {
            $message = sprintf("I cannot generate the method '%s' for you because it is a reserved keyword", $methodName);
            $this->io->writeBrokenCodeBlock($message, 2);
        }
    }
}
