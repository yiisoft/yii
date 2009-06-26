<?php
/**
 * CForm class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CForm is the base class providing the common features needed by data model objects.
 *
 * CForm defines the basic framework for data models that need to be validated.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.form
 * @since 1.1
 */
class CForm extends CFormElement implements ArrayAccess
{
	public $legend;
	public $description;  // TODO!!!
	public $method='post';
	public $action='';
	public $on;

	public $inputElementClass='CFormInputElement';
	public $buttonElementClass='CFormButtonElement';
	public $formElementClass='CForm';

	private $_model;

	private $_elements;
	private $_buttons;

	public function __construct($parent,$model=null)
	{
		$this->parent=$parent;
		$this->_model=$model;
	}

	public function submitted($loadData=true)
	{
		$ret=$this->clicked($this->getUniqueId());
		if($ret && $loadData)
			$this->loadData();
		return $ret;
	}

	protected function getUniqueId()
	{
		if(isset($this->attributes['id']))
			return 'yform_'.$this->attributes['id'];
		else
			return 'yform_'.sprintf('%x',crc32(serialize(array_keys($this->getElements()->toArray()))));
	}

	public function getSubmitMethod()
	{
		return strcasecmp($this->getRoot()->method,'get') ? 'post' : 'get';
	}

	public function loadData()
	{
		if($this->_model!==null)
		{
			$class=get_class($this->_model);
			if($this->getSubmitMethod()==='get')
			{
				if(isset($_GET[$class]))
					$this->_model->setAttributes($_GET[$class]);
			}
			else if(isset($_POST[$class]))
				$this->_model->setAttributes($_POST[$class]);
		}
		foreach($this->getElements() as $element)
		{
			if($element instanceof self)
				$element->loadData();
		}
	}

	public function clicked($name)
	{
		if($this->getSubmitMethod()==='get')
			return isset($_GET[$name]);
		else
			return isset($_POST[$name]);
	}

	protected function evaluateVisible()
	{
		foreach($this->getElements() as $element)
			if($element->getVisible())
				return true;
		return false;
	}

	public function getRoot()
	{
		$root=$this;
		while($root->parent instanceof self)
			$root=$root->parent;
		return $root;
	}

	public function getOwner()
	{
		$owner=$this->parent;
		while($owner instanceof self)
			$owner=$owner->parent;
		return $owner;
	}

	public function getModel()
	{
		$form=$this;
		while($form->_model===null && $form->parent instanceof self)
			$form=$form->parent;
		return $form->_model;
	}

	public function setModel($model)
	{
		$this->_model=$model;
	}

	public function getElements()
	{
		if($this->_elements===null)
			$this->_elements=new CFormElementCollection($this,false);
		return $this->_elements;
	}

	public function setElements($elements)
	{
		$collection=$this->getElements();
		foreach($elements as $name=>$config)
			$collection->add($name,$config);
	}

	public function getButtons()
	{
		if($this->_buttons===null)
			$this->_buttons=new CFormElementCollection($this,true);
		return $this->_buttons;
	}

	public function setButtons($buttons)
	{
		$collection=$this->getButtons();
		foreach($buttons as $name=>$config)
			$collection->add($name,$config);
	}

	public function render()
	{
		$output='';
		if(!$this->parent instanceof self)
			$output.=$this->begin();
		$output.=$this->body();
		if(!$this->parent instanceof self)
			$output.=$this->end();
		return $output;
	}

	public function token()
	{
		if(isset($this->attributes['name']))
			$name=$this->attributes['name'];
		else
			$name='yform';
		return CHtml::hiddenField($this->getUniqueID(),1);
	}

	public function begin()
	{
		$output=CHtml::beginForm($this->action,$this->method,$this->attributes);
		if($this->legend!==null)
			$output.="\n<fieldset>\n<legend>\n".$this->legend."</legend>\n";
		$output.='<div style="visibility:none">'.$this->token().'</div>';
		return $output;
	}

	public function end()
	{
		if($this->legend!==null)
			return "</fieldset>\n".CHtml::endForm();
		else
			return CHtml::endForm();
	}

	public function body()
	{
		$output=CHtml::errorSummary($this->getModel());
		$output.="<ul>\n";
		foreach($this->getElements() as $element)
		{
			if($element->getVisible())
			{
				$output.="<li>";
				$output.=$element;
				$output.="</li>\n";
			}
		}
		foreach($this->getButtons() as $button)
		{
			if($button->getVisible())
			{
				$output.="<li>";
				$output.=$button;
				$output.="</li>\n";
			}
		}
		$output.="</ul>\n";
		return $output;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->getElements()->contains($offset);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->getElements()->itemAt($offset);
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to set element
	 * @param mixed the element value
	 */
	public function offsetSet($offset,$item)
	{
		$this->getElements()->add($offset,$item);
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		$this->getElements()->remove($offset);
	}
}


