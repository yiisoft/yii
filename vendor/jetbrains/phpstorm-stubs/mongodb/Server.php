<?php

namespace MongoDB\Driver;

use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\RuntimeException;

/**
 * @link https://php.net/manual/en/class.mongodb-driver-server.php
 */
final class Server
{
    public const TYPE_UNKNOWN = 0;
    public const TYPE_STANDALONE = 1;
    public const TYPE_MONGOS = 2;
    public const TYPE_POSSIBLE_PRIMARY = 3;
    public const TYPE_RS_PRIMARY = 4;
    public const TYPE_RS_SECONDARY = 5;
    public const TYPE_RS_ARBITER = 6;
    public const TYPE_RS_OTHER = 7;
    public const TYPE_RS_GHOST = 8;

    /**
     * @since 1.11.0
     */
    public const TYPE_LOAD_BALANCER = 9;

    /**
     * Server constructor.
     * @link https://php.net/manual/en/mongodb-driver-server.construct.php
     * @throws RuntimeException (can only be created internally)
     */
    final private function __construct() {}

    final public function __wakeup() {}

    /**
     * Execute one or more write operations on this server
     * @link https://php.net/manual/en/mongodb-driver-server.executebulkwrite.php
     * @param string $namespace A fully qualified namespace (e.g. "databaseName.collectionName").
     * @param BulkWrite $bulkWrite The MongoDB\Driver\BulkWrite to execute.
     * @param array|WriteConcern|null $options
     * @throws BulkWriteException on any write failure (e.g. write error, failure to apply a write concern).
     * @throws InvalidArgumentException on argument parsing errors.
     * @throws ConnectionException if connection to the server fails (for reasons other than authentication).
     * @throws AuthenticationException if authentication is needed and fails.
     * @throws RuntimeException on other errors.
     * @return WriteResult
     * @since 1.0.0
     */
    final public function executeBulkWrite(string $namespace, BulkWrite $bulkWrite, array|WriteConcern|null $options = null): WriteResult {}

    /**
     * Execute a database command on this server
     * @link https://php.net/manual/en/mongodb-driver-server.executecommand.php
     * @param string $db The name of the database on which to execute the command.
     * @param Command $command The MongoDB\Driver\Command to execute.
     * @param array|ReadPreference|null $options Optionally, a MongoDB\Driver\ReadPreference to select the server for this operation. If none is given, the read preference from the MongoDB Connection URI will be used.
     * @throws InvalidArgumentException on argument parsing errors.
     * @throws ConnectionException if connection to the server fails (for reasons other than authentication).
     * @throws AuthenticationException if authentication is needed and fails.
     * @throws RuntimeException on other errors (e.g. invalid command, issuing a write command to a secondary).
     * @return Cursor
     * @since 1.0.0
     */
    final public function executeCommand(string $db, Command $command, array|ReadPreference|null $options = null): Cursor {}

    /**
     * Execute a database command that reads on this server
     * @link https://secure.php.net/manual/en/mongodb-driver-server.executereadcommand.php
     * @param string                  $db
     * @param \MongoDB\Driver\Command $command
     * @param array|null              $options
     * @throws InvalidArgumentException On argument parsing errors or  if the "session" option is used with an associated transaction in combination with a "readConcern" or "writeConcern" option.
     * @throws ConnectionException If connection to the server fails (for reasons other than authentication).
     * @throws AuthenticationException If authentication is needed and fails.
     * @throws RuntimeException On other errors (e.g. invalid command).
     * @since 1.4.0
     */
    final public function executeReadCommand(string $db, Command $command, ?array $options = null): Cursor {}

    /**
     * Execute a database command that reads and writes on this server
     * @link https://secure.php.net/manual/en/mongodb-driver-server.executereadwritecommand.php
     * @param string                  $db
     * @param \MongoDB\Driver\Command $command
     * @param array|null              $options
     * @throws InvalidArgumentException On argument parsing errors OR if the "session" option is used with an associated transaction in combination with a "readConcern" or "writeConcern" option OR if the "session" option is used in combination with an unacknowledged write concern
     * @throws ConnectionException If connection to the server fails (for reasons other than authentication).
     * @throws AuthenticationException If authentication is needed and fails.
     * @throws RuntimeException On other errors (e.g. invalid command).
     * @since 1.4.0
     */
    final public function executeReadWriteCommand(string $db, Command $command, ?array $options = null): Cursor {}

    /**
     * Execute a database command that writes on this server
     * @link https://secure.php.net/manual/en/mongodb-driver-server.executewritecommand.php
     * @param string                  $db
     * @param \MongoDB\Driver\Command $command
     * @param array|null              $options
     * @throws InvalidArgumentException On argument parsing errors or  if the "session" option is used with an associated transaction in combination with a "readConcern" or "writeConcern" option.
     * @throws ConnectionException If connection to the server fails (for reasons other than authentication).
     * @throws AuthenticationException If authentication is needed and fails.
     * @throws RuntimeException On other errors (e.g. invalid command).
     * @since 1.4.0
     */
    final public function executeWriteCommand(string $db, Command $command, ?array $options = null): Cursor {}

    /**
     * Execute a database query on this server
     * @link https://php.net/manual/en/mongodb-driver-server.executequery.php
     * @param string $namespace A fully qualified namespace (e.g. "databaseName.collectionName").
     * @param Query $query The MongoDB\Driver\Query to execute.
     * @param array|ReadPreference|null $options
     * @throws InvalidArgumentException on argument parsing errors.
     * @throws ConnectionException if connection to the server fails (for reasons other than authentication).
     * @throws AuthenticationException if authentication is needed and fails.
     * @throws RuntimeException on other errors (e.g. invalid command, issuing a write command to a secondary).
     * @return Cursor
     */
    final public function executeQuery(string $namespace, Query $query, array|ReadPreference|null $options = null): Cursor {}

    /**
     * Returns the hostname of this server
     * @link https://php.net/manual/en/mongodb-driver-server.gethost.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function getHost(): string {}

    /**
     * Returns an array of information about this server
     * @link https://php.net/manual/en/mongodb-driver-server.getinfo.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function getInfo(): array {}

    /**
     * Returns the latency of this server
     * @link https://php.net/manual/en/mongodb-driver-server.getlatency.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function getLatency(): int {}

    /**
     * Returns the port on which this server is listening
     * @link https://php.net/manual/en/mongodb-driver-server.getport.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function getPort(): int {}

    /**
     * Returns a ServerDescription for this server
     * https://www.php.net/manual/en/mongodb-driver-server.getserverdescription.php
     * @since 1.13.0
     */
    final public function getServerDescription(): ServerDescription {}

    /**
     * Returns an array of tags describing this server in a replica set
     * @link https://php.net/manual/en/mongodb-driver-server.gettags.php
     * @throws InvalidArgumentException on argument parsing errors.
     * @return array An array of tags used to describe this server in a replica set. The array will contain zero or more string key and value pairs.
     */
    final public function getTags(): array {}

    /**
     * Returns an integer denoting the type of this server
     * @link https://php.net/manual/en/mongodb-driver-server.gettype.php
     * @throws InvalidArgumentException on argument parsing errors.
     * @return int denoting the type of this server
     */
    final public function getType(): int {}

    /**
     * Checks if this server is an arbiter member of a replica set
     * @link https://php.net/manual/en/mongodb-driver-server.isarbiter.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function isArbiter(): bool {}

    /**
     * Checks if this server is a hidden member of a replica set
     * @link https://php.net/manual/en/mongodb-driver-server.ishidden.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function isHidden(): bool {}

    /**
     * Checks if this server is a passive member of a replica set
     * @link https://php.net/manual/en/mongodb-driver-server.ispassive.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function isPassive(): bool {}

    /**
     * Checks if this server is a primary member of a replica set
     * @link https://php.net/manual/en/mongodb-driver-server.isprimary.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function isPrimary(): bool {}

    /**
     * Checks if this server is a secondary member of a replica set
     * @link https://php.net/manual/en/mongodb-driver-server.issecondary.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function isSecondary(): bool {}
}
