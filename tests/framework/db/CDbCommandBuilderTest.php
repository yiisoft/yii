<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.schema.mysql.CMysqlSchema');

class CDbCommandBuilderTest extends CTestCase
{
	/**
	 * @var CDbConnection
	 */
	private $db;

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_mysql'))
			$this->markTestSkipped('PDO and MySQL extensions are required.');

		$this->db=new CDbConnection('mysql:host=127.0.0.1;dbname=yii','test','test');
		$this->db->charset='UTF8';
		$this->db->enableParamLogging=true;
		try
		{
			$this->db->active=true;
		}
		catch(Exception $e)
		{
			$schemaFile=realpath(dirname(__FILE__).'/data/mysql.sql');
			$this->markTestSkipped("Please read $schemaFile for details on setting up the test environment for MySQL test case.");
		}

		$tables=array('comments','post_category','posts','categories','profiles','users','items','orders','types');
		foreach($tables as $table)
			$this->db->createCommand("DROP TABLE IF EXISTS $table CASCADE")->execute();

		$sqls=file_get_contents(dirname(__FILE__).'/data/mysql.sql');
		foreach(explode(';',$sqls) as $sql)
		{
			if(trim($sql)!=='')
				$this->db->createCommand($sql)->execute();
		}
	}

	public function tearDown()
	{
		$this->db->active=false;
	}

	public function testIssue1407_1()
	{
		// :parameter1 and :parameter2 should be removed inside CDbCommandBuilder::createCountCommand()
		$tableSchema=$this->db->getSchema()->getTable('users');
		$builder=$this->db->getSchema()->getCommandBuilder();

		$criteria1=new CDbCriteria();
		$criteria1->select=array('t.*',':parameter1 AS test');
		$criteria1->params[':parameter1']='testingValue';
		$criteria1->order='IF (t.username=:parameter2,t.username,t.email) DESC';
		$criteria1->params[':parameter2']='user2';
		$criteria1->addCondition('t.email LIKE :parameter4');
		$criteria1->params[':parameter4']='email%';
		$criteria1->addInCondition('t.id',array(1,2,3));

		$criteria2=clone $criteria1;

		$this->assertEquals(3,$builder->createCountCommand($tableSchema,$criteria1)->queryScalar());

		$result=$builder->createFindCommand($tableSchema,$criteria2)->queryAll();
		$this->assertCount(3,$result);
		$this->assertEquals(array(
			array(
				'id'=>'2',
				'username'=>'user2',
				'email'=>'email2',
				'test'=>'testingValue',
				'password'=>'pass2',
			),
			array(
				'id'=>'3',
				'username'=>'user3',
				'email'=>'email3',
				'test'=>'testingValue',
				'password'=>'pass3',
			),
			array(
				'id'=>'1',
				'username'=>'user1',
				'email'=>'email1',
				'test'=>'testingValue',
				'password'=>'pass1',
			),
		),$result);
	}

	public function testIssue1407_2()
	{
		// :parameter1 is not used in SQL, thus exception should be thrown
		$tableSchema=$this->db->getSchema()->getTable('users');
		$builder=$this->db->getSchema()->getCommandBuilder();

		$criteria=new CDbCriteria();
		$criteria->select=array('t.*');
		$criteria->params[':parameter1']='testingValue';
		$criteria->order='IF (t.username=:parameter2,t.username,t.email) DESC';
		$criteria->params[':parameter2']='user2';
		$criteria->addCondition('t.email LIKE :parameter4');
		$criteria->params[':parameter4']='email%';
		$criteria->addInCondition('t.id',array(1,2,3));

		$this->setExpectedException('CDbException');
		$builder->createCountCommand($tableSchema,$criteria)->queryScalar();
	}

	public function testIssue1407_3()
	{
		// :parameter2 is not used in SQL, thus exception should be thrown
		$tableSchema=$this->db->getSchema()->getTable('users');
		$builder=$this->db->getSchema()->getCommandBuilder();

		$criteria=new CDbCriteria();
		$criteria->select=array('t.*',':parameter1 AS test');
		$criteria->params[':parameter1']='testingValue';
		$criteria->order='IF (t.username="user2",t.username,t.email) DESC';
		$criteria->params[':parameter2']='user2';
		$criteria->addCondition('t.email LIKE :parameter4');
		$criteria->params[':parameter4']='email%';
		$criteria->addInCondition('t.id',array(1,2,3));

		$this->setExpectedException('CDbException');
		$builder->createCountCommand($tableSchema,$criteria)->queryScalar();
	}

	public function testIssue1407_4()
	{
		// both :parameter1 and :parameter2 are not used in SQL, thus exception should be thrown
		$tableSchema=$this->db->getSchema()->getTable('users');
		$builder=$this->db->getSchema()->getCommandBuilder();

		$criteria=new CDbCriteria();
		$criteria->select=array('t.*');
		$criteria->params[':parameter1']='testingValue';
		$criteria->order='IF (t.username="user2",t.username,t.email) DESC';
		$criteria->params[':parameter2']='user2';
		$criteria->addCondition('t.email LIKE :parameter4');
		$criteria->params[':parameter4']='email%';
		$criteria->addInCondition('t.id',array(1,2,3));

		$this->setExpectedException('CDbException');
		$builder->createCountCommand($tableSchema,$criteria)->queryScalar();
	}

	public function testIssue1407_5()
	{
		// :parameter3 is not used
		$tableSchema=$this->db->getSchema()->getTable('users');
		$builder=$this->db->getSchema()->getCommandBuilder();

		$criteria=new CDbCriteria();
		$criteria->select=array('t.*',':parameter1 AS test');
		$criteria->params[':parameter1']='testingValue';
		$criteria->order='IF (t.username=:parameter2,t.username,t.email) DESC';
		$criteria->params[':parameter2']='user2';
		$criteria->params[':parameter3']='parameter3Value';
		$criteria->addCondition('t.email LIKE :parameter4');
		$criteria->params[':parameter4']='email%';
		$criteria->addInCondition('t.id',array(1,2,3));

		$this->setExpectedException('CDbException');
		$builder->createCountCommand($tableSchema,$criteria)->queryScalar();
	}

	public function testMultipleInsert()
	{
		$builder=$this->db->getSchema()->getCommandBuilder();
		$tableName='types';
		$data=array(
			array(
				'int_col'=>1,
				'char_col'=>'char_col_1',
				'char_col2'=>'char_col_2_1',
				'float_col'=>1.1,
				'bool_col'=>true,
			),
			array(
				'int_col'=>2,
				'char_col'=>'char_col_2',
				'float_col'=>2.2,
				'bool_col'=>false,
			),
		);
		$command=$builder->createMultipleInsertCommand($tableName,$data);
		$command->execute();

		$rows=$builder->dbConnection->createCommand('SELECT * FROM '.$builder->dbConnection->quoteTableName($tableName))->queryAll();

		$this->assertEquals(count($data),count($rows),'Records count miss matches!');
		foreach($rows as $rowIndex=>$row)
			foreach($row as $columnName=>$value)
			{
				$columnIndex=array_search($columnName,$data[$rowIndex],true);
				if($columnIndex==false)
					continue;
				$expectedValue=$data[$rowIndex][$columnIndex];
				$this->assertTrue($expectedValue==$value,"Value for column '{$columnName}' incorrect!");
			}
	}
}
