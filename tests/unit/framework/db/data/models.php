<?php

class User extends CActiveRecord
{
	public $username2;

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
			'posts'=>array(self::HAS_MANY,'Post','author_id'),
		);
	}

	public function tableName()
	{
		return 'users';
	}
}

class Post extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO,'User','author_id'),
			'firstComment'=>array(self::HAS_ONE,'Comment','post_id','order'=>'??.content'),
			'comments'=>array(self::HAS_MANY,'Comment','post_id','order'=>'??.content DESC'),
			'categories'=>array(self::MANY_MANY,'Category','post_category(post_id,category_id)'),
		);
	}

	public function tableName()
	{
		return 'posts';
	}
}

class PostExt extends CActiveRecord
{
	public $title='default title';
	public $id;

	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'posts';
	}

	public function relations()
	{
		return array(
			'comments'=>array(self::HAS_MANY,'Comment','post_id','order'=>'??.content DESC','with'=>array('post','author.posts.author')),
		);
	}
}

class Comment extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'post'=>array(self::BELONGS_TO,'Post','post_id'),
			'author'=>array(self::BELONGS_TO,'User','author_id'),
		);
	}

	public function tableName()
	{
		return 'comments';
	}
}


class Category extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'categories';
	}

	public function relations()
	{
		return array(
			'posts'=>array(self::MANY_MANY, 'Post', 'post_category(post_id,category_id)'),
			'parent'=>array(self::BELONGS_TO,'Category','parent_id'),
			'children'=>array(self::HAS_MANY,'Category','parent_id'),
			'nodes'=>array(self::HAS_MANY,'Category','parent_id','with'=>array('parent','children')),
		);
	}
}


class Order extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'items'=>array(self::HAS_MANY,'Item','col1, col2'),
		);
	}

	public function tableName()
	{
		return 'orders';
	}
}

class Item extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'order'=>array(self::BELONGS_TO,'Order','col1, col2'),
		);
	}

	public function tableName()
	{
		return 'items';
	}
}

class ComplexType extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'types';
	}
}

class Content extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'Content';
	}

	public function relations()
	{
		return array(
			'parent'=>array(self::BELONGS_TO,'Content','parentID'),
			'children'=>array(self::HAS_MANY,'Content','parentID'),
			'owner'=>array(self::BELONGS_TO,'User','ownerID'),
		);
	}
}

class Article extends Content
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'Article';
	}

	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO,'User','authorID'),
			'comments'=>array(self::HAS_MANY,'ArticleComment','parentID'),
		);
	}
}

class ArticleComment extends Content
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'Comment';
	}

	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO,'User','authorID'),
			'article'=>array(self::BELONGS_TO,'Article','parentID'),
		);
	}
}


class UserNoFk extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY,'PostNoFk','author_id'),
		);
	}

	public function tableName()
	{
		return 'users';
	}
}

class PostNoFk extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO,'UserNoFk','author_id'),
		);
	}

	public function tableName()
	{
		return 'posts_nofk';
	}
}
