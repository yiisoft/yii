<?php

namespace MongoDB\Driver\Monitoring;

/**
 * @since 1.13.0
 * @link https://www.php.net/manual/en/class.mongodb-driver-monitoring-sdamsubscriber.php
 */
interface SDAMSubscriber extends Subscriber
{
    /**
     * Notification method for a server description change
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.serverchanged.php
     */
    public function serverChanged(ServerChangedEvent $event): void;

    /**
     * Notification method for closing a server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.serverclosed.php
     */
    public function serverClosed(ServerClosedEvent $event): void;

    /**
     * Notification method for opening a server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.serveropening.php
     */
    public function serverOpening(ServerOpeningEvent $event): void;

    /**
     * Notification method for a failed server heartbeat
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.serverheartbeatfailed.php
     */
    public function serverHeartbeatFailed(ServerHeartbeatFailedEvent $event): void;

    /**
     * Notification method for a started server heartbeat
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.serverheartbeatstarted.php
     */
    public function serverHeartbeatStarted(ServerHeartbeatStartedEvent $event): void;

    /**
     * Notification method for a successful server heartbeat
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.serverheartbeatsucceeded.php
     */
    public function serverHeartbeatSucceeded(ServerHeartbeatSucceededEvent $event): void;

    /**
     * Notification method for a topology description change
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.topologychanged.php
     */
    public function topologyChanged(TopologyChangedEvent $event): void;

    /**
     * Notification method for closing the topology
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.topologyclosed.php
     */
    public function topologyClosed(TopologyClosedEvent $event): void;

    /**
     * Notification method for opening the topology
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-sdamsubscriber.topologyopening.php
     */
    public function topologyOpening(TopologyOpeningEvent $event): void;
}
