<?php

class User2 extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function rules()
	{
		return array(
			array('username, password, email', 'required'),
			array('username, password', 'match', 'pattern'=>'/^[\d\w_]+$/'),
			array('email', 'email'),
			array('username', 'length', 'min'=>3, 'max'=>32),
			array('password', 'length', 'min'=>6, 'max'=>32),
		);
	}

	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY,'Post2','author_id'),
			'friends'=>array(self::MANY_MANY,'User2','test.user_friends(id,friend)'),
		);
	}

	public function tableName()
	{
		return 'test.users';
	}
}

class Post2 extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO,'User2','author_id'),
			'firstComment'=>array(self::HAS_ONE,'Comment2','post_id','order'=>'"firstComment".content'),
			'comments'=>array(self::HAS_MANY,'Comment2','post_id','order'=>'comments.content DESC'),
			'categories'=>array(self::MANY_MANY,'Category2','test.post_category(post_id,category_id)','order'=>'categories.id DESC'),
		);
	}

	public function rules()
	{
		return array(
			array('title', 'required'),
		);
	}

	public function tableName()
	{
		return 'test.posts';
	}
}

class PostExt2 extends CActiveRecord
{
	public $title='default title';
	public $id;

	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'test.posts';
	}

	public function relations()
	{
		return array(
			'comments'=>array(self::HAS_MANY,'Comment2','post_id','order'=>'comments.content DESC','with'=>array('post'=>array('alias'=>'post'), 'author')),
		);
	}
}

class Comment2 extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'post'=>array(self::BELONGS_TO,'Post2','post_id'),
			'author'=>array(self::BELONGS_TO,'User2','author_id'),
		);
	}

	public function tableName()
	{
		return 'test.comments';
	}
}


class Category2 extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'test.categories';
	}

	public function relations()
	{
		return array(
			'posts'=>array(self::MANY_MANY, 'Post2', 'test.post_category(post_id,category_id)'),
			'parent'=>array(self::BELONGS_TO,'Category2','parent_id'),
			'children'=>array(self::HAS_MANY,'Category2','parent_id'),
			'nodes'=>array(self::HAS_MANY,'Category2','parent_id','with'=>array('parent','children')),
		);
	}
}


class Order2 extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'items'=>array(self::HAS_MANY,'Item2','col1, col2'),
		);
	}

	public function tableName()
	{
		return 'test.orders';
	}
}

class Item2 extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'order'=>array(self::BELONGS_TO,'Order2','col1, col2','alias'=>'_order'),
		);
	}

	public function tableName()
	{
		return 'test.items';
	}
}

class ComplexType2 extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'yii_types';
	}
}