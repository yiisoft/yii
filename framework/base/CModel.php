<?php
/**
 * CModel class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModel is the base class providing the common features needed by data model objects.
 *
 * CModel defines the basic framework for data models that need to be validated.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.0
 */
abstract class CModel extends CComponent
{
	private $_errors=array();	// attribute name => array of errors

	/**
	 * Performs the validation.
	 * This method executes every validation rule as declared in {@link rules}.
	 * Errors found during the validation can be retrieved via {@link getErrors}.
	 * @param array the list of attributes to be validated. Defaults to null,
	 * meaning every attribute as listed in {@link rules} will be validated.
	 * @param string the scenario that the validation rules should be applied.
	 * This is used to match the {@link CValidator::on on} property set in
	 * the validation rules. Defaults to null, meaning all validation rules
	 * should be applied. If this parameter is a non-empty string (e.g. 'register'),
	 * then only those validation rules whose {@link CValidator::on on} property
	 * is not set or contains this string (e.g. 'register') will be applied.
	 * NOTE: this parameter has been available since version 1.0.1.
	 * @return boolean whether the validation is successful without any error.
	 */
	public function validate($attributes=null,$on=null)
	{
		$this->clearErrors();
		if($this->beforeValidate($on))
		{
			foreach($this->getValidators() as $validator)
			{
				if(empty($on) || $validator->on===array() || in_array($on,$validator->on))
					$validator->validate($this,$attributes);
			}
			$this->afterValidate($on);
			return !$this->hasErrors();
		}
		else
			return false;
	}

	/**
	 * This method is invoked before validation starts.
	 * You may override this method to do preliminary checks before validation.
	 * @param string the set of the validation rules that should be applied. See {@link validate}
	 * for more details about this parameter.
	 * NOTE: this parameter has been available since version 1.0.1.
	 * @return boolean whether validation should be executed. Defaults to true.
	 */
	protected function beforeValidate($on)
	{
		return true;
	}

	/**
	 * This method is invoked after validation ends.
	 * You may override this method to do postprocessing after validation.
	 * @param string the set of the validation rules that should be applied. See {@link validate}
	 * for more details about this parameter.
	 * NOTE: this parameter has been available since version 1.0.1.
	 */
	protected function afterValidate($on)
	{
	}

	/**
	 * @return array validators built based on {@link rules()}.
	 */
	public function createValidators()
	{
		$validators=array();
		foreach($this->rules() as $rule)
		{
			if(isset($rule[0],$rule[1]))  // attributes, validator name
				$validators[]=CValidator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2));
			else
				throw new CException(Yii::t('yii','{class} has an invalid validation rule. The rule must specify attributes to be validated and the validator name.',
					array('{class}'=>get_class($this))));
		}
		return $validators;
	}

	/**
	 * @return array a list of validators created according to {@link rules}.
	 * @since 1.0.1
	 */
	protected function getValidators()
	{
		return $this->createValidators();
	}

	/**
	 * Returns the attribute labels.
	 * Attribute labels are mainly used in error messages of validation.
	 * By default an attribute label is generated using {@link generateAttributeLabel}.
	 * This method allows you to explicitly specify attribute labels.
	 *
	 * Note, in order to inherit labels defined in the parent class, a child class needs to
	 * merge the parent labels with child labels using functions like array_merge().
	 *
	 * @return array attribute labels (name=>label)
	 * @see generateAttributeLabel
	 */
	public function attributeLabels()
	{
		return array();
	}

	/**
	 * Returns the validation rules for attributes.
	 *
	 * This method should be overridden to declare validation rules.
	 * Each rule is an array with the following structure:
	 * <pre>
	 * array('attribute list', 'validator name', 'on'=>'insert', ...validation parameters...)
	 * </pre>
	 * where
	 * <ul>
	 * <li>attribute list: specifies the attributes to be validated;</li>
	 * <li>validator name: specifies the validator to be used. It can be a class name (or class in dot syntax),
	 *   or a validation method in the AR class. A validator class must extend from {@link CValidator},
	 *   while a validation method must have the following signature:
	 * <pre>
	 * function validatorName($attribute,$params)
	 * </pre>
	 *   When using a built-in validator class, you can use an alias name instead of the full class name.
	 *   For example, you can use "required" instead of "system.validators.CRequiredValidator".
	 *   For more details, see {@link CValidator}.</li>
	 * <li>on: this specifies when the validation rule should be performed. Please see {@link validate}
	 *   for more details about this option. </li>
	 * <li>additional parameters are used to initialize the corresponding validator properties. See {@link CValidator}
	 *   for possible properties.</li>
	 * </ul>
	 *
	 * The following are some examples:
	 * <pre>
	 * array(
	 *     array('username', 'length', 'min'=>3, 'max'=>12),
	 *     array('password', 'compare', 'compareAttribute'=>'password2'),
	 *     array('password', 'authenticate'),
	 * );
	 * </pre>
	 *
	 * Note, in order to inherit rules defined in the parent class, a child class needs to
	 * merge the parent rules with child rules using functions like array_merge().
	 *
	 * @return array validation rules to be applied when {@link validate()} is called.
	 */
	public function rules()
	{
		return array();
	}

	/**
	 * Returns the text label for the specified attribute.
	 * @param string the attribute name
	 * @return string the attribute label
	 * @see generateAttributeLabel
	 */
	public function getAttributeLabel($attribute)
	{
		$labels=$this->attributeLabels();
		if(isset($labels[$attribute]))
			return $labels[$attribute];
		else
			return $this->generateAttributeLabel($attribute);
	}

	/**
	 * Returns a value indicating whether there is any error.
	 * @param string attribute name. Use null to check all attributes.
	 * @return boolean whether there is any error.
	 */
	public function hasErrors($attribute=null)
	{
		if($attribute===null)
			return $this->_errors!==array();
		else
			return isset($this->_errors[$attribute]);
	}

	/**
	 * Returns errors for all attribute or a single attribute.
	 * @param string attribute name. Use null to retrieve errors for all attributes.
	 * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
	 */
	public function getErrors($attribute=null)
	{
		if($attribute===null)
			return $this->_errors;
		else
			return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : array();
	}

	/**
	 * Adds a new error to the specified attribute.
	 * @param string attribute name
	 * @param string new error message
	 */
	public function addError($attribute,$error)
	{
		$this->_errors[$attribute][]=$error;
	}

	/**
	 * Removes errors for all attributes or a single attribute.
	 * @param string attribute name. Use null to remove errors for all attribute.
	 */
	public function clearErrors($attribute=null)
	{
		if($attribute===null)
			$this->_errors=array();
		else
			unset($this->_errors[$attribute]);
	}

	/**
	 * Generates a user friendly attribute label.
	 * This is done by replacing underscores or dashes with blanks and
	 * changing the first letter of each word to upper case.
	 * For example, 'department_name' or 'DepartmentName' becomes 'Department Name'.
	 * @param string the column name
	 * @return string the attribute label
	 */
	public function generateAttributeLabel($name)
	{
		return ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
	}
}