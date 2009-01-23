<?php
/**
 * CActiveRecord class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CActiveRecord is the base class for classes representing relational data.
 *
 * It implements the active record design pattern, a popular Object-Relational Mapping (ORM) technique.
 *
 * Each active record class represents a database table/view, while an active record instance
 * represents a row in that table. Using CActiveRecord, one can perform CRUD
 * (create, read, update, delete) database actions in an object-oriented way. For example,
 * the following code inserts a row to the 'Post' table (where 'Post' is the class representing
 * the 'Post' table):
 * <pre>
 * $post=new Post;
 * $post->title='sample post';
 * $post->save();
 * </pre>
 *
 * In the following, we elaborate how to use CActiveRecord.
 *
 * First, we need to set up a database connection to be used by CActiveRecord. This is
 * done by loading a {@link CDbConnection} application component whose ID is "db", using the following application
 * configuration:
 * <pre>
 * array(
 *     'components'=>array(
 *         'db'=>array(
 *             'class'=>'CDbConnection',
 *             'connectionString'=>'sqlite:test.db',
 *         ),
 *     )
 * );
 * </pre>
 * To use a different database connection, you should override {@link getDbConnection}.
 *
 * Second, for every database table that we want to access, define an active record class.
 * The following example shows the minimal code needed for a 'Post' class, which represents
 * the 'Post' table.
 * <pre>
 * class Post extends CActiveRecord
 * {
 *     public static function model($className=__CLASS__)
 *     {
 *         return parent::model($className);
 *     }
 * }
 * </pre>
 * The 'model()' method is declared as such for every active record class (to be explained shortly).
 * By convention, the 'Post' class is associated with the database table named 'Post' (the class name).
 * If the class should be associated with some other table, you may override
 * the {@link tableName()} method.
 *
 * To access column values, use $record->columnName, where $record refers to an active record instance.
 * For example, the following code sets the 'title' column (attribute) of $post:
 * <pre>
 * $post=new Post;
 * $post->title='a sample post';
 * </pre>
 * Although we never explicitly declare the 'title' property in the 'Post' class, we can
 * still access it in the above code. This is because 'title' is a column in the 'Post' table,
 * and CActiveRecord makes it accessible as a property with the help of PHP __get() magic method.
 * If you attempt to access a non-existing column in the same way, an exception will be thrown.
 *
 * To insert a new row into 'Post', we first create a new instance of 'Post' class,
 * and then call 'save()' to do the insertion.
 * <pre>
 * $post=new Post;
 * $post->title='sample post';
 * $post->save();
 * </pre>
 * After insertion, the $post object will contain an updated primary key if it is auto-incremental.
 *
 * To query for posts, we will use the 'model' method we defined earlier on. The 'model' method
 * is the only static method defined in CActiveRecord. It returns a static active record instance
 * that is used to access class-level methods (something similar to static class methods).
 * The following 'find' methods are all class-level methods that CActiveRecord implements to
 * facilitate database querying:
 * <pre>
 * // find the first row satisfying the specified condition
 * $post=Post::model()->find($condition,$params);
 *
 * // find all rows satisfying the specified condition
 * $posts=Post::model()->findAll($condition,$params);
 *
 * // find the row with the specified primary key
 * $post=Post::model()->findByPk($postID,$condition,$params);
 *
 * // find all rows with the specified primary keys
 * $posts=Post::model()->findAllByPk($postIDs,$condition,$params);
 *
 * // find the row with the specified attribute values
 * $post=Post::model()->findByAttributes($attributes,$condition,$params);
 *
 * // find all rows with the specified attribute values
 * $posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
 *
 * // find the first row using the specified SQL statement
 * $post=Post::model()->findBySql($sql,$params);
 *
 * // find all rows using the specified SQL statement
 * $posts=Post::model()->findAllBySql($sql,$params);
 *
 * // get the number of rows satisfying the specified condition
 * $n=Post::model()->count($condition,$params);
 *
 * // get the number of rows using the specified SQL statement
 * $n=Post::model()->countBySql($sql,$params);
 * </pre>
 * where $condition specifies the WHERE clause, and $params gives a list of parameters that
 * should be bound to the generated SQL statement.
 * You may also pass a {@link CDbCriteria} object at the place of $condition to specify
 * more complex query conditions (in that case, $params will be ignored since you can sepcify it
 * in the {@link CDbCriteria} object.)
 *
 * As we can see from the above, we have a set of find() methods and a set of findAll() methods.
 * The result of the former is either an active record instance or null
 * if no result is found, while the result of the latter is always an array.
 *
 * After obtaining an active record from query, we can update it or delete it.
 * <pre>
 * $post->title='new post title';
 * $post->save(); // or $post->delete();
 * </pre>
 * In the above, we are using the same 'save()' method to do both insertion and update.
 * CActiveRecord is intelligent enough to differentiate these two scenarios.
 *
 * CActiveRecord also has a few class-level methods to facilitate updating and deleting rows
 * without instantiating active record objects. Below is a summary:
 * <pre>
 * // update all records with the specified attributes and condition
 * Post::model()->updateAll($attributes,$condition,$params);
 *
 * // update one or several records with the specified primary key(s) and attribute values
 * Post::model()->updateByPk($postID,$attributes,$condition,$params);
 *
 * // update one or several counter columns
 * Post::model()->updateCounters($pk,$counters);
 *
 * // delete all records with the specified condition
 * Post::model()->deleteAll($condition,$params);
 *
 * // delete one or several records with the specified primary key(s) and attribute values
 * Post::model()->deleteByPk($pk,$condition,$params);
 *
 * // check if a record exists with the specified condition
 * Post::model()->exists($condition,$params);
 * </pre>
 *
 * A very useful feature that CActiveRecord supports is retrieving related active records.
 * An active record is often related with other active records, via the relationship
 * defined between database tables. For example, a post belongs to an author and has many comments;
 * a user has a profile; a post belongs to and has many categories. CActiveRecord makes retrieving
 * these related objects very easy.
 *
 * Before retrieving related objects, we need to declare these relations in the class definition.
 * This is done by overriding the {@link relations} method:
 * <pre>
 * class Post extends CActiveRecord
 * {
 *     public function relations()
 *     {
 *         return array(
 *             'author'=>array(self::BELONGS_TO, 'User', 'authorID'),
 *             'comments'=>array(self::HAS_MANY, 'Comment', 'postID'),
 *         );
 *     }
 * }
 * </pre>
 * In the above, we declare two relations:
 * <ul>
 * <li>the 'author' relation is of BELONGS_TO type; it references the 'User' active record class;
 *   and the 'authorID' column of the 'Post' table is the foreign key.</li>
 * <li>the 'comments' relation is of HAS_MANY type; it references the 'Comment' active record class;
 *   and the 'postID' column of the 'Comment' table is the foreign key.</li>
 * </ul>
 *
 * Since the 'Post' class knows what kind of related objects it has, we can use
 * the following code to access the author and comments of a post:
 * <pre>
 * $post=Post::model()->findByPk($postID);
 * $author=$post->author;
 * $comments=$post->comments;
 * </pre>
 * Internally, when we access 'author' or 'comments' the first time, an SQL query will
 * be executed to fetch the corresponding active record(s). This is the so-called
 * lazy loading, i.e., the related objects are loaded the first time when they are accessed.
 *
 * If we have 100 posts and would like to obtain the author and comments for every post,
 * lazy loading would be very inefficient, because it means we will need 200 SQL queries
 * in order to fetch those related objects. In this situation, we should resort to
 * the so-called eager loading.
 * <pre>
 * $posts=Post::model()->with('author','comments')->findAll($condition,$params);
 * </pre>
 * Here we used the 'with' method to specify that we want to bring back post records
 * together with their related author and comments. Internally, two SQL queries
 * will be created and executed: one brings back the posts and their authors, and the other
 * brings back the comments). The reason we do not fetch them all together in one shot is
 * because the returned rows would otherwise contain many repetitive results (e.g. the post
 * information is repeated for every comment it has), which is both inefficient for database
 * server and PHP code.
 *
 * Eager loading can be nested. For example, if for every comment, we also want to know
 * its author, we could use the following 'with' find:
 * <pre>
 * $posts=Post::model()->with(array('author','comments'=>'author'))->findAll($condition,$params);
 * </pre>
 * How many SQL queries do we need now? Still two (one for posts and their authors, and one
 * for comments and their authors)! In fact, if there are N HAS_MANY/MANY_MANY relations involved,
 * we would need N+1 queries in total.
 *
 * There is a minor inconvenience when using eager loading, though. That is, we need to
 * disambiguate any column references in conditions or search criteria, because we are
 * joining several tables together. In general, it would be safe if we prefix every column referenced
 * in the condition or search criteria with the table name.
 *
 * Now let's describe more about the possible relations we can declare for active records.
 * From database point of view, the relationship between two tables A and B has three types:
 * one-to-many, one-to-one and many-to-many. In active records, these are classified into four types:
 * <ul>
 * <li>BELONGS_TO: if A vs. B is one-to-many, then B belongs to A (e.g. a post belongs to an author);</li>
 * <li>HAS_MANY: if A vs. B is one-to-many, then A has many B (e.g. an author has many posts);</li>
 * <li>HAS_ONE: this is special case of has-many where A has at most one B;</li>
 * <li>MANY_MANY: this corresponds to many-to-many relationship in database. An intermediate
 *   join table is needed as most DBMS do not support many-to-many relationship directly.
 *   For example, a post can belong to many categories and a category can have many posts.
 *   We would use a table 'post_category' to join the 'posts' and 'categories' tables together.</li>
 * </ul>
 *
 * We already see how to declare a relation in an active record class. In many situations,
 * we often have more constraints on those related objects, such as the conditions that the related
 * objects should satisfy, their orderings, etc. These can be specified as additional options
 * in our relation declaration. For example,
 * <pre>
 * class Post extends CActiveRecord
 * {
 *     ......
 *     public function relations()
 *     {
 *         return array(
 *             'comments'=>array(self::HAS_MANY,'Comment','postID',
 *                               'order'=>'??.createTime DESC',
 *                               'with'=>'author'),
 *         );
 *     }
 * }
 * </pre>
 * where the 'order' option specifies that the comments should be sorted by their creation time,
 * and the 'with' option specifies that the comments should be loaded together with their authors.
 * The special token '??.' is for disamibiguating the column reference. When SQL statement
 * is generated, it will be replaced automatically by the table alias for the 'Comment' table.
 * More detailed description about possible options can be found in {@link CActiveRelation} and
 * {@link CHasManyRelation}.
 *
 * CActiveRecord has built-in validation functionality that validates the user input data
 * before they are saved to database. To use the validation, override {@link CModel::rules()} as follows,
 * <pre>
 * class Post extends CActiveRecord
 * {
 *     public function rules()
 *     {
 *         return array(
 *             array('title, content', 'required'),
 *             array('title', 'length', 'min'=>5),
 *         );
 *     }
 * }
 * </pre>
 * The method returns a list of validation rules, each represented as an array of the following format:
 * <pre>
 * array('attribute list', 'validator name', 'on'=>'insert', ...validation parameters...)
 * </pre>
 * where
 * <ul>
 * <li>attribute list: specifies the attributes to be validated;</li>
 * <li>validator name: specifies the validator to be used. It can be a class name (or class in dot syntax),
 *   or a validation method in the AR class. A validator class must extend from {@link CValidator},
 *   while a validation method must have the following signature:
 * <pre>
 * function validatorName($attribute,$params)
 * </pre>
 *   When using a built-in validator class, you can use an alias name instead of the full class name.
 *   For example, you can use "required" instead of "system.validators.CRequiredValidator".
 *   For more details, see {@link CValidator}.</li>
 * <li>on: this specifies the scenario when the validation rule should be performed. Please see {@link CModel::validate}
 *   for more details about this option. NOTE: if the validation is triggered by the {@link save} call,
 *   it will use 'insert' to indicate an insertion operation, and 'update' an updating operation.
 *   You may thus specify a rule with the 'on' option so that it is applied only to insertion or updating.</li>
 * <li>additional parameters are used to initialize the corresponding validator properties. See {@link CValidator}
 *   for possible properties.</li>
 * </ul>
 *
 * When {@link save} is called, validation will be performed. If there are any validation errors,
 * {@link save} will return false and the errors can be retrieved back via {@link getErrors}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
abstract class CActiveRecord extends CModel
{
	const BELONGS_TO='CBelongsToRelation';
	const HAS_ONE='CHasOneRelation';
	const HAS_MANY='CHasManyRelation';
	const MANY_MANY='CManyManyRelation';

	/**
	 * @var CDbConnection the default database connection for all active record classes.
	 * By default, this is the 'db' application component.
	 * @see getDbConnection
	 */
	public static $db;

	private static $_models=array();			// class name => model

	/**
	 * @var boolean whether the record is new and should be inserted when calling {@link save}.
	 * This property is automatically in constructor and {@link populateRecord}.
	 * Defaults to false, but it will be set to true if the instance is created using
	 * the new operator.
	 */
	public $isNewRecord=false;

	private $_md;
	private $_attributes=array();				// attribute name => attribute value
	private $_related=array();					// attribute name => related objects


	/**
	 * Constructor.
	 * @param array initial attributes (name => value). The attributes
	 * are subject to filtering via {@link setAttributes}. If this parameter is null,
	 * nothing will be done in the constructor (this is internally used by {@link populateRecord}).
	 * @param string scenario name. See {@link setAttributes} for more details about this parameter.
	 * This parameter has been available since version 1.0.2.
	 * @see setAttributes
	 */
	public function __construct($attributes=array(),$scenario='')
	{
		if($attributes===null) // internally used by populateRecord() and model()
			return;

		$this->isNewRecord=true;
		$this->_attributes=$this->getMetaData()->attributeDefaults;

		if($attributes!==array())
			$this->setAttributes($attributes,$scenario);

		$this->afterConstruct();
	}

	/**
	 * PHP sleep magic method.
	 * This method ensures that the model meta data reference is set to null.
	 */
	public function __sleep()
	{
		$this->_md=null;
		return array_keys((array)$this);
	}

	/**
	 * PHP getter magic method.
	 * This method is overridden so that AR attributes can be accessed like properties.
	 * @param string property name
	 * @return mixed property value
	 * @see getAttribute
	 */
	public function __get($name)
	{
		if(isset($this->_attributes[$name]))
			return $this->_attributes[$name];
		else if(isset($this->getMetaData()->columns[$name]))
			return null;
		else if(isset($this->_related[$name]))
			return $this->_related[$name];
		else if(isset($this->getMetaData()->relations[$name]))
			return $this->getRelated($name);
		else
			return parent::__get($name);
	}

	/**
	 * PHP setter magic method.
	 * This method is overridden so that AR attributes can be accessed like properties.
	 * @param string property name
	 * @param mixed property value
	 */
	public function __set($name,$value)
	{
		if(isset($this->getMetaData()->columns[$name]))
			$this->_attributes[$name]=$value;
		else if(isset($this->getMetaData()->relations[$name]))
			$this->_related[$name]=$value;
		else
			parent::__set($name,$value);
	}

	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking
	 * if the named attribute is null or not.
	 * @param string the property name or the event name
	 * @return boolean whether the property value is null
	 * @since 1.0.1
	 */
	public function __isset($name)
	{
		if(isset($this->_attributes[$name]))
			return true;
		else if(isset($this->getMetaData()->columns[$name]))
			return false;
		else if(isset($this->_related[$name]))
			return true;
		else if(isset($this->getMetaData()->relations[$name]))
			return $this->getRelated($name)!==null;
		else
			return parent::__isset($name);
	}

	/**
	 * Returns the related record(s).
	 * This method will return the related record(s) of the current record.
	 * If the relation is HAS_ONE or BELONGS_TO, it will return a single object
	 * or null if the object does not exist.
	 * If the relation is HAS_MANY or MANY_MANY, it will return an array of objects
	 * or an empty array.
	 * @param string the relation name (see {@link relations})
	 * @param boolean whether to reload the related objects from database. Defaults to false.
	 * @return mixed the related object(s).
	 * @throws CDbException if the relation is not specified in {@link relations}.
	 * @since 1.0.2
	 */
	public function getRelated($name,$refresh=false)
	{
		if(!$refresh && (isset($this->_related[$name]) || array_key_exists($name,$this->_related)))
			return $this->_related[$name];

		$md=$this->getMetaData();
		if(isset($md->relations[$name]))
		{
			$relation=$md->relations[$name];
			if($this->isNewRecord && ($relation instanceof CHasOneRelation || $relation instanceof CHasManyRelation))
				return $this->_related[$name]=$relation instanceof CHasOneRelation ? null : array();
			$finder=new CActiveFinder($this,array($name=>$relation->with));
			$finder->lazyFind($this);
			return isset($this->_related[$name]) ? $this->_related[$name] : $this->_related[$name]=null;
		}
		else
			throw new CDbException(Yii::t('yii','{class} does not have relation "{name}".',
				array('{class}'=>get_class($this), '{name}'=>$name)));
	}

	/**
	 * Sets a component property to be null.
	 * This method overrides the parent implementation by clearing
	 * the specified attribute value.
	 * @param string the property name or the event name
	 * @since 1.0.1
	 */
	public function __unset($name)
	{
		if(isset($this->getMetaData()->columns[$name]))
			unset($this->_attributes[$name]);
		else if(isset($this->getMetaData()->relations[$name]))
			unset($this->_related[$name]);
		else
			parent::__unset($name);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * The model returned is a static instance of the AR class.
	 * It is provided for invoking class-level methods (something similar to static class methods.)
	 *
	 * EVERY derived AR class must override this method as follows,
	 * <pre>
	 * public static function model($className=__CLASS__)
	 * {
	 *     return parent::model($className);
	 * }
	 * </pre>
	 *
	 * @param string active record class name.
	 * @return CActiveRecord active record model instance.
	 */
	public static function model($className=__CLASS__)
	{
		if(isset(self::$_models[$className]))
			return self::$_models[$className];
		else
		{
			$model=self::$_models[$className]=new $className(null);
			$model->isNewRecord=false;
			$model->_md=new CActiveRecordMetaData($model);
			return $model;
		}
	}

	/**
	 * @return CActiveRecordMetaData the meta for this AR class.
	 */
	public function getMetaData()
	{
		if($this->_md!==null)
			return $this->_md;
		else
			return $this->_md=self::model(get_class($this))->_md;
	}

	/**
	 * Returns the name of the associated database table.
	 * By default this method returns the class name as the table name.
	 * You may override this method if the table is not named after this convention.
	 * @return string the table name
	 */
	public function tableName()
	{
		return get_class($this);
	}

	/**
	 * This method should be overridden to declare related objects.
	 *
	 * There are four types of relations that may exist between two active record objects:
	 * <ul>
	 * <li>BELONGS_TO: e.g. a member belongs to a team;</li>
	 * <li>HAS_ONE: e.g. a member has at most one profile;</li>
	 * <li>HAS_MANY: e.g. a team has many members;</li>
	 * <li>MANY_MANY: e.g. a member has many skills and a skill belongs to a member.</li>
	 * </ul>
	 *
	 * By declaring these relations, CActiveRecord can bring back related objects
	 * in either lazy loading or eager loading approach, and you save the effort of
	 * writing complex JOIN SQL statements.
	 *
	 * Each kind of related objects is defined in this method as an array with the following elements:
	 * <pre>
	 * 'varName'=>array('relationType', 'className', 'foreignKey', ...additional options)
	 * </pre>
	 * where 'varName' refers to the name of the variable/property that the related object(s) can
	 * be accessed through; 'relationType' refers to the type of the relation, which can be one of the
	 * following four constants: self::BELONGS_TO, self::HAS_ONE, self::HAS_MANY and self::MANY_MANY;
	 * 'className' refers to the name of the active record class that the related object(s) is of;
	 * and 'foreignKey' states the foreign key that relates the two kinds of active record.
	 * Note, for composite foreign keys, they must be listed together, separating with space or comma;
	 * and for foreign keys used in MANY_MANY relation, the joining table must be declared as well
	 * (e.g. 'joinTable(fk1, fk2)').
	 *
	 * Additional options may be specified as name-value pairs in the rest array elements:
	 * <ul>
	 * <li>'select': string|array, a list of columns to be selected. Defaults to '*', meaning all columns.
	 *   Column names should be disambiguated if they appear in an expression (e.g. COUNT(??.name) AS name_count).</li>
	 * <li>'condition': string, the WHERE clause. Defaults to empty. Note, column references need to
	 *   be disambiguated with prefix '??.' (e.g. ??.age&gt;20)</li>
	 * <li>'order': string, the ORDER BY clause. Defaults to empty. Note, column references need to
	 *   be disambiguated with prefix '??.' (e.g. ??.age DESC)</li>
	 * <li>'with': string|array, a list of child related objects that should be loaded together with this object.
	 *   Note, this is only honored by lazy loading, not eager loading.</li>
	 * <li>'joinType': type of join. Defaults to 'LEFT OUTER JOIN'.</li>
	 * <li>'aliasToken': the column prefix for column reference disambiguation. Defaults to '??.'.</li>
	 * </ul>
	 *
	 * The following options are available for certain relations when lazy loading:
	 * <ul>
	 * <li>'group': string, the GROUP BY clause. Defaults to empty. Note, column references need to
	 *   be disambiguated with prefix '??.' (e.g. ??.age). This option only applies to HAS_MANY and MANY_MANY relations.</li>
	 * <li>'having': string, the HAVING clause. Defaults to empty. Note, column references need to
	 *   be disambiguated with prefix '??.' (e.g. ??.age). This option only applies to HAS_MANY and MANY_MANY relations.</li>
	 * <li>'limit': limit of the rows to be selected. This option does not apply to BELONGS_TO relation.</li>
	 * <li>'offset': offset of the rows to be selected. This option does not apply to BELONGS_TO relation.</li>
	 * </ul>
	 *
	 * Below is an example declaring related objects for 'Post' active record class:
	 * <pre>
	 * return array(
	 *     'author'=>array(self::BELONGS_TO, 'User', 'authorID'),
	 *     'comments'=>array(self::HAS_MANY, 'Comment', 'postID', 'with'=>'author', 'order'=>'createTime DESC'),
	 *     'tags'=>array(self::MANY_MANY, 'Tag', 'PostTag(postID, tagID)', 'order'=>'name'),
	 * );
	 * </pre>
	 *
	 * @return array list of related object declarations. Defaults to empty array.
	 */
	public function relations()
	{
		return array();
	}

	/**
	 * Returns the list of all attribute names of the model.
	 * This would return all column names of the table associated with this AR class.
	 * @return array list of attribute names.
	 * @since 1.0.1
	 */
	public function attributeNames()
	{
		return array_keys($this->getMetaData()->columns);
	}

	/**
	 * Returns the name of attributes that are safe to be massively assigned.
	 * The default implementation returns all table columns exception primary key(s).
	 * Child class may override this method to further limit the attributes that can be massively assigned.
	 * See {@link CModel::safeAttributes} on how to override this method.
	 * @return array list of safe attribute names.
	 * @see CModel::safeAttributes
	 */
	public function safeAttributes()
	{
		$attributes=array();
		foreach($this->getMetaData()->columns as $name=>$column)
		{
			if(!$column->isPrimaryKey)
				$attributes[]=$name;
		}
		return $attributes;
	}

	/**
	 * Returns the database connection used by active record.
	 * By default, the "db" application component is used as the database connection.
	 * You may override this method if you want to use a different database connection.
	 * @return CDbConnection the database connection used by active record.
	 */
	public function getDbConnection()
	{
		if(self::$db!==null)
			return self::$db;
		else
		{
			self::$db=Yii::app()->getDb();
			if(self::$db instanceof CDbConnection)
			{
				self::$db->setActive(true);
				return self::$db;
			}
			else
				throw new CDbException(Yii::t('yii','Active Record requires a "db" CDbConnection application component.'));
		}
	}

	/**
	 * @param string the relation name
	 * @return CActiveRelation the named relation declared for this AR class. Null if the relation does not exist.
	 */
	public function getActiveRelation($name)
	{
		return isset($this->getMetaData()->relations[$name]) ? $this->getMetaData()->relations[$name] : null;
	}

	/**
	 * @return CDbTableSchema the metadata of the table that this AR belongs to
	 */
	public function getTableSchema()
	{
		return $this->getMetaData()->tableSchema;
	}

	/**
	 * @return CDbCommandBuilder the command builder used by this AR
	 */
	public function getCommandBuilder()
	{
		return $this->getDbConnection()->getSchema()->getCommandBuilder();
	}

	/**
	 * @param string attribute name
	 * @return boolean whether this AR has the named attribute (table column).
	 */
	public function hasAttribute($name)
	{
		return isset($this->getMetaData()->columns[$name]);
	}

	/**
	 * Returns the named attribute value.
	 * If this is a new record and the attribute is not set before,
	 * the default column value will be returned.
	 * If this record is the result of a query and the attribute is not loaded,
	 * null will be returned.
	 * You may also use $this->AttributeName to obtain the attribute value.
	 * @param string the attribute name
	 * @return mixed the attribute value. Null if the attribute is not set or does not exist.
	 * @throws CException if the attribute does not exist.
	 * @see hasAttribute
	 */
	public function getAttribute($name)
	{
		if(property_exists($this,$name))
			return $this->$name;
		else if(isset($this->_attributes[$name]))
			return $this->_attributes[$name];
		else if(isset($this->getMetaData()->columns[$name]))
			return null;
		else
			throw new CDbException(Yii::t('yii','{class} does not have attribute "{name}".',
				array('{class}'=>get_class($this), '{name}'=>$name)));
	}

	/**
	 * Sets the named attribute value.
	 * You may also use $this->AttributeName to set the attribute value.
	 * @param string the attribute name
	 * @param mixed the attribute value.
	 * @throws CException if the attribute does not exist.
	 * @see hasAttribute
	 */
	public function setAttribute($name,$value)
	{
		if(property_exists($this,$name))
			$this->$name=$value;
		else if(isset($this->getMetaData()->columns[$name]))
			$this->_attributes[$name]=$value;
		else
			throw new CDbException(Yii::t('yii','{class} does not have attribute "{name}".',
				array('{class}'=>get_class($this), '{name}'=>$name)));
	}

	/**
	 * Adds a related object to this record.
	 * This method is used internally by {@link CActiveFinder} to populate related objects.
	 * @param string attribute name
	 * @param mixed the related record
	 * @param boolean whether the relation is HAS_MANY/MANY_MANY.
	 */
	public function addRelatedRecord($name,$record,$multiple)
	{
		if($multiple)
		{
			if(!isset($this->_related[$name]))
				$this->_related[$name]=array();
			if($record instanceof CActiveRecord)
				$this->_related[$name][]=$record;
		}
		else if(!isset($this->_related[$name]))
			$this->_related[$name]=$record;
	}

	/**
	 * Returns all column attribute values.
	 * Note, related objects are not returned.
	 * @param mixed names of attributes whose value needs to be returned.
	 * If this is true (default), then all attribute values will be returned, including
	 * those that are not loaded from DB (null will be returned for those attributes).
	 * If this is null, all attributes except those that are not loaded from DB will be returned.
	 * @return array attribute values indexed by attribute names.
	 */
	public function getAttributes($names=true)
	{
		$attributes=$this->_attributes;
		foreach($this->getMetaData()->columns as $name=>$column)
		{
			if(property_exists($this,$name))
				$attributes[$name]=$this->$name;
			else if($names===true && !isset($attributes[$name]))
				$attributes[$name]=null;
		}
		if(is_array($names))
		{
			$attrs=array();
			foreach($names as $name)
				$attrs[$name]=isset($attributes[$name])?$attributes[$name]:null;
			return $attrs;
		}
		else
			return $attributes;
	}

	/**
	 * Saves the current record.
	 * The record is inserted as a row into the database table if it is manually
	 * created using the 'new' operator. If it is obtained using one of those
	 * 'find' methods, the record is considered not new and it will be used to
	 * update the corresponding row in the table. You may check this status via {@link isNewRecord}.
	 * Validation may be performed before saving the record. If the validation fails,
	 * the record will not be saved.
	 * If the record is being inserted and its primary key is null,
	 * after insertion the primary key will be populated with the value
	 * generated automatically by the database.
	 * @param boolean whether to perform validation before saving the record.
	 * If the validation fails, the record will not be saved to database.
	 * The validation will be performed under either 'insert' or 'update' scenario,
	 * depending on whether {@link isNewRecord} is true or false.
	 * @param array list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the saving succeeds
	 */
	public function save($runValidation=true,$attributes=null)
	{
		if(!$runValidation || $this->validate($this->isNewRecord?'insert':'update', $attributes))
			return $this->isNewRecord ? $this->insert($attributes) : $this->update($attributes);
		else
			return false;
	}

	/**
	 * Returns a list of validators created according to {@link CModel::rules rules}.
	 * This overrides the parent implementation so that the validators are only
	 * created once for each type of AR.
	 * @return array a list of validators created according to {@link CModel::rules}.
	 * @since 1.0.1
	 */
	public function getValidators()
	{
		return $this->getMetaData()->getValidators();
	}

	/**
	 * This event is raised before the record is saved.
	 * @param CEvent the event parameter
	 * @since 1.0.2
	 */
	public function onBeforeSave($event)
	{
		$this->raiseEvent('onBeforeSave',$event);
	}

	/**
	 * This event is raised after the record is saved.
	 * @param CEvent the event parameter
	 * @since 1.0.2
	 */
	public function onAfterSave($event)
	{
		$this->raiseEvent('onAfterSave',$event);
	}

	/**
	 * This event is raised before the record is deleted.
	 * @param CEvent the event parameter
	 * @since 1.0.2
	 */
	public function onBeforeDelete($event)
	{
		$this->raiseEvent('onBeforeDelete',$event);
	}

	/**
	 * This event is raised after the record is deleted.
	 * @param CEvent the event parameter
	 * @since 1.0.2
	 */
	public function onAfterDelete($event)
	{
		$this->raiseEvent('onAfterDelete',$event);
	}

	/**
	 * This event is raised after the record instance is created by new operator.
	 * @param CEvent the event parameter
	 * @since 1.0.2
	 */
	public function onAfterConstruct($event)
	{
		$this->raiseEvent('onAfterConstruct',$event);
	}

	/**
	 * This event is raised after the record is instantiated by a find method.
	 * @param CEvent the event parameter
	 * @since 1.0.2
	 */
	public function onAfterFind($event)
	{
		$this->raiseEvent('onAfterFind',$event);
	}

	/**
	 * This method is invoked before saving a record (after validation, if any).
	 * The default implementation raises the {@link onBeforeSave} event.
	 * You may override this method to do any preparation work for record saving.
	 * Use {@link isNewRecord} to determine whether the saving is
	 * for inserting or updating record.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @return boolean whether the saving should be executed. Defaults to true.
	 */
	protected function beforeSave()
	{
		$this->onBeforeSave(new CEvent($this));
		return true;
	}

	/**
	 * This method is invoked after saving a record.
	 * The default implementation raises the {@link onAfterSave} event.
	 * You may override this method to do postprocessing after record saving.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	protected function afterSave()
	{
		$this->onAfterSave(new CEvent($this));
	}

	/**
	 * This method is invoked before deleting a record.
	 * The default implementation raises the {@link onBeforeDelete} event.
	 * You may override this method to do any preparation work for record deletion.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @return boolean whether the record should be deleted. Defaults to true.
	 */
	protected function beforeDelete()
	{
		$this->onBeforeDelete(new CEvent($this));
		return true;
	}

	/**
	 * This method is invoked after deleting a record.
	 * The default implementation raises the {@link onAfterDelete} event.
	 * You may override this method to do postprocessing after the record is deleted.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	protected function afterDelete()
	{
		$this->onAfterDelete(new CEvent($this));
	}

	/**
	 * This method is invoked after a record instance is created by new operator.
	 * The default implementation raises the {@link onAfterConstruct} event.
	 * You may override this method to do postprocessing after record creation.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	protected function afterConstruct()
	{
		$this->attachBehaviors($this->behaviors());
		$this->onAfterConstruct(new CEvent($this));
	}

	/**
	 * This method is invoked after each record is instantiated by a find method.
	 * The default implementation raises the {@link onAfterFind} event.
	 * You may override this method to do postprocessing after each newly found record is instantiated.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 */
	protected function afterFind()
	{
		$this->attachBehaviors($this->behaviors());
		$this->onAfterFind(new CEvent($this));
	}

	/**
	 * Inserts a row into the table based on this active record attributes.
	 * If the table's primary key is auto-incremental and is null before insertion,
	 * it will be populated with the actual value after insertion.
	 * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
	 * @param array list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the attributes are valid and the record is inserted successfully.
	 * @throws CException if the record is not new
	 */
	public function insert($attributes=null)
	{
		if(!$this->isNewRecord)
			throw new CDbException(Yii::t('yii','The active record cannot be inserted to database because it is not new.'));
		if($this->beforeSave())
		{
			$builder=$this->getCommandBuilder();
			$table=$this->getMetaData()->tableSchema;
			$command=$builder->createInsertCommand($table,$this->getAttributes($attributes));
			if($command->execute())
			{
				$primaryKey=$table->primaryKey;
				if($table->sequenceName!==null && is_string($primaryKey) && $this->$primaryKey===null)
					$this->$primaryKey=$builder->getLastInsertID($table);
				$this->afterSave();
				$this->isNewRecord=false;
				return true;
			}
			else
				$this->afterSave();
		}
		else
			return false;
	}

	/**
	 * Updates the row represented by this active record.
	 * All loaded attributes will be saved to the database.
	 * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
	 * @param array list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the update is successful
	 * @throws CException if the record is new
	 */
	public function update($attributes=null)
	{
		if($this->isNewRecord)
			throw new CDbException(Yii::t('yii','The active record cannot be updated because it is new.'));
		if($this->beforeSave())
		{
			$this->updateByPk($this->getPrimaryKey(),$this->getAttributes($attributes));
			$this->afterSave();
			return true;
		}
		else
			return false;
	}

	/**
	 * Saves a selected list of attributes.
	 * Unlike {@link save}, this method only saves the specified attributes
	 * of an existing row dataset. It thus has better performance.
	 * Note, this method does neither attribute filtering nor validation.
	 * So do not use this method with untrusted data (such as user posted data).
	 * You may consider the following alternative if you want to do so:
	 * <pre>
	 * $postRecord=Post::model()->findByPk($postID);
	 * $postRecord->attributes=$_POST['post'];
	 * $postRecord->save();
	 * </pre>
	 * @param array attributes to be updated. Each element represents an attribute name
	 * or an attribute value indexed by its name. If the latter, the record's
	 * attribute will be changed accordingly before saving.
	 * @return boolean whether the update is successful
	 * @throws CException if the record is new or any database error
	 */
	public function saveAttributes($attributes)
	{
		if(!$this->isNewRecord)
		{
			$values=array();
			foreach($attributes as $name=>$value)
			{
				if(is_integer($name))
					$values[$value]=$this->$value;
				else
					$values[$name]=$this->$name=$value;
			}
			return $this->updateByPk($this->getPrimaryKey(),$values)>0;
		}
		else
			throw new CDbException(Yii::t('yii','The active record cannot be updated because it is new.'));
	}

	/**
	 * Deletes the row corresponding to this active record.
	 * @return boolean whether the deletion is successful.
	 * @throws CException if the record is new
	 */
	public function delete()
	{
		if(!$this->isNewRecord)
		{
			if($this->beforeDelete())
			{
				$result=$this->deleteByPk($this->getPrimaryKey())>0;
				$this->afterDelete();
				return $result;
			}
			else
				return false;
		}
		else
			throw new CDbException(Yii::t('yii','The active record cannot be deleted because it is new.'));
	}

	/**
	 * Repopulates this active record with the latest data.
	 * @return boolean whether the row still exists in the database. If true, the latest data will be populated to this active record.
	 */
	public function refresh()
	{
		if(!$this->isNewRecord && ($record=$this->findByPk($this->getPrimaryKey()))!==null)
		{
			$this->_attributes=array();
			$this->_related=array();
			foreach($this->getMetaData()->columns as $name=>$column)
				$this->$name=$record->$name;
			return true;
		}
		else
			return false;
	}

	/**
	 * Compares this active record with another one.
	 * The comparison is made by comparing the primary key values of the two active records.
	 * @return boolean whether the two active records refer to the same row in the database table.
	 */
	public function equals($record)
	{
		return $this->tableName()===$record->tableName() && $this->getPrimaryKey()===$record->getPrimaryKey();
	}

	/**
	 * @return mixed the primary key value. An array (column name=>column value) is returned if the primary key is composite.
	 * If primary key is not defined, null will be returned.
	 */
	public function getPrimaryKey()
	{
		$table=$this->getMetaData()->tableSchema;
		if(is_string($table->primaryKey))
			return $this->{$table->primaryKey};
		else if(is_array($table->primaryKey))
		{
			$values=array();
			foreach($table->primaryKey as $name)
				$values[$name]=$this->$name;
			return $values;
		}
		else
			return null;
	}

	/**
	 * Finds a single active record with the specified condition.
	 * @param mixed query condition or criteria.
	 * If a string, it is treated as query condition (the WHERE clause);
	 * If an array, it is treated as the initial values for constructing a {@link CDbCriteria} object;
	 * Otherwise, it should be an instance of {@link CDbCriteria}.
	 * @param array parameters to be bound to an SQL statement.
	 * This is only used when the first parameter is a string (query condition).
	 * In other cases, please use {@link CDbCriteria::params} to set parameters.
	 * @return CActiveRecord the record found. Null if no record is found.
	 */
	public function find($condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createCriteria($condition,$params);
		$criteria->limit=1;
		$command=$builder->createFindCommand($this->getTableSchema(),$criteria);
		return $this->populateRecord($command->queryRow());
	}

	/**
	 * Finds all active records satisfying the specified condition.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return array list of active records satisfying the specified condition. An empty array is returned if none is found.
	 */
	public function findAll($condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createCriteria($condition,$params);
		$command=$builder->createFindCommand($this->getTableSchema(),$criteria);
		return $this->populateRecords($command->queryAll());
	}

	/**
	 * Finds a single active record with the specified primary key.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return CActiveRecord the record found. Null if none is found.
	 */
	public function findByPk($pk,$condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createPkCriteria($this->getTableSchema(),$pk,$condition,$params);
		$criteria->limit=1;
		$command=$builder->createFindCommand($this->getTableSchema(),$criteria);
		return $this->populateRecord($command->queryRow());
	}

	/**
	 * Finds all active records with the specified primary keys.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return array the records found. An empty array is returned if none is found.
	 */
	public function findAllByPk($pk,$condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createPkCriteria($this->getTableSchema(),$pk,$condition,$params);
		$command=$builder->createFindCommand($this->getTableSchema(),$criteria);
		return $this->populateRecords($command->queryAll());
	}

	/**
	 * Finds a single active record that has the specified attribute values.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param array list of attribute values (indexed by attribute names) that the active records should match.
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return CActiveRecord the record found. Null if none is found.
	 */
	public function findByAttributes($attributes,$condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createColumnCriteria($this->getTableSchema(),$attributes,$condition,$params);
		$criteria->limit=1;
		$command=$builder->createFindCommand($this->getTableSchema(),$criteria);
		return $this->populateRecord($command->queryRow());
	}

	/**
	 * Finds all active records that have the specified attribute values.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param array list of attribute values (indexed by attribute names) that the active records should match.
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return array the records found. An empty array is returned if none is found.
	 */
	public function findAllByAttributes($attributes,$condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createColumnCriteria($this->getTableSchema(),$attributes,$condition,$params);
		$command=$builder->createFindCommand($this->getTableSchema(),$criteria);
		return $this->populateRecords($command->queryAll());
	}

	/**
	 * Finds a single active record with the specified SQL statement.
	 * @param string the SQL statement
	 * @param array parameters to be bound to the SQL statement
	 * @return CActiveRecord the record found. Null if none is found.
	 */
	public function findBySql($sql,$params=array())
	{
		$command=$this->getCommandBuilder()->createSqlCommand($sql,$params);
		return $this->populateRecord($command->queryRow());
	}

	/**
	 * Finds all active records using the specified SQL statement.
	 * @param string the SQL statement
	 * @param array parameters to be bound to the SQL statement
	 * @return array the records found. An empty array is returned if none is found.
	 */
	public function findAllBySql($sql,$params=array())
	{
		$command=$this->getCommandBuilder()->createSqlCommand($sql,$params);
		return $this->populateRecords($command->queryAll());
	}

	/**
	 * Finds the number of rows satisfying the specified query condition.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return integer the number of rows satisfying the specified query condition.
	 */
	public function count($condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createCriteria($condition,$params);
		return $builder->createCountCommand($this->getTableSchema(),$criteria)->queryScalar();
	}

	/**
	 * Finds the number of rows using the given SQL statement.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param string the SQL statement
	 * @param array parameters to be bound to the SQL statement
	 * @return integer the number of rows using the given SQL statement.
	 */
	public function countBySql($sql,$params=array())
	{
		return $this->getCommandBuilder()->createSqlCommand($sql,$params)->queryScalar();
	}

	/**
	 * Checks whether there is row satisfying the specified condition.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return boolean whether there is row satisfying the specified condition.
	 */
	public function exists($condition,$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createCriteria($condition,$params);
		$table=$this->getTableSchema();
		$criteria->select=reset($table->columns)->rawName;
		$criteria->limit=1;
		return $builder->createFindCommand($table,$criteria)->queryRow()!==false;
	}

	/**
	 * Specifies which related objects should be eagerly loaded.
	 * This method takes variable number of parameters. Each parameter specifies
	 * the name of a relation. If a parameter is an associative array, then
	 * the array keys refer to the relation names, while the array values
	 * refer to the child relation names. For example,
	 * <pre>
	 * author' : query with 'author'.
	 * array('author','category') : query with both 'author' and 'category'.
	 * array('author'=>'group','category') : same as above, plus author 'group'.
	 * array('author'=>array('group','comments'),'category') : same as above, plus author 'group' and 'comments'.</li>
	 * </pre>
	 * This method returns a {@link CActiveFinder} instance that provides
	 * a set of find methods similar to that of CActiveRecord.
	 * The followings are some examples:
	 * <pre>
	 * $posts=Post::model()->with('author','comments'=>'author')->findAll(array('limit'=>10));
	 * $post=Post::model()->with('author'=>array('groups','country'),'comments')->find();
	 * </pre>
	 * @return CActiveFinder the active finder instance. If no parameter is passed in, the object itself will be returned.
	 */
	public function with()
	{
		if(func_num_args()>0)
		{
			$with=func_get_args();
			return new CActiveFinder($this,$with);
		}
		else
			return $this;
	}

	/**
	 * Updates records with the specified primary key(s).
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * Note, the attributes are not checked for safety and validation is NOT performed.
	 * @param mixed primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
	 * @param array list of attributes (name=>$value) to be updated
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return integer the number of rows being updated
	 */
	public function updateByPk($pk,$attributes,$condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$table=$this->getTableSchema();
		$criteria=$builder->createPkCriteria($table,$pk,$condition,$params);
		$command=$builder->createUpdateCommand($table,$attributes,$criteria);
		return $command->execute();
	}

	/**
	 * Updates records with the specified condition.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * Note, the attributes are not checked for safety and no validation is done.
	 * @param array list of attributes (name=>$value) to be updated
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return integer the number of rows being updated
	 */
	public function updateAll($attributes,$condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createCriteria($condition,$params);
		$command=$builder->createUpdateCommand($this->getTableSchema(),$attributes,$criteria);
		return $command->execute();
	}

	/**
	 * Updates one or several counter columns.
	 * Note, this updates all rows of data unless a condition or criteria is specified.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param array the counters to be updated (column name=>increment value)
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return integer the number of rows being updated
	 */
	public function updateCounters($counters,$condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createCriteria($condition,$params);
		$command=$builder->createUpdateCounterCommand($this->getTableSchema(),$counters,$criteria);
		return $command->execute();
	}

	/**
	 * Deletes rows with the specified primary key.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return integer the number of rows deleted
	 */
	public function deleteByPk($pk,$condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createPkCriteria($this->getTableSchema(),$pk,$condition,$params);
		$command=$builder->createDeleteCommand($this->getTableSchema(),$criteria);
		return $command->execute();
	}

	/**
	 * Deletes rows with the specified condition.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed query condition or criteria.
	 * @param array parameters to be bound to an SQL statement.
	 * @return integer the number of rows deleted
	 */
	public function deleteAll($condition='',$params=array())
	{
		$builder=$this->getCommandBuilder();
		$criteria=$builder->createCriteria($condition,$params);
		$command=$builder->createDeleteCommand($this->getTableSchema(),$criteria);
		return $command->execute();
	}

	/**
	 * Creates an active record with the given attributes.
	 * This method is internally used by the find methods.
	 * @param array attribute values (column name=>column value)
	 * @return CActiveRecord the newly created active record. The class of the object is the same as the model class.
	 * Null is returned if the input data is false.
	 */
	public function populateRecord($attributes)
	{
		if($attributes!==false)
		{
			$class=get_class($this);
			$record=new $class(null);
			$record->isNewRecord=false;
			$record->_md=$this->getMetaData();
			foreach($attributes as $name=>$value)
			{
				if(property_exists($record,$name))
					$record->$name=$value;
				else if(isset($record->_md->columns[$name]))
					$record->_attributes[$name]=$value;
			}
			$record->afterFind();
			return $record;
		}
		else
			return null;
	}

	/**
	 * Creates a list of active records based on the input data.
	 * This method is internally used by the find methods.
	 * @param array list of attribute values for the active records.
	 * @return array list of active records.
	 */
	public function populateRecords($data)
	{
		$records=array();
		$class=get_class($this);
		$md=$this->getMetaData();
		$table=$md->tableSchema;
		foreach($data as $attributes)
		{
			$record=new $class(null);
			$record->isNewRecord=false;
			$record->_md=$md;
			foreach($attributes as $name=>$value)
			{
				if(property_exists($record,$name))
					$record->$name=$value;
				else if(isset($record->_md->columns[$name]))
					$record->_attributes[$name]=$value;
			}
			$record->afterFind();
			$records[]=$record;
		}
		return $records;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to check on
	 * @return boolean
	 * @since 1.0.2
	 */
	public function offsetExists($offset)
	{
		return isset($this->getMetaData()->columns[$offset]);
	}
}


/**
 * CActiveRelation is the base class for representing active relations.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CActiveRelation extends CComponent
{
	/**
	 * @var string name of the related object
	 */
	public $name;
	/**
	 * @var string name of the related active record class
	 */
	public $className;
	/**
	 * @var string the foreign key in this relation
	 */
	public $foreignKey;
	/**
	 * @var string join type. Defaults to 'LEFT OUTER JOIN'.
	 */
	public $joinType='LEFT OUTER JOIN';
	/**
	 * @var string list of column names (separated by commas) to be selected.
	 * Do not quote or prefix the column names unless they are used in an expression.
	 * In that case, you should prefix the column names with '??.'.
	 */
	public $select='*';
	/**
	 * @var string WHERE clause. Column names referenced in the condition should be prefixed with '??.'.
	 */
	public $condition='';
	/**
	 * @var string ORDER BY clause. Column names referenced here should be prefixed with '??.'.
	 */
	public $order='';
	/**
	 * @var string the alias for the table that this relation refers to. Defaults to null, meaning
	 * the alias will be generated automatically. If you set this property explicitly, make sure
	 * the alias is unique globally.
	 * @see aliasToken
	 * @since 1.0.1
	 */
	public $alias;
	/**
	 * @var string the column prefix placeholder. It will be replaced by the corresponding table alias.
	 * @see alias
	 */
	public $aliasToken='??';
	/**
	 * @var string|array specifies which related objects should be eagerly loaded when this related object is lazily loaded.
	 * For more details about this property, see {@link CActiveRecord::with()}.
	 */
	public $with=array();

	/**
	 * Constructor.
	 * @param string name of the relation
	 * @param string name of the related active record class
	 * @param string foreign key for this relation
	 * @param array additional options (name=>value). The keys must be the property names of this class.
	 */
	public function __construct($name,$className,$foreignKey,$options=array())
	{
		$this->name=$name;
		$this->className=$className;
		$this->foreignKey=$foreignKey;
		foreach($options as $name=>$value)
			$this->$name=$value;
	}
}


/**
 * CBelongsToRelation represents the parameters specifying a BELONGS_TO relation.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CBelongsToRelation extends CActiveRelation
{
}


/**
 * CHasOneRelation represents the parameters specifying a HAS_ONE relation.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CHasOneRelation extends CActiveRelation
{
}


/**
 * CHasManyRelation represents the parameters specifying a HAS_MANY relation.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CHasManyRelation extends CActiveRelation
{
	/**
	 * @var string GROUP BY clause. Column names referenced here should be prefixed with '??.'.
	 */
	public $group='';
	/**
	 * @var string HAVING clause. Column names referenced here should be prefixed with '??.'.
	 */
	public $having='';
	/**
	 * @var integer limit of the rows to be selected. It is effective only for lazy loading this related object. Defaults to -1, meaning no limit.
	 */
	public $limit=-1;
	/**
	 * @var integer offset of the rows to be selected. It is effective only for lazy loading this related object. Defaults to -1, meaning no offset.
	 */
	public $offset=-1;
}


/**
 * CManyManyRelation represents the parameters specifying a MANY_MANY relation.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CManyManyRelation extends CHasManyRelation
{
}


/**
 * CActiveRecordMetaData represents the meta-data for an Active Record class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CActiveRecordMetaData
{
	/**
	 * @var CDbTableSchema the table schema information
	 */
	public $tableSchema;
	/**
	 * @var array table columns
	 */
	public $columns;
	/**
	 * @var array list of relations
	 */
	public $relations=array();
	/**
	 * @var array attribute default values
	 */
	public $attributeDefaults=array();

	private $_model;
	private $_validators;

	/**
	 * Constructor.
	 * @param CActiveRecord the model instance
	 */
	public function __construct($model)
	{
		$this->_model=$model;

		$tableName=$model->tableName();
		if(($table=$model->getDbConnection()->getSchema()->getTable($tableName))===null)
			throw new CDbException(Yii::t('yii','The table "{table}" for active record class "{class}" cannot be found in the database.',
				array('{class}'=>get_class($model),'{table}'=>$tableName)));
		$this->tableSchema=$table;
		$this->columns=$table->columns;

		foreach($table->columns as $name=>$column)
		{
			if(!$column->isPrimaryKey && $column->defaultValue!==null)
				$this->attributeDefaults[$name]=$column->defaultValue;
		}

		foreach($model->relations() as $name=>$config)
		{
			if(isset($config[0],$config[1],$config[2]))  // relation class, AR class, FK
				$this->relations[$name]=new $config[0]($name,$config[1],$config[2],array_slice($config,3));
			else
				throw new CDbException(Yii::t('yii','Active record "{class}" has an invalid configuration for relation "{relation}". It must specify the relation type, the related active record class and the foreign key.',
					array('{class}'=>get_class($model),'{relation}'=>$name)));
		}
	}

	/**
	 * @return array list of validators
	 */
	public function getValidators()
	{
		if(!$this->_validators)
			$this->_validators=$this->_model->createValidators();
		return $this->_validators;
	}
}
