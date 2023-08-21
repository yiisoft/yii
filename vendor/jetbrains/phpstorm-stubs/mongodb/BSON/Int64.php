<?php

namespace MongoDB\BSON;

use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;

/**
 * BSON type for a 64-bit integer.
 *
 * @since 1.5.0
 * @link https://secure.php.net/manual/en/class.mongodb-bson-int64.php
 */
final class Int64 implements Type, \Serializable, \JsonSerializable
{
    /** @since 1.16.0 */
    final public function __construct(string|int $value) {}

    /**
     * Serialize an Int64
     * @link https://www.php.net/manual/en/mongodb-bson-int64.serialize.php
     * @throws InvalidArgumentException
     */
    final public function serialize(): string {}

    public static function __set_state(array $properties) {}

    /**
     * Unserialize an Int64
     * @link https://www.php.net/manual/en/mongodb-bson-int64.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    final public function unserialize(string $data): void {}

    /**
     * Returns a representation that can be converted to JSON
     * @link https://www.php.net/manual/en/mongodb-bson-int64.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    final public function jsonSerialize() {}

    /**
     * Returns the Symbol as a string
     * @return string Returns the string representation of this Symbol.
     */
    final public function __toString(): string {}
}
