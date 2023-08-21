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

namespace Phing\Util;

/**
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class StatsTimer
{
    protected $name;

    protected $series;

    protected $clock;

    public function __construct($name, Clock $clock)
    {
        $this->name = $name;
        $this->clock = $clock;
        $this->series = new Series();
    }

    public function start()
    {
        $duration = new Duration();
        $duration->setStartTime($this->clock->getCurrentTime());
        $this->series->add($duration);
    }

    public function finish()
    {
        $this->series->setFinishTime($this->clock->getCurrentTime());
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTime()
    {
        return $this->series->getTotalTime();
    }

    public function getSeries()
    {
        return $this->series;
    }
}
