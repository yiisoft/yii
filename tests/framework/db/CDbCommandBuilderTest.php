<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.schema.mysql.CMysqlSchema');

class CDbCommandBuilderTest extends CTestCase
{
	/**
	 * @var CDbConnection
	 */
	private $db;
	/**
	 * @var CDbCommandBuilder
	 */
	private $builder;

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

		$this->builder=$this->db->schema->commandBuilder;
	}

	public function tearDown()
	{
		$this->db->active=false;
	}

	public function testIssue957()
	{
		// changing origin data
		$criteria=new CDbCriteria();
		$criteria->join='INNER JOIN profiles ON profiles.user_id=users.id';
		$criteria->condition='user_id=:uid';
		$criteria->params[':uid']=2;
		$criteria->limit=1;
		$criteria->offset=1;
		$updateCommand=$this->builder->createUpdateCommand('users',array('password'=>'123','first_name'=>'321'),$criteria);
		$this->assertNotSame(false,strpos($updateCommand->text,'UPDATE'));
		$this->assertNotSame(false,strpos($updateCommand->text,'INNER JOIN'));
		$this->assertNotSame(false,strpos($updateCommand->text,'ON'));
		$this->assertSame(false,strpos($updateCommand->text,'LIMIT'));
		$updateCommand->execute();

		// asserting changed data
		$criteria=new CDbCriteria();
		$criteria->select='password';
		$users=$this->builder->createFindCommand('users',$criteria)->queryColumn();
		$this->assertEquals(array('pass1','123','pass3'),$users);

		$criteria=new CDbCriteria();
		$criteria->select='first_name';
		$profiles=$this->builder->createFindCommand('profiles',$criteria)->queryColumn();
		$this->assertEquals(array('first 1','first 2'),$profiles);
	}
}
