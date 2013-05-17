<?php
class CTypedMapTestFoo {}
class CTypedMapTestBar {}

/**
 * CTypedMapTest
 */
class CTypedMapTest extends CTestCase
{
	public function testAddRightType()
	{
		$typedMap = new CTypedMap('CTypedMapTestFoo');
		$typedMap->add(0, new CTypedMapTestFoo());
	}

	public function testAddWrongType()
	{
		$this->setExpectedException('CException');

		$typedMap = new CTypedMap('CTypedMapTestFoo');
		$typedMap->add(0, new CTypedMapTestBar());
	}
}