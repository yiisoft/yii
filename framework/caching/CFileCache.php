<?php
/**
 * CFileCache class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFileCache provides a file-based caching mechanism.
 *
 * For each data value being cached, CFileCache will use store it in a separate file
 * under {@link cachePath} which defaults to 'protected/runtime/cache'.
 * CFileCache will perform garbage collection automatically to remove expired cache files.
 *
 * See {@link CCache} manual for common cache operations that are supported by CFileCache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching
 * @since 1.0.6
 */
class CFileCache extends CCache
{
	/**
	 * @var string the directory to store cache files. Defaults to null, meaning
	 * using 'protected/runtime/cache' as the directory.
	 */
	public $cachePath;
	/**
	 * @var string cache file suffix. Defaults to '.bin'.
	 */
	public $cacheFileSuffix='.bin';

	private $_gcProbability=1;

	/**
	 * Initializes this application component.
	 * This method is required by the {@link IApplicationComponent} interface.
	 * It checks the availability of memcache.
	 * @throws CException if APC cache extension is not loaded or is disabled.
	 */
	public function init()
	{
		parent::init();
		if($this->cachePath===null)
			$this->cachePath=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'cache';
		if(is_dir($this->cachePath))
		{
			if(rand(0,100)<$this->_gcProbability)
				$this->gc();
		}
		else
			mkdir($this->cachePath,0777,true);
	}

	/**
	 * @return integer the probability (percentage) that gc (garbage collection) should be performed upon initializing the file cache component. Defaults to 1, meaning 1% chance.
	 */
	public function getGCProbability()
	{
		return $this->_gcProbability;
	}

	/**
	 * @param integer the probability (percentage) that gc (garbage collection) should be performed upon initializing the file cache component.
	 * This number should be between 0 and 100. A value 0 meaning no GC will be performed at all.
	 * While a value 100 meaning GC is performed each time the file cache is loaded.
	 */
	public function setGCProbability($value)
	{
		$value=(int)$value;
		if($value<0)
			$value=0;
		if($value>100)
			$value=100;
		$this->_gcProbability=$value;
	}

	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 */
	public function flush()
	{
		return $this->gc(false);
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		$cacheFile=$this->getCacheFile($key);
		if(is_file($cacheFile))
		{
			if(@filemtime($cacheFile)>time())
				return file_get_contents($cacheFile);
			else
				@unlink($cacheFile);
		}
		return false;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string the key identifying the value to be cached
	 * @param string the value to be cached
	 * @param integer the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key,$value,$expire)
	{
		if($expire<=0)
			$expire=31536000; // 1 year
		$expire+=time();

		$cacheFile=$this->getCacheFile($key);
		if(@file_put_contents($cacheFile,$value,LOCK_EX)==strlen($value))
		{
			@chmod($cacheFile,0777);
			return @touch($cacheFile,$expire);
		}
		else
			return false;
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string the key identifying the value to be cached
	 * @param string the value to be cached
	 * @param integer the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key,$value,$expire)
	{
		$cacheFile=$this->getCacheFile($key);
		if(@filemtime($cacheFile)>time())
			return false;
		return $this->setValue($key,$value,$expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		$cacheFile=$this->getCacheFile($key);
		return @unlink($cacheFile);
	}

	/**
	 * Returns the cache file path given the cache key.
	 * @param string cache key
	 * @return string the cache file path
	 */
	protected function getCacheFile($key)
	{
		return $this->cachePath.DIRECTORY_SEPARATOR.$key.$this->cacheFileSuffix;
	}

	/**
	 * Removes expired cache files.
	 * @param boolean whether to removed expired cache files only. If true, all cache files under {@link cachePath} will be removed.
	 */
	protected function gc($expiredOnly=true)
	{
		if(($handle=opendir($this->cachePath))===false)
			return;
		while($file=readdir($handle))
		{
			if($file[0]==='.' || !is_file($file=$this->cachePath.DIRECTORY_SEPARATOR.$file))
				continue;
			if($expiredOnly && filemtime($file)<time() || !$expiredOnly)
				@unlink($file);
		}
		closedir($handle);
	}
}
