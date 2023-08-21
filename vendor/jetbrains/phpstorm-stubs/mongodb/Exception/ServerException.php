<?php

namespace MongoDB\Driver\Exception;

/**
 * Base class for exceptions thrown by the server. The code of this exception and its subclasses will correspond to the original
 * error code from the server.
 *
 * @link https://secure.php.net/manual/en/class.mongodb-driver-exception-serverexception.php
 * @since 1.5.0
 */
class ServerException extends RuntimeException implements Exception {}
