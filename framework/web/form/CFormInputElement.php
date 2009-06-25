<?php

class CFormInputElement extends CFormElement
{
	public static $coreTypes=array(
		'text'=>'activeTextField',
		'hidden'=>'activeHiddenField',
		'password'=>'activePasswordField',
		'textarea'=>'activeTextArea',
		'file'=>'activeFileField',
		'radio'=>'activeRadioButton',
		'checkbox'=>'activeCheckBox',
		'listbox'=>'activeListBox',
		'dropdownlist'=>'activeDropDownList',
		'checkboxlist'=>'activeCheckBoxList',
		'radiolist'=>'activeRadioButtonList',
	);
	public $type;
	public $name;
	public $hint;
	public $items=array();
	private $_label;

	protected function evaluateVisible()
	{
		return $this->getForm()->getModel()->isAttributeSafe($this->name);
	}

	public function getRequired()
	{
		return $this->getForm()->getModel()->isAttributeRequired($this->name);
	}

	public function getLabel()
	{
		if($this->_label!==null)
			return $this->_label;
		else if(($model=$this->getForm()->getModel())!==null)
			return $model->getAttributeLabel($this->name);
		else
			return '';
	}

	public function setLabel($value)
	{
		$this->_label=$value;
	}

	public function render()
	{
		return $this->renderLabel()
				. $this->renderInput()
				. $this->renderHint()
				. $this->renderError();
	}

	public function renderLabel()
	{
		return CHtml::activeLabelEx($this->getForm()->getModel(), $this->name, array('label'=>$this->getLabel()));
	}

	public function renderInput()
	{
		if(isset(self::$coreTypes[$this->type]))
		{
			$method=self::$coreTypes[$this->type];
			if(strpos($method,'List')!==false)
				return CHtml::$method($this->getForm()->getModel(), $this->name, $this->items, $this->attributes);
			else
				return CHtml::$method($this->getForm()->getModel(), $this->name, $this->attributes);
		}
		else
		{
			$attributes=$this->attributes;
			$attributes['model']=$this->getForm()->getModel();
			$attributes['attribute']=$this->name;
			ob_start();
			$this->getForm()->getOwner()->widget($this->type, $attributes);
			return ob_get_clean();
		}
	}

	public function renderError()
	{
		return CHtml::error($this->getForm()->getModel(), $this->name);
	}

	public function renderHint()
	{
		return $this->hint===null ? '' : '<p class="hint">'.CHtml::encode($this->hint).'</p>';
	}
}
