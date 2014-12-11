<?php

Yii::import('system.collections.CAttributeCollection');

class CAttributeCollectionTest extends CTestCase
{
	public function testCanGetProperty()
	{
		$collection = new CAttributeCollection();
		$collection->Property = 'value';
		$this->assertEquals('value', $collection->Property);
		$this->assertTrue($collection->canGetProperty('Property'));
	}

	public function testCanNotGetUndefinedProperty()
	{
		$collection = new CAttributeCollection(array(), true);
		$this->assertFalse($collection->canGetProperty('Property'));
		$this->setExpectedException('CException');
		$value=$collection->Property;
	}

	public function testCanSetProperty()
	{
		$collection = new CAttributeCollection();
		$collection->Property = 'value';
		$this->assertEquals('value', $collection->itemAt('Property'));
		$this->assertTrue($collection->canSetProperty('Property'));
	}

	public function testCanNotSetPropertyIfReadOnly()
	{
		$collection = new CAttributeCollection(array(), true);
		$this->setExpectedException('CException');
		$collection->Property = 'value';
	}

	public function testGetCaseSensitive()
	{
		$collection = new CAttributeCollection();
		$collection->caseSensitive=false;
		$this->assertFalse($collection->caseSensitive);
		$collection->caseSensitive=true;
		$this->assertTrue($collection->caseSensitive);
	}

	public function testSetCaseSensitive()
	{
		$collection = new CAttributeCollection();
		$collection->Property = 'value';
		$collection->caseSensitive=false;
		$this->assertEquals('value', $collection->itemAt('property'));
	}

	public function testItemAt()
	{
		$collection = new CAttributeCollection();
		$collection->Property = 'value';
		$this->assertEquals('value', $collection->itemAt('Property'));
	}

	public function testAdd()
	{
		$collection = new CAttributeCollection();
		$collection->add('Property', 'value');
		$this->assertEquals('value', $collection->itemAt('Property'));
	}

	public function testRemove()
	{
		$collection = new CAttributeCollection();
		$collection->add('Property', 'value');
		$collection->remove('Property');
		$this->assertEquals(0, count($collection));
	}

	public function testUnset(){
		$collection = new CAttributeCollection();
		$collection->add('Property', 'value');
		unset($collection->Property);
		$this->assertEquals(0, count($collection));
	}

	public function testIsset(){
		$collection = new CAttributeCollection();
		$this->assertFalse(isset($collection->Property));
		$collection->Property = 'value';
		$this->assertTrue(isset($collection->Property));
	}

	public function testContains()
	{
		$collection = new CAttributeCollection();
		$this->assertFalse($collection->contains('Property'));
		$collection->Property = 'value';
		$this->assertTrue($collection->contains('Property'));
	}

	public function testHasProperty()
	{
		$collection = new CAttributeCollection();
		$this->assertFalse($collection->hasProperty('Property'));
		$collection->Property = 'value';
		$this->assertTrue($collection->hasProperty('Property'));
	}

  public function testMergeWithCaseSensitive()
  {
    $collection = new CAttributeCollection();
    $item = array('Test'=>'Uppercase');
    $collection->mergeWith($item);
    $this->assertEquals('Uppercase', $collection->itemAt('test'));
  }

  public function testMergeWithCaseInSensitive()
  {
    $collection = new CAttributeCollection();
    $collection->caseSensitive = true;
    $collection->add('k1','item');

    $item = array('K1'=>'ITEM');
    $collection->mergeWith($item);
    $this->assertEquals('item', $collection->itemAt('k1'));
    $this->assertEquals('ITEM', $collection->itemAt('K1'));
  }
}
