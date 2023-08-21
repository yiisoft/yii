<?php

namespace MongoDB\Driver\Exception;

/**
 * Base class for exceptions thrown during client-side encryption.
 * @link https://php.net/manual/en/class.mongodb-driver-exception-encryptionexception.php
 * @since 1.7.0
 */
class EncryptionException extends RuntimeException implements Exception {}
