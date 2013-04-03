<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');

require_once(dirname(__FILE__).'/../data/models.php');

class CActiveRecordEventWrappersTest extends CTestCase
{
	private $_connection;

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->_connection=new CDbConnection('sqlite::memory:');
		$this->_connection->active=true;
		$this->_connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/../data/sqlite.sql'));
		CActiveRecord::$db=$this->_connection;

		// reset models
		UserWithWrappers::clearCounters();
		UserWithWrappers::setBeforeFindCriteria(null);
		PostWithWrappers::clearCounters();
		PostWithWrappers::setBeforeFindCriteria(null);
		CommentWithWrappers::clearCounters();
	}

	public function tearDown()
	{
		$this->_connection->active=false;
	}

	/**
	 * provides different db critieras to test beforeFind criteria modification
	 * @return array (db critiera, expected records, column assertations)
	 */
	public function userCriteriaProvider()
	{
		return array(
			array(new CDbCriteria(array('limit'=>1)), 1, array()),
			array(new CDbCriteria(array('select'=>"'MisterX' AS username")), 4, array('username'=>'MisterX')),
			array(new CDbCriteria(array('with'=>'posts')), 4, array()),
		);
	}

	/**
	 * provides different db critieras to test beforeFind criteria modification
	 * @return array (db critiera, expected records, column assertations)
	 */
	public function postCriteriaProvider()
	{
		return array(
			array('', 3, array()),
			array(new CDbCriteria(array('select'=>"'changedTitle' AS title")), 3, array('title'=>'changedTitle')),
			array(new CDbCriteria(array('condition'=>"title='post 2'")), 1, array()),
			array(new CDbCriteria(array('with'=>'comments')), 3, array()),
			array(new CDbCriteria(array('scopes'=>'rename')), 3, array('title'=>'renamed post')),
		);
	}

	/**
	 * provides different db critieras to test beforeFind criteria modification
	 * @return array (db critiera, expected records, column assertations)
	 */
	public function postCriteriaProviderLazy()
	{
		return array_merge($this->postCriteriaProvider(), array(
			array(new CDbCriteria(array('limit'=>1)), 1, array()),
		));
	}

	/**
	 * Check whether criteria given by dataprovider has been applied
	 * @param array $records
	 * @param CDbCriteria $criteria
	 * @param integer $count
	 * @param array $assertations
	 */
	public function assertCriteriaApplied($records, $criteria, $count, $assertations)
	{
		$this->assertEquals($count, count($records));
		foreach($assertations as $attribute => $value) {
			foreach($records as $record) {
				$this->assertEquals($value, $record->{$attribute});
			}
		}
		if (!empty($criteria))
		{
			$with = (array)$criteria->with;
			foreach($with as $relation) {
				foreach($records as $record) {
					$this->assertTrue($record->hasRelated($relation), 'relation should have been loaded due to with in criteria');
				}
			}
		}
	}

	/**
	 * tests number of calls to beforeFind() on normal find*() method call
	 */
	public function testBeforeFind()
	{
		UserWithWrappers::model()->find();
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->findByAttributes(array('username'=>'user1'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->findByPk(1);
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->findBySql('SELECT * FROM users');
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->findAll();
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->findAllByAttributes(array('username'=>'user1'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->findAllByPk(1);
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->findAllBySql('SELECT * FROM users');
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		// test for query with no result
		$this->assertEmpty(UserWithWrappers::model()->find('1=0'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->findByAttributes(array('username'=>'notExistingUser')));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->findByPk(1000));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->findBySql('SELECT * FROM users WHERE 1=0'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->findAll('1=0'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->findAllByAttributes(array('username'=>'notExistingUser')));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->findAllByPk(array(1000,1001)));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->findAllBySql('SELECT * FROM users WHERE 1=0'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);

	}

	/**
	 * setting select in beforeFind should not effect select on stat relation
	 * https://github.com/yiisoft/yii/issues/1381
	 */
	public function testBeforeFindStatSelect()
	{
		PostWithWrappers::setBeforeFindCriteria(new CDbCriteria(array(
			'select' => 'id, content',
		)));

		$user1 = UserWithWrappers::model()->findByPk(1);
		$this->assertEquals(1, $user1->postCount);

		$user2 = UserWithWrappers::model()->findByPk(2);
		$this->assertEquals(3, $user2->postCount);
	}

	/**
	 * tests if criteria modification in beforeFind() applies on normal find*() method call
	 * @dataProvider userCriteriaProvider
	 */
	public function testBeforeFindCriteriaModification($criteria, $count, $assertations)
	{
		UserWithWrappers::setBeforeFindCriteria($criteria);

		$user = UserWithWrappers::model()->find();
		$this->assertCriteriaApplied(array($user), $criteria, 1, $assertations);

		$user = UserWithWrappers::model()->findByAttributes(array('username'=>'user1'));
		$this->assertCriteriaApplied(array($user), $criteria, 1, $assertations);

		$user = UserWithWrappers::model()->findByPk(1);
		$this->assertCriteriaApplied(array($user), $criteria, 1, $assertations);

		$user = UserWithWrappers::model()->findBySql('SELECT * FROM users');
		$this->assertCriteriaApplied(array($user), $criteria, 1, array());

		$users = UserWithWrappers::model()->findAll();
		$this->assertCriteriaApplied($users, $criteria, $count, $assertations);

		$users = UserWithWrappers::model()->findAllByAttributes(array('username'=>array('user1','user2','user3','user4')));
		$this->assertCriteriaApplied($users, $criteria, $count, $assertations);

		$users = UserWithWrappers::model()->findAllByPk(array(1,2,3,4));
		$this->assertCriteriaApplied($users, $criteria, $count, $assertations);

		$users = UserWithWrappers::model()->findAllBySql('SELECT * FROM users');
		$this->assertCriteriaApplied($users, $criteria, 4, array());
	}

	/**
	 * tests number of calls to beforeFind() on normal find*() method call with eager loading of relations
	 */
	public function testBeforeFindRelationalEager()
	{
		UserWithWrappers::model()->with('posts.comments')->find();
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->with('posts.comments')->findByAttributes(array('username'=>'user1'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->with('posts.comments')->findByPk(1);
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->with('posts.comments')->findBySql('SELECT * FROM users');
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->with('posts.comments')->findAll();
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->with('posts.comments')->findAllByAttributes(array('username'=>'user1'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->with('posts.comments')->findAllByPk(1);
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		UserWithWrappers::model()->with('posts.comments')->findAllBySql('SELECT * FROM users');
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		// test for query with no result
		$this->assertEmpty(UserWithWrappers::model()->with('posts.comments')->find('1=0'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->with('posts.comments')->findByAttributes(array('username'=>'notExistingUser')));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->with('posts.comments')->findByPk(1000));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->with('posts.comments')->findBySql('SELECT * FROM users WHERE id=1000'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->with('posts.comments')->findAll('1=0'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->with('posts.comments')->findAllByAttributes(array('username'=>'notExistingUser')));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->with('posts.comments')->findAllByPk(array(1000, 1001)));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);

		$this->assertEmpty(UserWithWrappers::model()->with('posts.comments')->findAllBySql('SELECT * FROM users WHERE 1=0'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);
	}

	/**
	 * tests if criteria modification in beforeFind() applies when model is loaded eager via relation
	 * @dataProvider postCriteriaProvider
	 */
	public function testBeforeFindRelationalEagerCriteriaModification($criteria, $count, $assertations)
	{
		PostWithWrappers::setBeforeFindCriteria($criteria);

		$user = UserWithWrappers::model()->with('posts.comments')->find('t.id=2');
		$this->assertTrue($user->hasRelated('posts'));
		$this->assertCriteriaApplied($user->posts, $criteria, $count, $assertations);

		$user = UserWithWrappers::model()->with('posts.comments')->findByAttributes(array('username'=>'user2'));
		$this->assertTrue($user->hasRelated('posts'));
		$this->assertCriteriaApplied($user->posts, $criteria, $count, $assertations);

		$user = UserWithWrappers::model()->with('posts.comments')->findByPk(2);
		$this->assertTrue($user->hasRelated('posts'));
		$this->assertCriteriaApplied($user->posts, $criteria, $count, $assertations);

		$user = UserWithWrappers::model()->with('posts.comments')->findBySql('SELECT * FROM users WHERE id=2');
		$this->assertTrue($user->hasRelated('posts'));
		$this->assertCriteriaApplied($user->posts, $criteria, $count, $assertations);

		$users = UserWithWrappers::model()->with('posts.comments')->findAll('t.id=2');
		$user = reset($users);
		$this->assertTrue($user->hasRelated('posts'));
		$this->assertCriteriaApplied($user->posts, $criteria, $count, $assertations);

		$users = UserWithWrappers::model()->with('posts.comments')->findAllByAttributes(array('username'=>'user2'));
		$user = reset($users);
		$this->assertTrue($user->hasRelated('posts'));
		$this->assertCriteriaApplied($user->posts, $criteria, $count, $assertations);

		$users = UserWithWrappers::model()->with('posts.comments')->findAllByPk(2);
		$user = reset($users);
		$this->assertTrue($user->hasRelated('posts'));
		$this->assertCriteriaApplied($user->posts, $criteria, $count, $assertations);

		$users = UserWithWrappers::model()->with('posts.comments')->findAllBySql('SELECT * FROM users WHERE id=2');
		$user = reset($users);
		$this->assertTrue($user->hasRelated('posts'));
		$this->assertCriteriaApplied($user->posts, $criteria, $count, $assertations);
	}

	/**
	 * tests number of calls to beforeFind() on normal find*() method call with lazy loading of relations
	 */
	public function testBeforeFindRelationalLazy()
	{
		$user=UserWithWrappers::model()->find();
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$user->posts;
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);

		$user=UserWithWrappers::model()->find();
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),1);
		$user->posts(array('with'=>'comments'));
		$this->assertEquals(UserWithWrappers::getCounter('beforeFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('beforeFind'),1);
		$this->assertEquals(CommentWithWrappers::getCounter('beforeFind'),1);
	}

	/**
	 * tests if criteria modification in beforeFind() applies when model is loaded lazy via relation
	 * @dataProvider postCriteriaProviderLazy
	 */
	public function testBeforeFindRelationalLazyCriteriaModification($criteria, $count, $assertations)
	{
		PostWithWrappers::setBeforeFindCriteria($criteria);

		$user=UserWithWrappers::model()->findByPk(2);
		$posts = $user->posts;
		$this->assertCriteriaApplied($posts, $criteria, $count, $assertations);

		$user=UserWithWrappers::model()->findByPk(2);
		$posts = $user->posts(array('with'=>'comments'));
		$this->assertCriteriaApplied($posts, $criteria, $count, $assertations);
		foreach($posts as $post) {
			$this->assertTrue($post->hasRelated('comments'));
		}
	}

	/**
	 * tests if criteria modification in beforeFind() does not overide scopes defined by already applied criteria
	 * @dataProvider postCriteriaProviderLazy
	 */
	public function testBeforeFindRelationalLazyCriteriaScopes($criteria, $count, $assertations)
	{
		PostWithWrappers::setBeforeFindCriteria($criteria);

		$user=UserWithWrappers::model()->findByPk(2);
		$posts = $user->postsWithScope;
		$this->assertCriteriaApplied($posts, $criteria, $count, $assertations);
		foreach($posts as $post) {
			$this->assertEquals('replaced content', $post->content);
		}

		$user=UserWithWrappers::model()->findByPk(2);
		$posts = $user->posts(array('with'=>'comments','scopes'=>array('replaceContent')));
		$this->assertCriteriaApplied($posts, $criteria, $count, $assertations);
		foreach($posts as $post) {
			$this->assertTrue($post->hasRelated('comments'));
			$this->assertEquals('replaced content', $post->content);
		}
	}

	/**
	 * tests number of calls to afterFind() on normal find*() method call
	 */
	public function testAfterFind()
	{
		UserWithWrappers::model()->find();
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);

		UserWithWrappers::model()->findByAttributes(array('username'=>'user1'));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);

		UserWithWrappers::model()->findByPk(1);
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);

		UserWithWrappers::model()->findBySql('SELECT * FROM users');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);

		UserWithWrappers::model()->findAll();
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),4);

		UserWithWrappers::model()->findAllByAttributes(array('username'=>'user1'));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);

		UserWithWrappers::model()->findAllByPk(1);
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);

		UserWithWrappers::model()->findAllBySql('SELECT * FROM users');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),4);

		// test for query with no result
		UserWithWrappers::model()->find('1=0');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->findByAttributes(array('username'=>'notExistingUser'));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->findByPk(1000);
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->findBySql('SELECT * FROM users WHERE 1=0');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->findAll('1=0');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->findAllByAttributes(array('username'=>'notExistingUser'));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->findAllByPk(array(1000,1001));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->findAllBySql('SELECT * FROM users WHERE 1=0');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
	}

	/**
	 * tests number of calls to afterFind() on normal find*() method call with eager loding of relations
	 */
	public function testAfterFindRelational()
	{
		UserWithWrappers::model()->with('posts.comments')->find();
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),4);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),5);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),10);

		UserWithWrappers::model()->with('posts.comments')->findByAttributes(array('username'=>'user2'));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),3);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),6);

		UserWithWrappers::model()->with('posts.comments')->findByPk(2);
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),3);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),6);

		UserWithWrappers::model()->with('posts.comments')->findBySql('SELECT * FROM users WHERE id=2');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),3);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),6);

		UserWithWrappers::model()->with('posts.comments')->findAll();
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),4);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),5);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),10);

		UserWithWrappers::model()->with('posts.comments')->findAllByAttributes(array('username'=>'user2'));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),3);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),6);

		UserWithWrappers::model()->with('posts.comments')->findAllByPk(2);
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),3);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),6);

		UserWithWrappers::model()->with('posts.comments')->findAllBySql('SELECT * FROM users');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),4);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),5);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),10);

		// test for query with no result
		UserWithWrappers::model()->with('posts.comments')->find('1=0');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->with('posts.comments')->findByAttributes(array('username'=>'notExistingUser'));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->with('posts.comments')->findByPk(1000);
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->with('posts.comments')->findBySql('SELECT * FROM users WHERE id=1000');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->with('posts.comments')->findAll('1=0');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->with('posts.comments')->findAllByAttributes(array('username'=>'notExistingUser'));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->with('posts.comments')->findAllByPk(array(1000, 1001));
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),0);

		UserWithWrappers::model()->with('posts.comments')->findAllBySql('SELECT * FROM users WHERE 1=0');
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),0);
	}

	/**
	 * CActiveRecord::getRelated doesn't call afterFind() with `through` relation
	 * https://github.com/yiisoft/yii/issues/591
	 */
	public function testIssue591()
	{
		UserWithWrappers::model()->with('comments')->findByPk(1);
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),3);

		$user = UserWithWrappers::model()->findByPk(1);
		$user->comments;
		$this->assertEquals(UserWithWrappers::getCounter('afterFind'),1);
		$this->assertEquals(PostWithWrappers::getCounter('afterFind'),0);
		$this->assertEquals(CommentWithWrappers::getCounter('afterFind'),3);
	}
}