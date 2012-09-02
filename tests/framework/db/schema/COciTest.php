<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.schema.oci.COciSchema');

/**
 * @group oci
 */
class COciTest extends CTestCase
{
	const DB_DSN_PREFIX='oci';
	const DB_HOST='127.0.0.1';
	const DB_PORT='1521';
	const DB_SERVICE='xe';
	const DB_USER='test';
	const DB_PASS='123123';

	/**
	 * @var CDbConnection
	 */
	private $db;

	public function setUp()
	{
		if((!extension_loaded('oci8') && !extension_loaded('oci8_11g')) || !extension_loaded('pdo') || !extension_loaded('pdo_oci'))
			$this->markTestSkipped('PDO and OCI extensions are required.');

		$dsn=self::DB_DSN_PREFIX.':dbname='.self::DB_HOST.':'.self::DB_PORT.'/'.self::DB_SERVICE.';charset=UTF8';
		$schemaFilePath=realpath(dirname(__FILE__).'/../data/oci.sql');

		$this->db=new CDbConnection($dsn, self::DB_USER, self::DB_PASS);
		$this->db->charset='UTF8';

		try
		{
			$this->db->active=true;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			$this->markTestSkipped("Please read {$schemaFilePath} for details on setting up the test environment for OCI test case.");
		}

		$rawSqls=file_get_contents($schemaFilePath);
		$sqls='';

		foreach(array_filter(explode("\n", $rawSqls)) as $line)
		{
			if(substr($line, 0, 2)=='--' && substr($line, 0, 13)!='--SEPARATOR--')
				continue;
			$sqls.=$line."\n";
		}

		foreach(explode('--SEPARATOR--', $sqls) as $sql)
		{
			if(trim($sql)!=='')
				$this->db->createCommand($sql)->execute();
		}
	}

	public function tearDown()
	{
		$this->db->active=false;
	}

	public function testSchema()
	{
		$schema=$this->db->schema;
		$this->assertInstanceOf('CDbSchema', $schema);
		$this->assertEquals($schema->dbConnection, $this->db);
		$this->assertInstanceOf('CDbCommandBuilder', $schema->commandBuilder);
		$this->assertEquals('"users"', $schema->quoteTableName('users'));
		$this->assertEquals('"id"', $schema->quoteColumnName('id'));
		$this->assertInstanceOf('CDbTableSchema', $schema->getTable('users'));
		$this->assertNull($schema->getTable('foo'));
	}

	public function testColumnComments()
	{
		$tables=$this->db->schema->tables;

		$usersColumns=$tables['users']->columns;
		$this->assertEquals('User\'s entry primary key', $usersColumns['id']->comment);
		$this->assertEquals('Имя пользователя', $usersColumns['username']->comment);
		$this->assertEquals('用户的密码', $usersColumns['password']->comment);
		$this->assertEquals('דוא"ל של המשתמש', $usersColumns['email']->comment);
	}
}
