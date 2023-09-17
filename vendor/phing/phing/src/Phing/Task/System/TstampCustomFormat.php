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

class TstampCustomFormat
{
    /** @var string */
    public $propertyName = '';
    /** @var string */
    public $pattern = '';
    /** @var null|string */
    public $locale = null;
    /** @var null|string */
    public $timezone = null;

    /**
     * The property to receive the date/time string in the given pattern.
     */
    public function setProperty(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * The ICU pattern to be used.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
     */
    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    /**
     * The locale used to create date/time string.
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * The timezone used to create date/time string.
     */
    public function setTimezone(string $timezone): void
    {
        $this->timezone = $timezone;
    }

    /**
     * Validate parameter.
     *
     * @param TstampTask $tstampTask Reference to parent task
     */
    public function validate(TstampTask $tstampTask): void
    {
        if (empty($this->propertyName)) {
            throw new BuildException('property attribute must be provided', $tstampTask->getLocation());
        }

        if (empty($this->pattern)) {
            throw new BuildException('pattern attribute must be provided', $tstampTask->getLocation());
        }

        if (false !== strpos($this->pattern, '%')) {
            $tstampTask->log('pattern attribute must use ICU format https://www.phing.info/guide/chunkhtml/TstampTask.html', Project::MSG_WARN);
        }
    }
}
