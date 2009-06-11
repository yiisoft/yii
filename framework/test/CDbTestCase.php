<?php

Yii::import('system.test.CTestCase');

class CDbTestCase extends CTestCase
{
	public function __get($name)
	{
		if(($rows=$this->getFixture()->getRows($name))!==false)
			return $rows;
		else
			throw new Exception("Unknown property '$name' for class '".get_class($this)."'.");
	}

	public function __call($name,$params)
	{
		if(isset($params[0]) && $this->getFixture()->hasRecord($name,$params[0]))
			return $this->getFixture()->getRecord($name,$params[0]);
		else
			throw new Exception("Unknown method '$name' for class '".get_class($this)."'.");
	}

	public function getFixture()
	{
		return Yii::app()->getComponent('fixture');
	}

	public function fixtures()
	{
		return array();
	}

	public function setUp()
	{
		parent::setUp();
		$this->getFixture()->load($this->fixtures());
	}

	public function tearDown()
	{
		$this->getFixture()->unload($this->fixtures());
		parent::tearDown();
	}
}