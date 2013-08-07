<?php
/**
 * CNumberValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CNumberValidator validates that the attribute value is a number.
 *
 * In addition to the {@link message} property for setting a custom error message,
 * CNumberValidator has a couple custom error messages you can set that correspond to different
 * validation scenarios. To specify a custom message when the numeric value is too big,
 * you may use the {@link tooBig} property. Similarly with {@link tooSmall}.
 * The messages may contain additional placeholders that will be replaced
 * with the actual content. In addition to the "{attribute}" placeholder, recognized by all
 * validators (see {@link CValidator}), CNumberValidator allows for the following placeholders
 * to be specified:
 * <ul>
 * <li>{min}: when using {@link tooSmall}, replaced with the lower limit of the number {@link min}.</li>
 * <li>{max}: when using {@link tooBig}, replaced with the upper limit of the number {@link max}.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 * @since 1.0
 */
class CNumberValidator extends CValidator
{
	/**
	 * @var boolean whether the attribute value can only be an integer. Defaults to false.
	 */
	public $integerOnly=false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	/**
	 * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
	 */
	public $max;
	/**
	 * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
	 */
	public $min;
	/**
	 * @var string user-defined error message used when the value is too big.
	 */
	public $tooBig;
	/**
	 * @var string user-defined error message used when the value is too small.
	 */
	public $tooSmall;
	/**
	 * @var string the regular expression for matching integers.
	 * @since 1.1.7
	 */
	public $integerPattern='/^\s*[+-]?\d+\s*$/';
	/**
	 * @var string the regular expression for matching numbers.
	 * @since 1.1.7
	 */
	public $numberPattern='/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';


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
		if(!is_numeric($value))
		{
			// https://github.com/yiisoft/yii/issues/1955
			// https://github.com/yiisoft/yii/issues/1669
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be a number.');
			$this->addError($object,$attribute,$message);
			return;
		}
		if($this->integerOnly)
		{
			if(!preg_match($this->integerPattern,"$value"))
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be an integer.');
				$this->addError($object,$attribute,$message);
			}
		}
		else
		{
			if(!preg_match($this->numberPattern,"$value"))
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} must be a number.');
				$this->addError($object,$attribute,$message);
			}
		}
		if($this->min!==null && $value<$this->min)
		{
			$message=$this->tooSmall!==null?$this->tooSmall:Yii::t('yii','{attribute} is too small (minimum is {min}).');
			$this->addError($object,$attribute,$message,array('{min}'=>$this->min));
		}
		if($this->max!==null && $value>$this->max)
		{
			$message=$this->tooBig!==null?$this->tooBig:Yii::t('yii','{attribute} is too big (maximum is {max}).');
			$this->addError($object,$attribute,$message,array('{max}'=>$this->max));
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.7
	 */
	public function clientValidateAttribute($object,$attribute)
	{
		$label=$object->getAttributeLabel($attribute);

		if(($message=$this->message)===null)
			$message=$this->integerOnly ? Yii::t('yii','{attribute} must be an integer.') : Yii::t('yii','{attribute} must be a number.');
		$message=strtr($message, array(
			'{attribute}'=>$label,
		));

		if(($tooBig=$this->tooBig)===null)
			$tooBig=Yii::t('yii','{attribute} is too big (maximum is {max}).');
		$tooBig=strtr($tooBig, array(
			'{attribute}'=>$label,
			'{max}'=>$this->max,
		));

		if(($tooSmall=$this->tooSmall)===null)
			$tooSmall=Yii::t('yii','{attribute} is too small (minimum is {min}).');
		$tooSmall=strtr($tooSmall, array(
			'{attribute}'=>$label,
			'{min}'=>$this->min,
		));

		$pattern=$this->integerOnly ? $this->integerPattern : $this->numberPattern;
		$js="
if(!value.match($pattern)) {
	messages.push(".CJSON::encode($message).");
}
";
		if($this->min!==null)
		{
			$js.="
if(value<{$this->min}) {
	messages.push(".CJSON::encode($tooSmall).");
}
";
		}
		if($this->max!==null)
		{
			$js.="
if(value>{$this->max}) {
	messages.push(".CJSON::encode($tooBig).");
}
";
		}

		if($this->allowEmpty)
		{
			$js="
if(jQuery.trim(value)!='') {
	$js
}
";
		}

		return $js;
	}
}
