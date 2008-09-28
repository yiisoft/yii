<?php

Yii::import('system.db.CDbConnection');

if(!defined('SRC_DB_FILE'))
	define('SRC_DB_FILE',dirname(__FILE__).'/data/source.db');
if(!defined('TEST_DB_FILE'))
	define('TEST_DB_FILE',dirname(__FILE__).'/data/test.db');

class CDbTransactionTest extends CTestCase
{
	private $_connection;

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');
		copy(SRC_DB_FILE,TEST_DB_FILE);

		$this->_connection=new CDbConnection('sqlite:'.TEST_DB_FILE);
		$this->_connection->active=true;
	}

	public function tearDown()
	{
		$this->_connection->active=false;
	}

	public function testBeginTransaction()
	{
		$sql='INSERT INTO posts(id,title,create_time,author_id) VALUES(10,\'test post\',11000,1)';
		$transaction=$this->_connection->beginTransaction();
		try
		{
			$this->_connection->createCommand($sql)->execute();
			$this->_connection->createCommand($sql)->execute();
			$this->fail('Expected exception not raised');
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollBack();
			$reader=$this->_connection->createCommand('SELECT * FROM posts WHERE id=10')->query();
			$this->assertFalse($reader->read());
		}
	}

	public function testCommit()
	{
		$sql='INSERT INTO posts(id,title,create_time,author_id) VALUES(10,\'test post\',11000,1)';
		$transaction=$this->_connection->beginTransaction();
		try
		{
			$this->_connection->createCommand($sql)->execute();
			$this->assertTrue($transaction->active);
			$transaction->commit();
			$this->assertFalse($transaction->active);
		}
		catch(Exception $e)
		{
			$transaction->rollBack();
			$this->fail('Unexpected exception');
		}
		$n=$this->_connection->createCommand('SELECT COUNT(*) FROM posts WHERE id=10')->queryScalar();
		$this->assertEquals($n,1);
	}
}

