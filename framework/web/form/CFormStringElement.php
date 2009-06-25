<?php

class CFormStringElement extends CFormElement
{
	public $content;
	private $_on;

	public function getVisible()
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
		return $this->content;
	}
}
