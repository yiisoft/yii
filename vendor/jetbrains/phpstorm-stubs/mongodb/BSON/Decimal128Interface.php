<?php

namespace MongoDB\BSON;

/**
 * This interface is implemented by MongoDB\BSON\Decimal128 but may also be used for type-hinting and userland classes.
 * @link https://www.php.net/manual/en/class.mongodb-bson-decimal128interface.php
 */
interface Decimal128Interface
{
    /**
     * Returns the string representation of this Decimal128Interface
     * @link https://www.php.net/manual/en/mongodb-bson-decimal128interface.tostring.php
     * @return string Returns the string representation of this Decimal128Interface
     */
    public function __toString();
}
