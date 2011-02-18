<?php
class NewComponent extends CComponent
{
	private $_object = null;
	private $_text = 'default';
	public $eventHandled = false;
	public $behaviorCalled = false;

	public function getText()
	{
		return $this->_text;
	}

	public function setText($value)
	{
		$this->_text=$value;
	}

	public function getObject()
	{
		if(!$this->_object)
		{
			$this->_object=new NewComponent;
			$this->_object->_text='object text';
		}
		return $this->_object;
	}

	public function onMyEvent()
	{
		$this->raiseEvent('OnMyEvent',new CEvent($this));
	}

	public function myEventHandler($event)
	{
		$this->eventHandled=true;
	}
	public function exprEvaluator($p1,$comp) {
		return "Hello $p1";
	}
}
