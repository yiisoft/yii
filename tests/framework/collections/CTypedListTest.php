<?php

class CTypedListTest extends CTestCase
{
	public function testClassType()
	{
		$list=new CTypedList(CComponent::class);
		$list[]=new CComponent;

		$this->expectException(CException::class);
		$list[]=new stdClass;
	}

	public function testInterfaceType()
	{
		$list=new CTypedList(Traversable::class);
		$list[]=new CList;

		$this->expectException(CException::class);
		$list[]=new CComponent;
	}
}
