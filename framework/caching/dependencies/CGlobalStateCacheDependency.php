<?php
/**
 * CGlobalStateCacheDependency class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yeeframework.com/
 * @copyright 2008-2013 Yee Software LLC
 * @license http://www.yeeframework.com/license/
 */

/**
 * CGlobalStateCacheDependency represents a dependency based on a global state value.
 *
 * CGlobalStateCacheDependency checks if a global state is changed or not.
 * If the global state is changed, the dependency is reported as changed.
 * To specify which global state this dependency should check with,
 * set {@link stateName} to the name of the global state.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.caching.dependencies
 * @since 1.0
 */
class CGlobalStateCacheDependency extends CCacheDependency
{
	/**
	 * @var string the name of the global state whose value is to check
	 * if the dependency has changed.
	 * @see CApplication::setGlobalState
	 */
	public $stateName;

	/**
	 * Constructor.
	 * @param string $name the name of the global state
	 */
	public function __construct($name=null)
	{
		$this->stateName=$name;
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the value of the global state.
	 * @throws CException if {@link stateName} is empty
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependentData()
	{
		if($this->stateName!==null)
			return Yee::app()->getGlobalState($this->stateName);
		else
			throw new CException(Yee::t('yee','CGlobalStateCacheDependency.stateName cannot be empty.'));
	}
}
