<?php

Yii::import('system.db.CDbConnection');

class CPostgres2Test extends CTestCase
{
	private $db;

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_pgsql'))
			$this->markTestSkipped('PDO and PostgreSQL extensions are required.');

		$this->db=new CDbConnection('pgsql:host=127.0.0.1;dbname=yii','test','test');
		$this->db->charset='UTF8';
		try
		{
			$this->db->active=true;
		}
		catch(Exception $e)
		{
			$schemaFile=realpath(dirname(__FILE__).'/../data/postgres.sql');
			$this->markTestSkipped("Please read $schemaFile for details on setting up the test environment for PostgreSQL test case.");
		}
	}

	public function tearDown()
	{
		$this->db->active=false;
	}

	public function testCreateTable()
	{
		$sql=$this->db->schema->createTable('test',array(
			'id'=>'pk',
			'name'=>'string not null',
			'desc'=>'text',
			'primary key (id, name)',
		));
		$expect="CREATE TABLE \"test\" (\n"
			. "\t\"id\" serial NOT NULL PRIMARY KEY,\n"
			. "\t\"name\" character varying (255) not null,\n"
			. "\t\"desc\" text,\n"
			. "\tprimary key (id, name)\n"
			. ")";
		$this->assertEquals($expect, $sql);
	}

	public function testCreateTableBig()
	{
		$sql=$this->db->schema->createTable('test',array(
			'id'=>'bigpk',
			'name'=>'string not null',
			'desc'=>'text',
			'number'=>'bigint',
			'number2'=>'bigint not null default 0',
			'primary key (id, name)',
		));
		$expect="CREATE TABLE \"test\" (\n"
			. "\t\"id\" bigserial NOT NULL PRIMARY KEY,\n"
			. "\t\"name\" character varying (255) not null,\n"
			. "\t\"desc\" text,\n"
			. "\t\"number\" bigint,\n"
			. "\t\"number2\" bigint not null default 0,\n"
			. "\tprimary key (id, name)\n"
			. ")";
		$this->assertEquals($expect, $sql);
	}

	public function testRenameTable()
	{
		$sql=$this->db->schema->renameTable('test', 'test2');
		$expect='ALTER TABLE "test" RENAME TO "test2"';
		$this->assertEquals($expect, $sql);
	}

	public function testDropTable()
	{
		$sql=$this->db->schema->dropTable('test');
		$expect='DROP TABLE "test"';
		$this->assertEquals($expect, $sql);
	}

	public function testAddColumn()
	{
		$sql=$this->db->schema->addColumn('test', 'id', 'integer');
		$expect='ALTER TABLE "test" ADD COLUMN "id" integer';
		$this->assertEquals($expect, $sql);
	}

	public function testAlterColumn()
	{
		$sql=$this->db->schema->alterColumn('test', 'id', 'boolean');
		$expect='ALTER TABLE "test" ALTER COLUMN "id" TYPE boolean';
		$this->assertEquals($expect, $sql);
	}

	public function testRenameColumn()
	{
		$sql=$this->db->schema->renameColumn('users', 'username', 'name');
		$expect='ALTER TABLE "users" RENAME COLUMN "username" TO "name"';
		$this->assertEquals($expect, $sql);
	}

	public function testDropColumn()
	{
		$sql=$this->db->schema->dropColumn('test', 'id');
		$expect='ALTER TABLE "test" DROP COLUMN "id"';
		$this->assertEquals($expect, $sql);
	}

	public function testAddForeignKey()
	{
		$sql=$this->db->schema->addForeignKey('fk_test', 'profile', 'user_id', 'users', 'id');
		$expect='ALTER TABLE "profile" ADD CONSTRAINT "fk_test" FOREIGN KEY ("user_id") REFERENCES "users" ("id")';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->addForeignKey('fk_test', 'profile', 'user_id', 'users', 'id','CASCADE','RESTRICTED');
		$expect='ALTER TABLE "profile" ADD CONSTRAINT "fk_test" FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE ON UPDATE RESTRICTED';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->addForeignKey('fk_test', 'profile', 'user_id,id', 'users', 'id,username','CASCADE','RESTRICTED');
		$expect='ALTER TABLE "profile" ADD CONSTRAINT "fk_test" FOREIGN KEY ("user_id", "id") REFERENCES "users" ("id", "username") ON DELETE CASCADE ON UPDATE RESTRICTED';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->addForeignKey('fk_test', 'profile', array('user_id','id'), 'users', array('id','username'),'CASCADE','RESTRICTED');
		$expect='ALTER TABLE "profile" ADD CONSTRAINT "fk_test" FOREIGN KEY ("user_id", "id") REFERENCES "users" ("id", "username") ON DELETE CASCADE ON UPDATE RESTRICTED';
		$this->assertEquals($expect, $sql);
	}

	public function testDropForeignKey()
	{
		$sql=$this->db->schema->dropForeignKey('fk_test', 'profile');
		$expect='ALTER TABLE "profile" DROP CONSTRAINT "fk_test"';
		$this->assertEquals($expect, $sql);
	}

	public function testCreateIndex()
	{
		$sql=$this->db->schema->createIndex('id_pk','test','id');
		$expect='CREATE INDEX "id_pk" ON "test" ("id")';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->createIndex('id_pk','test','id1,id2',true);
		$expect='ALTER TABLE ONLY "test" ADD CONSTRAINT "id_pk" UNIQUE ("id1", "id2")';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->createIndex('id_pk','test',array('id1','id2'),true);
		$expect='ALTER TABLE ONLY "test" ADD CONSTRAINT "id_pk" UNIQUE ("id1", "id2")';
		$this->assertEquals($expect, $sql);
	}

	public function testDropIndex()
	{
		$sql=$this->db->schema->dropIndex('id_pk','test');
		$expect='DROP INDEX "id_pk"';
		$this->assertEquals($expect, $sql);
	}
}
