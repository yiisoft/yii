<?php

namespace MongoDB\BSON;

/**
 * This interface is implemented by MongoDB\BSON\Binary but may also be used for type-hinting and userland classes.
 * @link https://www.php.net/manual/en/class.mongodb-bson-binaryinterface.php
 */
interface BinaryInterface
{
    /**
     * @link https://www.php.net/manual/en/mongodb-bson-binaryinterface.getdata.php
     * @return string Returns the BinaryInterface's data
     */
    public function getData();

    /**
     * @link https://www.php.net/manual/en/mongodb-bson-binaryinterface.gettype.php
     * @return int Returns the BinaryInterface's type.
     */
    public function getType();

    /**
     * This method is an alias of: MongoDB\BSON\BinaryInterface::getData().
     * @link https://www.php.net/manual/en/mongodb-bson-binaryinterface.tostring.php
     * @return string Returns the BinaryInterface's data.
     */
    public function __toString();
}
