<?php

use JetBrains\PhpStorm\Deprecated;

/**
 * Helper autocomplete for php redis extension
 *
 * @link https://github.com/phpredis/phpredis/blob/develop/redis.stub.php
 */
class Redis
{
    public const AFTER = 'after';
    public const BEFORE = 'before';
    public const LEFT = 'left';
    public const RIGHT = 'right';

    /**
     * Options
     */
    public const OPT_SERIALIZER = 1;
    public const OPT_PREFIX = 2;
    public const OPT_READ_TIMEOUT = 3;
    public const OPT_SCAN = 4;
    public const OPT_FAILOVER = 5;
    public const OPT_TCP_KEEPALIVE = 6;
    public const OPT_COMPRESSION = 7;
    public const OPT_REPLY_LITERAL = 8;
    public const OPT_COMPRESSION_LEVEL = 9;
    public const OPT_NULL_MULTIBULK_AS_NULL = 10;
    public const OPT_MAX_RETRIES = 11;
    public const OPT_BACKOFF_ALGORITHM = 12;
    public const OPT_BACKOFF_BASE = 13;
    public const OPT_BACKOFF_CAP = 14;

    /**
     * Cluster options
     */
    public const FAILOVER_NONE = 0;
    public const FAILOVER_ERROR = 1;
    public const FAILOVER_DISTRIBUTE = 2;
    public const FAILOVER_DISTRIBUTE_SLAVES = 3;

    /**
     * SCAN options
     */
    public const SCAN_NORETRY = 0;
    public const SCAN_RETRY = 1;

    /**
     * @since 5.3.0
     */
    public const SCAN_PREFIX = 2;

    /**
     * @since 5.3.0
     */
    public const SCAN_NOPREFIX = 3;

    /**
     * Serializers
     */
    public const SERIALIZER_NONE = 0;
    public const SERIALIZER_PHP = 1;
    public const SERIALIZER_IGBINARY = 2;
    public const SERIALIZER_MSGPACK = 3;
    public const SERIALIZER_JSON = 4;

    /**
     * Compressions
     */
    public const COMPRESSION_NONE = 0;
    public const COMPRESSION_LZF = 1;
    public const COMPRESSION_ZSTD = 2;
    public const COMPRESSION_LZ4 = 3;

    /**
     * Compression ZSTD levels
     */
    public const COMPRESSION_ZSTD_MIN = 1;
    public const COMPRESSION_ZSTD_DEFAULT = 3;
    public const COMPRESSION_ZSTD_MAX = 22;

    /**
     * Multi
     */
    public const ATOMIC = 0;
    public const MULTI = 1;
    public const PIPELINE = 2;

    /**
     * Types
     */
    public const REDIS_NOT_FOUND = 0;
    public const REDIS_STRING = 1;
    public const REDIS_SET = 2;
    public const REDIS_LIST = 3;
    public const REDIS_ZSET = 4;
    public const REDIS_HASH = 5;
    public const REDIS_STREAM = 6;

    /**
     * Backoff algorithms
     * @since 5.3.5
     */
    public const BACKOFF_ALGORITHM_DEFAULT = 0;
    public const BACKOFF_ALGORITHM_DECORRELATED_JITTER = 1;
    public const BACKOFF_ALGORITHM_FULL_JITTER = 2;
    public const BACKOFF_ALGORITHM_EQUAL_JITTER = 3;
    public const BACKOFF_ALGORITHM_EXPONENTIAL = 4;
    public const BACKOFF_ALGORITHM_UNIFORM = 5;
    public const BACKOFF_ALGORITHM_CONSTANT = 6;

    /**
     * Creates a Redis client
     *
     * @param array|null $options configuration options
     *
     * @example
     *
     * $redis = new Redis();
     *
     * // Starting from version 6.0.0 it's possible to specify configuration options.
     * // This allows to connect to the server without explicitly invoking connect command.
     * $redis = new Redis([
     *     // If you do wish to connect via the constructor, only 'host' is strictly required,
     *     // which will cause PhpRedis to connect to that host on Redis' default port (6379).
     *     'host' => '127.0.0.1',
     *     'port' => 6379,
     *     'readTimeout' => 2.5,
     *     'connectTimeout' => 2.5,
     *     'persistent' => true,
     *     // Valid formats: NULL, ['user', 'pass'], 'pass', or ['pass']
     *     'auth' => ['phpredis', 'phpredis'],
     *     // See PHP stream options for valid SSL configuration settings.
     *     'ssl' => ['verify_peer' => false],
     *     // How quickly to retry a connection after we time out or it  closes.
     *     // Note that this setting is overridden by 'backoff' strategies.
     *     'retryInterval' => 100,
     *     // Which backoff algorithm to use. 'decorrelated jitter' is likely the
     *     // bestone for most solution, but there are many to choose from
     *     'backoff' => [
     *         'algorithm' => Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
     *         // 'base', and 'cap' are in milliseconds and represent the first delay redis will
     *         // use when reconnecting, and the maximum delay we will reach while retrying.
     *         'base' => 500,
     *         'cap' => 750,
     *     ],
     * ]);
     */
    public function __construct($options = null) {}

    /**
     * Connects to a Redis instance.
     *
     * @param string $host           can be a host, or the path to a unix domain socket
     * @param int    $port           optional
     * @param float  $timeout        value in seconds (optional, default is 0.0 meaning unlimited)
     * @param string|null $persistent_id  identity for the requested persistent connection
     * @param int    $retry_interval retry interval in milliseconds.
     * @param float  $read_timeout   value in seconds (optional, default is 0 meaning unlimited)
     * @param array|null $context    since PhpRedis >= 5.3.0 can specify authentication and stream information on connect
     *
     * @return bool TRUE on success, FALSE on error
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * $redis->connect('127.0.0.1', 6379);
     * $redis->connect('127.0.0.1');            // port 6379 by default
     * $redis->connect('127.0.0.1', 6379, 2.5); // 2.5 sec timeout.
     * $redis->connect('/tmp/redis.sock');      // unix domain socket.
     * // since PhpRedis >= 5.3.0 can specify authentication and stream information on connect
     * $redis->connect('127.0.0.1', 6379, 1, NULL, 0, 0, ['auth' => ['phpredis', 'phpredis']]);
     * </pre>
     */
    public function connect(
        $host,
        $port = 6379,
        $timeout = 0,
        $persistent_id = null,
        $retry_interval = 0,
        $read_timeout = 0,
        $context = null
    ) {}

    /**
     * Connects to a Redis instance.
     *
     * @param string $host           can be a host, or the path to a unix domain socket
     * @param int    $port           optional
     * @param float  $timeout        value in seconds (optional, default is 0.0 meaning unlimited)
     * @param string $persistent_id  identity for the requested persistent connection
     * @param int    $retry_interval retry interval in milliseconds.
     * @param float  $read_timeout   value in seconds (optional, default is 0 meaning unlimited)
     * @param array  $context        since PhpRedis >= 5.3.0 can specify authentication and stream information on connect
     *
     * @return bool TRUE on success, FALSE on error
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->connect(%parametersList%)')]
    public function open(
        $host,
        $port = 6379,
        $timeout = 0,
        $persistent_id = null,
        $retry_interval = 0,
        $read_timeout = 0,
        $context = null
    ) {}

    /**
     * A method to determine if a phpredis object thinks it's connected to a server
     *
     * @return bool Returns TRUE if phpredis thinks it's connected and FALSE if not
     *
     * @throws RedisException
     */
    public function isConnected() {}

    /**
     * Retrieve our host or unix socket that we're connected to
     *
     * @return string|false The host or unix socket we're connected to or FALSE if we're not connected
     *
     * @throws RedisException
     */
    public function getHost() {}

    /**
     * Get the port we're connected to
     *
     * This number will be zero if we are connected to a unix socket.
     *
     * @return int|false Returns the port we're connected to or FALSE if we're not connected
     *
     * @throws RedisException
     */
    public function getPort() {}

    /**
     * Get the database number phpredis is pointed to
     *
     * @return int|bool Returns the database number (int) phpredis thinks it's pointing to
     * or FALSE if we're not connected
     *
     * @throws RedisException
     */
    public function getDbNum() {}

    /**
     * Get the (write) timeout in use for phpredis
     *
     * @return float|false The timeout (DOUBLE) specified in our connect call or FALSE if we're not connected
     *
     * @throws RedisException
     */
    public function getTimeout() {}

    /**
     * Get the read timeout specified to phpredis or FALSE if we're not connected
     *
     * @return float|bool Returns the read timeout (which can be set using setOption and Redis::OPT_READ_TIMEOUT)
     * or FALSE if we're not connected
     *
     * @throws RedisException
     */
    public function getReadTimeout() {}

    /**
     * Gets the persistent ID that phpredis is using
     *
     * @return string|null|bool Returns the persistent id phpredis is using
     * (which will only be set if connected with pconnect),
     * NULL if we're not using a persistent ID,
     * and FALSE if we're not connected
     *
     * @throws RedisException
     */
    public function getPersistentID() {}

    /**
     * Get the authentication information on the connection, if any.
     *
     * @return mixed The authentication information used to authenticate the connection.
     *
     * @throws RedisException
     */
    public function getAuth() {}

    /**
     * Connects to a Redis instance or reuse a connection already established with pconnect/popen.
     *
     * The connection will not be closed on close or end of request until the php process ends.
     * So be patient on to many open FD's (specially on redis server side) when using persistent connections on
     * many servers connecting to one redis server.
     *
     * Also more than one persistent connection can be made identified by either host + port + timeout
     * or host + persistentId or unix socket + timeout.
     *
     * This feature is not available in threaded versions. pconnect and popen then working like their non persistent
     * equivalents.
     *
     * @param string $host           can be a host, or the path to a unix domain socket
     * @param int    $port           optional
     * @param float  $timeout        value in seconds (optional, default is 0.0 meaning unlimited)
     * @param string|null $persistent_id  identity for the requested persistent connection
     * @param int    $retry_interval retry interval in milliseconds.
     * @param float  $read_timeout   value in seconds (optional, default is 0 meaning unlimited)
     * @param array|null $context    since PhpRedis >= 5.3.0 can specify authentication and stream information on connect
     *
     * @return bool TRUE on success, FALSE on error.
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * $redis->pconnect('127.0.0.1', 6379);
     *
     * // port 6379 by default - same connection like before
     * $redis->pconnect('127.0.0.1');
     *
     * // 2.5 sec timeout and would be another connection than the two before.
     * $redis->pconnect('127.0.0.1', 6379, 2.5);
     *
     * // x is sent as persistent_id and would be another connection than the three before.
     * $redis->pconnect('127.0.0.1', 6379, 2.5, 'x');
     *
     * // unix domain socket - would be another connection than the four before.
     * $redis->pconnect('/tmp/redis.sock');
     * </pre>
     */
    public function pconnect(
        $host,
        $port = 6379,
        $timeout = 0,
        $persistent_id = null,
        $retry_interval = 0,
        $read_timeout = 0,
        $context = null
    ) {}

    /**
     * @param string $host
     * @param int    $port
     * @param float  $timeout
     * @param string|null $persistent_id
     * @param int    $retry_interval
     * @param float  $read_timeout
     * @param array|null $context
     *
     * @return bool
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->pconnect(%parametersList%)')]
    public function popen(
        $host,
        $port = 6379,
        $timeout = 0,
        $persistent_id = null,
        $retry_interval = 0,
        $read_timeout = 0,
        $context = null
    ) {}

    /**
     * Disconnects from the Redis instance.
     *
     * Note: Closing a persistent connection requires PhpRedis >= 4.2.0
     *
     * @since >= 4.2 Closing a persistent connection requires PhpRedis
     *
     * @return bool TRUE on success, FALSE on error
     *
     * @throws RedisException
     */
    public function close() {}

    /**
     * Swap one Redis database with another atomically
     *
     * Note: Requires Redis >= 4.0.0
     *
     * @param int $db1
     * @param int $db2
     *
     * @return bool TRUE on success and FALSE on failure
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/swapdb
     * @since >= 4.0
     * @example
     * <pre>
     * // Swaps DB 0 with DB 1 atomically
     * $redis->swapdb(0, 1);
     * </pre>
     */
    public function swapdb(int $db1, int $db2) {}

    /**
     * Set a configurable option on the Redis object.
     *
     * @param int   $option The option constant.
     * @param mixed $value  The option value.
     *
     * @return bool TRUE on success, FALSE on error
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);        // don't serialize data
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);         // use built-in serialize/unserialize
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);    // use igBinary serialize/unserialize
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_MSGPACK);     // Use msgpack serialize/unserialize
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);        // Use json serialize/unserialize
     *
     * $redis->setOption(Redis::OPT_PREFIX, 'myAppName:');                      // use custom prefix on all keys
     *
     * // Options for the SCAN family of commands, indicating whether to abstract
     * // empty results from the user.  If set to SCAN_NORETRY (the default), phpredis
     * // will just issue one SCAN command at a time, sometimes returning an empty
     * // array of results.  If set to SCAN_RETRY, phpredis will retry the scan command
     * // until keys come back OR Redis returns an iterator of zero
     * $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_NORETRY);
     * $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
     * </pre>
     */
    public function setOption($option, $value) {}

    /**
     * Retrieve the value of a configuration setting as set by Redis::setOption()
     *
     * @param int $option parameter name
     *
     * @return mixed|null The setting itself or false on failure.
     *
     * @throws RedisException
     *
     * @see setOption()
     * @example
     * // return option value
     * $redis->getOption(Redis::OPT_SERIALIZER);
     */
    public function getOption($option) {}

    /**
     * Check the current connection status
     *
     * @param string $message An optional string message that Redis will reply with, if passed.
     *
     * @return bool|string TRUE if the command is successful or returns message
     * Throws a RedisException object on connectivity error, as described above.
     * @throws RedisException
     * @link    https://redis.io/commands/ping
     *
     * @example
     * // When called without an argument, PING returns `TRUE`
     * $redis->ping();
     *
     * // If passed an argument, that argument is returned. Here 'hello' will be returned
     * $redis->ping('hello');
     */
    public function ping($message = null) {}

    /**
     * Sends a string to Redis, which replies with the same string
     *
     * @param string $message
     *
     * @return string|Redis The string sent to Redis or false on failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/echo
     *
     * @example $redis->echo('Hello, World');
     */
    public function echo($message) {}

    /**
     * Get the value related to the specified key
     *
     * @param string $key
     *
     * @return string|mixed|false|Redis If key didn't exist, FALSE is returned or Redis if in multimode
     * Otherwise, the value related to this key is returned
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/get
     * @example
     * <pre>
     * $redis->set('key', 'hello');
     * $redis->get('key');
     *
     * // set and get with serializer
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
     *
     * $redis->set('key', ['asd' => 'as', 'dd' => 123, 'b' => true]);
     * var_dump($redis->get('key'));
     * // Output:
     * array(3) {
     *  'asd' => string(2) "as"
     *  'dd' => int(123)
     *  'b' => bool(true)
     * }
     * </pre>
     */
    public function get($key) {}

    /**
     * Set the string value in argument as value of the key.
     *
     * @since If you're using Redis >= 2.6.12, you can pass extended options as explained in example
     *
     * @param string $key     The key name to set.
     * @param mixed  $value   The value to set the key to.
     * @param mixed  $options Either an array with options for how to perform the set or an integer with an expiration.
     *                        If an expiration is set PhpRedis will actually send the `SETEX` command.
     * Since 2.6.12 it also supports different flags inside an array.
     * OPTION                         DESCRIPTION
     * ------------                   --------------------------------------------------------------
     * ['EX' => 60]                   expire 60 seconds.
     * ['PX' => 6000]                 expire in 6000 milliseconds.
     * ['EXAT' => time() + 10]        expire in 10 seconds.
     * ['PXAT' => time()*1000 + 1000] expire in 1 second.
     * ['KEEPTTL' => true]            Redis will not update the key's current TTL.
     * ['XX']                         Only set the key if it already exists.
     * ['NX']                         Only set the key if it doesn't exist.
     * ['GET']                        Instead of returning `+OK` return the previous value of the
     *                                key or NULL if the key didn't exist.
     *
     * @example
     * <pre>
     * // Simple key -> value set
     * $redis->set('key', 'value');
     *
     * // Will redirect, and actually make an SETEX call
     * $redis->set('key','value', 10);
     *
     * // Will set the key, if it doesn't exist, with a ttl of 10 seconds
     * $redis->set('key', 'value', ['nx', 'ex' => 10]);
     *
     * // Will set a key, if it does exist, with a ttl of 1000 milliseconds
     * $redis->set('key', 'value', ['xx', 'px' => 1000]);
     * </pre>
     *
     * @return bool|Redis TRUE if the command is successful or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link     https://redis.io/commands/set
     */
    public function set($key, $value, $options = null) {}

    /**
     * Set the string value in argument as value of the key, with a time to live.
     *
     * @param string $key    The name of the key to set.
     * @param int    $expire The key's expiration in seconds.
     * @param mixed  $value  The value to set the key.
     *
     * @return bool|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/setex
     * @example $redis->setex('key', 3600, 'value'); // sets key → value, with 1h TTL.
     */
    public function setex($key, $expire, $value) {}

    /**
     * Set the value and expiration in milliseconds of a key.
     *
     * @see     setex()
     * @param string $key    The key to set
     * @param int    $expire The TTL to set, in milliseconds.
     * @param mixed  $value  The value to set the key to.
     *
     * @return bool|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/psetex
     * @example $redis->psetex('key', 1000, 'value'); // sets key → value, with 1sec TTL.
     */
    public function psetex($key, $expire, $value) {}

    /**
     * Set the string value in argument as value of the key if the key doesn't already exist in the database.
     *
     * @param string       $key
     * @param mixed $value
     *
     * @return bool|array|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/setnx
     * @example
     * <pre>
     * $redis->setnx('key', 'value');   // return TRUE
     * $redis->setnx('key', 'value');   // return FALSE
     * </pre>
     */
    public function setnx(string $key, $value) {}

    /**
     * Remove specified keys.
     *
     * @param string|array $key1 Either an array with one or more key names or a string with the name of a key.
     * @param string ...$otherKeys One or more additional keys passed in a variadic fashion.
     *
     * @return false|int|Redis Number of keys deleted or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/del
     * @example
     * <pre>
     * $redis->set('key1', 'val1');
     * $redis->set('key2', 'val2');
     * $redis->set('key3', 'val3');
     * $redis->set('key4', 'val4');
     *
     * $redis->del('key1', 'key2');     // return 2
     * $redis->del(['key3', 'key4']);   // return 2
     * </pre>
     */
    public function del($key1, ...$otherKeys) {}

    /**
     * Remove specified keys.
     *
     * @param string|array $key An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @param string       ...$otherKeys
     *
     * @return false|int|Redis Number of keys deleted or Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: "%class%->del(%parametersList%)")]
    public function delete($key, ...$otherKeys) {}

    /**
     * Delete a key asynchronously in another thread. Otherwise it is just as DEL, but non blocking.
     *
     * @see del()
     * @param string|array $key An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @param string       ...$other_keys
     *
     * @return false|int|Redis Number of keys unlinked or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/unlink
     * @example
     * <pre>
     * $redis->set('key1', 'val1');
     * $redis->set('key2', 'val2');
     * $redis->set('key3', 'val3');
     * $redis->set('key4', 'val4');
     * $redis->unlink('key1', 'key2');          // return 2
     * $redis->unlink(array('key3', 'key4'));   // return 2
     * </pre>
     */
    public function unlink($key, ...$other_keys) {}

    /**
     * Enter and exit transactional mode.
     *
     * @param int $mode Redis::MULTI|Redis::PIPELINE
     * Defaults to Redis::MULTI.
     * A Redis::MULTI block of commands runs as a single transaction;
     * a Redis::PIPELINE block is simply transmitted faster to the server, but without any guarantee of atomicity.
     * discard cancels a transaction.
     *
     * @return static|Redis returns the Redis instance and enters multi-mode or Redis if in multimode
     * Once in multi-mode, all subsequent method calls return the same object until exec() is called.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/multi
     * @example
     * <pre>
     * $ret = $redis->multi()
     *      ->set('key1', 'val1')
     *      ->get('key1')
     *      ->set('key2', 'val2')
     *      ->get('key2')
     *      ->exec();
     *
     * //$ret == array (
     * //    0 => TRUE,
     * //    1 => 'val1',
     * //    2 => TRUE,
     * //    3 => 'val2');
     * </pre>
     */
    public function multi($mode = Redis::MULTI) {}

    /**
     * Returns a Redis instance which can simply transmitted faster to the server.
     *
     * @return bool|Redis returns the Redis instance.
     * Once in pipeline-mode, all subsequent method calls return the same object until exec() is called.
     * Pay attention, that Pipeline is not a transaction, so you can get unexpected
     * results in case of big pipelines and small read/write timeouts.
     *
     * @throws RedisException
     *
     * @link   https://redis.io/topics/pipelining
     * @example
     * <pre>
     * $ret = $this->redis->pipeline()
     *      ->ping()
     *      ->multi()->set('x', 42)->incr('x')->exec()
     *      ->ping()
     *      ->multi()->get('x')->del('x')->exec()
     *      ->ping()
     *      ->exec();
     *
     * //$ret == array (
     * //    0 => '+PONG',
     * //    1 => [TRUE, 43],
     * //    2 => '+PONG',
     * //    3 => [43, 1],
     * //    4 => '+PONG');
     * </pre>
     */
    public function pipeline() {}

    /**
     * Execute either a MULTI or PIPELINE block and return the array of replies.
     *
     * @return array|false|Redis The array of pipeline'd or multi replies or false on failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @see multi()
     * @link https://redis.io/commands/exec
     * @link https://redis.io/commands/multi
     *
     * @example
     * $res = $redis->multi()
     *              ->set('foo', 'bar')
     *              ->get('foo')
     *              ->del('list')
     *              ->rpush('list', 'one', 'two', 'three')
     *              ->exec();
     */
    public function exec() {}

    /**
     * Flushes all previously queued commands in a transaction and restores the connection state to normal.
     *
     * @return bool|Redis True if we could discard the transaction or Redis if in multimode
     *
     * @throws RedisException
     *
     * @see multi()
     * @link https://redis.io/commands/discard
     *
     * @example
     * $redis->getMode();
     * $redis->set('foo', 'bar');
     * $redis->discard();
     * $redis->getMode();
     */
    public function discard() {}

    /**
     * Watches a key for modifications by another client. If the key is modified between WATCH and EXEC,
     * the MULTI/EXEC transaction will fail (return FALSE). unwatch cancels all the watching of all keys by this client.
     *
     * @param string|array $key     Either an array with one or more key names, or a string key name
     * @param string ...$other_keys If the first argument was passed as a string, any number of
     *                              additional string key names may be passed variadically.
     *
     * @return bool|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/watch
     * @example
     * <pre>
     * $redis->watch('x');
     * // long code here during the execution of which other clients could well modify `x`
     * $ret = $redis->multi()
     *          ->incr('x')
     *          ->exec();
     * // $ret = FALSE if x has been modified between the call to WATCH and the call to EXEC.
     * </pre>
     */
    public function watch($key, ...$other_keys) {}

    /**
     * Remove any previously WATCH'ed keys in a transaction.
     *
     * @throws RedisException
     *
     * @see watch()
     * @return bool|Redis
     * @link    https://redis.io/commands/unwatch
     */
    public function unwatch() {}

    /**
     * Subscribes the client to the specified channels.
     *
     * Once the client enters the subscribed state it is not supposed to issue any other commands, except for additional SUBSCRIBE, SSUBSCRIBE, PSUBSCRIBE, UNSUBSCRIBE, SUNSUBSCRIBE, PUNSUBSCRIBE, PING, RESET and QUIT commands.
     *
     * @param array|string $channels One or more channel names.
     * @param callable $callback     The callback PhpRedis will invoke when we receive a message from one of the subscribed channels.
     *
     * @return false|array|Redis False on faiilure. Note that this command will block the client in a subscribe loop, waiting for messages to arrive
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/subscribe
     * @since 2.0
     *
     * @example
     * $redis->subscribe(['channel-1', 'channel-2'], function ($redis, $channel, $message) {
     *     echo "[$channel]: $message\n";
     *
     *     // Unsubscribe from the message channel when we read 'quit'
     *     if ($message == 'quit') {
     *         echo "Unsubscribing from '$channel'\n";
     *         $redis->unsubscribe([$channel]);
     *     }
     * });
     *
     * // Once we read 'quit' from both channel-1 and channel-2 the subscribe loop will be broken and this command will execute.
     * echo "Subscribe loop ended\n";
     */
    public function subscribe($channels, $callback) {}

    /**
     * Subscribe to channels by pattern
     *
     * @param array                 $patterns an array of glob-style patterns to subscribe
     * @param string|array|callable $callback Either a string or an array with an object and method.
     *                     The callback will get four arguments ($redis, $pattern, $channel, $message)
     * @return mixed|Redis Any non-null return value in the callback will be returned to the caller or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/psubscribe
     * @example
     * <pre>
     * function f($redis, $pattern, $chan, $msg) {
     *  echo "Pattern: $pattern\n";
     *  echo "Channel: $chan\n";
     *  echo "Payload: $msg\n";
     * }
     *
     * $redis->psubscribe(array('chan-1', 'chan-2', 'chan-3'), 'f')
     * </pre>
     */
    public function psubscribe($patterns, $callback) {}

    /**
     * Publish messages to channels.
     *
     * Warning: this function will probably change in the future.
     *
     * @param string $channel The channel to publish to.
     * @param string $message The message itself.
     *
     * @return false|int|Redis Number of clients that received the message or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/publish
     * @example $redis->publish('chan-1', 'hello, world!'); // send message.
     */
    public function publish($channel, $message) {}

    /**
     * A command allowing you to get information on the Redis pub/sub system
     *
     * @param string       $keyword    String, which can be: "channels", "numsub", or "numpat"
     * @param mixed        $argument   Optional, variant.
     *                                 For the "channels" subcommand, you can pass a string pattern.
     *                                 For "numsub" an array of channel names
     *
     * @return mixed|Redis Either an integer or an array or Redis if in multimode
     *   - channels  Returns an array where the members are the matching channels.
     *   - numsub    Returns a key/value array where the keys are channel names and
     *               values are their counts.
     *   - numpat    Integer return containing the number active pattern subscriptions
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/pubsub
     * @example
     * <pre>
     * $redis->pubsub('channels'); // All channels
     * $redis->pubsub('channels', '*pattern*'); // Just channels matching your pattern
     * $redis->pubsub('numsub', array('chan1', 'chan2')); // Get subscriber counts for 'chan1' and 'chan2'
     * $redis->pubsub('numpat'); // Get the number of pattern subscribers
     * </pre>
     */
    public function pubsub($keyword, $argument = null) {}

    /**
     * Stop listening for messages posted to the given channels.
     *
     * @param array $channels One or more channels to unsubscribe from.
     *
     * @return bool|array|Redis The array of unsubscribed channels.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/unsubscribe
     *
     * @example
     * $redis->subscribe(['channel-1', 'channel-2'], function ($redis, $channel, $message) {
     *     if ($message == 'quit') {
     *         echo "$channel => 'quit' detected, unsubscribing!\n";
     *         $redis->unsubscribe([$channel]);
     *     } else {
     *         echo "$channel => $message\n";
     *     }
     * });
     */
    public function unsubscribe(array $channels) {}

    /**
     * Stop listening for messages posted to the given channels.
     *
     * @param array $patterns   an array of glob-style patterns to unsubscribe
     *
     * @return false|array
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/punsubscribe
     */
    public function punsubscribe(array $patterns) {}

    /**
     * Verify if the specified key/keys exists
     *
     * This function took a single argument and returned TRUE or FALSE in phpredis versions < 4.0.0.
     *
     * @since >= 4.0 Returned int, if < 4.0 returned bool
     *
     * @param mixed $key Either an array of keys or a string key
     * @param mixed ...$other_keys If the previous argument was a string, you may send any number of additional keys to test.
     *
     * @return int|bool|Redis The number of keys tested that do exist or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/exists
     * @link https://github.com/phpredis/phpredis#exists
     * @example
     * <pre>
     * $redis->exists('key'); // 1
     * $redis->exists('NonExistingKey'); // 0
     *
     * $redis->mset(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz']);
     * $redis->exists(['foo', 'bar', 'baz]); // 3
     * $redis->exists('foo', 'bar', 'baz'); // 3
     * </pre>
     */
    public function exists($key, ...$other_keys) {}

    /**
     * Increment the number stored at key by one.
     *
     * @param string $key The key to increment
     * @param int    $by  An optional amount to increment by.
     *
     * @return false|int|Redis the new value or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/incr
     * @example
     * <pre>
     * $redis->incr('key1'); // key1 didn't exists, set to 0 before the increment and now has the value 1
     * $redis->incr('key1'); // 2
     * $redis->incr('key1'); // 3
     * $redis->incr('key1'); // 4
     * $redis->incr('key1', 2); // 6
     * </pre>
     */
    public function incr($key, $by = 1) {}

    /**
     * Increment the float value of a key by the given amount
     *
     * @param string $key
     * @param float  $increment
     *
     * @return float|false|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/incrbyfloat
     * @example
     * <pre>
     * $redis->set('x', 3);
     * $redis->incrByFloat('x', 1.5);   // float(4.5)
     * $redis->get('x');                // float(4.5)
     * $redis->incrByFloat('x', 3.1415926);
     * </pre>
     */
    public function incrByFloat($key, $increment) {}

    /**
     * Increment the number stored at key by one.
     *
     * @param string $key   The key to increment.
     * @param int    $value The amount to increment.
     *
     * @return false|int|Redis the new value or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/incrby
     * @example
     * <pre>
     * $redis->incr('key1');        // key1 didn't exists, set to 0 before the increment and now has the value 1
     * $redis->incr('key1');        // 2
     * $redis->incr('key1');        // 3
     * $redis->incr('key1');        // 4
     * $redis->incrBy('key1', 10);  // 14
     * </pre>
     */
    public function incrBy($key, $value) {}

    /**
     * Decrement the number stored at key by one.
     *
     * @param string $key The key to decrement
     * @param int    $by  How much to decrement the key. Note that if this value is not sent or is set to `1`
     *
     * @return false|int|Redis The new value of the key or false on failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/decr
     * @example
     * <pre>
     * $redis->decr('key1'); // key1 didn't exists, set to 0 before the increment and now has the value -1
     * $redis->decr('key1'); // -2
     * $redis->decr('key1'); // -3
     * $redis->decr('key1', 2); // -5
     * </pre>
     */
    public function decr($key, $by = 1) {}

    /**
     * Decrement the number stored at key by one.
     *
     * @param string $key The integer key to decrement.
     * @param int    $value How much to decrement the key.
     *
     * @return false|int|Redis The new value of the key or false on failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/decrby
     * @example
     * <pre>
     * $redis->decr('key1');        // key1 didn't exists, set to 0 before the increment and now has the value -1
     * $redis->decr('key1');        // -2
     * $redis->decr('key1');        // -3
     * $redis->decrBy('key1', 10);  // -13
     * </pre>
     */
    public function decrBy($key, $value) {}

    /**
     * Adds the string values to the head (left) of the list.
     * Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     *
     * @param string $key       The list to prepend.
     * @param mixed  ...$value1 One or more elements to prepend.
     *
     * @return int|false|Redis The new length of the list in case of success, FALSE in case of Failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/lpush
     * @example
     * <pre>
     * $redis->lPush('l', 'v1', 'v2', 'v3', 'v4')   // int(4)
     * var_dump( $redis->lRange('l', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v4"
     * //   [1]=> string(2) "v3"
     * //   [2]=> string(2) "v2"
     * //   [3]=> string(2) "v1"
     * // }
     * </pre>
     */
    public function lPush($key, ...$value1) {}

    /**
     * Adds the string values to the tail (right) of the list.
     * Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     *
     * @param string $key      The list to append to.
     * @param mixed ...$value1 one or more elements to append.
     *
     * @return int|false|Redis The new length of the list in case of success, FALSE in case of Failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/rpush
     * @example
     * <pre>
     * $redis->rPush('l', 'v1', 'v2', 'v3', 'v4');    // int(4)
     * var_dump( $redis->lRange('l', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v2"
     * //   [2]=> string(2) "v3"
     * //   [3]=> string(2) "v4"
     * // }
     * </pre>
     */
    public function rPush($key, ...$value1) {}

    /**
     * Adds the string value to the head (left) of the list if the list exists.
     *
     * @param string $key   The key to prepend to.
     * @param mixed  $value The value to prepend.
     *
     * @return int|false|Redis The new length of the list in case of success, FALSE in case of Failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/lpushx
     * @example
     * <pre>
     * $redis->del('key1');
     * $redis->lPushx('key1', 'A');     // returns 0
     * $redis->lPush('key1', 'A');      // returns 1
     * $redis->lPushx('key1', 'B');     // returns 2
     * $redis->lPushx('key1', 'C');     // returns 3
     * // key1 now points to the following list: [ 'A', 'B', 'C' ]
     * </pre>
     */
    public function lPushx($key, $value) {}

    /**
     * Adds the string value to the tail (right) of the list if the ist exists. FALSE in case of Failure.
     *
     * @param string $key   The key to prepend to.
     * @param mixed  $value The value to prepend.
     *
     * @return int|false|Redis The new length of the list in case of success, FALSE in case of Failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/rpushx
     * @example
     * <pre>
     * $redis->del('key1');
     * $redis->rPushx('key1', 'A'); // returns 0
     * $redis->rPush('key1', 'A'); // returns 1
     * $redis->rPushx('key1', 'B'); // returns 2
     * $redis->rPushx('key1', 'C'); // returns 3
     * // key1 now points to the following list: [ 'A', 'B', 'C' ]
     * </pre>
     */
    public function rPushx($key, $value) {}

    /**
     * Returns and removes the first element of the list.
     *
     * @param string $key   The list to pop from.
     * @param int    $count Optional number of elements to remove.  By default one element is popped.
     *
     * @return  mixed|bool|Redis if command executed successfully BOOL FALSE in case of failure (empty list) or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/lpop
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * $redis->lPop('key1');        // key1 => [ 'B', 'C' ]
     * </pre>
     */
    public function lPop($key, $count = 0) {}

    /**
     * Returns and removes the last element of the list.
     *
     * @param string $key   A redis LIST key name.
     * @param int    $count The maximum number of elements to pop at once. NOTE: The `count` argument requires Redis >= 6.2.0
     *
     * @return mixed|array|string|bool|Redis if command executed successfully BOOL FALSE in case of failure (empty list) or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/rpop
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * $redis->rPop('key1');        // key1 => [ 'A', 'B' ]
     * </pre>
     */
    public function rPop($key, $count = 0) {}

    /**
     * Is a blocking lPop primitive. If at least one of the lists contains at least one element,
     * the element will be popped from the head of the list and returned to the caller.
     * Il all the list identified by the keys passed in arguments are empty, blPop will block
     * during the specified timeout until an element is pushed to one of those lists. This element will be popped.
     *
     * @param string|string[]  $key_or_keys    String array containing the keys of the lists OR variadic list of strings
     * @param string|float|int $timeout_or_key Timeout is always the required final parameter
     * @param mixed           ...$extra_args
     *
     * @return array|null|false|Redis Can return various things depending on command and data or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/blpop
     * @example
     * <pre>
     * // Non blocking feature
     * $redis->lPush('key1', 'A');
     * $redis->del('key2');
     *
     * $redis->blPop('key1', 'key2', 10);        // array('key1', 'A')
     * // OR
     * $redis->blPop(['key1', 'key2'], 10);      // array('key1', 'A')
     *
     * $redis->blPop('key1', 'key2', 10);        // array('key1', 'A')
     * // OR
     * $redis->blPop(['key1', 'key2'], 10); // array('key1', 'A')
     *
     * // Blocking feature
     *
     * // process 1
     * $redis->del('key1');
     * $redis->blPop('key1', 10);
     * // blocking for 10 seconds
     *
     * // process 2
     * $redis->lPush('key1', 'A');
     *
     * // process 1
     * // array('key1', 'A') is returned
     * </pre>
     */
    public function blPop($key_or_keys, $timeout_or_key, ...$extra_args) {}

    /**
     * Is a blocking rPop primitive. If at least one of the lists contains at least one element,
     * the element will be popped from the head of the list and returned to the caller.
     * Il all the list identified by the keys passed in arguments are empty, brPop will
     * block during the specified timeout until an element is pushed to one of those lists.
     * This element will be popped.
     *
     * @param string|string[] $key_or_keys     String array containing the keys of the lists OR variadic list of strings
     * @param string|float|int $timeout_or_key Timeout is always the required final parameter
     * @param mixed           ...$extra_args
     *
     * @return array|null|true|Redis Can return various things depending on command and data or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/brpop
     * @example
     * <pre>
     * // Non blocking feature
     * $redis->rPush('key1', 'A');
     * $redis->del('key2');
     *
     * $redis->brPop('key1', 'key2', 10); // array('key1', 'A')
     * // OR
     * $redis->brPop(array('key1', 'key2'), 10); // array('key1', 'A')
     *
     * $redis->brPop('key1', 'key2', 10); // array('key1', 'A')
     * // OR
     * $redis->brPop(array('key1', 'key2'), 10); // array('key1', 'A')
     *
     * // Blocking feature
     *
     * // process 1
     * $redis->del('key1');
     * $redis->brPop('key1', 10);
     * // blocking for 10 seconds
     *
     * // process 2
     * $redis->lPush('key1', 'A');
     *
     * // process 1
     * // array('key1', 'A') is returned
     * </pre>
     */
    public function brPop($key_or_keys, $timeout_or_key, ...$extra_args) {}

    /**
     * Returns the size of a list identified by Key. If the list didn't exist or is empty,
     * the command returns 0. If the data type identified by Key is not a list, the command return FALSE.
     *
     * @param string $key
     *
     * @return int|bool|Redis The size of the list identified by Key exists or Redis if in multimode
     * bool FALSE if the data type identified by Key is not list
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/llen
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C'); // key1 => [ 'A', 'B', 'C' ]
     * $redis->lLen('key1');       // 3
     * $redis->rPop('key1');
     * $redis->lLen('key1');       // 2
     * </pre>
     */
    public function lLen($key) {}

    /**
     * @link https://redis.io/commands/llen
     *
     * @param string $key
     *
     * @return false|int|Redis The size of the list identified by Key exists or Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->lLen(%parametersList%)')]
    public function lSize($key) {}

    /**
     * Return the specified element of the list stored at the specified key.
     * 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ...
     * Return FALSE in case of a bad index or a key that doesn't point to a list.
     *
     * @param string $key
     * @param int    $index
     *
     * @return mixed|bool|Redis the element at this index or Redis if in multimode
     *
     * @throws RedisException
     *
     * Bool FALSE if the key identifies a non-string data type, or no value corresponds to this index in the list Key.
     *
     * @link    https://redis.io/commands/lindex
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * $redis->lIndex('key1', 0);     // 'A'
     * $redis->lIndex('key1', -1);    // 'C'
     * $redis->lIndex('key1', 10);    // `FALSE`
     * </pre>
     */
    public function lIndex($key, $index) {}

    /**
     * @link https://redis.io/commands/lindex
     *
     * @param string $key
     * @param int $index
     * @return mixed|bool|Redis the element at this index or Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->lIndex(%parametersList%)')]
    public function lGet($key, $index) {}

    /**
     * Set the list at index with the new value.
     *
     * @param string $key   The list to modify.
     * @param int    $index The position of the element to change.
     * @param mixed  $value The new value.
     *
     * @return bool|Redis TRUE if the new value is setted or Redis if in multimode
     * FALSE if the index is out of range, or data type identified by key is not a list.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/lset
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');    // key1 => [ 'A', 'B', 'C' ]
     * $redis->lIndex('key1', 0);     // 'A'
     * $redis->lSet('key1', 0, 'X');
     * $redis->lIndex('key1', 0);     // 'X'
     * </pre>
     */
    public function lSet($key, $index, $value) {}

    /**
     * Returns the specified elements of the list stored at the specified key in
     * the range [start, end]. start and stop are interpretated as indices: 0 the first element,
     * 1 the second ... -1 the last element, -2 the penultimate ...
     *
     * @param string $key   The list to query.
     * @param int    $start The beginning index to retrieve.  This number can be negative meaning start from the end of the list.
     * @param int    $end   The end index to retrieve. This can also be negative to start from the end of the list.
     *
     * @return array|Redis containing the values in specified range or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/lrange
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');
     * $redis->lRange('key1', 0, -1); // array('A', 'B', 'C')
     * </pre>
     */
    public function lRange($key, $start, $end) {}

    /**
     * @link https://redis.io/commands/lrange
     *
     * @param string    $key
     * @param int       $start
     * @param int       $end
     * @return array|Redis returns Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->lRange(%parametersList%)')]
    public function lGetRange($key, $start, $end) {}

    /**
     * Trims an existing list so that it will contain only a specified range of elements.
     *
     * @param string $key   The list to trim
     * @param int    $start The starting index to keep
     * @param int    $end   The ending index to keep.
     *
     * @return array|false|Redis Bool return FALSE if the key identify a non-list value or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link        https://redis.io/commands/ltrim
     * @example
     * <pre>
     * $redis->rPush('key1', 'A');
     * $redis->rPush('key1', 'B');
     * $redis->rPush('key1', 'C');
     * $redis->lRange('key1', 0, -1); // array('A', 'B', 'C')
     * $redis->lTrim('key1', 0, 1);
     * $redis->lRange('key1', 0, -1); // array('A', 'B')
     * </pre>
     */
    public function lTrim($key, $start, $end) {}

    /**
     * @link  https://redis.io/commands/ltrim
     *
     * @param string    $key
     * @param int       $start
     * @param int       $stop
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->lTrim(%parametersList%)')]
    public function listTrim($key, $start, $stop) {}

    /**
     * Removes the first count occurrences of the value element from the list.
     * If count is zero, all the matching elements are removed. If count is negative,
     * elements are removed from tail to head.
     *
     * @param string $key   The list to truncate.
     * @param mixed  $value The value to remove.
     * @param int    $count How many elements matching the value to remove.
     *
     * @return int|bool|Redis the number of elements to remove or Redis if in multimode
     * bool FALSE if the value identified by key is not a list.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/lrem
     * @example
     * <pre>
     * $redis->lPush('key1', 'A');
     * $redis->lPush('key1', 'B');
     * $redis->lPush('key1', 'C');
     * $redis->lPush('key1', 'A');
     * $redis->lPush('key1', 'A');
     *
     * $redis->lRange('key1', 0, -1);   // array('A', 'A', 'C', 'B', 'A')
     * $redis->lRem('key1', 'A', 2);    // 2
     * $redis->lRange('key1', 0, -1);   // array('C', 'B', 'A')
     * </pre>
     */
    public function lRem($key, $value, $count = 0) {}

    /**
     * @link https://redis.io/commands/lremove
     *
     * @param string $key
     * @param string $value
     * @param int $count
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->lRem(%parametersList%)')]
    public function lRemove($key, $value, $count) {}

    /**
     * Insert value in the list before or after the pivot value. the parameter options
     * specify the position of the insert (before or after). If the list didn't exists,
     * or the pivot didn't exists, the value is not inserted.
     *
     * @param string       $key
     * @param string       $position Redis::BEFORE | Redis::AFTER
     * @param mixed        $pivot
     * @param mixed        $value
     *
     * @return false|int|Redis The number of the elements in the list, -1 if the pivot didn't exists or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/linsert
     * @example
     * <pre>
     * $redis->del('key1');
     * $redis->lInsert('key1', Redis::AFTER, 'A', 'X');     // 0
     *
     * $redis->lPush('key1', 'A');
     * $redis->lPush('key1', 'B');
     * $redis->lPush('key1', 'C');
     *
     * $redis->lInsert('key1', Redis::BEFORE, 'C', 'X');    // 4
     * $redis->lRange('key1', 0, -1);                       // array('A', 'B', 'X', 'C')
     *
     * $redis->lInsert('key1', Redis::AFTER, 'C', 'Y');     // 5
     * $redis->lRange('key1', 0, -1);                       // array('A', 'B', 'X', 'C', 'Y')
     *
     * $redis->lInsert('key1', Redis::AFTER, 'W', 'value'); // -1
     * </pre>
     */
    public function lInsert($key, $position, $pivot, $value) {}

    /**
     * Adds a values to the set value stored at key.
     *
     * @param  string  $key  Required key
     * @param  mixed   $value
     * @param  mixed  ...$other_values  Variadic list of values
     *
     * @return int|bool|Redis The number of elements added to the set or Redis if in multimode
     * If this value is already in the set, FALSE is returned
     *
     * @link    https://redis.io/commands/sadd
     * @example
     * <pre>
     * $redis->sAdd('k', 'v1');                // int(1)
     * $redis->sAdd('k', 'v1', 'v2', 'v3');    // int(2)
     * </pre>
     */
    public function sAdd(string $key, $value, ...$other_values) {}

    /**
     * Removes the specified members from the set value stored at key.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  mixed  ...$other_values  Variadic list of members
     *
     * @return false|int|Redis The number of elements removed from the set or Redis if in multimode
     *
     * @link    https://redis.io/commands/srem
     * @example
     * <pre>
     * var_dump( $redis->sAdd('k', 'v1', 'v2', 'v3') );    // int(3)
     * var_dump( $redis->sRem('k', 'v2', 'v3') );          // int(2)
     * var_dump( $redis->sMembers('k') );
     * //// Output:
     * // array(1) {
     * //   [0]=> string(2) "v1"
     * // }
     * </pre>
     */
    public function sRem(string $key, $value, ...$other_values) {}

    /**
     * @link    https://redis.io/commands/srem
     *
     * @param   string  $key
     * @param   string|mixed  ...$member1
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->sRem(%parametersList%)')]
    public function sRemove($key, ...$member1) {}

    /**
     * Moves the specified member from the set at srcKey to the set at dstKey.
     *
     * @param string       $srcKey
     * @param string       $dstKey
     * @param mixed        $member
     *
     * @return bool|Redis If the operation is successful, return TRUE or Redis if in multimode
     * If the srcKey and/or dstKey didn't exist, and/or the member didn't exist in srcKey, FALSE is returned.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/smove
     * @example
     * <pre>
     * $redis->sAdd('key1' , 'set11');
     * $redis->sAdd('key1' , 'set12');
     * $redis->sAdd('key1' , 'set13');          // 'key1' => {'set11', 'set12', 'set13'}
     * $redis->sAdd('key2' , 'set21');
     * $redis->sAdd('key2' , 'set22');          // 'key2' => {'set21', 'set22'}
     * $redis->sMove('key1', 'key2', 'set13');  // 'key1' =>  {'set11', 'set12'}
     *                                          // 'key2' =>  {'set21', 'set22', 'set13'}
     * </pre>
     */
    public function sMove($srcKey, $dstKey, $member) {}

    /**
     * Checks if value is a member of the set stored at the key key.
     *
     * @param string       $key
     * @param mixed        $value
     *
     * @return bool|Redis TRUE if value is a member of the set at key key, FALSE otherwise or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sismember
     * @example
     * <pre>
     * $redis->sAdd('key1' , 'set1');
     * $redis->sAdd('key1' , 'set2');
     * $redis->sAdd('key1' , 'set3'); // 'key1' => {'set1', 'set2', 'set3'}
     *
     * $redis->sIsMember('key1', 'set1'); // TRUE
     * $redis->sIsMember('key1', 'setX'); // FALSE
     * </pre>
     */
    public function sIsMember(string $key, $value) {}

    /**
     * @link    https://redis.io/commands/sismember
     *
     * @param string       $key
     * @param string|mixed $value
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->sIsMember(%parametersList%)')]
    public function sContains($key, $value) {}

    /**
     * Returns the cardinality of the set identified by key.
     *
     * @param string $key
     *
     * @return false|int|Redis the cardinality of the set identified by key, 0 if the set doesn't exist or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/scard
     * @example
     * <pre>
     * $redis->sAdd('key1' , 'set1');
     * $redis->sAdd('key1' , 'set2');
     * $redis->sAdd('key1' , 'set3');   // 'key1' => {'set1', 'set2', 'set3'}
     * $redis->sCard('key1');           // 3
     * $redis->sCard('keyX');           // 0
     * </pre>
     */
    public function sCard($key) {}

    /**
     * Removes and returns a random element from the set value at Key.
     *
     * @param string $key   The set in question.
     * @param int    $count An optional number of members to pop. This defaults to removing one element.
     *
     * @return string|mixed|array|bool|Redis "popped" values or Redis if in multimode
     * bool FALSE if set identified by key is empty or doesn't exist.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/spop
     * @example
     * <pre>
     * $redis->sAdd('key1' , 'set1');
     * $redis->sAdd('key1' , 'set2');
     * $redis->sAdd('key1' , 'set3');   // 'key1' => {'set3', 'set1', 'set2'}
     * $redis->sPop('key1');            // 'set1', 'key1' => {'set3', 'set2'}
     * $redis->sPop('key1');            // 'set3', 'key1' => {'set2'}
     *
     * // With count
     * $redis->sAdd('key2', 'set1', 'set2', 'set3');
     * var_dump( $redis->sPop('key2', 3) ); // Will return all members but in no particular order
     *
     * // array(3) {
     * //   [0]=> string(4) "set2"
     * //   [1]=> string(4) "set3"
     * //   [2]=> string(4) "set1"
     * // }
     * </pre>
     */
    public function sPop($key, $count = 0) {}

    /**
     * Returns a random element(s) from the set value at Key, without removing it.
     *
     * @param string $key   The set to query.
     * @param int    $count An optional count of members to return.
     *
     *                      If this value is positive, Redis will return *up to* the requested
     *                      number but with unique elements that will never repeat.  This means
     *                      you may recieve fewer then `$count` replies.
     *
     *                      If the number is negative, Redis will return the exact number requested
     *                      but the result may contain duplicate elements.
     *
     * @return string|mixed|array|bool|Redis value(s) from the set or Redis if in multimode
     * bool FALSE if set identified by key is empty or doesn't exist and count argument isn't passed.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/srandmember
     * @example
     * <pre>
     * $redis->sAdd('key1' , 'one');
     * $redis->sAdd('key1' , 'two');
     * $redis->sAdd('key1' , 'three');              // 'key1' => {'one', 'two', 'three'}
     *
     * var_dump( $redis->sRandMember('key1') );     // 'key1' => {'one', 'two', 'three'}
     *
     * // string(5) "three"
     *
     * var_dump( $redis->sRandMember('key1', 2) );  // 'key1' => {'one', 'two', 'three'}
     *
     * // array(2) {
     * //   [0]=> string(2) "one"
     * //   [1]=> string(5) "three"
     * // }
     * </pre>
     */
    public function sRandMember($key, $count = 0) {}

    /**
     * Returns the members of a set resulting from the intersection of all the sets
     * held at the specified keys. If just a single key is specified, then this command
     * produces the members of this set. If one of the keys is missing, FALSE is returned.
     *
     * @param string $key1         keys identifying the different sets on which we will apply the intersection.
     * @param string ...$otherKeys variadic list of keys
     *
     * @return array|false|Redis contain the result of the intersection between those keys or Redis if in multimode
     * If the intersection between the different sets is empty, the return value will be empty array.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sinter
     * @example
     * <pre>
     * $redis->sAdd('key1', 'val1');
     * $redis->sAdd('key1', 'val2');
     * $redis->sAdd('key1', 'val3');
     * $redis->sAdd('key1', 'val4');
     *
     * $redis->sAdd('key2', 'val3');
     * $redis->sAdd('key2', 'val4');
     *
     * $redis->sAdd('key3', 'val3');
     * $redis->sAdd('key3', 'val4');
     *
     * var_dump($redis->sInter('key1', 'key2', 'key3'));
     *
     * //array(2) {
     * //  [0]=>
     * //  string(4) "val4"
     * //  [1]=>
     * //  string(4) "val3"
     * //}
     * </pre>
     */
    public function sInter($key1, ...$otherKeys) {}

    /**
     * Performs a sInter command and stores the result in a new set.
     *
     * @param array|string $key Either a string key, or an array of keys (with at least two elements,
     *                          consisting of the destination key name and one or more source keys names.
     * @param string ...$otherKeys If the first argument was a string, subsequent arguments should be source key names.
     *
     * @return int|false|Redis The cardinality of the resulting set, or FALSE in case of a missing key or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sinterstore
     * @example
     * <pre>
     * $redis->sAdd('key1', 'val1');
     * $redis->sAdd('key1', 'val2');
     * $redis->sAdd('key1', 'val3');
     * $redis->sAdd('key1', 'val4');
     *
     * $redis->sAdd('key2', 'val3');
     * $redis->sAdd('key2', 'val4');
     *
     * $redis->sAdd('key3', 'val3');
     * $redis->sAdd('key3', 'val4');
     *
     * var_dump($redis->sInterStore('output', 'key1', 'key2', 'key3'));
     * var_dump($redis->sMembers('output'));
     *
     * //int(2)
     * //
     * //array(2) {
     * //  [0]=>
     * //  string(4) "val4"
     * //  [1]=>
     * //  string(4) "val3"
     * //}
     * </pre>
     */
    public function sInterStore(string $key, ...$otherKeys) {}

    /**
     * Performs the union between N sets and returns it.
     *
     * @param string $key1         first key for union
     * @param string ...$otherKeys variadic list of keys corresponding to sets in redis
     *
     * @return array|false|Redis The union of all these sets or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sunionstore
     * @example
     * <pre>
     * $redis->sAdd('s0', '1');
     * $redis->sAdd('s0', '2');
     * $redis->sAdd('s1', '3');
     * $redis->sAdd('s1', '1');
     * $redis->sAdd('s2', '3');
     * $redis->sAdd('s2', '4');
     *
     * var_dump($redis->sUnion('s0', 's1', 's2'));
     *
     * array(4) {
     * //  [0]=>
     * //  string(1) "3"
     * //  [1]=>
     * //  string(1) "4"
     * //  [2]=>
     * //  string(1) "1"
     * //  [3]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public function sUnion($key1, ...$otherKeys) {}

    /**
     * Performs the same action as sUnion, but stores the result in the first key
     *
     * @param string $dstKey       The destination key
     * @param string $key1         The first source key
     * @param string ...$otherKeys One or more additional source keys
     *
     * @return false|int|Redis Any number of keys corresponding to sets in redis or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sunionstore
     * @example
     * <pre>
     * $redis->del('s0', 's1', 's2');
     *
     * $redis->sAdd('s0', '1');
     * $redis->sAdd('s0', '2');
     * $redis->sAdd('s1', '3');
     * $redis->sAdd('s1', '1');
     * $redis->sAdd('s2', '3');
     * $redis->sAdd('s2', '4');
     *
     * var_dump($redis->sUnionStore('dst', 's0', 's1', 's2'));
     * var_dump($redis->sMembers('dst'));
     *
     * //int(4)
     * //array(4) {
     * //  [0]=>
     * //  string(1) "3"
     * //  [1]=>
     * //  string(1) "4"
     * //  [2]=>
     * //  string(1) "1"
     * //  [3]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public function sUnionStore($dstKey, $key1, ...$otherKeys) {}

    /**
     * Performs the difference between N sets and returns it.
     *
     * @param string $key1         first key for diff
     * @param string ...$otherKeys variadic list of keys corresponding to sets in redis
     *
     * @return array|false|Redis string[] The difference of the first set will all the others or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sdiff
     * @example
     * <pre>
     * $redis->del('s0', 's1', 's2');
     *
     * $redis->sAdd('s0', '1');
     * $redis->sAdd('s0', '2');
     * $redis->sAdd('s0', '3');
     * $redis->sAdd('s0', '4');
     *
     * $redis->sAdd('s1', '1');
     * $redis->sAdd('s2', '3');
     *
     * var_dump($redis->sDiff('s0', 's1', 's2'));
     *
     * //array(2) {
     * //  [0]=>
     * //  string(1) "4"
     * //  [1]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public function sDiff($key1, ...$otherKeys) {}

    /**
     * Performs the same action as sDiff, but stores the result in the first key
     *
     * @param string $dstKey       the key to store the diff into.
     * @param string $key1         first key for diff
     * @param string ...$otherKeys variadic list of keys corresponding to sets in redis
     *
     * @return int|false|Redis The cardinality of the resulting set, or FALSE in case of a missing key or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sdiffstore
     * @example
     * <pre>
     * $redis->del('s0', 's1', 's2');
     *
     * $redis->sAdd('s0', '1');
     * $redis->sAdd('s0', '2');
     * $redis->sAdd('s0', '3');
     * $redis->sAdd('s0', '4');
     *
     * $redis->sAdd('s1', '1');
     * $redis->sAdd('s2', '3');
     *
     * var_dump($redis->sDiffStore('dst', 's0', 's1', 's2'));
     * var_dump($redis->sMembers('dst'));
     *
     * //int(2)
     * //array(2) {
     * //  [0]=>
     * //  string(1) "4"
     * //  [1]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public function sDiffStore($dstKey, $key1, ...$otherKeys) {}

    /**
     * Returns the contents of a set.
     *
     * @param string $key
     *
     * @return array|false|Redis An array of elements, the contents of the set or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/smembers
     * @example
     * <pre>
     * $redis->del('s');
     * $redis->sAdd('s', 'a');
     * $redis->sAdd('s', 'b');
     * $redis->sAdd('s', 'a');
     * $redis->sAdd('s', 'c');
     * var_dump($redis->sMembers('s'));
     *
     * //array(3) {
     * //  [0]=>
     * //  string(1) "c"
     * //  [1]=>
     * //  string(1) "a"
     * //  [2]=>
     * //  string(1) "b"
     * //}
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public function sMembers($key) {}

    /**
     * Check if one or more values are members of a set.
     *
     * @link https://redis.io/commands/smismember
     * @see smember()
     *
     * @param string $key              The set to query.
     * @param string $member           The first value to test if exists in the set.
     * @param string ...$other_members Any number of additional values to check.
     *
     * @return Redis|array|false An array of integers representing whether each passed value was a member of the set.
     *
     * @example
     * $redis->sAdd('ds9-crew', ...["Sisko", "Kira", "Dax", "Worf", "Bashir", "O'Brien"]);
     * $members = $redis->sMIsMember('ds9-crew', ...['Sisko', 'Picard', 'Data', 'Worf']);
     */
    public function sMisMember(string $key, string $member, string ...$other_members): array|false {}

    /**
     * @link    https://redis.io/commands/smembers
     *
     * @param  string  $key
     * @return array|Redis   An array of elements, the contents of the set or Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->sMembers(%parametersList%)')]
    public function sGetMembers($key) {}

    /**
     * Scan a set for members
     *
     * @param string $key         The Redis SET key in question.
     * @param int|null &$iterator A reference to an iterator which should be initialized to NULL that
     *                            PhpRedis will update with the value returned from Redis after each
     *                            subsequent call to SSCAN.  Once this cursor is zero you know all
     *                            members have been traversed.
     * @param string $pattern     An optional glob style pattern to match against, so Redis only
     *                            returns the subset of members matching this pattern.
     * @param int    $count       A hint to Redis as to how many members it should scan in one command
     *                            before returning members for that iteration.
     *
     * @return array|false|Redis PHPRedis will return an array of keys or FALSE when we're done iterating or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sscan
     * @example
     * <pre>
     * $redis->del('myset');
     * for ($i = 0; $i < 10000; $i++) {
     *     $redis->sAdd('myset', "member:$i");
     * }
     * $redis->sadd('myset', 'foofoo');
     *
     * $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_NORETRY);
     *
     * $scanned = 0;
     * $it = null;
     *
     * // Without Redis::SCAN_RETRY we may receive empty results and
     * // a nonzero iterator.
     * do {
     *     // Scan members containing '5'
     *     $members = $redis->sscan('myset', $it, '*5*');
     *     foreach ($members as $member) {
     *          echo "NORETRY: $member\n";
     *          $scanned++;
     *     }
     * } while ($it != 0);
     * echo "TOTAL: $scanned\n";
     *
     * $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
     *
     * $scanned = 0;
     * $it = null;
     *
     * // With Redis::SCAN_RETRY PhpRedis will never return an empty array
     * // when the cursor is non-zero
     * while (($members = $redis->sScan('set', $it, '*5*'))) {
     *     foreach ($members as $member) {
     *         echo "RETRY: $member\n";
     *         $scanned++;
     *     }
     * }
     * </pre>
     */
    public function sScan($key, &$iterator, $pattern = null, $count = 0) {}

    /**
     * Sets a value and returns the previous entry at that key.
     *
     * @param string       $key
     * @param mixed        $value
     *
     * @return string|mixed|false|Redis A string (mixed, if used serializer), the previous value located at this key or false if it didn't exist or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/getset
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $exValue = $redis->getSet('x', 'lol');   // return '42', replaces x by 'lol'
     * $newValue = $redis->get('x')'            // return 'lol'
     * </pre>
     */
    public function getSet($key, $value) {}

    /**
     * Returns a random key
     *
     * @return string|false|Redis an existing key in redis or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/randomkey
     * @example
     * <pre>
     * $key = $redis->randomKey();
     * $surprise = $redis->get($key);  // who knows what's in there.
     * </pre>
     */
    public function randomKey() {}

    /**
     * Switches to a given database
     *
     * @param int $dbIndex
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/select
     * @example
     * <pre>
     * $redis->select(0);       // switch to DB 0
     * $redis->set('x', '42');  // write 42 to x
     * $redis->move('x', 1);    // move to DB 1
     * $redis->select(1);       // switch to DB 1
     * $redis->get('x');        // will return 42
     * </pre>
     */
    public function select($dbIndex) {}

    /**
     * Moves a key to a different database.
     *
     * @param string $key
     * @param int    $dbIndex
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/move
     * @example
     * <pre>
     * $redis->select(0);       // switch to DB 0
     * $redis->set('x', '42');  // write 42 to x
     * $redis->move('x', 1);    // move to DB 1
     * $redis->select(1);       // switch to DB 1
     * $redis->get('x');        // will return 42
     * </pre>
     */
    public function move($key, $dbIndex) {}

    /**
     * Renames a key
     *
     * @param string $srcKey
     * @param string $dstKey
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/rename
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $redis->rename('x', 'y');
     * $redis->get('y');   // → 42
     * $redis->get('x');   // → `FALSE`
     * </pre>
     */
    public function rename($srcKey, $dstKey) {}

    /**
     * @link    https://redis.io/commands/rename
     *
     * @param   string  $srcKey
     * @param   string  $dstKey
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->rename(%parametersList%)')]
    public function renameKey($srcKey, $dstKey) {}

    /**
     * Renames a key
     *
     * Same as rename, but will not replace a key if the destination already exists.
     * This is the same behaviour as setnx.
     *
     * @param string $srcKey
     * @param string $dstKey
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/renamenx
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $redis->rename('x', 'y');
     * $redis->get('y');   // → 42
     * $redis->get('x');   // → `FALSE`
     * </pre>
     */
    public function renameNx($srcKey, $dstKey) {}

    /**
     * Sets an expiration in seconds on the key in question.  If connected to
     * redis-server >= 7.0.0 you may send an additional "mode" argument which
     * modifies how the command will execute.
     *
     * @param string $key The key to set an expiration on.
     * @param int    $ttl The key's remaining Time To Live, in seconds
     * @param string|null $mode A two character modifier that changes how the command works.
     *                      NX - Set expiry only if key has no expiry
     *                      XX - Set expiry only if key has an expiry
     *                      LT - Set expiry only when new expiry is < current expiry
     *                      GT - Set expiry only when new expiry is > current expiry
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/expire
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $redis->expire('x', 3);  // x will disappear in 3 seconds.
     * sleep(5);                    // wait 5 seconds
     * $redis->get('x');            // will return `FALSE`, as 'x' has expired.
     * </pre>
     */
    public function expire($key, $ttl, $mode = null) {}

    /**
     * Sets an expiration date (a timeout in milliseconds) on an item
     *
     * If connected to Redis >= 7.0.0 you can pass an optional mode argument that modifies how the command will execute.
     *
     * @param string $key       The key to set an expiration on.
     * @param int    $ttl       The key's remaining Time To Live, in milliseconds
     * @param string|null $mode A two character modifier that changes how the command works.
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/pexpire
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $redis->pExpire('x', 11500); // x will disappear in 11500 milliseconds.
     * $redis->ttl('x');            // 12
     * $redis->pttl('x');           // 11500
     * </pre>
     */
    public function pExpire($key, $ttl, $mode = null) {}

    /**
     * @link    https://redis.io/commands/expire
     *
     * @param   string  $key
     * @param   int     $ttl
     * @return  bool|Redis returns Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->expire(%parametersList%)')]
    public function setTimeout($key, $ttl) {}

    /**
     * Sets an expiration date (a timestamp) on an item.
     *
     * If connected to Redis >= 7.0.0 you can pass an optional 'mode' argument.
     * @see expire() For a description of the mode argument.
     *
     * @param string $key       The key to set an expiration on.
     * @param int    $timestamp The unix timestamp to expire at.
     * @param string|null $mode An option 'mode' that modifies how the command acts
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/expireat
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $now = time(NULL);               // current timestamp
     * $redis->expireAt('x', $now + 3); // x will disappear in 3 seconds.
     * sleep(5);                        // wait 5 seconds
     * $redis->get('x');                // will return `FALSE`, as 'x' has expired.
     * </pre>
     */
    public function expireAt($key, $timestamp, $mode = null) {}

    /**
     * Sets an expiration date (a timestamp) on an item. Requires a timestamp in milliseconds
     *
     * If connected to Redis >= 7.0.0 you can pass an optional 'mode' argument.
     *
     * @param string $key       The key to set an expiration on.
     * @param int    $timestamp Unix timestamp. The key's date of death, in seconds from Epoch time
     * @param string|null $mode A two character modifier that changes how the command works.
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/pexpireat
     * @example
     * <pre>
     * $redis->set('x', '42');
     * $redis->pExpireAt('x', 1555555555005);
     * echo $redis->ttl('x');                       // 218270121
     * echo $redis->pttl('x');                      // 218270120575
     * </pre>
     */
    public function pExpireAt($key, $timestamp, $mode = null) {}

    /**
     * Returns the keys that match a certain pattern.
     *
     * @param string $pattern pattern, using '*' as a wildcard
     *
     * @return array|false|Redis The keys that match a certain pattern or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/keys
     * @example
     * <pre>
     * $allKeys = $redis->keys('*');   // all keys will match this.
     * $keyWithUserPrefix = $redis->keys('user*');
     * </pre>
     */
    public function keys($pattern) {}

    /**
     * @param string $pattern
     *
     * @throws RedisException
     * @link    https://redis.io/commands/keys
     */
    #[Deprecated(replacement: '%class%->keys(%parametersList%)')]
    public function getKeys($pattern) {}

    /**
     * Returns the current database's size
     *
     * @return false|int|Redis DB size, in number of keys or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/dbsize
     * @example
     * <pre>
     * $count = $redis->dbSize();
     * echo "Redis has $count keys\n";
     * </pre>
     */
    public function dbSize() {}

    /**
     * Authenticate a Redis connection after its been established.
     * Warning: The password is sent in plain-text over the network.
     *
     * @param mixed $credentials A string password, or an array with one or two string elements.
     *
     * @return bool|Redis TRUE if the connection is authenticated, FALSE otherwise or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/auth
     *
     * @example
     * $redis->auth('password');
     * $redis->auth(['password']);
     * $redis->auth(['username', 'password']);
     */
    public function auth($credentials) {}

    /**
     * Starts the background rewrite of AOF (Append-Only File)
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/bgrewriteaof
     * @example $redis->bgrewriteaof();
     */
    public function bgrewriteaof() {}

    /**
     * Changes the slave status
     * Either host and port, or no parameter to stop being a slave.
     *
     * This method and the corresponding command in Redis has been marked deprecated
     * and users should instead use replicaof() if connecting to redis-server >= 5.0.0.
     *
     * @param string $host [optional]
     * @param int    $port [optional]
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/slaveof
     * @example
     * <pre>
     * $redis->slaveof('10.0.1.7', 6379);
     * // ...
     * $redis->slaveof();
     * </pre>
     */
    #[Deprecated(replacement: '%class%->replicaof(%parametersList%)')]
    public function slaveof($host = '127.0.0.1', $port = 6379) {}

    /**
     * Access the Redis slowLog
     *
     * @param string $operation The operation you wish to perform. This can be one of the following values:
     *                          'GET'   - Retrieve the Redis slowlog as an array.
     *                          'LEN'   - Retrieve the length of the slowlog.
     *                          'RESET' - Remove all slowlog entries.
     * @param int    $length    This optional argument can be passed when operation
     *                          is 'get' and will specify how many elements to retrieve.
     *                          If omitted Redis will send up to a default number of
     *                          entries, which is configurable.
     *
     *                          Note:  With Redis >= 7.0.0 you can send -1 to mean "all".
     *
     * @return mixed|Redis The return value of SLOWLOG will depend on which operation was performed or Redis if in multimode
     * - SLOWLOG GET: Array of slowLog entries, as provided by Redis
     * - SLOGLOG LEN: Integer, the length of the slowLog
     * - SLOWLOG RESET: Boolean, depending on success
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * // Get ten slowLog entries
     * $redis->slowLog('get', 10);
     * // Get the default number of slowLog entries
     *
     * $redis->slowLog('get');
     * // Reset our slowLog
     * $redis->slowLog('reset');
     *
     * // Retrieve slowLog length
     * $redis->slowLog('len');
     * </pre>
     *
     * @link https://redis.io/commands/slowlog
     */
    public function slowLog(string $operation, int $length = 0) {}

    /**
     * Describes the object pointed to by a key.
     * The information to retrieve (string) and the key (string).
     * Info can be one of the following:
     * - "encoding"
     * - "refcount"
     * - "idletime"
     *
     * @param string $subcommand
     * @param string $key
     *
     * @return string|int|false|Redis for "encoding", int for "refcount" and "idletime", FALSE if the key doesn't exist or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/object
     * @example
     * <pre>
     * $redis->lPush('l', 'Hello, world!');
     * $redis->object("encoding", "l"); // → ziplist
     * $redis->object("refcount", "l"); // → 1
     * $redis->object("idletime", "l"); // → 400 (in seconds, with a precision of 10 seconds).
     * </pre>
     */
    public function object($subcommand, $key) {}

    /**
     * Performs a synchronous save.
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     * If a save is already running, this command will fail and return FALSE.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/save
     * @example $redis->save();
     */
    public function save() {}

    /**
     * Performs a background save.
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     * If a save is already running, this command will fail and return FALSE
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/bgsave
     * @example $redis->bgSave();
     */
    public function bgSave() {}

    /**
     * Returns the timestamp of the last disk save.
     *
     * @return false|int|Redis timestamp or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/lastsave
     * @example $redis->lastSave();
     */
    public function lastSave() {}

    /**
     * Blocks the current client until all the previous write commands are successfully transferred and
     * acknowledged by at least the specified number of slaves.
     *
     * @param int $numreplicas The number of replicas we want to confirm write operaions
     * @param int $timeout     How long to wait (zero meaning forever).
     *
     * @return int|false|Redis The command returns the number of slaves reached by all the writes performed in the or Redis if in multimode
     *              context of the current connection
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/wait
     * @example $redis->wait(2, 1000);
     */
    public function wait($numreplicas, $timeout) {}

    /**
     * Returns the type of data pointed by a given key.
     *
     * @param string $key
     *
     * @return false|int|Redis returns Redis if in multimode
     * Depending on the type of the data pointed by the key,
     * this method will return the following value:
     * - string: Redis::REDIS_STRING
     * - set:   Redis::REDIS_SET
     * - list:  Redis::REDIS_LIST
     * - zset:  Redis::REDIS_ZSET
     * - hash:  Redis::REDIS_HASH
     * - stream: Redis::REDIS_STREAM
     * - other: Redis::REDIS_NOT_FOUND
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/type
     * @example $redis->type('key');
     */
    public function type(string $key) {}

    /**
     * Append specified string to the string stored in specified key.
     *
     * @param string       $key
     * @param mixed        $value
     *
     * @return false|int|Redis Size of the value after the append or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/append
     * @example
     * <pre>
     * $redis->set('key', 'value1');
     * $redis->append('key', 'value2'); // 12
     * $redis->get('key');              // 'value1value2'
     * </pre>
     */
    public function append($key, $value) {}

    /**
     * Return a substring of a larger string
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @return string|Redis the substring or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/getrange
     * @example
     * <pre>
     * $redis->set('key', 'string value');
     * $redis->getRange('key', 0, 5);   // 'string'
     * $redis->getRange('key', -5, -1); // 'value'
     * </pre>
     */
    public function getRange($key, $start, $end) {}

    /**
     * Return a substring of a larger string
     *
     * @param   string  $key
     * @param   int     $start
     * @param   int     $end
     *
     * @throws RedisException
     */
    #[Deprecated]
    public function substr($key, $start, $end) {}

    /**
     * Changes a substring of a larger string.
     *
     * @param string $key
     * @param int    $offset
     * @param string $value
     *
     * @return false|int|Redis the length of the string after it was modified or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/setrange
     * @example
     * <pre>
     * $redis->set('key', 'Hello world');
     * $redis->setRange('key', 6, "redis"); // returns 11
     * $redis->get('key');                  // "Hello redis"
     * </pre>
     */
    public function setRange($key, $offset, $value) {}

    /**
     * Get the length of a string value.
     *
     * @param string $key
     * @return false|int|Redis The length of the string key if it exists, zero if it does not, and false on failure.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/strlen
     * @example
     * <pre>
     * $redis->set('key', 'value');
     * $redis->strlen('key'); // 5
     * </pre>
     */
    public function strlen($key) {}

    /**
     * Return the position of the first bit set to 1 or 0 in a string. The position is returned, thinking of the
     * string as an array of bits from left to right, where the first byte's most significant bit is at position 0,
     * the second byte's most significant bit is at position 8, and so forth.
     *
     * @param string $key   The key to check (must be a string)
     * @param bool   $bit   Whether to look for an unset (0) or set (1) bit.
     * @param int    $start Where in the string to start looking.
     * @param int    $end   Where in the string to stop looking.
     * @param bool   $bybit If true, Redis will treat $start and $end as BIT values and not bytes, so if start was 0 and end was 2, Redis would only search the first two bits.
     *
     * @return false|int|Redis The command returns the position of the first bit set to 1 or 0 according to the request or Redis if in multimode
     * If we look for set bits (the bit argument is 1) and the string is empty or composed of just
     * zero bytes, -1 is returned. If we look for clear bits (the bit argument is 0) and the string
     * only contains bit set to 1, the function returns the first bit not part of the string on the
     * right. So if the string is three bytes set to the value 0xff the command BITPOS key 0 will
     * return 24, since up to bit 23 all the bits are 1. Basically, the function considers the right
     * of the string as padded with zeros if you look for clear bits and specify no range or the
     * start argument only. However, this behavior changes if you are looking for clear bits and
     * specify a range with both start and end. If no clear bit is found in the specified range, the
     * function returns -1 as the user specified a clear range and there are no 0 bits in that range.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/bitpos
     * @example
     * <pre>
     * $redis->set('key', '\xff\xff');
     * $redis->bitpos('key', 1); // int(0)
     * $redis->bitpos('key', 1, 1); // int(8)
     * $redis->bitpos('key', 1, 3); // int(-1)
     * $redis->bitpos('key', 0); // int(16)
     * $redis->bitpos('key', 0, 1); // int(16)
     * $redis->bitpos('key', 0, 1, 5); // int(-1)
     * </pre>
     */
    public function bitpos($key, $bit, $start = 0, $end = -1, $bybit = false) {}

    /**
     * Return a single bit out of a larger string
     *
     * @param string $key
     * @param int    $offset
     *
     * @return false|int|Redis the bit value (0 or 1) or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/getbit
     * @example
     * <pre>
     * $redis->set('key', "\x7f");  // this is 0111 1111
     * $redis->getBit('key', 0);    // 0
     * $redis->getBit('key', 1);    // 1
     * </pre>
     */
    public function getBit($key, $offset) {}

    /**
     * Changes a single bit of a string.
     *
     * @param string   $key
     * @param int      $offset
     * @param bool|int $value  bool or int (1 or 0)
     *
     * @return false|int|Redis 0 or 1, the value of the bit before it was set or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/setbit
     * @example
     * <pre>
     * $redis->set('key', "*");     // ord("*") = 42 = 0x2f = "0010 1010"
     * $redis->setBit('key', 5, 1); // returns 0
     * $redis->setBit('key', 7, 1); // returns 0
     * $redis->get('key');          // chr(0x2f) = "/" = b("0010 1111")
     * </pre>
     */
    public function setBit($key, $offset, $value) {}

    /**
     * Count bits in a string
     *
     * @param string $key The key in question (must be a string key)
     * @param int $start The index where Redis should start counting. If omitted it defaults to zero, which means the start of the string.
     * @param int $end The index where Redis should stop counting. If omitted it defaults to -1, meaning the very end of the string.
     * @param bool $bybit Whether or not Redis should treat $start and $end as bit positions, rather than bytes.
     *
     * @return false|int|Redis The number of bits set to 1 in the value behind the input key or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/bitcount
     * @example
     * <pre>
     * $redis->set('bit', '345'); // // 11 0011  0011 0100  0011 0101
     * var_dump( $redis->bitCount('bit', 0, 0) ); // int(4)
     * var_dump( $redis->bitCount('bit', 1, 1) ); // int(3)
     * var_dump( $redis->bitCount('bit', 2, 2) ); // int(4)
     * var_dump( $redis->bitCount('bit', 0, 2) ); // int(11)
     * </pre>
     */
    public function bitCount($key, $start = 0, $end = -1, $bybit = false) {}

    /**
     * Bitwise operation on multiple keys.
     *
     * @param string $operation    either "AND", "OR", "NOT", "XOR"
     * @param string $retKey       return key
     * @param string $key1         first key
     * @param string ...$otherKeys variadic list of keys
     *
     * @return false|int|Redis The size of the string stored in the destination key or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/bitop
     * @example
     * <pre>
     * $redis->set('bit1', '1'); // 11 0001
     * $redis->set('bit2', '2'); // 11 0010
     *
     * $redis->bitOp('AND', 'bit', 'bit1', 'bit2'); // bit = 110000
     * $redis->bitOp('OR',  'bit', 'bit1', 'bit2'); // bit = 110011
     * $redis->bitOp('NOT', 'bit', 'bit1', 'bit2'); // bit = 110011
     * $redis->bitOp('XOR', 'bit', 'bit1', 'bit2'); // bit = 11
     * </pre>
     */
    public function bitOp($operation, $retKey, $key1, ...$otherKeys) {}

    /**
     * Removes all entries from the current database.
     *
     * @param bool|null $async Whether to perform the task in a blocking or non-blocking way. Requires server version 4.0.0 or greater
     *
     * @return bool|Redis Always TRUE or Redis if in multimode
     * @throws RedisException
     * @link    https://redis.io/commands/flushdb
     * @example $redis->flushDB();
     */
    public function flushDB($async = null) {}

    /**
     * Removes all entries from all databases.
     *
     * @param bool|null $async Whether to perform the task in a blocking or non-blocking way. Requires server version 4.0.0 or greater
     *
     * @return bool|Redis Always TRUE or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/flushall
     * @example $redis->flushAll();
     */
    public function flushAll($async = null) {}

    /**
     * Sort the contents of a Redis key in various ways.
     *
     * @param string $key     The key you wish to sort
     * @param array|null $options Various options controlling how you would like the data sorted.
     *                        See blow for a detailed description of this options array.
     *                        'SORT'  => 'ASC'|| 'DESC' // Sort in descending or descending order.
     *                        'ALPHA' => true || false  // Whether to sort alphanumerically.
     *                        'LIMIT' => [0, 10]        // Return a subset of the data at offset, count
     *                        'BY'    => 'weight_*'     // For each element in the key, read data from the
     *                                                     external key weight_* and sort based on that value.
     *                        'GET'   => 'weight_*'     // For each element in the source key, retrieve the
     *                                                     data from key weight_* and return that in the result
     *                                                     rather than the source keys' element.  This can
     *                                                     be used in combination with 'BY'
     *
     * @return mixed This command can either return an array with the sorted data or the
     * number of elements placed in a destination set when using the STORE option.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sort
     * @example
     * <pre>
     * $redis->del('s');
     * $redis->sadd('s', 5);
     * $redis->sadd('s', 4);
     * $redis->sadd('s', 2);
     * $redis->sadd('s', 1);
     * $redis->sadd('s', 3);
     *
     * var_dump($redis->sort('s')); // 1,2,3,4,5
     * var_dump($redis->sort('s', array('sort' => 'desc'))); // 5,4,3,2,1
     * var_dump($redis->sort('s', array('sort' => 'desc', 'store' => 'out'))); // (int)5
     * </pre>
     */
    public function sort($key, $options = null) {}

    /**
     * Returns an associative array of strings and integers
     *
     * If connected to Redis server >= 7.0.0 you may pass multiple optional sections.
     *
     * @param string ...$sections Optional section(s) you wish Redis server to return.
     * SERVER | CLIENTS | MEMORY | PERSISTENCE | STATS | REPLICATION | CPU | CLUSTER | KEYSPACE | COMMANDSTATS
     *
     * Returns an associative array of strings and integers, with the following keys:
     * - redis_version
     * - redis_git_sha1
     * - redis_git_dirty
     * - arch_bits
     * - multiplexing_api
     * - process_id
     * - uptime_in_seconds
     * - uptime_in_days
     * - lru_clock
     * - used_cpu_sys
     * - used_cpu_user
     * - used_cpu_sys_children
     * - used_cpu_user_children
     * - connected_clients
     * - connected_slaves
     * - client_longest_output_list
     * - client_biggest_input_buf
     * - blocked_clients
     * - used_memory
     * - used_memory_human
     * - used_memory_peak
     * - used_memory_peak_human
     * - mem_fragmentation_ratio
     * - mem_allocator
     * - loading
     * - aof_enabled
     * - changes_since_last_save
     * - bgsave_in_progress
     * - last_save_time
     * - total_connections_received
     * - total_commands_processed
     * - expired_keys
     * - evicted_keys
     * - keyspace_hits
     * - keyspace_misses
     * - hash_max_zipmap_entries
     * - hash_max_zipmap_value
     * - pubsub_channels
     * - pubsub_patterns
     * - latest_fork_usec
     * - vm_enabled
     * - role
     *
     * @return array|false|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/info
     * @example
     * <pre>
     * $redis->info();
     *
     * or
     *
     * $redis->info("COMMANDSTATS"); //Information on the commands that have been run (>=2.6 only)
     * $redis->info("CPU"); // just CPU information from Redis INFO
     * </pre>
     */
    public function info(...$sections) {}

    /**
     * Returns an indexed array whose first element is the role
     *
     * @return mixed|Redis Will return an array with the role of the connected instance unless there is an error.  returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/role
     * @example
     * <pre>
     * $redis->role();
     * </pre>
     */
    public function role() {}

    /**
     * Resets the statistics reported by Redis using the INFO command (`info()` function).
     * These are the counters that are reset:
     *      - Keyspace hits
     *      - Keyspace misses
     *      - Number of commands processed
     *      - Number of connections received
     *      - Number of expired keys
     *
     * @return bool|Redis `TRUE` in case of success, `FALSE` in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @example $redis->resetStat();
     * @link https://redis.io/commands/config-resetstat
     */
    #[Deprecated(replacement: "%class%->rawCommand('CONFIG', 'RESETSTAT')")]
    public function resetStat() {}

    /**
     * Returns the time to live left for a given key, in seconds. If the key doesn't exist, FALSE is returned.
     *
     * @param string $key
     *
     * @return int|bool|Redis the time left to live in seconds or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/ttl
     * @example
     * <pre>
     * $redis->setex('key', 123, 'test');
     * $redis->ttl('key'); // int(123)
     * </pre>
     */
    public function ttl($key) {}

    /**
     * Returns a time to live left for a given key, in milliseconds.
     *
     * If the key doesn't exist, FALSE is returned.
     *
     * @param string $key
     *
     * @return int|bool|Redis the time left to live in milliseconds or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/pttl
     * @example
     * <pre>
     * $redis->setex('key', 123, 'test');
     * $redis->pttl('key'); // int(122999)
     * </pre>
     */
    public function pttl($key) {}

    /**
     * Remove the expiration timer from a key.
     *
     * @param string $key
     *
     * @return bool|Redis TRUE if a timeout was removed, FALSE if the key didn’t exist or didn’t have an expiration timer or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/persist
     * @example $redis->persist('key');
     */
    public function persist($key) {}

    /**
     * Sets multiple key-value pairs in one atomic command.
     * MSETNX only returns TRUE if all the keys were set (see SETNX).
     *
     * @param array<string, string> $array Pairs: array(key => value, ...)
     *
     * @return bool|Redis TRUE in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/mset
     * @example
     * <pre>
     * $redis->mSet(array('key0' => 'value0', 'key1' => 'value1'));
     * var_dump($redis->get('key0'));
     * var_dump($redis->get('key1'));
     * // Output:
     * // string(6) "value0"
     * // string(6) "value1"
     * </pre>
     */
    public function mSet($array) {}

    /**
     * Get the values of all the specified keys.
     * If one or more keys dont exist, the array will contain FALSE at the position of the key.
     *
     * @param array $keys Array containing the list of the keys
     *
     * @return array|Redis Array containing the values related to keys in argument or Redis if in multimode
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * $redis->set('key1', 'value1');
     * $redis->set('key2', 'value2');
     * $redis->set('key3', 'value3');
     * $redis->getMultiple(array('key1', 'key2', 'key3')); // array('value1', 'value2', 'value3');
     * $redis->getMultiple(array('key0', 'key1', 'key5')); // array(`FALSE`, 'value2', `FALSE`);
     * </pre>
     */
    #[Deprecated(replacement: '%class%->mGet(%parametersList%)')]
    public function getMultiple(array $keys) {}

    /**
     * Returns the values of all specified keys.
     *
     * For every key that does not hold a string value or does not exist,
     * the special value false is returned. Because of this, the operation never fails.
     *
     * @param array $array
     *
     * @return false|list<false|string>|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/mget
     * @example
     * <pre>
     * $redis->del('x', 'y', 'z', 'h');  // remove x y z
     * $redis->mset(array('x' => 'a', 'y' => 'b', 'z' => 'c'));
     * $redis->hset('h', 'field', 'value');
     * var_dump($redis->mget(array('x', 'y', 'z', 'h')));
     * // Output:
     * // array(3) {
     * //   [0]=> string(1) "a"
     * //   [1]=> string(1) "b"
     * //   [2]=> string(1) "c"
     * //   [3]=> bool(false)
     * // }
     * </pre>
     */
    public function mGet(array $array) {}

    /**
     * Set one ore more string keys but only if none of the key exist.
     *
     * @see mSet()
     *
     * @param array<string, string> $array
     * @return false|int|Redis 1 (if the keys were set) or 0 (no key was set) or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/msetnx
     * @example $redis->msetnx(['foo' => 'bar', 'baz' => 'bop']);
     */
    public function msetnx(array $array) {}

    /**
     * Pops a value from the tail of a list, and pushes it to the front of another list.
     * Also return this value.
     *
     * @since   redis >= 1.1
     *
     * @param string $srcKey
     * @param string $dstKey
     *
     * @return string|mixed|false|Redis The element that was moved in case of success, FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/rpoplpush
     * @example
     * <pre>
     * $redis->del('x', 'y');
     *
     * $redis->lPush('x', 'abc');
     * $redis->lPush('x', 'def');
     * $redis->lPush('y', '123');
     * $redis->lPush('y', '456');
     *
     * // move the last of x to the front of y.
     * var_dump($redis->rpoplpush('x', 'y'));
     * var_dump($redis->lRange('x', 0, -1));
     * var_dump($redis->lRange('y', 0, -1));
     *
     * //Output:
     * //
     * //string(3) "abc"
     * //array(1) {
     * //  [0]=>
     * //  string(3) "def"
     * //}
     * //array(3) {
     * //  [0]=>
     * //  string(3) "abc"
     * //  [1]=>
     * //  string(3) "456"
     * //  [2]=>
     * //  string(3) "123"
     * //}
     * </pre>
     */
    public function rPopLPush($srcKey, $dstKey) {}

    /**
     * A blocking version of rPopLPush, with an integral timeout in the third parameter.
     *
     * @param string $srcKey
     * @param string $dstKey
     * @param int|float $timeout The number of seconds to wait. Note that you must be connected to Redis >= 6.0.0 to send a floating point timeout.
     *
     * @return  string|mixed|bool|Redis  The element that was moved in case of success, FALSE in case of timeout or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/brpoplpush
     */
    public function bRPopLPush($srcKey, $dstKey, $timeout) {}

    /**
     * Adds the specified member with a given score to the sorted set stored at key
     *
     * @param string      $key              The sorted set in question.
     * @param array|float $score_or_options Either the score for the first element, or an array of options.
     *                                      'NX',       # Only update elements that already exist
     *                                      'NX',       # Only add new elements but don't update existing ones.
     *                                      'LT'        # Only update existing elements if the new score is
     *                                                  # less than the existing one.
     *                                      'GT'        # Only update existing elements if the new score is
     *                                                  # greater than the existing one.
     *                                      'CH'        # Instead of returning the number of elements added,
     *                                                  # Redis will return the number Of elements that were
     *                                                  # changed in the operation.
     *                                      'INCR'      # Instead of setting each element to the provide score,
     *                                      # increment the element by the
     *                                      # provided score, much like ZINCRBY.  When this option
     *                                      # is passed, you may only send a single score and member.
     *
     *                                     Note:  'GX', 'LT', and 'NX' cannot be passed together, and PhpRedis
     *                                            will send whichever one is last in the options array.
     *
     * @param mixed $more_scores_and_mems       A variadic number of additional scores and members.
     *
     * @return false|int|Redis Number of values added or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zadd
     * @example
     * <pre>
     * $redis->zAdd('z', 1, 'v1', 2, 'v2', 3, 'v3', 4, 'v4' );  // int(2)
     * $redis->zRem('z', 'v2', 'v3');                           // int(2)
     * $redis->zAdd('z', ['NX'], 5, 'v5');                      // int(1)
     * $redis->zAdd('z', ['NX'], 6, 'v5');                      // int(0)
     * $redis->zAdd('z', 7, 'v6');                              // int(1)
     * $redis->zAdd('z', 8, 'v6');                              // int(0)
     *
     * var_dump( $redis->zRange('z', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v4"
     * //   [2]=> string(2) "v5"
     * //   [3]=> string(2) "v8"
     * // }
     *
     * var_dump( $redis->zRange('z', 0, -1, true) );
     * // Output:
     * // array(4) {
     * //   ["v1"]=> float(1)
     * //   ["v4"]=> float(4)
     * //   ["v5"]=> float(5)
     * //   ["v6"]=> float(8)
     * </pre>
     */
    public function zAdd($key, $score_or_options, ...$more_scores_and_mems) {}

    /**
     * Returns a range of elements from the ordered set stored at the specified key,
     * with values in the range [start, end]. start and stop are interpreted as zero-based indices:
     * 0 the first element,
     * 1 the second ...
     * -1 the last element,
     * -2 the penultimate ...
     *
     * @param string          $key   The sorted set in question.
     * @param string|int      $start The starting index we want to return.
     * @param string|int      $end   The final index we want to return.
     *
     * @param array|bool|null $options This value may either be an array of options to pass to
     *                                 the command, or for historical purposes a boolean which
     *                                 controls just the 'WITHSCORES' option.
     *                                 'WITHSCORES' => true,     # Return both scores and members.
     *                                 'LIMIT'      => [10, 10], # Start at offset 10 and return 10 elements.
     *                                 'REV'                     # Return the elements in reverse order
     *                                 'BYSCORE',                # Treat `start` and `end` as scores instead
     *                                 'BYLEX'                   # Treat `start` and `end` as lexicographical values.

     *                                 Note:  'BYLEX' and 'BYSCORE' are mutually exclusive.
     *
     * @return array|Redis Array containing the values in specified range or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zrange
     * @example
     * <pre>
     * $redis->zAdd('key1', 0, 'val0');
     * $redis->zAdd('key1', 2, 'val2');
     * $redis->zAdd('key1', 10, 'val10');
     * $redis->zRange('key1', 0, -1); // array('val0', 'val2', 'val10')
     * // with scores
     * $redis->zRange('key1', 0, -1, true); // array('val0' => 0, 'val2' => 2, 'val10' => 10)
     * </pre>
     */
    public function zRange($key, $start, $end, $options = null) {}

    /**
     * Deletes a specified member from the ordered set.
     *
     * @param string       $key
     * @param string|mixed $member1
     * @param string|mixed ...$otherMembers
     *
     * @return false|int|Redis Number of deleted values or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zrem
     * @example
     * <pre>
     * $redis->zAdd('z', 1, 'v1', 2, 'v2', 3, 'v3', 4, 'v4' );  // int(2)
     * $redis->zRem('z', 'v2', 'v3');                           // int(2)
     * var_dump( $redis->zRange('z', 0, -1) );
     * //// Output:
     * // array(2) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v4"
     * // }
     * </pre>
     */
    public function zRem($key, $member1, ...$otherMembers) {}

    /**
     * @link https://redis.io/commands/zrem
     *
     * @param string       $key
     * @param string|mixed $member1
     * @param string|mixed ...$otherMembers
     *
     * @return false|int|Redis Number of deleted values or Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->zRem(%parametersList%)')]
    public function zDelete($key, $member1, ...$otherMembers) {}

    /**
     * Returns the elements of the sorted set stored at the specified key in the range [start, end]
     * in reverse order. start and stop are interpretated as zero-based indices:
     * 0 the first element,
     * 1 the second ...
     * -1 the last element,
     * -2 the penultimate ...
     *
     * @param string $key    The sorted set in question.
     * @param int    $start  The index to start listing elements
     * @param int    $end    The index to stop listing elements.
     * @param mixed  $scores Whether or not Redis should also return each members score.
     *
     * @return array|Redis Array containing the values in specified range or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zrevrange
     * @example
     * <pre>
     * $redis->zAdd('key', 0, 'val0');
     * $redis->zAdd('key', 2, 'val2');
     * $redis->zAdd('key', 10, 'val10');
     * $redis->zRevRange('key', 0, -1); // array('val10', 'val2', 'val0')
     *
     * // with scores
     * $redis->zRevRange('key', 0, -1, true); // array('val10' => 10, 'val2' => 2, 'val0' => 0)
     * $redis->zRevRange('key', 0, -1, ['withscores' => true]);
     * </pre>
     */
    public function zRevRange($key, $start, $end, $scores = null) {}

    /**
     * Returns the elements of the sorted set stored at the specified key which have scores in the
     * range [start,end]. Adding a parenthesis before start or end excludes it from the range.
     * +inf and -inf are also valid limits.
     *
     * zRevRangeByScore returns the same items in reverse order, when the start and end parameters are swapped.
     *
     * @param string $key     The sorted set to query.
     * @param string $start   The minimum score of elements that Redis should return.
     * @param string $end     The maximum score of elements that Redis should return.
     * @param array  $options Options that change how Redis will execute the command.
     *
     *                        OPTION       TYPE            MEANING
     *                        'WITHSCORES' bool            Whether to also return scores.
     *                        'LIMIT'      [offset, count] Limit the reply to a subset of elements.
     *
     * @return array|false|Redis Array containing the values in specified range or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zrangebyscore
     * @example
     * <pre>
     * $redis->zAdd('key', 0, 'val0');
     * $redis->zAdd('key', 2, 'val2');
     * $redis->zAdd('key', 10, 'val10');
     * $redis->zRangeByScore('key', 0, 3);                                          // array('val0', 'val2')
     * $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE);              // array('val0' => 0, 'val2' => 2)
     * $redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1));                        // array('val2')
     * $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE, 'limit' => array(1, 1));  // array('val2' => 2)
     * </pre>
     */
    public function zRangeByScore($key, $start, $end, array $options = []) {}

    /**
     * List elements from a Redis sorted set by score, highest to lowest
     *
     * @param string $key     The sorted set to query.
     * @param string $start   The highest score to include in the results.
     * @param string $end     The lowest score to include in the results.
     * @param array  $options An options array that modifies how the command executes.
     *                        <code>
     *                        $options = [
     *                            'WITHSCORES' => true|false # Whether or not to return scores
     *                            'LIMIT' => [offset, count] # Return a subset of the matching members
     *                        ];
     *                        </code>
     *
     *                        NOTE:  For legacy reason, you may also simply pass `true` for the
     *                               options argument, to mean `WITHSCORES`.
     *
     * @return array|false|Redis returns Redis if in multimode
     *
     * @throws RedisException
     * @see zRangeByScore()
     *
     * @example
     * $redis->zadd('oldest-people', 122.4493, 'Jeanne Calment', 119.2932, 'Kane Tanaka',
     *                               119.2658, 'Sarah Knauss',   118.7205, 'Lucile Randon',
     *                               117.7123, 'Nabi Tajima',    117.6301, 'Marie-Louise Meilleur',
     *                               117.5178, 'Violet Brown',   117.3753, 'Emma Morano',
     *                               117.2219, 'Chiyo Miyako',   117.0740, 'Misao Okawa');
     *
     * $redis->zRevRangeByScore('oldest-people', 122, 119);
     * $redis->zRevRangeByScore('oldest-people', 'inf', 118);
     * $redis->zRevRangeByScore('oldest-people', '117.5', '-inf', ['LIMIT' => [0, 1]]);
     */
    public function zRevRangeByScore(string $key, string $start, string $end, array $options = []) {}

    /**
     * Returns a lexigraphical range of members in a sorted set, assuming the members have the same score. The
     * min and max values are required to start with '(' (exclusive), '[' (inclusive), or be exactly the values
     * '-' (negative inf) or '+' (positive inf).  The command must be called with either three *or* five
     * arguments or will return FALSE.
     *
     * @param  string  $key  The ZSET you wish to run against.
     * @param  string  $min  The minimum alphanumeric value you wish to get.
     * @param  string  $max  The maximum alphanumeric value you wish to get.
     * @param  int  $offset  Optional argument if you wish to start somewhere other than the first element.
     * @param  int  $count  An optional count to limit the replies to (used in conjunction with offset)
     *
     * @return array|false|Redis Array containing the values in the specified range or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zrangebylex
     * @example
     * <pre>
     * foreach (array('a', 'b', 'c', 'd', 'e', 'f', 'g') as $char) {
     *     $redis->zAdd('key', $char);
     * }
     *
     * $redis->zRangeByLex('key', '-', '[c'); // array('a', 'b', 'c')
     * $redis->zRangeByLex('key', '-', '(c'); // array('a', 'b')
     * $redis->zRangeByLex('key', '-', '[c'); // array('b', 'c')
     * </pre>
     */
    public function zRangeByLex(string $key, string $min, string $max, int $offset = -1, int $count = -1) {}

    /**
     * Retrieve the score of one or more members in a sorted set.
     *
     * @link https://redis.io/commands/zmscore
     *
     * @param string $key           The sorted set
     * @param mixed  $member        The first member to return the score from
     * @param mixed  $other_members One or more additional members to return the scores of.
     *
     * @return Redis|array|false An array of the scores of the requested elements.
     *
     * @example
     * $redis->zAdd('zs', 0, 'zero', 1, 'one', 2, 'two', 3, 'three');
     *
     * $redis->zMScore('zs', 'zero', 'two');
     * $redis->zMScore('zs', 'one', 'not-a-member');
     */
    public function zMscore(string $key, string $member, string ...$other_members): array|false {}

    /**
     * Pop one or more of the highest scoring elements from a sorted set.
     *
     * @param string $key     The sorted set to pop elements from.
     * @param int|null $count An optional count of elements to pop.
     *
     * @return Redis|array|false All of the popped elements with scores or false on fialure.
     *
     * @link https://redis.io/commands/zpopmax
     *
     * @example
     * $redis->zAdd('zs', 0, 'zero', 1, 'one', 2, 'two', 3, 'three');
     *
     * $redis->zPopMax('zs');
     * $redis->zPopMax('zs', 2);.
     */
    public function zPopMax(string $key, int $count = null): array|false {}

    /**
     * Pop one or more of the lowest scoring elements from a sorted set.
     *
     * @param string $key     The sorted set to pop elements from.
     * @param int|null $count An optional count of elements to pop.
     *
     * @return Redis|array|false The popped elements with their scores or false on failure.
     *
     * @link https://redis.io/commands/zpopmin
     *
     * @example
     * $redis->zAdd('zs', 0, 'zero', 1, 'one', 2, 'two', 3, 'three');
     *
     * $redis->zPopMin('zs');
     * $redis->zPopMin('zs', 2);
     */
    public function zPopMin(string $key, int $count = null): array|false {}

    /**
     * Retrieve one or more random members from a Redis sorted set.
     *
     * @param string $key     The sorted set to pull random members from.
     * @param array|null $options One or more options that determine exactly how the command operates.
     *
     *                        OPTION       TYPE     MEANING
     *                        'COUNT'      int      The number of random members to return.
     *                        'WITHSCORES' bool     Whether to return scores and members instead of
     *
     * @return Redis|string|array One ore more random elements.
     *
     * @see     https://redis.io/commands/zrandmember
     *
     * @example $redis->zRandMember('zs', ['COUNT' => 2, 'WITHSCORES' => true]);
     */
    public function zRandMember(string $key, array $options = null): string|array|false {}

    /**
     * List members of a Redis sorted set within a legographical range, in reverse order.
     *
     * @param string $key    The sorted set to list
     * @param string $min    The maximum legographical element to include in the result.
     * @param string $min    The minimum lexographical element to include in the result.
     * @param int    $offset An option offset within the matching elements to start at.
     * @param int    $count  An optional count to limit the replies to.
     *
     * @return false|array|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @see zRangeByLex()
     * @link    https://redis.io/commands/zrevrangebylex
     *
     * @example
     * $redis->zRevRangeByLex('captains', '[Q', '[J');
     * $redis->zRevRangeByLex('captains', '[Q', '[J', 1, 2);
     */
    public function zRevRangeByLex(string $key, string $min, string $max, int $offset = -1, int $count = -1) {}

    /**
     * Removes all elements in the sorted set stored at key between the lexicographical range specified by min and max.
     * Applies when all the elements in a sorted set are inserted with the same score, in order to force lexicographical ordering.
     *
     * @param string $key The sorted set to remove elements from.
     * @param string $min The start of the lexographical range to remove.
     * @param string $max The end of the lexographical range to remove
     *
     * @return int|false|Redis The number of elements removed
     *
     * @link    https://redis.io/commands/zremrangebylex
     *
     * @example
     * $redis->zRemRangeByLex('zs', '[a', '(b');
     * $redis->zRemRangeByLex('zs', '(banana', '(eggplant');
     */
    public function zRemRangeByLex(string $key, string $min, string $max) {}

    /**
     * Returns the number of elements of the sorted set stored at the specified key which have
     * scores in the range [start,end]. Adding a parenthesis before start or end excludes it
     * from the range. +inf and -inf are also valid limits.
     *
     * @param string $key
     * @param string $start
     * @param string $end
     *
     * @return false|int|Redis the size of a corresponding zRangeByScore or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zcount
     * @example
     * <pre>
     * $redis->zAdd('key', 0, 'val0');
     * $redis->zAdd('key', 2, 'val2');
     * $redis->zAdd('key', 10, 'val10');
     * $redis->zCount('key', 0, 3); // 2, corresponding to array('val0', 'val2')
     * </pre>
     */
    public function zCount($key, $start, $end) {}

    /**
     * Deletes the elements of the sorted set stored at the specified key which have scores in the range [start,end].
     *
     * @param string $key
     * @param string $start double or "+inf" or "-inf" as a string
     * @param string $end double or "+inf" or "-inf" as a string
     *
     * @return false|int|Redis The number of values deleted from the sorted set or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zremrangebyscore
     * @example
     * <pre>
     * $redis->zAdd('key', 0, 'val0');
     * $redis->zAdd('key', 2, 'val2');
     * $redis->zAdd('key', 10, 'val10');
     * $redis->zRemRangeByScore('key', '0', '3'); // 2
     * </pre>
     */
    public function zRemRangeByScore($key, $start, $end) {}

    /**
     * @param string $key
     * @param float  $start
     * @param float  $end
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->zRemRangeByScore(%parametersList%)')]
    public function zDeleteRangeByScore($key, $start, $end) {}

    /**
     * Deletes the elements of the sorted set stored at the specified key which have rank in the range [start,end].
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @return false|int|Redis The number of values deleted from the sorted set or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zremrangebyrank
     * @example
     * <pre>
     * $redis->zAdd('key', 1, 'one');
     * $redis->zAdd('key', 2, 'two');
     * $redis->zAdd('key', 3, 'three');
     * $redis->zRemRangeByRank('key', 0, 1); // 2
     * $redis->zRange('key', 0, -1, array('withscores' => TRUE)); // array('three' => 3)
     * </pre>
     */
    public function zRemRangeByRank($key, $start, $end) {}

    /**
     * @link    https://redis.io/commands/zremrangebyscore
     *
     * @param string $key
     * @param int    $start
     * @param int    $end
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->zRemRangeByRank(%parametersList%)')]
    public function zDeleteRangeByRank($key, $start, $end) {}

    /**
     * Returns the cardinality of an ordered set.
     *
     * @param string $key
     *
     * @return false|int|Redis the set's cardinality or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zsize
     * @example
     * <pre>
     * $redis->zAdd('key', 0, 'val0');
     * $redis->zAdd('key', 2, 'val2');
     * $redis->zAdd('key', 10, 'val10');
     * $redis->zCard('key');            // 3
     * </pre>
     */
    public function zCard($key) {}

    /**
     * Given one or more sorted set key names, return every element that is in the first
     * set but not any of the others.
     *
     * @param array $keys    One ore more sorted sets.
     * @param array|null $options An array which can contain ['WITHSCORES' => true] if you want Redis to return members and scores.
     *
     * @return Redis|array|false An array of members or false on failure.
     *
     * @link https://redis.io/commands/zdiff
     *
     * @example
     * $redis->zAdd('primes', 1, 'one', 3, 'three', 5, 'five');
     * $redis->zAdd('evens', 2, 'two', 4, 'four');
     * $redis->zAdd('mod3', 3, 'three', 6, 'six');
     *
     * $redis->zDiff(['primes', 'evens', 'mod3']);
     */
    public function zdiff(array $keys, array $options = null): array|false {}

    /**
     * @param string $key
     * @return false|int|Redis returns Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->zCard(%parametersList%)')]
    public function zSize($key) {}

    /**
     * Returns the score of a given member in the specified sorted set.
     *
     * @param string $key    The sorted set to query.
     * @param mixed  $member The member we wish to query.
     *
     * @return float|bool|Redis false if member or key not exists or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zscore
     * @example
     * <pre>
     * $redis->zAdd('key', 2.5, 'val2');
     * $redis->zScore('key', 'val2'); // 2.5
     * </pre>
     */
    public function zScore($key, $member) {}

    /**
     * Returns the rank of a given member in the specified sorted set, starting at 0 for the item
     * with the smallest score. zRevRank starts at 0 for the item with the largest score.
     *
     * @param string       $key
     * @param mixed        $member
     *
     * @return int|false|Redis the item's score, or false if key or member is not exists or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zrank
     * @example
     * <pre>
     * $redis->del('z');
     * $redis->zAdd('key', 1, 'one');
     * $redis->zAdd('key', 2, 'two');
     * $redis->zRank('key', 'one');     // 0
     * $redis->zRank('key', 'two');     // 1
     * $redis->zRevRank('key', 'one');  // 1
     * $redis->zRevRank('key', 'two');  // 0
     * </pre>
     */
    public function zRank($key, $member) {}

    /**
     * @see zRank()
     * @param string       $key
     * @param string|mixed $member
     *
     * @return int|false|Redis the item's score, false - if key or member is not exists or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link   https://redis.io/commands/zrevrank
     */
    public function zRevRank($key, $member) {}

    /**
     * Increments the score of a member from a sorted set by a given amount.
     *
     * @param string $key
     * @param float  $value (double) value that will be added to the member's score
     * @param mixed  $member
     *
     * @return float|Redis the new value or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zincrby
     * @example
     * <pre>
     * $redis->del('key');
     * $redis->zIncrBy('key', 2.5, 'member1');  // key or member1 didn't exist, so member1's score is to 0
     *                                          // before the increment and now has the value 2.5
     * $redis->zIncrBy('key', 1, 'member1');    // 3.5
     * </pre>
     */
    public function zIncrBy($key, $value, $member) {}

    /**
     * Creates an union of sorted sets given in second argument.
     * The result of the union will be stored in the sorted set defined by the first argument.
     * The third optionnel argument defines weights to apply to the sorted sets in input.
     * In this case, the weights will be multiplied by the score of each element in the sorted set
     * before applying the aggregation. The forth argument defines the AGGREGATE option which
     * specify how the results of the union are aggregated.
     *
     * @param string $output
     * @param array  $zSetKeys
     * @param null|array $weights
     * @param string|null $aggregateFunction  Either "SUM", "MIN", or "MAX": defines the behaviour to use on
     * duplicate entries during the zUnionStore
     *
     * @return false|int|Redis The number of values in the new sorted set or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zunionstore
     * @example
     * <pre>
     * $redis->del('k1');
     * $redis->del('k2');
     * $redis->del('k3');
     * $redis->del('ko1');
     * $redis->del('ko2');
     * $redis->del('ko3');
     *
     * $redis->zAdd('k1', 0, 'val0');
     * $redis->zAdd('k1', 1, 'val1');
     *
     * $redis->zAdd('k2', 2, 'val2');
     * $redis->zAdd('k2', 3, 'val3');
     *
     * $redis->zUnionStore('ko1', array('k1', 'k2')); // 4, 'ko1' => array('val0', 'val1', 'val2', 'val3')
     *
     * // Weighted zUnionStore
     * $redis->zUnionStore('ko2', array('k1', 'k2'), array(1, 1)); // 4, 'ko2' => array('val0', 'val1', 'val2', 'val3')
     * $redis->zUnionStore('ko3', array('k1', 'k2'), array(5, 1)); // 4, 'ko3' => array('val0', 'val2', 'val3', 'val1')
     * </pre>
     */
    public function zUnionStore($output, $zSetKeys, ?array $weights = null, $aggregateFunction = null) {}

    /**
     * @param string     $Output
     * @param array      $ZSetKeys
     * @param array|null $Weights
     * @param string     $aggregateFunction
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->zUnionStore(%parametersList%)')]
    public function zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM') {}

    /**
     * Creates an intersection of sorted sets given in second argument.
     * The result of the union will be stored in the sorted set defined by the first argument.
     * The third optional argument defines weights to apply to the sorted sets in input.
     * In this case, the weights will be multiplied by the score of each element in the sorted set
     * before applying the aggregation. The forth argument defines the AGGREGATE option which
     * specify how the results of the union are aggregated.
     *
     * @param string $output
     * @param array  $zSetKeys
     * @param null|array $weights
     * @param string|null $aggregateFunction Either "SUM", "MIN", or "MAX":
     * defines the behaviour to use on duplicate entries during the zInterStore.
     *
     * @return false|int|Redis The number of values in the new sorted set or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zinterstore
     * @example
     * <pre>
     * $redis->del('k1');
     * $redis->del('k2');
     * $redis->del('k3');
     *
     * $redis->del('ko1');
     * $redis->del('ko2');
     * $redis->del('ko3');
     * $redis->del('ko4');
     *
     * $redis->zAdd('k1', 0, 'val0');
     * $redis->zAdd('k1', 1, 'val1');
     * $redis->zAdd('k1', 3, 'val3');
     *
     * $redis->zAdd('k2', 2, 'val1');
     * $redis->zAdd('k2', 3, 'val3');
     *
     * $redis->zInterStore('ko1', array('k1', 'k2'));               // 2, 'ko1' => array('val1', 'val3')
     * $redis->zInterStore('ko2', array('k1', 'k2'), array(1, 1));  // 2, 'ko2' => array('val1', 'val3')
     *
     * // Weighted zInterStore
     * $redis->zInterStore('ko3', array('k1', 'k2'), array(1, 5), 'min'); // 2, 'ko3' => array('val1', 'val3')
     * $redis->zInterStore('ko4', array('k1', 'k2'), array(1, 5), 'max'); // 2, 'ko4' => array('val3', 'val1')
     * </pre>
     */
    public function zInterStore($output, $zSetKeys, array $weights = null, $aggregateFunction = null) {}

    /**
     * @param $Output
     * @param $ZSetKeys
     * @param array|null $Weights
     * @param string $aggregateFunction
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->zInterStore(%parametersList%)')]
    public function zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM') {}

    /**
     * Scan a sorted set for members, with optional pattern and count
     *
     * @param string $key      String, the set to scan.
     * @param int|null &$iterator Long (reference), initialized to NULL.
     * @param string $pattern  String (optional), the pattern to match.
     * @param int    $count    How many keys to return per iteration (Redis might return a different number).
     *
     * @return array|false|Redis PHPRedis will return matching keys from Redis, or FALSE when iteration is complete or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/zscan
     * @example
     * <pre>
     * $iterator = null;
     * while ($members = $redis-zscan('zset', $iterator)) {
     *     foreach ($members as $member => $score) {
     *         echo $member . ' => ' . $score . PHP_EOL;
     *     }
     * }
     * </pre>
     */
    public function zScan($key, &$iterator, $pattern = null, $count = 0) {}

    /**
     * Block until Redis can pop the highest or lowest scoring members from one or more ZSETs.
     * There are two commands (BZPOPMIN and BZPOPMAX for popping the lowest and highest scoring elements respectively.)
     *
     * @param string|array     $key_or_keys    Either a string key or an array of one or more keys.
     * @param string|int|array $timeout_or_key If the previous argument was an array, this argument must be a timeout value. Otherwise it could also be another key.
     * @param mixed            ...$extra_args  Can consist of additional keys, until the last argument which needs to be a timeout.
     *
     * @return false|array|Redis Either an array with the key member and score of the highest or lowest element or an empty array or Redis if in multimode
     * if the timeout was reached without an element to pop.
     *
     * @throws RedisException
     *
     * @since >= 5.0
     * @link https://redis.io/commands/bzpopmax
     * @example
     * <pre>
     * // Wait up to 5 seconds to pop the *highest* scoring member from sets `zs1` and `zs2`
     * $redis->bzPopMax(['zs1', 'zs2'], 5);
     * $redis->bzPopMax('zs1', 'zs2', 5);
     * </pre>
     */
    public function bzPopMax($key_or_keys, $timeout_or_key, ...$extra_args) {}

    /**
     * POP the minimum scoring element off of one or more sorted sets, blocking up to a specified timeout if no elements are available.
     *
     * @param string|array     $key_or_keys    Either a string key or an array of one or more keys.
     * @param string|int|array $timeout_or_key If the previous argument was an array, this argument must be a timeout value. Otherwise it could also be another key.
     * @param mixed            ...$extra_args  Can consist of additional keys, until the last argument which needs to be a timeout.
     *
     * @return false|array|Redis Either an array with the key member and score of the highest or lowest element or an empty array or Redis if in multimode
     * if the timeout was reached without an element to pop.
     *
     * @throws RedisException
     *
     * @see bzPopMax
     * @since >= 5.0
     * @link https://redis.io/commands/bzpopmin
     *
     * @example
     * <pre>
     * // Wait up to 5 seconds to pop the *lowest* scoring member from sets `zs1` and `zs2`.
     * $redis->bzPopMin(['zs1', 'zs2'], 5);
     * $redis->bzPopMin('zs1', 'zs2', 5);
     * </pre>
     */
    public function bzPopMin($key_or_keys, $timeout_or_key, ...$extra_args) {}

    /**
     * Adds a value to the hash stored at key. If this value is already in the hash, FALSE is returned.
     *
     * @param string $key
     * @param string $hashKey
     * @param mixed  $value
     *
     * @return int|bool|Redis returns Redis if in multimode
     * - 1 if value didn't exist and was added successfully,
     * - 0 if the value was already present and was replaced, FALSE if there was an error.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hset
     * @example
     * <pre>
     * $redis->del('h')
     * $redis->hSet('h', 'key1', 'hello');  // 1, 'key1' => 'hello' in the hash at "h"
     * $redis->hGet('h', 'key1');           // returns "hello"
     *
     * $redis->hSet('h', 'key1', 'plop');   // 0, value was replaced.
     * $redis->hGet('h', 'key1');           // returns "plop"
     * </pre>
     */
    public function hSet($key, $hashKey, $value) {}

    /**
     * Adds a value to the hash stored at key only if this field isn't already in the hash.
     *
     * @param string $key
     * @param string $hashKey
     * @param string $value
     *
     * @return  bool|Redis TRUE if the field was set, FALSE if it was already present or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hsetnx
     * @example
     * <pre>
     * $redis->del('h')
     * $redis->hSetNx('h', 'key1', 'hello'); // TRUE, 'key1' => 'hello' in the hash at "h"
     * $redis->hSetNx('h', 'key1', 'world'); // FALSE, 'key1' => 'hello' in the hash at "h". No change since the field
     * wasn't replaced.
     * </pre>
     */
    public function hSetNx($key, $hashKey, $value) {}

    /**
     * Gets a value from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @param string $key
     * @param string $hashKey
     *
     * @return string|false|Redis The value, if the command executed successfully BOOL FALSE in case of failure or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hget
     */
    public function hGet($key, $hashKey) {}

    /**
     * Returns the length of a hash, in number of items
     *
     * @param string $key
     *
     * @return int|false|Redis the number of items in a hash, FALSE if the key doesn't exist or isn't a hash or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hlen
     * @example
     * <pre>
     * $redis->del('h')
     * $redis->hSet('h', 'key1', 'hello');
     * $redis->hSet('h', 'key2', 'plop');
     * $redis->hLen('h'); // returns 2
     * </pre>
     */
    public function hLen($key) {}

    /**
     * Removes a values from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @param string $key
     * @param string $hashKey1
     * @param string ...$otherHashKeys
     *
     * @return int|bool|Redis Number of deleted fields or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hdel
     * @example
     * <pre>
     * $redis->hMSet('h',
     *               array(
     *                    'f1' => 'v1',
     *                    'f2' => 'v2',
     *                    'f3' => 'v3',
     *                    'f4' => 'v4',
     *               ));
     *
     * var_dump( $redis->hDel('h', 'f1') );        // int(1)
     * var_dump( $redis->hDel('h', 'f2', 'f3') );  // int(2)
     * s
     * var_dump( $redis->hGetAll('h') );
     * //// Output:
     * //  array(1) {
     * //    ["f4"]=> string(2) "v4"
     * //  }
     * </pre>
     */
    public function hDel($key, $hashKey1, ...$otherHashKeys) {}

    /**
     * Returns the keys in a hash, as an array of strings.
     *
     * @param string $key
     *
     * @return array|false|Redis An array of elements, the keys of the hash. This works like PHP's array_keys() or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hkeys
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hSet('h', 'a', 'x');
     * $redis->hSet('h', 'b', 'y');
     * $redis->hSet('h', 'c', 'z');
     * $redis->hSet('h', 'd', 't');
     * var_dump($redis->hKeys('h'));
     *
     * // Output:
     * // array(4) {
     * // [0]=>
     * // string(1) "a"
     * // [1]=>
     * // string(1) "b"
     * // [2]=>
     * // string(1) "c"
     * // [3]=>
     * // string(1) "d"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public function hKeys($key) {}

    /**
     * Returns the values in a hash, as an array of strings.
     *
     * @param string $key
     *
     * @return array|false|Redis An array of elements, the values of the hash. This works like PHP's array_values() or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hvals
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hSet('h', 'a', 'x');
     * $redis->hSet('h', 'b', 'y');
     * $redis->hSet('h', 'c', 'z');
     * $redis->hSet('h', 'd', 't');
     * var_dump($redis->hVals('h'));
     *
     * // Output
     * // array(4) {
     * //   [0]=>
     * //   string(1) "x"
     * //   [1]=>
     * //   string(1) "y"
     * //   [2]=>
     * //   string(1) "z"
     * //   [3]=>
     * //   string(1) "t"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public function hVals($key) {}

    /**
     * Returns the whole hash, as an array of strings indexed by strings.
     *
     * @param string $key
     *
     * @return array|false|Redis An array of elements, the contents of the hash or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hgetall
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hSet('h', 'a', 'x');
     * $redis->hSet('h', 'b', 'y');
     * $redis->hSet('h', 'c', 'z');
     * $redis->hSet('h', 'd', 't');
     * var_dump($redis->hGetAll('h'));
     *
     * // Output:
     * // array(4) {
     * //   ["a"]=>
     * //   string(1) "x"
     * //   ["b"]=>
     * //   string(1) "y"
     * //   ["c"]=>
     * //   string(1) "z"
     * //   ["d"]=>
     * //   string(1) "t"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public function hGetAll($key) {}

    /**
     * Verify if the specified member exists in a key.
     *
     * @param string $key
     * @param string $hashKey
     *
     * @return bool|Redis If the member exists in the hash table, return TRUE, otherwise return FALSE or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hexists
     * @example
     * <pre>
     * $redis->hSet('h', 'a', 'x');
     * $redis->hExists('h', 'a');               //  TRUE
     * $redis->hExists('h', 'NonExistingKey');  // FALSE
     * </pre>
     */
    public function hExists($key, $hashKey) {}

    /**
     * Increments the value of a member from a hash by a given amount.
     *
     * @param string $key
     * @param string $hashKey
     * @param int    $value (integer) value that will be added to the member's value
     *
     * @return false|int|Redis the new value or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hincrby
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hIncrBy('h', 'x', 2); // returns 2: h[x] = 2 now.
     * $redis->hIncrBy('h', 'x', 1); // h[x] ← 2 + 1. Returns 3
     * </pre>
     */
    public function hIncrBy($key, $hashKey, $value) {}

    /**
     * Increment the float value of a hash field by the given amount
     *
     * @param string $key
     * @param string $field
     * @param float  $increment
     *
     * @return float|false|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hincrbyfloat
     * @example
     * <pre>
     * $redis = new Redis();
     * $redis->connect('127.0.0.1');
     * $redis->hset('h', 'float', 3);
     * $redis->hset('h', 'int',   3);
     * var_dump( $redis->hIncrByFloat('h', 'float', 1.5) ); // float(4.5)
     *
     * var_dump( $redis->hGetAll('h') );
     *
     * // Output
     *  array(2) {
     *    ["float"]=>
     *    string(3) "4.5"
     *    ["int"]=>
     *    string(1) "3"
     *  }
     * </pre>
     */
    public function hIncrByFloat($key, $field, $increment) {}

    /**
     * Fills in a whole hash. Non-string values are converted to string, using the standard (string) cast.
     * NULL values are stored as empty strings
     *
     * @param string $key
     * @param array  $hashKeys key → value array
     *
     * @return bool|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hmset
     * @example
     * <pre>
     * $redis->del('user:1');
     * $redis->hMSet('user:1', array('name' => 'Joe', 'salary' => 2000));
     * $redis->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.
     * </pre>
     */
    public function hMSet($key, $hashKeys) {}

    /**
     * Retrieve the values associated to the specified fields in the hash.
     *
     * @param string $key
     * @param array  $hashKeys
     *
     * @return array|false|Redis Array An array of elements, the values of the specified fields in the hash, or Redis if in multimode
     * with the hash keys as array keys.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hmget
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hSet('h', 'field1', 'value1');
     * $redis->hSet('h', 'field2', 'value2');
     * $redis->hMGet('h', array('field1', 'field2')); // returns array('field1' => 'value1', 'field2' => 'value2')
     * </pre>
     */
    public function hMGet($key, $hashKeys) {}

    /**
     * Scan a HASH value for members, with an optional pattern and count.
     *
     * @param string $key
     * @param int|null ?$iterator The scan iterator, which should be initialized to NULL before the first call.
     * @param string $pattern    Optional pattern to match against.
     * @param int    $count      How many keys to return in a go (only a sugestion to Redis).
     *
     * @return array|bool|Redis An array of members that match our pattern or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/hscan
     * @example
     * <pre>
     * $redis->del('big-hash');
     *
     * for ($i = 0; $i < 1000; $i++) {
     *     $fields["field:$i"] = "value:$i";
     * }
     *
     * $redis->hMSet('big-hash', $fields);
     *
     * $it = NULL;
     *
     * do {
     *     // Scan the hash but limit it to fields that match '*:1?3'
     *     $fields = $redis->hScan('big-hash', $it, '*:1?3');
     *
     *     foreach ($fields as $field => $value) {
     *         echo "[$field] => $value\n";
     *     }
     * } while ($it != 0);
     * </pre>
     */
    public function hScan($key, &$iterator, $pattern = null, $count = 0) {}

    /**
     * Get the string length of the value associated with field in the hash stored at key
     *
     * @param string $key
     * @param string $field
     *
     * @return false|int|Redis the string length of the value associated with field, or zero when field is not present in the hash or Redis if in multimode
     * or key does not exist at all.
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hstrlen
     * @since >= 3.2
     *
     * @example
     * $redis->del('hash');
     * $redis->hMSet('hash', ['50bytes' => str_repeat('a', 50)]);
     * $redis->hStrLen('hash', '50bytes');
     */
    public function hStrLen(string $key, string $field) {}

    /**
     * Add one or more geospatial items to the specified key.
     * This function must be called with at least one longitude, latitude, member triplet.
     *
     * @param string $key
     * @param float  $longitude
     * @param float  $latitude
     * @param string $member
     * @param mixed  $other_triples_and_options You can continue to pass longitude, lattitude, and member arguments to add as many members
     *                                          as you wish. Optionally, the final argument may be a string with options for the command
     *
     * @return false|int|Redis The number of elements added to the geospatial key or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/geoadd
     * @since >= 3.2
     *
     * @example
     * <pre>
     * $redis->del("myplaces");
     *
     * // Since the key will be new, $result will be 2
     * $result = $redis->geoAdd(
     *   "myplaces",
     *   -122.431, 37.773, "San Francisco",
     *   -157.858, 21.315, "Honolulu"
     * ); // 2
     *
     * $redis->geoAdd('cities', -121.837478, 39.728494, 'Chico', ['XX', 'CH']);
     * $redis->geoAdd('cities', -121.8374, 39.7284, 'Chico', -122.03218, 37.322, 'Cupertino');
     * </pre>
     */
    public function geoAdd($key, $longitude, $latitude, $member, ...$other_triples_and_options) {}

    /**
     * Retrieve Geohash strings for one or more elements of a geospatial index.
     *
     * @param string $key
     * @param string ...$member variadic list of members
     *
     * @return false|array|Redis One or more Redis Geohash encoded strings or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/geohash
     * @since >= 3.2
     *
     * @example
     * <pre>
     * $redis->geoAdd("hawaii", -157.858, 21.306, "Honolulu", -156.331, 20.798, "Maui");
     * $hashes = $redis->geoHash("hawaii", "Honolulu", "Maui");
     * var_dump($hashes);
     * // Output: array(2) {
     * //   [0]=>
     * //   string(11) "87z9pyek3y0"
     * //   [1]=>
     * //   string(11) "8e8y6d5jps0"
     * // }
     * </pre>
     */
    public function geoHash($key, ...$member) {}

    /**
     * Return longitude, latitude positions for each requested member.
     *
     * @param string $key
     * @param string ...$member
     * @return array|Redis One or more longitude/latitude positions or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/geopos
     * @since >= 3.2
     *
     * @example
     * <pre>
     * $redis->geoAdd("hawaii", -157.858, 21.306, "Honolulu", -156.331, 20.798, "Maui");
     * $positions = $redis->geoPos("hawaii", "Honolulu", "Maui");
     * var_dump($positions);
     *
     * // Output:
     * array(2) {
     *  [0]=> array(2) {
     *      [0]=> string(22) "-157.85800248384475708"
     *      [1]=> string(19) "21.3060004581273077"
     *  }
     *  [1]=> array(2) {
     *      [0]=> string(22) "-156.33099943399429321"
     *      [1]=> string(20) "20.79799924753607598"
     *  }
     * }
     * </pre>
     */
    public function geoPos(string $key, string ...$member) {}

    /**
     * Search a geospacial sorted set for members in various ways.
     *
     * @param string          $key      The set to query.
     * @param array|string    $position Either a two element array with longitude and lattitude, or a string representing a member of the set.
     * @param array|int|float $shape    Either a number representine the radius of a circle to search, or
     *                                  a two element array representing the width and height of a box to search.
     * @param string          $unit     The unit of our shape. See geodist() for possible units.
     * @param array           $options  See georadius() for options. Note that the `STORE` options are not allowed for this command.
     *
     * @return mixed[]
     */
    public function geosearch(string $key, array|string $position, array|int|float $shape, string $unit, array $options = []): array|false {}

    /**
     * Search a geospacial sorted set for members within a given area or range, storing the results into
     * a new set.
     *
     * @param string          $dst      The destination where results will be stored.
     * @param string          $src      The key to query.
     * @param array|string    $position Either a two element array with longitude and lattitude, or a string representing a member of the set.
     * @param array|int|float $shape    Either a number representine the radius of a circle to search, or
     *                                  a two element array representing the width and height of a box to search.
     * @param string          $unit     The unit of our shape. See geoDist for possible units.
     * @param array           $options
     *                        <code>
     *                        $options = [
     *                           'ASC' | 'DESC',  # The sort order of returned members
     *                           'WITHDIST'       # Also store distances.
     *                            # Limit to N returned members.  Optionally a two element array may be
     *                            # passed as the `LIMIT` argument, and the `ANY` argument.
     *                            'COUNT' => [<int>], or [<int>, <bool>]
     *                        ];
     *                        </code>
     *
     * @return mixed[]|int|true|Redis
     */
    public function geosearchstore(string $dst, string $src, array|string $position, array|int|float $shape, string $unit, array $options = []): array|false {}

    /**
     * Return the distance between two members in a geospatial set.
     *
     * If units are passed it must be one of the following values:
     * - 'm' => Meters
     * - 'km' => Kilometers
     * - 'mi' => Miles
     * - 'ft' => Feet
     *
     * @param string $key
     * @param string $member1
     * @param string $member2
     * @param string|null $unit Which unit to use when computing distance, defaulting to meters. M - meters, KM - kilometers, FT - feet, MI - miles
     *
     * @return float|Redis The distance between the two passed members in the units requested (meters by default) or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/geodist
     * @since >= 3.2
     *
     * @example
     * <pre>
     * $redis->geoAdd("hawaii", -157.858, 21.306, "Honolulu", -156.331, 20.798, "Maui");
     *
     * $meters = $redis->geoDist("hawaii", "Honolulu", "Maui");
     * $kilometers = $redis->geoDist("hawaii", "Honolulu", "Maui", 'km');
     * $miles = $redis->geoDist("hawaii", "Honolulu", "Maui", 'mi');
     * $feet = $redis->geoDist("hawaii", "Honolulu", "Maui", 'ft');
     *
     * echo "Distance between Honolulu and Maui:\n";
     * echo "  meters    : $meters\n";
     * echo "  kilometers: $kilometers\n";
     * echo "  miles     : $miles\n";
     * echo "  feet      : $feet\n";
     *
     * // Bad unit
     * $inches = $redis->geoDist("hawaii", "Honolulu", "Maui", 'in');
     * echo "Invalid unit returned:\n";
     * var_dump($inches);
     *
     * // Output
     * Distance between Honolulu and Maui:
     * meters    : 168275.204
     * kilometers: 168.2752
     * miles     : 104.5616
     * feet      : 552084.0028
     * Invalid unit returned:
     * bool(false)
     * </pre>
     */
    public function geoDist($key, $member1, $member2, $unit = null) {}

    /**
     * Return members of a set with geospatial information that are within the radius specified by the caller.
     *
     * @param $key
     * @param $longitude
     * @param $latitude
     * @param $radius
     * @param $unit
     * @param array|null $options
     * <pre>
     * |Key         |Value          |Description                                        |
     * |------------|---------------|---------------------------------------------------|
     * |COUNT       |integer > 0    |Limit how many results are returned                |
     * |            |WITHCOORD      |Return longitude and latitude of matching members  |
     * |            |WITHDIST       |Return the distance from the center                |
     * |            |WITHHASH       |Return the raw geohash-encoded score               |
     * |            |ASC            |Sort results in ascending order                    |
     * |            |DESC           |Sort results in descending order                   |
     * |STORE       |key            |Store results in key                               |
     * |STOREDIST   |key            |Store the results as distances in key              |
     * </pre>
     * Note: It doesn't make sense to pass both ASC and DESC options but if both are passed
     * the last one passed will be used.
     * Note: When using STORE[DIST] in Redis Cluster, the store key must has to the same slot as
     * the query key or you will get a CROSSLOT error.
     * @return mixed|Redis When no STORE option is passed, this function returns an array of results or Redis if in multimode
     * If it is passed this function returns the number of stored entries.
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/georadius
     * @since >= 3.2
     * @example
     * <pre>
     * // Add some cities
     * $redis->geoAdd("hawaii", -157.858, 21.306, "Honolulu", -156.331, 20.798, "Maui");
     *
     * echo "Within 300 miles of Honolulu:\n";
     * var_dump($redis->geoRadius("hawaii", -157.858, 21.306, 300, 'mi'));
     *
     * echo "\nWithin 300 miles of Honolulu with distances:\n";
     * $options = ['WITHDIST'];
     * var_dump($redis->geoRadius("hawaii", -157.858, 21.306, 300, 'mi', $options));
     *
     * echo "\nFirst result within 300 miles of Honolulu with distances:\n";
     * $options['count'] = 1;
     * var_dump($redis->geoRadius("hawaii", -157.858, 21.306, 300, 'mi', $options));
     *
     * echo "\nFirst result within 300 miles of Honolulu with distances in descending sort order:\n";
     * $options[] = 'DESC';
     * var_dump($redis->geoRadius("hawaii", -157.858, 21.306, 300, 'mi', $options));
     *
     * // Output
     * Within 300 miles of Honolulu:
     * array(2) {
     *  [0]=> string(8) "Honolulu"
     *  [1]=> string(4) "Maui"
     * }
     *
     * Within 300 miles of Honolulu with distances:
     * array(2) {
     *     [0]=>
     *   array(2) {
     *         [0]=>
     *     string(8) "Honolulu"
     *         [1]=>
     *     string(6) "0.0002"
     *   }
     *   [1]=>
     *   array(2) {
     *         [0]=>
     *     string(4) "Maui"
     *         [1]=>
     *     string(8) "104.5615"
     *   }
     * }
     *
     * First result within 300 miles of Honolulu with distances:
     * array(1) {
     *     [0]=>
     *   array(2) {
     *         [0]=>
     *     string(8) "Honolulu"
     *         [1]=>
     *     string(6) "0.0002"
     *   }
     * }
     *
     * First result within 300 miles of Honolulu with distances in descending sort order:
     * array(1) {
     *     [0]=>
     *   array(2) {
     *         [0]=>
     *     string(4) "Maui"
     *         [1]=>
     *     string(8) "104.5615"
     *   }
     * }
     * </pre>
     */
    public function geoRadius($key, $longitude, $latitude, $radius, $unit, array $options = []) {}

    /**
     * This method is identical to geoRadius except that instead of passing a longitude and latitude as the "source"
     * you pass an existing member in the geospatial set
     *
     * @param string $key
     * @param string $member
     * @param $radius
     * @param $units
     * @param array|null $options see georadius
     *
     * @return mixed|Redis The zero or more entries that are close enough to the member given the distance and radius specified or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/georadiusbymember
     * @since >= 3.2
     * @see georadius
     * @example
     * <pre>
     * $redis->geoAdd("hawaii", -157.858, 21.306, "Honolulu", -156.331, 20.798, "Maui");
     *
     * echo "Within 300 miles of Honolulu:\n";
     * var_dump($redis->geoRadiusByMember("hawaii", "Honolulu", 300, 'mi'));
     *
     * echo "\nFirst match within 300 miles of Honolulu:\n";
     * var_dump($redis->geoRadiusByMember("hawaii", "Honolulu", 300, 'mi', ['count' => 1]));
     *
     * // Output
     * Within 300 miles of Honolulu:
     * array(2) {
     *  [0]=> string(8) "Honolulu"
     *  [1]=> string(4) "Maui"
     * }
     *
     * First match within 300 miles of Honolulu:
     * array(1) {
     *  [0]=> string(8) "Honolulu"
     * }
     * </pre>
     */
    public function geoRadiusByMember($key, $member, $radius, $units, array $options = []) {}

    /**
     * Execute the Redis CONFIG command in a variety of ways.
     *
     * @param string            $operation       Operations that PhpRedis supports: RESETSTAT, REWRITE, GET, and SET.
     * @param array|string|null $key_or_settings One or more keys or values.
     * @param string|null       $value           The value if this is a `CONFIG SET` operation.
     *
     * @return mixed|Redis Associative array for `GET`, key -> value or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/config-get
     * @example
     * <pre>
     * $redis->config('GET', 'timeout');
     * $redis->config('GET', ['timeout', 'databases']);
     * $redis->config('SET', 'timeout', 30);
     * $redis->config('SET', ['timeout' => 30, 'loglevel' => 'warning']);
     * </pre>
     */
    public function config($operation, $key_or_settings = null, $value = null) {}

    /**
     * Evaluate a LUA script serverside
     *
     * @param string $script
     * @param array  $args
     * @param int    $numKeys
     *
     * @return mixed|Redis What is returned depends on what the LUA script itself returns, which could be a scalar value or Redis if in multimode
     * (int/string), or an array. Arrays that are returned can also contain other arrays, if that's how it was set up in
     * your LUA script.  If there is an error executing the LUA script, the getLastError() function can tell you the
     * message that came back from Redis (e.g. compile error).
     *
     * @throws RedisException
     *
     * @link   https://redis.io/commands/eval
     * @example
     * <pre>
     * $redis->eval("return 1"); // Returns an integer: 1
     * $redis->eval("return {1,2,3}"); // Returns Array(1,2,3)
     * $redis->del('mylist');
     * $redis->rpush('mylist','a');
     * $redis->rpush('mylist','b');
     * $redis->rpush('mylist','c');
     * // Nested response:  Array(1,2,3,Array('a','b','c'));
     * $redis->eval("return {1,2,3,redis.call('lrange','mylist',0,-1)}}");
     * </pre>
     */
    public function eval($script, $args = [], $numKeys = 0) {}

    /**
     * @param   string  $script
     * @param   array   $args
     * @param   int     $numKeys
     * @return  mixed|Redis   @see eval() , returns Redis if in multimode
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->eval(%parametersList%)')]
    public function evaluate($script, $args = [], $numKeys = 0) {}

    /**
     * Evaluate a LUA script serverside, from the SHA1 hash of the script instead of the script itself.
     * In order to run this command Redis will have to have already loaded the script, either by running it or via
     * the SCRIPT LOAD command.
     *
     * @param string $scriptSha
     * @param array  $args
     * @param int    $numKeys
     *
     * @return mixed|Redis @see eval() , returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @see     eval()
     * @link    https://redis.io/commands/evalsha
     * @example
     * <pre>
     * $script = 'return 1';
     * $sha = $redis->script('load', $script);
     * $redis->evalSha($sha); // Returns 1
     * </pre>
     */
    public function evalSha($scriptSha, $args = [], $numKeys = 0) {}

    /**
     * @param string $scriptSha
     * @param array  $args
     * @param int    $numKeys
     *
     * @throws RedisException
     */
    #[Deprecated(replacement: '%class%->evalSha(%parametersList%)')]
    public function evaluateSha($scriptSha, $args = [], $numKeys = 0) {}

    /**
     * Execute the Redis SCRIPT command to perform various operations on the scripting subsystem.
     * @param string $command load | flush | kill | exists
     * @param mixed ...$script
     *
     * @return  mixed|Redis This command returns various things depending on the specific operation executed. Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/script-load
     * @link    https://redis.io/commands/script-kill
     * @link    https://redis.io/commands/script-flush
     * @link    https://redis.io/commands/script-exists
     * @example
     * <pre>
     * $redis->script('load', $script);
     * $redis->script('flush');
     * $redis->script('kill');
     * $redis->script('exists', $script1, [$script2, $script3, ...]);
     * </pre>
     *
     * SCRIPT LOAD will return the SHA1 hash of the passed script on success, and FALSE on failure.
     * SCRIPT FLUSH should always return TRUE
     * SCRIPT KILL will return true if a script was able to be killed and false if not
     * SCRIPT EXISTS will return an array with TRUE or FALSE for each passed script
     */
    public function script($command, ...$script) {}

    /**
     * The last error message (if any)
     *
     * @return string|null A string with the last returned script based error message, or NULL if there is no error
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * $redis->eval('this-is-not-lua');
     * $err = $redis->getLastError();
     * // "ERR Error compiling script (new function): user_script:1: '=' expected near '-'"
     * </pre>
     */
    public function getLastError() {}

    /**
     * Clear the last error message
     *
     * @return bool This should always return true or throw an exception if we're not connected.
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * $redis->set('x', 'a');
     * $redis->incr('x');
     * $err = $redis->getLastError();
     * // "ERR value is not an integer or out of range"
     * $redis->clearLastError();
     * $err = $redis->getLastError();
     * // NULL
     * </pre>
     */
    public function clearLastError() {}

    /**
     * Issue the CLIENT command with various arguments.
     * The Redis CLIENT command can be used in four ways:
     * - CLIENT LIST
     * - CLIENT GETNAME
     * - CLIENT SETNAME [name]
     * - CLIENT KILL [ip:port]
     *
     * @param string $command
     * @param mixed ...$args
     * @return mixed This will vary depending on which client command was executed:
     * - CLIENT LIST will return an array of arrays with client information.
     * - CLIENT GETNAME will return the client name or false if none has been set
     * - CLIENT SETNAME will return true if it can be set and false if not
     * - CLIENT KILL will return true if the client can be killed, and false if not
     *
     * @throws RedisException
     *
     * Note: phpredis will attempt to reconnect so you can actually kill your own connection but may not notice losing it!
     *
     * @link https://redis.io/commands/client-list
     * @link https://redis.io/commands/client-getname
     * @link https://redis.io/commands/client-setname
     * @link https://redis.io/commands/client-kill
     *
     * @example
     * <pre>
     * $redis->client('list'); // Get a list of clients
     * $redis->client('getname'); // Get the name of the current connection
     * $redis->client('setname', 'somename'); // Set the name of the current connection
     * $redis->client('kill', <ip:port>); // Kill the process at ip:port
     * </pre>
     */
    public function client($command, ...$args) {}

    /**
     * A utility method to prefix the value with the prefix setting for phpredis.
     *
     * @param string $value The value you wish to prefix
     *
     * @return string If a prefix is set up, the value now prefixed
     * If there is no prefix, the value will be returned unchanged.
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * $redis->setOption(Redis::OPT_PREFIX, 'my-prefix:');
     * $redis->_prefix('my-value'); // Will return 'my-prefix:my-value'
     * </pre>
     */
    public function _prefix($value) {}

    /**
     * A utility method to unserialize data with whatever serializer is set up.  If there is no serializer set, the
     * value will be returned unchanged.  If there is a serializer set up, and the data passed in is malformed, an
     * exception will be thrown. This can be useful if phpredis is serializing values, and you return something from
     * redis in a LUA script that is serialized.
     *
     * @param string $value The value to be unserialized
     *
     * @return mixed
     * @example
     * <pre>
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
     * $redis->_unserialize('a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}'); // Will return Array(1,2,3)
     * </pre>
     */
    public function _unserialize($value) {}

    /**
     * A utility method to serialize values manually. This method allows you to serialize a value with whatever
     * serializer is configured, manually. This can be useful for serialization/unserialization of data going in
     * and out of EVAL commands as phpredis can't automatically do this itself.  Note that if no serializer is
     * set, phpredis will change Array values to 'Array', and Objects to 'Object'.
     *
     * @param mixed $value The value to be serialized.
     *
     * @return string
     * @example
     * <pre>
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
     * $redis->_serialize("foo"); // returns "foo"
     * $redis->_serialize(Array()); // Returns "Array"
     * $redis->_serialize(new stdClass()); // Returns "Object"
     *
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
     * $redis->_serialize("foo"); // Returns 's:3:"foo";'
     * </pre>
     */
    public function _serialize($value) {}

    /**
     * Dump a key out of a redis database, the value of which can later be passed into redis using the RESTORE command.
     * The data that comes out of DUMP is a binary representation of the key as Redis stores it.
     * @param string $key
     *
     * @return string|false|Redis A binary string representing the key's value or FALSE if the key doesn't exist or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/dump
     * @example
     * <pre>
     * $redis->set('foo', 'bar');
     * $val = $redis->dump('foo'); // $val will be the Redis encoded key value
     * </pre>
     */
    public function dump($key) {}

    /**
     * Restore a key from the result of a DUMP operation.
     *
     * @param string $key     The name of the key you wish to create.
     * @param int    $ttl     What Redis should set the key's TTL (in milliseconds) to once it is created.Zero means no TTL at all.
     * @param string $value   The serialized binary value of the string (generated by DUMP).
     * @param array|null $options An array of additional options that modifies how the command operates.
     *                        <code>
     *                        $options = [
     *                            'ABSTTL'          # If this is present, the `$ttl` provided by the user should
     *                                              # be an absolute timestamp, in milliseconds()
     *
     *                            'REPLACE'         # This flag instructs Redis to store the key even if a key with
     *                                              # that name already exists.
     *
     *                            'IDLETIME' => int # Tells Redis to set the keys internal 'idletime' value to a
     *                                              # specific number (see the Redis command OBJECT for more info).
     *                            'FREQ'     => int # Tells Redis to set the keys internal 'FREQ' value to a specific
     *                                              # number (this relates to Redis' LFU eviction algorithm).
     *                        ];
     *                        </code>
     * @return bool|Redis True if the key was stored, false if not.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/restore
     * @example
     * <pre>
     * $redis->set('foo', 'bar');
     * $val = $redis->dump('foo');
     * $redis->restore('bar', 0, $val); // The key 'bar', will now be equal to the key 'foo'
     * </pre>
     */
    public function restore($key, $ttl, $value, $options = null) {}

    /**
     * Migrates a key to a different Redis instance.
     *
     * @param string $host    The destination host
     * @param int    $port    The TCP port to connect to.
     * @param string|array $key     The key to migrate.
     * @param int    $db      The target DB.
     * @param int    $timeout The maximum amount of time given to this transfer.
     * @param bool   $copy    Should we send the COPY flag to redis.
     * @param bool   $replace Should we send the REPLACE flag to redis.
     * @param mixed $credentials
     *
     * @return bool|Redis
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/migrate
     * @example
     * <pre>
     * $redis->migrate('backup', 6379, 'foo', 0, 3600);
     * </pre>
     */
    public function migrate($host, $port, $key, $dstdb, $timeout, $copy = false, $replace = false, $credentials = null) {}

    /**
     * Retrieve the server time from the connected Redis instance.
     *
     * @return false|array If successful, the time will come back as an associative array with element zero being the
     * unix timestamp, and element one being microseconds.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/time
     * @example
     * <pre>
     * var_dump( $redis->time() );
     * // array(2) {
     * //   [0] => string(10) "1342364352"
     * //   [1] => string(6) "253002"
     * // }
     * </pre>
     */
    public function time() {}

    /**
     * Scan the keyspace for keys
     *
     * @param int|null ?$iterator The cursor returned by Redis for every subsequent call to SCAN.  On
     *                         the initial invocation of the call, it should be initialized by the
     *                         caller to NULL.  Each time SCAN is invoked, the iterator will be
     *                         updated to a new number, until finally Redis will set the value to
     *                         zero, indicating that the scan is complete.
     *
     * @param string $pattern  An optional glob-style pattern for matching key names.  If passed as
     *                         NULL, it is the equivalent of sending '*' (match every key).
     *
     * @param int    $count    A hint to redis that tells it how many keys to return in a single
     *                         call to SCAN.  The larger the number, the longer Redis may block
     *                         clients while iterating the key space.
     *
     * @param string|null $type An optional argument to specify which key types to scan (e.g. 'STRING', 'LIST', 'SET')
     *
     * @return array|false|Redis This function will return an array of keys or FALSE if there are no more keys or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link   https://redis.io/commands/scan
     * @example
     * <pre>
     * $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_NORETRY);
     *
     * $it = null;
     *
     * do {
     *     $keys = $redis->scan($it, '*zorg*');
     *     foreach ($keys as $key) {
     *         echo "KEY: $key\n";
     *     }
     * } while ($it != 0);
     *
     * $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
     *
     * $it = null;
     *
     * // When Redis::SCAN_RETRY is enabled, we can use simpler logic, as we will never receive an
     * // empty array of keys when the iterator is nonzero.
     * while ($keys = $redis->scan($it, '*zorg*')) {
     *     foreach ($keys as $key) {
     *         echo "KEY: $key\n";
     *     }
     * }
     * </pre>
     */
    public function scan(&$iterator, $pattern = null, $count = 0, $type = null) {}

    /**
     * Adds all the element arguments to the HyperLogLog data structure stored at the key.
     *
     * @param string $key
     * @param array  $elements
     *
     * @return bool|int|Redis Returns 1 if the set was altered, and zero if not. Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/pfadd
     * @example $redis->pfAdd('key', array('elem1', 'elem2'))
     */
    public function pfAdd($key, array $elements) {}

    /**
     * When called with a single key, returns the approximated cardinality computed by the HyperLogLog data
     * structure stored at the specified variable, which is 0 if the variable does not exist.
     *
     * @param string|array $key_or_keys Either one key or an array of keys
     *
     * @return false|int|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/pfcount
     * @example
     * <pre>
     * $redis->pfAdd('key1', array('elem1', 'elem2'));
     * $redis->pfAdd('key2', array('elem3', 'elem2'));
     * $redis->pfCount('key1'); // int(2)
     * $redis->pfCount(array('key1', 'key2')); // int(3)
     * </pre>
     */
    public function pfCount($key_or_keys) {}

    /**
     * Merge multiple HyperLogLog values into an unique value that will approximate the cardinality
     * of the union of the observed Sets of the source HyperLogLog structures.
     *
     * @param string $destKey
     * @param array  $sourceKeys
     *
     * @return bool|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/pfmerge
     * @example
     * <pre>
     * $redis->pfAdd('key1', array('elem1', 'elem2'));
     * $redis->pfAdd('key2', array('elem3', 'elem2'));
     * $redis->pfMerge('key3', array('key1', 'key2'));
     * $redis->pfCount('key3'); // int(3)
     * </pre>
     */
    public function pfMerge($destKey, array $sourceKeys) {}

    /**
     * Send arbitrary things to the redis server.
     *
     * @param string $command      Required command to send to the server.
     * @param mixed  ...$arguments Optional variable amount of arguments to send to the server.
     *
     * @return mixed|Redis returns Redis if in multimode
     *
     * @throws RedisException
     *
     * @example
     * <pre>
     * $redis->rawCommand('SET', 'key', 'value'); // bool(true)
     * $redis->rawCommand('GET", 'key'); // string(5) "value"
     * </pre>
     */
    public function rawCommand($command, ...$arguments) {}

    /**
     * Detect whether we're in ATOMIC/MULTI/PIPELINE mode.
     *
     * @return false|int|Redis Either Redis::ATOMIC, Redis::MULTI or Redis::PIPELINE or Redis if in multimode
     *
     * @throws RedisException
     *
     * @example $redis->getMode();
     */
    public function getMode() {}

    /**
     * Acknowledge one or more messages on behalf of a consumer group.
     *
     * @param string $stream
     * @param string $group
     * @param array  $messages
     *
     * @return false|int|Redis The number of messages Redis reports as acknowledged or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xack
     * @example
     * <pre>
     * $redis->xAck('stream', 'group1', ['1530063064286-0', '1530063064286-1']);
     * </pre>
     */
    public function xAck($stream, $group, $messages) {}

    /**
     * Append a message to a stream.
     *
     * @param string $key
     * @param string $id  The ID for the message we want to add. This can be the special value '*'
     *                            which means Redis will generate the ID that appends the message to the
     *                            end of the stream. It can also be a value in the form <ms>-* which will
     *                            generate an ID that appends to the end ot entries with the same <ms> value (if any exist).
     * @param array $messages
     * @param int $maxlen        If specified Redis will append the new message but trim any number of the
     *                            oldest messages in the stream until the length is <= $maxlen.
     * @param bool $isApproximate Used in conjunction with `$maxlen`, this flag tells Redis to trim the stream
     *                            but in a more efficient way, meaning the trimming may not be exactly to `$maxlen` values.
     * @param bool $nomkstream    If passed as `TRUE`, the stream must exist for Redis to append the message.
     *
     * @return string|true|Redis The added message ID or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xadd
     * @example
     * <pre>
     * $redis->xAdd('mystream', "*", ['field' => 'value']);
     * $redis->xAdd('mystream', "*", ['field' => 'value'], 10);
     * $redis->xAdd('mystream', "*", ['field' => 'value'], 10, true);
     * </pre>
     */
    public function xAdd($key, $id, $messages, $maxLen = 0, $isApproximate = false, $nomkstream = false) {}

    /**
     * This method allows a consumer to take ownership of pending stream entries, by ID.  Another
     * command that does much the same thing but does not require passing specific IDs is `Redis::xAutoClaim`.
     *
     * @param string  $key           The stream we wish to claim messages for.
     * @param string  $group         Our consumer group.
     * @param string  $consumer      Our consumer.
     * @param int     $min_idle      The minimum idle-time in milliseconds a message must have for ownership to be transferred.
     * @param array   $ids
     * @param array   $options       An options array that modifies how the command operates.
     *
     *                               <code>
     *                               # Following is an options array describing every option you can pass.  Note that
     *                               # 'IDLE', and 'TIME' are mutually exclusive.
     *                               $options = [
     *                                   'IDLE'       => 3            # Set the idle time of the message to a 3.  By default
     *                                                                # the idle time is set to zero.
     *                                   'TIME'       => 1000*time()  # Same as IDLE except it takes a unix timestamp in
     *                                                                # milliseconds.
     *                                   'RETRYCOUNT' => 0            # Set the retry counter to zero.  By default XCLAIM
     *                                                                # doesn't modify the counter.
     *                                   'FORCE'                      # Creates the pending message entry even if IDs are
     *                                                                # not already
     *                                                                # in the PEL with another client.
     *                                   'JUSTID'                     # Return only an array of IDs rather than the messages
     *                                                                # themselves.
     *                               ];
     *                               </code>
     *
     * @return false|array|Redis Either an array of message IDs along with corresponding data, or just an array of IDs or Redis if in multimode
     * (if the 'JUSTID' option was passed).
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xclaim
     * @example
     * <pre>
     * $ids = ['1530113681011-0', '1530113681011-1', '1530113681011-2'];
     *
     * // Without any options
     * $redis->xClaim('mystream', 'group1', 'myconsumer1', 0, $ids);
     *
     * // With options
     * $redis->xClaim(
     *     'mystream', 'group1', 'myconsumer2', 0, $ids,
     *     [
     *         'IDLE' => time() * 1000,
     *         'RETRYCOUNT' => 5,
     *         'FORCE',
     *         'JUSTID'
     *     ]
     * );
     * </pre>
     */
    public function xClaim(string $key, string $group, string $consumer, int $min_iddle, array $ids, array $options) {}

    /**
     * Delete one or more messages from a stream
     *
     * @param string $key
     * @param array  $ids
     *
     * @return false|int|Redis The number of messages removed or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xdel
     * @example
     * <pre>
     * $redis->xDel('mystream', ['1530115304877-0', '1530115305731-0']);
     * </pre>
     */
    public function xDel($key, $ids) {}

    /**
     * Perform various operation on consumer groups for a particular Redis STREAM.  What the command does
     * is primarily based on which operation is passed.
     *
     * @param string $operation      The subcommand you intend to execute.  Valid options are as follows
     *                               'HELP'           - Redis will return information about the command
     *                               Requires: none
     *                               'CREATE'         - Create a consumer group.
     *                               Requires:  Key, group, consumer.
     *                               'SETID'          - Set the ID of an existing consumer group for the stream.
     *                               Requires:  Key, group, id.
     *                               'CREATECONSUMER' - Create a new consumer group for the stream.  You must
     *                               also pass key, group, and the consumer name you wish to
     *                               create.
     *                               Requires:  Key, group, consumer.
     *                               'DELCONSUMER'    - Delete a consumer from group attached to the stream.
     *                               Requires:  Key, group, consumer.
     *                               'DESTROY'        - Delete a consumer group from a stream.
     *                               Requires:  Key, group.
     * @param string|null $key       The STREAM we're operating on.
     * @param string|null $group     The consumer group we want to create/modify/delete.
     * @param string|null $id_or_consumer The STREAM id (e.g. '$') or consumer group.  See the operation section
     *                               for information about which to send.
     * @param bool   $mkstream       This flag may be sent in combination with the 'CREATE' operation, and
     *                               cause Redis to also create the STREAM if it doesn't currently exist.
     *
     * @param int    $entries_read   Allows you to set Redis' 'entries-read' STREAM value.  This argument is
     *                               only relevant to the 'CREATE' and 'SETID' operations.
     *                               Note:  Requires Redis >= 7.0.0.
     *
     * @return mixed|Redis This command returns different types depending on the specific XGROUP command executed or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xgroup
     * @example
     * <pre>
     * $redis->xGroup('CREATE', 'mystream', 'mygroup', 0);
     * $redis->xGroup('CREATE', 'mystream', 'mygroup', 0, true); // create stream
     * $redis->xGroup('DESTROY', 'mystream', 'mygroup');
     * </pre>
     */
    public function xGroup($operation, $key = null, $group = null, $id_or_consumer = null, $mkstream = false, $entries_read = -2) {}

    /**
     * Get information about a stream or consumer groups
     *
     * @param string $operation The specific info operation to perform. e.g.: 'CONSUMERS', 'GROUPS', 'STREAM', 'HELP'
     * @param string|null $arg1 The first argument (depends on operation)
     * @param string|null $arg2 The second argument
     * @param int    $count     The COUNT argument to `XINFO STREAM`
     *
     * @return mixed|Redis This command returns different types depending on which subcommand is used or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xinfo
     * @example
     * <pre>
     * $redis->xInfo('STREAM', 'mystream');
     * </pre>
     */
    public function xInfo($operation, $arg1 = null, $arg2 = null, $count = -1) {}

    /**
     * Get the length of a given stream.
     *
     * @param string $stream
     *
     * @return false|int|Redis The number of messages in the stream or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xlen
     * @example
     * <pre>
     * $redis->xLen('mystream');
     * </pre>
     */
    public function xLen($stream) {}

    /**
     * Get information about pending messages in a given stream
     *
     * @param string      $stream   The stream to inspect.
     * @param string      $group    The user group we want to see pending messages from.
     * @param string|null $start    The minimum ID to consider.
     * @param string|null $end      The maximum ID to consider.
     * @param int         $count    Optional maximum number of messages to return.
     * @param string|null $consumer If provided, limit the returned messages to a specific consumer.
     *
     * @return array|string|false|Redis Information about the pending messages, in various forms depending on or Redis if in multimode
     * the specific invocation of XPENDING.
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/xpending
     * @example
     * <pre>
     * $redis->xPending('mystream', 'mygroup');
     * $redis->xPending('mystream', 'mygroup', '-', '+', 1, 'consumer-1');
     * </pre>
     */
    public function xPending($stream, $group, $start = null, $end = null, $count = -1, $consumer = null) {}

    /**
     * Get a range of messages from a given stream
     *
     * @param string $stream The stream key name to list.
     * @param string $start The minimum ID to return.
     * @param string $end   The maximum ID to return.
     * @param int    $count An optional maximum number of entries to return.
     *
     * @return array|bool|Redis The messages in the stream within the requested range or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xrange
     * @example
     * <pre>
     * // Get everything in this stream
     * $redis->xRange('mystream', '-', '+');
     * // Only the first two messages
     * $redis->xRange('mystream', '-', '+', 2);
     * </pre>
     */
    public function xRange($stream, $start, $end, $count = -1) {}

    /**
     * Read data from one or more streams and only return IDs greater than sent in the command.
     *
     * @param array $streams An associative array with stream name keys and minimum id values.
     * @param int   $count   An optional limit to how many entries are returnd *per stream*
     * @param int   $block   An optional maximum number of milliseconds to block the caller if no data is available on any of the provided streams.
     *
     * @return array|bool|Redis The messages in the stream newer than the IDs passed to Redis (if any) or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xread
     * @example
     * <pre>
     * $redis->xRead(['stream1' => '1535222584555-0', 'stream2' => '1535222584555-0']);
     * </pre>
     */
    public function xRead($streams, $count = -1, $block = -1) {}

    /**
     * This method is similar to xRead except that it supports reading messages for a specific consumer group.
     *
     * @param string $group    The consumer group to use.
     * @param string $consumer The consumer to use.
     * @param array  $streams  An array of stream names and message IDs
     * @param int|null $count  Optional maximum number of messages to return
     * @param int|null $block  How long to block if there are no messages available.
     *
     * @return array|bool|Redis The messages delivered to this consumer group (if any) or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xreadgroup
     * @example
     * <pre>
     * // Consume messages for 'mygroup', 'consumer1'
     * $redis->xReadGroup('mygroup', 'consumer1', ['s1' => 0, 's2' => 0]);
     * // Read a single message as 'consumer2' for up to a second until a message arrives.
     * $redis->xReadGroup('mygroup', 'consumer2', ['s1' => 0, 's2' => 0], 1, 1000);
     * </pre>
     */
    public function xReadGroup($group, $consumer, $streams, $count = 1, $block = 1) {}

    /**
     * This is identical to xRange except the results come back in reverse order.
     * Also note that Redis reverses the order of "start" and "end".
     *
     * @param string $stream The stream key to query.
     * @param string $end   The maximum message ID to include.
     * @param string $start The minimum message ID to include.
     * @param int    $count An optional maximum number of messages to include.
     *
     * @return array|bool|Redis The messages in the range specified or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xrevrange
     * @example
     * <pre>
     * $redis->xRevRange('mystream', '+', '-');
     * $redis->xRevRange('mystream', '0-2', '0-1');
     * </pre>
     */
    public function xRevRange($stream, $end, $start, $count = -1) {}

    /**
     * Trim the stream length to a given maximum.
     * If the "approximate" flag is pasesed, Redis will use your size as a hint but only trim trees in whole nodes
     * (this is more efficient)
     *
     * @param string $stream    The STREAM key to trim.
     * @param string $threshold This can either be a maximum length, or a minimum id.
     *                          MAXLEN - An integer describing the maximum desired length of the stream after the command.
     *                          MINID  - An ID that will become the new minimum ID in the stream, as Redis will trim all
     *                          messages older than this ID.
     * @param bool   $approx    Whether redis is allowed to do an approximate trimming of the stream.  This is
     *                          more efficient for Redis given how streams are stored internally.
     * @param bool   $minid     When set to `true`, users should pass a minimum ID to the `$threshold` argument.
     * @param int    $limit     An optional upper bound on how many entries to trim during the command.
     *
     * @return false|int|Redis The number of messages trimed from the stream or Redis if in multimode
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/xtrim
     * @example
     * <pre>
     * // Trim to exactly 100 messages
     * $redis->xTrim('mystream', 100);
     * // Let Redis approximate the trimming
     * $redis->xTrim('mystream', 100, true);
     * </pre>
     */
    public function xTrim($stream, $threshold, $approx = false, $minid = false, $limit = -1) {}

    /**
     * Adds a values to the set value stored at key.
     *
     * @param string $key Required key
     * @param array  $values Required values
     *
     * @return int|bool|Redis The number of elements added to the set or Redis if in multimode
     * If this value is already in the set, FALSE is returned
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/sadd
     * @link    https://github.com/phpredis/phpredis/commit/3491b188e0022f75b938738f7542603c7aae9077
     * @since   phpredis 2.2.8
     * @example
     * <pre>
     * $redis->sAddArray('k', array('v1'));                // boolean
     * $redis->sAddArray('k', array('v1', 'v2', 'v3'));    // boolean
     * </pre>
     */
    public function sAddArray($key, array $values) {}

    public function __destruct() {}

    /**
     * Compress a value with the currently configured compressor as set with Redis::setOption().
     *
     * @param string $value The value to be compressed
     *
     * @return string The compressed result
     */
    public function _compress($value) {}

    /**
     * Uncompress the provided argument that has been compressed with the
     * currently configured compressor as set with Redis::setOption().
     *
     * @param string $value The compressed value to uncompress.
     *
     * @return string The uncompressed result.
     */
    public function _uncompress($value) {}

    /**
     * Pack the provided value with the configured serializer and compressor as set with Redis::setOption().
     *
     * @param mixed $value The value to pack
     *
     * @return string The packed result having been serialized and compressed.
     */
    public function _pack($value) {}

    /**
     * Unpack the provided value with the configured compressor and serializer as set with Redis::setOption().
     *
     * @param string $value The value which has been serialized and compressed.
     *
     * @return mixed The uncompressed and eserialized value.
     */
    public function _unpack($value) {}

    /**
     * Execute the Redis ACL command.
     *
     * @param string $subcmd Minumum of one argument for Redis and two for RedisCluster.
     * @param string ...$args
     *
     * @return mixed
     *
     * @example
     *
     * $redis->acl('USERS'); // Get a list of users
     * $redis->acl('LOG');   // See log of Redis' ACL subsystem
     */
    public function acl($subcmd, ...$args) {}

    /**
     * POP one or more elements from one or more sorted sets, blocking up to a specified amount of time when no elements are available.
     *
     * @param float  $timeout How long to block if there are no element available
     * @param array  $keys    The sorted sets to pop from
     * @param string $from    The string 'MIN' or 'MAX' (case insensitive) telling Redis whether you wish to pop the lowest or highest scoring members from the set(s).
     * @param int    $count   Pop up to how many elements.
     *
     * @return array|null|false|Redis This function will return an array of popped elements, or false
     *                                depending on whether any elements could be popped within the specified timeout.
     *
     * NOTE: If Redis::OPT_NULL_MULTIBULK_AS_NULL is set to true via Redis::setOption(), this method will instead return NULL when Redis doesn't pop any elements.
     * @since phpredis 6.0
     */
    public function bzmpop($timeout, $keys, $from, $count = 1) {}

    /**
     * POP one or more of the highest or lowest scoring elements from one or more sorted sets.
     *
     * @link https://redis.io/commands/zmpop
     *
     * @param array  $keys  One or more sorted sets
     * @param string $from  The string 'MIN' or 'MAX' (case insensitive) telling Redis whether you want to pop the lowest or highest scoring elements.
     * @param int    $count Pop up to how many elements at once.
     *
     * @return array|null|false|Redis An array of popped elements or false if none could be popped.
     * @since phpredis 6.0
     */
    public function zmpop($keys, $from, $count = 1) {}

    /**
     * Pop one or more elements from one or more Redis LISTs, blocking up to a specified timeout when no elements are available.
     *
     * @link https://redis.io/commands/blmpop
     *
     * @param float  $timeout The number of seconds Redis will block when no elements are available.
     * @param array  $keys    One or more Redis LISTs to pop from.
     * @param string $from    The string 'LEFT' or 'RIGHT' (case insensitive), telling Redis whether to pop elements from the beginning or end of the LISTs.
     * @param int    $count   Pop up to how many elements at once.
     *
     * @return array|null|false|Redis One or more elements popped from the list(s) or false if all LISTs were empty.
     * @since phpredis 6.0
     */
    public function blmpop($timeout, $keys, $from, $count = 1) {}

    /**
     * Pop one or more elements off of one or more Redis LISTs.
     *
     * @link https://redis.io/commands/lmpop
     *
     * @param array  $keys  An array with one or more Redis LIST key names.
     * @param string $from  The string 'LEFT' or 'RIGHT' (case insensitive), telling Redis whether to pop elements from the beginning or end of the LISTs.
     * @param int    $count The maximum number of elements to pop at once.
     *
     * @return array|null|false|Redis One or more elements popped from the LIST(s) or false if all the LISTs were empty.
     *
     * @since phpredis 6.0
     */
    public function lmpop($keys, $from, $count = 1) {}

    /**
     * @param string|null $opt
     * @param mixed       ...$args
     *
     * @return mixed
     */
    public function command($opt = null, ...$args) {}

    /**
     * Make a copy of a key.
     *
     * $redis = new Redis(['host' => 'localhost']);
     *
     * @param string $src         The key to copy
     * @param string $dst         The name of the new key created from the source key.
     * @param array|null $options An array with modifiers on how COPY should operate.
     *                            'REPLACE' => true|false # Whether to replace an existing key.
     *                            'DB' => int             # Copy key to specific db.
     *
     * @return bool|Redis True if the copy was completed and false if not.
     *
     * @link https://redis.io/commands/copy
     * @since phpredis 6.0
     *
     * @example
     * $redis->pipeline()
     *       ->select(1)
     *       ->del('newkey')
     *       ->select(0)
     *       ->del('newkey')
     *       ->mset(['source1' => 'value1', 'exists' => 'old_value'])
     *       ->exec();
     *
     * var_dump($redis->copy('source1', 'newkey'));
     * var_dump($redis->copy('source1', 'newkey', ['db' => 1]));
     * var_dump($redis->copy('source1', 'exists'));
     * var_dump($redis->copy('source1', 'exists', ['REPLACE' => true]));
     */
    public function copy($src, $dst, $options = null) {}

    /**
     * @param string $key
     *
     * @return string|Redis
     */
    public function debug($key) {}

    /**
     * This is simply the read-only variant of eval, meaning the underlying script may not modify data in redis.
     *
     * @param string  $script_sha
     * @param mixed[] $args
     * @param int     $num_keys
     *
     * @return mixed
     *
     * @see eval()
     * @since phpredis 6.0
     */
    public function eval_ro($script_sha, $args = [], $num_keys = 0) {}

    /**
     * This is simply the read-only variant of evalsha, meaning the underlying script may not modify data in redis.
     *
     * @param string  $sha1
     * @param mixed[] $args
     * @param int     $num_keys
     *
     * @return mixed
     * @see evalsha()
     * @since phpredis 6.0
     */
    public function evalsha_ro($sha1, $args = [], $num_keys = 0) {}

    /**
     * @param array|null $to
     * @param bool       $abort
     * @param int        $timeout
     *
     * @return bool|Redis
     * @since phpredis 6.0
     */
    public function failover($to = null, $abort = false, $timeout = 0) {}

    /**
     * Get the expiration of a given key as a unix timestamp
     *
     * @param string $key The key to check.
     *
     * @return int|false|Redis The timestamp when the key expires, or -1 if the key has no expiry and -2 if the key doesn't exist.
     *
     * @link https://redis.io/commands/expiretime
     * @since phpredis 6.0
     *
     * @example
     * $redis->setEx('mykey', 60, 'myval');
     * $redis->expiretime('mykey');
     */
    public function expiretime($key) {}

    /**
     * Get the expriation timestamp of a given Redis key but in milliseconds.
     *
     * @param string $key The key to check
     *
     * @link https://redis.io/commands/pexpiretime
     * @see expiretime()
     * @since phpredis 6.0
     *
     * @return int|false|Redis The expiration timestamp of this key (in milliseconds) or -1 if the key has no expiration, and -2 if it does not exist.
     */
    public function pexpiretime($key) {}

    /**
     * Invoke a function.
     *
     * @param string $fn   The name of the function
     * @param array  $keys Optional list of keys
     * @param array  $args Optional list of args
     *
     * @return mixed Function may return arbitrary data so this method can return strings, arrays, nested arrays, etc.
     *
     * @link https://redis.io/commands/fcall
     * @since phpredis 6.0
     */
    public function fcall($fn, $keys = [], $args = []) {}

    /**
     * This is a read-only variant of the FCALL command that cannot execute commands that modify data.
     *
     * @param string $fn   The name of the function
     * @param array  $keys Optional list of keys
     * @param array  $args Optional list of args
     *
     * @return mixed Function may return arbitrary data so this method can return strings, arrays, nested arrays, etc.
     *
     * @link https://redis.io/commands/fcall_ro
     * @since phpredis 6.0
     */
    public function fcall_ro($fn, $keys = [], $args = []) {}

    /**
     * Functions is an API for managing code to be executed on the server.
     *
     * @param string $operation The subcommand you intend to execute. Valid options are as follows
     *                          'LOAD'      - Create a new library with the given library name and code.
     *                          'DELETE'    - Delete the given library.
     *                          'LIST'      - Return general information on all the libraries
     *                          'STATS'     - Return information about the current function running
     *                          'KILL'      - Kill the current running function
     *                          'FLUSH'     - Delete all the libraries
     *                          'DUMP'      - Return a serialized payload representing the current libraries
     *                          'RESTORE'   - Restore the libraries represented by the given payload
     * @param mixed  $args      Additional arguments
     *
     * @return Redis|bool|string|array  Depends on subcommand.
     *
     * @link https://redis.io/commands/function
     * @since phpredis 6.0
     */
    public function function($operation, ...$args) {}

    /**
     * A readonly variant of `GEORADIUS` that may be executed on replicas.
     *
     * @param string  $key
     * @param float   $lng
     * @param float   $lat
     * @param float   $radius
     * @param string  $unit
     * @param mixed[] $options
     *
     * @return mixed
     * @see georadius()
     */
    public function georadius_ro($key, $lng, $lat, $radius, $unit, $options = []) {}

    /**
     * This is the read-only variant of `GEORADIUSBYMEMBER` that can be run on replicas.
     *
     * @param string  $key
     * @param string  $member
     * @param float   $radius
     * @param string  $unit
     * @param mixed[] $options
     *
     * @return mixed
     *
     * @see georadiusbymember()
     */
    public function georadiusbymember_ro($key, $member, $radius, $unit, $options = []) {}

    /**
     * Get the value of a key and optionally set it's expiration.
     *
     * @param string $key     The key to query
     * @param array  $options Options to modify how the command works.
     *                        'EX'     => <seconds>      # Expire in N seconds
     *                        'PX'     => <milliseconds> # Expire in N milliseconds
     *                        'EXAT'   => <timestamp>    # Expire at a unix timestamp (in seconds)
     *                        'PXAT'   => <mstimestamp>  # Expire at a unix timestamp (in milliseconds);
     *                        'PERSIST'                  # Remove any configured expiration on the key.
     *
     * @return string|bool|Redis The key's value or false if it didn't exist.
     *
     * @see     https://redis.io/comands/getex
     * @since phpredis 6.0
     *
     * @example $redis->getEx('mykey', ['EX' => 60]);
     */
    public function getEx($key, $options = []) {}

    /**
     * Get a key from Redis and delete it in an atomic operation.
     *
     * @param string $key The key to get/delete.
     *
     * @return Redis|string|bool The value of the key or false if it didn't exist.
     *
     * @see     https://redis.io/commands/getdel
     * @since phpredis 6.0
     *
     * @example $redis->getDel('token:123');
     */
    public function getDel($key) {}

    /**
     * Get the longest common subsequence between two string keys.
     *
     * @param string $key1    The first key to check
     * @param string $key2    The second key to check
     * @param array|null $options An optional array of modifiers for the comand.
     *                            'MINMATCHLEN'  => int  # Exclude matching substrings that are less than this value
     *                            'WITHMATCHLEN' => bool # Whether each match should also include its length.
     *                            'LEN'                  # Return the length of the longest subsequence
     *                            'IDX'                  # Each returned match will include the indexes where the
     *                                                   # match occurs in each string.
     *                             NOTE:  'LEN' cannot be used with 'IDX'.
     *
     * @return Redis|string|array|int|false Various reply types depending on options.
     *
     * @link https://redis.io/commands/lcs
     * @since phpredis 6.0
     *
     * @example
     * $redis->set('seq1', 'gtaggcccgcacggtctttaatgtatccctgtttaccatgccatacctgagcgcatacgc');
     * $redis->set('seq2', 'aactcggcgcgagtaccaggccaaggtcgttccagagcaaagactcgtgccccgctgagc');
     * echo $redis->lcs('seq1', 'seq2') . "\n";
     */
    public function lcs($key1, $key2, $options = null) {}

    /**
     * Get the number of bytes sent and received on the socket.
     *
     * @return array An array in the form [$sent_bytes, $received_bytes]
     * @since phpredis 6.0
     */
    public function getTransferredBytes() {}

    /**
     * Reset the number of bytes sent and received on the socket.
     * @since phpredis 6.0
     *
     * @return void
     */
    public function clearTransferredBytes() {}

    /**
     * Get one or more random field from a hash.
     *
     * @param string $key     The hash to query.
     * @param array|null $options An array of options to modify how the command behaves.
     *                            'COUNT'      => int  # An optional number of fields to return.
     *                            'WITHVALUES' => bool # Also return the field values.
     *
     * @return Redis|array|string One or more random fields (and possibly values).
     *
     * @see     https://redis.io/commands/hrandfield
     * @since phpredis 6.0
     *
     * @example
     * $redis->hRandField('settings');
     * $redis->hRandField('settings', ['count' => 2, 'withvalues' => true]);
     */
    public function hRandField($key, $options = null) {}

    /**
     * Move an element from one list into another.
     *
     * @param string $src       The source list.
     * @param string $dst       The destination list
     * @param string $wherefrom Where in the source list to retrieve the element.  This can be either
     *                          - `Redis::LEFT`, or `Redis::RIGHT`.
     * @param string $whereto   Where in the destination list to put the element.  This can be either
     *                          - `Redis::LEFT`, or `Redis::RIGHT`.
     *
     * @return Redis|string|false The element removed from the source list.
     * @since phpredis 6.0
     *
     * @example
     * $redis->rPush('numbers', 'one', 'two', 'three');
     * $redis->lMove('numbers', 'odds', Redis::LEFT, Redis::LEFT);
     */
    public function lMove($src, $dst, $wherefrom, $whereto) {}

    /**
     * Move an element from one list to another, blocking up to a timeout until an element is available.
     *
     * @param string $src       The source list
     * @param string $dst       The destination list
     * @param string $wherefrom Where in the source list to extract the element. - `Redis::LEFT`, or `Redis::RIGHT`.
     * @param string $whereto   Where in the destination list to put the element.- `Redis::LEFT`, or `Redis::RIGHT`.
     * @param float  $timeout   How long to block for an element.
     *
     * @return Redis|string|false;
     * @since phpredis 6.0
     *
     * @example
     * $redis->lPush('numbers', 'one');
     * $redis->blmove('numbers', 'odds', Redis::LEFT, Redis::LEFT 1.0);
     * // This call will block, if no additional elements are in 'numbers'
     * $redis->blmove('numbers', 'odds', Redis::LEFT, Redis::LEFT, 1.0);
     */
    public function blmove($src, $dst, $wherefrom, $whereto, $timeout) {}

    /**
     * Retrieve the index of an element in a list.
     *
     * @param string $key         The list to query.
     * @param mixed  $value       The value to search for.
     * @param array|null $options  Options to configure how the command operates
     *                            # How many matches to return.  By default a single match is returned.
     *                            # If count is set to zero, it means unlimited.
     *                            'COUNT' => <num-matches>
     *
     *                            # Specify which match you want returned.  `RANK` 1 means "the first match"
     *                            # 2 means the second, and so on.  If passed as a negative number the
     *                            # RANK is computed right to left, so a `RANK` of -1 means "the last match".
     *                            'RANK'  => <rank>
     *
     *                            # This argument allows you to limit how many elements Redis will search before
     *                            # returning.  This is useful to prevent Redis searching very long lists while
     *                            # blocking the client.
     *                            'MAXLEN => <max-len>
     *
     * @return Redis|null|bool|int|array Returns one or more of the matching indexes, or null/false if none were found.
     * @since phpredis 6.0
     */
    public function lPos($key, $value, $options = null) {}

    /**
     * Reset the state of the connection.
     *
     * @return Redis|bool Should always return true unless there is an error.
     * @since phpredis 6.0
     */
    public function reset() {}

    /**
     * Compute the intersection of one or more sets and return the cardinality of the result.
     *
     * @param array $keys  One or more set key names.
     * @param int   $limit A maximum cardinality to return. This is useful to put an upper bound on the amount of work Redis will do.
     *
     * @return Redis|int|false
     *
     * @link https://redis.io/commands/sintercard
     * @since phpredis 6.0
     *
     * @example
     * $redis->sAdd('set1', 'apple', 'pear', 'banana', 'carrot');
     * $redis->sAdd('set2', 'apple',         'banana');
     * $redis->sAdd('set3',          'pear', 'banana');
     *
     * $redis->sInterCard(['set1', 'set2', 'set3']);
     */
    public function sInterCard($keys, $limit = -1) {}

    /**
     * Used to turn a Redis instance into a replica of another, or to remove
     * replica status promoting the instance to a primary.
     *
     * @link https://redis.io/commands/replicaof
     * @link https://redis.io/commands/slaveof
     * @see slaveof()
     * @since phpredis 6.0
     *
     * @param string|null $host The host of the primary to start replicating.
     * @param int         $port The port of the primary to start replicating.
     *
     * @return Redis|bool Success if we were successfully able to start replicating a primary or
     *                    were able to promote teh replicat to a primary.
     *
     * @example
     * $redis = new Redis(['host' => 'localhost']);
     *
     * // Attempt to become a replica of a Redis instance at 127.0.0.1:9999
     * $redis->replicaof('127.0.0.1', 9999);
     *
     * // When passed no arguments, PhpRedis will deliver the command `REPLICAOF NO ONE`
     * // attempting to promote the instance to a primary.
     * $redis->replicaof();
     */
    public function replicaof($host = null, $port = 6379) {}

    /**
     * Update one or more keys last modified metadata.
     *
     * @link https://redis.io/commands/touch/
     *
     * @param array|string $key_or_array Either the first key or if passed as the only argument an array of keys.
     * @param string      ...$more_keys  One or more keys to send to the command.
     *
     * @return Redis|int|false This command returns the number of keys that exist and had their last modified time reset
     * @since phpredis 6.0
     */
    public function touch($key_or_array, ...$more_keys) {}

    /**
     * This is simply a read-only variant of the sort command
     *
     * @param string       $key
     * @param mixed[]|null $options
     *
     * @return mixed
     * @see sort()
     * @since phpredis 6.0
     */
    public function sort_ro($key, $options = null) {}

    /**
     * Subscribes the client to the specified shard channels.
     *
     * @param array    $channels One or more channel names.
     * @param callable $cb       The callback PhpRedis will invoke when we receive a message from one of the subscribed channels.
     *
     * @return bool True on success, false on faiilure.  Note that this command will block the
     *              client in a subscribe loop, waiting for messages to arrive.
     *
     * @link https://redis.io/commands/ssubscribe
     * @since phpredis 6.0
     *
     * @example
     * $redis = new Redis(['host' => 'localhost']);
     *
     * $redis->ssubscribe(['channel-1', 'channel-2'], function ($redis, $channel, $message) {
     *     echo "[$channel]: $message\n";
     *
     *     // Unsubscribe from the message channel when we read 'quit'
     *     if ($message == 'quit') {
     *         echo "Unsubscribing from '$channel'\n";
     *         $redis->sunsubscribe([$channel]);
     *     }
     * });
     *
     * // Once we read 'quit' from both channel-1 and channel-2 the subscribe loop will be
     * // broken and this command will execute.
     * echo "Subscribe loop ended\n";
     */
    public function ssubscribe($channels, $cb) {}

    /**
     * Unsubscribes the client from the given shard channels,
     * or from all of them if none is given.
     *
     * @param array $channels One or more channels to unsubscribe from.
     *
     * @return Redis|array|bool The array of unsubscribed channels.
     *
     * @link https://redis.io/commands/sunsubscribe
     * @see ssubscribe()
     * @since phpredis 6.0
     *
     * @example
     * $redis->ssubscribe(['channel-1', 'channel-2'], function ($redis, $channel, $message) {
     *     if ($message == 'quit') {
     *         echo "$channel => 'quit' detected, unsubscribing!\n";
     *         $redis->sunsubscribe([$channel]);
     *     } else {
     *         echo "$channel => $message\n";
     *     }
     * });
     *
     * echo "We've unsubscribed from both channels, exiting\n";
     */
    public function sunsubscribe($channels) {}

    /**
     * This command allows a consumer to claim pending messages that have been idle for a specified period of time.
     * Its purpose is to provide a mechanism for picking up messages that may have had a failed consumer.
     *
     * @link https://redis.io/commands/xautoclaim
     * @link https://redis.io/commands/xclaim
     * @link https://redis.io/docs/data-types/streams-tutorial/
     * @since phpredis 6.0
     *
     * @param string $key      The stream to check.
     * @param string $group    The consumer group to query.
     * @param string $consumer Which consumer to check.
     * @param int    $min_idle The minimum time in milliseconds for the message to have been pending.
     * @param string $start    The minimum message id to check.
     * @param int    $count    An optional limit on how many messages are returned.
     * @param bool   $justid   If the client only wants message IDs and not all of their data.
     *
     * @return Redis|array|bool An array of pending IDs or false if there are none, or on failure.
     *
     * @example
     * $redis->xGroup('CREATE', 'ships', 'combatants', '0-0', true);
     *
     * $redis->xAdd('ships', '1424-74205', ['name' => 'Defiant']);
     *
     * // Consume the ['name' => 'Defiant'] message
     * $msgs = $redis->xReadGroup('combatants', "Jem'Hadar", ['ships' => '>'], 1);
     *
     * // The "Jem'Hadar" consumer has the message presently
     * $pending = $redis->xPending('ships', 'combatants');
     * var_dump($pending);
     *
     * // Asssume control of the pending message with a different consumer.
     * $res = $redis->xAutoClaim('ships', 'combatants', 'Sisko', 0, '0-0');
     *
     * // Now the 'Sisko' consumer owns the message
     * $pending = $redis->xPending('ships', 'combatants');
     * var_dump($pending);
     */
    public function xAutoClaim($key, $group, $consumer, $min_idle, $start, $count = -1, $justid = false) {}

    /**
     * Count the number of elements in a sorted set whos members fall within the provided
     * lexographical range.
     *
     * @param string $key The sorted set to check.
     * @param string $min The minimum matching lexographical string
     * @param string $max The maximum matching lexographical string
     *
     * @return Redis|int|false The number of members that fall within the range or false on failure.
     *
     * @link https://redis.io/commands/zlexcount
     *
     * @example
     * $redis->zAdd('captains', 0, 'Janeway', 0, 'Kirk', 0, 'Picard', 0, 'Sisko', 0, 'Archer');
     * $redis->zLexCount('captains', '[A', '[S');
     */
    public function zLexCount($key, $min, $max) {}

    /**
     * This command is similar to ZRANGE except that instead of returning the values directly
     * it will store them in a destination key provided by the user
     *
     * @param string          $dstkey  The key to store the resulting element(s)
     * @param string          $srckey  The source key with element(s) to retrieve
     * @param string          $start   The starting index to store
     * @param string          $end     The ending index to store
     * @param array|bool|null $options Our options array that controls how the command will function.
     *
     * @return Redis|int|false The number of elements stored in $dstkey or false on failure.
     *
     * @see https://redis.io/commands/zrange/
     * @see Redis::zRange for a full description of the possible options.
     * @since phpredis 6.0
     */
    public function zRangeStore($dstkey, $srckey, $start, $end, $options = null) {}

    /**
     * Store the difference of one or more sorted sets in a destination sorted set.
     *
     * @param string $dst  The destination set name.
     * @param array  $keys One or more source key names
     *
     * @return Redis|int|false The number of elements stored in the destination set or false on failure.
     *
     * @see zDiff()
     * @link https://redis.io/commands/zdiff
     * @since phpredis 6.0
     */
    public function zDiffStore($dst, $keys) {}

    /**
     * Similar to ZINTER but instead of returning the intersected values, this command returns the
     * cardinality of the intersected set.
     *
     * @link https://redis.io/commands/zintercard
     * @link https://redis.io/commands/zinter
     * @see zInter()
     *
     * @param array $keys  One ore more sorted set key names.
     * @param int   $limit An optional upper bound on the returned cardinality. If set to a value
     *                     greater than zero, Redis will stop processing the intersection once the
     *                     resulting cardinality reaches this limit.
     *
     * @return Redis|int|false The cardinality of the intersection or false on failure.
     * @since phpredis 6.0
     *
     * @example
     * $redis->zAdd('zs1', 1, 'one', 2, 'two', 3, 'three', 4, 'four');
     * $redis->zAdd('zs2', 2, 'two', 4, 'four');
     *
     * $redis->zInterCard(['zs1', 'zs2']);
     */
    public function zInterCard($keys, $limit = -1) {}
}

class RedisException extends Exception {}
