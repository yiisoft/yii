<?php
/**
 * CUrlValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CUrlValidator validates that the attribute value is a valid http or https URL.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CUrlValidator extends CValidator
{
	/**
	 * @var string the regular expression used to validates the attribute value.
	 */
	public $pattern='/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
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
		if(!$this->validateValue($value))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not a valid URL.');
			$this->addError($object,$attribute,$message);
		}
	}

	/**
	 * Validates a static value to see if it is a valid URL.
	 * Note that this method does not respect {@link allowEmpty} property.
	 * This method is provided so that you can call it directly without going through the model validation rule mechanism.
	 * @param mixed $value the value to be validated
	 * @return boolean whether the value is a valid URL
	 * @since 1.1.1
	 */
	public function validateValue($value)
	{
		return is_string($value) && preg_match($this->pattern,$value);
	}
}

