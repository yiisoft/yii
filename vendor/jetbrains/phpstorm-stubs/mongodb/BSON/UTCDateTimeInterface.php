<?php

namespace MongoDB\BSON;

use DateTime;

/**
 * This interface is implemented by MongoDB\BSON\UTCDateTime but may also be used for type-hinting and userland classes.
 * @link https://www.php.net/manual/en/class.mongodb-bson-utcdatetimeinterface.php
 */
interface UTCDateTimeInterface
{
    /**
     * @link https://www.php.net/manual/en/mongodb-bson-utcdatetimeinterface.todatetime.php
     * @return DateTime Returns the DateTime representation of this UTCDateTimeInterface. The returned DateTime should use the UTC time zone.
     */
    public function toDateTime();

    /**
     * Returns the string representation of this UTCDateTimeInterface
     * @link https://www.php.net/manual/en/mongodb-bson-utcdatetimeinterface.tostring.php
     * @return string
     */
    public function __toString();
}
