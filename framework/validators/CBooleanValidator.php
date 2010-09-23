<?php
/**
 * CBooleanValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CBooleanValidator validates that the attribute value is either {@link trueValue}  or {@link falseValue}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0.10
 */
class CBooleanValidator extends CValidator
{
	/**
	 * @var mixed the value representing true status. Defaults to '1'.
	 */
	public $trueValue='1';
	/**
	 * @var mixed the value representing false status. Defaults to '0'.
	 */
	public $falseValue='0';
	/**
	 * @var boolean whether the comparison to {@link trueValue} and {@link falseValue} is strict.
	 * When this is true, the attribute value and type must both match those of {@link trueValue} or {@link falseValue}.
	 * Defaults to false, meaning only the value needs to be matched.
	 */
	public $strict=false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if(!$this->strict && $value!=$this->trueValue && $value!=$this->falseValue
			|| $this->strict && $value!==$this->trueValue && $value!==$this->falseValue)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be either {true} or {false}.',
				array('{true}'=>$this->trueValue, '{false}'=>$this->falseValue));
			$this->addError($object,$attribute,$message);
		}
	}
}
