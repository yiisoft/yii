<?php

namespace MongoDB\Driver\Monitoring;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\ServerDescription;

/**
 * @since 1.13.0
 */
final class ServerChangedEvent
{
    final private function __construct() {}

    /**
     * Returns the port on which this server is listening
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.getport.php
     */
    final public function getPort(): int {}

    /**
     * Returns the hostname of the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.gethost.php
     */
    final public function getHost(): string {}

    /**
     * Returns the new description for the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.getnewdescription.php
     */
    final public function getNewDescription(): ServerDescription {}

    /**
     * Returns the previous description for the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.getpreviousdescription.php
     */
    final public function getPreviousDescription(): ServerDescription {}

    /**
     * Returns the topology ID associated with this server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.gettopologyid.php
     */
    final public function getTopologyId(): ObjectId {}

    final public function __wakeup(): void {}
}
