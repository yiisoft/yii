<?php

class CFormInputCollection extends CMap
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
			$value['name']=$key;
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
		else if($value instanceof CBaseFormElement)
			parent::add($key,$value);
		else if(is_string($value))
			parent::add($key,new CFormStringElement($this->_form,array('content'=>$value)));
		else
			throw new CException('');
	}
}
