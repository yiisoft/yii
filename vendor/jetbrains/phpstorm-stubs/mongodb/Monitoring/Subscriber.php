<?php

namespace MongoDB\Driver\Monitoring;

/**
 * Base interface for event subscribers.
 * This is used for type-hinting MongoDB\Driver\Monitoring\addSubscriber() and MongoDB\Driver\Monitoring\removeSubscriber() and should not be implemented directly.
 * This interface has no methods. Its only purpose is to be the base interface for all event subscribers.
 * @link https://secure.php.net/manual/en/class.mongodb-driver-monitoring-subscriber.php
 * @since 1.3.0
 */
interface Subscriber {}
