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

namespace Phing\Type\Selector;

use Phing\Io\File;

/**
 * Selector that chooses files based on their last modified date. Ant uses
 * millisecond precision (thanks to Java); PHP is forced to use only seconds
 * precision.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Bruce Atherton <bruce@callenish.com> (Ant)
 */
class DateSelector extends BaseExtendSelector
{
    public const MILLIS_KEY = 'millis';
    public const SECONDS_KEY = 'seconds';
    public const DATETIME_KEY = 'datetime';
    public const CHECKDIRS_KEY = 'checkdirs';
    public const GRANULARITY_KEY = 'granularity';
    public const WHEN_KEY = 'when';
    private $seconds = -1; // millis in Ant, but PHP doesn't support that level of precision
    private $dateTime;
    private $includeDirs = false;
    private $granularity = 0;
    private $cmp = 2;
    private static $timeComparisons = ['before', 'after', 'equal'];

    public function __construct()
    {
        parent::__construct();
        //if (Os.isFamily("dos")) {
        //    granularity = 2000;
        //}
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $buf = '{dateselector date: ';
        $buf .= $this->dateTime;
        $buf .= ' compare: ';
        if (0 === $this->cmp) {
            $buf .= 'before';
        } elseif (1 === $this->cmp) {
            $buf .= 'after';
        } else {
            $buf .= 'equal';
        }
        $buf .= ' granularity: ';
        $buf .= $this->granularity;
        $buf .= '}';

        return $buf;
    }

    /**
     * For users that prefer to express time in seconds since 1970.
     *
     * @param int $seconds the time to compare file's last modified date to,
     *                     expressed in seconds
     */
    public function setSeconds($seconds)
    {
        $this->seconds = (int) $seconds;
    }

    /**
     * Returns the seconds value the selector is set for.
     */
    public function getSeconds()
    {
        return $this->seconds;
    }

    /**
     * @param int $millis the time to compare file's last modified date to, expressed in milliseconds
     */
    public function setMillis($millis)
    {
        $this->setSeconds((int) $millis / 1000);
    }

    /**
     * Sets the date. The user must supply it in MM/DD/YYYY HH:MM AM_PM
     * format.
     *
     * @param string $dateTime a string in MM/DD/YYYY HH:MM AM_PM format
     */
    public function setDatetime($dateTime)
    {
        $dt = strtotime($dateTime);
        if (false === $dt) {
            $this->setError(
                'Date of ' . $dateTime
                . ' Cannot be parsed correctly. It should be in'
                . " a format parsable by PHP's strtotime() function."
            );
        } else {
            $this->dateTime = $dateTime;
            $this->setSeconds($dt);
        }
    }

    /**
     * Should we be checking dates on directories?
     *
     * @param bool $includeDirs whether to check the timestamp on directories
     */
    public function setCheckdirs($includeDirs)
    {
        $this->includeDirs = (bool) $includeDirs;
    }

    /**
     * Sets the number of seconds leeway we will give before we consider
     * a file not to have matched a date.
     *
     * @param int $granularity
     */
    public function setGranularity($granularity)
    {
        $this->granularity = (int) $granularity;
    }

    /**
     * Sets the type of comparison to be done on the file's last modified
     * date.
     *
     * @param string $cmp The comparison to perform
     */
    public function setWhen($cmp)
    {
        $idx = array_search($cmp, self::$timeComparisons, true);
        if (false === $idx) {
            $this->setError('Invalid value for ' . self::WHEN_KEY . ': ' . $cmp);
        } else {
            $this->cmp = $idx;
        }
    }

    /**
     * When using this as a custom selector, this method will be called.
     * It translates each parameter into the appropriate setXXX() call.
     *
     * @param array $parameters the complete set of parameters for this selector
     */
    public function setParameters(array $parameters): void
    {
        parent::setParameters($parameters);
        if (null !== $parameters) {
            for ($i = 0, $size = count($parameters); $i < $size; ++$i) {
                $paramname = $parameters[$i]->getName();

                switch (strtolower($paramname)) {
                    case self::MILLIS_KEY:
                        $this->setMillis($parameters[$i]->getValue());

                        break;

                    case self::SECONDS_KEY:
                        $this->setSeconds($parameters[$i]->getValue());

                        break;

                    case self::DATETIME_KEY:
                        $this->setDatetime($parameters[$i]->getValue());

                        break;

                    case self::CHECKDIRS_KEY:
                        $this->setCheckdirs($parameters[$i]->getValue());

                        break;

                    case self::GRANULARITY_KEY:
                        $this->setGranularity($parameters[$i]->getValue());

                        break;

                    case self::WHEN_KEY:
                        $this->setWhen($parameters[$i]->getValue());

                        break;

                    default:
                        $this->setError('Invalid parameter ' . $paramname);
                } // switch
            }
        }
    }

    /**
     * This is a consistency check to ensure the selector's required
     * values have been set.
     */
    public function verifySettings()
    {
        if (null === $this->dateTime && $this->seconds < 0) {
            $this->setError(
                'You must provide a datetime or the number of seconds.'
            );
        } elseif ($this->seconds < 0) {
            $this->setError(
                'Date of ' . $this->dateTime
                . ' results in negative seconds'
                . ' value relative to epoch (January 1, 1970, 00:00:00 GMT).'
            );
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * @param File   $basedir  the base directory the scan is being done from
     * @param string $filename is the name of the file to check
     * @param File   $file     is a PhingFile object the selector can use
     *
     * @return bool Whether the file should be selected or not
     */
    public function isSelected(File $basedir, $filename, File $file)
    {
        $this->validate();
        if ($file->isDirectory() && (false === $this->includeDirs)) {
            return true;
        }
        if (0 === $this->cmp) {
            return ($file->lastModified() - $this->granularity) < $this->seconds;
        }

        if (1 === $this->cmp) {
            return ($file->lastModified() - $this->granularity) > $this->seconds;
        }

        return abs($file->lastModified() - $this->seconds) <= $this->granularity;
    }
}
