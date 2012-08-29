<?php

class CRedisHttpSessionTest extends CTestCase
{
	public function testGetSavePath()
	{
		$redis = new CRedisHttpSession();

		$redis->servers = array(array('host' => 'localhost'));
		$this->assertSame(
			'tcp://localhost:6379?prefix=YiiSession&timeout=60',
			$redis->getSavePath()
		);

		$redis->servers = array(array('host' => '127.0.0.1', 'port' => 2233));
		$this->assertSame(
			'tcp://127.0.0.1:2233?prefix=YiiSession&timeout=60',
			$redis->getSavePath()
		);

		// Thanks this assertion fix missing ampersand before weight :)
		$redis->servers = array(
			array('host' => 'localhost', 'port' => 6379, 'weight' => 2),
			array('host' => 'localhost', 'port' => 6380, 'weight' => 5),
		);
		$this->assertSame(
			'tcp://localhost:6379?prefix=YiiSession&timeout=60&weight=2, tcp://localhost:6380?prefix=YiiSession&timeout=60&weight=5',
			$redis->getSavePath()
		);

		$redis->servers = array(array(
			'host' => 'localhost',
			'prefix' => 'other prefix',
			'timeout' => 25,
		));
		$this->assertSame(
			'tcp://localhost:6379?prefix=other prefix&timeout=25',
			$redis->getSavePath()
		);
	}

	public function testGetSetSessionData()
	{
		$sess = new CRedisHttpSession();
		$sess->servers = array(array('host' => 'localhost'));
		$sess->init();

		$key = $sess->prefix . $sess->getSessionID();

		$sess['foo'] = 'bar';
		$sess['array'] = array('pew' => 55);
		session_write_close();

		$redis = new Redis();
		$redis->connect('localhost');

		$this->assertSame('foo|s:3:"bar";array|a:1:{s:3:"pew";i:55;}', $redis->get($key));

		$redis->del($key);
	}
}

?>