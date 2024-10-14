<?php
/**
 * CRedisCache class file
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CRedisCache implements a cache application component based on {@link https://redis.io/ redis}.
 *
 * CRedisCache needs to be configured with {@link hostname}, {@link port} and {@link database} of the server
 * to connect to. By default CRedisCache assumes there is a redis server running on localhost at
 * port 6379 and uses the database number 0.
 *
 * CRedisCache also supports {@link https://redis.io/commands/auth the AUTH command} of redis.
 * When the server needs authentication, you can set the {@link password} property to
 * authenticate with the server after connect.
 *
 * See {@link CCache} manual for common cache operations that are supported by CRedisCache.
 *
 * To use CRedisCache as the cache application component, configure the application as follows,
 * <pre>
 * array(
 *     'components'=>array(
 *         'cache'=>array(
 *             'class'=>'CRedisCache',
 *             'hostname'=>'localhost',
 *             'port'=>6379,
 *             'database'=>0,
 *             'options'=>STREAM_CLIENT_CONNECT,
 *             'username' => 'default' // only for REDIS version 6.0 or later
 *         ),
 *     ),
 * )
 * </pre>
 *
 * The minimum required redis version is 2.0.0.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @package system.caching
 * @since 1.1.14
 */
class CRedisCache extends CCache
{
	/**
	 * @var string hostname to use for connecting to the redis server. Defaults to 'localhost'.
	 */
	public $hostname='localhost';
	/**
	 * @var int the port to use for connecting to the redis server. Default port is 6379.
	 */
	public $port=6379;
	/**
	 * @var string the username to use to authenticate with the redis server. If set, AUTH command will be sent with username.
	 */
	public $username;
	/**
	 * @var string the password to use to authenticate with the redis server. If not set, no AUTH command will be sent.
	 */
	public $password;
	/**
	 * @var int the redis database to use. This is an integer value starting from 0. Defaults to 0.
	 */
	public $database=0;
	/**
	 * @var int the options to pass to the flags parameter of stream_socket_client when connecting to the redis server. Defaults to STREAM_CLIENT_CONNECT.
	 * @see https://php.net/manual/en/function.stream-socket-client.php
	 */
	public $options=STREAM_CLIENT_CONNECT;
	/**
	 * @var float timeout to use for connection to redis. If not set the timeout set in php.ini will be used: ini_get("default_socket_timeout")
	 */
	public $timeout=null;
	/**
	* @var boolean Send sockets over SSL protocol. Default state is false.
	*/
	public $ssl=false;
	/**
	 * @var resource redis socket connection
	 */
	private $_socket;

	/**
	 * Establishes a connection to the redis server.
	 * It does nothing if the connection has already been established.
	 * @throws CException if connecting fails
	 */
	protected function connect()
	{
		$this->_socket=@stream_socket_client(
			$this->hostname.':'.$this->port,
			$errorNumber,
			$errorDescription,
			$this->timeout ? $this->timeout : ini_get("default_socket_timeout"),
			$this->options
		);
		if ($this->_socket)
		{
			if($this->ssl)
				stream_socket_enable_crypto($this->_socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT);
			if ($this->password !== null) {
				if ($this->username !== null) {
					$this->executeCommand('AUTH',array($this->username, $this->password));
				} else {
					$this->executeCommand('AUTH',array($this->password));
				}
			}
				
			$this->executeCommand('SELECT',array($this->database));
		}
		else
		{
			$this->_socket = null;
			throw new CException('Failed to connect to redis: '.$errorDescription,(int)$errorNumber);
		}
	}

	/**
	 * Executes a redis command.
	 * For a list of available commands and their parameters see {@link https://redis.io/commands}.
	 *
	 * @param string $name the name of the command
	 * @param array $params list of parameters for the command
	 * @return array|bool|null|string Dependend on the executed command this method
	 * will return different data types:
	 * <ul>
	 *   <li><code>true</code> for commands that return "status reply".</li>
	 *   <li><code>string</code> for commands that return "integer reply"
	 *       as the value is in the range of a signed 64 bit integer.</li>
	 *   <li><code>string</code> or <code>null</code> for commands that return "bulk reply".</li>
	 *   <li><code>array</code> for commands that return "Multi-bulk replies".</li>
	 * </ul>
	 * See {@link https://redis.io/topics/protocol redis protocol description}
	 * for details on the mentioned reply types.
	 * @throws CException for commands that return {@link https://redis.io/topics/protocol#error-reply error reply}.
	 */
	public function executeCommand($name,$params=array())
	{
		if($this->_socket===null)
			$this->connect();

		array_unshift($params,$name);
		$command='*'.count($params)."\r\n";
		foreach($params as $arg)
			$command.='$'.$this->byteLength($arg)."\r\n".$arg."\r\n";

		fwrite($this->_socket,$command);

		return $this->parseResponse(implode(' ',$params));
	}

	/**
	 * Reads the result from socket and parses it
	 * @return array|bool|null|string
	 * @throws CException socket or data problems
	 */
	private function parseResponse()
	{
		if(($line=fgets($this->_socket))===false)
			throw new CException('Failed reading data from redis connection socket.');
		$type=$line[0];
		$line=substr($line,1,-2);
		switch($type)
		{
			case '+': // Status reply
				return true;
			case '-': // Error reply
				throw new CException('Redis error: '.$line);
			case ':': // Integer reply
				// no cast to int as it is in the range of a signed 64 bit integer
				return $line;
			case '$': // Bulk replies
				if($line=='-1')
					return null;
				$length=$line+2;
				$data='';
				while($length>0)
				{
					if(($block=fread($this->_socket,$length))===false)
						throw new CException('Failed reading data from redis connection socket.');
					$data.=$block;
					$length-=$this->byteLength($block);
				}
				return substr($data,0,-2);
			case '*': // Multi-bulk replies
				$count=(int)$line;
				$data=array();
				for($i=0;$i<$count;$i++)
					$data[]=$this->parseResponse();
				return $data;
			default:
				throw new CException('Unable to parse data received from redis.');
		}
	}

	/**
	 * Counting amount of bytes in a string.
	 *
	 * @param string $str
	 * @return int
	 */
	private function byteLength($str)
	{
		return function_exists('mb_strlen') ? mb_strlen($str, '8bit') : strlen($str);
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		$value=$this->executeCommand('GET',array($key));
		if ($value===null)
			return false;
		return $value;
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		$response=$this->executeCommand('MGET',$keys);
		$result=array();
		$i=0;
		foreach($keys as $key)
			$result[$key]=$response[$i++];
		return $result;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key,$value,$expire)
	{
		if ($expire==0)
			return (bool)$this->executeCommand('SET',array($key,$value));
		return (bool)$this->executeCommand('SETEX',array($key,$expire,$value));
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key,$value,$expire)
	{
		if ($expire == 0)
			return (bool)$this->executeCommand('SETNX',array($key,$value));

		if($this->executeCommand('SETNX',array($key,$value)))
		{
			$this->executeCommand('EXPIRE',array($key,$expire));
			return true;
		}
		else
			return false;
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		return (bool)$this->executeCommand('DEL',array($key));
	}

	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 */
	protected function flushValues()
	{
		return $this->executeCommand('FLUSHDB');
	}
}
