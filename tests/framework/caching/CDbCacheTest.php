<?php

if(!defined('DBCACHE_TEST_DBFILE'))
	define('DBCACHE_TEST_DBFILE',Yii::app()->getRuntimePath().'/CDbCacheTest_database.db');

if(!defined('DBCACHE_TEST_DB'))
	define('DBCACHE_TEST_DB','sqlite:'.DBCACHE_TEST_DBFILE);


class CDbCacheTest extends CTestCase
{
	private $_config1=array(
		'id'=>'testApp',
		'components'=>array(
			'cache'=>array(
				'class'=>'CDbCache',
			),
		),
	);
	private $_config2=array(
		'id'=>'testApp',
		'components'=>array(
			'db'=>array(
				'connectionString'=>DBCACHE_TEST_DB,
			),
			'cache'=>array(
				'class'=>'system.caching.CDbCache',
				'connectionID'=>'db',
			),
		),
	);

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');
	}

	public function testKeyPrefix()
	{
		$cache=new CDbCache;
		$this->assertEquals($cache->keyPrefix,'');
		$cache->keyPrefix='key';
		$this->assertEquals($cache->keyPrefix,'key');

		$app=new TestApplication($this->_config1);
		$app->reset();
		$this->assertTrue($app->cache instanceof CDbCache);
		$this->assertEquals($app->cache->keyPrefix,$app->id);
	}

	public function testGetAndSet()
	{
		$app=new TestApplication($this->_config1);
		$app->reset();
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key='data1';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);

		$app2=new TestApplication($this->_config1);
		$this->assertTrue($app2->cache->get($key)===$data);
	}

	public function testMGet()
	{
		$app=new TestApplication($this->_config1);
		$app->reset();
		$cache=$app->cache;

		$key1='multidata1';
		$data1='abc';
		$key2='multidata2';
		$data2=34;

		$this->assertEquals($cache->mget(array($key1,$key2)), array($key1=>false,$key2=>false));
		$cache->set($key1,$data1);
		$cache->set($key2,$data2);
		$this->assertEquals($cache->mget(array($key1,$key2)), array($key1=>$data1,$key2=>$data2));
		$app2=new TestApplication($this->_config1);
		$this->assertEquals($app2->cache->mget(array($key1,$key2)), array($key1=>$data1,$key2=>$data2));
	}

	public function testArrayAccess()
	{
		$app=new TestApplication($this->_config1);
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

	public function testExpire()
	{
		$app=new TestApplication($this->_config1);
		$app->reset();
		$cache=$app->cache;
		$data=array('abc'=>1,2=>'def');
		$key='data3';
		$cache->set($key,$data,2);
		$this->assertTrue($cache->get($key)===$data);
		sleep(4);
		$app2=new TestApplication($this->_config1);
		$this->assertFalse($app2->cache->get($key));
	}

	public function testUseDbConnection()
	{
		@unlink(DBCACHE_TEST_DBFILE);
		$app=new TestApplication($this->_config2);
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key='data4';
		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);

		$app2=new TestApplication($this->_config2);
		$this->assertTrue($app2->cache->get($key)===$data);
	}

	public function testDependency()
	{
		@unlink(DBCACHE_TEST_DBFILE);
		$app=new TestApplication($this->_config2);
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key='data5';

		$cache->set($key,$data,0,new CFileCacheDependency(__FILE__));
		$this->assertTrue($cache->get($key)===$data);
		$app=new TestApplication($this->_config2);
		$this->assertTrue($app->cache->get($key)===$data);

		$key2='data6';
		$cache->set($key2,$data,0,new CFileCacheDependency(DBCACHE_TEST_DBFILE));
		sleep(2); // sleep to ensure timestamp is changed for the db file
		$cache->set('data7',$data);
		$app=new TestApplication($this->_config2);
		$this->assertFalse($app->cache->get($key2));
	}

	public function testAdd()
	{
		@unlink(DBCACHE_TEST_DBFILE);
		$app=new TestApplication($this->_config2);
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key='data8';

		$cache->set($key,$data);
		$this->assertTrue($cache->set($key,$data));
		$this->assertFalse($cache->add($key,$data));
		$this->assertTrue($cache->add('data9',$data));
	}

	public function testDelete()
	{
		@unlink(DBCACHE_TEST_DBFILE);
		$app=new TestApplication($this->_config2);
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key='data10';
		$cache->set($key,$data);
		$cache->delete($key);
		$this->assertFalse($cache->get($key));
	}

	public function testFlush()
	{
		@unlink(DBCACHE_TEST_DBFILE);
		$app=new TestApplication($this->_config2);
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key1='data11';
		$key2='data12';
		$cache->set($key1,$data);
		$cache->set($key2,$data);
		$cache->flush();
		$this->assertFalse($cache->get($key1));
		$this->assertFalse($cache->get($key2));
	}
}