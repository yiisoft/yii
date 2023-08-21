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

use Phing\Exception\BuildException;
use Phing\Task;
use Throwable;

/**
 * A phing sleep task.
 *
 * <p>A task for sleeping a short period of time, useful when a
 * build or deployment process requires an interval between tasks.</p>
 *
 * <p>A negative value can be supplied to any of attributes provided the total sleep time
 * is positive, pending fundamental changes in physics and PHP execution times.</p>
 *
 * <p>Note that sleep times are always hints to be interpreted by the OS how it feels
 * small times may either be ignored or rounded up to a minimum timeslice. Note
 * also that the system clocks often have a fairly low granularity too, which complicates
 * measuring how long a sleep actually took.</p>
 *
 * @author  Daniel Kutik, daniel@kutik.eu
 */
class SleepTask extends Task
{
    /**
     * failure flag.
     *
     * @var bool
     */
    private $failOnError = true;

    /**
     * sleep seconds.
     */
    private $seconds = 0;

    /**
     * sleep hours.
     */
    private $hours = 0;
    /**
     * sleep minutes.
     */
    private $minutes = 0;

    /**
     * sleep milliseconds.
     */
    private $milliseconds = 0;

    public function setFailOnError(bool $var): void
    {
        $this->failOnError = $var;
    }

    public function getFailOnError(): bool
    {
        return $this->failOnError;
    }

    public function setHours($hours)
    {
        $this->hours = $hours;
    }

    public function getHours()
    {
        return $this->hours;
    }

    public function setMilliseconds($milliseconds): void
    {
        $this->milliseconds = $milliseconds;
    }

    public function getMilliseconds()
    {
        return $this->milliseconds;
    }

    public function setMinutes($minutes): void
    {
        $this->minutes = $minutes;
    }

    public function getMinutes()
    {
        return $this->minutes;
    }

    public function setSeconds($seconds): void
    {
        $this->seconds = $seconds;
    }

    public function getSeconds()
    {
        return $this->seconds;
    }

    public function main()
    {
        try {
            $this->validateAttributes();
            $sleepTime = $this->getSleepTime();
            usleep($sleepTime * 1000);
        } catch (Throwable $e) {
            if ($this->failOnError) {
                throw new BuildException($e);
            }
        }
    }

    /**
     * return time to sleep.
     *
     * @return int time. if below 0 then there is an error
     */
    private function getSleepTime()
    {
        return ((($this->hours * 60) + $this->minutes) * 60 + $this->seconds) * 1000 + $this->milliseconds;
    }

    /**
     * verify parameters.
     *
     * @throws BuildException if something is invalid
     */
    private function validateAttributes(): void
    {
        if ($this->getSleepTime() < 0) {
            throw new BuildException('Negative sleep periods are not supported');
        }
    }
}
