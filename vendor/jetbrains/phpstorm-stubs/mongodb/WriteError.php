<?php

namespace MongoDB\Driver;

/**
 * The MongoDB\Driver\WriteError class encapsulates information about a write error and may be returned as an array element from MongoDB\Driver\WriteResult::getWriteErrors().
 */
final class WriteError
{
    final private function __construct() {}

    final public function __wakeup() {}

    /**
     * Returns the WriteError's error code
     * @link https://php.net/manual/en/mongodb-driver-writeerror.getcode.php
     */
    final public function getCode(): int {}

    /**
     * Returns the index of the write operation corresponding to this WriteError
     * @link https://php.net/manual/en/mongodb-driver-writeerror.getindex.php
     */
    final public function getIndex(): int {}

    /**
     * Returns additional metadata for the WriteError
     * @link https://php.net/manual/en/mongodb-driver-writeerror.getinfo.php
     */
    final public function getInfo(): ?object {}

    /**
     * Returns the WriteError's error message
     * @link https://php.net/manual/en/mongodb-driver-writeerror.getmessage.php
     */
    final public function getMessage(): string {}
}
