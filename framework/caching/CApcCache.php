<?php
/**
 * CApcCache class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CApcCache provides APC caching in terms of an application component.
 *
 * The caching is based on {@link https://www.php.net/apc APC}.
 * To use this application component, the APC PHP extension must be loaded.
 *
 * See {@link CCache} manual for common cache operations that are supported by CApcCache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.caching
 * @since 1.0
 */
class CApcCache extends CCache
{
	/**
	 * @var boolean whether to use apcu or apc as the underlying caching extension.
	 * If true {@link https://pecl.php.net/package/apcu apcu} will be used.
	 * If false {@link https://pecl.php.net/package/apc apc}. will be used.
	 * Defaults to false.
	 * @since 1.1.17
	 */
	public $useApcu=false;


	/**
	 * Initializes this application component.
	 * This method is required by the {@link IApplicationComponent} interface.
	 * It checks the availability of APC.
	 * @throws CException if APC cache extension is not loaded or is disabled.
	 */
	public function init()
	{
		parent::init();
		$extension=$this->useApcu ? 'apcu' : 'apc';
		if(!extension_loaded($extension))
			throw new CException(Yii::t('yii',"CApcCache requires PHP {extension} extension to be loaded.",
				array('{extension}'=>$extension)));
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		return $this->useApcu ? apcu_fetch($key) : apc_fetch($key);
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		return $this->useApcu ? apcu_fetch($keys) : apc_fetch($keys);
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
		return $this->useApcu ? apcu_store($key,$value,$expire) : apc_store($key,$value,$expire);
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
		return $this->useApcu ? apcu_add($key,$value,$expire) : apc_add($key,$value,$expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		return $this->useApcu ? apcu_delete($key) : apc_delete($key);
	}

	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 * @since 1.1.5
	 */
	protected function flushValues()
	{
		return $this->useApcu ? apcu_clear_cache() : apc_clear_cache('user');
	}
}
