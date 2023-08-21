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

use PhpSpec\CodeGenerator\GeneratorManager;
use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Exception\Fracture\CollaboratorNotFoundException;
use PhpSpec\Locator\Resource;
use PhpSpec\Locator\ResourceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CollaboratorNotFoundListener implements EventSubscriberInterface
{
    /**
     * @var ConsoleIO
     */
    private $io;

    /**
     * @var CollaboratorNotFoundException[]
     */
    private $exceptions = array();

    /**
     * @var ResourceManager
     */
    private $resources;

    /**
     * @var GeneratorManager
     */
    private $generator;

    
    public function __construct(ConsoleIO $io, ResourceManager $resources, GeneratorManager $generator)
    {
        $this->io = $io;
        $this->resources = $resources;
        $this->generator = $generator;
    }

    
    public static function getSubscribedEvents(): array
    {
        return array(
            'afterExample' => array('afterExample', 10),
            'afterSuite'   => array('afterSuite', -10)
        );
    }

    
    public function afterExample(ExampleEvent $event): void
    {
        if (($exception = $event->getException()) &&
            ($exception instanceof CollaboratorNotFoundException)) {
            $this->exceptions[$exception->getCollaboratorName()] = $exception;
        }
    }

    
    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->io->isCodeGenerationEnabled()) {
            return;
        }

        foreach ($this->exceptions as $exception) {
            $resource = $this->resources->createResource($exception->getCollaboratorName());

            if ($this->resourceIsInSpecNamespace($exception, $resource)) {
                continue;
            }

            if ($this->io->askConfirmation(
                sprintf('Would you like me to generate an interface `%s` for you?', $exception->getCollaboratorName())
            )) {
                $this->generator->generate($resource, 'interface');
                $event->markAsWorthRerunning();
            }
        }
    }

    
    private function resourceIsInSpecNamespace(CollaboratorNotFoundException $exception, Resource $resource): bool
    {
        return strpos($exception->getCollaboratorName(), $resource->getSpecNamespace()) === 0;
    }
}
