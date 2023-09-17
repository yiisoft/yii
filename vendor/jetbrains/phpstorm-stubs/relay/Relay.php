<?php

namespace Relay;

/**
 * Relay client.
 */
class Relay
{
    /**
     * Relay's version.
     *
     * @var string
     */
    public const VERSION = "0.6.2";

    /**
     * Relay's version.
     *
     * @var string
     */
    public const Version = "0.6.2";

    /**
     * Integer representing no compression algorithm.
     *
     * @var int
     */
    public const COMPRESSION_NONE = 0;

    /**
     * Integer representing the LZF compression algorithm.
     *
     * @var int
     */
    public const COMPRESSION_LZF = 1;

    /**
     * Integer representing the Zstandard compression algorithm.
     *
     * @var int
     */
    public const COMPRESSION_ZSTD = 2;

    /**
     * Integer representing the LZ4 compression algorithm.
     *
     * @var int
     */
    public const COMPRESSION_LZ4 = 3;

    /**
     * Integer representing no serializer.
     *
     * @var int
     */
    public const SERIALIZER_NONE = 0;

    /**
     * Integer representing the PHP serializer.
     *
     * @var int
     */
    public const SERIALIZER_PHP = 1;

    /**
     * Integer representing the igbinary serializer.
     *
     * @var int
     */
    public const SERIALIZER_IGBINARY = 2;

    /**
     * Integer representing the MessagePack serializer.
     *
     * @var int
     */
    public const SERIALIZER_MSGPACK = 3;

    /**
     * Integer representing the JSON serializer.
     *
     * @var int
     */
    public const SERIALIZER_JSON = 4;

    /**
     * Integer representing the atomic mode.
     *
     * @see \Relay\Relay::getMode()
     * @var int
     */
    public const ATOMIC = 0x00;

    /**
     * Integer representing the pipeline mode.
     *
     * @see \Relay\Relay::getMode()
     * @var int
     */
    public const PIPELINE = 0x02;

    /**
     * Integer representing the `MULTI` mode.
     *
     * @see \Relay\Relay::getMode()
     * @var int
     */
    public const MULTI = 0x01;

    /**
     * Integer representing the prefix option.
     *
     * @var int
     */
    public const OPT_PREFIX = 2;

    /**
     * Integer representing the read timeout option.
     *
     * @var int
     */
    public const OPT_READ_TIMEOUT = 3;

    /**
     * Integer representing the maximum retries option.
     *
     * @var int
     */
    public const OPT_MAX_RETRIES = 11;

    /**
     * Integer representing the backoff algorithm.
     *
     * @var int
     */
    public const OPT_BACKOFF_ALGORITHM = 12;

    /**
     * Toggle TCP_KEEPALIVE on a connection
     *
     * @var int
     */
    public const OPT_TCP_KEEPALIVE = 6;

    /**
     * Integer representing the default backoff algorithm.
     *
     * @var int
     */
    public const BACKOFF_ALGORITHM_DEFAULT = 0;

    /**
     * Integer representing the decorrelated jitter backoff algorithm.
     *
     * @var int
     */
    public const BACKOFF_ALGORITHM_DECORRELATED_JITTER = 1;

    /**
     * Integer representing the full jitter backoff algorithm.
     *
     * @var int
     */
    public const BACKOFF_ALGORITHM_FULL_JITTER = 2;

    /**
     * Integer representing the base for backoff computation.
     *
     * @var int
     */
    public const OPT_BACKOFF_BASE = 13;

    /**
     * Integer representing the backoff time cap.
     *
     * @var int
     */
    public const OPT_BACKOFF_CAP = 14;

    /**
     * Integer representing the PhpRedis compatibility mode option.
     *
     * Enabled by default. Disabling will cause Relay to:
     * 1. Return `null` when a key doesn't exist, instead of `false`
     * 2. Throw exceptions when an error occurs, instead of returning `false`
     *
     * @var int
     */
    public const OPT_PHPREDIS_COMPATIBILITY = 100;

    /**
     * Integer representing the serializer option.
     *
     * @var int
     */
    public const OPT_SERIALIZER = 1;

    /**
     * Integer representing the compression option.
     *
     * @var int
     */
    public const OPT_COMPRESSION = 7;

    /**
     * Integer representing the compression level option.
     *
     * @var int
     */
    public const OPT_COMPRESSION_LEVEL = 9;

    /**
     * Integer representing the reply literal option.
     *
     * @var int
     */
    public const OPT_REPLY_LITERAL = 8;

    /**
     * Integer representing the null-multi-bulk-as-null option.
     *
     * @var int
     */
    public const OPT_NULL_MULTIBULK_AS_NULL = 10;

    /**
     * Integer representing the throw-on-error option.
     *
     * Disabled by default. When enabled, Relay will throw exceptions when errors occur.
     *
     * @var int
     */
    public const OPT_THROW_ON_ERROR = 105;

    /**
     * Integer representing Relay’s invalidation option.
     *
     * Enabled by default. When disabled will prevent Relay from
     * performing instantaneous client-side invalidation when a key
     * is changed without waiting for Redis to send an `INVALIDATE`
     * message. The invalidation occurs only in the same FPM pool.
     *
     * @var int
     */
    public const OPT_CLIENT_INVALIDATIONS = 101;

    /**
     * Integer representing Relay’s allow patterns option.
     *
     * When set only keys matching these patterns will be cached,
     * unless they also match an `OPT_IGNORE_PATTERNS`.
     *
     * @var int
     */
    public const OPT_ALLOW_PATTERNS = 102;

    /**
     * Integer representing Relay’s ignore patterns option.
     *
     * When set keys matching these patterns will not be cached.
     *
     * @var int
     */
    public const OPT_IGNORE_PATTERNS = 103;

    /**
     * Whether use in-memory caching. Enabled by default.
     *
     * @var int
     */
    public const OPT_USE_CACHE = 104;

    /**
     * Integer representing the scan option.
     *
     * @var int
     */
    public const OPT_SCAN = 4;

    /**
     * Issue one `SCAN` command at a time, sometimes returning an empty array of results.
     *
     * @var int
     */
    public const SCAN_NORETRY = 0;

    /**
     * Retry the `SCAN` command until keys come back, or iterator of zero is returned.
     *
     * @var int
     */
    public const SCAN_RETRY = 1;

    /**
     * Prepend the set prefix to any `MATCH` pattern.
     *
     * @var int
     */
    public const SCAN_PREFIX = 2;

    /**
     * Do not prepend the set prefix to any `MATCH` pattern.
     *
     * @var int
     */
    public const SCAN_NOPREFIX = 3;

    /**
     * Redis command argument.
     *
     * @internal
     * @var string
     */
    public const BEFORE = 'BEFORE';

    /**
     * Redis command argument.
     *
     * @internal
     * @var string
     */
    public const AFTER = 'AFTER';

    /**
     * Redis command argument.
     *
     * @internal
     * @var string
     */
    public const LEFT = 'LEFT';

    /**
     * Redis command argument.
     *
     * @internal
     * @var string
     */
    public const RIGHT = 'RIGHT';

    /**
     * Integer representing "key not found".
     *
     * @see \Relay\Relay::type()
     * @var int
     */
    public const REDIS_NOT_FOUND = 0;

    /**
     * Integer representing Redis `string` type.
     *
     * @see \Relay\Relay::type()
     * @var int
     */
    public const REDIS_STRING = 1;

    /**
     * Integer representing Redis `set` type.
     *
     * @see \Relay\Relay::type()
     * @var int
     */
    public const REDIS_SET = 2;

    /**
     * Integer representing Redis `list` type.
     *
     * @see \Relay\Relay::type()
     * @var int
     */
    public const REDIS_LIST = 3;

    /**
     * Integer representing Redis `zset` type.
     *
     * @see \Relay\Relay::type()
     * @var int
     */
    public const REDIS_ZSET = 4;

    /**
     * Integer representing Redis `hash` type.
     *
     * @see \Relay\Relay::type()
     * @var int
     */
    public const REDIS_HASH = 5;

    /**
     * Integer representing Redis `stream` type.
     *
     * @see \Relay\Relay::type()
     * @var int
     */
    public const REDIS_STREAM = 6;

    /**
     * Establishes a new connection to Redis, or re-uses already opened connection.
     *
     * @param  string  $host
     * @param  int  $port
     * @param  float  $connect_timeout
     * @param  float  $command_timeout
     * @param  array  $context
     */
    #[\Relay\Attributes\Server]
    public function __construct(
        string $host = null,
        int $port = 6379,
        float $connect_timeout = 0.0,
        float $command_timeout = 0.0,
        #[\SensitiveParameter] array $context = [],
        int $database = 0,
    ) {}

    /**
     * Establishes a new connection to Redis.
     * Will use `pconnect()` unless `relay.default_pconnect` is disabled.
     *
     * @param  string  $host
     * @param  int  $port
     * @param  float  $timeout
     * @param  string|null  $persistent_id
     * @param  int  $retry_interval
     * @param  float  $read_timeout
     * @param  array  $context
     * @param  int  $database
     * @return bool
     */
    #[\Relay\Attributes\Server]
    public function connect(
        string $host,
        int $port = 6379,
        float $timeout = 0.0,
        ?string $persistent_id = null,
        int $retry_interval = 0,
        float $read_timeout = 0.0,
        #[\SensitiveParameter] array $context = [],
        int $database = 0
    ): bool {}

    /**
     * Establishes a persistent connection to Redis.
     *
     * @param  string  $host
     * @param  int  $port
     * @param  float  $timeout
     * @param  string|null  $persistent_id
     * @param  int  $retry_interval
     * @param  float  $read_timeout
     * @param  array  $context
     * @param  int  $database
     * @return bool
     */
    #[\Relay\Attributes\Server]
    public function pconnect(
        string $host,
        int $port = 6379,
        float $timeout = 0.0,
        ?string $persistent_id = null,
        int $retry_interval = 0,
        float $read_timeout = 0.0,
        #[\SensitiveParameter] array $context = [],
        int $database = 0
    ): bool {}

    /**
     * Closes the current connection, unless it's persistent.
     *
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public function close(): bool {}

    /**
     * Closes the current connection, if it's persistent.
     *
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public function pclose(): bool {}

    /**
     * Registers a new event listener.
     *
     * @param  callable  $callback
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public function listen(?callable $callback): bool {}

    /**
     * Registers a new `flushed` event listener.
     *
     * @param  callable  $callback
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public function onFlushed(?callable $callback): bool {}

    /**
     * Registers a new `invalidated` event listener.
     *
     * @param  callable  $callback
     * @param  string|null  $pattern
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public function onInvalidated(?callable $callback, ?string $pattern = null): bool {}

    /**
     * Dispatches all pending events.
     *
     * @return int|false
     */
    #[\Relay\Attributes\Local]
    public function dispatchEvents(): int|false {}

    /**
     * Returns a client option.
     *
     * @param  int  $option
     * @return mixed
     */
    #[\Relay\Attributes\Local]
    public function getOption(int $option): mixed {}

    /**
     * Returns or sets a client option.
     *
     * @param  int  $option
     * @param  mixed  $value
     * @return mixed
     */
    #[\Relay\Attributes\Local]
    public function option(int $option, mixed $value = null): mixed {}

    /**
     * Sets a client option.
     *
     * Relay specific options:
     *
     * - `OPT_ALLOW_PATTERNS`
     * - `OPT_IGNORE_PATTERNS`
     * - `OPT_THROW_ON_ERROR`
     * - `OPT_CLIENT_INVALIDATIONS`
     * - `OPT_PHPREDIS_COMPATIBILITY`
     *
     * Supported PhpRedis options:
     *
     * - `OPT_PREFIX`
     * - `OPT_READ_TIMEOUT`
     * - `OPT_COMPRESSION`
     * - `OPT_COMPRESSION_LEVEL`
     * - `OPT_MAX_RETRIES`
     * - `OPT_BACKOFF_ALGORITHM`
     * - `OPT_BACKOFF_BASE`
     * - `OPT_BACKOFF_CAP`
     * - `OPT_SCAN`
     * - `OPT_REPLY_LITERAL`
     * - `OPT_NULL_MULTIBULK_AS_NULL`
     *
     * @param  int  $option
     * @param  mixed  $value
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public function setOption(int $option, mixed $value): bool {}

    /**
     * Adds ignore pattern(s). Matching keys will not be cached in memory.
     *
     * @param  string  $pattern,...
     * @return int
     */
    #[\Relay\Attributes\Local]
    public function addIgnorePatterns(string ...$pattern): int {}

    /**
     * Adds allow pattern(s). Only matching keys will be cached in memory.
     *
     * @param  string  $pattern,...
     * @return int
     */
    #[\Relay\Attributes\Local]
    public function addAllowPatterns(string ...$pattern): int {}

    /**
     * Returns the connection timeout.
     *
     * @return float|false
     */
    #[\Relay\Attributes\Local]
    public function getTimeout(): float|false {}

    /**
     * @see Relay\Relay::getTimeout()
     *
     * @return float|false
     */
    #[\Relay\Attributes\Local]
    public function timeout(): float|false {}

    /**
     * Returns the read timeout.
     *
     * @return float|false
     */
    #[\Relay\Attributes\Local]
    public function getReadTimeout(): float|false {}

    /**
     * @see Relay\Relay::getReadTimeout()
     *
     * @return float|false
     */
    #[\Relay\Attributes\Local]
    public function readTimeout(): float|false {}

    /**
     * Returns the number of bytes sent and received over the network during the Relay object's
     * lifetime, or since the last time {@link Relay::clearBytes()} was called.
     *
     * @return array{int, int}
     */
    #[\Relay\Attributes\Local]
    public function getBytes(): array {}

    /**
     * @see Relay\Relay::getBytes()
     *
     * @return array{int, int}
     */
    #[\Relay\Attributes\Local]
    public function bytes(): array {}

    /**
     * Returns the host or unix socket.
     *
     * @return string|false
     */
    #[\Relay\Attributes\Local]
    public function getHost(): string|false {}

    /**
     * Whether Relay is connected to Redis.
     *
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public function isConnected(): bool {}

    /**
     * Returns the port.
     *
     * @return int|false
     */
    #[\Relay\Attributes\Local]
    public function getPort(): int|false {}

    /**
     * Returns the authentication information.
     * In PhpRedis compatibility mode this method returns any configured password in plain-text.
     *
     * @return mixed
     */
    #[\Relay\Attributes\Local]
    public function getAuth(): mixed {}

    /**
     * Returns the currently selected DB
     *
     * @return int|false
     */
    #[\Relay\Attributes\Local]
    public function getDbNum(): mixed {}

    /**
     * Returns the serialized value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    #[\Relay\Attributes\Local]
    public function _serialize(mixed $value): mixed {}

    /**
     * Returns the unserialized value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    #[\Relay\Attributes\Local]
    public function _unserialize(mixed $value): mixed {}

    /**
     * Compress data with Relay's currently configured compression algorithm.
     *
     * @param  string  $value
     * @return string
     */
    #[\Relay\Attributes\Local]
    public function _compress(string $value): string {}

    /**
     * Uncompress data with Relay's currently configured compression algorithm.
     *
     * @param  string  $value
     * @return string
     */
    #[\Relay\Attributes\Local]
    public function _uncompress(string $value): string {}

    /**
     * Returns the serialized and compressed value.
     *
     * @param  mixed  $value
     * @return string
     */
    #[\Relay\Attributes\Local]
    public function _pack(mixed $value): string {}

    /**
     * Returns the unserialized and decompressed value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    #[\Relay\Attributes\Local]
    public function _unpack(mixed $value): mixed {}

    /**
     * Returns the value with the prefix.
     *
     * @param  mixed  $value
     * @return string
     */
    #[\Relay\Attributes\Local]
    public function _prefix(mixed $value): string {}

    /**
     * Returns the last error message, if any.
     *
     * @return string|null
     */
    #[\Relay\Attributes\Local]
    public function getLastError(): string|null {}

    /**
     * Clears the last error that is set, if any.
     *
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public function clearLastError(): bool {}

    /**
     * Returns the connection's endpoint identifier.
     *
     * @return string|false
     */
    #[\Relay\Attributes\Local]
    public function endpointId(): string|false {}

    /**
     * @see Relay\Relay::endpointId()
     *
     * @return string|false
     */
    public function getPersistentID(): string|false {}

    /**
     * Returns a unique representation of the underlying socket connection identifier.
     *
     * @return string|false
     */
    #[\Relay\Attributes\Local]
    public function socketId(): string|false {}

    /**
     * Returns information about the license.
     *
     * @return array
     */
    #[\Relay\Attributes\Local]
    public static function license(): array {}

    /**
     * Returns statistics about Relay.
     *
     * - `usage.total_requests`: The total number of requests we've seen
     * - `usage.active_requests`: The number of requests currently in-flight
     * - `usage.max_active_requests`: The most concurrent in-flight requests we've seen
     * - `usage.free_epoch_records`: The estimated number of free epoch reclamation records
     *
     * - `stats.requests`: The total number of requests the cache has received
     * - `stats.misses`: Requests where we had to ask Redis for a value
     * - `stats.hits`: Requests where we did not have to ask redis for the value
     * - `stats.dirty_skips`: The number of times Relay has skipped an entire database because it was dirty.
     * - `stats.errors`: How many times a 'severe' error occurs (presently this is only incremented if we get a `null` response from hiredis)
     * - `stats.empty`: How many times we've run out of free requests (indicating the size of the ring buffers should be increased)
     * - `stats.oom`: The number of times we've run out of memory
     * - `stats.ops_per_sec`: The number of commands processed per second
     * - `stats.walltime`: The number of microseconds Relay has spent doing work
     * - `stats.bytes_sent`: The number of bytes Relay has written to the network
     * - `stats.bytes_received`: The number of bytes Relay has read from the network
     *
     * - `memory.total`: The total bytes of allocated memory
     * - `memory.limit`: The capped number of bytes Relay has available to use
     * - `memory.active`: The total amount of memory mapped into the allocator
     * - `memory.used`: The amount of memory pointing to live objects including metadata
     *
     * - `endpoints.*.redis`: Information about the connected Redis server.
     * - `endpoints.*.connections.*.keys`: The total number of cached keys for the connection.
     *
     * @return array
     */
    #[\Relay\Attributes\Local]
    public static function stats(): array {}

    /**
     * Returns the number of bytes allocated, or `0` in client-only mode.
     *
     * @return int
     */
    #[\Relay\Attributes\Local]
    public static function maxMemory(): int {}

    /**
     * Returns the number of bytes allocated, or `0` in client-only mode.
     *
     * @deprecated 0.5.0 Use `Relay:maxMemory()`
     *
     * @return int
     */
    #[\Relay\Attributes\Local]
    public static function memory(): int {}

    /**
     * Execute any command against Redis, without applying
     * the prefix, compression and serialization.
     *
     * @param  string  $cmd
     * @param  mixed  $args,...
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function rawCommand(string $cmd, mixed ...$args): mixed {}

    /**
     * Select the Redis logical database having the specified zero-based numeric index.
     *
     * @param  int  $db
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function select(int $db): Relay|bool {}

    /**
     * Authenticate the connection using a password or an ACL username and password.
     *
     * @param  mixed  $auth
     * @return bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function auth(#[\SensitiveParameter] mixed $auth): bool {}

    /**
     * The INFO command returns information and statistics about Redis in a format
     * that is simple to parse by computers and easy to read by humans.
     *
     * @see https://redis.io/commands/info
     *
     * @param  string  $sections,...
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function info(string ...$sections): Relay|array|false {}

    /**
     * Deletes all the keys of the currently selected database.
     *
     * @param  bool  $async
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function flushdb(bool $async = false): Relay|bool {}

    /**
     * Deletes all the keys of all the existing databases, not just the currently selected one.
     *
     * @param  bool  $async
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function flushall(bool $async = false): Relay|bool {}

    /**
     * Invokes a Redis function.
     *
     * @param  string  $name
     * @param  array  $keys
     * @param  array  $argv
     * @param  callable  $handler
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function fcall(string $name, array $keys = [], array $argv = [], callable $handler = null): mixed {}

    /**
     * Invokes a read-only Redis function.
     *
     * @param  string  $name
     * @param  array  $keys
     * @param  array  $argv
     * @param  callable  $handler
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function fcall_ro(string $name, array $keys = [], array $argv = [], callable $handler = null): mixed {}

    /**
     * Calls `FUNCTION` sub-command.
     *
     * @param  string  $op
     * @param  string  $args,...
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function function(string $op, string ...$args): mixed {}

    /**
     * Flushes Relay's in-memory cache of all databases.
     * When given an endpoint, only that connection will be flushed.
     * When given an endpoint and database index, only that database
     * for that connection will be flushed.
     *
     * @param  string|null  $endpointId
     * @param  int|null  $db
     * @return bool
     */
    #[\Relay\Attributes\Local]
    public static function flushMemory(?string $endpointId = null, int $db = null): bool {}

    /**
     * Returns the number of keys in the currently-selected database.
     *
     * @return Relay|int
     */
    #[\Relay\Attributes\RedisCommand]
    public function dbsize(): Relay|int|false {}

    /**
     * Serialize and return the value stored at key in a Redis-specific format.
     *
     * @param  mixed  $key
     * @return Relay|string|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function dump(mixed $key): Relay|string|false {}

    /**
     * Attach or detach the instance as a replica of another instance.
     *
     * @param  string|null  $host
     * @param  int  $port
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function replicaof(?string $host = null, $port = 0): Relay|bool {}

    /**
     * Create a key associated with a value that is obtained by deserializing the provided serialized value.
     *
     * @param  mixed  $key
     * @param  int  $ttl
     * @param  string  $value
     * @param  array  $options
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function restore(mixed $key, int $ttl, string $value, ?array $options = null): Relay|bool {}

    /**
     * Atomically transfer a key from a Redis instance to another one.
     *
     * @param  string  $host
     * @param  int  $port
     * @param  string|array  $key
     * @param  int  $dstdb
     * @param  int  $timeout
     * @param  bool  $copy
     * @param  bool  $replace
     * @param  mixed  $credentials
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function migrate(
        string $host,
        int $port,
        string|array $key,
        int $dstdb,
        int $timeout,
        bool $copy = false,
        bool $replace = false,
        #[\SensitiveParameter] mixed $credentials = null
    ): Relay|bool {}

    /**
     * This command copies the value stored at the source key to the destination key.
     *
     * @param  mixed  $src
     * @param  mixed  $dst
     * @param  array  $options
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function copy(mixed $src, mixed $dst, ?array $options = null): Relay|int|false {}

    /**
     * Asks Redis to echo back the provided string.
     *
     * @param  string  $arg
     * @return Relay|bool|string
     */
    #[\Relay\Attributes\RedisCommand]
    public function echo(string $arg): Relay|bool|string {}

    /**
     * Returns PONG if no argument is provided, otherwise return a copy of the argument as a bulk.
     *
     * @param  string  $arg
     * @return Relay|bool|string
     */
    #[\Relay\Attributes\RedisCommand]
    public function ping(string $arg = null): Relay|bool|string {}

    /**
     * Returns the number of milliseoconds since Relay has seen activity from the server.
     *
     * @return Relay|int|false
     */
    #[\Relay\Attributes\Local]
    public function idleTime(): Relay|int|false {}

    /**
     * Returns a random key from Redis.
     *
     * @return Relay|string|null|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function randomkey(): Relay|string|null|bool {}

    /**
     * Returns the current time from Redis.
     *
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function time(): Relay|array|false {}

    /**
     * Asynchronously rewrite the append-only file.
     *
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function bgrewriteaof(): Relay|bool {}

    /**
     * Returns the UNIX time stamp of the last successful save to disk.
     *
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function lastsave(): Relay|int|false {}

    /**
     * Asynchronously save the dataset to disk.
     *
     * @param  bool  $schedule
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function bgsave(bool $schedule = false): Relay|bool {}

    /**
     * Synchronously save the dataset to disk.
     *
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function save(): Relay|bool {}

    /**
     * Returns the role of the instance in the context of replication.
     *
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function role(): Relay|array|false {}

    /**
     * Returns the remaining time to live of a key that has a timeout in seconds.
     *
     * @param  mixed  $key
     * @return Relay|int
     */
    #[\Relay\Attributes\RedisCommand]
    public function ttl(mixed $key): Relay|int|false {}

    /**
     * Returns the remaining time to live of a key that has a timeout in milliseconds.
     *
     * @param  mixed  $key
     * @return Relay|int
     */
    #[\Relay\Attributes\RedisCommand]
    public function pttl(mixed $key): Relay|int|false {}

    /**
     * Returns if key(s) exists.
     *
     * @param  mixed  $keys,...
     * @return Relay|bool|int
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function exists(mixed ...$keys): Relay|bool|int {}

    /**
     * Evaluate script using the Lua interpreter.
     *
     * @see https://redis.io/commands/eval
     *
     * @param  mixed  $script
     * @param  array  $args
     * @param  int  $num_keys
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function eval(mixed $script, array $args = [], int $num_keys = 0): mixed {}

    /**
     * Evaluate script using the Lua interpreter.  This is just the "read-only" variant of EVAL
     * meaning it can be run on read-only replicas.
     *
     * @see https://redis.io/commands/eval_ro
     *
     * @param  mixed  $script
     * @param  array  $args
     * @param  int  $num_keys
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function eval_ro(mixed $script, array $args = [], int $num_keys = 0): mixed {}

    /**
     * Evaluates a script cached on the server-side by its SHA1 digest.
     *
     *
     * @param  string  $sha
     * @param  array  $args
     * @param  int  $num_keys
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function evalsha(string $sha, array $args = [], int $num_keys = 0): mixed {}

    /**
     * Evaluates a script cached on the server-side by its SHA1 digest.  This is just the "read-only" variant
     * of `EVALSHA` meaning it can be run on read-only replicas.
     *
     * @param  string  $sha
     * @param  array  $args
     * @param  int  $num_keys
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function evalsha_ro(string $sha, array $args = [], int $num_keys = 0): mixed {}

    /**
     * Executes `CLIENT` command operations.
     *
     * @param  string  $operation
     * @param  mixed  $args,...
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function client(string $operation, mixed ...$args): mixed {}

    /**
     * Add one or more members to a geospacial sorted set
     *
     * @param  string  $key
     * @param  float  $lng
     * @param  float  $lat
     * @param  string  $member
     * @param  mixed  $other_triples_and_options,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function geoadd(
        string $key,
        float $lng,
        float $lat,
        string $member,
        mixed ...$other_triples_and_options
    ): Relay|int|false {}

    /**
     * Get the distance between two members of a geospacially encoded sorted set.
     *
     * @param  string  $key
     * @param  string  $src
     * @param  string  $dst
     * @param  string|null  $unit
     * @return Relay|float|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function geodist(string $key, string $src, string $dst, ?string $unit = null): Relay|float|false {}

    /**
     * Retrieve one or more GeoHash encoded strings for members of the set.
     *
     * @param  string  $key
     * @param  string  $member
     * @param  string  $other_members,...
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function geohash(string $key, string $member, string ...$other_members): Relay|array|false {}

    /**
     * Retrieve members of a geospacially sorted set that are within a certain radius of a location.
     *
     * @param  string  $key
     * @param  float  $lng
     * @param  float  $lat
     * @param  float  $radius
     * @param  string  $unit
     * @param  array  $options
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function georadius(string $key, float $lng, float $lat, float $radius, string $unit, array $options = []): mixed {}

    /**
     * Similar to `GEORADIUS` except it uses a member as the center of the query.
     *
     * @param  string  $key
     * @param  string  $member
     * @param  float  $radius
     * @param  string  $unit
     * @param  array  $options
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function georadiusbymember(string $key, string $member, float $radius, string $unit, array $options = []): mixed {}

    /**
     * Similar to `GEORADIUS` except it uses a member as the center of the query.
     *
     * @param  string  $key
     * @param  string  $member
     * @param  float  $radius
     * @param  string  $unit
     * @param  array  $options
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function georadiusbymember_ro(string $key, string $member, float $radius, string $unit, array $options = []): mixed {}

    /**
     * Retrieve members of a geospacially sorted set that are within a certain radius of a location.
     *
     * @param  string  $key
     * @param  float  $lng
     * @param  float  $lat
     * @param  float  $radius
     * @param  string  $unit
     * @param  array  $options
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function georadius_ro(string $key, float $lng, float $lat, float $radius, string $unit, array $options = []): mixed {}

    /**
     * Search a geospacial sorted set for members in various ways.
     *
     * @param  string  $key
     * @param  array|string  $position
     * @param  array|int|float  $shape
     * @param  string  $unit
     * @param  array  $options
     * @return Relay|array
     */
    #[\Relay\Attributes\RedisCommand]
    public function geosearch(
        string $key,
        array|string $position,
        array|int|float $shape,
        string $unit,
        array $options = []
    ): Relay|array {}

    /**
     * Search a geospacial sorted set for members within a given area or range, storing the results into
     * a new set.
     *
     * @param  string  $dst
     * @param  string  $src
     * @param  array|string  $position
     * @param  array|int|float  $shape
     * @param  string  $unit
     * @param  array  $options
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function geosearchstore(
        string $dst,
        string $src,
        array|string $position,
        array|int|float $shape,
        string $unit,
        array $options = []
    ): Relay|int|false {}

    /**
     * Get the value of key.
     *
     * @param  mixed  $key
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function get(mixed $key): mixed {}

    /**
     * Atomically sets key to value and returns the old value stored at key.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function getset(mixed $key, mixed $value): mixed {}

    /**
     * Returns the substring of the string value stored at key,
     * determined by the offsets start and end (both are inclusive).
     *
     * @param  mixed  $key
     * @param  int  $start
     * @param  int  $end
     * @return Relay|string|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function getrange(mixed $key, int $start, int $end): Relay|string|false {}

    /**
     * Overwrites part of the string stored at key, starting at
     * the specified offset, for the entire length of value.
     *
     * @param  mixed  $key
     * @param  int  $start
     * @param  mixed  $value
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function setrange(mixed $key, int $start, mixed $value): Relay|int|false {}

    /**
     * Returns the bit value at offset in the string value stored at key.
     *
     * @param  mixed  $key
     * @param  int  $pos
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function getbit(mixed $key, int $pos): Relay|int|false {}

    /**
     * Count the number of set bits (population counting) in a string.
     *
     * @param  mixed  $key
     * @param  int  $start
     * @param  int  $end
     * @param  bool  $by_bit
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function bitcount(mixed $key, int $start = 0, int $end = -1, bool $by_bit = false): Relay|int|false {}

    /**
     * This is a container command for runtime configuration commands.
     *
     * @param  string  $operation
     * @param  mixed  $key
     * @param  string|null  $value
     * @return Relay|array|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function config(string $operation, mixed $key = null, ?string $value = null): Relay|array|bool {}

    /**
     * Return an array with details about every Redis command.
     *
     * @param  array  $args,...
     * @return Relay|array|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function command(mixed ...$args): Relay|array|int|false {}

    /**
     * Perform a bitwise operation on one or more keys, storing the result in a new key.
     *
     * @param  string  $operation
     * @param  string  $dstkey
     * @param  string  $srckey
     * @param  string  $other_keys,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function bitop(string $operation, string $dstkey, string $srckey, string ...$other_keys): Relay|int|false {}

    /**
     * Return the position of the first bit set to 1 or 0 in a string.
     *
     * @param  mixed  $key
     * @param  int  $bit
     * @param  int  $start
     * @param  int  $end
     * @param  bool  $bybit
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function bitpos(mixed $key, int $bit, int $start = null, int $end = null, bool $bybit = false): Relay|int|false {}

    /**
     * Sets or clears the bit at offset in the string value stored at key.
     *
     * @param  mixed  $key
     * @param  int  $pos
     * @param  int  $val
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function setbit(mixed $key, int $pos, int $val): Relay|int|false {}

    /**
     * Interact with Redis' ACLs
     *
     * @param  string  $cmd
     * @param  string  $args,...
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function acl(string $cmd, string ...$args): mixed {}

    /**
     * If key already exists and is a string, this command appends
     * the value at the end of the string. If key does not exist
     * it is created and set as an empty string, so APPEND will
     * be similar to SET in this special case.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function append(mixed $key, mixed $value): Relay|int|false {}

    /**
     * Set key to hold the string value. If key already holds
     * a value, it is overwritten, regardless of its type.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  mixed  $options
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function set(mixed $key, mixed $value, mixed $options = null): mixed {}

    /**
     * Get the value of key and optionally set its expiration.
     * GETEX is similar to GET, but is a write command with additional options.
     *
     * @param  mixed  $key
     * @param  array  $options
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function getex(mixed $key, ?array $options = null): mixed {}

    /**
     * Get the value of key and delete the key. This command is similar to GET,
     * except for the fact that it also deletes the key on success
     * (if and only if the key's value type is a string).
     *
     * @param  mixed  $key
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function getdel(mixed $key): mixed {}

    /**
     * Set key to hold the string value and set key to timeout after a given number of seconds.
     *
     * @param  mixed  $key
     * @param  int  $seconds
     * @param  mixed  $value
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function setex(mixed $key, int $seconds, mixed $value): Relay|bool {}

    /**
     * Adds the specified elements to the specified HyperLogLog.
     *
     * @param  string  $key
     * @param  array  $elements
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function pfadd(string $key, array $elements): Relay|int|false {}

    /**
     * Return the approximated cardinality of the set(s) observed by the HyperLogLog at key(s).
     *
     * @param  string  $key
     * @return Relay|int
     */
    #[\Relay\Attributes\RedisCommand]
    public function pfcount(string $key): Relay|int|false {}

    /**
     * Merge given HyperLogLogs into a single one.
     *
     * @param  string  $dst
     * @param  array  $srckeys
     * @return Relay|bool
     */
    public function pfmerge(string $dst, array $srckeys): Relay|bool {}

    /**
     * Set key to hold the string value and set key to timeout after a given number of milliseconds.
     *
     *
     * @param  mixed  $key
     * @param  int  $milliseconds
     * @param  mixed  $value
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function psetex(mixed $key, int $milliseconds, mixed $value): Relay|bool {}

    /**
     * Posts a message to the given channel.
     *
     * @param  string  $channel
     * @param  string  $message
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function publish(string $channel, string $message): Relay|int|false {}

    /**
     * Set key to hold string value if key does not exist. In that case, it is equal to SET.
     * When key already holds a value, no operation is performed.
     * SETNX is short for "SET if Not eXists".
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function setnx(mixed $key, mixed $value): Relay|bool {}

    /**
     * Returns the values of all specified keys.
     *
     * @param  array  $keys
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function mget(array $keys): Relay|array|false {}

    /**
     * Move key from the currently selected database to the specified destination database.
     *
     * @param  mixed  $key
     * @param  int  $db
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function move(mixed $key, int $db): Relay|int|false {}

    /**
     * Sets the given keys to their respective values.
     * MSET replaces existing values with new values, just as regular SET.
     *
     * @param  array  $kvals
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function mset(array $kvals): Relay|bool {}

    /**
     * Sets the given keys to their respective values.
     * MSETNX will not perform any operation at all even if just a single key already exists.
     *
     * @param  array  $kvals
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function msetnx(array $kvals): Relay|bool {}

    /**
     * Renames key.
     *
     * @param  mixed  $key
     * @param  mixed  $newkey
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function rename(mixed $key, mixed $newkey): Relay|bool {}

    /**
     * Renames key if the new key does not yet exist.
     *
     * @param  mixed  $key
     * @param  mixed  $newkey
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function renamenx(mixed $key, mixed $newkey): Relay|bool {}

    /**
     * Removes the specified keys.
     *
     * @param  mixed  $keys,...
     * @return Relay|int|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function del(mixed ...$keys): Relay|int|bool {}

    /**
     * Removes the specified keys without blocking Redis.
     *
     * @param  mixed  $keys,...
     * @return Relay|int
     */
    #[\Relay\Attributes\RedisCommand]
    public function unlink(mixed ...$keys): Relay|int|false {}

    /**
     * Set a timeout on key.
     *
     * @param  mixed  $key
     * @param  int  $seconds
     * @param  string|null  $mode
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function expire(mixed $key, int $seconds, ?string $mode = null): Relay|bool {}

    /**
     * Set a key's time to live in milliseconds.
     *
     * @param  mixed  $key
     * @param  int  $milliseconds
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function pexpire(mixed $key, int $milliseconds): Relay|bool {}

    /**
     * Set a timeout on key using a unix timestamp.
     *
     * @param  mixed  $key
     * @param  int  $timestamp
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function expireat(mixed $key, int $timestamp): Relay|bool {}

    /**
     * Returns the absolute Unix timestamp in seconds at which the given key will expire.
     * If the key exists but doesn't have a TTL this function return -1.
     * If the key does not exist -2.
     *
     * @param  mixed  $key
     * @return Relay|int|false
     * */
    #[\Relay\Attributes\RedisCommand]
    public function expiretime(mixed $key): Relay|int|false {}

    /**
     * Set the expiration for a key as a UNIX timestamp specified in milliseconds.
     *
     * @param  mixed  $key
     * @param  int  $timestamp_ms
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function pexpireat(mixed $key, int $timestamp_ms): Relay|bool {}

    /**
     * Semantic the same as EXPIRETIME, but returns the absolute Unix expiration
     * timestamp in milliseconds instead of seconds.
     *
     * @param  mixed  $key
     * @return Relay|int|false
     * */
    #[\Relay\Attributes\RedisCommand]
    public function pexpiretime(mixed $key): Relay|int|false {}

    /**
     * Remove the existing timeout on key, turning the key from volatile to persistent.
     *
     * @param  mixed  $key
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function persist(mixed $key): Relay|bool {}

    /**
     * Returns the type of a given key.
     *
     * In PhpRedis compatibility mode this will return an integer
     * (one of the REDIS_<type>) constants. Otherwise it will
     * return the string that Redis returns.
     *
     * @param  mixed  $key
     * @return Relay|int|string|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function type(mixed $key): Relay|int|string|bool {}

    /**
     * Atomically returns and removes the first/last element of the list
     * stored at source, and pushes the element at the first/last
     * element of the list stored at destination.
     *
     * @param  mixed  $srckey
     * @param  mixed  $dstkey
     * @param  string  $srcpos
     * @param  string  $dstpos
     * @return Relay|string|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function lmove(mixed $srckey, mixed $dstkey, string $srcpos, string $dstpos): Relay|string|null|false {}

    /**
     * BLMOVE is the blocking variant of LMOVE. When source contains elements,
     * this command behaves exactly like LMOVE. When used inside a
     * MULTI/EXEC block, this command behaves exactly like LMOVE.
     *
     * @param  mixed  $srckey
     * @param  mixed  $dstkey
     * @param  string  $srcpos
     * @param  string  $dstpos
     * @param  float  $timeout
     * @return Relay|string|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function blmove(mixed $srckey, mixed $dstkey, string $srcpos, string $dstpos, float $timeout): Relay|string|null|false {}

    /**
     * Returns the specified elements of the list stored at key.
     *
     * @param  mixed  $key
     * @param  int  $start
     * @param  int  $stop
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function lrange(mixed $key, int $start, int $stop): Relay|array|false {}

    /**
     * Insert all the specified values at the head of the list stored at key.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  mixed  $mems,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function lpush(mixed $key, mixed $mem, mixed ...$mems): Relay|int|false {}

    /**
     * Insert all the specified values at the tail of the list stored at key.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  mixed  $mems,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function rpush(mixed $key, mixed $mem, mixed ...$mems): Relay|int|false {}

    /**
     * Inserts specified values at the head of the list stored at key,
     * only if key already exists and holds a list.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  mixed  $mems,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function lpushx(mixed $key, mixed $mem, mixed ...$mems): Relay|int|false {}

    /**
     * Inserts specified values at the tail of the list stored at key,
     * only if key already exists and holds a list.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  mixed  $mems,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function rpushx(mixed $key, mixed $mem, mixed ...$mems): Relay|int|false {}

    /**
     * Sets the list element at index to element.
     *
     * @param  mixed  $key
     * @param  int  $index
     * @param  mixed  $mem
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function lset(mixed $key, int $index, mixed $mem): Relay|bool {}

    /**
     * Removes and returns the first elements of the list stored at key.
     *
     * @param  mixed  $key
     * @param  int  $count
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function lpop(mixed $key, int $count = 1): mixed {}

    /**
     * The command returns the index of matching elements inside a Redis list.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @param  array  $options
     * @return Relay|int|array|false|null
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function lpos(mixed $key, mixed $value, ?array $options = null): Relay|int|array|false|null {}

    /**
     * Removes and returns the last elements of the list stored at key.
     *
     * @param  mixed  $key
     * @param  int  $count
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function rpop(mixed $key, int $count = 1): mixed {}

    /**
     * Atomically returns and removes the last element (tail) of the list stored at source,
     * and pushes the element at the first element (head) of the list stored at destination.
     *
     * @param  mixed  $source
     * @param  mixed  $dest
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function rpoplpush(mixed $source, mixed $dest): mixed {}

    /**
     * Atomically returns and removes the last element (tail) of the list stored at source,
     * and pushes the element at the first element (head) of the list stored at destination.
     * This command will block for an element up to the provided timeout.
     *
     * @param  mixed  $source
     * @param  mixed  $dest
     * @param  float  $timeout
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function brpoplpush(mixed $source, mixed $dest, float $timeout): mixed {}

    /**
     * BLPOP is a blocking list pop primitive. It is the blocking version of LPOP because
     * it blocks the connection when there are no elements to pop from any of the given lists.
     *
     * @param  string|array  $key
     * @param  string|float  $timeout_or_key
     * @param  array  $extra_args,...
     * @return Relay|array|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function blpop(string|array $key, string|float $timeout_or_key, mixed ...$extra_args): Relay|array|null|false {}

    /**
     * Pop elements from a list, or block until one is available
     *
     * @param  float  $timeout
     * @param  array  $keys
     * @param  string  $from
     * @param  int  $count
     * @return Relay|array|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function blmpop(float $timeout, array $keys, string $from, int $count = 1): Relay|array|null|false {}

    /**
     * Remove and return members with scores in a sorted set or block until one is available
     *
     * @param  float  $timeout
     * @param  array  $keys
     * @param  string  $from
     * @param  int  $count
     * @return Relay|array|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function bzmpop(float $timeout, array $keys, string $from, int $count = 1): Relay|array|null|false {}

    /**
     * Pops one or more elements from the first non-empty list key from the list of provided key names.
     *
     * @param  array  $keys
     * @param  string  $from
     * @param  int  $count
     * @return Relay|array|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function lmpop(array $keys, string $from, int $count = 1): Relay|array|null|false {}

    /**
     * Pops one or more elements, that are member-score pairs, from the
     * first non-empty sorted set in the provided list of key names.
     *
     * @param  array  $keys
     * @param  string  $from
     * @param  int  $count
     * @return Relay|array|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zmpop(array $keys, string $from, int $count = 1): Relay|array|null|false {}

    /**
     * BRPOP is a blocking list pop primitive. It is the blocking version of RPOP because
     * it blocks the connection when there are no elements to pop from any of the given lists.
     *
     * @param  string|array  $key
     * @param  string|float  $timeout_or_key
     * @param  array  $extra_args,...
     * @return Relay|array|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function brpop(string|array $key, string|float $timeout_or_key, mixed ...$extra_args): Relay|array|null|false {}

    /**
     * BZPOPMAX is the blocking variant of the sorted set ZPOPMAX primitive.
     *
     * @param  string|array  $key
     * @param  string|float  $timeout_or_key
     * @param  array  $extra_args,...
     * @return Relay|array|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function bzpopmax(string|array $key, string|float $timeout_or_key, mixed ...$extra_args): Relay|array|null|false {}

    /**
     * BZPOPMIN is the blocking variant of the sorted set ZPOPMIN primitive.
     *
     * @param  string|array  $key
     * @param  string|float  $timeout_or_key
     * @param  array  $extra_args,...
     * @return Relay|array|null|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function bzpopmin(string|array $key, string|float $timeout_or_key, mixed ...$extra_args): Relay|array|null|false {}

    /**
     * This is a container command for object introspection commands.
     *
     * @param  string  $op
     * @param  mixed  $key
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function object(string $op, mixed $key): mixed {}

    /**
     * Return the positions (longitude,latitude) of all the specified members
     * of the geospatial index represented by the sorted set at key.
     *
     * @param  mixed  $key
     * @param  mixed  $members,...
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function geopos(mixed $key, mixed ...$members): Relay|array|false {}

    /**
     * Removes the first count occurrences of elements equal to element from the list stored at key.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  int  $count
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function lrem(mixed $key, mixed $mem, int $count = 0): Relay|int|false {}

    /**
     * Returns the element at index index in the list stored at key.
     *
     * @param  mixed  $key
     * @param  int  $index
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function lindex(mixed $key, int $index): mixed {}

    /**
     * Inserts element in the list stored at key either before or after the reference value pivot.
     *
     * @param  mixed  $key
     * @param  string  $op
     * @param  mixed  $pivot
     * @param  mixed  $element
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function linsert(mixed $key, string $op, mixed $pivot, mixed $element): Relay|int|false {}

    /**
     * Trim an existing list so that it will contain only the specified range of elements specified.
     *
     * @param  mixed  $key
     * @param  int  $start
     * @param  int  $end
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function ltrim(mixed $key, int $start, int $end): Relay|bool {}

    /**
     * Returns the value associated with field in the hash stored at key.
     *
     * @param  mixed  $hash
     * @param  mixed  $member
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hget(mixed $hash, mixed $member): mixed {}

    /**
     * Returns the string length of the value associated with field in the hash stored at key.
     *
     * @param  mixed  $hash
     * @param  mixed  $member
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hstrlen(mixed $hash, mixed $member): Relay|int|false {}

    /**
     * Returns all fields and values of the hash stored at key.
     *
     * @param  mixed  $hash
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hgetall(mixed $hash): Relay|array|false {}

    /**
     * Returns all field names in the hash stored at key.
     *
     * @param  mixed  $hash
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hkeys(mixed $hash): Relay|array|false {}

    /**
     * Returns all values in the hash stored at key.
     *
     * @param  mixed  $hash
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hvals(mixed $hash): Relay|array|false {}

    /**
     * Returns the values associated with the specified fields in the hash stored at key.
     *
     * @param  mixed  $hash
     * @param  array  $members
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hmget(mixed $hash, array $members): Relay|array|false {}

    /**
     * When called with just the key argument, return a random field from the hash value stored at key.
     *
     * @param  mixed  $hash
     * @param  array  $options
     * @return Relay|array|string|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hrandfield(mixed $hash, ?array $options = null): Relay|array|string|false {}

    /**
     * Sets the specified fields to their respective values in the hash stored at key.
     *
     * @param  mixed  $hash
     * @param  array  $members
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function hmset(mixed $hash, array $members): Relay|bool {}

    /**
     * Returns if field is an existing field in the hash stored at key.
     *
     * @param  mixed  $hash
     * @param  mixed  $member
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hexists(mixed $hash, mixed $member): Relay|bool {}

    /**
     * Sets field in the hash stored at key to value, only if field does not yet exist.
     *
     * @param  mixed  $hash
     * @param  mixed  $member
     * @param  mixed  $value
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function hsetnx(mixed $hash, mixed $member, mixed $value): Relay|bool {}

    /**
     * Sets field in the hash stored at key to value.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  mixed  $val
     * @param  mixed  $kvals,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function hset(mixed $key, mixed $mem, mixed $val, mixed ...$kvals): Relay|int|false {}

    /**
     * Removes the specified fields from the hash stored at key.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  string  $mems,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function hdel(mixed $key, mixed $mem, string ...$mems): Relay|int|false {}

    /**
     * Increments the number stored at field in the hash stored at key by increment.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  int  $value
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function hincrby(mixed $key, mixed $mem, int $value): Relay|int|false {}

    /**
     * Increment the specified field of a hash stored at key, and representing
     * a floating point number, by the specified increment.
     *
     * @param  mixed  $key
     * @param  mixed  $mem
     * @param  float  $value
     * @return Relay|float|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function hincrbyfloat(mixed $key, mixed $mem, float $value): Relay|float|bool {}

    /**
     * Increments the number stored at key by one.
     *
     * @param  mixed  $key
     * @param  int  $by
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function incr(mixed $key, int $by = 1): Relay|int|false {}

    /**
     * Decrements the number stored at key by one.
     *
     * @param  mixed  $key
     * @param  int  $by
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function decr(mixed $key, int $by = 1): Relay|int|false {}

    /**
     * Increments the number stored at key by increment.
     *
     * @param  mixed  $key
     * @param  int  $value
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function incrby(mixed $key, int $value): Relay|int|false {}

    /**
     * Decrements the number stored at key by decrement.
     *
     * @param  mixed  $key
     * @param  int  $value
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function decrby(mixed $key, int $value): Relay|int|false {}

    /**
     * Increment the string representing a floating point number stored at key by the specified increment.
     *
     * @param  mixed  $key
     * @param  float  $value
     * @return Relay|float|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function incrbyfloat(mixed $key, float $value): Relay|float|false {}

    /**
     * Returns the members of the set resulting from the difference between the first set and all the successive sets.
     *
     * @param  mixed  $key
     * @param  mixed  $other_keys,...
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function sdiff(mixed $key, mixed ...$other_keys): Relay|array|false {}

    /**
     * This command is equal to SDIFF, but instead of returning the resulting set, it is stored in destination.
     * If destination already exists, it is overwritten.
     *
     * @param  mixed  $key
     * @param  mixed  $other_keys,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function sdiffstore(mixed $key, mixed ...$other_keys): Relay|int|false {}

    /**
     * Returns the members of the set resulting from the intersection of all the given sets.
     *
     * @param  mixed  $key
     * @param  mixed  $other_keys,...
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function sinter(mixed $key, mixed ...$other_keys): Relay|array|false {}

    /**
     * Intersect multiple sets and return the cardinality of the result.
     *
     * @param  array  $keys
     * @param  int  $limit
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function sintercard(array $keys, int $limit = -1): Relay|int|false {}

    /**
     * This command is equal to SINTER, but instead of returning the resulting set, it is stored in destination.
     * If destination already exists, it is overwritten.
     *
     * @param  mixed  $key
     * @param  mixed  $other_keys,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function sinterstore(mixed $key, mixed ...$other_keys): Relay|int|false {}

    /**
     * Returns the members of the set resulting from the union of all the given sets.
     *
     * @param  mixed  $key
     * @param  mixed  $other_keys,...
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function sunion(mixed $key, mixed ...$other_keys): Relay|array|false {}

    /**
     * This command is equal to SUNION, but instead of returning the resulting set, it is stored in destination.
     * If destination already exists, it is overwritten.
     *
     * @param  mixed  $key
     * @param  mixed  $other_keys,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function sunionstore(mixed $key, mixed ...$other_keys): Relay|int|false {}

    /**
     * Alters the last access time of a key(s).
     *
     * @param  array|string  $key_or_array
     * @param  mixed  $more_keys,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function touch(array|string $key_or_array, mixed ...$more_keys): Relay|int|false {}

    /**
     * A pipeline block is simply transmitted faster to the server (like `MULTI`), but without any guarantee of atomicity.
     *
     * @return Relay|bool
     */
    #[\Relay\Attributes\Local]
    public function pipeline(): Relay|bool {}

    /**
     * Marks the start of a transaction block. Subsequent commands will be queued for atomic execution using EXEC.
     *
     * Accepts `Relay::MULTI` and `Relay::PIPELINE` modes.
     *
     * @param  int  $mode
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function multi(int $mode = 0): Relay|bool {}

    /**
     * Executes all previously queued commands in a transaction and restores the connection state to normal.
     *
     * @return Relay|array|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function exec(): Relay|array|bool {}

    /**
     * Wait for the synchronous replication of all the write
     * commands sent in the context of the current connection.
     *
     * @param  int  $replicas
     * @param  int  $timeout
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function wait(int $replicas, $timeout): Relay|int|false {}

    /**
     * Marks the given keys to be watched for conditional execution of a transaction.
     *
     * @param  mixed  $key
     * @param  mixed  $other_keys,...
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function watch(mixed $key, mixed ...$other_keys): Relay|bool {}

    /**
     * Flushes all the previously watched keys for a transaction.
     * If you call EXEC or DISCARD, there's no need to manually call UNWATCH.
     *
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function unwatch(): Relay|bool {}

    /**
     * Flushes all previously queued commands in a transaction and restores the connection state to normal.
     * If WATCH was used, DISCARD unwatches all keys watched by the connection.
     *
     * @return bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function discard(): bool {}

    /**
     * Get the mode Relay is currently in.
     * `Relay::ATOMIC`, `Relay::PIPELINE` or `Relay::MULTI`.
     *
     * @param  bool  $masked
     * @return int
     */
    #[\Relay\Attributes\Local]
    public function getMode(bool $masked = false): int {}

    /**
     * Clear the accumulated sent and received bytes.
     *
     * @return void
     */
    #[\Relay\Attributes\Local]
    public function clearBytes(): void {}

    /**
     * Scan the keyspace for matching keys.
     *
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @param  string|null  $type
     * @return array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function scan(mixed &$iterator, mixed $match = null, int $count = 0, ?string $type = null): array|false {}

    /**
     * Iterates fields of Hash types and their associated values.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function hscan(mixed $key, mixed &$iterator, mixed $match = null, int $count = 0): array|false {}

    /**
     * Iterates elements of Sets types.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function sscan(mixed $key, mixed &$iterator, mixed $match = null, int $count = 0): array|false {}

    /**
     * Iterates elements of Sorted Set types and their associated scores.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zscan(mixed $key, mixed &$iterator, mixed $match = null, int $count = 0): array|false {}

    /**
     * Returns all keys matching pattern.
     *
     * @param  mixed  $pattern
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function keys(mixed $pattern): Relay|array|false {}

    /**
     * Interact with the Redis slowlog.
     *
     * @param  string  $operation
     * @param  string  $extra_args,...
     * @return Relay|array|int|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function slowlog(string $operation, string ...$extra_args): Relay|array|int|bool {}

    /**
     * Returns all the members of the set value stored at `$key`.
     *
     * @param  mixed  $set
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function smembers(mixed $set): Relay|array|false {}

    /**
     * Returns if `$member` is a member of the set stored at `$key`.
     *
     * @param  mixed  $set
     * @param  mixed  $member
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function sismember(mixed $set, mixed $member): Relay|bool {}

    /**
     * Returns whether each member is a member of the set stored at `$key`.
     *
     * @param  mixed  $set
     * @param  mixed  $members,...
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function smismember(mixed $set, mixed ...$members): Relay|array|false {}

    /**
     * Remove the specified members from the set stored at `$key`.
     *
     * @param  mixed  $set
     * @param  mixed  $member
     * @param  mixed  $members,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function srem(mixed $set, mixed $member, mixed ...$members): Relay|int|false {}

    /**
     * Add the specified members to the set stored at `$key`.
     *
     * @param  mixed  $set
     * @param  mixed  $member
     * @param  mixed  $members,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function sadd(mixed $set, mixed $member, mixed ...$members): Relay|int|false {}

    /**
     * Sort the elements in a list, set or sorted set.
     *
     * @param  mixed  $key
     * @param  array  $options
     * @return Relay|array|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function sort(mixed $key, array $options = []): Relay|array|int|false {}

    /**
     * Sort the elements in a list, set or sorted set. Read-only variant of SORT.
     *
     * @param  mixed  $key
     * @param  array  $options
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function sort_ro(mixed $key, array $options = []): Relay|array|false {}

    /**
     * Move member from the set at source to the set at destination.
     *
     * @param  mixed  $srcset
     * @param  mixed  $dstset
     * @param  mixed  $member
     * @return Relay|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function smove(mixed $srcset, mixed $dstset, mixed $member): Relay|bool {}

    /**
     * Removes and returns one or more random members from the set value store at `$key`.
     *
     * @param  mixed  $set
     * @param  int  $count
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function spop(mixed $set, int $count = 1): mixed {}

    /**
     * Returns one or multiple random members from a set.
     *
     * @param  mixed  $set
     * @param  int  $count
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function srandmember(mixed $set, int $count = 1): mixed {}

    /**
     * Returns the set cardinality (number of elements) of the set stored at `$key`.
     *
     * @param  mixed  $key
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function scard(mixed $key): Relay|int|false {}

    /**
     * Execute a script management command.
     *
     * @param  string  $command
     * @param  string  $args,...
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function script(string $command, string ...$args): mixed {}

    /**
     * Returns the length of the string value stored at `$key`.
     *
     * @param  mixed  $key
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function strlen(mixed $key): Relay|int|false {}

    /**
     * Returns the number of fields contained in the hash stored at `$key`.
     *
     * @param  mixed  $key
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function hlen(mixed $key): Relay|int|false {}

    /**
     * Returns the length of the list stored at `$key`.
     *
     * @param  mixed  $key
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function llen(mixed $key): Relay|int|false {}

    /**
     * Acknowledge one or more IDs as having been processed by the consumer group.
     *
     * @param  mixed  $key
     * @param  string  $group
     * @param  array  $ids
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function xack(mixed $key, string $group, array $ids): Relay|int|false {}

    /**
     * Append a message to a stream.
     *
     * @param  string  $key
     * @param  string  $id
     * @param  int  $maxlen
     * @param  bool  $approx
     * @param  bool  $nomkstream
     * @return Relay|string|false
     */
    public function xadd(
        string $key,
        string $id,
        array $values,
        int $maxlen = 0,
        bool $approx = false,
        bool $nomkstream = false
    ): Relay|string|false {}

    /**
     * Claim ownership of stream message(s).
     *
     * @param  string  $key
     * @param  string  $group
     * @param  string  $consumer
     * @param  int  $min_idle
     * @param  array  $ids
     * @param  array  $options
     * @return Relay|array|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function xclaim(
        string $key,
        string $group,
        string $consumer,
        int $min_idle,
        array $ids,
        array $options
    ): Relay|array|bool {}

    /**
     * Automatically take ownership of stream message(s) by metrics
     *
     * @param  string  $key
     * @param  string  $group
     * @param  string  $consumer
     * @param  int  $min_idle
     * @param  string  $start
     * @param  int  $count
     * @param  bool  $justid
     * @return Relay|array|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function xautoclaim(
        string $key,
        string $group,
        string $consumer,
        int $min_idle,
        string $start,
        int $count = -1,
        bool $justid = false
    ): Relay|bool|array {}

    /**
     * Get the length of a stream.
     *
     * @param  string  $key
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function xlen(string $key): Relay|int|false {}

    /**
     * Perform utility operations having to do with consumer groups
     *
     * @param  string  $operation
     * @param  mixed  $key
     * @param  string  $group
     * @param  string  $id_or_consumer
     * @param  bool  $mkstream
     * @param  int  $entries_read
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function xgroup(
        string $operation,
        mixed $key = null,
        string $group = null,
        string $id_or_consumer = null,
        bool $mkstream = false,
        int $entries_read = -2
    ): mixed {}

    /**
     * Remove one or more specific IDs from a stream.
     *
     * @param  string  $key
     * @param  array  $ids
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function xdel(string $key, array $ids): Relay|int|false {}

    /**
     * Retrieve information about a stream key.
     *
     * @param  string  $operation
     * @param  string|null  $arg1
     * @param  string|null  $arg2
     * @param  int  $count
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function xinfo(string $operation, ?string $arg1 = null, ?string $arg2 = null, int $count = -1): mixed {}

    /**
     * Query pending entries in a stream.
     *
     * @param  string  $key
     * @param  string  $group
     * @param  string|null  $start
     * @param  string|null  $end
     * @param  int  $count
     * @param  string|null  $consumer
     * @param  int  $idle
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function xpending(
        string $key,
        string $group,
        ?string $start = null,
        ?string $end = null,
        int $count = -1,
        ?string $consumer = null,
        int $idle = 0
    ): Relay|array|false {}

    /**
     * Lists elements in a stream.
     *
     * @param  mixed  $key
     * @param  string  $start
     * @param  string  $end
     * @param  int  $count = -1
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function xrange(mixed $key, string $start, string $end, int $count = -1): Relay|array|false {}

    /**
     * Get a range of entries from a STREAM ke in reverse chronological order.
     *
     * @param  string  $key
     * @param  string  $end
     * @param  string  $start
     * @param  int  $count
     * @return Relay|array|bool
     */
    #[\Relay\Attributes\RedisCommand]
    public function xrevrange(string $key, string $end, string $start, int $count = -1): Relay|array|bool {}

    /**
     * Read messages from a stream.
     *
     * @param  array  $streams
     * @param  int  $count
     * @param  int  $block
     * @return Relay|array|bool|null
     */
    #[\Relay\Attributes\RedisCommand]
    public function xread(array $streams, int $count = -1, int $block = -1): Relay|array|bool|null {}

    /**
     * Read messages from a stream using a consumer group.
     *
     * @param  string  $group
     * @param  string  $consumer
     * @param  array  $streams
     * @param  int  $count
     * @param  int  $block
     * @return Relay|array|bool|null
     */
    #[\Relay\Attributes\RedisCommand]
    public function xreadgroup(
        string $group,
        string $consumer,
        array $streams,
        int $count = 1,
        int $block = 1
    ): Relay|array|bool|null {}

    /**
     * Truncate a STREAM key in various ways.
     *
     * @param  string  $key
     * @param  string  $threshold
     * @param  bool  $approx
     * @param  bool  $minid
     * @param  int  $limit
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function xtrim(
        string $key,
        string $threshold,
        bool $approx = false,
        bool $minid = false,
        int $limit = -1
    ): Relay|int|false {}

    /**
     * Adds all the specified members with the specified scores to the sorted set stored at key.
     *
     * @param  mixed  $key
     * @param  mixed  $args,...
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function zadd(mixed $key, mixed ...$args): mixed {}

    /**
     * When called with just the key argument, return a random element from the sorted set value stored at key.
     * If the provided count argument is positive, return an array of distinct elements.
     *
     * @param  mixed  $key
     * @param  array|null  $options
     * @return mixed
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrandmember(mixed $key, ?array $options = null): mixed {}

    /**
     * Returns the specified range of elements in the sorted set stored at key.
     *
     * @param  mixed  $key
     * @param  string  $start
     * @param  string  $end
     * @param  mixed  $options
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrange(mixed $key, string $start, string $end, mixed $options = null): Relay|array|false {}

    /**
     * Returns the specified range of elements in the sorted set stored at key.
     *
     * @param  mixed  $key
     * @param  int  $start
     * @param  int  $end
     * @param  mixed  $options
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrevrange(mixed $key, int $start, int $end, mixed $options = null): Relay|array|false {}

    /**
     * Returns all the elements in the sorted set at key with a score between
     * min and max (including elements with score equal to min or max).
     *
     * @param  mixed  $key
     * @param  mixed  $start
     * @param  mixed  $end
     * @param  mixed  $options
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrangebyscore(mixed $key, mixed $start, mixed $end, mixed $options = null): Relay|array|false {}

    /**
     * Returns all the elements in the sorted set at key with a score between
     * max and min (including elements with score equal to max or min).
     *
     * @param  mixed  $key
     * @param  mixed  $start
     * @param  mixed  $end
     * @param  mixed  $options
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrevrangebyscore(mixed $key, mixed $start, mixed $end, mixed $options = null): Relay|array|false {}

    /**
     * Returns all the elements in the sorted set at key with a score between
     * max and min (including elements with score equal to max or min).
     *
     * @param  mixed  $dst
     * @param  mixed  $src
     * @param  mixed  $start
     * @param  mixed  $end
     * @param  mixed  $options
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrangestore(mixed $dst, mixed $src, mixed $start, mixed $end, mixed $options = null): Relay|int|false {}

    /**
     * When all the elements in a sorted set are inserted with the same score,
     * in order to force lexicographical ordering, this command returns all
     * the elements in the sorted set at key with a value between min and max.
     *
     * @param  mixed  $key
     * @param  mixed  $min
     * @param  mixed  $max
     * @param  int  $offset
     * @param  int  $count
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrangebylex(mixed $key, mixed $min, mixed $max, int $offset = -1, int $count = -1): Relay|array|false {}

    /**
     * When all the elements in a sorted set are inserted with the same score,
     * in order to force lexicographical ordering, this command returns all
     * the elements in the sorted set at key with a value between max and min.
     *
     * @param  mixed  $key
     * @param  mixed  $max
     * @param  mixed  $min
     * @param  int  $offset
     * @param  int  $count
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrevrangebylex(mixed $key, mixed $max, mixed $min, int $offset = -1, int $count = -1): Relay|array|false {}

    /**
     * Returns the rank of member in the sorted set stored at key, with the scores
     * ordered from low to high. The rank (or index) is 0-based, which means
     * that the member with the lowest score has rank 0.
     *
     * @param  mixed  $key
     * @param  mixed  $rank
     * @param  bool  $withscore
     * @return Relay|array|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrank(mixed $key, mixed $rank, bool $withscore = false): Relay|array|int|false {}

    /**
     * Returns the rank of member in the sorted set stored at key, with the scores
     * ordered from high to low. The rank (or index) is 0-based, which means
     * that the member with the highest score has rank 0.
     *
     * @param  mixed  $key
     * @param  mixed  $rank
     * @param  bool  $withscore
     * @return Relay|array|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrevrank(mixed $key, mixed $rank, bool $withscore = false): Relay|array|int|false {}

    /**
     * Removes the specified members from the sorted set stored at key.
     * Non existing members are ignored.
     *
     * @param  mixed  $key
     * @param  mixed  $args,...
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zrem(mixed $key, mixed ...$args): Relay|int|false {}

    /**
     * When all the elements in a sorted set are inserted with the same score,
     * in order to force lexicographical ordering, this command removes all
     * elements in the sorted set stored at key between the
     * lexicographical range specified by min and max.
     *
     * @param  mixed  $key
     * @param  mixed  $min
     * @param  mixed  $max
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zremrangebylex(mixed $key, mixed $min, mixed $max): Relay|int|false {}

    /**
     * Removes all elements in the sorted set stored at key with rank between
     * start and stop. Both start and stop are 0 -based indexes with 0 being
     * the element with the lowest score.
     *
     * @param  mixed  $key
     * @param  int  $start
     * @param  int  $end
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zremrangebyrank(mixed $key, int $start, int $end): Relay|int|false {}

    /**
     * Removes all elements in the sorted set stored at key with
     * a score between min and max (inclusive).
     *
     * @param  mixed  $key
     * @param  mixed  $min
     * @param  mixed  $max
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zremrangebyscore(mixed $key, mixed $min, mixed $max): Relay|int|false {}

    /**
     * Returns the sorted set cardinality (number of elements) of the sorted set stored at key.
     *
     * @param  mixed  $key
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand, \Relay\Attributes\Cached]
    public function zcard(mixed $key): Relay|int|false {}

    /**
     * Returns the number of elements in the sorted set at key with a score between min and max.
     *
     * @param  mixed  $key
     * @param  mixed  $min
     * @param  mixed  $max
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zcount(mixed $key, mixed $min, mixed $max): Relay|int|false {}

    /**
     * This command is similar to ZDIFFSTORE, but instead of storing the
     * resulting sorted set, it is returned to the client.
     *
     * @param  array  $keys
     * @param  array  $options
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zdiff(array $keys, ?array $options = null): Relay|array|false {}

    /**
     * Computes the difference between the first and all successive
     * input sorted sets and stores the result in destination.
     *
     * @param  mixed  $dst
     * @param  array  $keys
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zdiffstore(mixed $dst, array $keys): Relay|int|false {}

    /**
     * Increments the score of member in the sorted set stored at key by increment.
     *
     * @param  mixed  $key
     * @param  float  $score
     * @param  mixed  $mem
     * @return Relay|float|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zincrby(mixed $key, float $score, mixed $mem): Relay|float|false {}

    /**
     * When all the elements in a sorted set are inserted with the same score,
     * in order to force lexicographical ordering, this command returns the
     * number of elements in the sorted set at key with a value between min and max.
     *
     * @param  mixed  $key
     * @param  mixed  $min
     * @param  mixed  $max
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zlexcount(mixed $key, mixed $min, mixed $max): Relay|int|false {}

    /**
     * Returns the scores associated with the specified members in the sorted set stored at key.
     *
     * @param  mixed  $key
     * @param  mixed  $mems,...
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zmscore(mixed $key, mixed ...$mems): Relay|array|false {}

    /**
     * Returns the score of member in the sorted set at key.
     *
     * @param  mixed  $key
     * @param  mixed  $member
     * @return Relay|float|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zscore(mixed $key, mixed $member): Relay|float|false {}

    /**
     * This command is similar to ZINTERSTORE, but instead of storing
     * the resulting sorted set, it is returned to the client.
     *
     * @param  array  $keys
     * @param  array  $weights
     * @param  mixed  $options
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zinter(array $keys, ?array $weights = null, mixed $options = null): Relay|array|false {}

    /**
     * Intersect multiple sorted sets and return the cardinality of the result.
     *
     * @param  array  $keys
     * @param  int  $limit
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zintercard(array $keys, int $limit = -1): Relay|int|false {}

    /**
     * Computes the intersection of numkeys sorted sets given by the
     * specified keys, and stores the result in destination.
     *
     * @param  mixed  $dst
     * @param  array  $keys
     * @param  array  $weights
     * @param  mixed  $options
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zinterstore(mixed $dst, array $keys, ?array $weights = null, mixed $options = null): Relay|int|false {}

    /**
     * This command is similar to ZUNIONSTORE, but instead of storing
     * the resulting sorted set, it is returned to the client.
     *
     * @param  array  $keys
     * @param  array  $weights
     * @param  mixed  $options
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zunion(array $keys, ?array $weights = null, mixed $options = null): Relay|array|false {}

    /**
     * Computes the union of numkeys sorted sets given by the
     * specified keys, and stores the result in destination.
     *
     * @param  mixed  $dst
     * @param  array  $keys
     * @param  array  $weights
     * @param  mixed  $options
     * @return Relay|int|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zunionstore(mixed $dst, array $keys, ?array $weights = null, mixed $options = null): Relay|int|false {}

    /**
     * Removes and returns up to count members with the lowest
     * scores in the sorted set stored at key.
     *
     * @param  mixed  $key
     * @param  int  $count
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zpopmin(mixed $key, int $count = 1): Relay|array|false {}

    /**
     * Removes and returns up to count members with the highest
     * scores in the sorted set stored at key.
     *
     * @param  mixed  $key
     * @param  int  $count
     * @return Relay|array|false
     */
    #[\Relay\Attributes\RedisCommand]
    public function zpopmax(mixed $key, int $count = 1): Relay|array|false {}

    /**
     * Returns keys cached in runtime memory.
     *
     * @internal Temporary debug helper. Do not use.
     * @return mixed
     */
    #[\Relay\Attributes\Local]
    public function _getKeys() {}
}
