<?php

namespace MongoDB\Driver\Exception;

/**
 * Thrown when the driver fails to authenticate with the server.
 * @link https://php.net/manual/en/class.mongodb-driver-exception-authenticationexception.php
 * @since 1.0.0
 */
class AuthenticationException extends ConnectionException implements Exception {}
