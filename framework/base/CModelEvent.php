<?php
/**
 * CModelEvent class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModelEvent class.
 *
 * CModelEvent represents the event parameters needed by events raised by a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.0
 */
class CModelEvent extends CEvent
{
	/**
	 * @var boolean whether the model is valid. Defaults to true.
	 * If this is set false, {@link CModel::validate()} will return false and quit the current validation process.
	 */
	public $isValid=true;
}
