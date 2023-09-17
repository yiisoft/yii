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

use Monolog\Logger;
use Phing\Project;
use Phing\Target;

/**
 * Listener which sends events to Monolog.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class MonologListener implements BuildListener
{
    /**
     * log category we log into.
     */
    public const LOG_PHING = 'phing';

    /**
     * @var Logger
     */
    private $log;

    /**
     * Construct the listener.
     */
    public function __construct()
    {
        $this->log = new Logger(self::LOG_PHING);
    }

    /**
     * @see BuildListener#buildStarted
     * {@inheritDoc}.
     */
    public function buildStarted(BuildEvent $event)
    {
        $log = $this->log->withName(Project::class);
        $log->info('Build started.');
    }

    /**
     * @see BuildListener#buildFinished
     * {@inheritDoc}.
     */
    public function buildFinished(BuildEvent $event)
    {
        $log = $this->log->withName(Project::class);
        if (null === $event->getException()) {
            $log->info('Build finished.');
        } else {
            $log->error('Build finished with error. ' . $event->getException());
        }
    }

    /**
     * @see BuildListener#targetStarted
     * {@inheritDoc}.
     */
    public function targetStarted(BuildEvent $event)
    {
        $log = $this->log->withName(Target::class);
        $log->info("Target \"{$event->getTarget()->getName()}\" started.");
    }

    /**
     * @see BuildListener#targetFinished
     * {@inheritDoc}.
     */
    public function targetFinished(BuildEvent $event)
    {
        $targetName = $event->getTarget()->getName();
        $cat = $this->log->withName(Target::class);
        if (null === $event->getException()) {
            $cat->info("Target \"{$targetName}\" finished.");
        } else {
            $cat->error("Target \"{$targetName}\" finished with error. " . $event->getException());
        }
    }

    /**
     * @see BuildListener#taskStarted
     * {@inheritDoc}.
     */
    public function taskStarted(BuildEvent $event)
    {
        $task = $event->getTask();
        $log = $this->log->withName(get_class($task));
        $log->info("Task \"{$task->getTaskName()}\" started.");
    }

    /**
     * @see BuildListener#taskFinished
     * {@inheritDoc}.
     */
    public function taskFinished(BuildEvent $event)
    {
        $task = $event->getTask();
        $log = $this->log->withName(get_class($task));
        if (null === $event->getException()) {
            $log->info("Task \"{$task->getTaskName()}\" finished.");
        } else {
            $log->error("Task \"{$task->getTaskName()}\" finished with error. {$event->getException()}");
        }
    }

    /**
     * @see BuildListener#messageLogged
     * {@inheritDoc}.
     */
    public function messageLogged(BuildEvent $event)
    {
        $categoryObject = $event->getTask();
        if (null === $categoryObject) {
            $categoryObject = $event->getTarget();
            if (null === $categoryObject) {
                $categoryObject = $event->getProject();
            }
        }

        $log = $this->log->withName(get_class($categoryObject));

        switch ($event->getPriority()) {
            case Project::MSG_WARN:
                $log->warning($event->getMessage());

                break;

            case Project::MSG_INFO:
                $log->info($event->getMessage());

                break;

            case Project::MSG_VERBOSE:
            case Project::MSG_DEBUG:
                $log->debug($event->getMessage());

                break;

            default:
                $log->error($event->getMessage());

                break;
        }
    }
}
