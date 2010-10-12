<?php
/**
 * CCache class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCache is the base class for cache classes with different cache storage implementation.
 *
 * A data item can be stored in cache by calling {@link set} and be retrieved back
 * later by {@link get}. In both operations, a key identifying the data item is required.
 * An expiration time and/or a dependency can also be specified when calling {@link set}.
 * If the data item expires or the dependency changes, calling {@link get} will not
 * return back the data item.
 *
 * Note, by definition, cache does not ensure the existence of a value
 * even if it does not expire. Cache is not meant to be a persistent storage.
 *
 * CCache implements the interface {@link ICache} with the following methods:
 * <ul>
 * <li>{@link get} : retrieve the value with a key (if any) from cache</li>
 * <li>{@link set} : store the value with a key into cache</li>
 * <li>{@link add} : store the value only if cache does not have this key</li>
 * <li>{@link delete} : delete the value with the specified key from cache</li>
 * <li>{@link flush} : delete all values from cache</li>
 * </ul>
 *
 * Child classes must implement the following methods:
 * <ul>
 * <li>{@link getValue}</li>
 * <li>{@link setValue}</li>
 * <li>{@link addValue}</li>
 * <li>{@link deleteValue}</li>
 * <li>{@link flush} (optional)</li>
 * </ul>
 *
 * CCache also implements ArrayAccess so that it can be used like an array.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching
 * @since 1.0
 */
abstract class CCache extends CApplicationComponent implements ICache, ArrayAccess
{
	/**
	 * @var string a string prefixed to every cache key so that it is unique. Defaults to {@link CApplication::getId() application ID}.
	 */
	public $keyPrefix;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by setting default cache key prefix.
	 */
	public function init()
	{
		parent::init();
		if($this->keyPrefix===null)
			$this->keyPrefix=Yii::app()->getId();
	}

	/**
	 * @param string $key a key identifying a value to be cached
	 * @return sring a key generated from the provided key which ensures the uniqueness across applications
	 */
	protected function generateUniqueKey($key)
	{
		return md5($this->keyPrefix.$key);
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * @param string $id a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache, expired or the dependency has changed.
	 */
	public function get($id)
	{
		if(($value=$this->getValue($this->generateUniqueKey($id)))!==false)
		{
			$data=unserialize($value);
			if(!is_array($data))
				return false;
			if(!($data[1] instanceof ICacheDependency) || !$data[1]->getHasChanged())
			{
				Yii::trace('Serving "'.$id.'" from cache','system.caching.'.get_class($this));
				return $data[0];
			}
		}
		return false;
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * Some caches (such as memcache, apc) allow retrieving multiple cached values at one time,
	 * which may improve the performance since it reduces the communication cost.
	 * In case a cache doesn't support this feature natively, it will be simulated by this method.
	 * @param array $ids list of keys identifying the cached values
	 * @return array list of cached values corresponding to the specified keys. The array
	 * is returned in terms of (key,value) pairs.
	 * If a value is not cached or expired, the corresponding array value will be false.
	 * @since 1.0.8
	 */
	public function mget($ids)
	{
		$uniqueIDs=array();
		$results=array();
		foreach($ids as $id)
		{
			$uniqueIDs[$id]=$this->generateUniqueKey($id);
			$results[$id]=false;
		}
		$values=$this->getValues($uniqueIDs);
		foreach($uniqueIDs as $id=>$uniqueID)
		{
			if(!isset($values[$uniqueID]))
				continue;
			$data=unserialize($values[$uniqueID]);
			if(is_array($data) && (!($data[1] instanceof ICacheDependency) || !$data[1]->getHasChanged()))
			{
				Yii::trace('Serving "'.$id.'" from cache','system.caching.'.get_class($this));
				$results[$id]=$data[0];
			}
		}
		return $results;
	}

	/**
	 * Stores a value identified by a key into cache.
	 * If the cache already contains such a key, the existing value and
	 * expiration time will be replaced with the new ones.
	 *
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labeled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function set($id,$value,$expire=0,$dependency=null)
	{
		Yii::trace('Saving "'.$id.'" to cache','system.caching.'.get_class($this));
		if($dependency!==null)
			$dependency->evaluateDependency();
		$data=array($value,$dependency);
		return $this->setValue($this->generateUniqueKey($id),serialize($data),$expire);
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * Nothing will be done if the cache already contains the key.
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labeled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function add($id,$value,$expire=0,$dependency=null)
	{
		Yii::trace('Adding "'.$id.'" to cache','system.caching.'.get_class($this));
		if($dependency!==null)
			$dependency->evaluateDependency();
		$data=array($value,$dependency);
		return $this->addValue($this->generateUniqueKey($id),serialize($data),$expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * @param string $id the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	public function delete($id)
	{
		Yii::trace('Deleting "'.$id.'" from cache','system.caching.'.get_class($this));
		return $this->deleteValue($this->generateUniqueKey($id));
	}

	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 * @return boolean whether the flush operation was successful.
	 */
	public function flush()
	{
		Yii::trace('Flushing cache','system.caching.'.get_class($this));
		return $this->flushValues();
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This method should be implemented by child classes to retrieve the data
	 * from specific cache storage. The uniqueness and dependency are handled
	 * in {@link get()} already. So only the implementation of data retrieval
	 * is needed.
	 * @param string $key a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 * @throws CException if this method is not overridden by child classes
	 */
	protected function getValue($key)
	{
		throw new CException(Yii::t('yii','{className} does not support get() functionality.',
			array('{className}'=>get_class($this))));
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * The default implementation simply calls {@link getValue} multiple
	 * times to retrieve the cached values one by one.
	 * If the underlying cache storage supports multiget, this method should
	 * be overridden to exploit that feature.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 * @since 1.0.8
	 */
	protected function getValues($keys)
	{
		$results=array();
		foreach($keys as $key)
			$results[$key]=$this->getValue($key);
		return $results;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This method should be implemented by child classes to store the data
	 * in specific cache storage. The uniqueness and dependency are handled
	 * in {@link set()} already. So only the implementation of data storage
	 * is needed.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 * @throws CException if this method is not overridden by child classes
	 */
	protected function setValue($key,$value,$expire)
	{
		throw new CException(Yii::t('yii','{className} does not support set() functionality.',
			array('{className}'=>get_class($this))));
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This method should be implemented by child classes to store the data
	 * in specific cache storage. The uniqueness and dependency are handled
	 * in {@link add()} already. So only the implementation of data storage
	 * is needed.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 * @throws CException if this method is not overridden by child classes
	 */
	protected function addValue($key,$value,$expire)
	{
		throw new CException(Yii::t('yii','{className} does not support add() functionality.',
			array('{className}'=>get_class($this))));
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This method should be implemented by child classes to delete the data from actual cache storage.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 * @throws CException if this method is not overridden by child classes
	 */
	protected function deleteValue($key)
	{
		throw new CException(Yii::t('yii','{className} does not support delete() functionality.',
			array('{className}'=>get_class($this))));
	}

	/**
	 * Deletes all values from cache.
	 * Child classes may implement this method to realize the flush operation.
	 * @return boolean whether the flush operation was successful.
	 * @throws CException if this method is not overridden by child classes
	 * @since 1.1.5
	 */
	protected function flushValues()
	{
		throw new CException(Yii::t('yii','{className} does not support flushValues() functionality.',
			array('{className}'=>get_class($this))));
	}

	/**
	 * Returns whether there is a cache entry with a specified key.
	 * This method is required by the interface ArrayAccess.
	 * @param string $id a key identifying the cached value
	 * @return boolean
	 */
	public function offsetExists($id)
	{
		return $this->get($id)!==false;
	}

	/**
	 * Retrieves the value from cache with a specified key.
	 * This method is required by the interface ArrayAccess.
	 * @param string $id a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache or expired.
	 */
	public function offsetGet($id)
	{
		return $this->get($id);
	}

	/**
	 * Stores the value identified by a key into cache.
	 * If the cache already contains such a key, the existing value will be
	 * replaced with the new ones. To add expiration and dependencies, use the set() method.
	 * This method is required by the interface ArrayAccess.
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 */
	public function offsetSet($id, $value)
	{
		$this->set($id, $value);
	}

	/**
	 * Deletes the value with the specified key from cache
	 * This method is required by the interface ArrayAccess.
	 * @param string $id the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	public function offsetUnset($id)
	{
		$this->delete($id);
	}
}