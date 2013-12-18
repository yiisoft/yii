<?php
/**
 * CDecimalValidator class file.
 *
 * @author Evan King <evan.king@bluespurs.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDecimalValidator validates that the attribute value is an SQL decimal value.
 *
 * In addition to the {@link message} property for setting a custom error message,
 * CDecimalValidator has a couple custom error messages you can set that correspond
 * to different validation scenarios. To specify a custom message when the numeric
 * value has too many digits before the decimal, you may use the {@link tooManyIntDigits}
 * property. Similarly with {@link tooManyFloatDigits} for too many digits after the
 * decimal.
 * The messages may contain additional placeholders that will be replaced with the
 * actual content. In addition to the "{attribute}" placeholder, recognized by all
 * validators (see {@link CValidator}), CDecimalValidator allows for the following
 * placeholders to be specified:
 * <ul>
 * <li>{max}: the maximum number of digits on the side of the decimal being validated {@link max}.</li>
 * </ul>
 *
 * @author Evan King <evan.king@bluespurs.com>
 * @package system.validators
 * @since never
 */
class CDecimalValidator extends CValidator
{
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	/**
	 * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
	 */
	public $maxIntDigits;
	/**
	 * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
	 */
	public $maxFloatDigits;
	/**
	 * @var string user-defined error message used when the input is not a number.
	 */
	public $message = '{attribute} must be a decimal number.';
	/**
	 * @var string user-defined error message used when there are too many digits before the decimal.
	 */
	public $tooManyIntDigits = '{attribute} has too many digits before the decimal (maximum is {max}).';
	/**
	 * @var string user-defined error message used when there are too many digits after the decimal.
	 */
	public $tooManyFloatDigits = '{attribute} has too many digits after the decimal (maximum is {max}).';
	/**
	 * @var string the regular expression for matching numbers.
	 * @since 1.1.7
	 */
	public $numberPattern='/^\s*[-+]?[0-9]*(,[0-9]{3})*\.?[0-9]*\s*$/';


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
		
		// Note: localeconv fails to return +- signs even when locale has been set,
		//	and SQL does not accept negative numbers in parenthesized format anyway
		$c = localeconv();
		$thousands = $c['thousands_sep'] ?: ',';
		$decimal = $c['decimal_point'] ?: '.';
		
		if(is_string($value))
			$value=trim(str_replace($thousands,'',$value));
		
		if(!is_numeric($value) || trim($value,'+-.,0123456789'))
		{
			$this->addError($object, $attribute, Yii::t('yii',$this->message));
			return;
		}
		
		$value=rtrim(ltrim($value,'0+-'),'0');
		
		$parts = explode($decimal,$value);
		if(count($parts)>2)
		{
			$this->addError($object, $attribute, Yii::t('yii',$this->message));
		}
		
		@list($intstr, $floatstr) = $parts;
		if((int)$intstr!=$intstr || (int)$floatstr!=$floatstr)
		{
			$this->addError($object,$attribute,Yii::t('yii',$this->message));
		}
		
		if($this->maxIntDigits!==null && strlen($intstr)>$this->maxIntDigits)
		{
			$this->addError(
				$object,
				$attribute,
				Yii::t('yii',$this->tooManyIntDigits),
				array('{max}'=>$this->maxIntDigits)
			);
		}
		
		if($this->maxFloatDigits!==null && strlen($floatstr)>$this->maxFloatDigits)
		{
			$this->addError(
				$object,
				$attribute,
				Yii::t('yii',$this->tooManyFloatDigits),
				array('{max}'=>$this->maxFloatDigits)
			);
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
		if(!$this->enableClientValidation)
			return '';
		
		$label=$object->getAttributeLabel($attribute);
		
		$message=strtr($this->message,array(
			'{attribute}'=>$label,
		));
		
		$tooManyIntDigits=strtr($this->tooManyIntDigits,array(
			'{attribute}'=>$label,
			'{max}'=>$this->maxIntDigits,
		));
		
		$tooManyFloatDigits=strtr($this->tooManyFloatDigits,array(
			'{attribute}'=>$label,
			'{max}'=>$this->maxFloatDigits,
		));
		
		$c = localeconv();
		$thousands = $c['thousands_sep'] ?: ',';
		$decimal = $c['decimal_point'] ?: '.';
		$thousandsMatch = ($thousands == '.') ? "\\." : $thousands;
		$decimalMatch = ($decimal == '.') ? "\\." : $decimal;
		$pattern=$this->numberPattern;
		$pattern = str_replace("\\.",$decimalMatch,$pattern);
		$pattern = str_replace(',',$thousandsMatch,$pattern);
		
		$js="
if(typeof(value)!='undefined' && value!==null && jQuery.trim(value)!='') {
	value = String(value).replace(/^[+-]?0*/, '').replace(/0+$/, '');
";

		$js.="
	if(!value.match($pattern)) {
		messages.push(".CJSON::encode($message).");
	} else {
";
		if($this->maxIntDigits!==null || $this->maxFloatDigits!==null)
		{
			$js.="
		var parts = value.replace(/$thousandsMatch/g,\"\").split(\"$decimal\");
";
		}
		if($this->maxIntDigits!==null)
		{
			$js.="
		if(parts[0] && parts[0].length>{$this->maxIntDigits}) {
			messages.push(".CJSON::encode($tooManyIntDigits).");
		}
";
		}
		if($this->maxFloatDigits!==null)
		{
			$js.="
		if(parts[1] && parts[1].length>{$this->maxFloatDigits}) {
			messages.push(".CJSON::encode($tooManyFloatDigits).");
		}
";
		}
		$js .= "
	}
}";

		if(!$this->allowEmpty)
		{
			$js.=" else {
	messages.push(".CJSON::encode($message).");
}";
		}

		return $js;
	}
}
