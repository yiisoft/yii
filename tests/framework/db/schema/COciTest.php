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
	const DB_PASS='test';

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

		$tables=array('comments', 'post_category', 'posts', 'categories', 'profiles', 'users', 'items', 'orders', 'types');

		// delete existing sequences
		foreach($tables as $table)
		{
			if($table==='post_category' || $table==='orders' || $table==='types')
				continue;
			$sequence=$table.'_id_sequence';
			$sql=<<<EOD
DECLARE c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_sequences WHERE sequence_name = '{$sequence}';
	IF c = 1 THEN EXECUTE IMMEDIATE 'DROP SEQUENCE "{$sequence}"'; END IF;
END;
EOD;
			$this->db->createCommand($sql)->execute();
		}

		// delete existing tables
		foreach($tables as $table)
		{
			$sql=<<<EOD
DECLARE c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = '{$table}';
	IF c = 1 THEN EXECUTE IMMEDIATE 'DROP TABLE "{$table}"'; END IF;
END;
EOD;
			$this->db->createCommand($sql)->execute();
		}

		$sqls='';
		foreach(explode("\n", file_get_contents($schemaFilePath)) as $line)
		{
			if(substr($line, 0, 2)==='--')
				continue;
			$sqls.=$line."\n";
		}
		foreach(array_filter(explode("\n\n", $sqls)) as $sql)
		{
			if(trim($sql)!=='')
			{
				if(mb_substr($sql, -4)!=='END;') // do not remove semicolons after BEGIN END blocks
					$sql=rtrim($sql, ';');
				$this->db->createCommand($sql)->execute();
			}
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

	public function testTable()
	{
		$table=$this->db->schema->getTable('posts');
		$this->assertInstanceOf('CDbTableSchema', $table);
		$this->assertEquals('posts', $table->name);
		$this->assertEquals('"posts"', $table->rawName);
		$this->assertEquals('id', $table->primaryKey);
		$this->assertEquals(array('author_id'=>array('users', 'id')), $table->foreignKeys);
		$this->assertEmpty($table->sequenceName);
		$this->assertCount(5, $table->columns);

		$this->assertInstanceOf('CDbColumnSchema', $table->getColumn('id'));
		$this->assertNull($table->getColumn('foo'));
		$this->assertEquals(array('id', 'title', 'create_time', 'author_id', 'content'), $table->columnNames);

		$table=$this->db->schema->getTable('orders');
		$this->assertEquals(array('key1', 'key2'), $table->primaryKey);

		$table=$this->db->schema->getTable('items');
		$this->assertEquals('id', $table->primaryKey);
		$this->assertEquals(array('col1'=>array('orders', 'key1'), 'col2'=>array('orders', 'key2')), $table->foreignKeys);

		$table=$this->db->schema->getTable('types');
		$this->assertInstanceOf('CDbTableSchema', $table);
		$this->assertEquals('types', $table->name);
		$this->assertEquals('"types"', $table->rawName);
		$this->assertNull($table->primaryKey);
		$this->assertEmpty($table->foreignKeys);
		$this->assertNull($table->sequenceName);

		$table=$this->db->schema->getTable('invalid');
		$this->assertNull($table);
	}

	public function testColumn()
	{
		$values=array(
			'name'=>array('id', 'title', 'create_time', 'author_id', 'content'),
			'rawName'=>array('"id"', '"title"', '"create_time"', '"author_id"', '"content"'),
			'defaultValue'=>array(null, null, null, null, null),
			'size'=>array(null, 512, 6, null, 4000),
			'precision'=>array(null, 512, 6, null, 4000),
			'scale'=>array(null, null, null, null, null),
			'dbType'=>array('NUMBER','VARCHAR2(512)','TIMESTAMP(6)(11)','NUMBER','CLOB(4000)'),
			'type'=>array('double','string','string','double','string'),
			'isPrimaryKey'=>array(true,false,false,false,false),
			'isForeignKey'=>array(false,false,false,true,false),
		);
		$this->checkColumns('posts',$values);
	}

	protected function checkColumns($tableName,$values)
	{
		$table=$this->db->schema->getTable($tableName);
		foreach($values as $name=>$value)
		{
			foreach(array_values($table->columns) as $i=>$column)
			{
				$type1=gettype($column->$name);
				$type2=gettype($value[$i]);
				$this->assertTrue($column->$name===$value[$i], "$tableName.{$column->name}.$name is {$column->$name} ($type1), different from the expected {$value[$i]} ($type2).");
			}
		}
	}

	public function testCommandBuilder()
	{
		$schema=$this->db->schema;
		$builder=$schema->commandBuilder;
		$this->assertTrue($builder instanceof CDbCommandBuilder);
		$table=$schema->getTable('posts');

		$c=$builder->createInsertCommand($table,array('title'=>'test post','create_time'=>new CDbExpression('TO_TIMESTAMP(:ts_value, \'YYYY-MM-DD\')', array(':ts_value'=>'2000-01-01')),'author_id'=>1,'content'=>'test content'));
		$this->assertEquals('INSERT INTO "posts" ("title", "create_time", "author_id", "content") VALUES (:yp0, TO_TIMESTAMP(:ts_value, \'YYYY-MM-DD\'), :yp1, :yp2) RETURNING "id" INTO :RETURN_ID',$c->text);
		$c->execute();
		$this->assertEquals(6,$builder->getLastInsertId($table));

		$c=$builder->createCountCommand($table,new CDbCriteria);
		$this->assertEquals('SELECT COUNT(*) FROM "posts" "t"',$c->text);
		$this->assertEquals(6,$c->queryScalar());

		$c=$builder->createDeleteCommand($table,new CDbCriteria(array(
			'condition'=>'"id"=:id',
			'params'=>array('id'=>6))));
		$this->assertEquals('DELETE FROM "posts" WHERE "id"=:id',$c->text);
		$c->execute();
		$c=$builder->createCountCommand($table,new CDbCriteria);
		$this->assertEquals(5,$c->queryScalar());

		$c=$builder->createFindCommand($table,new CDbCriteria(array(
			'select'=>'"id", "title"',
			'condition'=>'"id"=:id',
			'params'=>array(':id'=>5),
			'order'=>'"title"',
			'limit'=>2,
			'offset'=>0)));
		$this->assertEquals('WITH USER_SQL AS (SELECT "id", "title" FROM "posts" "t" WHERE "id"=:id ORDER BY "title"),
	PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
 WHERE rownum <= 2', $c->text);
		$rows=$c->query()->readAll();
		$this->assertEquals(1,count($rows));
		$this->assertEquals('post 5',$rows[0]['title']);

		$c=$builder->createUpdateCommand($table,array('title'=>'new post 5'),new CDbCriteria(array(
			'condition'=>'"id"=:id',
			'params'=>array('id'=>5))));
		$c->execute();
		$c=$builder->createFindCommand($table,new CDbCriteria(array(
			'select'=>'"title"',
			'condition'=>'"id"=:id',
			'params'=>array('id'=>5))));
		$this->assertEquals('new post 5',$c->queryScalar());

		$c=$builder->createSqlCommand('SELECT "title" FROM "posts" WHERE "id"=:id',array(':id'=>3));
		$this->assertEquals('post 3',$c->queryScalar());

		$c=$builder->createUpdateCounterCommand($table,array('author_id'=>-1),new CDbCriteria(array('condition'=>'"id"=5')));
		$this->assertEquals('UPDATE "posts" SET "author_id"="author_id"-1 WHERE "id"=5',$c->text);
		$c->execute();
		$c=$builder->createSqlCommand('SELECT "author_id" FROM "posts" WHERE "id"=5');
		$this->assertEquals(2,$c->queryScalar());

		// Oracle does not support UPDATE with JOINs so there are no tests of them

		// test bind by position
		$c=$builder->createFindCommand($table,new CDbCriteria(array(
			'select'=>'"title"',
			'condition'=>'"id"=?',
			'params'=>array(4))));
		$this->assertEquals('SELECT "title" FROM "posts" "t" WHERE "id"=?',$c->text);
		$this->assertEquals('post 4',$c->queryScalar());

		// another bind by position
		$c=$builder->createUpdateCommand($table,array('title'=>'new post 4'),new CDbCriteria(array(
			'condition'=>'"id"=?',
			'params'=>array(4))));
		$c->execute();
		$c=$builder->createSqlCommand('SELECT "title" FROM "posts" WHERE "id"=4');
		$this->assertEquals('new post 4',$c->queryScalar());

		// testCreateCriteria
		$c=$builder->createCriteria('column=:value',array(':value'=>'value'));
		$this->assertEquals('column=:value',$c->condition);
		$this->assertEquals(array(':value'=>'value'),$c->params);

		$c=$builder->createCriteria(array('condition'=>'column=:value','params'=>array(':value'=>'value')));
		$this->assertEquals('column=:value',$c->condition);
		$this->assertEquals(array(':value'=>'value'),$c->params);

		$c2=$builder->createCriteria($c);
		$this->assertTrue($c2!==$c);
		$this->assertEquals('column=:value',$c2->condition);
		$this->assertEquals(array(':value'=>'value'),$c2->params);

		// testCreatePkCriteria
		$c=$builder->createPkCriteria($table,1,'author_id>1');
		$this->assertEquals('"posts"."id"=1 AND (author_id>1)',$c->condition);

		$c=$builder->createPkCriteria($table,array(1,2));
		$this->assertEquals('"posts"."id" IN (1, 2)',$c->condition);

		$c=$builder->createPkCriteria($table,array());
		$this->assertEquals('0=1',$c->condition);

		$table2=$schema->getTable('orders');
		$c=$builder->createPkCriteria($table2,array('key1'=>1,'key2'=>2),'name=""');
		$this->assertEquals('"orders"."key1"=1 AND "orders"."key2"=2 AND (name="")',$c->condition);

		$c=$builder->createPkCriteria($table2,array(array('key1'=>1,'key2'=>2),array('key1'=>3,'key2'=>4)));
		$this->assertEquals('("orders"."key1", "orders"."key2") IN ((1, 2), (3, 4))',$c->condition);

		// createColumnCriteria
		$c=$builder->createColumnCriteria($table,array('id'=>1,'author_id'=>2),'title=""');
		$this->assertEquals('"posts"."id"=:yp0 AND "posts"."author_id"=:yp1 AND (title="")',$c->condition);

		$c=$builder->createPkCriteria($table2,array());
		$this->assertEquals('0=1',$c->condition);
	}

	public function testResetSequence()
	{
		// we're assuming in this test that COciSchema::resetSequence() is not implemented
		// empty CDbSchema::resetSequence() being used

		$max=$this->db->createCommand('SELECT MAX("id") FROM "users"')->queryScalar();
		$this->db->createCommand('DELETE FROM "users"')->execute();
		$this->db->createCommand('INSERT INTO "users" ("username", "password", "email") VALUES (\'user4\', \'pass4\', \'email4\')')->execute();
		$max2=$this->db->createCommand('SELECT MAX("id") FROM "users"')->queryScalar();
		$this->assertEquals($max+1, $max2);

		$userTable=$this->db->schema->getTable('users');

		$this->db->createCommand('DELETE FROM "users"')->execute();
		$this->db->schema->resetSequence($userTable);
		$this->db->createCommand('INSERT INTO "users" ("username", "password", "email") VALUES (\'user4\', \'pass4\', \'email4\')')->execute();
		$max=$this->db->createCommand('SELECT MAX("id") FROM "users"')->queryScalar();
		$this->assertEquals(6, $max);
		$this->db->createCommand('INSERT INTO "users" ("username", "password", "email") VALUES (\'user4\', \'pass4\', \'email4\')')->execute();
		$max=$this->db->createCommand('SELECT MAX("id") FROM "users"')->queryScalar();
		$this->assertEquals(7, $max);

		$this->db->createCommand('DELETE FROM "users"')->execute();
		$this->db->schema->resetSequence($userTable, 10);
		$this->db->createCommand('INSERT INTO "users" ("username", "password", "email") VALUES (\'user4\', \'pass4\', \'email4\')')->execute();
		$max=$this->db->createCommand('SELECT MAX("id") FROM "users"')->queryScalar();
		$this->assertEquals(8, $max);
		$this->db->createCommand('INSERT INTO "users" ("username", "password", "email") VALUES (\'user4\', \'pass4\', \'email4\')')->execute();
		$max=$this->db->createCommand('SELECT MAX("id") FROM "users"')->queryScalar();
		$this->assertEquals(9, $max);
	}

	public function testColumnComments()
	{
		$tables=$this->db->schema->tables;

		// specified comments
		$usersColumns=$tables['users']->columns;
		$this->assertEquals('User\'s entry primary key', $usersColumns['id']->comment);
		$this->assertEquals('Имя пользователя', $usersColumns['username']->comment);
		$this->assertEquals('用户的密码', $usersColumns['password']->comment);
		$this->assertEquals('דוא"ל של המשתמש', $usersColumns['email']->comment);

		// empty comments
		$postsColumns=$tables['posts']->columns;
		$this->assertEmpty($postsColumns['id']->comment);
		$this->assertEmpty($postsColumns['title']->comment);
		$this->assertEmpty($postsColumns['create_time']->comment);
		$this->assertEmpty($postsColumns['author_id']->comment);
		$this->assertEmpty($postsColumns['content']->comment);
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
				'float_col'=>1,
				'bool_col'=>true,
			),
			array(
				'int_col'=>2,
				'char_col'=>'char_col_2',
				'float_col'=>2,
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
