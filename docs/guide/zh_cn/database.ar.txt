Active Record
=============

虽然 Yii DAO 可以处理几乎任何数据库相关的任务，
但很可能我们会花费 90% 的时间以编写一些执行普通 CRUD（create, read, update 和 delete）操作的 SQL 语句。
而且我们的代码中混杂了SQL语句时也会变得难以维护。要解决这些问题，我们可以使用 Active Record。

Active Record (AR) 是一个流行的 对象-关系映射 (ORM) 技术。
每个 AR 类代表一个数据表（或视图），数据表（或视图）的列在 AR 类中体现为类的属性，一个 AR 实例则表示表中的一行。
常见的 CRUD 操作作为 AR 的方法实现。因此，我们可以以一种更加面向对象的方式访问数据。
例如，我们可以使用以下代码向 `tbl_post` 表中插入一个新行。

~~~
[php]
$post=new Post;
$post->title='sample post';
$post->content='post body content';
$post->save();
~~~

下面我们讲解怎样设置 AR 并通过它执行 CRUD 操作。我们将在下一节中展示怎样使用 AR 处理数据库关系。
为简单起见，我们使用下面的数据表作为此节中的例子。注意，如果你使用 MySQL 数据库，你应该将下面的 SQL 中的
 `AUTOINCREMENT` 替换为 `AUTO_INCREMENT`。

~~~
[sql]
CREATE TABLE tbl_post (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	create_time INTEGER NOT NULL
);
~~~

> Note|注意: AR 并非要解决所有数据库相关的任务。它的最佳应用是模型化数据表为 PHP 结构和执行不包含复杂 SQL 语句的查询。
对于复杂查询的场景，应使用 Yii DAO。


建立数据库连接
--------------------------

AR 依靠一个数据库连接以执行数据库相关的操作。默认情况下，
它假定 `db` 应用组件提供了所需的 [CDbConnection] 数据库连接实例。如下应用配置提供了一个例子：

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:path/to/dbfile',
			// 开启表结构缓存（schema caching）提高性能
			// 'schemaCachingDuration'=>3600,
		),
	),
);
~~~

> Tip|提示: 由于 Active Record 依靠表的元数据（metadata）测定列的信息，读取元数据并解析需要时间。
如果你数据库的表结构很少改动，你应该通过配置 [CDbConnection::schemaCachingDuration] 
属性的值为一个大于零的值开启表结构缓存。

对 AR 的支持受 DBMS 的限制，当前只支持下列几种 DBMS：

   - [MySQL 4.1 或更高版本](http://www.mysql.com)
   - [PostgreSQL 7.3 或更高版本](http://www.postgres.com)
   - [SQLite 2 和 3](http://www.sqlite.org)
   - [Microsoft SQL Server 2000 或更高版本](http://www.microsoft.com/sqlserver/)
   - [Oracle](http://www.oracle.com)

> Note|注意:  1.0.4 版开始支持  Microsoft SQL Server；从1.0.5 版开始支持 Oracle。

如果你想使用一个不是 `db` 的应用组件，或者如果你想使用 AR 处理多个数据库，你应该覆盖
[CActiveRecord::getDbConnection()]。 [CActiveRecord] 类是所有 AR 类的基类。

> Tip|提示: 通过 AR 使用多个数据库有两种方式。如果数据库的结构不同，你可以创建不同的 AR 基类实现不同的 
[getDbConnection()|CActiveRecord::getDbConnection]。否则，动态改变静态变量 [CActiveRecord::db] 是一个好主意。

定义 AR 类
-----------------

要访问一个数据表，我们首先需要通过集成 [CActiveRecord] 定义一个 AR 类。
每个 AR 类代表一个单独的数据表，一个 AR 实例则代表那个表中的一行。
如下例子演示了代表 `tbl_post` 表的 AR 类的最简代码：

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

> Tip|提示: 由于 AR 类经常在多处被引用，我们可以导入包含 AR 类的整个目录，而不是一个个导入。
> 例如，如果我们所有的 AR 类文件都在 
> `protected/models` 目录中，我们可以配置应用如下：
> ~~~
> [php]
> return array(
> 	'import'=>array(
> 		'application.models.*',
> 	),
> );
> ~~~

默认情况下，AR 类的名字和数据表的名字相同。如果不同，请覆盖 [tableName()|CActiveRecord::tableName] 方法。
[model()|CActiveRecord::model] 方法为每个 AR 类声明为如此（稍后解释）。

> Info|信息: 要使用 1.1.0 版本中引入的 [表前缀功能](/doc/guide/database.dao#using-table-prefix)
> AR 类的 [tableName()|CActiveRecord::tableName] 方法可以通过如下方式覆盖
> ~~~
> [php]
> public function tableName()
> {
>     return '{{post}}';
> }
> ~~~
> 这就是说，我们将返回通过双大括号括起来的没有前缀的表名，而不是完整的表的名字。

数据表行中列的值可以作为相应 AR 实例的属性访问。例如，如下代码设置了
`title` 列 (属性):

~~~
[php]
$post=new Post;
$post->title='a sample post';
~~~

虽然我们从未在 `Post` 类中显式定义属性 `title`，我们还是可以通过上述代码访问。
这是因为 `title` 是 `tbl_post` 表中的一个列，CActiveRecord 通过PHP的 `__get()` 魔术方法使其成为一个可访问的属性。
如果我们尝试以同样的方式访问一个不存在的列，将会抛出一个异常。

> Info|信息: 此指南中，我们在表名和列名中均使用了小写字母。
这是因为不同的 DBMS 处理大小写的方式不同。
例如，PostgreSQL 默认情况下对列的名字大小写不敏感，而且我们必须在一个查询条件中用引号将大小写混合的列名引起来。
使用小写字母可以帮助我们避免此问题。

AR 依靠表中良好定义的主键。如果一个表没有主键，则必须在相应的 AR
类中通过如下方式覆盖 `primaryKey()` 方法指定哪一列或哪几列作为主键。

~~~
[php]
public function primaryKey()
{
	return 'id';
	// 对于复合主键，要返回一个类似如下的数组
	// return array('pk1', 'pk2');
}
~~~


创建记录
---------------

要向数据表中插入新行，我们要创建一个相应 AR 类的实例，设置其与表的列相关的属性，然后调用
 [save()|CActiveRecord::save] 方法完成插入：

~~~
[php]
$post=new Post;
$post->title='sample post';
$post->content='content for the sample post';
$post->create_time=time();
$post->save();
~~~

如果表的主键是自增的，在插入完成后，AR 实例将包含一个更新的主键。在上面的例子中，
`id` 属性将反映出新插入帖子的主键值，即使我们从未显式地改变它。

如果一个列在表结构中使用了静态默认值（例如一个字符串，一个数字）定义。则 AR 
实例中相应的属性将在此实例创建时自动含有此默认值。改变此默认值的一个方式就是在 AR 
类中显示定义此属性：

~~~
[php]
class Post extends CActiveRecord
{
	public $title='please enter a title';
	......
}

$post=new Post;
echo $post->title;  // 这儿将显示: please enter a title
~~~

记录在保存（插入或更新）到数据库之前，其属性可以赋值为 [CDbExpression] 类型。
例如，为保存一个由 MySQL 的 `NOW()` 函数返回的时间戳，我们可以使用如下代码：
~~~
[php]
$post=new Post;
$post->create_time=new CDbExpression('NOW()');
// $post->create_time='NOW()'; 不会起作用，因为
// 'NOW()' 将会被作为一个字符串处理。
$post->save();
~~~

> Tip|提示: 由于 AR 允许我们无需写一大堆 SQL 语句就能执行数据库操作，
我们经常会想知道 AR 在背后到底执行了什么 SQL 语句。这可以通过开启 Yii 的
[日志功能](/doc/guide/topics.logging) 实现。例如，我们在应用配置中开启了
[CWebLogRoute] ，我们将会在每个网页的最后看到执行过的 SQL 语句。

我们可以在应用配置中设置 [CDbConnection::enableParamLogging] 为 true 
，这样绑定在 SQL 语句中的参数值也会被记录。


读取记录
--------------

要读取数据表中的数据，我们可以通过如下方式调用 `find` 系列方法中的一种：

~~~
[php]
// 查找满足指定条件的结果中的第一行
$post=Post::model()->find($condition,$params);
// 查找具有指定主键值的那一行
$post=Post::model()->findByPk($postID,$condition,$params);
// 查找具有指定属性值的行
$post=Post::model()->findByAttributes($attributes,$condition,$params);
// 通过指定的 SQL 语句查找结果中的第一行
$post=Post::model()->findBySql($sql,$params);
~~~

如上所示，我们通过 `Post::model()` 调用 `find` 方法。
请记住，静态方法 `model()` 是每个 AR 类所必须的。
此方法返回在对象上下文中的一个用于访问类级别方法（类似于静态类方法的东西）的 AR 实例。

如果 `find` 方法找到了一个满足查询条件的行，它将返回一个 `Post` 实例，实例的属性含有数据表行中相应列的值。
然后我们就可以像读取普通对象的属性那样读取载入的值，例如  `echo $post->title;`。

如果使用给定的查询条件在数据库中没有找到任何东西， `find` 方法将返回 null 。

调用 `find` 时，我们使用 `$condition` 和 `$params` 指定查询条件。此处
`$condition` 可以是 SQL 语句中的 `WHERE` 字符串，`$params` 则是一个参数数组，其中的值应绑定到 `$condation`
中的占位符。例如：

~~~
[php]
// 查找 postID=10 的那一行
$post=Post::model()->find('postID=:postID', array(':postID'=>10));
~~~

> Note|注意: 在上面的例子中，我们可能需要在特定的 DBMS 中将 `postID` 列的引用进行转义。
例如，如果我们使用 PostgreSQL，我们必须将此表达式写为 `"postID"=:postID`，因为 PostgreSQL
在默认情况下对列名大小写不敏感。

我们也可以使用 `$condition` 指定更复杂的查询条件。
不使用字符串，我们可以让 `$condition` 成为一个 [CDbCriteria] 的实例，它允许我们指定不限于 `WHERE` 的条件。
例如：

~~~
[php]
$criteria=new CDbCriteria;
$criteria->select='title';  // 只选择 'title' 列
$criteria->condition='postID=:postID';
$criteria->params=array(':postID'=>10);
$post=Post::model()->find($criteria); // $params 不需要了
~~~

注意，当使用 [CDbCriteria] 作为查询条件时，`$params` 参数不再需要了，因为它可以在
[CDbCriteria] 中指定，就像上面那样。

一种替代 [CDbCriteria] 的方法是给 `find` 方法传递一个数组。
数组的键和值各自对应标准（criterion）的属性名和值，上面的例子可以重写为如下：

~~~
[php]
$post=Post::model()->find(array(
	'select'=>'title',
	'condition'=>'postID=:postID',
	'params'=>array(':postID'=>10),
));
~~~

> Info|信息: 当一个查询条件是关于按指定的值匹配几个列时，我们可以使用
[findByAttributes()|CActiveRecord::findByAttributes]。我们使
`$attributes` 参数是一个以列名做索引的值的数组。在一些框架中，此任务可以通过调用类似
 `findByNameAndTitle` 的方法实现。虽然此方法看起来很诱人，
 但它常常引起混淆，冲突和比如列名大小写敏感的问题。

当有多行数据匹配指定的查询条件时，我们可以通过下面的 `findAll` 方法将他们全部带回。
每个都有其各自的 `find` 方法，就像我们已经讲过的那样。

~~~
[php]
// 查找满足指定条件的所有行
$posts=Post::model()->findAll($condition,$params);
// 查找带有指定主键的所有行
$posts=Post::model()->findAllByPk($postIDs,$condition,$params);
// 查找带有指定属性值的所有行
$posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
// 通过指定的SQL语句查找所有行
$posts=Post::model()->findAllBySql($sql,$params);
~~~

如果没有任何东西符合查询条件，`findAll` 将返回一个空数组。这跟 `find` 不同，`find` 会在没有找到什么东西时返回 null。

除了上面讲述的 `find` 和 `findAll` 方法，为了方便，（Yii）还提供了如下方法：

~~~
[php]
// 获取满足指定条件的行数
$n=Post::model()->count($condition,$params);
// 通过指定的 SQL 获取结果行数
$n=Post::model()->countBySql($sql,$params);
// 检查是否至少有一行复合指定的条件
$exists=Post::model()->exists($condition,$params);
~~~

更新记录
---------------

在 AR 实例填充了列的值之后，我们可以改变它们并把它们存回数据表。

~~~
[php]
$post=Post::model()->findByPk(10);
$post->title='new post title';
$post->save(); // 将更改保存到数据库
~~~

正如我们可以看到的，我们使用同样的 [save()|CActiveRecord::save] 方法执行插入和更新操作。
如果一个 AR 实例是使用 `new` 操作符创建的，调用 [save()|CActiveRecord::save] 将会向数据表中插入一行新数据；
如果 AR 实例是某个 `find` 或 `findAll` 方法的结果，调用 [save()|CActiveRecord::save] 将更新表中现有的行。
实际上，我们是使用 [CActiveRecord::isNewRecord] 说明一个 AR 实例是不是新的。

直接更新数据表中的一行或多行而不首先载入也是可行的。 AR 提供了如下方便的类级别方法实现此目的：

~~~
[php]
// 更新符合指定条件的行
Post::model()->updateAll($attributes,$condition,$params);
// 更新符合指定条件和主键的行
Post::model()->updateByPk($pk,$attributes,$condition,$params);
// 更新满足指定条件的行的计数列
Post::model()->updateCounters($counters,$condition,$params);
~~~

在上面的代码中， `$attributes` 是一个含有以 列名作索引的列值的数组；
`$counters` 是一个由列名索引的可增加的值的数组；`$condition` 和 `$params` 在前面的段落中已有描述。

删除记录
---------------

如果一个 AR 实例被一行数据填充,我们也可以删除此行数据。

~~~
[php]
$post=Post::model()->findByPk(10); // 假设有一个帖子，其 ID 为 10
$post->delete(); // 从数据表中删除此行
~~~

注意，删除之后， AR 实例仍然不变，但数据表中相应的行已经没了。

使用下面的类级别代码，可以无需首先加载行就可以删除它。

~~~
[php]
// 删除符合指定条件的行
Post::model()->deleteAll($condition,$params);
// 删除符合指定条件和主键的行
Post::model()->deleteByPk($pk,$condition,$params);
~~~

数据验证
---------------

当插入或更新一行时，我们常常需要检查列的值是否符合相应的规则。
如果列的值是由最终用户提供的，这一点就更加重要。总体来说，我们永远不能相信任何来自客户端的数据。

当调用 [save()|CActiveRecord::save] 时， AR 会自动执行数据验证。
验证是基于在 AR 类的  [rules()|CModel::rules]  方法中指定的规则进行的。
关于验证规则的更多详情，请参考 [声明验证规则](/doc/guide/form.model#declaring-validation-rules) 一节。
下面是保存记录时所需的典型的工作流。

~~~
[php]
if($post->save())
{
	// 数据有效且成功插入/更新
}
else
{
	// 数据无效，调用  getErrors() 提取错误信息
}
~~~

当要插入或更新的数据由最终用户在一个 HTML 表单中提交时，我们需要将其赋给相应的 AR 属性。
我们可以通过类似如下的方式实现：

~~~
[php]
$post->title=$_POST['title'];
$post->content=$_POST['content'];
$post->save();
~~~

如果有很多列，我们可以看到一个用于这种复制的很长的列表。
这可以通过使用如下所示的 [attributes|CActiveRecord::attributes]  属性简化操作。
更多信息可以在 [安全的特性赋值](/doc/guide/form.model#securing-attribute-assignments)
一节和  [创建动作](/doc/guide/form.action) 一节找到。

~~~
[php]
// 假设 $_POST['Post'] 是一个以列名索引列值为值的数组
$post->attributes=$_POST['Post'];
$post->save();
~~~


对比记录
-----------------

类似于表记录，AR 实例由其主键值来识别。
因此，要对比两个 AR 实例，假设它们属于相同的 AR 类， 我们只需要对比它们的主键值。
然而,一个更简单的方式是调用 [CActiveRecord::equals()]。

> Info|信息: 不同于 AR 在其他框架的执行, Yii 在其 AR 中支持多个主键. 一个复合主键由两个或更多字段构成。相应地，
主键值在 Yii 中表现为一个数组. [primaryKey|CActiveRecord::primaryKey] 属性给出了一个 AR 实例的主键值。


自定义
-------------

[CActiveRecord] 提供了几个占位符方法，它们可以在子类中被覆盖以自定义其工作流。

   - [beforeValidate|CModel::beforeValidate] 和
[afterValidate|CModel::afterValidate]: 这两个将在验证执行之前和之后被调用。

   - [beforeSave|CActiveRecord::beforeSave] 和
[afterSave|CActiveRecord::afterSave]: 这两个将在保存 AR 实例之前和之后被调用。

   - [beforeDelete|CActiveRecord::beforeDelete] 和
[afterDelete|CActiveRecord::afterDelete]: 这两个将在一个 AR 实例被删除之前和之后被调用。

   - [afterConstruct|CActiveRecord::afterConstruct]: 这个将在每个使用 `new` 操作符创建 AR 实例后被调用。

   - [beforeFind|CActiveRecord::beforeFind]: 这个将在一个 AR 查找器被用于执行查询（例如 `find()`, `findAll()`）之前被调用。
1.0.9 版本开始可用。  

   - [afterFind|CActiveRecord::afterFind]: 这个将在每个 AR 实例作为一个查询结果创建时被调用。


使用 AR 处理事务
-------------------------

每个 AR 实例都含有一个属性名叫 [dbConnection|CActiveRecord::dbConnection] ，是一个 [CDbConnection]
的实例，这样我们可以在需要时配合 AR 使用由 Yii DAO 提供的 [事务](/doc/guide/database.dao#using-transactions) 功能:

~~~
[php]
$model=Post::model();
$transaction=$model->dbConnection->beginTransaction();
try
{
	// 查找和保存是可能由另一个请求干预的两个步骤
	// 这样我们使用一个事务以确保其一致性和完整性
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


命名范围
------------

> Note: 对命名范围的支持从版本 1.0.5 开始。
> 命名范围的最初想法来源于 Ruby on Rails.

*命名范围(named scope)* 表示一个 *命名的（named）* 查询规则，它可以和其他命名范围联合使用并应用于 Active Record 查询。

命名范围主要是在 [CActiveRecord::scopes()] 方法中以名字-规则对的方式声明。
如下代码在 `Post` 模型类中声明了两个命名范围, `published` 和 `recently`。

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

每个命名范围声明为一个可用于初始化 [CDbCriteria] 实例的数组。
例如，`recently` 命名范围指定 `order` 属性为 `create_time DESC` ，
 `limit` 属性为 5。他们翻译为查询规则后就会返回最近的5篇帖子。

命名范围多用作 `find` 方法调用的修改器。
几个命名范围可以链到一起形成一个更有约束性的查询结果集。例如，
要找到最近发布的帖子，
我们可以使用如下代码：

~~~
[php]
$posts=Post::model()->published()->recently()->findAll();
~~~

总体来说，命名范围必须出现在一个 `find` 方法调用的左边。
它们中的每一个都提供一个查询规则，并联合到其他规则，
包括传递给 `find` 方法调用的那一个。
最终结果就像给一个查询添加了一系列过滤器。

> Note|注意: 命名范围只能用于类级别方法。也就是说，此方法必须使用 `ClassName::model()` 调用。


### 参数化的命名范围

命名范围可以参数化。例如，
我们想自定义 `recently` 命名范围中指定的帖子数量，要实现此目的，不是在[CActiveRecord::scopes] 方法中声明命名范围，
而是需要定义一个名字和此命名范围的名字相同的方法：

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

然后，我们就可以使用如下语句获取3条最近发布的帖子。

~~~
[php]
$posts=Post::model()->published()->recently(3)->findAll();
~~~

上面的代码中，如果我们没有提供参数 3，我们将默认获取 5 条最近发布的帖子。


### 默认范围

模型类可以有一个默认范围，它将应用于所有
(包括相关的那些) 关于此模型的查询。例如，一个支持多种语言的网站可能只想显示当前用户所指定的语言的内容。
因为可能会有很多关于此网站内容的查询，
我们可以定义一个默认范围以解决此问题。
为实现此目的，我们覆盖 [CActiveRecord::defaultScope] 方法如下：

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

现在，如果下面的方法被调用，将会自动使用上面定义的查询规则：

~~~
[php]
$contents=Content::model()->findAll();
~~~

注意，默认的命名范围只会应用于 `SELECT` 查询。`INSERT`, `UPDATE` 和 `DELETE` 查询将被忽略。

<div class="revision">
Author: qiang.xue <br />
Translators: riverlet, dongbeta <br />
ID: $Id$
<div>