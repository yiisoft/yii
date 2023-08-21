<?php

namespace MongoDB\Driver;

use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Exception\WriteConcernException;
use MongoDB\Driver\Exception\WriteException;
use MongoDB\Driver\Monitoring\Subscriber;

/**
 * The MongoDB\Driver\Manager is the main entry point to the extension. It is responsible for maintaining connections to MongoDB (be it standalone server, replica set, or sharded cluster).
 * No connection to MongoDB is made upon instantiating the Manager. This means the MongoDB\Driver\Manager can always be constructed, even though one or more MongoDB servers are down.
 * Any write or query can throw connection exceptions as connections are created lazily. A MongoDB server may also become unavailable during the life time of the script. It is therefore important that all actions on the Manager to be wrapped in try/catch statements.
 * @link https://php.net/manual/en/class.mongodb-driver-manager.php
 */
final class Manager
{
    /**
     * Manager constructor.
     * @link https://php.net/manual/en/mongodb-driver-manager.construct.php
     * @param string|null $uri A mongodb:// connection URI
     * @param array|null $options Connection string options
     * @param array|null $driverOptions Any driver-specific options not included in MongoDB connection spec.
     * @throws InvalidArgumentException on argument parsing errors
     * @throws RuntimeException if the uri format is invalid
     */
    final public function __construct(?string $uri = null, ?array $options = null, ?array $driverOptions = null) {}

    final public function __wakeup() {}

    /**
     * Return a ClientEncryption instance.
     * @link https://php.net/manual/en/mongodb-driver-manager.createclientencryption.php
     * @param array $options
     * @return \MongoDB\Driver\ClientEncryption
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException On argument parsing errors.
     * @throws \MongoDB\Driver\Exception\RuntimeException If the extension was compiled without libmongocrypt support.
     */
    final public function createClientEncryption(array $options) {}

    /**
     * Execute one or more write operations
     * @link https://php.net/manual/en/mongodb-driver-manager.executebulkwrite.php
     * @param string $namespace A fully qualified namespace (databaseName.collectionName)
     * @param BulkWrite $bulk The MongoDB\Driver\BulkWrite to execute.
     * @param array|WriteConcern|null $options WriteConcern type for backwards compatibility
     * @throws InvalidArgumentException on argument parsing errors.
     * @throws ConnectionException if connection to the server fails for other then authentication reasons
     * @throws AuthenticationException if authentication is needed and fails
     * @throws BulkWriteException on any write failure
     * @throws RuntimeException on other errors (invalid command, command arguments, ...)
     * @since 1.4.0 added $options argument
     */
    final public function executeBulkWrite(string $namespace, BulkWrite $bulk, array|WriteConcern|null $options = null): WriteResult {}

    /**
     * @link https://php.net/manual/en/mongodb-driver-manager.executecommand.php
     * @param string $db The name of the database on which to execute the command.
     * @param Command $command The command document.
     * @param array|ReadPreference|null $options ReadPreference type for backwards compatibility
     * @throws Exception
     * @throws AuthenticationException if authentication is needed and fails
     * @throws ConnectionException if connection to the server fails for other then authentication reasons
     * @throws RuntimeException on other errors (invalid command, command arguments, ...)
     * @throws WriteException on Write Error
     * @throws WriteConcernException on Write Concern failure
     * @since 1.4.0 added $options argument
     */
    final public function executeCommand(string $db, Command $command, array|ReadPreference|null $options = null): Cursor {}

    /**
     * Execute a MongoDB query
     * @link https://php.net/manual/en/mongodb-driver-manager.executequery.php
     * @param string $namespace A fully qualified namespace (databaseName.collectionName)
     * @param Query $query A MongoDB\Driver\Query to execute.
     * @param array|ReadPreference|null $options ReadPreference type for backwards compatibility
     * @throws Exception
     * @throws AuthenticationException if authentication is needed and fails
     * @throws ConnectionException if connection to the server fails for other then authentication reasons
     * @throws RuntimeException on other errors (invalid command, command arguments, ...)
     * @since 1.4.0 added $options argument
     */
    final public function executeQuery(string $namespace, Query $query, array|ReadPreference|null $options = null): Cursor {}

    /**
     * @link https://php.net/manual/en/mongodb-driver-manager.executereadcommand.php
     * @param string $db The name of the database on which to execute the command that reads.
     * @param Command $command The command document.
     * @param array|null $options
     * @throws Exception
     * @throws AuthenticationException if authentication is needed and fails
     * @throws ConnectionException if connection to the server fails for other then authentication reasons
     * @throws RuntimeException on other errors (invalid command, command arguments, ...)
     * @throws WriteException on Write Error
     * @throws WriteConcernException on Write Concern failure
     * @since 1.4.0
     */
    final public function executeReadCommand(string $db, Command $command, ?array $options = null): Cursor {}

    /**
     * @link https://php.net/manual/en/mongodb-driver-manager.executereadwritecommand.php
     * @param string $db The name of the database on which to execute the command that reads.
     * @param Command $command The command document.
     * @param array|null $options
     * @throws Exception
     * @throws AuthenticationException if authentication is needed and fails
     * @throws ConnectionException if connection to the server fails for other then authentication reasons
     * @throws RuntimeException on other errors (invalid command, command arguments, ...)
     * @throws WriteException on Write Error
     * @throws WriteConcernException on Write Concern failure
     * @since 1.4.0
     */
    final public function executeReadWriteCommand(string $db, Command $command, ?array $options = null): Cursor {}

    /**
     * @link https://php.net/manual/en/mongodb-driver-manager.executewritecommand.php
     * @param string $db The name of the database on which to execute the command that writes.
     * @param Command $command The command document.
     * @param array|null $options
     * @throws Exception
     * @throws AuthenticationException if authentication is needed and fails
     * @throws ConnectionException if connection to the server fails for other then authentication reasons
     * @throws RuntimeException on other errors (invalid command, command arguments, ...)
     * @throws WriteException on Write Error
     * @throws WriteConcernException on Write Concern failure
     * @since 1.4.0
     */
    final public function executeWriteCommand(string $db, Command $command, ?array $options = null): Cursor {}

    /**
     * Return the encryptedFieldsMap auto encryption option for the Manager
     * @link https://www.php.net/manual/en/mongodb-driver-manager.getencryptedfieldsmap.php
     * @since 1.14.0
     */
    final public function getEncryptedFieldsMap(): array|object|null {}

    /**
     * Return the ReadConcern for the Manager
     * @link https://php.net/manual/en/mongodb-driver-manager.getreadconcern.php
     * @throws InvalidArgumentException on argument parsing errors.
     */
    final public function getReadConcern(): ReadConcern {}

    /**
     * Return the ReadPreference for the Manager
     * @link https://php.net/manual/en/mongodb-driver-manager.getreadpreference.php
     * @throws InvalidArgumentException
     * @return ReadPreference
     */
    final public function getReadPreference(): ReadPreference {}

    /**
     * Return the servers to which this manager is connected
     * @link https://php.net/manual/en/mongodb-driver-manager.getservers.php
     * @throws InvalidArgumentException on argument parsing errors
     * @return Server[]
     */
    final public function getServers(): array {}

    /**
     * Return the WriteConcern for the Manager
     * @link https://php.net/manual/en/mongodb-driver-manager.getwriteconcern.php
     * @throws InvalidArgumentException on argument parsing errors.
     * @return WriteConcern
     */
    final public function getWriteConcern(): WriteConcern {}

    /**
     * Preselect a MongoDB node based on provided readPreference. This can be useful to guarantee a command runs on a specific server when operating in a mixed version cluster.
     * https://secure.php.net/manual/en/mongodb-driver-manager.selectserver.php
     * @param ReadPreference|null $readPreference Optionally, a MongoDB\Driver\ReadPreference to route the command to. If none given, defaults to the Read Preferences set by the MongoDB Connection URI.
     * @throws InvalidArgumentException on argument parsing errors.
     * @throws ConnectionException if connection to the server fails (for reasons other than authentication).
     * @throws AuthenticationException if authentication is needed and fails.
     * @throws RuntimeException if a server matching the read preference could not be found.
     * @return Server
     */
    final public function selectServer(?ReadPreference $readPreference = null) {}

    /**
     * Start a new client session for use with this client
     * @param array|null $options
     * @return \MongoDB\Driver\Session
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException On argument parsing errors
     * @throws \MongoDB\Driver\Exception\RuntimeException If the session could not be created (e.g. libmongoc does not support crypto).
     * @link https://secure.php.net/manual/en/mongodb-driver-manager.startsession.php
     * @since 1.4.0
     */
    final public function startSession(?array $options = null) {}

    /**
     * Registers a monitoring event subscriber with this Manager
     * @link https://www.php.net/manual/en/mongodb-driver-manager.addsubscriber.php
     * @since 1.10.0
     */
    final public function addSubscriber(Subscriber $subscriber): void {}

    /**
     * Unregisters a monitoring event subscriber with this Manager
     * @link https://www.php.net/manual/en/mongodb-driver-manager.removesubscriber.php
     * @since 1.10.0
     */
    final public function removeSubscriber(Subscriber $subscriber): void {}
}
