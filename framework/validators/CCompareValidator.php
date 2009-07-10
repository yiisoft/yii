<?php
/**
 * CCompareValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCompareValidator compares the specified attribute value with another value and validates if they are equal.
 *
 * The value being compared with can be another attribute value
 * (specified via {@link compareAttribute}) or a constant (specified via
 * {@link compareValue}. When both are specified, the latter takes
 * precedence. If neither is specified, the attribute will be compared
 * with another attribute whose name is by appending "_repeat" to the source
 * attribute name.
 *
 * The comparison can be either {@link strict} or not.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CCompareValidator extends CValidator
{
	/**
	 * @var string the name of the attribute to be compared with
	 */
	public $compareAttribute;
	/**
	 * @var string the constant value to be compared with
	 */
	public $compareValue;
	/**
	 * @var boolean whether the comparison is strict (both value and type must be the same.)
	 * Defaults to false.
	 */
	public $strict=false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to false.
	 * If this is true, it means the attribute is considered valid when it is empty.
	 */
	public $allowEmpty=false;

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
		if($this->compareValue!==null)
			$compareTo=$compareValue=$this->compareValue;
		else
		{
			$compareAttribute=$this->compareAttribute===null ? $attribute.'_repeat' : $this->compareAttribute;
			$compareValue=$object->$compareAttribute;
			$compareTo=$object->getAttributeLabel($compareAttribute);
		}
		if(($this->strict && $value!==$compareValue) || (!$this->strict && $value!=$compareValue))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be repeated exactly.');
			$this->addError($object,$attribute,$message,array('{compareAttribute}'=>$compareTo));
		}
	}
}

