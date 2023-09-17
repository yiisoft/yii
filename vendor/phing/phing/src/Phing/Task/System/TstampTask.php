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

use DateTime;
use Exception;
use IntlDateFormatter;
use Phing\Exception\BuildException;
use Phing\Task;

/**
 * Sets properties to the current time, or offsets from the current time.
 * The default properties are TSTAMP, DSTAMP and TODAY;.
 *
 * Based on Ant's Tstamp task.
 *
 * @author  Michiel Rook <mrook@php.net>
 *
 * @since   2.2.0
 */
class TstampTask extends Task
{
    /** @var \Phing\Task\System\TstampCustomFormat[] */
    private $customFormats = [];

    /** @var string */
    private $prefix = '';

    /**
     * Set a prefix for the properties.
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;

        if (!empty($this->prefix)) {
            $this->prefix .= '.';
        }
    }

    /**
     * @param TstampCustomFormat $format object representing `<format/>` tag
     */
    public function addFormat(TstampCustomFormat $format): void
    {
        $this->customFormats[] = $format;
    }

    public function init(): void
    {
        // Testing class instead of extension to allow polyfills
        if (!class_exists(IntlDateFormatter::class)) {
            throw new BuildException('TstampTask requires Intl extension');
        }
    }

    /**
     * Create the timestamps. Custom ones are done before the standard ones.
     */
    public function main(): void
    {
        $unixTime = $this->getUnixTime();

        foreach ($this->customFormats as $format) {
            $format->validate($this);
            $this->createProperty($format->propertyName, $unixTime, $format->pattern, $format->locale, $format->timezone);
        }

        $this->createProperty('DSTAMP', $unixTime, 'yyyyMMdd');
        $this->createProperty('TSTAMP', $unixTime, 'HHmm');
        $this->createProperty('TODAY', $unixTime);
    }

    /**
     * @param string      $propertyName  name of the property to be created
     * @param int         $unixTimestamp unix timestamp to be converted
     * @param null|string $pattern       ICU pattern, when null locale-dependent date pattern is used
     * @param null|string $locale        locale to use with timestamp, when null PHP default locale is used
     * @param null|string $timezone      timezone to use with timestamp, when null PHP default timezone is used
     */
    protected function createProperty(string $propertyName, int $unixTimestamp, ?string $pattern = null, ?string $locale = null, ?string $timezone = null): void
    {
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::LONG, IntlDateFormatter::NONE, $timezone, IntlDateFormatter::GREGORIAN, $pattern);
        $value = $formatter->format($unixTimestamp);
        $this->getProject()->setNewProperty($this->prefix . $propertyName, $value);
    }

    protected function getUnixTime(): int
    {
        // phing.tstamp.now.iso
        $property = $this->getProject()->getProperty('phing.tstamp.now.iso');
        if (null !== $property && '' !== $property) {
            try {
                $dateTime = new DateTime($property);

                return $dateTime->getTimestamp();
            } catch (Exception $e) {
                $this->log('magic property phing.tstamp.now.iso ignored as ' . $property . ' is not a valid number');
            }
        }

        // phing.tstamp.now
        $property = $this->getProject()->getProperty('phing.tstamp.now');
        if (null !== $property && '' !== $property) {
            $dateTime = DateTime::createFromFormat('U', $property);
            if ($dateTime instanceof DateTime) {
                return $dateTime->getTimestamp();
            }
            $this->log('magic property phing.tstamp.now ignored as ' . $property . ' is not a valid number');
        }

        return time();
    }
}
