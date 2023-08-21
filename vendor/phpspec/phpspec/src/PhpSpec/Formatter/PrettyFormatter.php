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
use PhpSpec\Event\SpecificationEvent;
use PhpSpec\Event\ExampleEvent;

final class PrettyFormatter extends ConsoleFormatter
{
    public function beforeSpecification(SpecificationEvent $event)
    {
        $this->getIO()->writeln(sprintf("\n      %s\n", $event->getSpecification()->getTitle()), 0);
    }

    public function afterExample(ExampleEvent $event)
    {
        $io = $this->getIO();
        $line  = $event->getExample()->getFunctionReflection()->getStartLine();
        $depth = 2;
        $title = preg_replace('/^it /', '', $event->getExample()->getTitle());

        $io->write(sprintf('<lineno>%4d</lineno> ', $line));

        switch ($event->getResult()) {
            case ExampleEvent::PASSED:
                $io->write(sprintf('<passed>✔ %s</passed>', $title), $depth - 1);
                break;
            case ExampleEvent::PENDING:
                $io->write(sprintf('<pending>- %s</pending>', $title), $depth - 1);
                break;
            case ExampleEvent::SKIPPED:
                $io->write(sprintf('<skipped>? %s</skipped>', $title), $depth - 1);
                break;
            case ExampleEvent::FAILED:
                $io->write(sprintf('<failed>✘ %s</failed>', $title), $depth - 1);
                break;
            case ExampleEvent::BROKEN:
                $io->write(sprintf('<broken>! %s</broken>', $title), $depth - 1);
                break;
        }

        $this->printSlowTime($event);
        $io->writeln();
        $this->printException($event);
    }

    public function afterSuite(SuiteEvent $event)
    {
        $io = $this->getIO();
        $io->writeln();

        foreach (array(
            'failed' => $this->getStatisticsCollector()->getFailedEvents(),
            'broken' => $this->getStatisticsCollector()->getBrokenEvents(),
            'skipped' => $this->getStatisticsCollector()->getSkippedEvents(),
        ) as $status => $events) {
            if (!\count($events)) {
                continue;
            }

            $io->writeln(sprintf("<%s>----  %s examples</%s>\n", $status, $status, $status));
            foreach ($events as $failEvent) {
                $io->writeln(sprintf(
                    '%s',
                    str_replace('\\', DIRECTORY_SEPARATOR, $failEvent->getSpecification()->getTitle())
                ), 8);
                $this->afterExample($failEvent);
                $io->writeln();
            }
        }

        $io->writeln(sprintf("\n%d specs", $this->getStatisticsCollector()->getTotalSpecs()));

        $counts = array();
        foreach ($this->getStatisticsCollector()->getCountsHash() as $type => $count) {
            if ($count) {
                $counts[] = sprintf('<%s>%d %s</%s>', $type, $count, $type, $type);
            }
        }

        $io->write(sprintf("%d examples ", $this->getStatisticsCollector()->getEventsCount()));
        if (\count($counts)) {
            $io->write(sprintf("(%s)", implode(', ', $counts)));
        }

        $io->writeln(sprintf("\n%sms", round($event->getTime() * 1000)));
    }

    protected function printSlowTime(ExampleEvent $event)
    {
        $io = $this->getIO();
        $ms = $event->getTime() * 1000;
        if ($ms > 100) {
            $io->write(sprintf(' <failed>(%sms)</failed>', round($ms)));
        } elseif ($ms > 50) {
            $io->write(sprintf(' <pending>(%sms)</pending>', round($ms)));
        }
    }

    protected function printException(ExampleEvent $event, $depth = null): void
    {
        $io = $this->getIO();

        if (null === $exception = $event->getException()) {
            return;
        }

        $depth = $depth ?: 8;
        $message = $this->getPresenter()->presentException($exception, $io->isVerbose());

        if (ExampleEvent::FAILED === $event->getResult()) {
            $io->writeln(sprintf('<failed>%s</failed>', lcfirst($message)), $depth);
        } elseif (ExampleEvent::PENDING === $event->getResult()) {
            $io->writeln(sprintf('<pending>%s</pending>', lcfirst($message)), $depth);
        } elseif (ExampleEvent::SKIPPED === $event->getResult()) {
            $io->writeln(sprintf('<skipped>%s</skipped>', lcfirst($message)), $depth);
        } else {
            $io->writeln(sprintf('<broken>%s</broken>', lcfirst($message)), $depth);
        }
    }
}
