<?php

namespace Seld\Signal;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SignalHandlerTest extends TestCase
{
    /**
     * @requires extension pcntl
     * @requires extension posix
     */
    public function testLoggingAndDefault(): void
    {
        $log = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $signal = SignalHandler::create(null, $log);
        $log->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Received SIGINT', []],
                ['Received SIGTERM', []]
            );

        posix_kill(posix_getpid(), SIGINT);
        posix_kill(posix_getpid(), SIGTERM);
        posix_kill(posix_getpid(), SIGURG);
    }

    /**
     * @requires extension pcntl
     * @requires extension posix
     * @requires PHP < 8.0
     */
    public function testNoAutoGCOnPHP7(): void
    {
        $log = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $signal = SignalHandler::create(null, $log);
        $log->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Received SIGINT', []],
                ['Received SIGINT', []]
            );

        posix_kill(posix_getpid(), SIGINT);
        unset($signal);
        posix_kill(posix_getpid(), SIGINT);
        SignalHandler::unregisterAll();
        self::assertSame(SIG_DFL, pcntl_signal_get_handler(SIGINT));
    }

    /**
     * @requires extension pcntl
     * @requires extension posix
     * @requires PHP >= 8.0
     */
    public function testAutoGCOnPHP8(): void
    {
        $log1 = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $signal1 = SignalHandler::create(null, $log1);
        $log1->expects(self::exactly(1))
            ->method('info')
            ->withConsecutive(
                ['Received SIGINT', []]
            );

        $log2 = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $signal2 = SignalHandler::create(null, $log2);
        $log2->expects(self::exactly(1))
            ->method('info')
            ->withConsecutive(
                ['Received SIGINT', []]
            );

        posix_kill(posix_getpid(), SIGINT);
        unset($signal2);
        posix_kill(posix_getpid(), SIGINT);
        unset($signal1);
        self::assertSame(SIG_DFL, pcntl_signal_get_handler(SIGINT));
    }

    /**
     * @requires extension pcntl
     * @requires extension posix
     */
    public function testCallbackAndCustom(): void
    {
        pcntl_signal(SIGINT, function () {
            // ignore
        });

        $sigName = null;

        $signal = SignalHandler::create([SignalHandler::SIGHUP], function ($name) use (&$sigName) {
            $sigName = $name;
        });

        posix_kill(posix_getpid(), SIGINT);
        self::assertNull($sigName);

        posix_kill(posix_getpid(), SIGHUP);
        self::assertSame('SIGHUP', $sigName);

        $signal->unregister();
    }

    /**
     * @requires extension pcntl
     * @requires extension posix
     */
    public function testTriggerResetCycle(): void
    {
        $signal = SignalHandler::create([SignalHandler::SIGUSR1, SignalHandler::SIGUSR2]);

        self::assertFalse($signal->isTriggered());
        posix_kill(posix_getpid(), SIGUSR1);
        self::assertTrue($signal->isTriggered());

        $signal->reset();
        self::assertFalse($signal->isTriggered());
        posix_kill(posix_getpid(), SIGUSR2);
        self::assertTrue($signal->isTriggered());

        $signal->unregister();
    }

    /**
     * @requires extension pcntl
     * @requires extension posix
     */
    public function testNestingWorks(): void
    {
        $log1 = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $signal1 = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGHUP], $log1);
        $log1->expects(self::exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Received SIGHUP', []],
                ['Received SIGINT', []]
            );

        $log2 = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $signal2 = SignalHandler::create([SignalHandler::SIGINT], $log2);
        $log2->expects(self::exactly(1))
            ->method('info')
            ->withConsecutive(
                ['Received SIGINT', []]
            );

        posix_kill(posix_getpid(), SIGINT);
        posix_kill(posix_getpid(), SIGHUP);
        $signal2->unregister();
        unset($signal2);

        self::assertNotSame(SIG_DFL, pcntl_signal_get_handler(SIGINT));
        self::assertNotSame(SIG_DFL, pcntl_signal_get_handler(SIGHUP));

        posix_kill(posix_getpid(), SIGINT);
        $signal1->unregister();
        unset($signal1);
        self::assertSame(SIG_DFL, pcntl_signal_get_handler(SIGINT));
        self::assertSame(SIG_DFL, pcntl_signal_get_handler(SIGHUP));
    }

    /**
     * @requires OSFAMILY Windows
     * @requires PHP >= 7.4
     */
    public function testLoggingAndDefaultOnWindows(): void
    {
        $log = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $signal = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGBREAK], $log);
        $log->expects(self::atLeastOnce())
            ->method('info')
            ->with(self::equalTo('Received SIGBREAK'));

        $this->dispatchWindowsSignal($signal, PHP_WINDOWS_EVENT_CTRL_BREAK);

        $signal->unregister();
    }

    /**
     * @requires OSFAMILY Windows
     * @requires PHP >= 7.4
     */
    public function testCallbackAndCustomOnWindows(): void
    {
        $sigName = null;

        $signal = SignalHandler::create([SignalHandler::SIGBREAK], function ($name) use (&$sigName) {
            $sigName = $name;
        });

        $this->dispatchWindowsSignal($signal, PHP_WINDOWS_EVENT_CTRL_BREAK);
        self::assertSame(SignalHandler::SIGBREAK, $sigName);

        $signal->unregister();
    }

    /**
     * @requires OSFAMILY Windows
     * @requires PHP >= 7.4
     */
    public function testTriggerResetCycleOnWindows(): void
    {
        $signal = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGBREAK]);

        self::assertFalse($signal->isTriggered());
        $this->dispatchWindowsSignal($signal, PHP_WINDOWS_EVENT_CTRL_BREAK);
        self::assertTrue($signal->isTriggered());

        $signal->reset();
        self::assertFalse($signal->isTriggered());
        $this->dispatchWindowsSignal($signal, PHP_WINDOWS_EVENT_CTRL_BREAK);
        self::assertTrue($signal->isTriggered());

        $signal->unregister();
    }

    /**
     * @requires OSFAMILY Windows
     * @requires PHP >= 7.4
     */
    public function testNestingWorksOnWindows(): void
    {
        $signal1 = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGBREAK]);

        $signal2 = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGBREAK]);

        self::assertFalse($signal1->isTriggered());
        self::assertFalse($signal2->isTriggered());
        $this->dispatchWindowsSignal($signal2, PHP_WINDOWS_EVENT_CTRL_BREAK);
        self::assertFalse($signal1->isTriggered());
        self::assertTrue($signal2->isTriggered());

        $signal2->unregister();
        unset($signal2);

        $this->dispatchWindowsSignal($signal1, PHP_WINDOWS_EVENT_CTRL_BREAK);
        self::assertTrue($signal1->isTriggered());

        $signal1->unregister();
        unset($signal1);
    }

    private function dispatchWindowsSignal(SignalHandler $handler, int $signal): void
    {
        sapi_windows_generate_ctrl_event($signal);
        sapi_windows_generate_ctrl_event($signal);
        sapi_windows_generate_ctrl_event($signal);

        // waits to try and get the signal handler to trigger
        $tries = 10;
        while (!$handler->isTriggered() && $tries-- > 0) {
            sapi_windows_generate_ctrl_event($signal);
            usleep(500000);
        }
    }
}
