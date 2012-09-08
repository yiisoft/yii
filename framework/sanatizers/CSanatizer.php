<?php
/**
 * CSanatizer class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSanatizer is the base class for all validators.
 *
 * Child classes must implement the {@link sanatizeAttribute} method.
 *
 * The following properties are defined in CSanatizer:
 * <ul>
 * <li>{@link attributes}: array, list of attributes to be sanatized;</li>
 * <li>{@link on}: string, in which scenario should the sanatizer be in effect.
 *   This is used to match the 'on' parameter supplied when calling {@link CModel::sanatize}.</li>
 * </ul>
 *
 * When using {@link createSanatizer} to create a sanatizer, the following aliases
 * are recognized as the corresponding built-in sanatizer classes:
 * <ul>
 * <li>trim: {@link CTrimSanatizer}</li>
 * <li>trim: {@link CNumberSanatizer}</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Suralc <thesurwaveing@googlemail.com>
 * @version $Id$
 * @package system.sanatizers
 * @since 1.1.13
 */
abstract class CSanatizer extends CComponent
{
	/**
	 * @var array list of built-in validators (name=>class)
	 */
	public static $builtInSanatizers=array(
                'number'=>'CNumberSanatizer',
                'trim'=>'CTrimSanatizer'
	);

	/**
	 * @var array list of attributes to be validated.
	 */
	public $attributes;
	/**
	 * @var string the user-defined error message. Different validators may define various
	 * placeholders in the message that are to be replaced with actual values. All validators
	 * recognize "{attribute}" placeholder, which will be replaced with the label of the attribute.
	 */
	public $message;
	/**
	 * @var boolean whether this validation rule should be skipped when there is already a validation
	 * error for the current attribute. Defaults to false.
	 */
	public $skipOnError=false;
	/**
	 * @var array list of scenarios that the validator should be applied.
	 * Each array value refers to a scenario name with the same name as its array key.
	 */
	public $on;
	/**
	 * @var array list of scenarios that the validator should not be applied to.
	 * Each array value refers to a scenario name with the same name as its array key.
	 */
	public $except;
	/**
	 * @var boolean whether attributes listed with this validator should be considered safe for massive assignment.
	 * Defaults to true.
	 */
	public $safe=true;
	/**
	 * @var boolean whether to perform client-side validation. Defaults to true.
	 * Please refer to {@link CActiveForm::enableClientValidation} for more details about client-side validation.
	 */
	public $enableClientValidation=true;
        
        /**
         * The model the sanatizer is called on.
         * @var CModel 
         */
        private $_model;

	/**
	 * Sanatizes a single attribute.
	 * This method should be overridden by child classes.
	 * @param CModel $object the data object being sanatized
	 * @param string $attribute the name of the attribute to be validated.
	 */
	abstract protected function sanatizeAttribute($object,$attribute);


	/**
	 * Creates a sanatizer object.
	 * @param string $name the name or class of the sanatizer
	 * @param CModel $object the data object being validated that may contain the inline validation method
	 * @param mixed $attributes list of attributes to be sanatized. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 * @param array $params initial values to be applied to the sanatizers properties
	 * @return CValidator the validator
	 */
	public static function createSanatizer($name,$object,$attributes,$params=array())
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
			$sanatizer=new CInlineSanatizer();
			$sanatizer->attributes=$attributes;
			$sanatizer->method=$name;
			$sanatizer->params=$params;
			if(isset($params['skipOnError']))
				$sanatizer->skipOnError=$params['skipOnError'];
		}
		else
		{
			$params['attributes']=$attributes;
			if(isset(self::$builtInSanatizers[$name]))
				$className=Yii::import(self::$builtInSanatizers[$name],true);
			else
				$className=Yii::import($name,true);
			$sanatizer=new $className;
			foreach($params as $name=>$value)
				$sanatizer->$name=$value;
		}

		$sanatizer->on=empty($on) ? array() : array_combine($on,$on);
		$sanatizer->except=empty($except) ? array() : array_combine($except,$except);
                $sanatizer->setModel($object);

		return $sanatizer;
	}

	/**
	 * Validates the specified object.
	 * @param CModel $object the data object being validated
	 * @param array $attributes the list of attributes to be validated. Defaults to null,
	 * meaning every attribute listed in {@link attributes} will be validated.
	 */
	public function sanatize($object,$attributes=null)
	{
		if(is_array($attributes))
			$attributes=array_intersect($this->attributes,$attributes);
		else
			$attributes=$this->attributes;
		foreach($attributes as $attribute)
		{
			if(!$this->skipOnError || !$object->hasErrors($attribute))
				$this->sanatizeAttribute($object,$attribute);
		}
	}

	/**
	 * Returns a value indicating whether the validator applies to the specified scenario.
	 * A validator applies to a scenario as long as any of the following conditions is met:
	 * <ul>
	 * <li>the validator's "on" property is empty</li>
	 * <li>the validator's "on" property contains the specified scenario</li>
	 * </ul>
	 * @param string $scenario scenario name
	 * @return boolean whether the validator applies to the specified scenario.
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
	 * @param CModel $object the data object being validated
	 * @param string $attribute the attribute being validated
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
         * 
         * @param type $value
         */
        public function setModel($value)
        {
            if($this->_model===null)
            {
                if($value instanceof CModel)
                    $this->_model=$value;
                else
                    throw new CException(Yii::t('yii', 'You may only use a model as sanatization target'));
            }                
        }
        public function getModel()
        {
            return $this->_model;
        }
}