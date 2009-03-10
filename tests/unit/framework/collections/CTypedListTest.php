<?php

Yii::import('system.collections.CTypedList');

class CTypedListTest extends CTestCase
{
	public function testClassType()
	{
		$list=new CTypedList('CComponent');
		$list[]=new CComponent;
		$this->setExpectedException('CException');
		$list[]=new stdClass;
	}

	public function testInterfaceType()
	{
		$list=new CTypedList('Traversable');
		$list[]=new CList;
		$this->setExpectedException('CException');
		$list[]=new CComponent;
	}
}
