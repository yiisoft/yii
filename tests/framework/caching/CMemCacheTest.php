<?php

if(!defined('MEMCACHE_TEST_HOST'))
	define('MEMCACHE_TEST_HOST', '127.0.0.1');

if(!defined('MEMCACHE_TEST_PORT'))
	define('MEMCACHE_TEST_PORT', 11211);


class CMemCacheTest extends CTestCase
{
	private $_config=array(
		'id'=>'testApp',
		'components'=>array(
			'cache'=>array(
	            'class'=>'CMemCache',
	            'servers'=>array(
	                array('host'=>MEMCACHE_TEST_HOST, 'port'=>MEMCACHE_TEST_PORT, 'weight'=>100),
	    		),
            ),
        ),

	);

	public function setUp()
	{
		if(!extension_loaded('memcache') && !extension_loaded('memcached'))
			$this->markTestSkipped('Memcache or memcached extensions are required.');
	}
	
	public function testMget()
	{
		$app=new TestApplication($this->_config);
		$app->reset();
		$cache=$app->cache;

		$data1=array('abc'=>1,2=>'def');
		$key1='data1';
		$data2=array('xyz'=>3,4=>'whn');
		$key2='data2';
		
		$cache->delete($key1);
		$cache->delete($key2);

		$this->assertFalse($cache->get($key1));
		$this->assertFalse($cache->get($key2));
		
		$cache->set($key1,$data1);
		$cache->set($key2,$data2);
		$this->assertTrue($cache->get($key1)===$data1);
		$this->assertTrue($cache->get($key2)===$data2);
		
		$mgetResult = $cache->mget(array($key1, $key2));
		$this->assertTrue(is_array($mgetResult));
		$this->assertEquals($mgetResult[$key1],$data1);
		$this->assertEquals($mgetResult[$key2],$data2);

		$cache->delete($key2);
		$mgetResult = $cache->mget(array($key1, $key2));
		$this->assertTrue(is_array($mgetResult));
		$this->assertEquals($mgetResult[$key1],$data1);
		$this->assertFalse($mgetResult[$key2]); // data2 is removed from cache

		$cache->delete($key1);
		$mgetResult = $cache->mget(array($key1, $key2));
		$this->assertTrue(is_array($mgetResult));
		$this->assertFalse($mgetResult[$key1]); // data1 is removed from cache
		$this->assertFalse($mgetResult[$key2]); // data2 is removed from cache
	}

	public function testKeyPrefix()
	{
		$cache=new CMemCache;
		$this->assertEquals($cache->keyPrefix,'');
		$cache->keyPrefix='key';
		$this->assertEquals($cache->keyPrefix,'key');

		$app=new TestApplication($this->_config);
		$app->reset();
		$this->assertTrue($app->cache instanceof CMemCache);
		$this->assertEquals($app->cache->keyPrefix,$app->id);
	}

	public function testGetAndSet()
	{
		$app=new TestApplication($this->_config);
		$app->reset();
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key='data1';
		$cache->delete($key);

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);

		$app2=new TestApplication($this->_config);
		$this->assertTrue($app2->cache->get($key)===$data);
	}

	public function testArrayAccess()
	{
		$app=new TestApplication($this->_config);
		$app->reset();
		$cache=$app->cache;
		$data=array('abc'=>1,2=>'def');
		$key='data2';
		$cache[$key]=$data;
		$this->assertTrue($cache->get($key)===$data);
		$this->assertTrue($cache[$key]===$data);
		unset($cache[$key]);
		$this->assertFalse($cache[$key]);
	}

	public function testExpire2()
	{
		$app=new TestApplication($this->_config);
		$app->reset();
		$cache=$app->cache;
		$data=array('xyz'=>3,4=>'mnp');
		$key='data3_2';
		$cache->set($key,$data,20);
		$this->assertTrue($cache->get($key)===$data);
		sleep(2);
		$app2=new TestApplication($this->_config);
		$this->assertEquals($data,$app2->cache->get($key));
	}

	public function testDelete()
	{
		$app=new TestApplication($this->_config);
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key='data10';
		$cache->set($key,$data);
		$cache->delete($key);
		$this->assertFalse($cache->get($key));
	}

	public function testBigExpireValues()
	{
		$app=new TestApplication($this->_config);
		$cache=$app->cache;

		$cache->set('key_1','value_1',60*60*24*30-10);
		$cache->set('key_2','value_2',60*60*24*30-2);
		$cache->set('key_3','value_3',60*60*24*30-1);
		$cache->set('key_4','value_4',60*60*24*30);
		$cache->set('key_5','value_5',60*60*24*30+1);
		$cache->set('key_6','value_6',60*60*24*30+2);
		$cache->set('key_7','value_7',60*60*24*30+10);

		sleep(4);

		$this->assertEquals('value_1',$cache->get('key_1'));
		$this->assertEquals('value_2',$cache->get('key_2'));
		$this->assertEquals('value_3',$cache->get('key_3'));
		$this->assertEquals('value_4',$cache->get('key_4'));
		$this->assertEquals('value_5',$cache->get('key_5'));
		$this->assertEquals('value_6',$cache->get('key_6'));
		$this->assertEquals('value_7',$cache->get('key_7'));
	}
}
