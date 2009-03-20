<?php
/**
 * CModel class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
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
abstract class CModel extends CComponent implements IteratorAggregate, ArrayAccess
{
	private $_errors=array();	// attribute name => array of errors
	private $_va;  // validator
	private $_se='';  // scenario

	/**
	 * Returns the list of attribute names of the model.
	 * @return array list of attribute names.
	 * @since 1.0.1
	 */
	abstract public function attributeNames();
	/**
	 * Returns the name of attributes that are safe to be massively assigned.
	 * For details about massive assignment, see the documentation of
	 * child classes (e.g. {@link CActiveRecord::setAttributes},
	 * {@link CFormModel::setAttributes}).
	 *
	 * The returned value of this method should be in the following structure:
	 * <pre>
	 * array(
	 *    // these attributes can be massively assigned in any scenario
	 *    // that is not explicitly specified below
	 *    'attr1, attr2, ...',
	 *
	 *    // these attributes can be massively assigned only in scenario 1
	 *    'scenario1' => 'attr2, attr3, ...',
	 *
	 *    // these attributes can be massively assigned only in scenario 2
	 *    'scenario2' => 'attr1, attr3, ...',
	 * );
	 * </pre>
	 * If the model is not scenario-sensitive (i.e., it is only used
	 * in one scenario, or all scenarios share the same set of safe attributes),
	 * the return value can be simplified as a single string:
	 * <pre>
	 * 'attr1, attr2, ...'
	 * </pre>
	 * @return array list of safe attribute names.
	 * @since 1.0.2
	 */
	abstract public function safeAttributes();

	/**
	 * Returns the validation rules for attributes.
	 *
	 * This method should be overridden to declare validation rules.
	 * Each rule is an array with the following structure:
	 * <pre>
	 * array('attribute list', 'validator name', 'on'=>'scenario name', ...validation parameters...)
	 * </pre>
	 * where
	 * <ul>
	 * <li>attribute list: specifies the attributes (separated by commas) to be validated;</li>
	 * <li>validator name: specifies the validator to be used. It can be the name of a model class
	 *   method, the name of a built-in validator, or a validator class (or its path alias).
	 *   A validation method must have the following signature:
	 * <pre>
	 * // $params refers to validation parameters given in the rule
	 * function validatorName($attribute,$params)
	 * </pre>
	 *   A built-in validator refers to one of the validators declared in {@link CValidator::builtInValidators}.
	 *   And a validator class is a class extending {@link CValidator}.</li>
	 * <li>on: this specifies the scenarios when the validation rule should be performed.
	 *   Separate different scenarios with commas. If this option is not set, the rule
	 *   will be applied in any scenario. Please see {@link validate} for more details about this option.</li>
	 * <li>additional parameters are used to initialize the corresponding validator properties.
	 *   Please refer to inidividal validator class API for possible properties.</li>
	 * </ul>
	 *
	 * The following are some examples:
	 * <pre>
	 * array(
	 *     array('username', 'required'),
	 *     array('username', 'length', 'min'=>3, 'max'=>12),
	 *     array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
	 *     array('password', 'authenticate', 'on'=>'login'),
	 * );
	 * </pre>
	 *
	 * Note, in order to inherit rules defined in the parent class, a child class needs to
	 * merge the parent rules with child rules using functions like array_merge().
	 *
	 * @return array validation rules to be applied when {@link validate()} is called.
	 * @see scenario
	 */
	public function rules()
	{
		return array();
	}

	/**
	 * Returns a list of behaviors that this model should behave as.
	 * The return value should be an array of behavior configurations indexed by
	 * behavior names. Each behavior configuration can be either a string specifying
	 * the behavior class or an array of the following structure:
	 * <pre>
	 * 'behaviorName'=>array(
	 *     'class'=>'path.to.BehaviorClass',
	 *     'property1'=>'value1',
	 *     'property2'=>'value2',
	 * )
	 * </pre>
	 *
	 * Note, the behavior classes must implement {@link IBehavior} or extend from
	 * {@link CBehavior}. Behaviors declared in this method will be attached
	 * to the model when it is instantiated.
	 *
	 * For more details about behaviors, see {@link CComponent}.
	 * @return array the behavior configurations (behavior name=>behavior configuration)
	 * @since 1.0.2
	 */
	public function behaviors()
	{
		return array();
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
	 * Performs the validation.
	 * This method executes the validation rule as declared in {@link rules}.
	 * Errors found during the validation can be retrieved via {@link getErrors}.
	 * @param string the scenario that the validation rules should be applied.
	 * Defaults to empty string, meaning only those validation rules whose "on"
	 * option is empty will be applied. If this is a non-empty string, only
	 * the validation rules whose "on" option is empty or contains the specified scenario
	 * will be applied. As of version 1.0.4, this parameter is deprecated, and
	 * the {@link scenario} property is the preferred way of specifying the scenario.
	 * In particular, when this parameter is empty, the {@link scenario} property value
	 * will be used as the effective scenario.
	 * @param array list of attributes that should be validated. Defaults to null,
	 * meaning any attribute listed in the applicable validation rules should be
	 * validated. If this parameter is given as a list of attributes, only
	 * the listed attributes will be validated.
	 * @return boolean whether the validation is successful without any error.
	 * @see beforeValidate
	 * @see afterValidate
	 */
	public function validate($scenario='',$attributes=null)
	{
		if($scenario==='')
			$scenario=$this->getScenario();

		$this->clearErrors();
		if($this->beforeValidate($scenario))
		{
			foreach($this->getValidators() as $validator)
			{
				if($validator->applyTo($scenario))
					$validator->validate($this,$attributes);
			}
			$this->afterValidate($scenario);
			return !$this->hasErrors();
		}
		else
			return false;
	}

	/**
	 * This method is invoked before validation starts.
	 * The default implementation calls {@link onBeforeValidate} to raise an event.
	 * You may override this method to do preliminary checks before validation.
	 * Make sure the parent implementation is invoked so that the event can be raised.
	 * @param string the set of the validation rules that should be applied. See {@link validate}
	 * for more details about this parameter.
	 * NOTE: this parameter has been available since version 1.0.1.
	 * @return boolean whether validation should be executed. Defaults to true.
	 */
	protected function beforeValidate($scenario)
	{
		$this->onBeforeValidate(new CEvent($this));
		return true;
	}

	/**
	 * This method is invoked after validation ends.
	 * The default implementation calls {@link onAfterValidate} to raise an event.
	 * You may override this method to do postprocessing after validation.
	 * Make sure the parent implementation is invoked so that the event can be raised.
	 * @param string the set of the validation rules that should be applied. See {@link validate}
	 * for more details about this parameter.
	 * NOTE: this parameter has been available since version 1.0.1.
	 */
	protected function afterValidate($scenario)
	{
		$this->onAfterValidate(new CEvent($this));
	}

	/**
	 * This event is raised before the validation is performed.
	 * @param CEvent the event parameter
	 * @since 1.0.2
	 */
	public function onBeforeValidate($event)
	{
		$this->raiseEvent('onBeforeValidate',$event);
	}

	/**
	 * This event is raised after the validation is performed.
	 * @param CEvent the event parameter
	 * @since 1.0.2
	 */
	public function onAfterValidate($event)
	{
		$this->raiseEvent('onAfterValidate',$event);
	}

	/**
	 * @return array list of validators created according to {@link rules}.
	 * @since 1.0.1
	 */
	public function getValidators()
	{
		return $this->createValidators();
	}

	/**
	 * Returns the validators that are applied to the specified attribute under the specified scenario.
	 * @param string the attribute name
	 * @param string the scenario name. As of version 1.0.4, this parameter is deprecated, and
	 * the {@link scenario} property is the preferred way of specifying the scenario.
	 * In particular, when this parameter is empty, the {@link scenario} property value
	 * will be used as the effective scenario.
	 * @return array the validators for the attribute. An empty array is returned if no validator applies to the attribute.
	 * @since 1.0.2
	 */
	public function getValidatorsForAttribute($attribute,$scenario='')
	{
		if($scenario==='')
			$scenario=$this->getScenario();

		if($this->_va===null)
		{
			$this->_va=array();
			foreach($this->getValidators() as $validator)
			{
				foreach($validator->attributes as $att)
					$this->_va[$att][]=$validator;
			}
		}
		$validators=array();
		if(isset($this->_va[$attribute]))
		{
			foreach($this->_va[$attribute] as $validator)
			{
				if($validator->applyTo($scenario))
					$validators[]=$validator;
			}
		}
		return $validators;
	}

	/**
	 * Returns a value indicating whether the attribute is required.
	 * This is determined based on the validation rules declared in {@link rules}.
	 * @param string attribute name
	 * @param string validation scenario. As of version 1.0.4, this parameter is deprecated, and
	 * the {@link scenario} property is the preferred way of specifying the scenario.
	 * In particular, when this parameter is empty, the {@link scenario} property value
	 * will be used as the effective scenario.
	 * @return boolean  whether the attribute is required
	 * @since 1.0.2
	 */
	public function isAttributeRequired($attribute,$scenario='')
	{
		if($scenario==='')
			$scenario=$this->getScenario();

		$validators=$this->getValidatorsForAttribute($attribute,$scenario);
		foreach($validators as $validator)
		{
			if($validator instanceof CRequiredValidator)
				return true;
		}
		return false;
	}

	/**
	 * Returns the text label for the specified attribute.
	 * @param string the attribute name
	 * @return string the attribute label
	 * @see generateAttributeLabel
	 * @see attributeLabels
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
	 * Returns a value indicating whether there is any validation error.
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
	 * Returns the errors for all attribute or a single attribute.
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
	 * Returns the first error of the specified attribute.
	 * @param string attribute name.
	 * @return string the error message. Null is returned if no error.
	 * @since 1.0.2
	 */
	public function getError($attribute)
	{
		return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
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

	/**
	 * Returns all attribute values.
	 * @param array list of attributes whose value needs to be returned.
	 * Defaults to null, meaning all attributes as listed in {@link attributeNames} will be returned.
	 * If it is an array, only the attributes in the array will be returned.
	 * @return array attribute values (name=>value).
	 */
	public function getAttributes($names=null)
	{
		$values=array();
		foreach($this->attributeNames() as $name)
			$values[$name]=$this->$name;

		if(is_array($names))
		{
			$values2=array();
			foreach($names as $name)
				$values2[$name]=isset($values[$name]) ? $values[$name] : null;
			return $values2;
		}
		else
			return $values;
	}

	/**
	 * Sets the attribute values in a massive way.
	 * Only safe attributes will be assigned by this method.
	 *
	 * Given a scenario, this method will assign values to the attributes
	 * that appear in the list returned by {@link safeAttributes} for the specified scenario:
	 * <ul>
	 * <li>If the scenario is false, attributes specified by {@link attributeNames}
	 * will be assigned.</li>
	 * <li>If the scenario is not an empty string, and it is found
	 * as a key in {@link safeAttributes}, the corresponding attributes
	 * will be assigned;</li>
	 * <li>If the scenario is an empty string, or if it is not found
	 * in {@link safeAttributes}, the first set of attributes in {@link safeAttributes}
	 * will be assigned.</li>
	 * </ul>
	 * @param array attribute values (name=>value) to be set.
	 * @param mixed scenario name.
	 * Note that as of version 1.0.4, if this parameter is an empty string, it will
	 * take the {@link scenario} property value.
	 * @see getSafeAttributeNames
	 */
	public function setAttributes($values,$scenario='')
	{
		if($scenario==='')
			$scenario=$this->getScenario();

		if(is_array($values))
		{
			$attributes=array_flip($this->getSafeAttributeNames($scenario));
			foreach($values as $name=>$value)
			{
				if(isset($attributes[$name]))
					$this->$name=$value;
			}
		}
	}

	/**
	 * Returns the scenario that this model is in.
	 * Scenario affects how massive attribute assignment is carried and which
	 * validations should be performed. An attribute can be declared as safe
	 * for massive assignment and requiring validation under certain scenarios.
	 * @return string the scenario that this model is in. Defaults to empty.
	 * @since 1.0.4
	 */
	public function getScenario()
	{
		return $this->_se;
	}

	/**
	 * @param string the scenario that this model is in.
	 * @see getScenario
	 * @since 1.0.4
	 */
	public function setScenario($value)
	{
		$this->_se=$value;
	}

	/**
	 * Returns the attribute names that are safe to be massively assigned.
	 * This method is internally used by {@link setAttributes}.
	 *
	 * Given a scenario, this method will choose the folllowing result
	 * from the list returned by {@link safeAttributes}:
	 * <ul>
	 * <li>If the scenario is false, attributes specified by {@link attributeNames}
	 * will be returned.</li>
	 * <li>If the scenario is not an empty string, and it is found
	 * as a key in {@link safeAttributes}, the corresponding attributes
	 * will be returned;</li>
	 * <li>If the scenario is an empty string, or if it is not found
	 * in {@link safeAttributes}, the first set of attributes in {@link safeAttributes}
	 * will be returned.</li>
	 * </ul>
	 * @param string scenario name.
	 * Note that as of version 1.0.4, if this parameter is an empty string, it will
	 * take the {@link scenario} property value.
	 * @return array safe attribute names
	 * @since 1.0.2
	 */
	public function getSafeAttributeNames($scenario='')
	{
		if($scenario==='')
			$scenario=$this->getScenario();

		if($scenario===false)
			return $this->attributeNames();

		$attributes=$this->safeAttributes();
		if(!is_array($attributes))
			$attributes=array($attributes);

		if($scenario!=='' && isset($attributes[$scenario]))
			return $this->ensureArray($attributes[$scenario]);

		if(isset($attributes[0], $attributes[1]))
			return $attributes;
		else
			return isset($attributes[0]) ? $this->ensureArray($attributes[0]) : array();
	}

	/**
	 * Returns an iterator for traversing the attributes in the model.
	 * This method is required by the interface IteratorAggregate.
	 * @return CMapIterator an iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		$attributes=$this->getAttributes();
		return new CMapIterator($attributes);
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to check on
	 * @return boolean
	 * @since 1.0.2
	 */
	public function offsetExists($offset)
	{
		return property_exists($this,$offset);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 * @since 1.0.2
	 */
	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to set element
	 * @param mixed the element value
	 * @since 1.0.2
	 */
	public function offsetSet($offset,$item)
	{
		$this->$offset=$item;
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to unset element
	 * @since 1.0.2
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}

	private function ensureArray($value)
	{
		return is_array($value) ? $value : preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}
}
