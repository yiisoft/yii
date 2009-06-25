<?php

class CFormButtonCollection extends CMap
{
	private $_form;

	public function __construct($form)
	{
		parent::__construct();
		$this->_form=$form;
	}

	public function add($key,$value)
	{
		if(is_array($value))
		{
			$class=$this->_form->buttonElementClass;
			$value['name']=$key;
			$button=new $class($this->_form,$value);
			parent::add($key, $button);
		}
		else if($value instanceof CFormButtonElement)
			parent::add($key,$value);
		else
			throw new CException('');
	}
}
