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

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\ExampleEvent;

final class ProgressFormatter extends ConsoleFormatter
{
    const FPS = 10;

    private $lastDraw;

    public function afterExample(ExampleEvent $event)
    {
        $this->printException($event);

        $now = microtime(true);
        if (!$this->lastDraw || ($now - $this->lastDraw) > 1/self::FPS) {
            $this->lastDraw = $now;
            $this->drawStats();
        }
    }

    private function displayIgnoredResources(): void
    {
        $io = $this->getIO();
        $stats = $this->getStatisticsCollector();
        $ignoredResourceEvents = $stats->getIgnoredResourceEvents();
        if (0 !== $ignoredResourcesCount = count($ignoredResourceEvents)) {
            $io->writeln(sprintf('<ignored>%d ignored</ignored>', $ignoredResourcesCount));
            foreach ($ignoredResourceEvents as $event) {
                $resource = $event->getResource();
                $io->writeln(sprintf(
                    '  <ignored>! <label>%s</label> could not be loaded at path <label>%s</label>.</ignored>',
                    $resource->getSpecClassname(),
                    $resource->getSpecFilename()
                ));
            }
        }
    }

    public function afterSuite(SuiteEvent $event)
    {
        $this->drawStats();

        $io = $this->getIO();
        $stats = $this->getStatisticsCollector();

        $io->freezeTemp();
        $io->writeln();

        $this->displayIgnoredResources();

        $io->writeln(sprintf("%d specs", $stats->getTotalSpecs()));

        $counts = array();
        foreach ($stats->getCountsHash() as $type => $count) {
            if ($count) {
                $counts[] = sprintf('<%s>%d %s</%s>', $type, $count, $type, $type);
            }
        }
        $count = $stats->getEventsCount();
        $plural = $count !== 1 ? 's' : '';
        $io->write(sprintf("%d example%s ", $count, $plural));
        if (\count($counts)) {
            $io->write(sprintf("(%s)", implode(', ', $counts)));
        }

        $io->writeln(sprintf("\n%sms", round($event->getTime() * 1000)));
        $io->writeln();
    }

    /**
     * @param $total
     * @param $counts
     */
    private function getPercentages($total, $counts): array
    {
        return array_map(
            function ($count) use ($total) {
                if (0 == $total) {
                    return 0;
                }

                $percent = ($count == $total) ? 100 : $count / ($total / 100);

                return $percent == 0 || $percent > 1 ? floor($percent) : 1;
            },
            $counts
        );
    }

    
    private function getBarLengths(array $counts): array
    {
        $stats = $this->getStatisticsCollector();
        $totalSpecsCount = $stats->getTotalSpecsCount();
        $specProgress = ($totalSpecsCount == 0) ? 1 : ($stats->getTotalSpecs() / $totalSpecsCount);
        $targetWidth = ceil($this->getIO()->getBlockWidth() * $specProgress);
        asort($counts);

        $barLengths = array_map(function ($count) use ($targetWidth, $counts) {
            return $count ? max(1, round($targetWidth * $count / array_sum($counts))) : 0;
        }, $counts);

        return $barLengths;
    }

    
    private function formatProgressOutput(array $barLengths, array $percents, bool $isDecorated): array
    {
        $size = $this->getIO()->getBlockWidth();
        $progress = array();
        foreach ($barLengths as $status => $length) {
            $percent = $percents[$status];
            $text = $percent.'%';
            $length = ($size - $length) >= 0 ? $length : $size;
            $size = $size - $length;

            if ($isDecorated) {
                if ($length > \strlen($text) + 2) {
                    $text = str_pad($text, $length, ' ', STR_PAD_BOTH);
                } else {
                    $text = str_pad('', $length, ' ');
                }

                $progress[$status] = sprintf("<$status-bg>%s</$status-bg>", $text);
            } else {
                $progress[$status] = str_pad(
                    sprintf('%s: %s', $status, $text),
                    15,
                    ' ',
                    STR_PAD_BOTH
                );
            }
        }
        krsort($progress);

        return $progress;
    }

    
    private function updateProgressBar(ConsoleIO $io, array $progress, int $total): void
    {
        if ($io->isDecorated()) {
            $progressBar = implode('', $progress);
            $pad = $this->getIO()->getBlockWidth() - \strlen(strip_tags($progressBar));
            $io->writeTemp($progressBar.str_repeat(' ', $pad + 1).$total);
        } else {
            $io->writeTemp('/'.implode('/', $progress).'/  '.$total.' examples');
        }
    }

    private function drawStats(): void
    {
        $io = $this->getIO();
        $stats = $this->getStatisticsCollector();

        $percents = $this->getPercentages($stats->getEventsCount(), $stats->getCountsHash());
        $barLengths = $this->getBarLengths($stats->getCountsHash());
        $progress = $this->formatProgressOutput($barLengths, $percents, $io->isDecorated());

        $this->updateProgressBar($io, $progress, $stats->getEventsCount());
    }
}
