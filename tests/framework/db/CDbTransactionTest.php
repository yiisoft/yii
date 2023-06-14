<?php

Yii::import('system.db.CDbConnection');

class CDbTransactionTest extends CTestCase
{
	private $_connection;

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->_connection=new CDbConnection('sqlite::memory:');
		$this->_connection->active=true;
		$this->_connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/data/sqlite.sql'));
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
			$transaction->rollback();
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
			$transaction->rollback();
			$this->fail('Unexpected exception');
		}
		$n=$this->_connection->createCommand('SELECT COUNT(*) FROM posts WHERE id=10')->queryScalar();
		$this->assertEquals($n,1);
	}

	public function testEventCommit()
	{
		$commited = null;
		$rollbacked = null;
		$sql='INSERT INTO posts(id,title,create_time,author_id) VALUES(10,\'test post\',11000,1)';
		$transaction=$this->_connection->beginTransaction();
		$transaction->onCommit = function($event) use (&$commited) { $commited = true; };
		$transaction->onRollback = function($event) use (&$rollbacked) { $rollbacked = true; };
		try
		{
			$this->_connection->createCommand($sql)->execute();
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollback();
		}

		$this->assertTrue($commited);
		$this->assertNull($rollbacked);
	}

	public function testEventRollbacked()
	{
		$commited = null;
		$rollbacked = null;
		$sql='INSERT INTO posts(id,title,create_time,author_id) VALUES(1,\'test post\',11000,1)';
		$transaction=$this->_connection->beginTransaction();
		$transaction->onCommit = function($event) use (&$commited) { $commited = true; };
		$transaction->onRollback = function($event) use (&$rollbacked) { $rollbacked = true; };
		try
		{
			$this->_connection->createCommand($sql)->execute();
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollback();
		}

		$this->assertNull($commited);
		$this->assertTrue($rollbacked);
	}
}

