<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Chris Kruining <chrise@kruining.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace PhpSpec\Formatter;

use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SpecificationEvent;
use PhpSpec\Event\SuiteEvent;

final class JsonFormatter extends BasicFormatter
{
    private $data = [
        'status' => '',
        'time' => 0,
        'specifications' => [],
    ];

    private const STATUS_NAME = [
        ExampleEvent::PASSED  => 'passed',
        ExampleEvent::PENDING => 'pending',
        ExampleEvent::SKIPPED => 'skipped',
        ExampleEvent::FAILED  => 'failed',
        ExampleEvent::BROKEN  => 'broken',
    ];

    public function beforeSpecification(SpecificationEvent $event)
    {
        $this->data['specifications'][$event->getSpecification()->getTitle()] = [
            'status' => '',
            'time' => 0,
            'examples' => [],
        ];
    }

    public function afterExample(ExampleEvent $event)
    {
        $specification = $event->getSpecification()->getTitle();
        $example = $event->getTitle();

        $this->data['specifications'][$specification]['examples'][$example] = [
            'status' => self::STATUS_NAME[$event->getResult()],
            'time' => $event->getTime(),
        ];

        $exception = $event->getException();

        if ($exception === null) {
            return;
        }

        $this->data['specifications'][$specification]['examples'][$example]['@exception'] = [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTrace(),
        ];
    }

    public function afterSpecification(SpecificationEvent $event)
    {
        $specification = $event->getSpecification()->getTitle();
        
        $this->data['specifications'][$specification]['status'] = self::STATUS_NAME[$event->getResult()];
        $this->data['specifications'][$specification]['time'] = $event->getTime();
    }

    public function afterSuite(SuiteEvent $event)
    {
        $this->data['status'] = self::STATUS_NAME[$event->getResult()];
        $this->data['time'] = $event->getTime();

        $this->getIO()->write(json_encode($this->data));
    }
}
