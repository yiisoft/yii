<?php

namespace MongoDB\BSON;

/**
 * @since 1.16.0
 * @link https://secure.php.net/manual/en/class.mongodb-bson-packedarray.php
 */
final class PackedArray implements \IteratorAggregate, \Serializable
{
    private function __construct() {}

    final public static function fromPHP(array $value): PackedArray {}

    final public function get(int $index): mixed {}

    final public function getIterator(): Iterator {}

    final public function has(int $index): bool {}

    final public function toPHP(?array $typeMap = null): array|object {}

    final public function __toString(): string {}

    final public static function __set_state(array $properties): PackedArray {}

    final public function serialize(): string {}

    final public function unserialize(string $data): void {}

    final public function __unserialize(array $data): void {}

    final public function __serialize(): array {}
}
