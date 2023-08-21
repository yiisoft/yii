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

use PhpSpec\Event\ResourceEvent;
use PhpSpec\Event\SuiteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SpecificationEvent;

class StatisticsCollector implements EventSubscriberInterface
{
    private $globalResult = 0;
    private $totalSpecs = 0;
    private $totalSpecsCount = 0;

    private $passedEvents = array();
    private $pendingEvents = array();
    private $skippedEvents = array();
    private $failedEvents = array();
    private $brokenEvents = array();
    private $resourceIgnoredEvents = array();

    public static function getSubscribedEvents()
    {
        return array(
            'afterSpecification' => array('afterSpecification', 10),
            'afterExample' => array('afterExample', 10),
            'beforeSuite' => array('beforeSuite', 10),
            'resourceIgnored' => array('onResourceIgnored', 1),
        );
    }

    public function afterSpecification(SpecificationEvent $event): void
    {
        $this->totalSpecs++;
    }

    public function afterExample(ExampleEvent $event): void
    {
        $this->globalResult = max($this->globalResult, $event->getResult());

        switch ($event->getResult()) {
            case ExampleEvent::PASSED:
                $this->passedEvents[] = $event;
                break;
            case ExampleEvent::PENDING:
                $this->pendingEvents[] = $event;
                break;
            case ExampleEvent::SKIPPED:
                $this->skippedEvents[] = $event;
                break;
            case ExampleEvent::FAILED:
                $this->failedEvents[] = $event;
                break;
            case ExampleEvent::BROKEN:
                $this->brokenEvents[] = $event;
                break;
        }
    }

    public function beforeSuite(SuiteEvent $suiteEvent): void
    {
        $this->totalSpecsCount = \count($suiteEvent->getSuite()->getSpecifications());
    }

    public function onResourceIgnored(ResourceEvent $resourceEvent)
    {
        $this->resourceIgnoredEvents[] = $resourceEvent;
    }

    public function getGlobalResult() : int
    {
        return $this->globalResult;
    }

    public function getAllEvents() : array
    {
        return array_merge(
            $this->passedEvents,
            $this->pendingEvents,
            $this->skippedEvents,
            $this->failedEvents,
            $this->brokenEvents
        );
    }

    public function getPassedEvents() : array
    {
        return $this->passedEvents;
    }

    public function getPendingEvents() : array
    {
        return $this->pendingEvents;
    }

    public function getSkippedEvents() : array
    {
        return $this->skippedEvents;
    }

    public function getFailedEvents() : array
    {
        return $this->failedEvents;
    }

    public function getBrokenEvents() : array
    {
        return $this->brokenEvents;
    }

    public function getIgnoredResourceEvents() : array
    {
        return $this->resourceIgnoredEvents;
    }

    /**
     * @return int[]
     */
    public function getCountsHash() : array
    {
        return array(
            'passed'  => \count($this->getPassedEvents()),
            'pending' => \count($this->getPendingEvents()),
            'skipped' => \count($this->getSkippedEvents()),
            'failed'  => \count($this->getFailedEvents()),
            'broken'  => \count($this->getBrokenEvents()),
        );
    }

    public function getTotalSpecs() : int
    {
        return $this->totalSpecs;
    }

    public function getEventsCount() : int
    {
        return array_sum($this->getCountsHash());
    }

    public function getTotalSpecsCount() : int
    {
        return $this->totalSpecsCount;
    }
}
