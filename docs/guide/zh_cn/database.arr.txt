关系型 Active Record
========================

我们已经了解了怎样使用  Active Record (AR) 从单个数据表中获取数据。
在本节中，我们讲解怎样使用 AR 连接多个相关数据表并取回关联（join）后的数据集。

为了使用关系型 AR，我们建议在需要关联的表中定义主键-外键约束。这些约束可以帮助保证相关数据的一致性和完整性。

为简单起见，我们使用如下所示的实体-关系（ER）图中的数据结构演示此节中的例子。

![ER Diagram](er.png)

> Info|信息: 对外键约束的支持在不同的 DBMS 中是不一样的。
> SQLite < 3.6.19 不支持外键约束，但你依然可以在建表时声明约束。


声明关系
----------------------

在我们使用 AR 执行关联查询之前，我们需要让 AR 知道一个 AR 类是怎样关联到另一个的。

两个 AR 类之间的关系直接通过 AR 类所代表的数据表之间的关系相关联。
从数据库的角度来说，表 A 和  B 之间有三种关系：一对多（one-to-many，例如 `tbl_user` 和 `tbl_post`），一对一（ one-to-one 例如
`tbl_user` 和 `tbl_profile`）和 多对多（many-to-many 例如 `tbl_category` 和 `tbl_post`）。
在 AR 中，有四种关系：

   - `BELONGS_TO`（属于）: 如果表 A 和 B 之间的关系是一对多，则 表 B 属于 表 A (例如 `Post` 属于 `User`);

   - `HAS_MANY`（有多个）: 如果表 A 和 B 之间的关系是一对多，则 A 有多个 B (例如 `User` 有多个 `Post`);

   - `HAS_ONE`（有一个）: 这是 `HAS_MANY` 的一个特例，A 最多有一个 B (例如 `User` 最多有一个 `Profile`);

   - `MANY_MANY`: 这个对应于数据库中的 多对多 关系。 由于多数 DBMS 不直接支持 多对多 关系，因此需要有一个关联表将 多对多 关系分割为 一对多 关系。
在我们的示例数据结构中，`tbl_post_category` 就是用于此目的的。在 AR 术语中，我们可以解释 `MANY_MANY` 为  `BELONGS_TO` 和 `HAS_MANY` 的组合。
例如，`Post` 属于多个（belongs to many） `Category` ，`Category` 有多个（has many） `Post`.

AR 中定义关系需要覆盖 [CActiveRecord] 中的 [relations()|CActiveRecord::relations] 方法。此方法返回一个关系配置数组。每个数组元素通过如下格式表示一个单一的关系。

~~~
[php]
'VarName'=>array('RelationType', 'ClassName', 'ForeignKey', ...additional options)
~~~

其中 `VarName` 是关系的名字；`RelationType` 指定关系类型，可以是一下四个常量之一：
`self::BELONGS_TO`, `self::HAS_ONE`, `self::HAS_MANY` and
`self::MANY_MANY`；`ClassName` 是此 AR 类所关联的 AR 类的名字；
`ForeignKey` 指定关系中使用的外键（一个或多个）。额外的选项可以在每个关系的最后指定（稍后详述）。

以下代码演示了怎样定义 `User` 和 `Post` 类的关系：

~~~
[php]
class Post extends CActiveRecord
{
	......

	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO, 'User', 'author_id'),
			'categories'=>array(self::MANY_MANY, 'Category',
				'tbl_post_category(post_id, category_id)'),
		);
	}
}

class User extends CActiveRecord
{
	......

	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'author_id'),
			'profile'=>array(self::HAS_ONE, 'Profile', 'owner_id'),
		);
	}
}
~~~

> Info|信息: 外键可能是复合的，包含两个或更多个列。
这种情况下，我们应该将这些外键名字链接，中间用空格或逗号分割。对于 `MANY_MANY` 关系类型，
关联表的名字必须也必须在外键中指定。例如， `Post` 中的 `categories` 关系由外键 `tbl_post_category(post_id, category_id)` 指定。

AR 类中的关系定义为每个关系向类中隐式添加了一个属性。在一个关联查询执行后，相应的属性将将被以关联的 AR 实例填充。
例如，如果 `$author` 代表一个 `User` AR 实例，
我们可以使用 `$author->posts` 访问其关联的 `Post` 实例。

执行关联查询
---------------------------

执行关联查询最简单的方法是读取一个 AR 实例中的关联属性。如果此属性以前没有被访问过，则一个关联查询将被初始化，它将两个表关联并使用当前 AR 实例的主键过滤。
查询结果将以所关联 AR 类的实例的方式保存到属性中。这就是传说中的 *懒惰式加载（lazy loading，也可译为 迟加载）* 方式，例如，关联查询只在关联的对象首次被访问时执行。
下面的例子演示了怎样使用这种方式：

~~~
[php]
// 获取 ID 为 10 的帖子
$post=Post::model()->findByPk(10);
// 获取帖子的作者(author): 此处将执行一个关联查询。
$author=$post->author;
~~~

> Info|信息: 如果关系中没有相关的实例，则相应的属性将为 null 或一个空数组。
`BELONGS_TO` 和 `HAS_ONE` 关系的结果是 null，
`HAS_MANY` 和 `MANY_MANY` 的结果是一个空数组。
注意， `HAS_MANY` 和 `MANY_MANY` 关系返回对象数组，你需要在访问任何属性之前先遍历这些结果。
否则，你可能会收到 "Trying to get property of non-object（尝试访问非对象的属性）" 错误。

懒惰式加载用起来很方便，但在某些情况下并不高效。如果我们想获取 `N` 个帖子的作者，使用这种懒惰式加载将会导致执行 `N` 个关联查询。
这种情况下，我们应该改为使用 *渴求式加载（eager loading）*方式。

渴求式加载方式会在获取主 AR 实例的同时获取关联的 AR 实例。
这是通过在使用 AR 中的 [find|CActiveRecord::find] 或 [findAll|CActiveRecord::findAll] 方法时配合使用 with 方法完成的。例如：

~~~
[php]
$posts=Post::model()->with('author')->findAll();
~~~

上述代码将返回一个 `Post` 实例的数组。与懒惰式加载方式不同，在我们访问每个 `Post` 实例中的 `author` 属性之前，它就已经被关联的 `User` 实例填充了。
渴求式加载通过 **一个** 关联查询返回所有帖子及其作者，而不是对每个帖子执行一次关联查询。

我们可以在
[with()|CActiveRecord::with] 方法中指定多个关系名字，渴求式加载将一次性全部取回他们。例如，如下代码会将帖子连同其作者和分类一并取回。

~~~
[php]
$posts=Post::model()->with('author','categories')->findAll();
~~~

我们也可以实现嵌套的渴求式加载。像下面这样，
我们传递一个分等级的关系名表达式到  [with()|CActiveRecord::with] 方法，而不是一个关系名列表:

~~~
[php]
$posts=Post::model()->with(
	'author.profile',
	'author.posts',
	'categories')->findAll();
~~~

上述示例将取回所有帖子及其作者和所属分类。它还同时取回每个作者的简介（author.profile）和帖子（author.posts）。

从版本 1.1.0 开始，渴求式加载也可以通过指定 [CDbCriteria::with] 的属性执行，就像下面这样：

~~~
[php]
$criteria=new CDbCriteria;
$criteria->with=array(
	'author.profile',
	'author.posts',
	'categories',
);
$posts=Post::model()->findAll($criteria);
~~~

或者

~~~
[php]
$posts=Post::model()->findAll(array(
	'with'=>array(
		'author.profile',
		'author.posts',
		'categories',
	)
);
~~~


关系型查询选项
------------------------

我们提到在关系声明时可以指定附加的选项。这些 名-值 对形式的选项用于自定义关系型查询。概括如下：

   - `select`: 关联的 AR 类中要选择(select)的列的列表。
默认为 '*'，即选择所有列。此选项中的列名应该是已经消除歧义的。

   - `condition`: 即 `WHERE` 条件。默认为空。此选项中的列名应该是已经消除歧义的。

   - `params`: 要绑定到所生成的 SQL 语句的参数。应该以 名-值 对数组的形式赋值。此选项从 1.0.3 版起有效。

   - `on`: 即 `ON` 语句。此处指定的条件将会通过 `AND` 操作符附加到 join 条件中。此选项中的列名应该是已经消除歧义的。
此选项不会应用到 `MANY_MANY` 关系中。此选项从 1.0.2 版起有效。

   - `order`: 即 `ORDER BY` 语句。默认为空。 此选项中的列名应该是已经消除歧义的。

   - `with`: a list of child related objects that should be loaded
together with this object. Be aware that using this option inappropriately
may form an infinite relation loop.

   - `joinType`: type of join for this relationship. It defaults to `LEFT
OUTER JOIN`.

   - `alias`: the alias for the table associated with this relationship.
This option has been available since version 1.0.1. It defaults to null,
meaning the table alias is the same as the relation name.

   - `together`: whether the table associated with this relationship should
be forced to join together with the primary table and other tables.
This option is only meaningful for `HAS_MANY` and `MANY_MANY` relations.
If this option is set false, the table associated with the `HAS_MANY` or `MANY_MANY`
relation will be joined with the primary table in a separate SQL query, which
may improve the overall query performance since less duplicated data is returned.
If this option is set true, the associated table will always be joined with the
primary table in a single SQL query, even if the primary table is paginated.
If this option is not set, the associated table will be joined with the
primary table in a single SQL query only when the primary table is not paginated.
For more details, see the section "Relational Query Performance".
This option has been available since version 1.0.3.

   - `join`: the extra `JOIN` clause. It defaults to empty. This option
has been available since version 1.1.3.

   - `group`: the `GROUP BY` clause. It defaults to empty. Column names
referenced in this option should be disambiguated.

   - `having`: the `HAVING` clause. It defaults to empty. Column names
referenced in this option should be disambiguated.
Note: option has been available since version 1.0.1.

   - `index`: the name of the column whose values should be used as keys
of the array that stores related objects. Without setting this option,
an related object array would use zero-based integer index.
This option can only be set for `HAS_MANY` and `MANY_MANY` relations.
This option has been available since version 1.0.7.


In addition, the following options are available for certain relationships
during lazy loading:

   - `limit`: limit of the rows to be selected. This option does NOT apply
to `BELONGS_TO` relation.

   - `offset`: offset of the rows to be selected. This option does NOT
apply to `BELONGS_TO` relation.

Below we modify the `posts` relationship declaration in the `User` by
including some of the above options:

~~~
[php]
class User extends CActiveRecord
{
	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'author_id',
							'order'=>'posts.create_time DESC',
							'with'=>'categories'),
			'profile'=>array(self::HAS_ONE, 'Profile', 'owner_id'),
		);
	}
}
~~~

Now if we access `$author->posts`, we would obtain the author's posts
sorted according to their creation time in descending order. Each post
instance also has its categories loaded.


Disambiguating Column Names
---------------------------

When a column name appears in two or more tables being joined
together, it needs to be disambiguated. This is done by prefixing the
column name with its table's alias name.

In relational AR query, the alias name for the primary table is fixed as `t`,
while the alias name for a relational table
is the same as the corresponding relation name by default. For example,
in the following statement, the alias name for `Post` and `Comment` is
`t` and `comments`, respectively:

~~~
[php]
$posts=Post::model()->with('comments')->findAll();
~~~

Now assume both `Post` and `Comment` have a column called `create_time` indicating
the creation time of a post or comment, and we would like to fetch posts together
with their comments by ordering first the posts' creation time and then the comments'
creation time. We need to disambiguate the `create_time` column like the following:

~~~
[php]
$posts=Post::model()->with('comments')->findAll(array(
	'order'=>'t.create_time, comments.create_time'
));
~~~

> Note: the behavior of column disambiguation has been changed since version 1.1.0.
> Previously in version 1.0.x, by default Yii would automatically generate a table
> alias for each relational table, and we had to use the prefix `??.` to refer to
> this automatically generated alias. Also, in version 1.0.x, the alias name
> of the primary table is the table name itself.


Dynamic Relational Query Options
--------------------------------

Starting from version 1.0.2, we can use dynamic relational query options
in both [with()|CActiveRecord::with] and the `with` option. The dynamic
options will overwrite existing options as specified in the [relations()|CActiveRecord::relations]
method. For example, with the above `User` model, if we want to use eager
loading approach to bring back posts belonging to an author in *ascending order*
(the `order` option in the relation specification is descending order), we
can do the following:

~~~
[php]
User::model()->with(array(
	'posts'=>array('order'=>'posts.create_time ASC'),
	'profile',
))->findAll();
~~~

Starting from version 1.0.5, dynamic query options can also be used when using the lazy loading approach to perform relational query. To do so, we should call a method whose name is the same as the relation name and pass the dynamic query options as the method parameter. For example, the following code returns a user's posts whose `status` is 1:

~~~
[php]
$user=User::model()->findByPk(1);
$posts=$user->posts(array('condition'=>'status=1'));
~~~


Relational Query Performance
----------------------------

As we described above, the eager loading approach is mainly used in the scenario
when we need to access many related objects. It generates a big complex SQL statement
by joining all needed tables. A big SQL statement is preferrable in many cases
since it simplifies filtering based on a column in a related table.
It may not be efficient in some cases, however.

Consider an example where we need to find the latest posts together with their comments.
Assuming each post has 10 comments, using a single big SQL statement, we will bring back
a lot of redundant post data since each post will be repeated for every comment it has.
Now let's try another approach: we first query for the latest posts, and then query for their comments.
In this new approach, we need to execute two SQL statements. The benefit is that there is
no redundancy in the query results.

So which approach is more efficient? There is no absolute answer. Executing a single big SQL statement
may be more efficient because it causes less overhead in DBMS for yparsing and executing
the SQL statements. On the other hand, using the single SQL statement, we end up with more redundant data
and thus need more time to read and process them.

For this reason, Yii provides the `together` query option so that we choose between the two approaches as needed.
By default, Yii adopts the first approach, i.e., generating a single SQL statement to perform
eager loading. We can set the `together` option to be false in the relation declarations so that some of
tables are joined in separate SQL statements. For example, in order to use the second approach
to query for the latest posts with their comments, we can declare the `comments` relation
in `Post` class as follows,

~~~
[php]
public function relations()
{
	return array(
		'comments' => array(self::HAS_MANY, 'Comment', 'post_id', 'together'=>false),
	);
}
~~~

We can also dynamically set this option when we perform the eager loading:

~~~
[php]
$posts = Post::model()->with(array('comments'=>array('together'=>false)))->findAll();
~~~

> Note: In version 1.0.x, the default behavior is that Yii will generate and
> execute `N+1` SQL statements if there are `N` `HAS_MANY` or `MANY_MANY` relations.
> Each `HAS_MANY` or `MANY_MANY` relation has its own SQL statement. By calling
> the `together()` method after `with()`, we can enforce only a single SQL statement
> is generated and executed. For example,
>
> ~~~
> [php]
> $posts=Post::model()->with(
> 	'author.profile',
> 	'author.posts',
> 	'categories')->together()->findAll();
> ~~~
>


Statistical Query
-----------------

> Note: Statistical query has been supported since version 1.0.4.

Besides the relational query described above, Yii also supports the so-called statistical query (or aggregational query). It refers to retrieving the aggregational information about the related objects, such as the number of comments for each post, the average rating for each product, etc. Statistical query can only be performed for objects related in `HAS_MANY` (e.g. a post has many comments) or `MANY_MANY` (e.g. a post belongs to many categories and a category has many posts).

Performing statistical query is very similar to performing relation query as we described before. We first need to declare the statistical query in the [relations()|CActiveRecord::relations] method of [CActiveRecord] like we do with relational query.

~~~
[php]
class Post extends CActiveRecord
{
	public function relations()
	{
		return array(
			'commentCount'=>array(self::STAT, 'Comment', 'post_id'),
			'categoryCount'=>array(self::STAT, 'Category', 'post_category(post_id, category_id)'),
		);
	}
}
~~~

In the above, we declare two statistical queries: `commentCount` calculates the number of comments belonging to a post, and `categoryCount` calculates the number of categories that a post belongs to. Note that the relationship between `Post` and `Comment` is `HAS_MANY`, while the relationship between `Post` and `Category` is `MANY_MANY` (with the joining table `post_category`). As we can see, the declaration is very similar to those relations we described in earlier subsections. The only difference is that the relation type is `STAT` here.


With the above declaration, we can retrieve the number of comments for a post using the expression `$post->commentCount`. When we access this property for the first time, a SQL statement will be executed implicitly to retrieve the corresponding result. As we already know, this is the so-called *lazy loading* approach. We can also use the *eager loading* approach if we need to determine the comment count for multiple posts:

~~~
[php]
$posts=Post::model()->with('commentCount', 'categoryCount')->findAll();
~~~

The above statement will execute three SQLs to bring back all posts together with their comment counts and category counts. Using the lazy loading approach, we would end up with `2*N+1` SQL queries if there are `N` posts.

By default, a statistical query will calculate the `COUNT` expression (and thus the comment count and category count in the above example). We can customize it by specifying additional options when we declare it in [relations()|CActiveRecord::relations]. The available options are summarized as below.

   - `select`: the statistical expression. Defaults to `COUNT(*)`, meaning the count of child objects.

   - `defaultValue`: the value to be assigned to those records that do not receive a statistical query result. For example, if a post does not have any comments, its `commentCount` would receive this value. The default value for this option is 0.

   - `condition`: the `WHERE` clause. It defaults to empty.

   - `params`: the parameters to be bound to the generated SQL statement.
This should be given as an array of name-value pairs.

   - `order`: the `ORDER BY` clause. It defaults to empty.

   - `group`: the `GROUP BY` clause. It defaults to empty.

   - `having`: the `HAVING` clause. It defaults to empty.


Relational Query with Named Scopes
----------------------------------

> Note: The support for named scopes has been available since version 1.0.5.

Relational query can also be performed in combination with [named scopes](/doc/guide/database.ar#named-scopes). It comes in two forms. In the first form, named scopes are applied to the main model. In the second form, named scopes are applied to the related models.

The following code shows how to apply named scopes to the main model.

~~~
[php]
$posts=Post::model()->published()->recently()->with('comments')->findAll();
~~~

This is very similar to non-relational queries. The only difference is that we have the `with()` call after the named-scope chain. This query would bring back recently published posts together with their comments.

And the following code shows how to apply named scopes to the related models.

~~~
[php]
$posts=Post::model()->with('comments:recently:approved')->findAll();
~~~

The above query will bring back all posts together with their approved comments. Note that `comments` refers to the relation name, while `recently` and `approved` refer to two named scopes declared in the `Comment` model class. The relation name and the named scopes should be separated by colons.

Named scopes can also be specified in the `with` option of the relational rules declared in [CActiveRecord::relations()]. In the following example, if we access `$user->posts`, it would bring back all *approved* comments of the posts.

~~~
[php]
class User extends CActiveRecord
{
	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'author_id',
				'with'=>'comments:approved'),
		);
	}
}
~~~

> Note: Named scopes applied to related models must be specified in [CActiveRecord::scopes]. As a result, they cannot be parameterized.


<div class="revision">$Id: database.arr.txt 2350 2010-08-28 18:57:21Z qiang.xue, translated by riverlet $</div>