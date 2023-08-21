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
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\PhpSpecEvent;
use PhpSpec\Event\ResourceEvent;
use PhpSpec\Exception\Example\PendingException;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\IO\IO;
use PhpSpec\Listener\StatisticsCollector;
use PhpSpec\Message\CurrentExampleTracker;

abstract class ConsoleFormatter extends BasicFormatter implements FatalPresenter
{
    /**
     * @var ConsoleIO
     */
    private $io;

    
    public function __construct(Presenter $presenter, ConsoleIO $io, StatisticsCollector $stats)
    {
        parent::__construct($presenter, $io, $stats);
        $this->io = $io;
    }

    /**
     * @return ConsoleIO
     */
    protected function getIO(): IO
    {
        return $this->io;
    }

    protected function printException(ExampleEvent $event): void
    {
        if (null === $exception = $event->getException()) {
            return;
        }

        if ($exception instanceof PendingException) {
            $this->printSpecificException($event, 'pending');
        } elseif ($exception instanceof SkippingException) {
            if ($this->io->isVerbose()) {
                $this->printSpecificException($event, 'skipped');
            }
        } elseif (ExampleEvent::FAILED === $event->getResult()) {
            $this->printSpecificException($event, 'failed');
        } else {
            $this->printSpecificException($event, 'broken');
        }
    }

    protected function printIgnoredResource(ResourceEvent $event): void
    {
        $resource = $event->getResource();

        $this->io->writeln(sprintf(
            '<ignored-bg>%s</ignored-bg>',
            str_pad($resource->getSpecClassname(), $this->io->getBlockWidth()),
        ));
        $this->io->writeln('      <ignored>- cannot be autoloaded</ignored>');
        $this->io->writeln(sprintf('      <ignored>expected to find spec at path %s</ignored>.', $resource->getSpecFilename()));
        $this->io->writeln();
    }

    protected function printSpecificException(ExampleEvent $event, string $type): void
    {
        $title = str_replace('\\', DIRECTORY_SEPARATOR, $event->getSpecification()->getTitle());
        $message = $this->getPresenter()->presentException($event->getException(), $this->io->isVerbose());

        foreach (explode("\n", wordwrap($title, $this->io->getBlockWidth(), "\n", true)) as $line) {
            $this->io->writeln(sprintf('<%s-bg>%s</%s-bg>', $type, str_pad($line, $this->io->getBlockWidth()), $type));
        }

        $this->io->writeln(sprintf(
            '<lineno>%4d</lineno>  <%s>- %s</%s>',
            $event->getExample()->getLineNumber(),
            $type,
            $event->getExample()->getTitle(),
            $type
        ));
        $this->io->writeln(sprintf('<%s>%s</%s>', $type, lcfirst($message), $type), 6);
        $this->io->writeln();
    }

    public function displayFatal(CurrentExampleTracker $currentExample, $error): void
    {
        if (
            (null !== $error && ($currentExample->getCurrentExample() || $error['type'] == E_ERROR)) ||
            (\is_null($currentExample->getCurrentExample()) && \defined('HHVM_VERSION'))
        ) {
            ini_set('display_errors', "stderr");
            $failedOpen = ($this->io->isDecorated()) ? '<failed>' : '';
            $failedClosed = ($this->io->isDecorated()) ? '</failed>' : '';
            $failedCross = ($this->io->isDecorated()) ? '✘' : '';

            $this->io->writeln("$failedOpen$failedCross Fatal error happened while executing the following $failedClosed");
            $this->io->writeln("$failedOpen    {$currentExample->getCurrentExample()} $failedClosed");
            $this->io->writeln("$failedOpen    {$error['message']} in {$error['file']} on line {$error['line']} $failedClosed");
        }
    }
}
