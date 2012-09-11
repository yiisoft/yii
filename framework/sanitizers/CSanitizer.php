<?php
/**
 * CSanitizer class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSanitizer is the base class for all sanitizors.
 *
 * Child classes must implement the {@link sanitizeAttribute} method.
 *
 * The following properties are defined in CSanitizer:
 * <ul>
 * <li>{@link attributes}: array, list of attributes to be sanitized;</li>
 * <li>{@link on}: string, in which scenario should the sanitizer be in effect.
 *   This is used to match the 'on' parameter supplied when calling {@link CModel::sanitize}.</li>
 * </ul>
 *
 * When using {@link createSanitizer} to create a sanitizer, the following aliases
 * are recognized as the corresponding built-in sanitizer classes:
 * <ul>
 * <li>trim: {@link CTrimSanitizer}</li>
 * <li>number: {@link CNumberSanitizer}</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Suralc <thesurwaveing@googlemail.com>
 * @package system.sanitizers
 * @since 1.1.13
 */
abstract class CSanitizer extends CComponent
{
	/**
	 * @var array list of built-in sanitizers (name=>class)
	 */
	public static $builtInSanitizers=array(
				'number'=>'CNumberSanitizer',
				'trim'=>'CTrimSanitizer',
	);

	/**
	 * @var array list of attributes to be sanitized.
	 */
	public $attributes;
	/**
	 * @var boolean whether this sanitizion rule should be skipped when there is already a sanitzing
	 * error for the current attribute. Defaults to false.
	 */
	public $skipOnError=false;
	/**
	 * @var array list of scenarios that the sanitizer should be applied.
	 * Each array value refers to a scenario name with the same name as its array key.
	 */
	public $on;
	/**
	 * @var array list of scenarios that the sanitizer should not be applied to.
	 * Each array value refers to a scenario name with the same name as its array key.
	 */
	public $except;
	/**
	 * @var boolean whether attributes listed with this sanitizer should be considered safe for massive assignment.
	 * Defaults to true.
	 */
	public $safe=true;
		
	/**
	 * The model the sanitizer is called on.
	 * @var CModel 
	 */
	private $_model;

	/**
	 * Sanitizes a single attribute.
	 * This method should be overridden by child classes.
	 * @param CModel $object the data object being sanitized
	 * @param string $attribute the name of the attribute to be sanitized.
	 */
	abstract protected function sanitizeAttribute($object,$attribute);


	/**
	 * Creates a santizer object.
	 * @param string $name the name or class of the sanitizer
	 * @param CModel $object the data object being sanitized that may contain the inline sanitization method
	 * @param mixed $attributes list of attributes to be sanitized. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 * @param array $params initial values to be applied to the sanitizers properties
	 * @return CValidator the validator
	 */
	public static function createSanitizer($name,$object,$attributes,$params=array())
	{
		if(is_string($attributes))
			$attributes=preg_split('/[\s,]+/',$attributes,-1,PREG_SPLIT_NO_EMPTY);
		if(isset($params['on']))
		{
			if(is_array($params['on']))
				$on=$params['on'];
			else
				$on=preg_split('/[\s,]+/',$params['on'],-1,PREG_SPLIT_NO_EMPTY);
		}
		else
			$on=array();

		if(isset($params['except']))
		{
			if(is_array($params['except']))
				$except=$params['except'];
			else
				$except=preg_split('/[\s,]+/',$params['except'],-1,PREG_SPLIT_NO_EMPTY);
		}
		else
			$except=array();

		if(method_exists($object,$name))
		{
			$sanitizer=new CInlineSanitizer();
			$sanitizer->attributes=$attributes;
			$sanitizer->method=$name;
			$sanitizer->params=$params;
			if(isset($params['skipOnError']))
				$sanitizer->skipOnError=$params['skipOnError'];
		}
		else
		{
			$params['attributes']=$attributes;
			if(isset(self::$builtInSanitizers[$name]))
				$className=Yii::import(self::$builtInSanitizers[$name],true);
			else
				$className=Yii::import($name,true);
			$sanitizer=new $className;
			foreach($params as $name=>$value)
				$sanitizer->$name=$value;
		}

		$sanitizer->on=empty($on)?array():array_combine($on,$on);
		$sanitizer->except=empty($except)?array():array_combine($except,$except);
		$sanitizer->setModel($object);
		
		return $sanitizer;
	}

	/**
	 * Sanitizes the specified object.
	 * @param CModel $object the data object being sanitized
	 * @param array $attributes the list of attributes to be sanitized. Defaults to null,
	 * meaning every attribute listed in {@link attributes} will be santized.
	 */
	public function sanitize($object,$attributes=null)
	{
		if(is_array($attributes))
			$attributes=array_intersect($this->attributes,$attributes);
		else
			$attributes=$this->attributes;
		foreach($attributes as $attribute)
		{
			if(!$this->skipOnError || !$object->hasErrors($attribute))
				$this->sanitizeAttribute($object,$attribute);
		}
	}

	/**
	 * Returns a value indicating whether the sanatizer applies to the specified scenario.
	 * A sanatizer applies to a scenario as long as any of the following conditions is met:
	 * <ul>
	 * <li>the sanatizer's "on" property is empty</li>
	 * <li>the sanatizer's "on" property contains the specified scenario</li>
	 * </ul>
	 * @param string $scenario scenario name
	 * @return boolean whether the sanatizer applies to the specified scenario.
	 */
	public function applyTo($scenario)
	{
		if(isset($this->except[$scenario]))
			return false;
		return empty($this->on) || isset($this->on[$scenario]);
	}

	/**
	 * Adds an error about the specified attribute to the active record.
	 * This is a helper method that performs message selection and internationalization.
	 * @param CModel $object the data object being sanatized
	 * @param string $attribute the attribute being sanatized
	 * @param string $message the error message
	 * @param array $params values for the placeholders in the error message
	 */
	protected function addError($object,$attribute,$message,$params=array())
	{
		$params['{attribute}']=$object->getAttributeLabel($attribute);
		$object->addError($attribute,strtr($message,$params));
	}

	/**
	 * Checks if the given value is empty.
	 * A value is considered empty if it is null, an empty array, or the trimmed result is an empty string.
	 * Note that this method is different from PHP empty(). It will return false when the value is 0.
	 * @param mixed $value the value to be checked
	 * @param boolean $trim whether to perform trimming before checking if the string is empty. Defaults to false.
	 * @return boolean whether the value is empty
	 */
	protected function isEmpty($value,$trim=false)
	{
		return $value===null || $value===array() || $value==='' || $trim && is_scalar($value) && trim($value)==='';
	}
	/**
	 * Sets the model this sanatizer is based on
	 * @param CModel $value The model this sanatizer is based on.
	 */
	public function setModel($value)
	{
		if($this->_model===null)
		{
			if($value instanceof CModel)
				$this->_model=$value;
			else
				throw new CException(Yii::t('yii', 'You may only use a model as sanitization target.'));
		}
	}
	/**
	 * Returns the model used in this sanatizer
	 * @return CModel the model used in this sanatizer
	 */ 
	public function getModel()
	{
		return $this->_model;
	}
}