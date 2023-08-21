<?php

namespace MongoDB\Driver\Exception;

/**
 * Thrown when a query or command fails to complete within a specified time limit (e.g. maxTimeMS).
 * @link https://php.net/manual/en/class.mongodb-driver-exception-executiontimeoutexception.php
 */
class ExecutionTimeoutException extends ServerException implements Exception {}
