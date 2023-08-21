<?php

namespace MongoDB\Driver\Monitoring;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\TopologyDescription;

/**
 * @since 1.13.0
 */
final class TopologyChangedEvent
{
    final private function __construct() {}

    /**
     * Returns the new description for the topology
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-topologychangedevent.getnewdescription.php
     */
    final public function getNewDescription(): TopologyDescription {}

    /**
     * Returns the previous description for the topology
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-topologychangedevent.getpreviousdescription.php
     */
    final public function getPreviousDescription(): TopologyDescription {}

    /**
     * Returns the topology ID
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-topologychangedevent.gettopologyid.php
     */
    final public function getTopologyId(): ObjectId {}

    final public function __wakeup(): void {}
}
