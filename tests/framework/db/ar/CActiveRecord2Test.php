<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');
require_once(dirname(__FILE__).'/../data/models2.php');

class CActiveRecord2Test extends CTestCase
{
	/**
	 * @var CDbConnection
	 */
	private $db;

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_pgsql'))
			$this->markTestSkipped('PDO and PostgreSQL extensions are required.');

		$this->db=new CDbConnection('pgsql:host=127.0.0.1;dbname=yii','test','test');
		try
		{
			$this->db->active=true;
		}
		catch(Exception $e)
		{
			$schemaFile=realpath(dirname(__FILE__).'/../data/postgres.sql');
			$this->markTestSkipped("Please read $schemaFile for details on setting up the test environment for PostgreSQL test case.");
		}

		try	{ $this->db->createCommand('DROP SCHEMA test CASCADE')->execute(); } catch(Exception $e) { }
		try	{ $this->db->createCommand('DROP TABLE yii_types CASCADE')->execute(); } catch(Exception $e) { }

		$sqls=file_get_contents(dirname(__FILE__).'/../data/postgres.sql');
		foreach(explode(';',$sqls) as $sql)
		{
			if(trim($sql)!=='')
				$this->db->createCommand($sql)->execute();
		}
		$this->db->active=false;

		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'db'=>array(
					'class'=>'system.db.CDbConnection',
					'connectionString'=>'pgsql:host=127.0.0.1;dbname=yii',
					'username'=>'test',
					'password'=>'test',
				),
			),
		);
		$app=new TestApplication($config);
		$app->db->active=true;
		CActiveRecord::$db=$this->db=$app->db;
	}

	public function tearDown()
	{
		$this->db->active=false;
	}

	public function testModel()
	{
		$model=Post2::model();
		$this->assertTrue($model instanceof Post2);
		$this->assertTrue($model->dbConnection===$this->db);
		$this->assertTrue($model->dbConnection->active);
		$this->assertEquals('test.posts',$model->tableName());
		$this->assertEquals('id',$model->tableSchema->primaryKey);
		$this->assertEquals('test.posts_id_seq',$model->tableSchema->sequenceName);
		$this->assertEquals(array(),$model->attributeLabels());
		$this->assertEquals('Id',$model->getAttributeLabel('id'));
		$this->assertEquals('Author Id',$model->getAttributeLabel('author_id'));
		$this->assertTrue($model->getActiveRelation('author') instanceof CBelongsToRelation);
		$this->assertTrue($model->tableSchema instanceof CDbTableSchema);
		$this->assertTrue($model->commandBuilder instanceof CDbCommandBuilder);
		$this->assertTrue($model->hasAttribute('id'));
		$this->assertFalse($model->hasAttribute('comments'));
		$this->assertFalse($model->hasAttribute('foo'));
		$this->assertEquals(array(),$model->getAttributes(false));

		$post=new Post2;
		$this->assertNull($post->id);
		$this->assertNull($post->title);
		$post->setAttributes(array('id'=>3,'title'=>'test title'));
		$this->assertNull($post->id);
		$this->assertEquals('test title',$post->title);
	}

	public function testFind()
	{
		// test find() with various parameters
		$post=Post2::model()->find();
		$this->assertTrue($post instanceof Post2);
		$this->assertEquals(1,$post->id);

		$post=Post2::model()->find('id=5');
		$this->assertTrue($post instanceof Post2);
		$this->assertEquals(5,$post->id);

		$post=Post2::model()->find('id=:id',array(':id'=>2));
		$this->assertTrue($post instanceof Post2);
		$this->assertEquals(2,$post->id);

		$post=Post2::model()->find(array('condition'=>'id=:id','params'=>array(':id'=>3)));
		$this->assertTrue($post instanceof Post2);
		$this->assertEquals(3,$post->id);

		// test find() without result
		$post=Post2::model()->find('id=6');
		$this->assertNull($post);

		// test findAll() with various parameters
		$posts=Post2::model()->findAll();
		$this->assertEquals(5,count($posts));
		$this->assertTrue($posts[3] instanceof Post2);
		$this->assertEquals(4,$posts[3]->id);

		$posts=Post2::model()->findAll(new CDbCriteria(array('limit'=>3,'offset'=>1)));
		$this->assertEquals(3,count($posts));
		$this->assertTrue($posts[2] instanceof Post2);
		$this->assertEquals(4,$posts[2]->id);

		// test findAll() without result
		$posts=Post2::model()->findAll('id=6');
		$this->assertTrue($posts===array());

		// test findByPk
		$post=Post2::model()->findByPk(2);
		$this->assertEquals(2,$post->id);

		$post=Post2::model()->findByPk(array(3,2));
		$this->assertEquals(2,$post->id);

		$post=Post2::model()->findByPk(array());
		$this->assertNull($post);

		$post=Post2::model()->findByPk(6);
		$this->assertNull($post);

		// test findAllByPk
		$posts=Post2::model()->findAllByPk(2);
		$this->assertEquals(1,count($posts));
		$this->assertEquals(2,$posts[0]->id);

		$posts=Post2::model()->findAllByPk(array(4,3,2),'id<4');
		$this->assertEquals(2,count($posts));
		$this->assertEquals(2,$posts[0]->id);
		$this->assertEquals(3,$posts[1]->id);

		$posts=Post2::model()->findAllByPk(array());
		$this->assertTrue($posts===array());

		// test findByAttributes
		$post=Post2::model()->findByAttributes(array('author_id'=>2),array('order'=>'id DESC'));
		$this->assertEquals(4,$post->id);

		// test findAllByAttributes
		$posts=Post2::model()->findAllByAttributes(array('author_id'=>2));
		$this->assertEquals(3,count($posts));

		// test findBySql
		$post=Post2::model()->findBySql('select * from test.posts where id=:id',array(':id'=>2));
		$this->assertEquals(2,$post->id);

		// test findAllBySql
		$posts=Post2::model()->findAllBySql('select * from test.posts where id>:id',array(':id'=>2));
		$this->assertEquals(3,count($posts));

		// test count
		$this->assertEquals(5,Post2::model()->count());
		$this->assertEquals(3,Post2::model()->count(array('condition'=>'id>2')));

		// test countBySql
		$this->assertEquals(1,Post2::model()->countBySql('select id from test.posts limit 1'));

		// test exists
		$this->assertTrue(Post2::model()->exists('id=:id',array(':id'=>1)));
		$this->assertFalse(Post2::model()->exists('id=:id',array(':id'=>6)));
	}

	public function testInsert()
	{
		$post=new Post2;
		$this->assertEquals(array(),$post->getAttributes(false));
		$post->title='test post 1';
		$post->create_time='2004-10-19 10:23:54';
		$post->author_id=1;
		$post->content='test post content 1';
		$this->assertTrue($post->isNewRecord);
		$this->assertNull($post->id);
		$this->assertTrue($post->save());
		$this->assertEquals(array(
			'id'=>6,
			'title'=>'test post 1',
			'create_time'=>$post->create_time,
			'author_id'=>1,
			'content'=>'test post content 1'),$post->getAttributes());
		$this->assertFalse($post->isNewRecord);
		$this->assertEquals($post->getAttributes(false),Post2::model()->findByPk($post->id)->getAttributes(false));
	}

	public function testUpdate()
	{
		// test save
		$post=Post2::model()->findByPk(1);
		$this->assertFalse($post->isNewRecord);
		$this->assertEquals('post 1',$post->title);
		$post->title='test post 1';
		$this->assertTrue($post->save());
		$this->assertFalse($post->isNewRecord);
		$this->assertEquals('test post 1',$post->title);
		$this->assertEquals('test post 1',Post2::model()->findByPk(1)->title);

		// test updateByPk
		$this->assertEquals(2,Post2::model()->updateByPk(array(4,5),array('title'=>'test post')));
		$this->assertEquals('post 2',Post2::model()->findByPk(2)->title);
		$this->assertEquals('test post',Post2::model()->findByPk(4)->title);
		$this->assertEquals('test post',Post2::model()->findByPk(5)->title);

		// test updateAll
		$this->assertEquals(1,Post2::model()->updateAll(array('title'=>'test post'),'id=1'));
		$this->assertEquals('test post',Post2::model()->findByPk(1)->title);

		// test updateCounters
		$this->assertEquals(2,Post2::model()->findByPk(2)->author_id);
		$this->assertEquals(2,Post2::model()->findByPk(3)->author_id);
		$this->assertEquals(2,Post2::model()->findByPk(4)->author_id);
		$this->assertEquals(3,Post2::model()->updateCounters(array('author_id'=>-1),'id>2'));
		$this->assertEquals(2,Post2::model()->findByPk(2)->author_id);
		$this->assertEquals(1,Post2::model()->findByPk(3)->author_id);
		$this->assertEquals(1,Post2::model()->findByPk(4)->author_id);
		$this->assertEquals(2,Post2::model()->findByPk(5)->author_id);
	}

	public function testDelete()
	{
		$post=Post2::model()->findByPk(1);
		$this->assertTrue($post->delete());
		$this->assertNull(Post2::model()->findByPk(1));

		$this->assertTrue(Post2::model()->findByPk(2) instanceof Post2);
		$this->assertTrue(Post2::model()->findByPk(3) instanceof Post2);
		$this->assertEquals(2,Post2::model()->deleteByPk(array(2,3)));
		$this->assertNull(Post2::model()->findByPk(2));
		$this->assertNull(Post2::model()->findByPk(3));

		$this->assertTrue(Post2::model()->findByPk(5) instanceof Post2);
		$this->assertEquals(1,Post2::model()->deleteAll('id=5'));
		$this->assertNull(Post2::model()->findByPk(5));
	}

	public function testRefresh()
	{
		$post=Post2::model()->findByPk(1);
		$post2=Post2::model()->findByPk(1);
		$post2->title='new post';
		$post2->save();
		$this->assertEquals('post 1',$post->title);
		$this->assertTrue($post->refresh());
		$this->assertEquals('new post',$post->title);
	}

	public function testEquals()
	{
		$post=Post2::model()->findByPk(1);
		$post2=Post2::model()->findByPk(1);
		$post3=Post2::model()->findByPk(3);
		$this->assertEquals(1,$post->primaryKey);
		$this->assertTrue($post->equals($post2));
		$this->assertTrue($post2->equals($post));
		$this->assertFalse($post->equals($post3));
		$this->assertFalse($post3->equals($post));
	}

	public function testValidation()
	{
		$user=new User2;
		$user->password='passtest';
		$this->assertFalse($user->hasErrors());
		$this->assertEquals(array(),$user->errors);
		$this->assertEquals(array(),$user->getErrors('username'));
		$this->assertFalse($user->save());
		$this->assertNull($user->id);
		$this->assertTrue($user->isNewRecord);
		$this->assertTrue($user->hasErrors());
		$this->assertTrue($user->hasErrors('username'));
		$this->assertTrue($user->hasErrors('email'));
		$this->assertFalse($user->hasErrors('password'));
		$this->assertEquals(1,count($user->getErrors('username')));
		$this->assertEquals(1,count($user->getErrors('email')));
		$this->assertEquals(2,count($user->errors));

		$user->clearErrors();
		$this->assertFalse($user->hasErrors());
		$this->assertEquals(array(),$user->errors);
	}

	public function testCompositeKey()
	{
		$order=new Order2;
		$this->assertEquals(array('key1','key2'),$order->tableSchema->primaryKey);
		$order=Order2::model()->findByPk(array('key1'=>2,'key2'=>1));
		$this->assertEquals('order 21',$order->name);
		$orders=Order2::model()->findAllByPk(array(array('key1'=>2,'key2'=>1),array('key1'=>1,'key2'=>3)));
		$this->assertEquals('order 13',$orders[0]->name);
		$this->assertEquals('order 21',$orders[1]->name);
	}

	public function testDefault()
	{
		$type=new ComplexType2;
		$this->assertEquals(1,$type->int_col2);
		$this->assertEquals('something',$type->char_col2);
		$this->assertEquals(1.23,$type->real_col);
		$this->assertNull($type->numeric_col);
		$this->assertNull($type->time);
		$this->assertNull($type->bool_col);
		$this->assertTrue($type->bool_col2);
	}

	public function testPublicAttribute()
	{
		$post=new PostExt2;
		$this->assertEquals(array('id'=>null,'title'=>'default title'),$post->getAttributes(false));
		$post=Post2::model()->findByPk(1);
		$this->assertEquals(array(
			'id'=>1,
			'title'=>'post 1',
			'create_time'=>'2004-10-19 10:23:54',
			'author_id'=>1,
			'content'=>'content 1'),$post->getAttributes(false));

		$post=new PostExt2;
		$post->title='test post';
		$post->create_time='2004-10-19 10:23:53';
		$post->author_id=1;
		$post->content='test';
		$post->save();
		$this->assertEquals(array(
			'id'=>6,
			'title'=>'test post',
			'create_time'=>'2004-10-19 10:23:53',
			'author_id'=>1,
			'content'=>'test'),$post->getAttributes(false));
	}

	public function testLazyRelation()
	{
		// test belongsTo
		$post=Post2::model()->findByPk(2);
		$this->assertTrue($post->author instanceof User2);
		$this->assertEquals(array(
			'id'=>2,
			'username'=>'user2',
			'password'=>'pass2',
			'email'=>'email2'),$post->author->getAttributes(false));

		// test hasOne
		$post=Post2::model()->findByPk(2);
		$this->assertTrue($post->firstComment instanceof Comment2);
		$this->assertEquals(array(
			'id'=>4,
			'content'=>'comment 4',
			'post_id'=>2,
			'author_id'=>2),$post->firstComment->getAttributes(false));
		$post=Post2::model()->findByPk(4);
		$this->assertNull($post->firstComment);

		// test hasMany
		$post=Post2::model()->findByPk(2);
		$this->assertEquals(2,count($post->comments));
		$this->assertEquals(array(
			'id'=>5,
			'content'=>'comment 5',
			'post_id'=>2,
			'author_id'=>2),$post->comments[0]->getAttributes(false));
		$this->assertEquals(array(
			'id'=>4,
			'content'=>'comment 4',
			'post_id'=>2,
			'author_id'=>2),$post->comments[1]->getAttributes(false));
		$post=Post2::model()->findByPk(4);
		$this->assertEquals(array(),$post->comments);

		// test manyMany
		$post=Post2::model()->findByPk(2);
		$this->assertEquals(2,count($post->categories));

		// TODO: when joining, need to replace both placeholders for the two joinin tables
		$this->assertEquals(array(
			'id'=>4,
			'name'=>'cat 4',
			'parent_id'=>1),$post->categories[0]->getAttributes(false));
		$this->assertEquals(array(
			'id'=>1,
			'name'=>'cat 1',
			'parent_id'=>null),$post->categories[1]->getAttributes(false));


		$post=Post2::model()->findByPk(4);
		$this->assertEquals(array(),$post->categories);

		// test self join
		$category=Category2::model()->findByPk(5);
		$this->assertEquals(array(),$category->posts);
		$this->assertEquals(2,count($category->children));
		$this->assertEquals(array(
			'id'=>6,
			'name'=>'cat 6',
			'parent_id'=>5),$category->children[0]->getAttributes(false));
		$this->assertEquals(array(
			'id'=>7,
			'name'=>'cat 7',
			'parent_id'=>5),$category->children[1]->getAttributes(false));
		$this->assertTrue($category->parent instanceof Category2);
		$this->assertEquals(array(
			'id'=>1,
			'name'=>'cat 1',
			'parent_id'=>null),$category->parent->getAttributes(false));

		$category=Category2::model()->findByPk(2);
		$this->assertEquals(1,count($category->posts));
		$this->assertEquals(array(),$category->children);
		$this->assertNull($category->parent);

		// test composite key
		$order=Order2::model()->findByPk(array('key1'=>1,'key2'=>2));
		$this->assertEquals(2,count($order->items));
		$order=Order2::model()->findByPk(array('key1'=>2,'key2'=>1));
		$this->assertEquals(0,count($order->items));
		$item=Item2::model()->findByPk(4);
		$this->assertTrue($item->order instanceof Order2);
		$this->assertEquals(array(
			'key1'=>2,
			'key2'=>2,
			'name'=>'order 22'),$item->order->getAttributes(false));
	}

	public function testEagerRelation()
	{
		$post=Post2::model()->with('author','firstComment','comments','categories')->findByPk(2);
		$this->assertEquals(array(
			'id'=>2,
			'username'=>'user2',
			'password'=>'pass2',
			'email'=>'email2'),$post->author->getAttributes(false));
		$this->assertTrue($post->firstComment instanceof Comment2);
		$this->assertEquals(array(
			'id'=>4,
			'content'=>'comment 4',
			'post_id'=>2,
			'author_id'=>2),$post->firstComment->getAttributes(false));
		$this->assertEquals(2,count($post->comments));
		$this->assertEquals(array(
			'id'=>5,
			'content'=>'comment 5',
			'post_id'=>2,
			'author_id'=>2),$post->comments[0]->getAttributes(false));
		$this->assertEquals(array(
			'id'=>4,
			'content'=>'comment 4',
			'post_id'=>2,
			'author_id'=>2),$post->comments[1]->getAttributes(false));
		$this->assertEquals(2,count($post->categories));

		$this->assertEquals(array(
			'id'=>4,
			'name'=>'cat 4',
			'parent_id'=>1),$post->categories[0]->getAttributes(false));
		$this->assertEquals(array(
			'id'=>1,
			'name'=>'cat 1',
			'parent_id'=>null),$post->categories[1]->getAttributes(false));

		$post=Post2::model()->with('author','firstComment','comments','categories')->findByPk(4);
		$this->assertEquals(array(
			'id'=>2,
			'username'=>'user2',
			'password'=>'pass2',
			'email'=>'email2'),$post->author->getAttributes(false));
		$this->assertNull($post->firstComment);
		$this->assertEquals(array(),$post->comments);
		$this->assertEquals(array(),$post->categories);
	}

	public function testLazyRecursiveRelation()
	{
		$post=PostExt2::model()->findByPk(2);
		$this->assertEquals(2,count($post->comments));
		$this->assertTrue($post->comments[0]->post instanceof Post2);
		$this->assertTrue($post->comments[1]->post instanceof Post2);
		$this->assertTrue($post->comments[0]->author instanceof User2);
		$this->assertTrue($post->comments[1]->author instanceof User2);
		$this->assertEquals(3,count($post->comments[0]->author->posts));
		$this->assertEquals(3,count($post->comments[1]->author->posts));
		$this->assertTrue($post->comments[0]->author->posts[1]->author instanceof User2);

		// test self join
		$category=Category2::model()->findByPk(1);
		$this->assertEquals(2,count($category->nodes));
		$this->assertTrue($category->nodes[0]->parent instanceof Category2);
		$this->assertTrue($category->nodes[1]->parent instanceof Category2);
		$this->assertEquals(2,count($category->nodes[0]->children)); // row in test.categories with id 5 (has 2 descendants)
		$this->assertEquals(0,count($category->nodes[1]->children)); // row in test.categories with id 4 (has 0 descendants)
	}

	public function testEagerRecursiveRelation()
	{
		$post=Post2::model()->with(array('comments'=>'author','categories'))->findByPk(2);
		$this->assertEquals(2,count($post->comments));
		$this->assertEquals(2,count($post->categories));
	}

	public function testRelationWithCondition()
	{
		$posts=Post2::model()->with('comments')->findAllByPk(array(2,3,4),array('order'=>'t.id'));
		$this->assertEquals(3,count($posts));
		$this->assertEquals(2,count($posts[0]->comments));
		$this->assertEquals(4,count($posts[1]->comments));
		$this->assertEquals(0,count($posts[2]->comments));

		$post=Post2::model()->with('comments')->findByAttributes(array('id'=>2));
		$this->assertTrue($post instanceof Post2);
		$this->assertEquals(2,count($post->comments));
		$posts=Post2::model()->with('comments')->findAllByAttributes(array('id'=>2));
		$this->assertEquals(1,count($posts));

		$post=Post2::model()->with('comments')->findBySql('select * from test.posts where id=:id',array(':id'=>2));
		$this->assertTrue($post instanceof Post2);
		$posts=Post2::model()->with('comments')->findAllBySql('select * from test.posts where id=:id1 OR id=:id2',array(':id1'=>2,':id2'=>3));
		$this->assertEquals(2,count($posts));

		$post=Post2::model()->with('comments','author')->find('t.id=:id',array(':id'=>2));
		$this->assertTrue($post instanceof Post2);

		$posts=Post2::model()->with('comments','author')->findAll(array(
			'select'=>'title',
			'condition'=>'t.id=:id',
			'limit'=>1,
			'offset'=>0,
			'order'=>'t.title',
			'params'=>array(':id'=>2)));
		$this->assertTrue($posts[0] instanceof Post2);

		$posts=Post2::model()->with('comments','author')->findAll(array(
			'select'=>'title',
			'condition'=>'t.id=:id',
			'limit'=>1,
			'offset'=>2,
			'order'=>'t.title',
			'params'=>array(':id'=>2)));
		$this->assertTrue($posts===array());
	}

	public function testSelfManyMany()
	{
		$user=User2::model()->findByPk(1);
		$this->assertTrue($user instanceof User2);
		$friends=$user->friends;
		$this->assertEquals(count($friends),2);
		$this->assertEquals($friends[0]->id,2);
		$this->assertEquals($friends[1]->id,3);

		$user=User2::model()->with('friends')->findByPk(1);
		$this->assertTrue($user instanceof User2);
		$friends=$user->friends;
		$this->assertEquals(count($friends),2);
		$this->assertEquals($friends[0]->id,2);
		$this->assertEquals($friends[1]->id,3);
	}

	public function testRelationalCount()
	{
		$count=Post2::model()->with('author','firstComment','comments','categories')->count();
		$this->assertEquals(5,$count);

		$count=Post2::model()->with('author','firstComment','comments','categories')->count('t.id=4');
		$this->assertEquals(1,$count);

		$count=Post2::model()->with('author','firstComment','comments','categories')->count('t.id=14');
		$this->assertEquals(0,$count);
	}

	public function testEmptyFinding()
	{
		$post=Post2::model()->with('author','firstComment','comments','categories')->find('t.id=100');
		$this->assertNull($post);

		$posts=Post2::model()->with('author','firstComment','comments','categories')->findAll('t.id=100');
		$this->assertTrue($posts===array());

		$post=Post2::model()->with('author','firstComment','comments','categories')->findBySql('SELECT * FROM test.posts WHERE id=100');
		$this->assertNull($post);

		Post2::model()->with('author','firstComment','comments','categories')->findAllBySql('SELECT * FROM test.posts WHERE id=100');
		$this->assertTrue($posts===array());
	}

	/**
	 * @see https://github.com/yiisoft/yii/issues/2122
	 */
	public function testIssue2122()
	{
		$user=User2::model()->findByPk(2);
		$this->assertEquals(2,count($user->postsWithParam));
		$this->assertEquals('post 2',$user->postsWithParam[0]->title);
		$this->assertEquals('post 3',$user->postsWithParam[1]->title);
	}

	/**
	 * https://github.com/yiisoft/yii/issues/2336
	 */
	public function testEmptyModel()
	{
		$post=new NullablePost2();
		$post->insert();

		$post=new NullablePost2();
		$post->title='dummy';
		$post->insert();

		$this->assertEquals(2,$this->db->createCommand('SELECT COUNT(*) FROM "test"."nullable_posts"')->queryScalar());
		$this->assertEquals(1,$this->db->createCommand('SELECT COUNT(*) FROM "test"."nullable_posts" WHERE LENGTH("title") > 0')->queryScalar());
	}
}
