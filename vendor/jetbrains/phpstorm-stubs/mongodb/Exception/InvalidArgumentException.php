<?php

namespace MongoDB\Driver\Exception;

/**
 * Thrown when a driver method is given invalid arguments (e.g. invalid option types).
 * @link https://php.net/manual/en/class.mongodb-driver-exception-invalidargumentexception.php
 * @since 1.0.0
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception {}
