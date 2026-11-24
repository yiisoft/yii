<?php

Yii::import('system.caching.CCache');

class CCacheTestStorage extends CCache
{
	private $_data=array();

	protected function getValue($key)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : false;
	}

	protected function setValue($key,$value,$expire)
	{
		$this->_data[$key]=$value;
		return true;
	}
}

class CCacheTest extends CTestCase
{
	private $_unserializeCalls=0;

	public function testMGetSkipsMissingValuesWithDefaultSerializer()
	{
		$cache=new CCacheTestStorage();
		$values=array('false'=>false, 'null'=>null, 'empty'=>'', 'zero'=>0, 'string'=>'value');
		foreach($values as $key=>$value)
			$this->assertTrue($cache->set($key,$value));

		$expected=array('missing-before'=>false)+$values+array('missing-after'=>false);
		$this->assertSame($expected,$cache->mget(array_keys($expected)));
	}

	public function testMGetSkipsMissingValuesWithCustomSerializer()
	{
		$cache=new CCacheTestStorage();
		$cache->serializer=array('serialize',array($this,'unserializeValue'));
		$this->assertTrue($cache->set('stored-false',false));
		$this->assertTrue($cache->set('stored-empty',''));

		$this->assertSame(array('missing'=>false,'stored-false'=>false,'stored-empty'=>''),
			$cache->mget(array('missing','stored-false','stored-empty')));
		$this->assertSame(2,$this->_unserializeCalls);
	}

	public function unserializeValue($value)
	{
		$this->assertInternalType('string',$value);
		++$this->_unserializeCalls;
		return unserialize($value);
	}
}
