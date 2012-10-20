<?php

Yii::import('system.db.CDbConnection');

class CDbCommandTest extends CTestCase
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

	public function testGetText()
	{
		$sql='SELECT * FROM posts';
		$command=$this->_connection->createCommand($sql);
		$this->assertEquals($command->text,$sql);
	}

	public function testSetText()
	{
		$sql='SELECT title FROM posts';
		$command=$this->_connection->createCommand($sql);
		$this->assertEquals($command->queryScalar(),'post 1');

		$newSql='SELECT id FROM posts';
		$command->text=$newSql;
		$this->assertEquals($command->text,$newSql);
		$this->assertEquals($command->queryScalar(),1);
	}

	public function testConnection()
	{
		$sql='SELECT title FROM posts';
		$command=$this->_connection->createCommand($sql);
		$this->assertEquals($command->connection,$this->_connection);
	}

	public function testPrepare()
	{
		$sql='SELECT title FROM posts';
		$command=$this->_connection->createCommand($sql);
		$this->assertEquals($command->pdoStatement,null);
		$command->prepare();
		$this->assertTrue($command->pdoStatement instanceof PDOStatement);
		$this->assertEquals($command->queryScalar(),'post 1');

		$command->text='Bad SQL';
		$this->setExpectedException('CException');
		$command->prepare();
	}

	public function testCancel()
	{
		$sql='SELECT title FROM posts';
		$command=$this->_connection->createCommand($sql);
		$command->prepare();
		$this->assertTrue($command->pdoStatement instanceof PDOStatement);
		$command->cancel();
		$this->assertEquals($command->pdoStatement,null);
	}

	public function testExecute()
	{
		$sql='INSERT INTO comments(content,post_id,author_id) VALUES (\'test comment\', 1, 1)';
		$command=$this->_connection->createCommand($sql);
		$this->assertEquals($command->execute(),1);
		$this->assertEquals($command->execute(),1);
		$command=$this->_connection->createCommand('SELECT * FROM comments WHERE content=\'test comment\'');
		$this->assertEquals($command->execute(),0);
		$command=$this->_connection->createCommand('SELECT COUNT(*) FROM comments WHERE content=\'test comment\'');
		$this->assertEquals($command->queryScalar(),2);

		$command=$this->_connection->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->execute();
	}

	public function testQuery()
	{
		$sql='SELECT * FROM posts';
		$reader=$this->_connection->createCommand($sql)->query();
		$this->assertTrue($reader instanceof CDbDataReader);

		$sql='SELECT * FROM posts';
		$command=$this->_connection->createCommand($sql);
		$command->prepare();
		$reader=$command->query();
		$this->assertTrue($reader instanceof CDbDataReader);

		$command=$this->_connection->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->query();
	}

	public function testBindParam()
	{
		$sql='INSERT INTO posts(title,create_time,author_id) VALUES (:title, :create_time, 1)';
		$command=$this->_connection->createCommand($sql);
		$title='test title';
		$createTime=time();
		$command->bindParam(':title',$title);
		$command->bindParam(':create_time',$createTime);
		$command->execute();

		$sql='SELECT create_time FROM posts WHERE title=:title';
		$command=$this->_connection->createCommand($sql);
		$command->bindParam(':title',$title);
		$this->assertEquals($command->queryScalar(),$createTime);

		$sql='INSERT INTO types (int_col, char_col, float_col, blob_col, numeric_col, bool_col) VALUES (:int_col, :char_col, :float_col, :blob_col, :numeric_col, :bool_col)';
		$command=$this->_connection->createCommand($sql);
		$intCol=123;
		$charCol='abc';
		$floatCol=1.23;
		$blobCol="\x10\x11\x12";
		$numericCol='1.23';
		$boolCol=false;
		$command->bindParam(':int_col',$intCol);
		$command->bindParam(':char_col',$charCol);
		$command->bindParam(':float_col',$floatCol);
		$command->bindParam(':blob_col',$blobCol);
		$command->bindParam(':numeric_col',$numericCol);
		$command->bindParam(':bool_col',$boolCol);
		$this->assertEquals(1,$command->execute());

		$sql='SELECT * FROM types';
		$row=$this->_connection->createCommand($sql)->queryRow();
		$this->assertEquals($row['int_col'],$intCol);
		$this->assertEquals($row['char_col'],$charCol);
		$this->assertEquals($row['float_col'],$floatCol);
		$this->assertEquals($row['blob_col'],$blobCol);
		$this->assertEquals($row['numeric_col'],$numericCol);
	}

	public function testBindValue()
	{
		$sql='INSERT INTO comments(content,post_id,author_id) VALUES (:content, 1, 1)';
		$command=$this->_connection->createCommand($sql);
		$command->bindValue(':content','test comment');
		$command->execute();

		$sql='SELECT post_id FROM comments WHERE content=:content';
		$command=$this->_connection->createCommand($sql);
		$command->bindValue(':content','test comment');
		$this->assertEquals($command->queryScalar(),1);
	}

	public function testQueryAll()
	{
		$rows=$this->_connection->createCommand('SELECT * FROM posts')->queryAll();
		$this->assertEquals(count($rows),5);
		$row=$rows[2];
		$this->assertEquals($row['id'],3);
		$this->assertEquals($row['title'],'post 3');

		$rows=$this->_connection->createCommand('SELECT * FROM posts WHERE id=10')->queryAll();
		$this->assertEquals($rows,array());
	}

	public function testQueryRow()
	{
		$sql='SELECT * FROM posts';
		$row=$this->_connection->createCommand($sql)->queryRow();
		$this->assertEquals($row['id'],1);
		$this->assertEquals($row['title'],'post 1');

		$sql='SELECT * FROM posts';
		$command=$this->_connection->createCommand($sql);
		$command->prepare();
		$row=$command->queryRow();
		$this->assertEquals($row['id'],1);
		$this->assertEquals($row['title'],'post 1');

		$sql='SELECT * FROM posts WHERE id=10';
		$command=$this->_connection->createCommand($sql);
		$this->assertFalse($command->queryRow());

		$command=$this->_connection->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->queryRow();
	}

	public function testQueryColumn()
	{
		$sql='SELECT * FROM posts';
		$column=$this->_connection->createCommand($sql)->queryColumn();
		$this->assertEquals($column,range(1,5));

		$command=$this->_connection->createCommand('SELECT id FROM posts WHERE id=10');
		$this->assertEquals($command->queryColumn(),array());

		$command=$this->_connection->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->queryColumn();
	}

	public function testQueryScalar()
	{
		$sql='SELECT * FROM posts';
		$this->assertEquals($this->_connection->createCommand($sql)->queryScalar(),1);

		$sql='SELECT id FROM posts';
		$command=$this->_connection->createCommand($sql);
		$command->prepare();
		$this->assertEquals($command->queryScalar(),1);

		$command=$this->_connection->createCommand('SELECT id FROM posts WHERE id=10');
		$this->assertFalse($command->queryScalar());

		$command=$this->_connection->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->queryScalar();
	}

	public function testFetchMode(){
		$sql='SELECT * FROM posts';
		$command=$this->_connection->createCommand($sql);
		$result = $command->queryRow();
		$this->assertTrue(is_array($result));

		$sql='SELECT * FROM posts';
		$command=$this->_connection->createCommand($sql);
		$command->setFetchMode(PDO::FETCH_OBJ);
		$result = $command->queryRow();
		$this->assertTrue(is_object($result));
	}
}