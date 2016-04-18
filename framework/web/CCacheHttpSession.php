<?php
/**
 * CCacheHttpSession class
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CCacheHttpSession implements a session component using cache as storage medium.
 *
 * The cache being used can be any cache application component implementing {@link ICache} interface.
 * The ID of the cache application component is specified via {@link cacheID}, which defaults to 'cache'.
 *
 * Beware, by definition cache storage are volatile, which means the data stored on them
 * may be swapped out and get lost. Therefore, you must make sure the cache used by this component
 * is NOT volatile. If you want to use {@link CDbCache} as storage medium, use {@link CDbHttpSession}
 * is a better choice.
 *
 * @property boolean $useCustomStorage Whether to use custom storage.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class CCacheHttpSession extends CHttpSession
{
	/**
	 * Prefix to the keys for storing cached data
	 */
	const CACHE_KEY_PREFIX='Yii.CCacheHttpSession.';
	/**
	 * @var string the ID of the cache application component. Defaults to 'cache' (the primary cache application component.)
	 */
	public $cacheID='cache';

	/**
	 * @var ICache the cache component
	 */
	private $_cache;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by checking if cache is available.
	 */
	public function init()
	{
		$this->_cache=Yii::app()->getComponent($this->cacheID);
		if(!($this->_cache instanceof ICache))
			throw new CException(Yii::t('yii','CCacheHttpSession.cacheID is invalid. Please make sure "{id}" refers to a valid cache application component.',
				array('{id}'=>$this->cacheID)));
		parent::init();
	}

	/**
	 * Returns a value indicating whether to use custom session storage.
	 * This method overrides the parent implementation and always returns true.
	 * @return boolean whether to use custom storage.
	 */
	public function getUseCustomStorage()
	{
		return true;
	}

	/**
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		$data=$this->_cache->get($this->calculateKey($id));
		return $data===false?'':$data;
	}

	/**
	 * Session write handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id,$data)
	{
		return $this->_cache->set($this->calculateKey($id),$data,$this->getTimeout());
	}

	/**
	 * Session destroy handler.
	 * Do not call this method directly.
	 *
	 * Since 1.1.18 release, this method always returns true.
	 * Please refer to the following issue for more details:
	 * {@link https://github.com/yiisoft/yii/issues/4020}
	 *
	 * @param string $id session ID
	 * @return boolean true if no error happens during deletion
	 */
	public function destroySession($id)
	{
		$this->_cache->delete($this->calculateKey($id));
		return true;
	}

	/**
	 * Generates a unique key used for storing session data in cache.
	 * @param string $id session variable name
	 * @return string a safe cache key associated with the session variable name
	 */
	protected function calculateKey($id)
	{
		return self::CACHE_KEY_PREFIX.$id;
	}
}
