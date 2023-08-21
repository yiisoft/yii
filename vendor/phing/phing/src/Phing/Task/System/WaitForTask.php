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
use Phing\Project;
use Phing\Task\System\Condition\ConditionBase;
use Phing\Task\System\Condition\ConditionEnumeration;

/**
 *  Based on Apache Ant Wait For:.
 *
 *  Licensed to the Apache Software Foundation (ASF) under one or more
 *  contributor license agreements.  See the NOTICE file distributed with
 *  this work for additional information regarding copyright ownership.
 *  The ASF licenses this file to You under the Apache License, Version 2.0
 *  (the "License"); you may not use this file except in compliance with
 *  the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 * @author  Michiel Rook <mrook@php.net>
 */
class WaitForTask extends ConditionBase
{
    public const ONE_MILLISECOND = 1;
    public const ONE_SECOND = 1000;
    public const ONE_MINUTE = 60000;
    public const ONE_HOUR = 3600000;
    public const ONE_DAY = 86400000;
    public const ONE_WEEK = 604800000;

    public const DEFAULT_MAX_WAIT_MILLIS = 180000;
    public const DEFAULT_CHECK_MILLIS = 500;

    protected $maxWait = self::DEFAULT_MAX_WAIT_MILLIS;
    protected $maxWaitMultiplier = self::ONE_MILLISECOND;

    protected $checkEvery = self::DEFAULT_CHECK_MILLIS;
    protected $checkEveryMultiplier = self::ONE_MILLISECOND;

    protected $timeoutProperty;

    public function __construct($taskName = 'waitfor')
    {
        parent::__construct($taskName);
    }

    /**
     * Set the maximum length of time to wait.
     *
     * @param int $maxWait
     */
    public function setMaxWait($maxWait)
    {
        $this->maxWait = (int) $maxWait;
    }

    /**
     * Set the max wait time unit.
     *
     * @param string $maxWaitUnit
     */
    public function setMaxWaitUnit($maxWaitUnit)
    {
        $this->maxWaitMultiplier = $this->convertUnit($maxWaitUnit);
    }

    /**
     * Set the time between each check.
     *
     * @param int $checkEvery
     */
    public function setCheckEvery($checkEvery)
    {
        $this->checkEvery = (int) $checkEvery;
    }

    /**
     * Set the check every time unit.
     *
     * @param string $checkEveryUnit
     */
    public function setCheckEveryUnit($checkEveryUnit)
    {
        $this->checkEveryMultiplier = $this->convertUnit($checkEveryUnit);
    }

    /**
     * Name of the property to set after a timeout.
     *
     * @param string $timeoutProperty
     */
    public function setTimeoutProperty($timeoutProperty)
    {
        $this->timeoutProperty = $timeoutProperty;
    }

    /**
     * Check repeatedly for the specified conditions until they become
     * true or the timeout expires.
     *
     * @throws BuildException
     */
    public function main()
    {
        if ($this->countConditions() > 1) {
            throw new BuildException('You must not nest more than one condition into <waitfor>');
        }

        if ($this->countConditions() < 1) {
            throw new BuildException('You must nest a condition into <waitfor>');
        }

        /**
         * @var ConditionEnumeration
         */
        $cs = $this->getIterator();
        $condition = $cs->current();

        $maxWaitMillis = $this->maxWait * $this->maxWaitMultiplier;
        $checkEveryMillis = $this->checkEvery * $this->checkEveryMultiplier;

        $start = microtime(true) * 1000;
        $end = $start + $maxWaitMillis;

        while (microtime(true) * 1000 < $end) {
            if ($condition->evaluate()) {
                $this->processSuccess();

                return;
            }

            usleep($checkEveryMillis * 1000);
        }

        $this->processTimeout();
    }

    /**
     * Convert the unit to a multipler.
     *
     * @param string $unit
     *
     * @throws BuildException
     *
     * @return int
     */
    protected function convertUnit($unit)
    {
        if ('week' === $unit) {
            return self::ONE_WEEK;
        }

        if ('day' === $unit) {
            return self::ONE_DAY;
        }

        if ('hour' === $unit) {
            return self::ONE_HOUR;
        }

        if ('minute' === $unit) {
            return self::ONE_MINUTE;
        }

        if ('second' === $unit) {
            return self::ONE_SECOND;
        }

        if ('millisecond' === $unit) {
            return self::ONE_MILLISECOND;
        }

        throw new BuildException("Illegal unit '{$unit}'");
    }

    protected function processSuccess()
    {
        $this->log($this->getTaskName() . ': condition was met', Project::MSG_VERBOSE);
    }

    protected function processTimeout()
    {
        $this->log($this->getTaskName() . ': timeout', Project::MSG_VERBOSE);

        if (null != $this->timeoutProperty) {
            $this->project->setNewProperty($this->timeoutProperty, 'true');
        }
    }
}
