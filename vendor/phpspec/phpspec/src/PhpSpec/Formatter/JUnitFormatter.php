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

namespace PhpSpec\Formatter;

use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\IO\IO;
use PhpSpec\Listener\StatisticsCollector;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\SpecificationEvent;

/**
 * The JUnit Formatter
 *
 * @author Gildas Quemener <gildas.quemener@gmail.com>
 */
final class JUnitFormatter extends BasicFormatter
{
    /** @var array */
    protected $testCaseNodes = array();

    /** @var array */
    protected $testSuiteNodes = array();

    /** @var array */
    protected $exampleStatusCounts = array();

    /** @var array */
    protected $jUnitStatuses = array(
        ExampleEvent::PASSED  => 'passed',
        ExampleEvent::PENDING => 'pending',
        ExampleEvent::SKIPPED => 'skipped',
        ExampleEvent::FAILED  => 'failed',
        ExampleEvent::BROKEN  => 'broken',
    );

    /** @var array */
    protected $resultTags = array(
        ExampleEvent::FAILED  => 'failure',
        ExampleEvent::BROKEN  => 'error',
        ExampleEvent::SKIPPED => 'skipped',
    );

    public function __construct(Presenter $presenter, IO $io, StatisticsCollector $stats)
    {
        parent::__construct($presenter, $io, $stats);

        $this->initTestCaseNodes();
    }

    /**
     * Set testcase nodes
     */
    public function setTestCaseNodes(array $testCaseNodes): void
    {
        $this->testCaseNodes = $testCaseNodes;
    }

    /**
     * Get testcase nodes
     */
    public function getTestCaseNodes(): array
    {
        return $this->testCaseNodes;
    }

    /**
     * Set testsuite nodes
     */
    public function setTestSuiteNodes(array $testSuiteNodes)
    {
        $this->testSuiteNodes = $testSuiteNodes;
    }

    /**
     * Get testsuite nodes
     */
    public function getTestSuiteNodes(): array
    {
        return $this->testSuiteNodes;
    }

    /**
     * Set example status counts
     */
    public function setExampleStatusCounts(array $exampleStatusCounts)
    {
        $this->exampleStatusCounts = $exampleStatusCounts;
    }

    /**
     * Get example status counts
     */
    public function getExampleStatusCounts(): array
    {
        return $this->exampleStatusCounts;
    }

    /**
     * {@inheritdoc}
     */
    public function afterExample(ExampleEvent $event)
    {
        $testCaseNode = sprintf(
            '<testcase name="%s" time="%F" classname="%s" status="%s"',
            $event->getTitle(),
            $event->getTime(),
            $event->getSpecification()->getClassReflection()->getName(),
            $this->jUnitStatuses[$event->getResult()]
        );

        $this->exampleStatusCounts[$event->getResult()]++;

        if (\in_array($event->getResult(), array(ExampleEvent::BROKEN, ExampleEvent::FAILED))) {
            $exception = $event->getException();
            $testCaseNode .= sprintf(
                '>'."\n".
                '<%s type="%s" message="%s" />'."\n".
                '<system-err>'."\n".
                '<![CDATA['."\n".
                '%s'."\n".
                ']]>'."\n".
                '</system-err>'."\n".
                '</testcase>',
                $this->resultTags[$event->getResult()],
                \get_class($exception),
                htmlspecialchars($exception->getMessage()),
                $exception->getTraceAsString()
            );
        } elseif (ExampleEvent::SKIPPED === $event->getResult()) {
            $testCaseNode .= sprintf(
                '>'."\n".
                '\<skipped><![CDATA[ %s ]]>\</skipped>'."\n".
                '</testcase>',
                htmlspecialchars($event->getException()->getMessage())
            );
        } else {
            $testCaseNode .= ' />';
        }

        $this->testCaseNodes[] = $testCaseNode;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSpecification(SpecificationEvent $event)
    {
        $this->testSuiteNodes[] = sprintf(
            '<testsuite name="%s" time="%F" tests="%s" failures="%s" errors="%s" skipped="%s">'."\n".
            '%s'."\n".
            '</testsuite>',
            $event->getTitle(),
            $event->getTime(),
            \count($this->testCaseNodes),
            $this->exampleStatusCounts[ExampleEvent::FAILED],
            $this->exampleStatusCounts[ExampleEvent::BROKEN],
            $this->exampleStatusCounts[ExampleEvent::PENDING] + $this->exampleStatusCounts[ExampleEvent::SKIPPED],
            implode("\n", $this->testCaseNodes)
        );

        $this->initTestCaseNodes();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSuite(SuiteEvent $event)
    {
        $stats = $this->getStatisticsCollector();

        $output = sprintf(
            '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" .
            '<testsuites time="%F" tests="%s" failures="%s" errors="%s">' . "\n" .
            '%s' . "\n" .
            '</testsuites>',
            $event->getTime(),
            $stats->getEventsCount(),
            \count($stats->getFailedEvents()),
            \count($stats->getBrokenEvents()),
            implode("\n", $this->testSuiteNodes)
        );

        $this->getIO()->write($output);
    }

    /**
     * Initialize test case nodes and example status counts
     */
    protected function initTestCaseNodes(): void
    {
        $this->testCaseNodes       = array();
        $this->exampleStatusCounts = array(
            ExampleEvent::PASSED  => 0,
            ExampleEvent::PENDING => 0,
            ExampleEvent::SKIPPED => 0,
            ExampleEvent::FAILED  => 0,
            ExampleEvent::BROKEN  => 0,
        );
    }
}
