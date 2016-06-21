<?php
/**
 * This file contains the base application component class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CApplicationComponent is the base class for application component classes.
 *
 * CApplicationComponent implements the basic methods required by {@link IApplicationComponent}.
 *
 * When developing an application component, try to put application component initialization code in
 * the {@link init()} method instead of the constructor. This has the advantage that
 * the application component can be customized through application configuration.
 *
 * @property boolean $isInitialized Whether this application component has been initialized (ie, {@link init()} is invoked).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
abstract class CApplicationComponent extends CComponent implements IApplicationComponent
{
	/**
	 * @var array the behaviors that should be attached to this component.
	 * The behaviors will be attached to the component when {@link init} is called.
	 * Please refer to {@link CModel::behaviors} on how to specify the value of this property.
	 */
	public $behaviors=array();

	private $_initialized=false;

	/**
	 * Initializes the application component.
	 * This method is required by {@link IApplicationComponent} and is invoked by application.
	 * If you override this method, make sure to call the parent implementation
	 * so that the application component can be marked as initialized.
	 */
	public function init()
	{
		$this->attachBehaviors($this->behaviors);
		$this->_initialized=true;
	}

	/**
	 * Checks if this application component has been initialized.
	 * @return boolean whether this application component has been initialized (ie, {@link init()} is invoked).
	 */
	public function getIsInitialized()
	{
		return $this->_initialized;
	}
}
