<?php

Yii::import('system.db.CDbConnection');

class PostRecord extends CComponent
{
	public $param1;
	public $param2;
	public $id;
	private $_title;
	public $content;
	public $create_time;
	public $author_id;

	public function __construct($param1,$param2)
	{
		$this->param1=$param1;
		$this->param2=$param2;
	}

	public function getTitle()
	{
		return $this->_title;
	}

	public function setTitle($value)
	{
		$this->_title=$value;
	}
}

class CDbDataReaderTest extends CTestCase
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

	public function testRead()
	{
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();
		for($i=1;$i<=5;++$i)
		{
			$row=$reader->read();
			$this->assertEquals($row['id'],$i);
		}
		$this->assertFalse($reader->read());
	}

	public function testReadColumn()
	{
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();
		$this->assertEquals($reader->readColumn(0),1);
		$this->assertEquals($reader->readColumn(1),'post 2');
		$reader->readColumn(0);
		$reader->readColumn(0);
		$this->assertEquals($reader->readColumn(0),5);
		$this->assertFalse($reader->readColumn(0));
	}

	public function testReadObject()
	{
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();
		$object=$reader->readObject('PostRecord',array(null,'v2'));
		$this->assertEquals($object->id,1);
		$this->assertEquals($object->title,'post 1');
		$this->assertEquals($object->param1,null);
		$this->assertEquals($object->param2,'v2');
	}

	public function testReadAll()
	{
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();
		$rows=$reader->readAll();
		$this->assertEquals(count($rows),5);
		$row=$rows[2];
		$this->assertEquals($row['id'],3);
		$this->assertEquals($row['title'],'post 3');

		$reader=$this->_connection->createCommand('SELECT * FROM posts WHERE id=10')->query();
		$this->assertEquals($reader->readAll(),array());
	}

	public function testClose()
	{
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();
		$row=$reader->read();
		$row=$reader->read();
		$this->assertFalse($reader->isClosed);
		$reader->close();
		$this->assertTrue($reader->isClosed);
	}

	public function testRowCount()
	{
		// unable to test because SQLite doesn't support row count
	}

	public function testColumnCount()
	{
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();
		$this->assertEquals($reader->columnCount,5);

		$reader=$this->_connection->createCommand('SELECT * FROM posts WHERE id=11')->query();
		$this->assertEquals($reader->ColumnCount,5);
	}

	public function testForeach()
	{
		$ids=array();
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();
		foreach($reader as $row)
			$ids[]=$row['id'];
		$this->assertEquals(count($ids),5);
		$this->assertEquals($ids[3],4);

		$this->setExpectedException('CException');
		foreach($reader as $row)
			$ids[]=$row['id'];
	}

	public function testFetchMode()
	{
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();

		$reader->fetchMode=PDO::FETCH_NUM;
		$row=$reader->read();
		$this->assertFalse(isset($row['id']));
		$this->assertTrue(isset($row[0]));

		$reader->fetchMode=PDO::FETCH_ASSOC;
		$row=$reader->read();
		$this->assertTrue(isset($row['id']));
		$this->assertFalse(isset($row[0]));
	}

	public function testBindColumn()
	{
		$reader=$this->_connection->createCommand('SELECT * FROM posts')->query();
		$reader->bindColumn(1,$id);
		$reader->bindColumn(2,$title);
		$reader->read();
		$this->assertEquals($id,1);
		$this->assertEquals($title,'post 1');
		$reader->read();
		$this->assertEquals($id,2);
		$this->assertEquals($title,'post 2');
	}
}

