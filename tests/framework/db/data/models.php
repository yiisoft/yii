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
			'roles'=>array(self::HAS_MANY,'Role','user_id'),
			'groups'=>array(self::HAS_MANY,'Group',array('group_id'=>'id'),'through'=>'roles'),
			'mentorships'=>array(self::HAS_MANY,'Mentorship','teacher_id','joinType'=>'INNER JOIN'),
			'students'=>array(self::HAS_MANY,'User',array('student_id'=>'id'),'through'=>'mentorships','joinType'=>'INNER JOIN'),
			'posts'=>array(self::HAS_MANY,'Post','author_id'),
			'postsOrderDescFormat1'=>array(self::HAS_MANY,'Post','author_id','scopes'=>'orderDesc'),
			'postsOrderDescFormat2'=>array(self::HAS_MANY,'Post','author_id','scopes'=>array('orderDesc')),
			'postCount'=>array(self::STAT,'Post','author_id'),
		);
	}

	public function tableName()
	{
		return 'users';
	}

	public function scopes()
    {
        return array(
            'nonEmptyPosts'=>array(
                'with'=>array(
                    'posts'=>array(
                        'condition'=>'posts.id is not NULL',
                    ),
                ),
            ),
        );
    }
}

class Mentorship extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'mentorships';
	}
}

class Group extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'roles'=>array(self::HAS_MANY,'Role','group_id'),
			'users'=>array(self::HAS_MANY,'User',array('user_id'=>'id'),'through'=>'roles'),
			'comments'=>array(self::HAS_MANY,'Comment',array('id'=>'author_id'),'through'=>'users'),
			'description'=>array(self::HAS_ONE,'GroupDescription','group_id'),
		);
	}

	public function tableName()
	{
		return 'groups';
	}
}

class GroupDescription extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'groups_descriptions';
	}
}

class Role extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'roles';
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
			'firstComment'=>array(self::HAS_ONE,'Comment','post_id','order'=>'firstComment.content'),
			'comments'=>array(self::HAS_MANY,'Comment','post_id','order'=>'comments.content DESC'),
			'commentCount'=>array(self::STAT,'Comment','post_id'),
			'categories'=>array(self::MANY_MANY,'Category','post_category(post_id,category_id)','order'=>'categories.id DESC'),
		);
	}

	public function tableName()
	{
		return 'posts';
	}

	public function behaviors()
	{
		return array(
			'PostScopesBehavior',
		);
	}

	public function scopes()
	{
		return array(
			'post23'=>array('condition'=>'posts.id=2 OR posts.id=3', 'alias'=>'posts', 'order'=>'posts.id'),
			'post23A'=>array('condition'=>"$this->tableAlias.id=2 OR $this->tableAlias.id=3",'order'=>"$this->tableAlias.id"),
			'post3'=>array('condition'=>'id=3'),
			'postX'=>array('condition'=>'id=:id1 OR id=:id2', 'params'=>array(':id1'=>2, ':id2'=>3)),
			'orderDesc'=>array('order'=>'posts.id DESC','alias'=>'posts'),
		);
	}

	public function rules()
	{
		return array(
			array('title', 'required'),
		);
	}

	public function recent($limit=5)
	{
		$this->getDbCriteria()->mergeWith(array(
			'order'=>'create_time DESC',
			'limit'=>$limit,
		));
		return $this;
	}

	//used for relation model scopes test
	public function p($id)
	{
		$this->getDbCriteria()->mergeWith(array(
			'condition'=>'posts.id=?',
			'params'=>array($id),
		));
		return $this;
	}
}

class PostScopesBehavior extends CActiveRecordBehavior
{
	public function behaviorPost23()
	{
		$this->getOwner()->getDbCriteria()->mergeWith(array(
			'condition'=>'posts.id=2 OR posts.id=3',
			'alias'=>'posts',
			'order'=>'posts.id',
		));

		return $this;
	}

	public function behaviorRecent($limit)
	{
		$this->getOwner()->getDbCriteria()->mergeWith(array(
			'order'=>'create_time DESC',
			'limit'=>$limit,
		));

		return $this;
	}

	//used for relation model scopes test
	public function behaviorP($id)
	{
		$this->getOwner()->getDbCriteria()->mergeWith(array(
			'condition'=>'posts.id=?',
			'params'=>array($id),
		));

		return $this;
	}
}

class PostSpecial extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'posts';
	}

	public function defaultScope()
	{
		return array(
			'condition'=>'posts.id=:id1 OR posts.id=:id2',
			'params'=>array(':id1'=>2, ':id2'=>3),
			'alias'=>'posts',
		);
	}

	public function scopes()
	{
		return array(
			'desc'=>array('order'=>'id DESC'),
		);
	}
}

class UserSpecial extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY,'PostSpecial','author_id'),
		);
	}

	public function tableName()
	{
		return 'users';
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
			'comments'=>array(self::HAS_MANY,'Comment','post_id','order'=>'comments.content DESC','with'=>array('post'=>array('alias'=>'post'), 'author')),
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
			'postCount'=>array(self::STAT, 'Post', 'post_category(post_id,category_id)'),
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
			'itemCount'=>array(self::STAT,'Item','col1, col2'),
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
			'order'=>array(self::BELONGS_TO,'Order','col1, col2','alias'=>'_order'),
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

class UserNoTogether extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY,'PostNoTogether','author_id','together'=>false,'joinType'=>'INNER JOIN'),
		);
	}

	public function tableName()
	{
		return 'users';
	}
}

class PostNoTogether extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'comments'=>array(self::HAS_MANY,'Comment','post_id','together'=>false,'joinType'=>'INNER JOIN'),
		);
	}

	public function tableName()
	{
		return 'posts';
	}
}

class UserWithWrappers extends CActiveRecord
{
	private static $_counters=array();

	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY,'PostWithWrappers','author_id'),
			'postCount'=>array(self::STAT,'PostWithWrappers','author_id'),
		);
	}

	public function tableName()
	{
		return 'users';
	}

	protected function beforeFind()
	{
		parent::beforeFind();
		$this->incrementCounter(__FUNCTION__);
	}

	protected function afterFind()
	{
		parent::afterFind();
		$this->incrementCounter(__FUNCTION__);
	}

	protected function incrementCounter($wrapper)
	{
		if(isset(self::$_counters[$wrapper]))
			self::$_counters[$wrapper]++;
		else
			self::$_counters[$wrapper]=1;
	}

	public static function getCounter($wrapper)
	{
		if(isset(self::$_counters[$wrapper]))
		{
			$result=self::$_counters[$wrapper];
		}
		else
			$result=0;

		self::clearCounters();

		return $result;
	}

	public static function clearCounters()
	{
		self::$_counters=array();
	}
}

class PostWithWrappers extends CActiveRecord
{
	private static $_counters=array();

	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO,'UserWithWrappers','author_id'),
			'comments'=>array(self::HAS_MANY,'CommentWithWrappers','post_id','order'=>'comments.content DESC'),
			'commentCount'=>array(self::STAT,'CommentWithWrappers','post_id'),
		);
	}

	public function tableName()
	{
		return 'posts';
	}

	public function rules()
	{
		return array(
			array('title', 'required'),
		);
	}

	protected function beforeFind()
	{
		parent::beforeFind();
		$this->incrementCounter(__FUNCTION__);
	}

	protected function afterFind()
	{
		parent::afterFind();
		$this->incrementCounter(__FUNCTION__);
	}

	protected function incrementCounter($wrapper)
	{
		if(isset(self::$_counters[$wrapper]))
			self::$_counters[$wrapper]++;
		else
			self::$_counters[$wrapper]=1;
	}

	public static function getCounter($wrapper)
	{
		if(isset(self::$_counters[$wrapper]))
		{
			$result=self::$_counters[$wrapper];
		}
		else
			$result=0;

		self::clearCounters();

		return $result;
	}

	public static function clearCounters()
	{
		self::$_counters=array();
	}
}

class CommentWithWrappers extends CActiveRecord
{
	private static $_counters=array();

	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'post'=>array(self::BELONGS_TO,'PostWithWrappers','post_id'),
			'author'=>array(self::BELONGS_TO,'UserWithWrappers','author_id'),
		);
	}

	public function tableName()
	{
		return 'comments';
	}

	protected function beforeFind()
	{
		parent::beforeFind();
		$this->incrementCounter(__FUNCTION__);
	}

	protected function afterFind()
	{
		parent::afterFind();
		$this->incrementCounter(__FUNCTION__);
	}

	protected function incrementCounter($wrapper)
	{
		if(isset(self::$_counters[$wrapper]))
			self::$_counters[$wrapper]++;
		else
			self::$_counters[$wrapper]=1;
	}

	public static function getCounter($wrapper)
	{
		if(isset(self::$_counters[$wrapper]))
		{
			$result=self::$_counters[$wrapper];
		}
		else
			$result=0;

		self::clearCounters();

		return $result;
	}

	public static function clearCounters()
	{
		self::$_counters=array();
	}
}