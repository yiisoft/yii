<?php

namespace MongoDB\BSON;

use JsonSerializable;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;

/**
 * Class MaxKey
 * @link https://php.net/manual/en/class.mongodb-bson-maxkey.php
 */
final class MaxKey implements Type, MaxKeyInterface, \Serializable, JsonSerializable
{
    public static function __set_state(array $properties) {}

    /**
     * Serialize a MaxKey
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-maxkey.serialize.php
     * @throws InvalidArgumentException
     */
    final public function serialize(): string {}

    /**
     * Unserialize a MaxKey
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-maxkey.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    final public function unserialize(string $data): void {}

    /**
     * Returns a representation that can be converted to JSON
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-maxkey.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    final public function jsonSerialize() {}
}
