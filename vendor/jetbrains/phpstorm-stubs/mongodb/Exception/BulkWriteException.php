<?php

namespace MongoDB\Driver\Exception;

/**
 * Thrown when a bulk write operation fails.
 * @link https://php.net/manual/en/class.mongodb-driver-exception-bulkwriteexception.php
 * @since 1.0.0
 */
class BulkWriteException extends WriteException implements Exception {}
