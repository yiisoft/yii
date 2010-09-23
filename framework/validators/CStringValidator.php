<?php
/**
 * CStringValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CStringValidator validates that the attribute value is of certain length.
 *
 * Note, this validator should only be used with string-typed attributes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CStringValidator extends CValidator
{
	/**
	 * @var integer maximum length. Defaults to null, meaning no maximum limit.
	 */
	public $max;
	/**
	 * @var integer minimum length. Defaults to null, meaning no minimum limit.
	 */
	public $min;
	/**
	 * @var integer exact length. Defaults to null, meaning no exact length limit.
	 */
	public $is;
	/**
	 * @var string user-defined error message used when the value is too short.
	 */
	public $tooShort;
	/**
	 * @var string user-defined error message used when the value is too long.
	 */
	public $tooLong;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	/**
	 * @var string the encoding of the string value to be validated (e.g. 'UTF-8').
	 * Setting this property requires you to enable mbstring PHP extension.
	 * The value of this property will be used as the 2nd parameter of the mb_strlen() function.
	 * Defaults to false, which means the strlen() function will be used for calculating the length
	 * of the string.
	 * @since 1.1.1
	 */
	public $encoding=false;

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
		if($this->encoding!==false && function_exists('mb_strlen'))
			$length=mb_strlen($value,$this->encoding);
		else
			$length=strlen($value);
		if($this->min!==null && $length<$this->min)
		{
			$message=$this->tooShort!==null?$this->tooShort:Yii::t('yii','{attribute} is too short (minimum is {min} characters).');
			$this->addError($object,$attribute,$message,array('{min}'=>$this->min));
		}
		if($this->max!==null && $length>$this->max)
		{
			$message=$this->tooLong!==null?$this->tooLong:Yii::t('yii','{attribute} is too long (maximum is {max} characters).');
			$this->addError($object,$attribute,$message,array('{max}'=>$this->max));
		}
		if($this->is!==null && $length!==$this->is)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is of the wrong length (should be {length} characters).');
			$this->addError($object,$attribute,$message,array('{length}'=>$this->is));
		}
	}
}

