<?php

class CFormButtonElement extends CFormElement
{
	public static $coreTypes=array(
		'submit'=>'submitButton',
		'button'=>'button',
		'image'=>'imageButton',
		'reset'=>'resetButton',
		'link'=>'linkButton',
	);
	public $name;
	public $type;
	public $label;

	private $_on;

	protected function evaluateVisible()
	{
		return empty($this->on) || in_array($this->getForm()->getModel()->getScenario(),$this->on);
	}

	public function getOn()
	{
		return $this->_on;
	}

	public function setOn($value)
	{
		$this->_on=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}

	public function render()
	{
		$attributes=$this->attributes;
		if(isset(self::$coreTypes[$this->type]))
		{
			$method=self::$coreTypes[$this->type];
			if($method==='linkButton')
			{
				if(!isset($attributes['params'][$this->name]))
					$attributes['params'][$this->name]=1;
				return CHtml::linkButton($this->label,$attributes);
			}
			$attributes['name']=$this->name;
			if($method==='imageButton')
				return CHtml::imageButton(isset($attributes['src']) ? $attributes['src'] : '',$attributes);
			else
				return CHtml::$method($this->label,$attributes);
		}
		else
		{
			$attributes['name']=$this->name;
			ob_start();
			$this->getForm()->getOwner()->widget($this->type, $attributes);
			return ob_get_clean();
		}
	}
}
