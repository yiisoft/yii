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
class TimerMap
{
    protected $map = [];

    public function get($name)
    {
        return $this->map[$name];
    }

    public function find($name, Clock $clock)
    {
        $timer = $this->map[$name] ?? null;
        if (null === $timer) {
            $timer = $this->createTimer($name, $clock);
            $this->map[$name] = $timer;
        }

        return $timer;
    }

    public function toSeriesMap()
    {
        $seriesMap = new SeriesMap();
        foreach ($this->map as $key => $timer) {
            $seriesMap->put($key, $timer->getSeries());
        }

        return $seriesMap;
    }

    protected function createTimer($name, Clock $clock)
    {
        return new SeriesTimer($name, $clock);
    }
}
