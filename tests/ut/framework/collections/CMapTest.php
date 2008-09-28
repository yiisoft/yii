<?php

Yii::import('system.collections.CMap');

class MapItem
{
	public $data='data';
}

class CMapTest extends CTestCase
{
	protected $map;
	protected $item1,$item2,$item3;

	public function setUp()
	{
		$this->map=new CMap;
		$this->item1=new MapItem;
		$this->item2=new MapItem;
		$this->item3=new MapItem;
		$this->map->add('key1',$this->item1);
		$this->map->add('key2',$this->item2);
	}

	public function tearDown()
	{
		$this->map=null;
		$this->item1=null;
		$this->item2=null;
		$this->item3=null;
	}

	public function testConstruct()
	{
		$a=array(1,2,'key3'=>3);
		$map=new CMap($a);
		$this->assertEquals(3,$map->getCount());
		$map2=new CMap($this->map);
		$this->assertEquals(2,$map2->getCount());
	}

	public function testGetReadOnly()
	{
		$map = new CMap(null, true);
		self::assertEquals(true, $map->getReadOnly(), 'List is not read-only');
	}

	public function testGetCount()
	{
		$this->assertEquals(2,$this->map->getCount());
	}

	public function testGetKeys()
	{
		$keys=$this->map->getKeys();
		$this->assertTrue(count($keys)===2 && $keys[0]==='key1' && $keys[1]==='key2');
	}

	public function testAdd()
	{
		$this->map->add('key3',$this->item3);
		$this->assertTrue($this->map->getCount()==3 && $this->map->contains('key3'));
	}

	public function testCanNotAddWhenReadOnly()
	{
		$map = new CMap(array(), true);
		$this->setExpectedException('CException');
		$map->add('key', 'value');
	}

	public function testRemove()
	{
		$this->map->remove('key1');
		$this->assertTrue($this->map->getCount()==1 && !$this->map->contains('key1'));
		$this->assertTrue($this->map->remove('unknown key')===null);
	}

	public function testCanNotRemoveWhenReadOnly()
	{
		$map = new CMap(array('key' => 'value'), true);
		$this->setExpectedException('CException');
		$map->remove('key');
	}

	public function testClear()
	{
		$this->map->clear();
		$this->assertTrue($this->map->getCount()==0 && !$this->map->contains('key1') && !$this->map->contains('key2'));
	}

	public function testContains()
	{
		$this->assertTrue($this->map->contains('key1'));
		$this->assertTrue($this->map->contains('key2'));
		$this->assertFalse($this->map->contains('key3'));
	}

	public function testCopyFrom()
	{
		$array=array('key3'=>$this->item3,'key4'=>$this->item1);
		$this->map->copyFrom($array);
		$this->assertTrue($this->map->getCount()==2 && $this->map['key3']===$this->item3 && $this->map['key4']===$this->item1);
		$this->setExpectedException('CException');
		$this->map->copyFrom($this);
	}

	public function testMergeWith()
	{
		$a=array('a'=>'v1','v2',array('2'),'c'=>array('3','c'=>'a'));
		$b=array('v22','a'=>'v11',array('2'),'c'=>array('c'=>'3','a'));
		$c=array('a'=>'v11','v2',array('2'),'c'=>array('3','c'=>'3','a'),'v22',array('2'));
		$map=new CMap($a);
		$map2=new CMap($b);
		$map->mergeWith($map2);
		$this->assertTrue($map->toArray()===$c);

		$array=array('key2'=>$this->item1,'key3'=>$this->item3);
		$this->map->mergeWith($array,false);
		$this->assertTrue($this->map->getCount()==3 && $this->map['key2']===$this->item1 && $this->map['key3']===$this->item3);
		$this->setExpectedException('CException');
		$this->map->mergeWith($this,false);
	}

	public function testArrayRead()
	{
		$this->assertTrue($this->map['key1']===$this->item1);
		$this->assertTrue($this->map['key2']===$this->item2);
		$this->assertEquals(null,$this->map['key3']);
	}

	public function testArrayWrite()
	{
		$this->map['key3']=$this->item3;
		$this->assertTrue($this->map['key3']===$this->item3 && $this->map->getCount()===3);
		$this->map['key1']=$this->item3;
		$this->assertTrue($this->map['key1']===$this->item3 && $this->map->getCount()===3);
		unset($this->map['key2']);
		$this->assertTrue($this->map->getCount()===2 && !$this->map->contains('key2'));
		unset($this->map['unknown key']);
	}

	public function testArrayForeach()
	{
		$n=0;
		$found=0;
		foreach($this->map as $index=>$item)
		{
			$n++;
			if($index==='key1' && $item===$this->item1)
				$found++;
			if($index==='key2' && $item===$this->item2)
				$found++;
		}
		$this->assertTrue($n==2 && $found==2);
	}

	public function testArrayMisc()
	{
		$this->assertEquals($this->map->Count,count($this->map));
		$this->assertTrue(isset($this->map['key1']));
		$this->assertFalse(isset($this->map['unknown key']));
	}

	public function testToArray()
	{
		$map = new CMap(array('key' => 'value'));
		self::assertEquals(array('key' => 'value'), $map->toArray());
	}
}
