Active Record
=============

Although Yii DAO can handle virtually any database-related task, chances
are that we would spend 90% of our time in writing some SQL statements
which perform the common CRUD (create, read, update and delete) operations.
It is also difficult to maintain our code when they are mixed with SQL
statements. To solve these problems, we can use Active Record.

Active Record (AR) is a popular Object-Relational Mapping (ORM) technique.
Each AR class represents a database table (or view) whose attributes are
represented as the AR class properties, and an AR instance represents a row
in that table. Common CRUD operations are implemented as AR methods. As a
result, we can access our data in a more object-oriented way. For example,
we can use the following code to insert a new row to the `tbl_post` table:

~~~
[php]
$post=new Post;
$post->title='sample post';
$post->content='post body content';
$post->save();
~~~

In the following we describe how to set up AR and use it to perform CRUD
operations. We will show how to use AR to deal with database relationships
in the next section. For simplicity, we use the following database table
for our examples in this section. Note that if you are using MySQL database,
you should replace `AUTOINCREMENT` with `AUTO_INCREMENT` in the following SQL.

~~~
[sql]
CREATE TABLE tbl_post (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	create_time INTEGER NOT NULL
);
~~~

> Note: AR is not meant to solve all database-related tasks. It is best
used for modeling database tables in PHP constructs and performing queries
that do not involve complex SQLs. Yii DAO should be used for those complex
scenarios.


Establishing DB Connection
--------------------------

AR relies on a DB connection to perform DB-related operations. By default,
it assumes that the `db` application component gives the needed
[CDbConnection] instance which serves as the DB connection. The following
application configuration shows an example:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:path/to/dbfile',
			// turn on schema caching to improve performance
			// 'schemaCachingDuration'=>3600,
		),
	),
);
~~~

> Tip: Because Active Record relies on the metadata about tables to
determine the column information, it takes time to read the metadata and
analyze it. If the schema of your database is less likely to be changed,
you should turn on schema caching by configuring the
[CDbConnection::schemaCachingDuration] property to be a value greater than
0.

Support for AR is limited by DBMS. Currently, only the following DBMS are
supported:

   - [MySQL 4.1 or later](http://www.mysql.com)
   - [PostgreSQL 7.3 or later](http://www.postgres.com)
   - [SQLite 2 and 3](http://www.sqlite.org)
   - [Microsoft SQL Server 2000 or later](http://www.microsoft.com/sqlserver/)
   - [Oracle](http://www.oracle.com)

If you want to use an application component other than `db`, or if you
want to work with multiple databases using AR, you should override
[CActiveRecord::getDbConnection()]. The [CActiveRecord] class is the base
class for all AR classes.

> Tip: There are two ways to work with multiple databases in AR. If the
schemas of the databases are different, you may create different base AR
classes with different implementation of
[getDbConnection()|CActiveRecord::getDbConnection]. Otherwise, dynamically
changing the static variable [CActiveRecord::db] is a better idea.

Defining AR Class
-----------------

To access a database table, we first need to define an AR class by
extending [CActiveRecord]. Each AR class represents a single database
table, and an AR instance represents a row in that table. The following
example shows the minimal code needed for the AR class representing the
`tbl_post` table.

~~~
[php]
class Post extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'tbl_post';
	}
}
~~~

> Tip: Because AR classes are often referenced in many places, we can
> import the whole directory containing the AR class, instead of including
> them one by one. For example, if all our AR class files are under
> `protected/models`, we can configure the application as follows:
> ~~~
> [php]
> return array(
> 	'import'=>array(
> 		'application.models.*',
> 	),
> );
> ~~~

By default, the name of the AR class is the same as the database table
name. Override the [tableName()|CActiveRecord::tableName] method if they
are different. The [model()|CActiveRecord::model] method is declared as
such for every AR class (to be explained shortly).

> Info: To use the [table prefix feature](/doc/guide/database.dao#using-table-prefix),
> the [tableName()|CActiveRecord::tableName] method
> for an AR class may be overridden as follows,
> ~~~
> [php]
> public function tableName()
> {
>     return '{{post}}';
> }
> ~~~
> That is, instead of returning the fully qualified table name, we return
> the table name without the prefix and enclose it in double curly brackets.

Column values of a table row can be accessed as properties of the
corresponding AR instance. For example, the following code sets the
`title` column (attribute):

~~~
[php]
$post=new Post;
$post->title='a sample post';
~~~

Although we never explicitly declare the `title` property in the `Post`
class, we can still access it in the above code. This is because `title` is
a column in the `tbl_post` table, and CActiveRecord makes it accessible as a
property with the help of the PHP `__get()` magic method. An exception will
be thrown if we attempt to access a non-existing column in the same way.

> Info: In this guide, we use lower case for all table names and column names.
This is because different DBMS handle case-sensitivity differently.
For example, PostgreSQL treats column names as case-insensitive
by default, and we must quote a column in a query condition if the column
contains mixed-case letters. Using lower case would help eliminate this problem.

AR relies on well defined primary keys of tables. If a table does not have a primary key, it is required that the corresponding AR class specify which column(s) should be the primary key by overriding the `primaryKey()` method as follows,

~~~
[php]
public function primaryKey()
{
	return 'id';
	// For composite primary key, return an array like the following
	// return array('pk1', 'pk2');
}
~~~


Creating Record
---------------

To insert a new row into a database table, we create a new instance of the
corresponding AR class, set its properties associated with the table
columns, and call the [save()|CActiveRecord::save] method to finish the
insertion.

~~~
[php]
$post=new Post;
$post->title='sample post';
$post->content='content for the sample post';
$post->create_time=time();
$post->save();
~~~

If the table's primary key is auto-incremental, after the insertion the AR
instance will contain an updated primary key. In the above example, the
`id` property will reflect the primary key value of the newly inserted
post, even though we never change it explicitly.

If a column is defined with some static default value (e.g. a string, a
number) in the table schema, the corresponding property in the AR instance
will automatically has such a value after the instance is created. One way
to change this default value is by explicitly declaring the property in the
AR class:

~~~
[php]
class Post extends CActiveRecord
{
	public $title='please enter a title';
	......
}

$post=new Post;
echo $post->title;  // this would display: please enter a title
~~~

An attribute can be assigned a value of [CDbExpression]
type before the record is saved (either insertion or updating) to the database.
For example, in order to save a timestamp returned by the MySQL `NOW()` function,
we can use the following code:

~~~
[php]
$post=new Post;
$post->create_time=new CDbExpression('NOW()');
// $post->create_time='NOW()'; will not work because
// 'NOW()' will be treated as a string
$post->save();
~~~

> Tip: While AR allows us to perform database operations without writing
cumbersom SQL statements, we often want to know what SQL statements are executed
by AR underneath. This can be achieved by turning on the [logging feature](/doc/guide/topics.logging)
of Yii. For example, we can turn on [CWebLogRoute] in the application configuration,
and we will see the executed SQL statements being displayed at the end of each Web page.
We can set [CDbConnection::enableParamLogging] to be true in
the application configuration so that the parameter values bound to the SQL
statements are also logged.


Reading Record
--------------

To read data in a database table, we call one of the `find` methods as
follows.

~~~
[php]
// find the first row satisfying the specified condition
$post=Post::model()->find($condition,$params);
// find the row with the specified primary key
$post=Post::model()->findByPk($postID,$condition,$params);
// find the row with the specified attribute values
$post=Post::model()->findByAttributes($attributes,$condition,$params);
// find the first row using the specified SQL statement
$post=Post::model()->findBySql($sql,$params);
~~~

In the above, we call the `find` method with `Post::model()`. Remember
that the static method `model()` is required for every AR class. The method
returns an AR instance that is used to access class-level methods
(something similar to static class methods) in an object context.

If the `find` method finds a row satisfying the query conditions, it will
return a `Post` instance whose properties contain the corresponding column
values of the table row. We can then read the loaded values like we do with
normal object properties, for example, `echo $post->title;`.

The `find` method will return null if nothing can be found in the database
with the given query condition.

When calling `find`, we use `$condition` and `$params` to specify query
conditions. Here `$condition` can be string representing the `WHERE` clause
in a SQL statement, and `$params` is an array of parameters whose values
should be bound to the placeholders in `$condition`. For example,

~~~
[php]
// find the row with postID=10
$post=Post::model()->find('postID=:postID', array(':postID'=>10));
~~~

> Note: In the above, we may need to escape the reference to the `postID` column
for certain DBMS. For example, if we are using PostgreSQL, we would have to write
the condition as `"postID"=:postID`, because PostgreSQL by default will treat column
names as case-insensitive.

We can also use `$condition` to specify more complex query conditions.
Instead of a string, we let `$condition` be a [CDbCriteria] instance, which
allows us to specify conditions other than the `WHERE` clause. For example,

~~~
[php]
$criteria=new CDbCriteria;
$criteria->select='title';  // only select the 'title' column
$criteria->condition='postID=:postID';
$criteria->params=array(':postID'=>10);
$post=Post::model()->find($criteria); // $params is not needed
~~~

Note, when using [CDbCriteria] as query condition, the `$params` parameter
is no longer needed since it can be specified in [CDbCriteria], as shown
above.

An alternative way to [CDbCriteria] is passing an array to the `find` method.
The array keys and values correspond to the criteria's property name and value,
respectively. The above example can be rewritten as follows,

~~~
[php]
$post=Post::model()->find(array(
	'select'=>'title',
	'condition'=>'postID=:postID',
	'params'=>array(':postID'=>10),
));
~~~

> Info: When a query condition is about matching some columns with the
specified values, we can use
[findByAttributes()|CActiveRecord::findByAttributes]. We let the
`$attributes` parameters be an array of the values indexed by the column
names. In some frameworks, this task can be achieved by calling methods
like `findByNameAndTitle`. Although this approach looks attractive, it
often causes confusion, conflict and issues like case-sensitivity of column
names.

When multiple rows of data matching the specified query condition, we can
bring them in all together using the following `findAll` methods, each of
which has its counterpart `find` method, as we already described.

~~~
[php]
// find all rows satisfying the specified condition
$posts=Post::model()->findAll($condition,$params);
// find all rows with the specified primary keys
$posts=Post::model()->findAllByPk($postIDs,$condition,$params);
// find all rows with the specified attribute values
$posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
// find all rows using the specified SQL statement
$posts=Post::model()->findAllBySql($sql,$params);
~~~

If nothing matches the query condition, `findAll` would return an empty
array. This is different from `find` who would return null if nothing is
found.

Besides the `find` and `findAll` methods described above, the following
methods are also provided for convenience:

~~~
[php]
// get the number of rows satisfying the specified condition
$n=Post::model()->count($condition,$params);
// get the number of rows using the specified SQL statement
$n=Post::model()->countBySql($sql,$params);
// check if there is at least a row satisfying the specified condition
$exists=Post::model()->exists($condition,$params);
~~~

Updating Record
---------------

After an AR instance is populated with column values, we can change them
and save them back to the database table.

~~~
[php]
$post=Post::model()->findByPk(10);
$post->title='new post title';
$post->save(); // save the change to database
~~~

As we can see, we use the same [save()|CActiveRecord::save] method to
perform insertion and updating operations. If an AR instance is created
using the `new` operator, calling [save()|CActiveRecord::save] would insert
a new row into the database table; if the AR instance is the result of some
`find` or `findAll` method call, calling [save()|CActiveRecord::save] would
update the existing row in the table. In fact, we can use
[CActiveRecord::isNewRecord] to tell if an AR instance is new or not.

It is also possible to update one or several rows in a database table
without loading them first. AR provides the following convenient
class-level methods for this purpose:

~~~
[php]
// update the rows matching the specified condition
Post::model()->updateAll($attributes,$condition,$params);
// update the rows matching the specified condition and primary key(s)
Post::model()->updateByPk($pk,$attributes,$condition,$params);
// update counter columns in the rows satisfying the specified conditions
Post::model()->updateCounters($counters,$condition,$params);
~~~

In the above, `$attributes` is an array of column values indexed by column
names; `$counters` is an array of incremental values indexed by column
names; and `$condition` and `$params` are as described in the previous
subsection.

Deleting Record
---------------

We can also delete a row of data if an AR instance has been populated with
this row.

~~~
[php]
$post=Post::model()->findByPk(10); // assuming there is a post whose ID is 10
$post->delete(); // delete the row from the database table
~~~

Note, after deletion, the AR instance remains unchanged, but the
corresponding row in the database table is already gone.

The following class-level methods are provided to delete rows without the
need of loading them first:

~~~
[php]
// delete the rows matching the specified condition
Post::model()->deleteAll($condition,$params);
// delete the rows matching the specified condition and primary key(s)
Post::model()->deleteByPk($pk,$condition,$params);
~~~

Data Validation
---------------

When inserting or updating a row, we often need to check if the column
values comply to certain rules. This is especially important if the column
values are provided by end users. In general, we should never trust
anything coming from the client side.

AR performs data validation automatically when
[save()|CActiveRecord::save] is being invoked. The validation is based on
the rules specified by in the [rules()|CModel::rules] method of the AR class.
For more details about how to specify validation rules, refer to
the [Declaring Validation Rules](/doc/guide/form.model#declaring-validation-rules)
section. Below is the typical workflow needed by saving a record:

~~~
[php]
if($post->save())
{
	// data is valid and is successfully inserted/updated
}
else
{
	// data is invalid. call getErrors() to retrieve error messages
}
~~~

When the data for inserting or updating is submitted by end users in an
HTML form, we need to assign them to the corresponding AR properties. We
can do so like the following:

~~~
[php]
$post->title=$_POST['title'];
$post->content=$_POST['content'];
$post->save();
~~~

If there are many columns, we would see a long list of such assignments.
This can be alleviated by making use of the
[attributes|CActiveRecord::attributes] property as shown below. More
details can be found in the [Securing Attribute Assignments](/doc/guide/form.model#securing-attribute-assignments)
section and the [Creating Action](/doc/guide/form.action) section.

~~~
[php]
// assume $_POST['Post'] is an array of column values indexed by column names
$post->attributes=$_POST['Post'];
$post->save();
~~~


Comparing Records
-----------------

Like table rows, AR instances are uniquely identified by their primary key
values. Therefore, to compare two AR instances, we merely need to compare
their primary key values, assuming they belong to the same AR class. A
simpler way is to call [CActiveRecord::equals()], however.

> Info: Unlike AR implementation in other frameworks, Yii supports
composite primary keys in its AR. A composite primary key consists of two
or more columns. Correspondingly, the primary key value is represented as
an array in Yii. The [primaryKey|CActiveRecord::primaryKey] property gives
the primary key value of an AR instance.

Customization
-------------

[CActiveRecord] provides a few placeholder methods that can be overridden
in child classes to customize its workflow.

   - [beforeValidate|CModel::beforeValidate] and
[afterValidate|CModel::afterValidate]: these are invoked before and
after validation is performed.

   - [beforeSave|CActiveRecord::beforeSave] and
[afterSave|CActiveRecord::afterSave]: these are invoked before and after
saving an AR instance.

   - [beforeDelete|CActiveRecord::beforeDelete] and
[afterDelete|CActiveRecord::afterDelete]: these are invoked before and
after an AR instance is deleted.

   - [afterConstruct|CActiveRecord::afterConstruct]: this is invoked for
every AR instance created using the `new` operator.

   - [beforeFind|CActiveRecord::beforeFind]: this is invoked before an AR finder
is used to perform a query (e.g. `find()`, `findAll()`).

   - [afterFind|CActiveRecord::afterFind]: this is invoked after every AR
instance created as a result of query.


Using Transaction with AR
-------------------------

Every AR instance contains a property named
[dbConnection|CActiveRecord::dbConnection] which is a [CDbConnection]
instance. We thus can use the
[transaction](/doc/guide/database.dao#using-transactions) feature provided by Yii
DAO if it is desired when working with AR:

~~~
[php]
$model=Post::model();
$transaction=$model->dbConnection->beginTransaction();
try
{
	// find and save are two steps which may be intervened by another request
	// we therefore use a transaction to ensure consistency and integrity
	$post=$model->findByPk(10);
	$post->title='new post title';
	$post->save();
	$transaction->commit();
}
catch(Exception $e)
{
	$transaction->rollBack();
}
~~~


Named Scopes
------------

> Info: The original idea of named scopes came from Ruby on Rails.

A *named scope* represents a *named* query criteria that can be combined with other named scopes and applied to an active record query.

Named scopes are mainly declared in the [CActiveRecord::scopes()] method as name-criteria pairs. The following code declares two named scopes, `published` and `recently`, in the `Post` model class:

~~~
[php]
class Post extends CActiveRecord
{
	......
	public function scopes()
	{
		return array(
			'published'=>array(
				'condition'=>'status=1',
			),
			'recently'=>array(
				'order'=>'create_time DESC',
				'limit'=>5,
			),
		);
	}
}
~~~

Each named scope is declared as an array which can be used to initialize a [CDbCriteria] instance. For example, the `recently` named scope specifies that the `order` property to be `create_time DESC` and the `limit` property to be 5, which translates to a query criteria that should bring back the most recent 5 posts.

Named scopes are mostly used as modifiers to the `find` method calls. Several named scopes may be chained together and result in a more restrictive query result set. For example, to find the recently published posts, we can use the following code:

~~~
[php]
$posts=Post::model()->published()->recently()->findAll();
~~~

In general, named scopes must appear to the left of a `find` method call. Each of them provides a query criteria, which is combined with other criterias, including the one passed to the `find` method call. The net effect is like adding a list of filters to a query.

> Note: Named scopes can only be used with class-level methods. That is, the method must be called using `ClassName::model()`.


### Parameterized Named Scopes

Named scopes can be parameterized. For example, we may want to customize the number of posts specified by the `recently` named scope. To do so, instead of declaring the named scope in the [CActiveRecord::scopes] method, we need to define a new method whose name is the same as the scope name:

~~~
[php]
public function recently($limit=5)
{
	$this->getDbCriteria()->mergeWith(array(
		'order'=>'create_time DESC',
		'limit'=>$limit,
	));
	return $this;
}
~~~

Then, we can use the following statement to retrieve the 3 recently published posts:

~~~
[php]
$posts=Post::model()->published()->recently(3)->findAll();
~~~

If we do not supply the parameter 3 in the above, we would retrieve the 5 recently published posts by default.


### Default Scope

A model class can have a default scope that would be applied for all queries (including relational ones) about the model. For example, a website supporting multiple languages may only want to display contents that are in the language the current user specifies. Because there may be many queries about the site contents, we can define a default scope to solve this problem. To do so, we override the [CActiveRecord::defaultScope] method as follows,

~~~
[php]
class Content extends CActiveRecord
{
	public function defaultScope()
	{
		return array(
			'condition'=>"language='".Yii::app()->language."'",
		);
	}
}
~~~

Now, if the following method call will automatically use the query criteria as defined above:

~~~
[php]
$contents=Content::model()->findAll();
~~~

> Note: Default scope and named scopes only apply to `SELECT` queries. They are ignored for `INSERT`, `UPDATE` and `DELETE` queries.
> Also, when declaring a scope (default or named), the AR class cannot be used to make DB queries in the method that declares the scope.

<div class="revision">$Id$</div>