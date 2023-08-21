<?php

namespace MongoDB\BSON;

/**
 * @since 1.16.0
 * @link https://secure.php.net/manual/en/class.mongodb-bson-iterator.php
 */
final class Iterator implements \Iterator
{
    final private function __construct() {}

    final public function current(): mixed {}

    final public function key(): string|int {}

    final public function next(): void {}

    final public function rewind(): void {}

    final public function valid(): bool {}

    final public function __wakeup(): void {}
}
