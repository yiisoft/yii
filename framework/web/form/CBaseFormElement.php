<?php

abstract class CBaseFormElement extends CComponent
{
	public $attributes=array();
	private $_visible;

	abstract function render();

	public function __toString()
	{
		return $this->render();
	}

	public function __get($name)
	{
		$getter='get'.$name;
		if(method_exists($this,$getter))
			return $this->$getter();
		else if(isset($this->attributes[$name]))
			return $this->attributes[$name];
		else
			throw new CException('');
	}

	public function __set($name,$value)
	{
		$setter='set'.$name;
		if(method_exists($this,$setter))
			$this->$setter($value);
		else
			$this->attributes[$name]=$value;
	}

	public function configure($config)
	{
		if(is_string($config))
			$config=require($config);
		if(is_array($config))
		{
			foreach($config as $name=>$value)
				$this->$name=$value;
		}
	}

	public function getVisible()
	{
		if($this->_visible===null)
			$this->_visible=$this->evaluateVisible();
		return $this->_visible;
	}

	public function setVisible($value)
	{
		$this->_visible=$value;
	}

	protected function evaluateVisible()
	{
		return true;
	}
}
