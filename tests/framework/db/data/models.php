<?php

/**
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $email
 */
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
			'profiles'=>array(self::HAS_MANY,'Profile','user_id'),
			'posts'=>array(self::HAS_MANY,'Post','author_id'),
			'postsCondition'=>array(self::HAS_MANY,'Post','author_id', 'condition'=>'postsCondition.id IN (2,3)'),
			'postsOrderDescFormat1'=>array(self::HAS_MANY,'Post','author_id','scopes'=>'orderDesc'),
			'postsOrderDescFormat2'=>array(self::HAS_MANY,'Post','author_id','scopes'=>array('orderDesc')),
			'postCount'=>array(self::STAT,'Post','author_id'),
			/* For {@link CActiveRecordTest::testHasManyThroughHasManyWithCustomSelect()}: */
			'mentorshipsCustomSelect'=>array(self::HAS_MANY,'Mentorship','teacher_id','select' => array('teacher_id', 'student_id')),
			'studentsCustomSelect'=>array(self::HAS_MANY,'User',array('student_id'=>'id'),'through'=>'mentorshipsCustomSelect','select' => array('id', 'username')),
			/* For {@link CActiveRecordTest::testRelationalStatWithScopes}: */
			'recentPostCount1'=>array(self::STAT,'Post','author_id','scopes'=>'recentScope'), // CStatRelation with scopes, HAS_MANY case
			'recentPostCount2'=>array(self::STAT,'Post','author_id','scopes'=>array('recentScope')), // CStatRelation with scopes, HAS_MANY case
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

/**
 * @property integer $teacher_id
 * @property integer $student_id
 * @property string $progress
 */
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

/**
 * @property integer $id
 * @property string $name
 */
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
			/* Support for {@link CActiveRecordTest::testLazyLoadThroughRelationWithCondition()}: */
			'rolesWhichEmptyByCondition'=>array(self::HAS_MANY,'Role','group_id','condition'=>'2=:compareValue','params'=>array(':compareValue'=>1)),
			'usersWhichEmptyByCondition'=>array(self::HAS_MANY,'User',array('user_id'=>'id'),'through'=>'rolesWhichEmptyByCondition'),
		);
	}

	public function tableName()
	{
		return 'groups';
	}
}

/**
 * @property integer $group_id
 * @property string $name
 */
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

/**
 * @property integer $user_id
 * @property integer $group_id
 * @property string $name
 */
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

/**
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $country
 * @property integer $user_id
 */
class Profile extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'profiles';
	}
}

/**
 * @property integer $id
 * @property string $title
 * @property float $create_time
 * @property integer $author_id
 * @property string $content
 */
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
			/* For {@link CActiveRecordTest::testRelationalStatWithScopes}: */
			'recentScope'=>array('condition'=>"$this->tableAlias.create_time>=:create_time", 'params'=>array(':create_time'=>100002)),// CStatRelation with scopes, HAS_MANY case
			'recentScope2'=>array('condition'=>"$this->tableAlias.create_time>=:create_time", 'params'=>array(':create_time'=>100001)),// CStatRelation with scopes, MANY_MANY case
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

/**
 * @property integer $id
 * @property string $title
 * @property float $create_time
 * @property integer $author_id
 * @property string $content
 */
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

/**
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $email
 */
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

/**
 * @property integer $id
 * @property string $title
 * @property float $create_time
 * @property integer $author_id
 * @property string $content
 */
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

/**
 * @property integer $id
 * @property string $content
 * @property integer $post_id
 * @property integer $author_id
 */
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
			'postAuthor'=>array(self::HAS_ONE,'User',array('author_id'=>'id'),'through'=>'post'),
			'postAuthorBelongsTo'=>array(self::BELONGS_TO,'User',array('author_id'=>'id'),'through'=>'post'),
		);
	}

	public function tableName()
	{
		return 'comments';
	}
}

/**
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 */
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
			/* For {@link CActiveRecordTest::testRelationalStatWithScopes}: */
			'recentPostCount1'=>array(self::STAT, 'Post', 'post_category(post_id,category_id)','scopes'=>'recentScope2'), // CStatRelation with scopes, MANY_MANY case
			'recentPostCount2'=>array(self::STAT, 'Post', 'post_category(post_id,category_id)','scopes'=>array('recentScope2')), // CStatRelation with scopes, MANY_MANY case
		);
	}
}

/**
 * @property integer $key1
 * @property integer $key2
 * @property string $name
 */
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

/**
 * @property integer $id
 * @property string $name
 * @property integer $col1
 * @property integer $col2
 */
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

/**
 * @property integer $int_col
 * @property integer $int_col2
 * @property string $char_col
 * @property string $char_col2
 * @property string $char_col3
 * @property string $char_col4
 * @property string $char_col5
 * @property float $float_col
 * @property float $float_col2
 * @property string $blob_col
 * @property float $numeric_col
 * @property float $time
 * @property integer $bool_col
 * @property integer $bool_col2
 * @property integer $null_col
 */
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

/**
 * @property integer $id
 * @property string $class
 * @property integer $parentID
 * @property integer $ownerID
 * @property string $title
 */
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

/**
 * @property integer $id
 * @property integer $authorID
 * @property string $body
 */
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

/**
 * @property integer $id
 * @property integer $authorID
 * @property string $body
 */
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

/**
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $email
 */
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

/**
 * @property integer $id
 * @property string $title
 * @property float $create_time
 * @property integer $author_id
 * @property string $content
 */
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

/**
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $email
 */
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

/**
 * @property integer $id
 * @property string $title
 * @property float $create_time
 * @property integer $author_id
 * @property string $content
 */
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

/**
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $email
 */
class UserWithWrappers extends CActiveRecord
{
	private static $_counters=array();

	private static $_beforeFindCriteria;

	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY,'PostWithWrappers','author_id'),
			'postsWithScope'=>array(self::HAS_MANY,'PostWithWrappers','author_id','scopes'=>array('replaceContent')),
			'postCount'=>array(self::STAT,'PostWithWrappers','author_id'),
			'comments'=>array(self::HAS_MANY,'CommentWithWrappers',array('id'=>'post_id'),'through'=>'posts')
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

		if (self::$_beforeFindCriteria!==null) {
			$this->getDbCriteria()->mergeWith(self::$_beforeFindCriteria);
		}
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

	public static function setBeforeFindCriteria($criteria)
	{
		self::$_beforeFindCriteria=(empty($criteria) ? null : $criteria);
	}
}

/**
 * @property integer $id
 * @property string $title
 * @property float $create_time
 * @property integer $author_id
 * @property string $content
 */
class PostWithWrappers extends CActiveRecord
{
	private static $_counters=array();

	private static $_beforeFindCriteria;

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

		if (self::$_beforeFindCriteria!==null) {
			$this->getDbCriteria()->mergeWith(self::$_beforeFindCriteria);
		}
	}

	protected function afterFind()
	{
		parent::afterFind();
		$this->incrementCounter(__FUNCTION__);
	}

	public function scopes()
	{
		return array(
			'rename'=>array(
				'select'=>"'renamed post' AS title",
			),
			'replaceContent' => array(
				'select'=>"'replaced content' AS content",
			),
		);
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

	public static function setBeforeFindCriteria($criteria)
	{
		self::$_beforeFindCriteria=(empty($criteria) ? null : $criteria);
	}
}

/**
 * @property integer $id
 * @property string $content
 * @property integer $post_id
 * @property integer $author_id
 */
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

/**
 * @property integer $id
 * @property integer $deleted
 * @property string $name
 */
class UserWithDefaultScope extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'UserWithDefaultScope';
	}

	public function defaultScope()
	{
		$alias=$this->getTableAlias(false,false);

		return array(
			'condition'=>"{$alias}.deleted IS NULL",
			'order'=>"{$alias}.name ASC",
		);
	}

	public function relations()
	{
		return array(
			'links'=>array(self::HAS_MANY,'UserWithDefaultScopeLink','from_id'),
		);
	}
}

class UserWithDefaultScopeAlias extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'users';
	}

	public function defaultScope()
	{
		return array(
			'alias'=>'my_alias',
			'condition'=>"my_alias.username='user1'",
			'order'=>'my_alias.username',
		);
	}
}

/**
 * @property integer $id
 * @property integer $from_id
 * @property integer $to_id
 */
class UserWithDefaultScopeLink extends CActiveRecord
{
	public static function model($class=__CLASS__)
	{
		return parent::model($class);
	}

	public function tableName()
	{
		return 'UserWithDefaultScopeLink';
	}

	public function relations()
	{
		return array(
			'from_user'=>array(self::BELONGS_TO,'UserWithDefaultScope','from_id'),
			'to_user'=>array(self::BELONGS_TO,'UserWithDefaultScope','to_id'),
		);
	}
}
