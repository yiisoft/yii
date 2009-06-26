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
 * CForm represents a form object that contains form input specification.
 *
 *
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
		parent::__construct($parent);
		$this->_model=$model;
	}

	public function submitted($loadData=true)
	{
		$ret=$this->clicked($this->getUniqueId());
		if($ret && $loadData)
			$this->loadData();
		return $ret;
	}

	public function loadData()
	{
		if($this->_model!==null)
		{
			$class=get_class($this->_model);
			if($this->getRoot()->method==='get')
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
		if($this->getRoot()->method==='get')
			return isset($_GET[$name]);
		else
			return isset($_POST[$name]);
	}

	public function getRoot()
	{
		$root=$this;
		while($root->getParent() instanceof self)
			$root=$root->getParent();
		return $root;
	}

	public function getOwner()
	{
		$owner=$this->getParent();
		while($owner instanceof self)
			$owner=$owner->getParent();
		return $owner;
	}

	public function getModel()
	{
		$form=$this;
		while($form->_model===null && $form->getParent() instanceof self)
			$form=$form->getParent();
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
		if($this->getParent() instanceof self) // is a sub-form
			return $this->body();
		else
			return $this->begin() . $this->body() . $this->end();
	}

	public function token()
	{
		return '<div style="visibility:none">'.CHtml::hiddenField($this->getUniqueID(),1).'</div>';
	}

	public function begin()
	{
		$output=CHtml::beginForm($this->action,$this->method,$this->attributes);
		$output.=$this->token();
		if($this->legend!==null)
			$output.="\n<fieldset>\n<legend>\n".$this->legend."</legend>\n";
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
	 * Evaluates the visibility of this form.
	 * This method will check the visibility of the {@link elements}.
	 * If any one of them is visible, the form is considered as visible. Otherwise, it is invisible.
	 * @return boolean whether this form is visible.
	 */
	protected function evaluateVisible()
	{
		foreach($this->getElements() as $element)
			if($element->getVisible())
				return true;
		return false;
	}

	/**
	 * Returns a unique ID that identifies this form in the current page.
	 * @return string the unique ID identifying this form
	 */
	protected function getUniqueId()
	{
		if(isset($this->attributes['id']))
			return 'yform_'.$this->attributes['id'];
		else
			return 'yform_'.sprintf('%x',crc32(serialize(array_keys($this->getElements()->toArray()))));
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
