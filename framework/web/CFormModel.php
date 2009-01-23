<?php
/**
 * CFormModel class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormModel represents a data model that collects HTML form inputs.
 *
 * Unlike {@link CActiveRecord}, the data collected by CFormModel are stored
 * in memory only, instead of database.
 *
 * To collect user inputs, you may extend CFormModel and define the attributes
 * whose values are to be collected from user inputs. You may override
 * {@link rules()} to declare validation rules that should be applied to
 * the attributes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CFormModel extends CModel
{
	private $_validators;
	private static $_names=array();

	/**
	 * Constructor.
	 * @param array initial attributes (name => value). The attributes
	 * are subject to filtering via {@link setAttributes}.
	 * @param string scenario name. See {@link setAttributes} for more details about this parameter.
	 * This parameter has been available since version 1.0.2.
	 * @see setAttributes
	 */
	public function __construct($attributes=array(),$scenario='')
	{
		if($attributes!==array())
			$this->setAttributes($attributes,$scenario);
		$this->attachBehaviors($this->behaviors());
	}

	/**
	 * Returns the name of attributes that are safe to be massively assigned.
	 * The default implementation simply returns {@link attributeNames}.
	 * This method may be overridden by child classes.
	 * See {@link CModel::safeAttributes} for more details about how to
	 * override this method.
	 * @return array list of safe attribute names.
	 * @see CModel::safeAttributes
	 * @since 1.0.2
	 */
	public function safeAttributes()
	{
		return $this->attributeNames();
	}

	/**
	 * Returns the list of attribute names.
	 * By default, this method returns all public properties of the class.
	 * You may override this method to change the default.
	 * @return array list of attribute names. Defaults to all public properties of the class.
	 */
	public function attributeNames()
	{
		$className=get_class($this);
		if(!isset(self::$_names[$className]))
		{
			$class=new ReflectionClass(get_class($this));
			$names=array();
			foreach($class->getProperties() as $property)
			{
				$name=$property->getName();
				if($property->isPublic() && !$property->isStatic())
					$names[]=$name;
			}
			return self::$_names[$className]=$names;
		}
		else
			return self::$_names[$className];
	}

	/**
	 * @return array list of validators created according to {@link CModel::rules rules}.
	 */
	public function getValidators()
	{
		if($this->_validators===null)
			$this->_validators=$this->createValidators();
		return $this->_validators;
	}
}