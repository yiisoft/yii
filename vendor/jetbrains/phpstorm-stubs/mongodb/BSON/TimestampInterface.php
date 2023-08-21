<?php

namespace MongoDB\BSON;

/**
 * Interface TimestampInterface
 *
 * @link https://secure.php.net/manual/en/class.mongodb-bson-timestampinterface.php
 * @since 1.3.0
 */
interface TimestampInterface
{
    /**
     * Returns the increment component of this TimestampInterface
     * @link https://secure.php.net/manual/en/mongodb-bson-timestampinterface.getincrement.php
     * @return int
     * @since 1.3.0
     */
    public function getIncrement();

    /**
     * Returns the timestamp component of this TimestampInterface
     * @link https://secure.php.net/manual/en/mongodb-bson-timestampinterface.gettimestamp.php
     * @return int
     * @since 1.3.0
     */
    public function getTimestamp();

    /**
     * Returns the string representation of this TimestampInterface
     * @link https://secure.php.net/manual/en/mongodb-bson-timestampinterface.tostring.php
     * @return string
     * @since 1.3.0
     */
    public function __toString();
}
