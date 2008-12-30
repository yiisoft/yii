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
	/**
	 * Constructor.
	 * @param array initial attributes (name => value). The attributes
	 * are subject to filtering via {@link setAttributes}.
	 */
	public function __construct($attributes=array())
	{
		if($attributes!==array())
			$this->setAttributes($attributes);
	}

	/**
	 * Returns the list of attribute names.
	 * By default, this method returns all public properties of the class.
	 * You may override this method to change the default.
	 * @return array list of attribute names. Defaults to all public properties of the class.
	 */
	public function attributeNames()
	{
		$class=new ReflectionClass(get_class($this));
		$attributes=array();
		foreach($class->getProperties() as $property)
		{
			if($property->isPublic())
				$attributes[]=$property->getName();
		}
		return $attributes;
	}

	/**
	 * @return array all attribute values (name=>value)
	 */
	public function getAttributes()
	{
		$values=array();
		foreach($this->attributeNames() as $name)
			$values[$name]=$this->$name;
		return $values;
	}

	/**
	 * Sets the attribute values.
	 * Only those attributes that are listed in {@link attributeNames()}
	 * will be set.
	 * @param array attribute values (name=>value)
	 */
	public function setAttributes($values)
	{
		if(is_array($values))
		{
			$attributes=array_flip($this->attributeNames());
			foreach($values as $name=>$value)
			{
				if(isset($attributes[$name]))
					$this->$name=$value;
			}
		}
	}
}