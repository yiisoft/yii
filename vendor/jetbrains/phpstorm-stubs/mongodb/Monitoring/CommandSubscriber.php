<?php

namespace MongoDB\Driver\Monitoring;

/**
 * Classes may implement this interface to register an event subscriber that is notified for each started, successful, and failed command event.
 * @see https://secure.php.net/manual/en/mongodb.tutorial.apm.php
 * @link https://secure.php.net/manual/en/class.mongodb-driver-monitoring-commandsubscriber.php
 * @since 1.3.0
 */
interface CommandSubscriber extends Subscriber
{
    /**
     * Notification method for a failed command.
     * If the subscriber has been registered with MongoDB\Driver\Monitoring\addSubscriber(), the driver will call this method when a command has failed.
     * @link https://secure.php.net/manual/en/mongodb-driver-monitoring-commandsubscriber.commandfailed.php
     * @param CommandFailedEvent $event An event object encapsulating information about the failed command.
     * @return void
     * @throws \InvalidArgumentException on argument parsing errors.
     * @since 1.3.0
     */
    public function commandFailed(CommandFailedEvent $event);

    /**
     * Notification method for a started command.
     * If the subscriber has been registered with MongoDB\Driver\Monitoring\addSubscriber(), the driver will call this method when a command has started.
     * @link https://secure.php.net/manual/en/mongodb-driver-monitoring-commandsubscriber.commandstarted.php
     * @param CommandStartedEvent $event An event object encapsulating information about the started command.
     * @return void
     * @throws \InvalidArgumentException on argument parsing errors.
     * @since 1.3.0
     */
    public function commandStarted(CommandStartedEvent $event);

    /**
     * Notification method for a successful command.
     * If the subscriber has been registered with MongoDB\Driver\Monitoring\addSubscriber(), the driver will call this method when a command has succeeded.
     * @link https://secure.php.net/manual/en/mongodb-driver-monitoring-commandsubscriber.commandsucceeded.php
     * @param CommandSucceededEvent $event An event object encapsulating information about the successful command.
     * @return void
     * @throws \InvalidArgumentException on argument parsing errors.
     * @since 1.3.0
     */
    public function commandSucceeded(CommandSucceededEvent $event);
}
