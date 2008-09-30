<?php
/**
 * CValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CValidator is the base class for all validators.
 *
 * Child classes must implement the {@link validateAttribute} method.
 *
 * The following properties are defined in CValidator:
 * <ul>
 * <li>{@link attributes}: array, list of attributes to be validated;</li>
 * <li>{@link message}: string, the customized error message. The message
 *   may contain placeholders that will be replaced with the actual content.
 *   For example, the "{attribute}" placeholder will be replaced with the label
 *   of the problematic attribute. Different validators may define additional
 *   placeholders.</li>
 * <li>{@link on}: string, in which scenario should the validator be in effect.
 *   This can be either "insert" or "update". If not set, the validator will
 *   apply in both scenarios.</li>
 * </ul>
 *
 * When using {@link createValidator} to create a validator, the following aliases
 * are recognized as the corresponding built-in validator classes:
 * <ul>
 * <li>required: {@link CRequiredValidator system.validators.CRequiredValidator}</li>
 * <li>filter: {@link CFilterValidator system.validators.CFilterValidator}</li>
 * <li>match: {@link CRegularExpressionValidator system.validators.CRegularExpressionValidator}</li>
 * <li>email: {@link CEmailValidator system.validators.CEmailValidator}</li>
 * <li>url: {@link CUrlValidator system.validators.CUrlValidator}</li>
 * <li>unique: {@link CUniqueValidator system.validators.CUniqueValidator}</li>
 * <li>compare: {@link CCompareValidator system.validators.CCompareValidator}</li>
 * <li>length: {@link CStringValidator system.validators.CStringValidator}</li>
 * <li>in: {@link CRangeValidator system.validators.CRangeValidator}</li>
 * <li>numerical: {@link CNumberValidator system.validators.CNumberValidator}</li>
 * <li>captcha: {@link CCaptchaValidator system.validators.CCaptchaValidator}</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
abstract class CValidator extends CComponent
{
	/**
	 * @var mixed list of attributes to be validated. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 */
	public $attributes;
	/**
	 * @var string the user-defined error message. Different validators may define various
	 * placeholders in the message that are to be replaced with actual values. All validators
	 * recognize "{attribute}" placeholder, which will be replaced with the label of the attribute.
	 */
	public $message;
	/**
	 * @var string when this validator should be applied. It is either "insert" or "update".
	 * If not set, the validator will be applied in both scenarios.
	 * This is only used for validating CActiveRecord.
	 */
	public $on;

	/**
	 * Validates a single attribute.
	 * This method should be overriden by child classes.
	 * @param CModel the data object being validated
	 * @param string the name of the attribute to be validated.
	 */
	abstract protected function validateAttribute($object,$attribute);


	/**
	 * Creates a validator object.
	 * @param string the name or class of the validator
	 * @param CModel the data object being validated that may contain the inline validation method
	 * @param mixed list of attributes to be validated. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 * @param array initial values to be applied to the validator properties
	 */
	public static function createValidator($name,$object,$attributes,$params)
	{
		static $builtInValidators=array(
			'required'=>'CRequiredValidator',
			'filter'=>'CFilterValidator',
			'match'=>'CRegularExpressionValidator',
			'email'=>'CEmailValidator',
			'url'=>'CUrlValidator',
			'unique'=>'CUniqueValidator',
			'compare'=>'CCompareValidator',
			'length'=>'CStringValidator',
			'in'=>'CRangeValidator',
			'numerical'=>'CNumberValidator',
			'captcha'=>'CCaptchaValidator',
		);

		if(is_string($attributes))
			$attributes=preg_split('/[\s,]+/',$attributes,-1,PREG_SPLIT_NO_EMPTY);

		if(method_exists($object,$name))
		{
			$validator=new CInlineValidator;
			$validator->attributes=$attributes;
			$validator->method=$name;
			if(isset($params['on']))
			{
				$validator->on=$params['on'];
				unset($params['on']);
			}
			$validator->params=$params;
		}
		else
		{
			$params['attributes']=$attributes;
			if(isset($builtInValidators[$name]))
				$className=Yii::import($builtInValidators[$name],true);
			else
				$className=Yii::import($name,true);
			$validator=new $className;
			foreach($params as $name=>$value)
				$validator->$name=$value;
		}
		return $validator;
	}

	/**
	 * Validates the specified object.
	 * @param CModel the data object being validated
	 */
	public function validate($object)
	{
		foreach($this->attributes as $attribute)
			$this->validateAttribute($object,$attribute);
	}

	/**
	 * Adds an error about the specified attribute to the active record.
	 * This is a helper method that performs message selection and internationalization.
	 * @param CModel the data object being validated
	 * @param string the attribute being validated
	 * @param string user-defined error message. If null, the default error message will be used.
	 * @param string the default error message
	 * @param array values for the placeholders in the error message
	 */
	protected function addError($object,$attribute,$message,$defaultMessage,$params=array())
	{
		$params['{attribute}']=$object->getAttributeLabel($attribute);
		$object->addError($attribute,Yii::t($message===null?$defaultMessage:$message,$params));
	}
}


/**
 * CInlineValidator represents a validator which is defined as a method in the object being validated.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CInlineValidator extends CValidator
{
	/**
	 * @var string the name of the validation method defined in the active record class
	 */
	public $method;
	/**
	 * @var array additional parameters that are passed to the validation method
	 */
	public $params;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$method=$this->method;
		$object->$method($attribute,$this->params);
	}
}
