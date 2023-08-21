<?php

namespace MongoDB\Driver\Monitoring;

use MongoDB\BSON\ObjectId;

/**
 * @since 1.13.0
 */
final class TopologyOpeningEvent
{
    final private function __construct() {}

    /**
     * Returns the topology ID
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-topologyopeningevent.gettopologyid.php
     */
    final public function getTopologyId(): ObjectId {}

    final public function __wakeup(): void {}
}
