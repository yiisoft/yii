<?php

namespace Relay;

/**
 * A collection of Redis data types.
 */
enum KeyType: int
{
    /**
     * @see Relay::REDIS_NOT_FOUND
     */
    case NotFound = Relay::REDIS_NOT_FOUND;

    /**
     * @see Relay::REDIS_STRING
     */
    case String = Relay::REDIS_STRING;

    /**
     * @see Relay::REDIS_SET
     */
    case Set = Relay::REDIS_SET;

    /**
     * @see Relay::REDIS_LIST
     */
    case List = Relay::REDIS_LIST;

    /**
     * @see Relay::REDIS_ZSET
     */
    case Zset = Relay::REDIS_ZSET;

    /**
     * @see Relay::REDIS_HASH
     */
    case Hash = Relay::REDIS_HASH;

    /**
     * @see Relay::REDIS_STREAM
     */
    case Stream = Relay::REDIS_STREAM;
}
