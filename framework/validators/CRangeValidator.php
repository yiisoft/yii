<?php
/**
 * CRangeValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CRangeValidator validates that the attribute value is among the list (specified via {@link range}).
 * You may invert the validation logic with help of the {@link not} property (available since 1.1.5).
 * For example,
 * <pre>
 * class QuestionForm extends CFormModel
 * {
 *     public function rules()
 *     {
 *         return array(
 *             array('text, tag', 'required'),
 *             array('text, 'type', 'type' => 'string'),
 *             array('tag, 'in', 'range' => array('php', 'mysql', 'jquery')),
 *         );
 *     }
 * }
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 * @since 1.0
 */
class CRangeValidator extends CValidator
{
	/**
	 * @var array list of valid values that the attribute value should be among
	 */
	public $range;
	/**
	 * @var boolean whether the comparison is strict (both type and value must be the same)
	 */
	public $strict=false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the attribute value should NOT be among the list of values defined via {@link range}.
	 * @since 1.1.5
	 **/
 	public $not=false;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @throws CException if given {@link range} is not an array
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if(!is_array($this->range))
			throw new CException(Yii::t('yii','The "range" property must be specified with a list of values.'));
		$result = false;
		if($this->strict)
			$result=in_array($value,$this->range,true);
		else
		{
			foreach($this->range as $r)
			{
				$result = $r === '' || $value === '' ? $r === $value : $r == $value;
				if($result)
					break;
			}
		}
		if(!$this->not && !$result)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not in the list.');
			$this->addError($object,$attribute,$message);
		}
		elseif($this->not && $result)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is in the list.');
			$this->addError($object,$attribute,$message);
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @throws CException if given {@link range} is not an array
	 * @return string the client-side validation script.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.7
	 */
	public function clientValidateAttribute($object,$attribute)
	{
		if(!is_array($this->range))
			throw new CException(Yii::t('yii','The "range" property must be specified with a list of values.'));

		if(($message=$this->message)===null)
			$message=$this->not ? Yii::t('yii','{attribute} is in the list.') : Yii::t('yii','{attribute} is not in the list.');
		$message=strtr($message,array(
			'{attribute}'=>$object->getAttributeLabel($attribute),
		));

		$range=array();
		foreach($this->range as $value)
			$range[]=(string)$value;
		$range=CJSON::encode($range);

		return "
if(".($this->allowEmpty ? "jQuery.trim(value)!='' && " : '').($this->not ? "jQuery.inArray(value, $range)>=0" : "jQuery.inArray(value, $range)<0").") {
	messages.push(".CJSON::encode($message).");
}
";
	}
}