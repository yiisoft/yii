<?php

namespace MongoDB\Driver\Exception;

/**
 * Base class for exceptions thrown when the driver fails to establish a database connection.
 * @link https://php.net/manual/en/class.mongodb-driver-exception-connectionexception.php
 * @since 1.0.0
 */
class ConnectionException extends RuntimeException implements Exception {}
