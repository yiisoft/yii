<?php

namespace MongoDB\Driver;

/**
 * @since 1.13.0
 */
class TopologyDescription
{
    public const TYPE_UNKNOWN = 'Unknown';
    public const TYPE_SINGLE = 'Single';
    public const TYPE_SHARDED = 'Sharded';
    public const TYPE_REPLICA_SET_NO_PRIMARY = 'ReplicaSetNoPrimary';
    public const TYPE_REPLICA_SET_WITH_PRIMARY = 'ReplicaSetWithPrimary';
    public const TYPE_LOAD_BALANCED = 'LoadBalanced';

    final private function __construct() {}

    /**
     * Returns the servers in the topology
     * @link https://www.php.net/manual/en/mongodb-driver-topologydescription.getservers.php
     * @return ServerDescription[]
     */
    final public function getServers(): array {}

    /**
     * Returns a string denoting the type of this topology
     * @link https://www.php.net/manual/en/mongodb-driver-topologydescription.gettype.php
     */
    final public function getType(): string {}

    /**
     * Returns whether the topology has a readable server
     * @link https://www.php.net/manual/en/mongodb-driver-topologydescription.hasreadableserver.php
     */
    final public function hasReadableServer(?ReadPreference $readPreference = null): bool {}

    /**
     * Returns whether the topology has a writable server
     * @link https://www.php.net/manual/en/mongodb-driver-topologydescription.haswritableserver.php
     */
    final public function hasWritableServer(): bool {}

    final public function __wakeup(): void {}
}
