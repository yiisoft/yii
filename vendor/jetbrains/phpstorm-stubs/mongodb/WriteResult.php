<?php

namespace MongoDB\Driver;

/**
 * The MongoDB\Driver\WriteResult class encapsulates information about an executed MongoDB\Driver\BulkWrite and may be returned by MongoDB\Driver\Manager::executeBulkWrite().
 * @link https://php.net/manual/en/class.mongodb-driver-writeresult.php
 */
final class WriteResult
{
    final private function __construct() {}

    final public function __wakeup() {}

    /**
     * Returns the number of documents deleted
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getdeletedcount.php
     */
    final public function getDeletedCount(): ?int {}

    /**
     * Returns the number of documents inserted (excluding upserts)
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getinsertedcount.php
     */
    final public function getInsertedCount(): ?int {}

    /**
     * Returns the number of documents selected for update
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getmatchedcount.php
     */
    final public function getMatchedCount(): ?int {}

    /**
     * Returns the number of existing documents updated
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getmodifiedcount.php
     */
    final public function getModifiedCount(): ?int {}

    /**
     * Returns the server associated with this write result
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getserver.php
     */
    final public function getServer(): Server {}

    /**
     * Returns the number of documents inserted by an upsert
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getupsertedcount.php
     */
    final public function getUpsertedCount(): ?int {}

    /**
     * Returns an array of identifiers for upserted documents
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getupsertedids.php
     */
    final public function getUpsertedIds(): array {}

    /**
     * Returns any write concern error that occurred
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getwriteconcernerror.php
     */
    final public function getWriteConcernError(): ?WriteConcernError {}

    /**
     * Returns any write errors that occurred
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getwriteerrors.php
     * @return WriteError[]
     */
    final public function getWriteErrors(): array {}

    /**
     * @since 1.16.0
     */
    final public function getErrorReplies(): array {}

    /**
     * Returns whether the write was acknowledged
     * @link https://php.net/manual/en/mongodb-driver-writeresult.isacknowledged.php
     */
    final public function isAcknowledged(): bool {}
}
