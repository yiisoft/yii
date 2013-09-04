<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.schema.mysql.CMysqlSchema');


class CMysql2Test extends CTestCase
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
		try
		{
			$this->db->active=true;
		}
		catch(Exception $e)
		{
			$schemaFile=realpath(dirname(__FILE__).'/../data/mysql.sql');
			$this->markTestSkipped("Please read $schemaFile for details on setting up the test environment for MySQL test case.");
		}

		$tables=array('comments','post_category','posts','categories','profiles','users','items','orders','types');
		foreach($tables as $table)
			$this->db->createCommand("DROP TABLE IF EXISTS $table CASCADE")->execute();

		$sqls=file_get_contents(dirname(__FILE__).'/../data/mysql.sql');
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

	public function testCreateTable()
	{
		$sql=$this->db->schema->createTable('test',array(
			'id'=>'pk',
			'name'=>'string not null',
			'desc'=>'text',
			'primary key (id, name)',
		),'Engine=InnoDB');
		$expect="CREATE TABLE `test` (\n"
			. "\t`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,\n"
			. "\t`name` varchar(255) not null,\n"
			. "\t`desc` text,\n"
			. "\tprimary key (id, name)\n"
			. ") Engine=InnoDB";
		$this->assertEquals($expect, $sql);
	}

	public function testRenameTable()
	{
		$sql=$this->db->schema->renameTable('test', 'test2');
		$expect='RENAME TABLE `test` TO `test2`';
		$this->assertEquals($expect, $sql);
	}

	public function testDropTable()
	{
		$sql=$this->db->schema->dropTable('test');
		$expect='DROP TABLE `test`';
		$this->assertEquals($expect, $sql);
	}

	public function testAddColumn()
	{
		$sql=$this->db->schema->addColumn('test', 'id', 'integer');
		$expect='ALTER TABLE `test` ADD `id` int(11)';
		$this->assertEquals($expect, $sql);
	}

	public function testAlterColumn()
	{
		$sql=$this->db->schema->alterColumn('test', 'id', 'boolean');
		$expect='ALTER TABLE `test` CHANGE `id` `id` tinyint(1)';
		$this->assertEquals($expect, $sql);
	}

	public function testRenameColumn()
	{
		$sql=$this->db->schema->renameColumn('users', 'username', 'name');
		$expect="ALTER TABLE `users` CHANGE `username` `name` varchar(128) NOT NULL COMMENT 'Name of the user'";
		$this->assertEquals($expect, $sql);
	}

	public function testDropColumn()
	{
		$sql=$this->db->schema->dropColumn('test', 'id');
		$expect='ALTER TABLE `test` DROP COLUMN `id`';
		$this->assertEquals($expect, $sql);
	}

	public function testAddForeignKey()
	{
		$sql=$this->db->schema->addForeignKey('fk_test', 'profile', 'user_id', 'users', 'id');
		$expect='ALTER TABLE `profile` ADD CONSTRAINT `fk_test` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->addForeignKey('fk_test', 'profile', 'user_id', 'users', 'id','CASCADE','RESTRICTED');
		$expect='ALTER TABLE `profile` ADD CONSTRAINT `fk_test` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICTED';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->addForeignKey('fk_test', 'profile', 'user_id,id', 'users', 'id,username','CASCADE','RESTRICTED');
		$expect='ALTER TABLE `profile` ADD CONSTRAINT `fk_test` FOREIGN KEY (`user_id`, `id`) REFERENCES `users` (`id`, `username`) ON DELETE CASCADE ON UPDATE RESTRICTED';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->addForeignKey('fk_test', 'profile', array('user_id', 'id'), 'users', array('id','username'),'CASCADE','RESTRICTED');
		$expect='ALTER TABLE `profile` ADD CONSTRAINT `fk_test` FOREIGN KEY (`user_id`, `id`) REFERENCES `users` (`id`, `username`) ON DELETE CASCADE ON UPDATE RESTRICTED';
		$this->assertEquals($expect, $sql);
	}

	public function testDropForeignKey()
	{
		$sql=$this->db->schema->dropForeignKey('fk_test', 'profile');
		$expect='ALTER TABLE `profile` DROP FOREIGN KEY `fk_test`';
		$this->assertEquals($expect, $sql);
	}

	public function testCreateIndex()
	{
		$sql=$this->db->schema->createIndex('id_pk','test','id');
		$expect='CREATE INDEX `id_pk` ON `test` (`id`)';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->createIndex('id_pk','test','id1,id2',true);
		$expect='CREATE UNIQUE INDEX `id_pk` ON `test` (`id1`, `id2`)';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->createIndex('id_pk','test',array('id1','id2'),true);
		$expect='CREATE UNIQUE INDEX `id_pk` ON `test` (`id1`, `id2`)';
		$this->assertEquals($expect, $sql);
	}

	public function testDropIndex()
	{
		$sql=$this->db->schema->dropIndex('id_pk','test');
		$expect='DROP INDEX `id_pk` ON `test`';
		$this->assertEquals($expect, $sql);
	}

	public function testAddPrimaryKey()
	{
		$sql=$this->db->schema->addPrimaryKey('this-string-is-ignored','table','id');
		$expect='ALTER TABLE `table` ADD PRIMARY KEY (`id` )';
		$this->assertEquals($expect, $sql);

		$sql=$this->db->schema->addPrimaryKey('this-string-is-ignored','table',array('id1','id2'));
		$expect='ALTER TABLE `table` ADD PRIMARY KEY (`id1`, `id2` )';
		$this->assertEquals($expect, $sql);
	}
}
