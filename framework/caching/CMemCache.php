<?php
/**
 * CMemCache class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMemCache implements a cache application component based on {@link http://memcached.org/ memcached}.
 *
 * CMemCache can be configured with a list of memcache servers by settings
 * its {@link setServers servers} property. By default, CMemCache assumes
 * there is a memcache server running on localhost at port 11211.
 *
 * See {@link CCache} manual for common cache operations that are supported by CMemCache.
 *
 * Note, there is no security measure to protected data in memcache.
 * All data in memcache can be accessed by any process running in the system.
 *
 * To use CMemCache as the cache application component, configure the application as follows,
 * <pre>
 * array(
 *     'components'=>array(
 *         'cache'=>array(
 *             'class'=>'CMemCache',
 *             'servers'=>array(
 *                 array(
 *                     'host'=>'server1',
 *                     'port'=>11211,
 *                     'weight'=>60,
 *                 ),
 *                 array(
 *                     'host'=>'server2',
 *                     'port'=>11211,
 *                     'weight'=>40,
 *                 ),
 *             ),
 *         ),
 *     ),
 * )
 * </pre>
 * In the above, two memcache servers are used: server1 and server2.
 * You can configure more properties of every server, including:
 * host, port, persistent, weight, timeout, retryInterval, status.
 * See {@link http://www.php.net/manual/en/function.memcache-addserver.php}
 * for more details.
 *
 * CMemCache can also be used with {@link http://pecl.php.net/package/memcached memcached}.
 * To do so, set {@link useMemcached} to be true.
 *
 * @property mixed $memCache The memcache instance (or memcached if {@link useMemcached} is true) used by this component.
 * @property array $servers List of memcache server configurations. Each element is a {@link CMemCacheServerConfiguration}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.caching
 * @since 1.0
 */
class CMemCache extends CCache
{
	/**
	 * @var boolean whether to use memcached or memcache as the underlying caching extension.
	 * If true {@link http://pecl.php.net/package/memcached memcached} will be used.
	 * If false {@link http://pecl.php.net/package/memcache memcache}. will be used.
	 * Defaults to false.
	 */
	public $useMemcached=false;
	/**
	 * @var Memcache the Memcache instance
	 */
	private $_cache=null;
	/**
	 * @var array list of memcache server configurations
	 */
	private $_servers=array();

	/**
	 * Initializes this application component.
	 * This method is required by the {@link IApplicationComponent} interface.
	 * It creates the memcache instance and adds memcache servers.
	 * @throws CException if memcache extension is not loaded
	 */
	public function init()
	{
		parent::init();
		$servers=$this->getServers();
		$cache=$this->getMemCache();
		if(count($servers))
		{
			foreach($servers as $server)
			{
				if($this->useMemcached)
					$cache->addServer($server->host,$server->port,$server->weight);
				else
					$cache->addServer($server->host,$server->port,$server->persistent,$server->weight,$server->timeout,$server->retryInterval,$server->status);
			}
		}
		else
			$cache->addServer('localhost',11211);
	}

	/**
	 * @throws CException if extension isn't loaded
	 * @return Memcache|Memcached the memcache instance (or memcached if {@link useMemcached} is true) used by this component.
	 */
	public function getMemCache()
	{
		if($this->_cache!==null)
			return $this->_cache;
		else
		{
			$extension=$this->useMemcached ? 'memcached' : 'memcache';
			if(!extension_loaded($extension))
				throw new CException(Yii::t('yii',"CMemCache requires PHP {extension} extension to be loaded.",
					array('{extension}'=>$extension)));
			return $this->_cache=$this->useMemcached ? new Memcached : new Memcache;
		}
	}

	/**
	 * @return array list of memcache server configurations. Each element is a {@link CMemCacheServerConfiguration}.
	 */
	public function getServers()
	{
		return $this->_servers;
	}

	/**
	 * @param array $config list of memcache server configurations. Each element must be an array
	 * with the following keys: host, port, persistent, weight, timeout, retryInterval, status.
	 * @see http://www.php.net/manual/en/function.Memcache-addServer.php
	 */
	public function setServers($config)
	{
		foreach($config as $c)
			$this->_servers[]=new CMemCacheServerConfiguration($c);
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		return $this->_cache->get($key);
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		return $this->useMemcached ? $this->_cache->getMulti($keys) : $this->_cache->get($keys);
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
		if($expire>0)
			$expire+=time();
		else
			$expire=0;

		return $this->useMemcached ? $this->_cache->set($key,$value,$expire) : $this->_cache->set($key,$value,0,$expire);
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
		if($expire>0)
			$expire+=time();
		else
			$expire=0;

		return $this->useMemcached ? $this->_cache->add($key,$value,$expire) : $this->_cache->add($key,$value,0,$expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		return $this->_cache->delete($key, 0);
	}

	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 * @since 1.1.5
	 */
	protected function flushValues()
	{
		return $this->_cache->flush();
	}
}
