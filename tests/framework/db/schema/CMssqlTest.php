<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.schema.mssql.CMssqlSchema');

require_once(dirname(__FILE__).'/../data/models2.php');

class MssqlUser2 extends User2
{
	public function tableName()
	{
		return '[dbo].[users]';
	}
}

/**
 * @group mssql
 */
class CMssqlTest extends CTestCase
{
	const DB_HOST='YII'; // This is the alias to MSSQL server. Defined in freetds.conf for GNU/Linux or in Client Network Utility on MS Windows.
	const DB_NAME='yii';
	const DB_USER='test';
	const DB_PASS='test';
	const DB_DSN_PREFIX='sqlsrv'; // Set this to 'mssql' or 'sqlsrv' on MS Windows or 'dblib' on GNU/Linux.

	/**
	 * @var CDbConnection
	 */
	private $db;

	public function setUp()
	{
		if(self::DB_DSN_PREFIX=='sqlsrv' && (!extension_loaded('pdo') || !extension_loaded('sqlsrv') || !extension_loaded('pdo_sqlsrv')))
			$this->markTestSkipped('PDO and SQLSRV extensions are required.');
		else if(self::DB_DSN_PREFIX!='sqlsrv' && (!extension_loaded('pdo') || !extension_loaded('pdo_dblib')))
			$this->markTestSkipped('PDO and MSSQL extensions are required.');

		if(self::DB_DSN_PREFIX=='sqlsrv')
			$dsn=self::DB_DSN_PREFIX.':Server='.self::DB_HOST.';Database='.self::DB_NAME;
		else
			$dsn=self::DB_DSN_PREFIX.':host='.self::DB_HOST.';dbname='.self::DB_NAME;

		$this->db=new CDbConnection($dsn,self::DB_USER,self::DB_PASS);
		if(self::DB_DSN_PREFIX=='sqlsrv')
			$this->db->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);
		try
		{
			$this->db->active=true;
		}
		catch(Exception $e)
		{
			$schemaFile=realpath(dirname(__FILE__).'/../data/mssql.sql');
			$this->markTestSkipped("Please read $schemaFile for details on setting up the test environment for MSSQL test case.");
		}

		$tables=array('comments','post_category','posts','categories','profiles','users','items','orders','types');
		foreach($tables as $table)
		{
			$sql=<<<EOD
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[{$table}]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
DROP TABLE [dbo].[{$table}]
EOD;
			$this->db->createCommand($sql)->execute();
		}

		$rawSqls=file_get_contents(dirname(__FILE__).'/../data/mssql.sql');

		// remove comments from SQL
		$sqls='';
		foreach(array_filter(explode("\n", $rawSqls)) as $line)
		{
			if(substr($line,0,2)=='--')
				continue;
			$sqls.=$line."\n";
		}

		// run SQL
		foreach(explode('GO',$sqls) as $sql)
		{
			if(trim($sql)!=='')
				$this->db->createCommand($sql)->execute();
		}

		CActiveRecord::$db=$this->db;
	}

	public function tearDown()
	{
		$this->db->active=false;
	}

	public function testSchema()
	{
		$schema=$this->db->schema;
		$this->assertTrue($schema instanceof CDbSchema);
		$this->assertEquals($schema->dbConnection,$this->db);
		$this->assertTrue($schema->commandBuilder instanceof CDbCommandBuilder);
		$this->assertEquals('[posts]',$schema->quoteTableName('posts'));
		$this->assertEquals('[dbo].[posts]',$schema->quoteTableName('dbo.posts'));
		$this->assertEquals('[id]',$schema->quoteColumnName('id'));
		$this->assertTrue($schema->getTable('posts') instanceof CDbTableSchema);
		$this->assertNull($schema->getTable('foo'));
	}

	public function testTable()
	{
		$table=$this->db->schema->getTable('posts');
		$this->assertTrue($table instanceof CDbTableSchema);
		$this->assertEquals('posts',$table->name);
		$this->assertEquals('dbo',$table->schemaName);
		$this->assertEquals('[dbo].[posts]',$table->rawName);
		$this->assertEquals('id',$table->primaryKey);
		$this->assertEquals(array('author_id'=>array('users','id')),$table->foreignKeys);
		$this->assertEquals('posts',$table->sequenceName);
		$this->assertEquals(5,count($table->columns));

		$this->assertTrue($table->getColumn('id') instanceof CDbColumnSchema);
		$this->assertTrue($table->getColumn('foo')===null);
		$this->assertEquals(array('id','title','create_time','author_id','content'),$table->columnNames);

		$table=$this->db->schema->getTable('orders');
		$this->assertEquals(array('key1','key2'),$table->primaryKey);

		$table=$this->db->schema->getTable('items');
		$this->assertEquals('id',$table->primaryKey);
		$this->assertEquals(array('col1'=>array('orders','key1'),'col2'=>array('orders','key2')),$table->foreignKeys);

		$table=$this->db->schema->getTable('types');
		$this->assertTrue($table instanceof CDbTableSchema);
		$this->assertEquals('types',$table->name);
		$this->assertEquals('[dbo].[types]',$table->rawName);
		$this->assertTrue($table->primaryKey===null);
		$this->assertTrue($table->foreignKeys===array());
		$this->assertTrue($table->sequenceName===null);

		$table=$this->db->schema->getTable('invalid');
		$this->assertNull($table);
	}

	public function testColumn()
	{
		$values=array(
			'name'=>array('id', 'title', 'create_time', 'author_id', 'content'),
			'rawName'=>array('[id]', '[title]', '[create_time]', '[author_id]', '[content]'),
			'defaultValue'=>array(null, null, null, null, null),
			'size'=>array(10, 128, null, 10, null),
			'precision'=>array(10, 128, null, 10, null),
			'scale'=>array(0, null, null, 0, null),
			'dbType'=>array('int','varchar','datetime','int','text'),
			'type'=>array('integer','string','string','integer','string'),
			'isPrimaryKey'=>array(true,false,false,false,false),
			'isForeignKey'=>array(false,false,false,true,false),
		);
		$this->checkColumns('posts',$values);
		$values=array(
			'name'=>array('int_col', 'int_col2', 'char_col', 'char_col2', 'char_col3', 'float_col', 'float_col2', 'blob_col', 'numeric_col', 'time', 'bool_col', 'bool_col2'),
			'rawName'=>array('[int_col]', '[int_col2]', '[char_col]', '[char_col2]', '[char_col3]', '[float_col]', '[float_col2]', '[blob_col]', '[numeric_col]', '[time]', '[bool_col]', '[bool_col2]'),
			'defaultValue'=>array(null, 1, null, "something", null, null, '1.23', null, '33.22', '2002-01-01 00:00:00', null, true),
			'size'=>array(10, 10, 100, 100, null, 24, 53, null, 5, null, null, null),
			'precision'=>array(10, 10, 100, 100, null, 24, 53, null, 5, null, null, null),
			'scale'=>array(0, 0, null, null, null, null, null, null, 2, null, null, null),
			'dbType'=>array('int','int','char','varchar','text','real','float','image','numeric','datetime','bit','bit'),
			'type'=>array('integer','integer','string','string','string','double','double','string','string','string','boolean','boolean'),
			'isPrimaryKey'=>array(false,false,false,false,false,false,false,false,false,false,false,false),
			'isForeignKey'=>array(false,false,false,false,false,false,false,false,false,false,false,false),
		);
		$this->checkColumns('types',$values);
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
				$this->assertTrue($column->$name==$value[$i], "$tableName.{$column->name}.$name is {$column->$name} ($type1), different from the expected {$value[$i]} ($type2).");
			}
		}
	}

	public function testCommandBuilder()
	{
		$schema=$this->db->schema;
		$builder=$schema->commandBuilder;
		$this->assertTrue($builder instanceof CDbCommandBuilder);
		$table=$schema->getTable('posts');

		$c=$builder->createInsertCommand($table,array('title'=>'test post','create_time'=>'2000-01-01','author_id'=>1,'content'=>'test content'));
		$this->assertEquals('INSERT INTO [dbo].[posts] ([title], [create_time], [author_id], [content]) VALUES (:yp0, :yp1, :yp2, :yp3)',$c->text);
		$c->execute();
		$this->assertEquals(6,$builder->getLastInsertId($table));
		$this->assertEquals(6, $this->db->getLastInsertID());

		$c=$builder->createCountCommand($table,new CDbCriteria);
		$this->assertEquals('SELECT COUNT(*) FROM [dbo].[posts] [t]',$c->text);
		$this->assertEquals(6,$c->queryScalar());

		$c=$builder->createDeleteCommand($table,new CDbCriteria(array(
			'condition'=>'id=:id',
			'params'=>array('id'=>6))));
		$this->assertEquals('DELETE FROM [dbo].[posts] WHERE id=:id',$c->text);
		$c->execute();
		$c=$builder->createCountCommand($table,new CDbCriteria);
		$this->assertEquals(5,$c->queryScalar());

		$c=$builder->createFindCommand($table,new CDbCriteria(array(
			'select'=>'id, title',
			'condition'=>'id=:id',
			'params'=>array('id'=>5),
			'order'=>'title',
			'limit'=>2,
			'offset'=>0)));
		$this->assertEquals('SELECT TOP 2 id, title FROM [dbo].[posts] [t] WHERE id=:id ORDER BY title',$c->text);
		$rows=$c->query()->readAll();
		$this->assertEquals(1,count($rows));
		$this->assertEquals('post 5',$rows[0]['title']);

		$c=$builder->createFindCommand($table,new CDbCriteria(array(
			'select'=>'id, title',
			'order'=>'title',
			'limit'=>2,
			'offset'=>3)));
		$this->assertEquals('SELECT * FROM (SELECT TOP 2 * FROM (SELECT TOP 5 id, title FROM [dbo].[posts] [t] ORDER BY title) as [__inner__] ORDER BY title DESC) as [__outer__] ORDER BY title ASC',$c->text);
		$rows=$c->query()->readAll();
		$this->assertEquals(2,count($rows));
		$this->assertEquals('post 4',$rows[0]['title']);

		$c=$builder->createUpdateCommand($table,array('title'=>'new post 5'),new CDbCriteria(array(
			'condition'=>'id=:id',
			'params'=>array('id'=>5))));
		$c->execute();
		$c=$builder->createFindCommand($table,new CDbCriteria(array(
			'select'=>'title',
			'condition'=>'id=:id',
			'params'=>array('id'=>5))));
		$this->assertEquals('new post 5',$c->queryScalar());

		$c=$builder->createSqlCommand('SELECT title FROM posts WHERE id=:id',array(':id'=>3));
		$this->assertEquals('post 3',$c->queryScalar());

		$c=$builder->createUpdateCounterCommand($table,array('author_id'=>-2),new CDbCriteria(array('condition'=>'id=5')));
		$this->assertEquals('UPDATE [dbo].[posts] SET [author_id]=[author_id]-2 WHERE id=5',$c->text);
		$c->execute();
		$c=$builder->createSqlCommand('SELECT author_id FROM posts WHERE id=5');
		$this->assertEquals(1,$c->queryScalar());

		// test bind by position
		$c=$builder->createFindCommand($table,new CDbCriteria(array(
			'select'=>'title',
			'condition'=>'id=?',
			'params'=>array(4))));
		$this->assertEquals('SELECT title FROM [dbo].[posts] [t] WHERE id=?',$c->text);
		$this->assertEquals('post 4',$c->queryScalar());

		// another bind by position
		$c=$builder->createUpdateCommand($table,array('title'=>'new post 4'),new CDbCriteria(array(
			'condition'=>'id=?',
			'params'=>array(4))));
		$c->execute();
		$c=$builder->createSqlCommand('SELECT title FROM posts WHERE id=4');
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
		$this->assertEquals('[dbo].[posts].[id]=1 AND (author_id>1)',$c->condition);

		$c=$builder->createPkCriteria($table,array(1,2));
		$this->assertEquals('[dbo].[posts].[id] IN (1, 2)',$c->condition);

		$c=$builder->createPkCriteria($table,array());
		$this->assertEquals('0=1',$c->condition);

		$table2=$schema->getTable('orders');
		$c=$builder->createPkCriteria($table2,array('key1'=>1,'key2'=>2),'name=\'\'');
		$this->assertEquals('[dbo].[orders].[key1]=1 AND [dbo].[orders].[key2]=2 AND (name=\'\')',$c->condition);

		$c=$builder->createPkCriteria($table2,array(array('key1'=>1,'key2'=>2),array('key1'=>3,'key2'=>4)));
		$this->assertEquals('(([dbo].[orders].[key1]=1 AND [dbo].[orders].[key2]=2) OR ([dbo].[orders].[key1]=3 AND [dbo].[orders].[key2]=4))',$c->condition);

		// createColumnCriteria
		$c=$builder->createColumnCriteria($table,array('id'=>1,'author_id'=>2),'title=\'\'');
		$this->assertEquals('[dbo].[posts].[id]=:yp0 AND [dbo].[posts].[author_id]=:yp1 AND (title=\'\')',$c->condition);

		$c=$builder->createPkCriteria($table2,array());
		$this->assertEquals('0=1',$c->condition);
	}

	public function testTransactions()
	{
		$transaction=$this->db->beginTransaction();
		$schema=$this->db->schema;
		$builder=$schema->commandBuilder;
		$table=$schema->getTable('posts');

		// Working transaction
		try
		{
			$builder->createInsertCommand($table, array('title'=>'working transaction test post 1','create_time'=>'2009-01-01','author_id'=>1,'content'=>'test content'))->execute();
			$builder->createInsertCommand($table, array('title'=>'working transaction test post 2','create_time'=>'2009-01-01','author_id'=>1,'content'=>'test content'))->execute();
			$transaction->commit();
		}
		catch (Exception $e)
		{
			$transaction->rollback();
		}
		$n=$builder->createCountCommand($table, new CDbCriteria(array('condition' => "title LIKE 'working transaction%'")))->queryScalar();
		$this->assertEquals(2, $n);

		// Failing Transaction
		$transaction=$this->db->beginTransaction();
		try
		{
			$builder->createInsertCommand($table, array('title'=>'failed transaction test post 1','create_time'=>'2009-01-01','author_id'=>1,'content'=>'test content'))->execute();
			$builder->createInsertCommand($table, array('id' => 1, 'title'=>'failed transaction test post 2','create_time'=>'2009-01-01','author_id'=>1,'content'=>'test content'))->execute();
			$transaction->commit();
		}
		catch (Exception $e)
		{
			$transaction->rollback();
		}
		$n=$builder->createCountCommand($table, new CDbCriteria(array('condition' => "title LIKE 'failed transaction%'")))->queryScalar();
		$this->assertEquals(0, $n);
	}

	public function testColumnComments()
	{
		$tables=$this->db->schema->tables;

		$usersColumns=$tables['users']->columns;
		$this->assertEquals('Name of the user', $usersColumns['username']->comment);
		$this->assertEquals('User\'s password', $usersColumns['password']->comment);
		$this->assertEquals('User\'s email', $usersColumns['email']->comment);

		$usersColumns=$tables['profiles']->columns;
		$this->assertEquals('用户名。', $usersColumns['first_name']->comment);
		$this->assertEquals('Тест Юникода', $usersColumns['id']->comment);
		$this->assertEquals('User\'s identifier', $usersColumns['user_id']->comment);
		$this->assertEmpty($usersColumns['last_name']->comment);
	}

	public function testARLastInsertId()
	{
		$user=new MssqlUser2();

		$user->username='testingUser';
		$user->password='testingPassword';
		$user->email='testing@email.com';

		$this->assertTrue($user->isNewRecord);
		$this->assertNull($user->primaryKey);
		$this->assertNull($user->id);
		$this->assertEquals(3, $this->db->createCommand('SELECT MAX(id) FROM [dbo].[users]')->queryScalar());

		$user->save();

		$this->assertFalse($user->isNewRecord);
		$this->assertEquals(4, $user->primaryKey);
		$this->assertEquals(4, $user->id);
		$this->assertEquals(4, $this->db->createCommand('SELECT MAX(id) FROM [dbo].[users]')->queryScalar());
	}

	public function testResetSequence()
	{
		$tables=$this->db->schema->tables;

		$this->db->schema->resetSequence($tables['users']);
		$this->db->createCommand()->insert('users',array('username'=>'testerX','password'=>'passwordX','email'=>'emailX@gmail.com'));
		$id=$this->db->createCommand()->select('id')->from('users')->where("[username]='testerX'")->queryScalar();
		$this->assertEquals(4,$id);

		$this->db->schema->resetSequence($tables['users'],100);
		$this->db->createCommand()->insert('users',array('username'=>'testerY','password'=>'passwordY','email'=>'emailY@gmail.com'));
		$id=$this->db->createCommand()->select('id')->from('users')->where("[username]='testerY'")->queryScalar();
		$this->assertEquals(100,$id);

		$this->db->schema->resetSequence($tables['users']);
		$this->db->createCommand()->insert('users',array('username'=>'testerZ','password'=>'passwordZ','email'=>'emailZ@gmail.com'));
		$id=$this->db->createCommand()->select('id')->from('users')->where("[username]='testerZ'")->queryScalar();
		$this->assertEquals(101,$id);
	}
}
