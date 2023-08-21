<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Listener;

use Phing\Exception\BuildException;
use Phing\Io\IOException;
use Phing\Io\OutputStream;
use Phing\Project;
use Phing\Util\Clock;
use Phing\Util\DefaultClock;
use Phing\Util\ProjectTimer;
use Phing\Util\ProjectTimerMap;
use Phing\Util\StringHelper;

use function end;
use function fmod;
use function intdiv;
use function vsprintf;

/**
 * Writes a build event to the console.
 *
 * Currently, it only writes which targets are being executed, and
 * any messages that get logged.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 *
 * @see       BuildEvent
 */
class DefaultLogger implements StreamRequiredBuildLogger
{
    /**
     *  Size of the left column in output. The default char width is 12.
     *
     * @var int
     */
    public const LEFT_COLUMN_SIZE = 12;

    /**
     * A day in seconds.
     */
    protected const A_DAY = 86400;

    /**
     * An hour in seconds.
     */
    protected const AN_HOUR = 3600;

    /**
     * A minute in seconds.
     */
    protected const A_MINUTE = 60;

    /**
     *  The message output level that should be used. The default is
     *  <code>Project::MSG_VERBOSE</code>.
     *
     * @var int
     */
    protected $msgOutputLevel = Project::MSG_ERR;

    /**
     *  Time that the build started.
     *
     * @var int
     */
    protected $startTime;

    /**
     * @var OutputStream stream to use for standard output
     */
    protected $out;

    /**
     * @var OutputStream stream to use for error output
     */
    protected $err;

    protected $emacsMode = false;
    /**
     * @var Clock|DefaultClock
     */
    protected $clock;
    /**
     * @var ProjectTimerMap
     */
    protected $projectTimerMap;

    /**
     *  Construct a new default logger.
     */
    public function __construct(Clock $clock = null)
    {
        $this->projectTimerMap = new ProjectTimerMap();
        if (null === $clock) {
            $this->clock = new DefaultClock();
        } else {
            $this->clock = $clock;
        }
    }

    /**
     *  Set the msgOutputLevel this logger is to respond to.
     *
     *  Only messages with a message level lower than or equal to the given
     *  level are output to the log.
     *
     *  <p> Constants for the message levels are in Project.php. The order of
     *  the levels, from least to most verbose, is:
     *
     *  <ul>
     *    <li>Project::MSG_ERR</li>
     *    <li>Project::MSG_WARN</li>
     *    <li>Project::MSG_INFO</li>
     *    <li>Project::MSG_VERBOSE</li>
     *    <li>Project::MSG_DEBUG</li>
     *  </ul>
     *
     *  The default message level for DefaultLogger is Project::MSG_ERR.
     *
     * @param int $level the logging level for the logger
     *
     * @see   BuildLogger#setMessageOutputLevel()
     */
    public function setMessageOutputLevel($level)
    {
        $this->msgOutputLevel = (int) $level;
    }

    /**
     * Sets the output stream.
     *
     * @see   BuildLogger#setOutputStream()
     */
    public function setOutputStream(OutputStream $output)
    {
        $this->out = $output;
    }

    /**
     * Sets the error stream.
     *
     * @see   BuildLogger#setErrorStream()
     */
    public function setErrorStream(OutputStream $err)
    {
        $this->err = $err;
    }

    /**
     * Sets this logger to produce emacs (and other editor) friendly output.
     *
     * @param bool $emacsMode <code>true</code> if output is to be unadorned so that
     *                        emacs and other editors can parse files names, etc
     */
    public function setEmacsMode($emacsMode)
    {
        $this->emacsMode = $emacsMode;
    }

    /**
     *  Sets the start-time when the build started. Used for calculating
     *  the build-time.
     */
    public function buildStarted(BuildEvent $event)
    {
        $this->findInitialProjectTimer()->start();
        if ($this->msgOutputLevel >= Project::MSG_INFO) {
            $this->printMessage(
                'Buildfile: ' . $event->getProject()->getProperty('phing.file'),
                $this->out,
                Project::MSG_INFO
            );
        }
    }

    /**
     *  Prints whether the build succeeded or failed, and any errors that
     *  occurred during the build. Also outputs the total build-time.
     *
     * @see   BuildEvent::getException()
     */
    public function buildFinished(BuildEvent $event)
    {
        $projectTimer = $this->findProjectTimer($event);
        $this->updateDurationWithInitialProjectTimer($projectTimer);
        $projectTimer->finish();
        $msg = PHP_EOL . $this->getBuildSuccessfulMessage() . PHP_EOL;
        $error = $event->getException();

        if (null !== $error) {
            $msg = PHP_EOL . $this->getBuildFailedMessage() . PHP_EOL;

            self::throwableMessage($msg, $error, Project::MSG_VERBOSE <= $this->msgOutputLevel);
        }
        $msg .= PHP_EOL . 'Total time: '
            . static::formatTime($projectTimer->getTime()) . PHP_EOL;

        null === $error
            ? $this->printMessage($msg, $this->out, Project::MSG_VERBOSE)
            : $this->printMessage($msg, $this->err, Project::MSG_ERR);
    }

    public static function throwableMessage(&$msg, $error, $verbose)
    {
        while ($error instanceof BuildException) {
            $cause = $error->getPrevious();
            if (null === $cause) {
                break;
            }
            $msg1 = trim($error);
            $msg2 = trim($cause);
            if (StringHelper::endsWith($msg2, $msg1)) {
                $msg .= StringHelper::substring($msg1, 0, strlen($msg1) - strlen($msg2) - 1);
                $error = $cause;
            } else {
                break;
            }
        }

        if ($verbose) {
            if ($error instanceof BuildException) {
                $msg .= $error->getLocation() . PHP_EOL;
            }
            $msg .= '[' . get_class($error) . '] ' . $error->getMessage() . PHP_EOL
                . $error->getTraceAsString() . PHP_EOL;
        } else {
            $msg .= ($error instanceof BuildException ? $error->getLocation() . ' ' : '')
                . $error->getMessage() . PHP_EOL;
        }

        if ($error->getPrevious() && $verbose) {
            $error = $error->getPrevious();
            do {
                $msg .= '[Caused by ' . get_class($error) . '] ' . $error->getMessage() . PHP_EOL
                    . $error->getTraceAsString() . PHP_EOL;
            } while ($error = $error->getPrevious());
        }
    }

    /**
     *  Prints the current target name.
     *
     * @see   BuildEvent::getTarget()
     */
    public function targetStarted(BuildEvent $event)
    {
        if (
            Project::MSG_INFO <= $this->msgOutputLevel
            && '' != $event->getTarget()->getName()
        ) {
            $showLongTargets = $event->getProject()->getProperty('phing.showlongtargets');
            $msg = PHP_EOL . $event->getProject()->getName() . ' > ' . $event->getTarget()->getName()
                . ($showLongTargets ? ' [' . $event->getTarget()->getDescription() . ']' : '') . ':' . PHP_EOL;
            $this->printMessage($msg, $this->out, $event->getPriority());
        }
    }

    /**
     *  Fired when a target has finished. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @see   BuildEvent::getException()
     */
    public function targetFinished(BuildEvent $event)
    {
    }

    /**
     *  Fired when a task is started. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @see   BuildEvent::getTask()
     */
    public function taskStarted(BuildEvent $event)
    {
    }

    /**
     *  Fired when a task has finished. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @param BuildEvent $event The BuildEvent
     *
     * @see   BuildEvent::getException()
     */
    public function taskFinished(BuildEvent $event)
    {
    }

    /**
     *  Print a message to the stdout.
     *
     * @see   BuildEvent::getMessage()
     */
    public function messageLogged(BuildEvent $event)
    {
        $priority = $event->getPriority();
        if ($priority <= $this->msgOutputLevel) {
            $msg = '';
            if (null !== $event->getTask() && !$this->emacsMode) {
                $name = $event->getTask();
                $name = $name->getTaskName();
                $msg = str_pad("[{$name}] ", self::LEFT_COLUMN_SIZE, ' ', STR_PAD_LEFT);
            }

            $msg .= $event->getMessage();

            if (Project::MSG_ERR != $priority) {
                $this->printMessage($msg, $this->out, $priority);
            } else {
                $this->printMessage($msg, $this->err, $priority);
            }
        }
    }

    /**
     * Formats time (expressed in seconds) to a human readable format.
     *
     * @param float $seconds time to convert, can have decimals
     * @noinspection PhpMissingBreakStatementInspection
     */
    public static function formatTime(float $seconds): string
    {
        /** @var float|int $number */
        $getPlural = function ($number): string {
            return 1 == $number ? '' : 's';
        };
        $chunks = [];
        $format = '';
        $precision = 4;

        switch (true) {
            // Days
            case $seconds >= self::A_DAY:
                $chunks[] = intdiv((int) $seconds, self::A_DAY);
                $chunks[] = $getPlural(end($chunks));
                $seconds = fmod($seconds, self::A_DAY);
                $format .= '%u day%s  ';
            // Hours
            // no break
            case $seconds >= self::AN_HOUR:
                $chunks[] = intdiv((int) $seconds, self::AN_HOUR);
                $chunks[] = $getPlural(end($chunks));
                $seconds = fmod($seconds, self::AN_HOUR);
                $format .= '%u hour%s  ';
            // Minutes
            // no break
            case $seconds >= self::A_MINUTE:
                $chunks[] = intdiv((int) $seconds, self::A_MINUTE);
                $chunks[] = $getPlural(end($chunks));
                $seconds = fmod($seconds, self::A_MINUTE);
                $format .= '%u minute%s  ';
                $precision = 2;
            // Seconds
            // no break
            default:
                $chunks[] = $seconds;
                $chunks[] = $getPlural(end($chunks));
                $format .= "%.{$precision}F second%s";

                break;
        }

        return vsprintf($format, $chunks);
    }

    /**
     * Get the message to return when a build failed.
     *
     * @return string The classic "BUILD FAILED"
     */
    protected function getBuildFailedMessage()
    {
        return 'BUILD FAILED';
    }

    /**
     * Get the message to return when a build succeeded.
     *
     * @return string The classic "BUILD FINISHED"
     */
    protected function getBuildSuccessfulMessage()
    {
        return 'BUILD FINISHED';
    }

    /**
     * Prints a message to console.
     *
     * @param string                $message  The message to print.
     *                                        Should not be
     *                                        <code>null</code>.
     * @param OutputStream|resource $stream   the stream to use for message printing
     * @param int                   $priority The priority of the message.
     *                                        (Ignored in this
     *                                        implementation.)
     *
     * @throws IOException
     */
    protected function printMessage($message, OutputStream $stream, $priority)
    {
        $stream->write($message . PHP_EOL);
    }

    protected function findInitialProjectTimer()
    {
        return $this->projectTimerMap->find('', $this->clock);
    }

    private function findProjectTimer(BuildEvent $buildEvent)
    {
        $project = $buildEvent->getProject();

        return $this->projectTimerMap->find($project, $this->clock);
    }

    private function updateDurationWithInitialProjectTimer(ProjectTimer $projectTimer)
    {
        $rootProjectTimer = $this->findInitialProjectTimer();
        $duration = $rootProjectTimer->getSeries()->current();
        $projectTimer->getSeries()->add($duration);
    }
}
