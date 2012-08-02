<?php
/**
 * CModelEvent class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
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
	 * @var boolean whether the model is in valid status and should continue its normal method execution cycles. Defaults to true.
	 * For example, when this event is raised in a {@link CFormModel} object that is executing {@link CModel::beforeValidate},
	 * if this property is set false by the event handler, the {@link CModel::validate} method will quit after handling this event.
	 * If true, the normal execution cycles will continue, including performing the real validations and calling
	 * {@link CModel::afterValidate}.
	 */
	public $isValid=true;
	/**
	 * @var CDbCriteria this property has been introduced in version 1.1.5 to hold the query criterita on {@link CActiveRecord::onBeforeFind} event.
	 * Since version 1.1.7 it is not used anymore and will allways be null.
	 * You can access criteria in {@link CActiveRecord::beforeFind} via <code>$this->getDbCriteria()</code>
	 * and in a behavior via <code>$this->owner->getDbCriteria()</code>.
	 * @since 1.1.5
	 * @deprecated since 1.1.7
	 */
	public $criteria;
}
