数据访问对象 (DAO)
=========================

数据访问对象（DAO） 对访问存储在不同数据库管理系统（DBMS）中的数据提供了一个通用的API。
因此，在将底层 DBMS 更换为另一个时，无需修改使用了 DAO 访问数据的代码。

Yii DAO 基于 [PHP Data Objects
(PDO)](http://php.net/manual/en/book.pdo.php) 构建。它是一个为众多流行的DBMS提供统一数据访问的扩展，这些 DBMS 包括 MySQL，
PostgreSQL 等等。因此，要使用 Yii DAO，PDO 扩展和特定的 PDO 数据库驱动(例如 `PDO_MYSQL`) 必须安装。

Yii DAO 主要包含如下四个类：

   - [CDbConnection]: 代表一个数据库连接。
   - [CDbCommand]: 代表一条通过数据库执行的 SQL 语句。
   - [CDbDataReader]: 代表一个只向前移动的，来自一个查询结果集中的行的流。
   - [CDbTransaction]: 代表一个数据库事务。

下面，我们介绍 Yii DAO 在不同场景中的应用。

建立数据库连接
--------------------------------

要建立一个数据库连接，创建一个 [CDbConnection] 实例并将其激活。
连接到数据库需要一个数据源的名字（DSN）以指定连接信息。用户名和密码也可能会用到。
当连接到数据库的过程中发生错误时 (例如，错误的 DSN 或无效的用户名/密码)，将会抛出一个异常。

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// 建立连接。你可以使用  try...catch 捕获可能抛出的异常
$connection->active=true;
......
$connection->active=false;  // 关闭连接
~~~

DSN 的格式取决于所使用的 PDO 数据库驱动。总体来说，
DSN 要含有 PDO 驱动的名字，跟上一个冒号，再跟上驱动特定的连接语法。可查阅 [PDO
文档](http://www.php.net/manual/en/pdo.construct.php) 获取更多信息。
下面是一个常用DSN格式的列表。

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`
   - SQL Server: `mssql:host=localhost;dbname=testdb`
   - Oracle: `oci:dbname=//localhost:1521/testdb`

由于 [CDbConnection] 继承自 [CApplicationComponent]，我们也可以将其作为一个 [应用组件](/doc/guide/basics.application#application-component)
使用。要这样做的话，
请在 [应用配置](/doc/guide/basics.application#application-configuration) 
中配置一个 `db` （或其他名字）应用组件如下：

~~~
[php]
array(
	......
	'components'=>array(
		......
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'mysql:host=localhost;dbname=testdb',
			'username'=>'root',
			'password'=>'password',
			'emulatePrepare'=>true,  // needed by some MySQL installations
		),
	),
)
~~~

然后我们就可以通过 `Yii::app()->db` 访问数据库连接了。它已经被自动激活了，除非我们特意配置了 
[CDbConnection::autoConnect] 为 false。通过这种方式，这个单独的DB连接就可以在我们代码中的很多地方共享。

执行 SQL 语句
------------------------

数据库连接建立后，SQL 语句就可以通过使用 [CDbCommand] 执行了。你可以通过使用指定的SQL语句作为参数调用 
[CDbConnection::createCommand()] 创建一个 [CDbCommand] 实例。

~~~
[php]
$connection=Yii::app()->db;   // 假设你已经建立了一个 "db" 连接
// 如果没有，你可能需要显式建立一个连接：
// $connection=new CDbConnection($dsn,$username,$password);
$command=$connection->createCommand($sql);
// 如果需要，此 SQL 语句可通过如下方式修改：
// $command->text=$newSQL;
~~~

一条 SQL 语句会通过 [CDbCommand] 以如下两种方式被执行：

   - [execute()|CDbCommand::execute]: 执行一个无查询 （non-query）SQL语句，
例如 `INSERT`, `UPDATE` 和 `DELETE` 。如果成功，它将返回此执行所影响的行数。

   - [query()|CDbCommand::query]: 执行一条会返回若干行数据的 SQL 语句，例如 `SELECT`。
如果成功，它将返回一个  [CDbDataReader] 实例，通过此实例可以遍历数据的结果行。为简便起见，
（Yii）还实现了一系列 `queryXXX()` 方法以直接返回查询结果。

执行 SQL 语句时如果发生错误，将会抛出一个异常。

~~~
[php]
$rowCount=$command->execute();   // 执行无查询 SQL
$dataReader=$command->query();   // 执行一个 SQL 查询
$rows=$command->queryAll();      // 查询并返回结果中的所有行
$row=$command->queryRow();       // 查询并返回结果中的第一行
$column=$command->queryColumn(); // 查询并返回结果中的第一列
$value=$command->queryScalar();  // 查询并返回结果中第一行的第一个字段
~~~

获取查询结果
----------------------

在 [CDbCommand::query()] 生成 [CDbDataReader] 实例之后，你可以通过重复调用
[CDbDataReader::read()] 获取结果中的行。你也可以在 PHP 的 `foreach` 语言结构中使用
[CDbDataReader] 一行行检索数据。 

~~~
[php]
$dataReader=$command->query();
// 重复调用 read() 直到它返回 false
while(($row=$dataReader->read())!==false) { ... }
// 使用 foreach 遍历数据中的每一行
foreach($dataReader as $row) { ... }
// 一次性提取所有行到一个数组
$rows=$dataReader->readAll();
~~~

> Note|注意: 不同于 [query()|CDbCommand::query], 所有的 `queryXXX()` 方法会直接返回数据。
例如， [queryRow()|CDbCommand::queryRow] 会返回代表查询结果第一行的一个数组。

使用事务
------------------

当一个应用要执行几条查询，每条查询要从数据库中读取并/或向数据库中写入信息时，
保证数据库没有留下几条查询而只执行了另外几条查询是非常重要的。
事务，在 Yii 中表现为 [CDbTransaction] 实例，可能会在下面的情况中启动：

   - 开始事务.
   - 一个个执行查询。任何对数据库的更新对外界不可见。
   - 提交事务。如果事务成功，更新变为可见。
   - 如果查询中的一个失败，整个事务回滚。

上述工作流可以通过如下代码实现：

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... other SQL executions
	$transaction->commit();
}
catch(Exception $e) // 如果有一条查询失败，则会抛出异常
{
	$transaction->rollBack();
}
~~~

绑定参数
------------------

要避免 [SQL 注入攻击](http://en.wikipedia.org/wiki/SQL_injection) 并提高重复执行的 SQL 语句的效率，
你可以 "准备（prepare）"一条含有可选参数占位符的 SQL 语句，在参数绑定时，这些占位符将被替换为实际的参数。

参数占位符可以是命名的 (表现为一个唯一的标记) 或未命名的 (表现为一个问号)。调用
[CDbCommand::bindParam()] 或 [CDbCommand::bindValue()] 以使用实际参数替换这些占位符。
这些参数不需要使用引号引起来：底层的数据库驱动会为你搞定这个。
参数绑定必须在 SQL 语句执行之前完成。

~~~
[php]
// 一条带有两个占位符 ":username" 和 ":email"的 SQL
$sql="INSERT INTO tbl_user (username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// 用实际的用户名替换占位符 ":username" 
$command->bindParam(":username",$username,PDO::PARAM_STR);
// 用实际的 Email 替换占位符 ":email" 
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// 使用新的参数集插入另一行
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

方法 [bindParam()|CDbCommand::bindParam] 和
[bindValue()|CDbCommand::bindValue] 非常相似。唯一的区别就是前者使用一个 PHP 变量绑定参数，
而后者使用一个值。对于那些内存中的大数据块参数，处于性能的考虑，应优先使用前者。

关于绑定参数的更多信息，请参考 [相关的PHP文档](http://www.php.net/manual/en/pdostatement.bindparam.php)。

绑定列
---------------

当获取查询结果时，你也可以使用 PHP 变量绑定列。
这样在每次获取查询结果中的一行时就会自动使用最新的值填充。

~~~
[php]
$sql="SELECT username, email FROM tbl_user";
$dataReader=$connection->createCommand($sql)->query();
// 使用 $username 变量绑定第一列 (username) 
$dataReader->bindColumn(1,$username);
// 使用 $email 变量绑定第二列 (email) 
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username 和 $email 含有当前行中的 username 和 email 
}
~~~

使用表前缀
------------------

从版本 1.1.0 起， Yii 提供了集成了对使用表前缀的支持。
表前缀是指在当前连接的数据库中的数据表的名字前面添加的一个字符串。
它常用于共享的服务器环境，这种环境中多个应用可能会共享同一个数据库，要使用不同的表前缀以相互区分。
例如，一个应用可以使用 `tbl_` 作为表前缀而另一个可以使用 `yii_`。

要使用表前缀，配置 [CDbConnection::tablePrefix] 属性为所希望的表前缀。
然后，在 SQL 语句中使用 `{{TableName}}` 代表表的名字，其中的 `TableName` 是指不带前缀的表名。
例如，如果数据库含有一个名为 `tbl_user` 的表，而 `tbl_` 被配置为表前缀，那我们就可以使用如下代码执行用户相关的查询：

~~~
[php]
$sql='SELECT * FROM {{user}}';
$users=$connection->createCommand($sql)->queryAll();
~~~

<div class="revision">$Id: database.dao.txt 2266 2010-07-17 13:58:30Z qiang.xue , translated by riverlet $</div>