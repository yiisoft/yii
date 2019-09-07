<?php

Yii::import('system.collections.CTypedList');

class CTypedListTest extends CTestCase
{
	public function testClassType()
	{
		$list=new CTypedList('CComponent');
		$list[]=new CComponent;
		$this->expectException('CException');
		$list[]=new stdClass;
	}

	public function testInterfaceType()
	{
		$list=new CTypedList('Traversable');
		$list[]=new CList;
		$this->expectException('CException');
		$list[]=new CComponent;
	}
}
