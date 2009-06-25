<?php

abstract class CFormElement extends CBaseFormElement
{
	private $_form;

	public function __construct($form,$config=null)
	{
		$this->_form=$form;
		$this->configure($config);
	}

	public function getForm()
	{
		return $this->_form;
	}
}
