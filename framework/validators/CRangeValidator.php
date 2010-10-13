<?php
/**
 * CRangeValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CRangeValidator validates that the attribute value is among the list (specified via {@link range}).
 * You may invert the validation logic with help of the {@link not} property (available since 1.1.5).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
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
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if(!is_array($this->range))
			throw new CException(Yii::t('yii','The "range" property must be specified with a list of values.'));
		if(!$this->not && !in_array($value,$this->range,$this->strict))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is not in the list.');
			$this->addError($object,$attribute,$message);				
		}
		else if($this->not && in_array($value,$this->range,$this->strict))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is in the list.');
			$this->addError($object,$attribute,$message);		
		}
	}
}