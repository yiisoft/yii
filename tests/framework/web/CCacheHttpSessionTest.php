<?php


class CCacheHttpSessionTest extends CTestCase
{
	/**
	 * @covers CCacheHttpSession::writeSession
	 * @covers CCacheHttpSession::readSession
	 * @covers CCacheHttpSession::destroySession
	 */
	public function testCustomSessionHandler()
	{
		$config=array(
			'components'=>array(
				'cache'=>array(
					'class'=>'CFileCache',
				),
			),
		);
		new TestApplication($config);

		$session=new CCacheHttpSession();
		$session->init();

		$this->assertTrue($session->destroySession('test'));
		$this->assertEquals('',$session->readSession('test'));

		$this->assertTrue($session->writeSession('test','any value'));
		$this->assertEquals('any value',$session->readSession('test'));

		$this->assertTrue($session->destroySession('test'));
		$this->assertEquals('',$session->readSession('test'));
	}

	/**
	 * @covers CCacheHttpSession::init
	 */
	public function testInvalidCache()
	{
		$session=new CCacheHttpSession();
		$session->cacheID='invalidCacheID';

		$this->setExpectedException('CException');

		$session->init();
	}
}
