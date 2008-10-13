<?php
/**
 * CEmailValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CEmailValidator validates that the attribute value is a valid email address.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CEmailValidator extends CValidator
{
	/**
	 * @var string the regular expression used to validates the attribute value.
	 */
	public $pattern='/^\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*$/';
	/**
	 * @var boolean whether to check the MX record for the email address.
	 * Defaults to false. To enable it, you need to make sure the PHP function 'checkdnsrr'
	 * exists in your PHP installation.
	 */
	public $checkMX=false;
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
		if(($valid=preg_match($this->pattern,$value)) && $this->checkMX && function_exists('checkdnsrr'))
		{
			$pos=strpos($value,'@');
			$domain=substr($value,$pos+1);
			$valid=checkdnsrr($domain,'MX');
		}
		if(!$valid)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii#{attribute} is not a valid email address.');
			$this->addError($object,$attribute,$message);
		}
	}
}
