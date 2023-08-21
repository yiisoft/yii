<?php

namespace MongoDB\Driver;

use MongoDB\Driver\Exception\InvalidArgumentException;

/**
 * The MongoDB\Driver\Command class is a value object that represents a database command.
 * To provide "Command Helpers" the MongoDB\Driver\Command object should be composed.
 * @link https://php.net/manual/en/class.mongodb-driver-command.php
 * @since 1.0.0
 */
final class Command
{
    /**
     * Construct new Command
     * @param array|object $document The complete command to construct
     * @param array|null $commandOptions Do not use this parameter to specify options described in the command's reference in the MongoDB manual.
     * @throws InvalidArgumentException on argument parsing errors.
     * @link https://secure.php.net/manual/en/mongodb-driver-command.construct.php
     * @since 1.0.0
     */
    final public function __construct(array|object $document, ?array $commandOptions = null) {}

    final public function __wakeup() {}
}
