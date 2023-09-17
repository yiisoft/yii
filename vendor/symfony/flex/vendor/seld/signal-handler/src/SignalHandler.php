<?php

/*
 * This file is part of signal-handler.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seld\Signal;

use Psr\Log\LoggerInterface;
use Closure;
use WeakReference;

/**
 * SignalHandler and factory
 */
final class SignalHandler
{
    /**
     * The SIGHUP signal is sent to a process when its controlling terminal is closed. It was originally designed to
     * notify the process of a serial line drop (a hangup). In modern systems, this signal usually means that the
     * controlling pseudo or virtual terminal has been closed. Many daemons will reload their configuration files and
     * reopen their logfiles instead of exiting when receiving this signal. nohup is a command to make a command ignore
     * the signal.
     */
    public const SIGHUP = 'SIGHUP';

    /**
     * The SIGINT signal is sent to a process by its controlling terminal when a user wishes to interrupt the process.
     * This is typically initiated by pressing Ctrl-C, but on some systems, the "delete" character or "break" key can be
     * used.
     *
     * On Windows this is used to denote a PHP_WINDOWS_EVENT_CTRL_C
     */
    public const SIGINT = 'SIGINT';

    /**
     * The SIGQUIT signal is sent to a process by its controlling terminal when the user requests that the process quit
     * and perform a core dump.
     */
    public const SIGQUIT = 'SIGQUIT';

    /**
     * The SIGILL signal is sent to a process when it attempts to execute an illegal, malformed, unknown, or privileged
     * instruction.
     */
    public const SIGILL = 'SIGILL';

    /**
     * The SIGTRAP signal is sent to a process when an exception (or trap) occurs: a condition that a debugger has
     * requested to be informed of — for example, when a particular function is executed, or when a particular variable
     * changes value.
     */
    public const SIGTRAP = 'SIGTRAP';

    /**
     * The SIGABRT signal is sent to a process to tell it to abort, i.e. to terminate. The signal is usually initiated
     * by the process itself when it calls abort function of the C Standard Library, but it can be sent to the process
     * from outside like any other signal.
     */
    public const SIGABRT = 'SIGABRT';

    public const SIGIOT = 'SIGIOT';

    /**
     * The SIGBUS signal is sent to a process when it causes a bus error. The conditions that lead to the signal being
     * sent are, for example, incorrect memory access alignment or non-existent physical address.
     */
    public const SIGBUS = 'SIGBUS';

    public const SIGFPE = 'SIGFPE';

    /**
     * The SIGKILL signal is sent to a process to cause it to terminate immediately (kill). In contrast to SIGTERM and
     * SIGINT, this signal cannot be caught or ignored, and the receiving process cannot perform any clean-up upon
     * receiving this signal.
     */
    public const SIGKILL = 'SIGKILL';

    /**
     * The SIGUSR1 signal is sent to a process to indicate user-defined conditions.
     */
    public const SIGUSR1 = 'SIGUSR1';

    /**
     * The SIGUSR1 signa2 is sent to a process to indicate user-defined conditions.
     */
    public const SIGUSR2 = 'SIGUSR2';

    /**
     * The SIGSEGV signal is sent to a process when it makes an invalid virtual memory reference, or segmentation fault,
     * i.e. when it performs a segmentation violation.
     */
    public const SIGSEGV = 'SIGSEGV';

    /**
     * The SIGPIPE signal is sent to a process when it attempts to write to a pipe without a process connected to the
     * other end.
     */
    public const SIGPIPE = 'SIGPIPE';

    /**
     * The SIGALRM, SIGVTALRM and SIGPROF signal is sent to a process when the time limit specified in a call to a
     * preceding alarm setting function (such as setitimer) elapses. SIGALRM is sent when real or clock time elapses.
     * SIGVTALRM is sent when CPU time used by the process elapses. SIGPROF is sent when CPU time used by the process
     * and by the system on behalf of the process elapses.
     */
    public const SIGALRM = 'SIGALRM';

    /**
     * The SIGTERM signal is sent to a process to request its termination. Unlike the SIGKILL signal, it can be caught
     * and interpreted or ignored by the process. This allows the process to perform nice termination releasing
     * resources and saving state if appropriate. SIGINT is nearly identical to SIGTERM.
     */
    public const SIGTERM = 'SIGTERM';

    public const SIGSTKFLT = 'SIGSTKFLT';
    public const SIGCLD = 'SIGCLD';

    /**
     * The SIGCHLD signal is sent to a process when a child process terminates, is interrupted, or resumes after being
     * interrupted. One common usage of the signal is to instruct the operating system to clean up the resources used by
     * a child process after its termination without an explicit call to the wait system call.
     */
    public const SIGCHLD = 'SIGCHLD';

    /**
     * The SIGCONT signal instructs the operating system to continue (restart) a process previously paused by the
     * SIGSTOP or SIGTSTP signal. One important use of this signal is in job control in the Unix shell.
     */
    public const SIGCONT = 'SIGCONT';

    /**
     * The SIGSTOP signal instructs the operating system to stop a process for later resumption.
     */
    public const SIGSTOP = 'SIGSTOP';

    /**
     * The SIGTSTP signal is sent to a process by its controlling terminal to request it to stop (terminal stop). It is
     * commonly initiated by the user pressing Ctrl+Z. Unlike SIGSTOP, the process can register a signal handler for or
     * ignore the signal.
     */
    public const SIGTSTP = 'SIGTSTP';

    /**
     * The SIGTTIN signal is sent to a process when it attempts to read in from the tty while in the background.
     * Typically, this signal is received only by processes under job control; daemons do not have controlling
     */
    public const SIGTTIN = 'SIGTTIN';

    /**
     * The SIGTTOU signal is sent to a process when it attempts to write out from the tty while in the background.
     * Typically, this signal is received only by processes under job control; daemons do not have controlling
     */
    public const SIGTTOU = 'SIGTTOU';

    /**
     * The SIGURG signal is sent to a process when a socket has urgent or out-of-band data available to read.
     */
    public const SIGURG = 'SIGURG';

    /**
     * The SIGXCPU signal is sent to a process when it has used up the CPU for a duration that exceeds a certain
     * predetermined user-settable value. The arrival of a SIGXCPU signal provides the receiving process a chance to
     * quickly save any intermediate results and to exit gracefully, before it is terminated by the operating system
     * using the SIGKILL signal.
     */
    public const SIGXCPU = 'SIGXCPU';

    /**
     * The SIGXFSZ signal is sent to a process when it grows a file larger than the maximum allowed size
     */
    public const SIGXFSZ = 'SIGXFSZ';

    /**
     * The SIGVTALRM signal is sent to a process when the time limit specified in a call to a preceding alarm setting
     * function (such as setitimer) elapses. SIGVTALRM is sent when CPU time used by the process elapses.
     */
    public const SIGVTALRM = 'SIGVTALRM';

    /**
     * The SIGPROF signal is sent to a process when the time limit specified in a call to a preceding alarm setting
     * function (such as setitimer) elapses. SIGPROF is sent when CPU time used by the process and by the system on
     * behalf of the process elapses.
     */
    public const SIGPROF = 'SIGPROF';

    /**
     * The SIGWINCH signal is sent to a process when its controlling terminal changes its size (a window change).
     */
    public const SIGWINCH = 'SIGWINCH';

    /**
     * The SIGPOLL signal is sent when an event occurred on an explicitly watched file descriptor.Using it effectively
     * leads to making asynchronous I/O requests since the kernel will poll the descriptor in place of the caller. It
     * provides an alternative to active polling.
     */
    public const SIGPOLL = 'SIGPOLL';

    public const SIGIO = 'SIGIO';

    /**
     * The SIGPWR signal is sent to a process when the system experiences a power failure.
     */
    public const SIGPWR = 'SIGPWR';

    /**
     * The SIGSYS signal is sent to a process when it passes a bad argument to a system call. In practice, this kind of
     * signal is rarely encountered since applications rely on libraries (e.g. libc) to make the call for them.
     */
    public const SIGSYS = 'SIGSYS';

    public const SIGBABY = 'SIGBABY';

    /**
     * CTRL+Break support, available on Windows only for PHP_WINDOWS_EVENT_CTRL_BREAK
     */
    public const SIGBREAK = 'SIGBREAK';

    private const ALL_SIGNALS = [
        self::SIGHUP, self::SIGINT, self::SIGQUIT, self::SIGILL, self::SIGTRAP, self::SIGABRT, self::SIGIOT, self::SIGBUS,
        self::SIGFPE, self::SIGKILL, self::SIGUSR1, self::SIGUSR2, self::SIGSEGV, self::SIGPIPE, self::SIGALRM, self::SIGTERM,
        self::SIGSTKFLT, self::SIGCLD, self::SIGCHLD, self::SIGCONT, self::SIGSTOP, self::SIGTSTP, self::SIGTTIN, self::SIGTTOU,
        self::SIGURG, self::SIGXCPU, self::SIGXFSZ, self::SIGVTALRM, self::SIGPROF, self::SIGWINCH, self::SIGPOLL, self::SIGIO,
        self::SIGPWR, self::SIGSYS, self::SIGBABY, self::SIGBREAK
    ];

    /**
     * @var self::SIG*|null
     */
    private $triggered = null;

    /**
     * @var list<self::SIG*>
     * @readonly
     */
    private $signals;

    /**
     * @var LoggerInterface|(callable(self::SIG* $name, SignalHandler $self): void)|null
     * @readonly
     */
    private $loggerOrCallback;

    /**
     * @var array<int, self|WeakReference<self>>
     */
    private static $handlers = [];

    /** @var Closure|null */
    private static $windowsHandler = null;

    /**
     * @param array<self::SIG*> $signals
     * @param LoggerInterface|(callable(self::SIG* $name, SignalHandler $self): void)|null $loggerOrCallback
     */
    private function __construct(array $signals, $loggerOrCallback)
    {
        if (!is_callable($loggerOrCallback) && !$loggerOrCallback instanceof LoggerInterface && $loggerOrCallback !== null) {
            throw new \InvalidArgumentException('$loggerOrCallback must be a '.LoggerInterface::class.' instance, a callable, or null, '.(is_object($loggerOrCallback) ? get_class($loggerOrCallback) : gettype($loggerOrCallback)).' received.');
        }

        $this->signals = $signals;
        $this->loggerOrCallback = $loggerOrCallback;
    }

    /**
     * @param self::SIG* $signalName
     */
    private function trigger(string $signalName): void
    {
        $this->triggered = $signalName;

        if ($this->loggerOrCallback instanceof LoggerInterface) {
            $this->loggerOrCallback->info('Received '.$signalName);
        } elseif ($this->loggerOrCallback !== null) {
            ($this->loggerOrCallback)($signalName, $this);
        }
    }

    /**
     * Fetches the triggered state of the handler
     *
     * @phpstan-impure
     */
    public function isTriggered(): bool
    {
        return $this->triggered !== null;
    }

    /**
     * Exits the process while communicating that the handled signal was what killed the process
     *
     * This is different from doing exit(SIGINT), and is also different to a successful exit(0).
     *
     * This should only be used when you received a signal and then handled it to gracefully shutdown and are now ready to shutdown.
     *
     * ```
     * $signal = SignalHandler::create([SignalHandler::SIGINT], function (string $signal, SignalHandler $handler) {
     *     // do cleanup here..
     *
     *     $handler->exitWithLastSignal();
     * });
     *
     * // or...
     *
     * $signal = SignalHandler::create([SignalHandler::SIGINT]);
     *
     * while ($doingThings) {
     *     if ($signal->isTriggered()) {
     *         $signal->exitWithLastSignal();
     *     }
     *
     *     // do more things
     * }
     * ```
     *
     * @see https://www.cons.org/cracauer/sigint.html
     * @return never
     */
    public function exitWithLastSignal(): void
    {
        $signal = $this->triggered ?? 'SIGINT';
        $signal = defined($signal) ? constant($signal) : 2;

        if (function_exists('posix_kill') && function_exists('posix_getpid')) {
            pcntl_signal($signal, SIG_DFL);
            posix_kill(posix_getpid(), $signal);
        }

        // just in case posix_kill above could not run
        // not strictly correct but it's the best we can do here
        exit(128 + $signal);
    }

    /**
     * Resets the state to let a handler accept a signal again
     */
    public function reset(): void
    {
        $this->triggered = null;
    }

    public function __destruct()
    {
        $this->unregister();
    }

    /**
     * @param (string|int)[] $signals array of signal names (more portable, see SignalHandler::SIG*) or constants - defaults to [SIGINT, SIGTERM]
     * @param LoggerInterface|callable $loggerOrCallback A PSR-3 Logger or a callback($signal, $signalName)
     * @return self A handler on which you can call isTriggered to know if the signal was received, and reset() to forget
     *
     * @phpstan-param list<self::SIG*|int> $signals
     * @phpstan-param LoggerInterface|(callable(self::SIG* $name, SignalHandler $self): void) $loggerOrCallback
     */
    public static function create(?array $signals = null, $loggerOrCallback = null): self
    {
        if ($signals === null) {
            $signals = [self::SIGINT, self::SIGTERM];
        }
        $signals = array_map(function ($signal) {
            if (is_int($signal)) {
                return self::getSignalName($signal);
            } elseif (!in_array($signal, self::ALL_SIGNALS, true)) {
                throw new \InvalidArgumentException('$signals must be an array of SIG* constants or self::SIG* constants, got '.var_export($signal, true));
            }
            return $signal;
        }, (array) $signals);

        $handler = new self($signals, $loggerOrCallback);

        if (PHP_VERSION_ID >= 80000) {
            array_unshift(self::$handlers, WeakReference::create($handler));
        } else {
            array_unshift(self::$handlers, $handler);
        }

        if (function_exists('sapi_windows_set_ctrl_handler') && PHP_SAPI === 'cli' && (in_array(self::SIGINT, $signals, true) || in_array(self::SIGBREAK, $signals, true))) {
            if (null === self::$windowsHandler) {
                self::$windowsHandler = Closure::fromCallable([self::class, 'handleWindowsSignal']);
                sapi_windows_set_ctrl_handler(self::$windowsHandler);
            }
        }

        if (function_exists('pcntl_signal') && function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);

            self::registerPcntlHandler($signals);
        }

        return $handler;
    }

    /**
     * Clears the signal handler
     *
     * On PHP 8+ this is not necessary and it will happen automatically on __destruct, but PHP 7 does not
     * support weak references and thus there you need to manually do this.
     *
     * If another handler was registered previously to this one, it becomes active again
     */
    public function unregister(): void
    {
        $signals = $this->signals;

        $index = false;
        foreach (self::$handlers as $key => $handler) {
            if (($handler instanceof WeakReference && $handler->get() === $this) || $handler === $this) {
                $index = $key;
                break;
            }
        }
        if ($index === false) {
            // guard against double-unregistration when __destruct happens
            return;
        }

        unset(self::$handlers[$index]);

        if (self::$windowsHandler !== null && (in_array(self::SIGINT, $signals, true) || in_array(self::SIGBREAK, $signals, true))) {
            if (self::getHandlerFor(self::SIGINT) === null && self::getHandlerFor(self::SIGBREAK) === null) {
                sapi_windows_set_ctrl_handler(self::$windowsHandler, false);
                self::$windowsHandler = null;
            }
        }

        if (function_exists('pcntl_signal')) {
            foreach ($signals as $signal) {
                // skip missing signals, for example OSX does not have all signals
                if (!defined($signal)) {
                    continue;
                }

                // keep listening to signals where we have a handler registered
                if (self::getHandlerFor($signal) !== null) {
                    continue;
                }

                pcntl_signal(constant($signal), SIG_DFL);
            }
        }
    }

    /**
     * Clears all signal handlers
     *
     * On PHP 8+ this should not be necessary as it will happen automatically on __destruct, but PHP 7 does not
     * support weak references and thus there you need to manually do this.
     *
     * This can be done to reset the global state, but ideally you should always call ->unregister() in a try/finally block to ensure it happens.
     */
    public static function unregisterAll(): void
    {
        if (self::$windowsHandler !== null) {
            sapi_windows_set_ctrl_handler(self::$windowsHandler, false);
            self::$windowsHandler = null;
        }

        foreach (self::$handlers as $key => $handler) {
            if ($handler instanceof WeakReference) {
                $handler = $handler->get();
                if ($handler === null) {
                    unset(self::$handlers[$key]);
                    continue;
                }
            }
            $handler->unregister();
        }
    }

    /**
     * @param list<self::SIG*> $signals
     */
    private static function registerPcntlHandler(array $signals): void
    {
        static $callable;
        if ($callable === null) {
            $callable = Closure::fromCallable([self::class, 'handlePcntlSignal']);
        }
        foreach ($signals as $signal) {
            // skip missing signals, for example OSX does not have all signals
            if (!defined($signal)) {
                continue;
            }

            pcntl_signal(constant($signal), $callable);
        }
    }

    private static function handleWindowsSignal(int $event): void
    {
        if (PHP_WINDOWS_EVENT_CTRL_C === $event) {
            self::callHandlerFor(self::SIGINT);
        } elseif (PHP_WINDOWS_EVENT_CTRL_BREAK === $event) {
            self::callHandlerFor(self::SIGBREAK);
        }
    }

    private static function handlePcntlSignal(int $signal): void
    {
        self::callHandlerFor(self::getSignalName($signal));
    }

    /**
     * Calls the first handler from the top of the stack that can handle a given signal
     *
     * @param self::SIG* $signal
     */
    private static function callHandlerFor(string $signal): void
    {
        $handler = self::getHandlerFor($signal);
        if ($handler !== null) {
            $handler->trigger($signal);
        }
    }

    /**
     * Returns the first handler from the top of the stack that can handle a given signal
     *
     * @param self::SIG* $signal
     * @return self|null
     */
    private static function getHandlerFor(string $signal): ?self
    {
        foreach (self::$handlers as $key => $handler) {
            if ($handler instanceof WeakReference) {
                $handler = $handler->get();
                if ($handler === null) {
                    unset(self::$handlers[$key]);
                    continue;
                }
            }
            if (in_array($signal, $handler->signals, true)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * @return self::SIG*
     */
    private static function getSignalName(int $signo): string
    {
        static $signals = null;
        if ($signals === null) {
            $signals = [];
            foreach (self::ALL_SIGNALS as $value) {
                if (defined($value)) {
                    $signals[constant($value)] = $value;
                }
            }
        }

        if (isset($signals[$signo])) {
            return $signals[$signo];
        }

        throw new \InvalidArgumentException('Unknown signal #'.$signo);
    }
}
