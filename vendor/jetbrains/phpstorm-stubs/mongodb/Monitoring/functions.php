<?php

namespace MongoDB\Driver\Monitoring;

/**
 * Registers a new monitoring event subscriber with the driver.
 * Registered subscribers will be notified of monitoring events through specific methods.
 * Note: If the object is already registered, this function is a no-op.
 * @link https://secure.php.net/manual/en/function.mongodb.driver.monitoring.addsubscriber.php
 * @param Subscriber $subscriber A monitoring event subscriber object to register.
 * @throws \InvalidArgumentException on argument parsing errors.
 * @since 1.3.0
 */
function addSubscriber(Subscriber $subscriber): void {}

/**
 * Unregisters an existing monitoring event subscriber from the driver.
 * Unregistered subscribers will no longer be notified of monitoring events.
 * Note: If the object is not registered, this function is a no-op.
 * @link https://secure.php.net/manual/en/function.mongodb.driver.monitoring.removesubscriber.php
 * @param Subscriber $subscriber A monitoring event subscriber object to register.
 * @throws \InvalidArgumentException on argument parsing errors.
 * @since 1.3.0
 */
function removeSubscriber(Subscriber $subscriber): void {}
