<?php

namespace MongoDB\BSON;

use JsonSerializable;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;

/**
 * BSON type for the Decimal128 floating-point format, which supports numbers with up to 34 decimal digits (i.e. significant digits) and an exponent range of −6143 to +6144.
 * @link https://php.net/manual/en/class.mongodb-bson-decimal128.php
 */
final class Decimal128 implements Type, Decimal128Interface, \Serializable, JsonSerializable
{
    /**
     * Construct a new Decimal128
     * @link https://php.net/manual/en/mongodb-bson-decimal128.construct.php
     * @param string $value A decimal string.
     */
    final public function __construct(string $value = '') {}

    /**
     * Returns the string representation of this Decimal128
     * @link https://php.net/manual/en/mongodb-bson-decimal128.tostring.php
     */
    final public function __toString(): string {}

    public static function __set_state(array $properties) {}

    /**
     * Serialize a Decimal128
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-decimal128.serialize.php
     * @return string
     * @throws InvalidArgumentException
     */
    final public function serialize(): string {}

    /**
     * Unserialize a Decimal128
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-decimal128.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    final public function unserialize(string $data): void {}

    /**
     * Returns a representation that can be converted to JSON
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-decimal128.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    final public function jsonSerialize() {}
}
