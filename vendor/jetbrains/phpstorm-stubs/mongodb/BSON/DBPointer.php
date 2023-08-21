<?php

namespace MongoDB\BSON;

use JetBrains\PhpStorm\Deprecated;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;

/**
 * BSON type for the "DbPointer" type. This BSON type is deprecated, and this class can not be instantiated. It will be created from a
 * BSON symbol type while converting BSON to PHP, and can also be converted back into BSON while storing documents in the database.
 *
 * @since 1.4.0
 * @link https://secure.php.net/manual/en/class.mongodb-bson-dbpointer.php
 */
#[Deprecated]
final class DBPointer implements Type, \Serializable, \JsonSerializable
{
    final private function __construct() {}

    /**
     * Serialize a DBPointer
     *
     * @link https://www.php.net/manual/en/mongodb-bson-dbpointer.serialize.php
     * @return string
     * @throws InvalidArgumentException
     */
    final public function serialize(): string {}

    /**
     * Unserialize a DBPointer
     *
     * @link https://www.php.net/manual/en/mongodb-bson-dbpointer.unserialize.php
     *
     * @param string $serialized
     *
     * @return void
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    final public function unserialize(string $data): void {}

    /**
     * Returns a representation that can be converted to JSON
     *
     * @link https://www.php.net/manual/en/mongodb-bson-dbpointer.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    final public function jsonSerialize() {}

    /**
     * Returns the Symbol as a string
     *
     * @return string Returns the string representation of this Symbol.
     */
    final public function __toString(): string {}
}
