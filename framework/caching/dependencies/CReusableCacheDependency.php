<?php
/**
 * CReusableCacheDependency class file.
 *
 * @author Charles Pick <charles.pick@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CReusableCacheDependency represents a reusable list of cache dependencies.
 * Dependencies in this chain will only be evaluated once per request, apart from
 * this it is identical to {@link CChainedCacheDependency}
 *
 * @author Charles Pick <charles.pick@gmail.com>
 * @package system.caching.dependencies
 * @since 1.1.11
 */
class CReusableCacheDependency extends CChainedCacheDependency
{
	private static $_data = array();
	private $_hash;
	/**
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 */
	public function evaluateDependency()
	{
		$hash=$this->getHash();
		if(isset(self::$_data[$hash]['dependencies']))
		{
			$this->setDependencies(self::$_data[$hash]['dependencies']);
			return;
		}
		parent::evaluateDependency();
		if(!isset(self::$_data[$hash]))
		{
			self::$_data[$hash]=array();
		}
		self::$_data[$hash]['dependencies']=$this->getDependencies();
	}

	/**
	 * Performs the actual dependency checking.
	 * This method returns true if any of the dependency objects
	 * reports a dependency change.
	 * @return boolean whether the dependency is changed or not.
	 */
	public function getHasChanged()
	{
		$hash=$this->getHash();
		if(!isset(self::$_data[$hash]['hasChanged']))
		{
			self::$_data[$hash]['hasChanged']=parent::getHasChanged();
		}
		return self::$_data[$hash]['hasChanged'];
	}
	/**
	 * Generates a unique hash that identifies this cache dependency.
	 * @return string the hash for this cache dependency
	 */
	public function getHash() {
		if($this->_hash===null)
		{
			$this->_hash=sha1(serialize($this));
		}
		return $this->_hash;
	}
}