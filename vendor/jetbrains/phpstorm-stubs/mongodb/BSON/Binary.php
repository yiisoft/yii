<?php

namespace MongoDB\BSON;

use JsonSerializable;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;

/**
 * Class Binary
 * @link https://php.net/manual/en/class.mongodb-bson-binary.php
 */
final class Binary implements Type, BinaryInterface, \Serializable, JsonSerializable
{
    public const TYPE_GENERIC = 0;
    public const TYPE_FUNCTION = 1;
    public const TYPE_OLD_BINARY = 2;
    public const TYPE_OLD_UUID = 3;
    public const TYPE_UUID = 4;
    public const TYPE_MD5 = 5;

    /**
     * @since 1.7.0
     */
    public const TYPE_ENCRYPTED = 6;

    /**
     * @since 1.12.0
     */
    public const TYPE_COLUMN = 7;
    public const TYPE_USER_DEFINED = 128;

    /**
     * Binary constructor.
     * @link https://php.net/manual/en/mongodb-bson-binary.construct.php
     */
    final public function __construct(string $data, int $type = Binary::TYPE_GENERIC) {}

    /**
     * Returns the Binary's data
     * @link https://php.net/manual/en/mongodb-bson-binary.getdata.php
     */
    final public function getData(): string {}

    /**
     * Returns the Binary's type
     * @link https://php.net/manual/en/mongodb-bson-binary.gettype.php
     */
    final public function getType(): int {}

    public static function __set_state(array $properties) {}

    /**
     * Returns the Binary's data
     * @link https://www.php.net/manual/en/mongodb-bson-binary.tostring.php
     */
    final public function __toString(): string {}

    /**
     * Serialize a Binary
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-binary.serialize.php
     * @throws InvalidArgumentException
     */
    final public function serialize(): string {}

    /**
     * Unserialize a Binary
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-binary.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    final public function unserialize(string $data) {}

    /**
     * Returns a representation that can be converted to JSON
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-binary.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    final public function jsonSerialize() {}
}
