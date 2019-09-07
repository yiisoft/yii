<?php

Yii::import('system.collections.CConfiguration');

class MyClass extends CComponent
{
	public $param1;
	private $_param2;
	public $param3;
	private $_object;
	public $backquote;

	public function getParam2()
	{
		return $this->_param2;
	}

	public function setParam2($value)
	{
		$this->_param2=$value;
	}

	public function getObject()
	{
		if($this->_object===null)
			$this->_object=new MyClass;
		return $this->_object;
	}
}

class CConfigurationTest extends CTestCase
{
	public $configFile;

	public function setUp(): void
	{
		$this->configFile=dirname(__FILE__).'/data/config.php';
	}

	public function tearDown(): void
	{
	}

	public function testLoadFromFile()
	{
		$config=new CConfiguration;
		$this->assertSame(array(), $config->toArray());
		$config->loadFromFile($this->configFile);
		$data=include($this->configFile);
		$this->assertSame($data, $config->toArray());
	}

	public function testSaveAsString()
	{
		$config=new CConfiguration($this->configFile);
		$str=$config->saveAsString();
		eval("\$data=$str;");
		$this->assertSame($data, $config->toArray());
	}

	public function testApplyTo()
	{
		$config=new CConfiguration($this->configFile);
		$object=new MyClass;
		$config->applyTo($object);
		$this->assertSame('value1', $object->param1);
		$this->assertFalse($object->param2);
		$this->assertSame(123, $object->param3);
		$this->assertSame("\\back'quote'", $object->backquote);
		/*
		$this->assertTrue($object->object->param1===null);
		$this->assertTrue($object->object->param2==='123');
		$this->assertTrue($object->object->param3===array('param1'=>'kkk','ddd',''));
		*/
	}

	public function testException()
	{
		$config=new CConfiguration(array('invalid'=>'value'));
		$object=new MyClass;
		$this->expectException('CException');
		$config->applyTo($object);
	}

	public function testCreateComponent()
	{
		$obj=Yii::createComponent(array('class'=>'MyClass','param2'=>3));
		$this->assertEquals(get_class($obj),'MyClass');
		$this->assertEquals($obj->param2,3);
	}
}
