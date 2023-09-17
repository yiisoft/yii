<?php

namespace MongoDB\Driver\Exception;

/**
 * Thrown when the driver encounters an unexpected value (e.g. during BSON serialization or deserialization).
 * @link https://php.net/manual/en/class.mongodb-driver-exception-unexpectedvalueexception.php
 * @since 1.0.0
 */
class UnexpectedValueException extends \UnexpectedValueException implements Exception {}
