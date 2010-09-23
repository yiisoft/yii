<?php
/**
 * CAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CAction is the base class for all controller action classes.
 *
 * CAction provides a way to divide a complex controller into
 * smaller actions in separate class files.
 *
 * Derived classes must implement {@link run()} which is invoked by
 * controller when the action is requested.
 *
 * An action instance can access its controller via {@link getController controller} property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.actions
 * @since 1.0
 */
abstract class CAction extends CComponent implements IAction
{
	private $_id;
	private $_controller;

	/**
	 * Constructor.
	 * @param CController $controller the controller who owns this action.
	 * @param string $id id of the action.
	 */
	public function __construct($controller,$id)
	{
		$this->_controller=$controller;
		$this->_id=$id;
	}

	/**
	 * @return CController the controller who owns this action.
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * @return string id of this action
	 */
	public function getId()
	{
		return $this->_id;
	}
}
