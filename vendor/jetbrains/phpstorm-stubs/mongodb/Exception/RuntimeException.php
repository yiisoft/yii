<?php

namespace MongoDB\Driver\Exception;

/**
 * Thrown when the driver encounters a runtime error (e.g. internal error from » libmongoc).
 * @link https://php.net/manual/en/class.mongodb-driver-exception-runtimeexception.php
 * @since 1.0.0
 */
class RuntimeException extends \RuntimeException implements Exception
{
    /**
     * @var bool
     * @since 1.6.0
     */
    protected $errorLabels;

    /**
     * Whether the given errorLabel is associated with this exception
     *
     * @since 1.6.0
     */
    final public function hasErrorLabel(string $label): bool {}
}
