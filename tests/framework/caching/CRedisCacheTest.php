<?php

class CRedisCacheTest extends CTestCase
{
	protected $config = array(
		'class' => 'CRedisCache',
		'hostname' => 'localhost',
		'port' => 6379,
		'database' => 0,
	);

	protected function getApplication()
	{
		$app=new TestApplication(array(
			'id' => 'testapp',
			'components'=>array(
				'cache' => $this->config
			)
		));
		$app->cache->flush();
		return $app;
	}

	public function setUp()
	{
		$dsn = $this->config['hostname'] . ':' .$this->config['port'];
		if(!@stream_socket_client($dsn, $errorNumber, $errorDescription, 0.5)) {
			$this->markTestSkipped('No redis server running at ' . $dsn .' : ' . $errorNumber . ' - ' . $errorDescription);
		}
	}

	public function testKeyPrefix()
	{
		$cache=new CRedisCache;
		$this->assertEquals($cache->keyPrefix,'');
		$cache->keyPrefix='key';
		$this->assertEquals($cache->keyPrefix,'key');

		$app=$this->getApplication();
		$this->assertTrue($app->cache instanceof CRedisCache);
		$this->assertEquals($app->id,$app->cache->keyPrefix);
	}

	public function testGetAndSet()
	{
		$app=$this->getApplication();
		$cache=$app->cache;

		$data=array('abc'=>1,2=>'def');
		$key='data1';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);
	}

	public function testMGet()
	{
		$app=$this->getApplication();
		$cache=$app->cache;

		$key1='multidata1';
		$data1='abc';
		$key2='multidata2';
		$data2=34;

		$this->assertEquals($cache->mget(array($key1,$key2)), array($key1=>false,$key2=>false));
		$cache->set($key1,$data1);
		$cache->set($key2,$data2);
		$this->assertEquals($cache->mget(array($key1,$key2)), array($key1=>$data1,$key2=>$data2));
	}

	public function testArrayAccess()
	{
		$app=$this->getApplication();
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
		$app=$this->getApplication();
		$cache=$app->cache;
		$data=array('abc'=>1,2=>'def');
		$key='data3';
		$cache->set($key,$data,2);
		$this->assertTrue($cache->get($key)===$data);
		sleep(1);
		$this->assertTrue($cache->get($key)===$data);
		sleep(2);
		$this->assertFalse($cache->get($key));
	}

	public function testAdd()
	{
		$cache = $this->getApplication()->cache;
		$this->assertTrue($cache->set('number_test', 42));

		// should not change existing keys
		$this->assertFalse($cache->add('number_test', 13));
		$this->assertEquals(42, $cache->get('number_test'));

		// should store data if it's not there yet
		$this->assertFalse($cache->get('add_test'));
		$this->assertTrue($cache->add('add_test', 13));
		$this->assertEquals(13, $cache->get('add_test'));
	}

	public function testDelete()
	{
		$cache = $this->getApplication()->cache;
		$this->assertTrue($cache->set('number_test', 'testvalue'));

		$this->assertNotNull($cache->get('number_test'));
		$this->assertTrue($cache->delete('number_test'));
		$this->assertFalse($cache->get('number_test'));
	}

	public function testFlush()
	{
		$cache = $this->getApplication()->cache;
		$this->assertTrue($cache->set('number_test', 'testvalue'));
		$this->assertTrue($cache->flush());
		$this->assertFalse($cache->get('number_test'));
	}

	/**
	 * Store a value that is 2 times buffer size big
	 * https://github.com/yiisoft/yii/pull/2750
	 */
	public function testLargeData()
	{
		$app=$this->getApplication();
		$cache=$app->cache;

		$data=str_repeat('XX',8192); // http://www.php.net/manual/en/function.fread.php
		$key='bigdata1';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);

		// try with multibyte string
		$data=str_repeat('ЖЫ',8192); // http://www.php.net/manual/en/function.fread.php
		$key='bigdata2';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);
	}

	public function testMultiByteGetAndSet()
	{
		$app=$this->getApplication();
		$cache=$app->cache;

		$data=array('abc'=>'ежик',2=>'def');
		$key='data1';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);
	}

}
