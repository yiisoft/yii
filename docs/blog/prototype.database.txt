Setting Up Database
===================

Having created a skeleton application and finished the database design, in this section we will create the blog database and establish the connection to it in the skeleton application.


Creating Database
-----------------

We choose to create a SQLite database. Because the database support in Yii is built on top of [PDO](http://www.php.net/manual/en/book.pdo.php), we can easily switch to use a different type of DBMS (e.g. MySQL, PostgreSQL) without the need to change our application code.

We create the database file `blog.db` under the directory `/wwwroot/blog/protected/data`. Note that both the directory and the database file have to be writable by the Web server process, as required by SQLite. We may simply copy the database file from the blog demo in our Yii installation which is located at `/wwwroot/yii/demos/blog/protected/data/blog.db`. We may also generate the database by executing the SQL statements in the file `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql`.

> Tip: To execute SQL statements, we may use the `sqlite3` command line tool that can be found in [the SQLite official website](http://www.sqlite.org/download.html).


Establishing Database Connection
--------------------------------

To use the blog database in the skeleton application we created, we need to modify its [application configuration](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) which is stored in the PHP script `/wwwroot/blog/protected/config/main.php`. The script returns an associative array consisting of name-value pairs, each of which is used to initialize a writable property of the [application instance](http://www.yiiframework.com/doc/guide/basics.application).

We configure the `db` component as follows,

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

The above configuration says that we have a `db` [application component](http://www.yiiframework.com/doc/guide/basics.application#application-component) whose `connectionString` property should be initialized as `sqlite:/wwwroot/blog/protected/data/blog.db` and whose `tablePrefix` property should be `tbl_`.

With this configuration, we can access the DB connection object using `Yii::app()->db` at any place in our code. Note that `Yii::app()` returns the application instance that we create in the entry script. If you are interested in possible methods and properties that the DB connection has, you may refer to its [class reference|CDbConnection]. However, in most cases we are not going to use this DB connection directly. Instead, we will use the so-called [ActiveRecord](http://www.yiiframework.com/doc/guide/database.ar) to access the database.

We would like to explain a bit more about the `tablePrefix` property that we set in the configuration. This tells the `db` connection that it should respect the fact we are using `tbl_` as the prefix to our database table names. In particular, if in a SQL statement there is a token enclosed within double curly brackets (e.g. `{{post}}`), then the `db` connection should translate it into a name with the table prefix (e.g. `tbl_post`) before sending it to DBMS for execution. This feature is especially useful if in future we need to modify the table name prefix without touching our source code. For example, if we are developing a generic content management system (CMS), we may exploit this feature so that when it is being installed in a new environment, we can allow users to choose a table prefix they like.

> Tip: If you want to use MySQL instead of SQLite to store data, you may create
> a MySQL database named `blog` using the SQL statements in
> `/wwwroot/yii/demos/blog/protected/data/schema.mysql.sql`. Then, modify the
> application configuration as follows,
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


<div class="revision">$Id$</div>