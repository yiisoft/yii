<?php

namespace Relay;

/**
 * Relay event class.
 */
final class Event
{
    /**
     * The integer representing the `INVALIDATED` event.
     *
     * @var int
     */
    public const INVALIDATED = 1;

    /**
     * The integer representing the `INVALIDATED` event.
     *
     * @var int
     */
    public const Invalidated = 1;

    /**
     * The integer representing the `FLUSHED` event.
     *
     * @var int
     */
    public const FLUSHED = 2;

    /**
     * The integer representing the `FLUSHED` event.
     *
     * @var int
     */
    public const Flushed = 2;

    /**
     * The type of the event represented by an integer.
     *
     * @var int
     */
    public int $type;

    /**
     * The event key. Only filled for `INVALIDATED` events.
     *
     * @var mixed
     */
    public mixed $key = null;

    /**
     * Whether the invalidation was created in the client or
     * originated from a Redis PUSH message.
     *
     * @var bool
     */
    public bool $client;
}
