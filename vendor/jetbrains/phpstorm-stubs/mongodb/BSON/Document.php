<?php

namespace MongoDB\BSON;

/**
 * @since 1.16.0
 * @link https://secure.php.net/manual/en/class.mongodb-bson-document.php
 */
final class Document implements \IteratorAggregate, \Serializable
{
    private function __construct() {}

    final public static function fromBSON(string $bson): Document {}

    final public static function fromJSON(string $json): Document {}

    final public static function fromPHP(array|object $value): Document {}

    final public function get(string $key): mixed {}

    final public function getIterator(): Iterator {}

    final public function has(string $key): bool {}

    final public function toPHP(?array $typeMap = null): array|object {}

    final public function toCanonicalExtendedJSON(): string {}

    final public function toRelaxedExtendedJSON(): string {}

    final public function __toString(): string {}

    final public static function __set_state(array $properties): Document {}

    final public function serialize(): string {}

    final public function unserialize(string $data): void {}

    final public function __unserialize(array $data): void {}

    final public function __serialize(): array {}
}
