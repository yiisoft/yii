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
 *   This is used to match the 'on' parameter supplied when calling {@link CModel::validate}.</li>
 * </ul>
 *
 * When using {@link createValidator} to create a validator, the following aliases
 * are recognized as the corresponding built-in validator classes:
 * <ul>
 * <li>required: {@link CRequiredValidator}</li>
 * <li>filter: {@link CFilterValidator}</li>
 * <li>match: {@link CRegularExpressionValidator}</li>
 * <li>email: {@link CEmailValidator}</li>
 * <li>url: {@link CUrlValidator}</li>
 * <li>unique: {@link CUniqueValidator}</li>
 * <li>compare: {@link CCompareValidator}</li>
 * <li>length: {@link CStringValidator}</li>
 * <li>in: {@link CRangeValidator}</li>
 * <li>numerical: {@link CNumberValidator}</li>
 * <li>captcha: {@link CCaptchaValidator}</li>
 * <li>type: {@link CTypeValidator}</li>
 * <li>file: {@link CFileValidator}</li>
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

	private $_on;

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
			'type'=>'CTypeValidator',
			'file'=>'CFileValidator',
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
	 * @param array the list of attributes to be validated. Defaults to null,
	 * meaning every attribute listed in {@link attributes} will be validated.
	 * @param CModel the data object being validated
	 */
	public function validate($object,$attributes=null)
	{
		if(is_array($attributes))
			$attributes=array_intersect($this->attributes,$attributes);
		else
			$attributes=$this->attributes;
		foreach($attributes as $attribute)
			$this->validateAttribute($object,$attribute);
	}

	/**
	 * @return array the set of tags (e.g. insert, register) that indicate when this validator should be applied.
	 * If this is empty, it means the validator should be applied in all situations.
	 * @since 1.0.1
	 */
	public function getOn()
	{
		return $this->_on;
	}

	/**
	 * @param mixed the set of tags (e.g. insert, register) that indicate when this validator should be applied.
 	 * This can be either an array of the tag names or a string of comma-separated tag names.
 	 * @since 1.0.1
	 */
	public function setOn($value)
	{
		if(is_string($value))
			$value=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
		$this->_on=$value;
	}

	/**
	 * Adds an error about the specified attribute to the active record.
	 * This is a helper method that performs message selection and internationalization.
	 * @param CModel the data object being validated
	 * @param string the attribute being validated
	 * @param string the error message
	 * @param array values for the placeholders in the error message
	 */
	protected function addError($object,$attribute,$message,$params=array())
	{
		$params['{attribute}']=$object->getAttributeLabel($attribute);
		$object->addError($attribute,strtr($message,$params));
	}
}

