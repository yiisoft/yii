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

use Phing\Util\Clock;
use Phing\Util\DefaultClock;
use Phing\Util\ProjectTimer;
use Phing\Util\ProjectTimerMap;
use Phing\Util\SeriesTimer;
use Phing\Util\StatisticsReport;

/**
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class StatisticsListener implements SubBuildListener
{
    /**
     * @var ProjectTimerMap
     */
    protected $projectTimerMap;
    private static $BUILDEVENT_PROJECT_NAME_HAS_NULL_VALUE = true;

    /**
     * @var Clock
     */
    private $clock;

    /**
     * @var StatisticsReport
     */
    private $statisticsReport;

    public function __construct(Clock $clock = null)
    {
        $this->projectTimerMap = new ProjectTimerMap();
        $this->statisticsReport = new StatisticsReport();
        if (null === $clock) {
            $this->clock = new DefaultClock();
        } else {
            $this->clock = $clock;
        }
    }

    public function buildStarted(BuildEvent $buildEvent)
    {
        if (self::$BUILDEVENT_PROJECT_NAME_HAS_NULL_VALUE) {
            $this->findInitialProjectTimer()->start();
        }
    }

    public function buildFinished(BuildEvent $buildEvent)
    {
        $projectTimer = $this->findProjectTimer($buildEvent);
        $this->updateDurationWithInitialProjectTimer($projectTimer);
        $this->buildFinishedTimer($projectTimer);
        $this->statisticsReport->write();
    }

    public function targetStarted(BuildEvent $buildEvent)
    {
        $this->findTargetTimer($buildEvent)->start();
    }

    public function targetFinished(BuildEvent $buildEvent)
    {
        $this->findTargetTimer($buildEvent)->finish();
    }

    public function taskStarted(BuildEvent $buildEvent)
    {
        $this->findTaskTimer($buildEvent)->start();
    }

    public function taskFinished(BuildEvent $buildEvent)
    {
        $this->findTaskTimer($buildEvent)->finish();
    }

    public function messageLogged(BuildEvent $buildEvent)
    {
    }

    public function subBuildStarted(BuildEvent $buildEvent)
    {
        $this->findProjectTimer($buildEvent)->start();
    }

    public function subBuildFinished(BuildEvent $buildEvent)
    {
        $projectTimer = $this->findProjectTimer($buildEvent);
        $this->buildFinishedTimer($projectTimer);
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

    /**
     * @return SeriesTimer
     */
    private function findTargetTimer(BuildEvent $buildEvent)
    {
        $projectTimer = $this->findProjectTimer($buildEvent);
        $target = $buildEvent->getTarget();
        $name = $target->getName();

        return $projectTimer->getTargetTimer($name);
    }

    /**
     * @return SeriesTimer
     */
    private function findTaskTimer(BuildEvent $buildEvent)
    {
        $projectTimer = $this->findProjectTimer($buildEvent);
        $task = $buildEvent->getTask();
        $name = $task->getTaskName();

        return $projectTimer->getTaskTimer($name);
    }

    private function buildFinishedTimer(ProjectTimer $projectTimer)
    {
        $projectTimer->finish();
        $this->statisticsReport->push($projectTimer);
    }

    private function updateDurationWithInitialProjectTimer(ProjectTimer $projectTimer)
    {
        $rootProjectTimer = $this->findInitialProjectTimer();
        $duration = $rootProjectTimer->getSeries()->current();
        $projectTimer->getSeries()->add($duration);
    }
}
