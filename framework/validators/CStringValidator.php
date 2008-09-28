<?php
/**
 * CStringValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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
	 * @var integer maximum length.
	 */
	public $max;
	/**
	 * @var integer minimum length.
	 */
	public $min;
	/**
	 * @var integer exact length.
	 */
	public $is;
	/**
	 * @var string user-defined error message used when the value is too long.
	 */
	public $tooShort;
	/**
	 * @var string user-defined error message used when the value is too short.
	 */
	public $tooLong;
	/**
	 * @var string user-defined error message used when the value length is not the expected one.
	 */
	public $wrongLength;
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
		$length=strlen($value);
		if($this->min!==null && $length<$this->min)
			$this->addError($object,$attribute,$this->tooShort,'yii##{attribute} is too short (minimum is {min} characters).',array('{min}'=>$this->min));
		if($this->max!==null && $length>$this->max)
			$this->addError($object,$attribute,$this->tooLong,'yii##{attribute} is too long (maximum is {max} characters).',array('{max}'=>$this->max));
		if($this->is!==null && $length!==$this->is)
			$this->addError($object,$attribute,$this->wrongLength,'yii##{attribute} is of the wrong length (should be {length} characters).',array('{length}'=>$this->is));
	}
}

