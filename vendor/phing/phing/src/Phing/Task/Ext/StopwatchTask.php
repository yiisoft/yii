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

namespace Phing\Task\Ext;

use Phing\Dispatch\DispatchTask;
use Phing\Exception\BuildException;
use Phing\Project;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Stopwatch.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class StopwatchTask extends DispatchTask
{
    /**
     * Name of the timer.
     *
     * @var string
     */
    private $name = '';

    /**
     * Category of the timer.
     *
     * @var string optional
     */
    private $category = '';

    /**
     * Holds an instance of Stopwatch.
     *
     * @var Stopwatch
     */
    private static $timer;

    /**
     * Initialize Task.
     */
    public function init()
    {
        if (!class_exists('\\Symfony\\Component\\Stopwatch\\Stopwatch')) {
            throw new BuildException('StopwatchTask requires symfony/stopwatch to be installed.');
        }

        $this->setAction('start');
    }

    /**
     * Start timer.
     */
    public function start(): void
    {
        $timer = $this->getStopwatchInstance();
        $timer->start($this->name, $this->category);
    }

    /**
     * Stop timer.
     */
    public function stop(): void
    {
        $timer = $this->getStopwatchInstance();
        $event = $timer->stop($this->name);

        foreach ($event->getPeriods() as $period) {
            $this->log(
                'Starttime: ' . $period->getStartTime() . ' - Endtime: ' . $period->getEndTime() .
                    ' - Duration: ' . $period->getDuration() . ' - Memory: ' . $period->getMemory(),
                Project::MSG_INFO
            );
        }

        $this->log('Name:       ' . $this->name);
        $this->log('Category:   ' . $event->getCategory());
        $this->log('Origin:     ' . $event->getOrigin());
        $this->log('Start time: ' . $event->getStartTime());
        $this->log('End time:   ' . $event->getEndTime());
        $this->log('Duration:   ' . $event->getDuration());
        $this->log('Memory:     ' . $event->getMemory());
    }

    /**
     * Measure lap time.
     */
    public function lap(): void
    {
        $timer = $this->getStopwatchInstance();
        $timer->lap($this->name);
    }

    /**
     * Set the name of the stopwatch.
     *
     * @param string $name the name of the stopwatch timer
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Set the category of the stopwatch.
     *
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * The main entry point.
     *
     * @throws BuildException
     */
    public function main()
    {
        switch ($this->getAction()) {
            case 'start':
                $this->start();

                break;

            case 'stop':
                $this->stop();

                break;

            case 'lap':
                $this->lap();

                break;

            default:
                throw new BuildException('action should be one of start, stop, lap.');
        }
    }

    /**
     * Get the stopwatch instance.
     *
     * @return Stopwatch
     */
    private function getStopwatchInstance(): Stopwatch
    {
        if (null === self::$timer) {
            self::$timer = new Stopwatch();
        }

        return self::$timer;
    }
}
