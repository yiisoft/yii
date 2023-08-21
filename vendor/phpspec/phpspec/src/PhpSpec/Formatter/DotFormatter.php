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

use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\ExampleEvent;

final class DotFormatter extends ConsoleFormatter
{
    /**
     * @var int
     */
    private $examplesCount = 0;

    
    public function beforeSuite(SuiteEvent $event)
    {
        $this->examplesCount = \count($event->getSuite());
    }

    
    public function afterExample(ExampleEvent $event)
    {
        $io = $this->getIO();

        $eventsCount = $this->getStatisticsCollector()->getEventsCount();
        if ($eventsCount === 1) {
            $io->writeln();
        }

        switch ($event->getResult()) {
            case ExampleEvent::PASSED:
                $io->write('<passed>.</passed>');
                break;
            case ExampleEvent::PENDING:
                $io->write('<pending>P</pending>');
                break;
            case ExampleEvent::SKIPPED:
                $io->write('<skipped>S</skipped>');
                break;
            case ExampleEvent::FAILED:
                $io->write('<failed>F</failed>');
                break;
            case ExampleEvent::BROKEN:
                $io->write('<broken>B</broken>');
                break;
        }

        $remainder = $eventsCount % 50;
        $endOfRow = 0 === $remainder;
        $lastRow = $eventsCount === $this->examplesCount;

        if ($lastRow && !$endOfRow) {
            $io->write(str_repeat(' ', 50 - $remainder));
        }

        if ($lastRow || $endOfRow) {
            $length = \strlen((string) $this->examplesCount);
            $format = sprintf(' %%%dd / %%%dd', $length, $length);

            $io->write(sprintf($format, $eventsCount, $this->examplesCount));

            if ($eventsCount !== $this->examplesCount) {
                $io->writeln();
            }
        }
    }

    
    public function afterSuite(SuiteEvent $event)
    {
        $this->getIO()->writeln("\n");

        $this->outputExceptions();
        $this->outputSuiteSummary($event);
    }

    private function outputExceptions(): void
    {
        $stats = $this->getStatisticsCollector();
        $notPassed = array_filter(array(
            'failed' => $stats->getFailedEvents(),
            'broken' => $stats->getBrokenEvents(),
            'pending' => $stats->getPendingEvents(),
            'skipped' => $stats->getSkippedEvents(),
        ));

        foreach ($notPassed as $events) {
            array_map(array($this, 'printException'), $events);
        }

        foreach ($stats->getIgnoredResourceEvents() as $event) {
            $this->printIgnoredResource($event);
        }
    }

    private function outputSuiteSummary(SuiteEvent $event): void
    {
        $this->outputTotalSpecCount();
        $this->outputTotalExamplesCount();
        $this->outputSpecificExamplesCount();

        $this->getIO()->writeln(sprintf("\n%sms", round($event->getTime() * 1000)));
    }

    private function plural($count)
    {
        return $count > 1 ? 's' : '';
    }

    private function outputTotalSpecCount(): void
    {
        $stats = $this->getStatisticsCollector();
        $count = $stats->getTotalSpecs();
        $line = sprintf("%d spec%s", $count, $this->plural($count));

        if (($ignoredCount = count($stats->getIgnoredResourceEvents())) > 0) {
            $line .= sprintf(' (%d ignored)', $ignoredCount);
        }
        $this->getIO()->writeln($line);
    }

    private function outputTotalExamplesCount(): void
    {
        $count = $this->getStatisticsCollector()->getEventsCount();
        $this->getIO()->write(sprintf("%d example%s ", $count, $this->plural($count)));
    }

    private function outputSpecificExamplesCount(): void
    {
        $typesWithEvents = array_filter($this->getStatisticsCollector()->getCountsHash());

        $counts = array();
        foreach ($typesWithEvents as $type => $count) {
            $counts[] = sprintf('<%s>%d %s</%s>', $type, $count, $type, $type);
        }

        if (\count($counts)) {
            $this->getIO()->write(sprintf("(%s)", implode(', ', $counts)));
        }
    }
}
