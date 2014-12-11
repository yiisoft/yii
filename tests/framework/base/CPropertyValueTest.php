<?php

class TextAlign extends CEnumerable
{
	const Left='Left';
	const Right='Right';
}

class CPropertyValueTest extends CTestCase
{
	public function testEnsureBoolean()
	{
		$entries=array
		(
			array(true,true),
			array(false,false),
			array(null,false),
			array(0,false),
			array(1,true),
			array(-1,true),
			array(2.1,true),
			array('',false),
			array('abc',false),
			array('0',false),
			array('1',true),
			array('123',true),
			array('false',false),
			array('true',true),
			array('tRue',true),
			array(array(),false),
			array(array(0),true),
			array(array(1),true),
		);
		foreach($entries as $index=>$entry)
			$this->assertTrue(CPropertyValue::ensureBoolean($entry[0])===$entry[1],
				"Comparison $index: {$this->varToString($entry[0])}=={$this->varToString($entry[1])}");
	}

	public function testEnsureString()
	{
		$entries=array
		(
			array('',''),
			array('abc','abc'),
			array(null,''),
			array(0,'0'),
			array(1,'1'),
			array(-1.1,'-1.1'),
			array(true,'true'),
			array(false,'false'),
		);

		if(version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			$entries = array_merge($entries, array(
				array(array(),'Array'),
				array(array(0),'Array'),
			));
		}

		foreach($entries as $index=>$entry)
			$this->assertTrue(CPropertyValue::ensureString($entry[0])===$entry[1],
				"Comparison $index: {$this->varToString($entry[0])}=={$this->varToString($entry[1])}");
	}

	public function testEnsureInteger()
	{
		$entries=array
		(
			array(123,123),
			array(1.23,1),
			array(null,0),
			array('',0),
			array('abc',0),
			array('123',123),
			array('1.23',1),
			array(' 1.23',1),
			array(' 1.23abc',1),
			array('abc1.23abc',0),
			array(true,1),
			array(false,0),
			array(array(),0),
			array(array(0),1),
		);
		foreach($entries as $index=>$entry)
			$this->assertTrue(CPropertyValue::ensureInteger($entry[0])===$entry[1],
				"Comparison $index: {$this->varToString($entry[0])}=={$this->varToString($entry[1])}");
	}

	public function testEnsureFloat()
	{
		$entries=array
		(
			array(123,123.0),
			array(1.23,1.23),
			array(null,0.0),
			array('',0.0),
			array('abc',0.0),
			array('123',123.0),
			array('1.23',1.23),
			array(' 1.23',1.23),
			array(' 1.23abc',1.23),
			array('abc1.23abc',0.0),
			array(true,1.0),
			array(false,0.0),
			array(array(),0.0),
			array(array(0),1.0),
		);
		foreach($entries as $index=>$entry)
			$this->assertTrue(CPropertyValue::ensureFloat($entry[0])===$entry[1],
				"Comparison $index: {$this->varToString($entry[0])}=={$this->varToString($entry[1])}");
	}

	public function testEnsureArray()
	{
		$entries=array
		(
			array(123,array(123)),
			array(null,array()),
			array('',array()),
			array('abc',array('abc')),
			array('(1,2)',array(1,2)),
			array('("key"=>"value",2=>3)',array("key"=>"value",2=>3)),
			array(true,array(true)),
			array(array(),array()),
			array(array(0),array(0)),
		);
		foreach($entries as $index=>$entry)
			$this->assertTrue(CPropertyValue::ensureArray($entry[0])===$entry[1],
				"Comparison $index: {$this->varToString($entry[0])}=={$this->varToString($entry[1])}");
	}

	private function varToString($var)
	{
		if(is_array($var))
			return 'Array';
		return (string)$var;
	}

	public function testEnsureObject()
	{
		$obj=new stdClass;
		$this->assertTrue(CPropertyValue::ensureObject($obj)===$obj);
	}

	public function testEnsureEnum()
	{
		$this->assertTrue(CPropertyValue::ensureEnum('Left','TextAlign')==='Left');
		$this->setExpectedException('CException');
		$this->assertTrue(CPropertyValue::ensureEnum('left','TextAlign')==='Left');
	}
}
