<?php

namespace MongoDB\BSON;

use JsonSerializable;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;

/**
 * Class MinKey
 * @link https://php.net/manual/en/class.mongodb-bson-minkey.php
 */
final class MinKey implements Type, MinKeyInterface, \Serializable, JsonSerializable
{
    public static function __set_state(array $properties) {}

    /**
     * Serialize a MinKey
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-minkey.serialize.php
     * @throws InvalidArgumentException
     */
    final public function serialize(): string {}

    /**
     * Unserialize a MinKey
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-minkey.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    final public function unserialize(string $data): void {}

    /**
     * Returns a representation that can be converted to JSON
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-minkey.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    final public function jsonSerialize() {}
}
