<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');

require_once(dirname(__FILE__).'/../../db/data/models.php');

class CHtmlTest extends CTestCase
{
	private $_connection;

	protected function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->_connection=new CDbConnection('sqlite::memory:');
		$this->_connection->active=true;
		$this->_connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/../../db/data/sqlite.sql'));
		CActiveRecord::$db=$this->_connection;
	}

	public function testResolveRelation()
	{
		$post = Post::model()->findByPk(1);

		
		//returns array
		$this->assertTrue(
			is_array(CHtml::resolveRelation($post, 'author')),
		);
		
		//attribute
 		$resolution = CHtml::resolveRelation($post, 'title'); 
 		$this->assertTrue($resolution['attribute'] === 'title');
 		$this->assertTrue($resolution['model'] instanceof Post);

		//single_relation
		$resolution = CHtml::resolveRelation($post, 'author'); 
		$this->assertTrue($resolution['attribute'] === 'primaryKey');
		$this->assertTrue($resolution['model'] instanceof User);
		
		//single_relation.attribute
		$resolution = CHtml::resolveRelation($post, 'author.username'); 
		$this->assertTrue($resolution['attribute'] === 'username');
		$this->assertTrue($resolution['model'] instanceof User);

		//single_relation.attribute(not_present)
		//not sure what's the right behavior here. feels rightish (think custom getters)
		$resolution = CHtml::resolveRelation($post, 'author.foobar'); 
		$this->assertTrue($resolution['attribute'] === 'foobar');
		$this->assertTrue($resolution['model'] instanceof User);
		
		//single_relation.many_relation
		$resolution = CHtml::resolveRelation($post, 'author.posts');
		$this->assertTrue($resolution['attribute'] === 'primaryKey');
		$this->assertTrue(is_array($resolution['model']));
		$this->assertTrue($resolution['model'][0] instanceof Post);

		//single_relation.many_relation.attribute
		$resolution = CHtml::resolveRelation($post, 'author.posts.title');
		$this->assertTrue($resolution['attribute'] === 'title');
		$this->assertTrue(is_array($resolution['model']));
		$this->assertTrue($resolution['model'][0] instanceof Post);

		//single_relation.many_relation.attribute(not_present)
		//not sure what's the right behavior here. feels rightish (think custom getters)
		$resolution = CHtml::resolveRelation($post, 'author.posts.foobar');
		$this->assertTrue($resolution['attribute'] === 'foobar');
		$this->assertTrue(is_array($resolution['model']));
		$this->assertTrue($resolution['model'][0] instanceof Post);

		//many_relation
		$resolution = CHtml::resolveRelation($post, 'comments');
		$this->assertTrue($resolution['attribute'] === 'primaryKey');
		$this->assertTrue(is_array($resolution['model']));
		$this->assertTrue($resolution['model'][0] instanceof Comment);

		//many_relation.attribute
		$resolution = CHtml::resolveRelation($post, 'comments.content');
		$this->assertTrue($resolution['attribute'] === 'content');
		$this->assertTrue(is_array($resolution['model']));
		$this->assertTrue($resolution['model'][0] instanceof Comment);

		//many_relation.single_relation
		//not sure what's the right behavior here. feels wrongish
		$resolution = CHtml::resolveRelation($post, 'comments.author');
		$this->assertTrue($resolution['attribute'] === 'author'); 
		$this->assertTrue(is_array($resolution['model']));
		$this->assertTrue($resolution['model'][0] instanceof Comment);

		//many_relation.attribute.attributes(not_present)
		$resolution = CHtml::resolveRelation($post, 'comments.content.foobar');
		$this->assertNull($resolution['attribute']);
		$this->assertNull($resolution['model']);
	}
}