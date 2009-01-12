<?php
/**
 * CUniqueValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CUniqueValidator validates that the attribute value is unique in the corresponding database table.
 *
 * CUniqueValidator can only be used for active record objects.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CUniqueValidator extends CValidator
{
	/**
	 * @var boolean whether the comparison is case sensitive. Defaults to true.
	 * Note, by setting it to false, you are assuming the attribute type is string.
	 */
	public $caseSensitive=true;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && ($value===null || $value===''))
			return;

		$column=$object->getTableSchema()->getColumn($attribute);
		if($column===null)
			throw new CException(Yii::t('yii','{class} does not have attribute "{attribute}".',
				array('{class}'=>get_class($object), '{attribute}'=>$attribute)));

		$columnName=$column->rawName;
		$criteria=array(
			'condition'=>$this->caseSensitive ? "$columnName=:value" : "LOWER($columnName)=LOWER(:value)",
			'params'=>array(':value'=>$value),
		);
		if($column->isPrimaryKey)
			$exists=$object->exists($criteria);
		else
		{
			// need to exclude the current record based on PK
			$criteria['limit']=2;
			$objects=$object->findAll($criteria);
			$n=count($objects);
			if($n===1)
				$exists=$objects[0]->getPrimaryKey()!=$object->getPrimaryKey();
			else
				$exists=$n>1;
		}

		if($exists)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} "{value}" has already been taken.');
			$this->addError($object,$attribute,$message,array('{value}'=>$value));
		}
	}
}

