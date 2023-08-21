<?php

namespace MongoDB\Driver\Monitoring;

/**
 * @since 1.13.0
 */
final class ServerHeartbeatSucceededEvent
{
    final private function __construct() {}

    /**
     * Returns the heartbeat's duration in microseconds
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.getdurationmicros.php
     */
    final public function getDurationMicros(): int {}

    /**
     * Returns the heartbeat reply document
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.getreply.php
     */
    final public function getReply(): object {}

    /**
     * Returns the port on which this server is listening
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.getport.php
     */
    final public function getPort(): int {}

    /**
     * Returns the hostname of the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.gethost.php
     */
    final public function getHost(): string {}

    /**
     * Returns whether the heartbeat used a streaming protocol
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.isstreaming.php
     */
    final public function isAwaited(): bool {}

    final public function __wakeup(): void {}
}
