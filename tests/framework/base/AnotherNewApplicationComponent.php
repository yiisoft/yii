<?php
class AnotherNewApplicationComponent extends CApplicationComponent {
	private $_text='new';

	public function getText()
	{
		return $this->_text;
	}

	public function setText($value)
	{
		return $this->_text=$value;
	}
}