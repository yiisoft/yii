<?php

declare(strict_types=1);

namespace PhpSpec\Formatter;

use Exception;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SpecificationEvent;
use PhpSpec\Event\SuiteEvent;

class TeamCityFormatter extends BasicFormatter
{
    /** @var string|null */
    private $startedTestName = null;
    /** @var bool */
    private $isSummaryTestCountPrinted = false;
    /** @var false|int */
    private $flowId;

    private function startTest(ExampleEvent $event): void
    {
        $testName = $event->getTitle();
        $this->startedTestName = $testName;
        $params = ['name' => $testName];

        $className = $event->getSpecification()->getClassReflection()->getName();
        $fileName = (string)$event->getSpecification()->getClassReflection()->getFileName();
        $referenceName = str_replace(' ', '_', $testName);
        $params['locationHint'] = "php_qn://{$fileName}::\\{$className}::{$referenceName}";

        $this->printEvent('testStarted', $params);
    }

    private function printIgnoredTest(string $testName, Exception $t, float $time): void
    {
        $this->printEvent(
            'testIgnored',
            [
                'name' => $testName,
                'message' => $this->getMessage($t),
                'details' => $this->getDetails($t),
                'duration' => $this->toMilliseconds($time),
            ]
        );
    }

    /**
     * A test ended.
     */
    private function endTest(ExampleEvent $event): void
    {
        $this->printEvent(
            'testFinished',
            [
                'name' => $event->getTitle(),
                'duration' => $this->toMilliseconds($event->getTime()),
            ]
        );
    }

    private function printEvent(string $eventName, array $params = []): void
    {
        $this->write("\n##teamcity[{$eventName}");

        if ($this->flowId) {
            $params['flowId'] = $this->flowId;
        }

        foreach ($params as $key => $value) {
            $escapedValue = self::escapeValue((string)$value);
            $this->write(" {$key}='{$escapedValue}'");
        }

        $this->write("]\n");
    }

    private function getMessage(Exception $t): string
    {
        return $this->getPresenter()->presentException($t, $this->getIO()->isVerbose());
    }

    private function getDetails(Exception $t): string
    {
        return ' '.str_replace("\n", "\n ", $t->getTraceAsString());
    }

    private static function escapeValue(string $text): string
    {
        return str_replace(
            ['|', "'", "\n", "\r", ']', '['],
            ['||', "|'", '|n', '|r', '|]', '|['],
            $text
        );
    }

    /**
     * @param float $time microseconds
     */
    private function toMilliseconds(float $time): int
    {
        return (int)round($time * 1000);
    }

    public function write(string $buffer): void
    {
        $this->getIO()->write($buffer);
    }

    private function plural(int $count): string
    {
        return $count !== 1 ? 's' : '';
    }

    private function outputSuiteSummary(SuiteEvent $event): void
    {
        $this->getIO()->write("\n\n");

        $count = $this->getStatisticsCollector()->getTotalSpecs();
        $this->getIO()->write(sprintf("%d spec%s\n", $count, $this->plural($count)));
        $count = $this->getStatisticsCollector()->getEventsCount();
        $this->getIO()->write(sprintf("%d example%s ", $count, $this->plural($count)));

        $typesWithEvents = array_filter($this->getStatisticsCollector()->getCountsHash());

        $counts = array();
        foreach ($typesWithEvents as $type => $count) {
            $counts[] = sprintf('<%s>%d %s</%s>', $type, $count, $type, $type);
        }

        if (\count($counts)) {
            $this->getIO()->write(sprintf("(%s)", implode(', ', $counts)));
        }

        $this->getIO()->write(sprintf("\n%sms\n", $this->toMilliseconds($event->getTime())));
    }

    /**
     * A test started.
     */
    public function beforeSuite(SuiteEvent $event): void
    {
        $this->flowId = stripos((string)ini_get('disable_functions'), 'getmypid') === false
            ? getmypid()
            : false;

        if (!$this->isSummaryTestCountPrinted) {
            $this->isSummaryTestCountPrinted = true;

            $this->printEvent(
                'testCount',
                [
                    'count' => array_sum(
                        array_map(
                            static function ($spec) {
                                return count($spec->getExamples());
                            },
                            $event->getSuite()->getSpecifications()
                        )
                    )
                ]
            );
        }

        $this->printEvent(
            'testSuiteStarted',
            ['name' => 'PHPSpecTestSuite']
        );
    }

    /**
     * @param SuiteEvent $event
     */
    public function afterSuite(SuiteEvent $event): void
    {
        $this->printEvent(
            'testSuiteFinished',
            [
                'name' => 'PHPSpecTestSuite',
                'duration' => $this->toMilliseconds($event->getTime()),
            ]
        );

        $this->outputSuiteSummary($event);
    }

    public function beforeExample(ExampleEvent $event): void
    {
        $this->startTest($event);
    }

    /**
     * @param ExampleEvent $event
     */
    public function afterExample(ExampleEvent $event): void
    {
        switch ($event->getResult()) {
            case ExampleEvent::BROKEN:
            case ExampleEvent::FAILED:
                if ($exception = $event->getException()) {
                    $this->printEvent(
                        'testFailed',
                        [
                            'name' => $event->getTitle(),
                            'message' => $this->getMessage($exception),
                            'details' => $this->getDetails($exception),
                            'duration' => $this->toMilliseconds($event->getTime()),
                        ]
                    );
                }
                break;
            case ExampleEvent::SKIPPED:
                if ($exception = $event->getException()) {
                    if ($this->startedTestName !== $event->getTitle()) {
                        $this->startTest($event);
                        $this->printIgnoredTest($event->getTitle(), $exception, $event->getTime());
                        $this->endTest($event);
                        break;
                    }

                    $this->printIgnoredTest($event->getTitle(), $exception, $event->getTime());
                }
                break;
            case ExampleEvent::PENDING:
                if ($exception = $event->getException()) {
                    $this->printIgnoredTest($event->getTitle(), $exception, $event->getTime());
                }
                break;
        }

        $this->endTest($event);
    }

    /**
     * @param SpecificationEvent $event
     */
    public function beforeSpecification(SpecificationEvent $event): void
    {
        $suiteName = $event->getSpecification()->getResource()->getSpecClassname();

        if (empty($suiteName)) {
            return;
        }

        $parameters = ['name' => $suiteName];
        $fileName = $event->getSpecification()->getResource()->getSpecFilename();
        $parameters['locationHint'] = "php_qn://{$fileName}::\\{$suiteName}";

        $this->printEvent('testSuiteStarted', $parameters);
    }

    /**
     * @param SpecificationEvent $event
     */
    public function afterSpecification(SpecificationEvent $event): void
    {
        $suiteName = $event->getSpecification()->getResource()->getSpecClassname();

        if (empty($suiteName)) {
            return;
        }

        $this->printEvent('testSuiteFinished', ['name' => $suiteName]);
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'beforeSuite' => ['beforeSuite', -10],
            'afterSuite' => ['afterSuite', -10],
            'beforeSpecification' => ['beforeSpecification', -10],
            'afterSpecification' => ['afterSpecification', -10],
            'beforeExample' => ['beforeExample', -10],
            'afterExample' => ['afterExample', -10],
        ];
    }
}
