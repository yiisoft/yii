<?php
/**
 * CFormElementCollection class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormElementCollection implements the collection for storing form elements.
 *
 * Because CFormElementCollection extends from {@link CMap}, it can be used like an associative array.
 * For example,
 * <pre>
 * $element=$collection['username'];
 * $collection['username']=array('type'=>'text', 'maxlength'=>128);
 * $collection['password']=new CFormInputElement($form, array('type'=>'password'));
 * $collection[]='some string';
 * </pre>
 *
 * CFormElementCollection can store three types of value: a configuration array, a {@link CFormElement}
 * object, or a string, as shown in the above example. Internally, these values will be converted
 * to {@link CFormElement} objects.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.form
 * @since 1.1
 */
class CFormElementCollection extends CMap
{
	private $_form;
	private $_forButtons;

	/**
	 * Constructor.
	 * @param CForm the form object that owns this collection
	 * @param boolean whether this collection is used to store buttons.
	 */
	public function __construct($form,$forButtons=false)
	{
		parent::__construct();
		$this->_form=$form;
		$this->_forButtons=$buttonOnly;
	}

	/**
	 * Adds an item to the collection.
	 * This method overrides the parent implementation to ensure
	 * only configuration arrays, strings, or {@link CFormElement} objects
	 * can be stored in this collection.
	 * @param mixed key
	 * @param mixed value
	 * @throws CException if the value is invalid.
	 */
	public function add($key,$value)
	{
		if(is_array($value))
		{
			$value['name']=$key;

			if($this->_forButtons)
			{
				$class=$this->_form->buttonElementClass;
				$button=new $class($this->_form,$value);
				parent::add($key, $button);
			}
			else
			{
				if(!isset($value['type']))
					$value['type']='text';
				if($value['type']==='string')
				{
					unset($value['type'],$value['name']);
					$class='CFormStringElement';
				}
				else if($value['type']==='form')
				{
					unset($value['type']);
					$class=$this->_form->formElementClass;
				}
				else
					$class=$this->_form->inputElementClass;
				parent::add($key,new $class($this->_form,$value));
			}
		}
		else if($value instanceof CFormElement)
		{
			if(property_exists($value,'name'))
				$value->name=$key;
			parent::add($key,$value);
		}
		else if(is_string($value))
			parent::add($key,new CFormStringElement($this->_form,array('content'=>$value)));
		else
			throw new CException(Yii::t('yii','The element "{name}" must be a configuration array, a string or a CFormElement object.',array('{name}'=>$key)));
	}
}
