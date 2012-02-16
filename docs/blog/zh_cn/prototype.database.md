建立数据库
===================

完成了程序骨架和数据库设计，在这一节里我们将创建博客的数据库并将其连接到程序骨架中。


创建数据库
-----------------

我们选择创建一个SQLite数据库。由于Yii中的数据库支持是建立在 [PDO](http://www.php.net/manual/en/book.pdo.php) 之上的，我们可以很容易地切换到一个不同的 DBMS (例如 MySQL, PostgreSQL) 而不需要修改我们的应用代码。

我们把数据库文件 `blog.db` 建立在 `/wwwroot/blog/protected/data` 中。注意，数据库文件和其所在的目录都必须对Web服务器进程可写，这是SQLite的要求。我们可以简单的从博客演示中复制这个数据库文件，它位于 `/wwwroot/yii/demos/blog/protected/data/blog.db`。我们也可以通过执行 `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql` 文件中的SQL语句自己创建这个数据库。

> Tip|提示: 要执行SQL语句，我们可以使用 `sqlite3` 命令行工具。它可以在 [SQLite 官方网站](http://www.sqlite.org/download.html) 中找到。


建立数据库连接
--------------------------------

要在我们创建的程序骨架中使用这个数据库，我们需要修改它的[应用配置](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) ，它保存在PHP脚本 `/wwwroot/blog/protected/config/main.php` 中。此脚本返回一个包含键值对的关联数组，它们中的每一项被用来初始化[应用实例](http://www.yiiframework.com/doc/guide/basics.application) 中的可写属性。

我们按如下方式配置 `db` 组件,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:/wwwroot/blog/protected/data/blog.db',
			'tablePrefix'=>'tbl_',
		),
	),
	......
);
~~~

上述配置的意思是说我们有一个 `db` [应用组件](http://www.yiiframework.com/doc/guide/basics.application#application-component) ，它的 `connectionString` 属性应当以 `sqlite:/wwwroot/blog/protected/data/blog.db` 这个值初始化，它的 `tablePrefix` 属性应该是 `tbl_`。

通过这个配置，我们就可以在代码的任意位置使用 `Yii::app()->db` 来访问数据库连接对象了。注意， `Yii::app()` 会返回我们在入口脚本中创建的应用实例。如果你对数据库连接的其他可用的方法和属性感兴趣，可以阅读 [类参考|CDbConnection]。然而，在多数情况下，我们并不会直接使用这个数据库连接。而是使用被称为 [ActiveRecord](http://www.yiiframework.com/doc/guide/database.ar) 的东西来访问数据库。

我们想对配置中的 `tablePrefix` 属性再解释一点。此属性告诉 `db` 连接它应该关注我们使用了 `tbl_` 作为数据库表前缀。具体来说，如果一条SQL语句中含有一个被双大括号括起来的标记 (例如 `{{post}}`)，那么 `db` 连接应该在把它提交给DBMS执行前，先将其翻译成带有表前缀的名字 (例如 `tbl_post`) 。这个特性非常有用，如果将来我们需要修改表前缀，就不需要再动代码了。例如，如果我们正在开发一个通用内容管理系统 (CMS)，我们就可以利用此特性，这样当它被安装在一个不同的环境中时，我们就能允许用户选择一个他们喜欢的表前缀。

> Tip|提示: 如果你想使用MySQL而不是SQLite来存储数据，你可以使用位于
> `/wwwroot/yii/demos/blog/protected/data/schema.mysql.sql` 文件
> 中的SQL语句创建一个名为 `blog` 的 MySQL 数据库。然后，按如下方式
> 修改应用配置,
>
> ~~~
> [php]
> return array(
>     ......
>     'components'=>array(
>         ......
>         'db'=>array(
>             'connectionString' => 'mysql:host=localhost;dbname=blog',
>             'emulatePrepare' => true,
>             'username' => 'root',
>             'password' => '',
>             'charset' => 'utf8',
>             'tablePrefix' => 'tbl_',
>         ),
>     ),
> 	......
> );
> ~~~


<div class="revision">$Id: prototype.database.txt 2332 2010-08-24 20:55:36Z mdomba $</div>