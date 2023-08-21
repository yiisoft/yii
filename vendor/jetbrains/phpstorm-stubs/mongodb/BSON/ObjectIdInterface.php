<?php

namespace MongoDB\BSON;

/**
 * This interface is implemented by MongoDB\BSON\ObjectId but may also be used for type-hinting and userland classes.
 * @link https://www.php.net/manual/en/class.mongodb-bson-objectidinterface.php
 */
interface ObjectIdInterface
{
    /**
     * @link https://www.php.net/manual/en/mongodb-bson-objectidinterface.gettimestamp.php
     * @return int Returns the timestamp component of this ObjectIdInterface.
     */
    public function getTimestamp();

    /**
     * Returns the hexadecimal representation of this ObjectId
     * @link https://www.php.net/manual/en/mongodb-bson-objectid.tostring.php
     * @return string Returns the hexadecimal representation of this ObjectId
     */
    public function __toString();
}
