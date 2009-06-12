<?php

require_once('PHPUnit/Extensions/SeleniumTestCase.php');

class CWebTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
	/**
	 * @var array a list of fixtures that should be loaded for all test cases.
	 * The array keys are fixture names, and the array values are either AR class names
	 * or table names. If table names, they must begin with a colon character (e.g. 'Post'
	 * means an AR class, while ':Post' means a table name).
	 */
	public $fixtures=array();

	public function __get($name)
	{
		if(($rows=$this->getFixtureManager()->getRows($name))!==false)
			return $rows;
		else
			throw new Exception("Unknown property '$name' for class '".get_class($this)."'.");
	}

	public function __call($name,$params)
	{
		if(isset($params[0]) && ($record=$this->getFixtureManager()->getRecord($name,$params[0]))!==false)
			return $record;
		else
			throw new Exception("Unknown method '$name' for class '".get_class($this)."'.");
	}

	public function getFixtureManager()
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
		$this->getFixtureManager()->load($this->fixtures());
	}
}
