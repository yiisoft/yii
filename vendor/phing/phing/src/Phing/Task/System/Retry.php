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

namespace Phing\Task\System;

use Exception;
use Phing\Exception\BuildException;
use Phing\Task;
use Phing\TaskContainer;

/**
 * Retries the nested task a set number of times.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class Retry extends Task implements TaskContainer
{
    /**
     * Task to execute n times.
     *
     * @var Task
     */
    private $nestedTask;

    /**
     * Set retry count to 1 by default.
     *
     * @var int
     */
    private $retryCount = 1;

    /**
     * The time to wait between retries in seconds, default to 0.
     *
     * @var int
     */
    private $retryDelay = 0;

    /**
     * Set the task.
     *
     * @param Task $t the task to retry
     *
     * @throws BuildException
     */
    public function addTask(Task $t)
    {
        if (null !== $this->nestedTask) {
            throw new BuildException(
                'The retry task container accepts a single nested task'
                . ' (which may be a sequential task container)'
            );
        }
        $this->nestedTask = $t;
    }

    /**
     * Set the number of times to retry the task.
     *
     * @param int $n the number to use
     */
    public function setRetryCount($n)
    {
        $this->retryCount = $n;
    }

    /**
     * Set the delay between retries (in seconds).
     *
     * @param int $retryDelay the time between retries
     *
     * @throws BuildException
     */
    public function setRetryDelay($retryDelay)
    {
        if ($retryDelay < 0) {
            throw new BuildException(
                'retryDelay must be a non-negative number'
            );
        }
        $this->retryDelay = $retryDelay;
    }

    /**
     * Perform the work.
     *
     * @throws BuildException if there is an error
     */
    public function main()
    {
        $errorMessages = '';
        for ($i = 0; $i <= $this->retryCount; ++$i) {
            try {
                $this->nestedTask->perform();

                break;
            } catch (Exception $e) {
                $errorMessages .= $e->getMessage();
                if ($i >= $this->retryCount) {
                    $taskName = $this->nestedTask->getTaskName();
                    $exceptionMessage = <<<EXCEPTION_MESSAGE
                        Task [{$taskName}] failed after [{$this->retryCount}] attempts; giving up
                        Error messages:
                        {$errorMessages}
                        EXCEPTION_MESSAGE;

                    throw new BuildException(
                        $exceptionMessage,
                        $this->getLocation()
                    );
                }

                if ($this->retryDelay > 0) {
                    $msg = sprintf(
                        'Attempt [%s]: error occurred; retrying after %s s...',
                        $i,
                        $this->retryDelay
                    );
                } else {
                    $msg = sprintf(
                        'Attempt [%s]: error occurred; retrying...',
                        $i
                    );
                }

                $this->log($msg);
                $errorMessages .= PHP_EOL;

                if ($this->retryDelay > 0) {
                    sleep($this->retryDelay);
                }
            }
        }
    }
}
