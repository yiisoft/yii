Data Access Objects (DAO)
=========================

Data Access Objects (DAO) provides a generic API to access data stored in
different database management systems (DBMS). As a result, the underlying
DBMS can be changed to a different one without requiring change of the code
which uses DAO to access the data.

Yii DAO is built on top of [PHP Data Objects
(PDO)](http://php.net/manual/en/book.pdo.php) which is an extension
providing unified data access to many popular DBMS, such as MySQL,
PostgreSQL. Therefore, to use Yii DAO, the PDO extension and the specific
PDO database driver (e.g. `PDO_MYSQL`) have to be installed.

Yii DAO mainly consists of the following four classes:

   - [CDbConnection]: represents a connection to a database.
   - [CDbCommand]: represents an SQL statement to execute against a database.
   - [CDbDataReader]: represents a forward-only stream of rows from a query result set.
   - [CDbTransaction]: represents a DB transaction.

In the following, we introduce the usage of Yii DAO in different
scenarios.

Establishing Database Connection
--------------------------------

To establish a database connection, create a [CDbConnection] instance and
activate it. A data source name (DSN) is needed to specify the information
required to connect to the database. A username and password may also be
needed to establish the connection. An exception will be raised in case an
error occurs during establishing the connection (e.g. bad DSN or invalid
username/password).

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// establish connection. You may try...catch possible exceptions
$connection->active=true;
......
$connection->active=false;  // close connection
~~~

The format of DSN depends on the PDO database driver in use. In general, a
DSN consists of the PDO driver name, followed by a colon, followed by the
driver-specific connection syntax. See [PDO
documentation](http://www.php.net/manual/en/pdo.construct.php) for complete
information. Below is a list of commonly used DSN formats:

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`
   - SQL Server: `mssql:host=localhost;dbname=testdb`
   - Oracle: `oci:dbname=//localhost:1521/testdb`

Because [CDbConnection] extends from [CApplicationComponent], we can also
use it as an [application
component](/doc/guide/basics.application#application-component). To do so, configure
in a `db` (or other name) application component in the [application
configuration](/doc/guide/basics.application#application-configuration) as follows,

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

We can then access the DB connection via `Yii::app()->db` which is already
activated automatically, unless we explictly configure
[CDbConnection::autoConnect] to be false. Using this approach, the single
DB connection can be shared in multiple places in our code.

Executing SQL Statements
------------------------

Once a database connection is established, SQL statements can be executed
using [CDbCommand]. One creates a [CDbCommand] instance by calling
[CDbConnection::createCommand()] with the specified SQL statement:

~~~
[php]
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
// If not, you may explicitly create a connection:
// $connection=new CDbConnection($dsn,$username,$password);
$command=$connection->createCommand($sql);
// if needed, the SQL statement may be updated as follows:
// $command->text=$newSQL;
~~~

A SQL statement is executed via [CDbCommand] in one of the following two
ways:

   - [execute()|CDbCommand::execute]: performs a non-query SQL statement,
such as `INSERT`, `UPDATE` and `DELETE`. If successful, it returns the
number of rows that are affected by the execution.

   - [query()|CDbCommand::query]: performs an SQL statement that returns
rows of data, such as `SELECT`. If successful, it returns a [CDbDataReader]
instance from which one can traverse the resulting rows of data. For
convenience, a set of `queryXXX()` methods are also implemented which
directly return the query results.

An exception will be raised if an error occurs during the execution of SQL
statements.

~~~
[php]
$rowCount=$command->execute();   // execute the non-query SQL
$dataReader=$command->query();   // execute a query SQL
$rows=$command->queryAll();      // query and return all rows of result
$row=$command->queryRow();       // query and return the first row of result
$column=$command->queryColumn(); // query and return the first column of result
$value=$command->queryScalar();  // query and return the first field in the first row
~~~

Fetching Query Results
----------------------

After [CDbCommand::query()] generates the [CDbDataReader] instance, one
can retrieve rows of resulting data by calling [CDbDataReader::read()]
repeatedly. One can also use [CDbDataReader] in PHP's `foreach` language
construct to retrieve row by row.

~~~
[php]
$dataReader=$command->query();
// calling read() repeatedly until it returns false
while(($row=$dataReader->read())!==false) { ... }
// using foreach to traverse through every row of data
foreach($dataReader as $row) { ... }
// retrieving all rows at once in a single array
$rows=$dataReader->readAll();
~~~

> Note: Unlike [query()|CDbCommand::query], all `queryXXX()` methods
return data directly. For example, [queryRow()|CDbCommand::queryRow]
returns an array representing the first row of the querying result.

Using Transactions
------------------

When an application executes a few queries, each reading and/or writing
information in the database, it is important to be sure that the database
is not left with only some of the queries carried out. A transaction,
represented as a [CDbTransaction] instance in Yii, may be initiated in this
case:

   - Begin the transaction.
   - Execute queries one by one. Any updates to the database are not visible to the outside world.
   - Commit the transaction. Updates become visible if the transaction is successful.
   - If one of the queries fails, the entire transaction is rolled back.

The above workflow can be implemented using the following code:

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
catch(Exception $e) // an exception is raised if a query fails
{
	$transaction->rollBack();
}
~~~

Binding Parameters
------------------

To avoid [SQL injection
attacks](http://en.wikipedia.org/wiki/SQL_injection) and to improve
performance of executing repeatedly used SQL statements, one can "prepare"
an SQL statement with optional parameter placeholders that are to be
replaced with the actual parameters during the parameter binding process.

The parameter placeholders can be either named (represented as unique
tokens) or unnamed (represented as question marks). Call
[CDbCommand::bindParam()] or [CDbCommand::bindValue()] to replace these
placeholders with the actual parameters. The parameters do not need to be
quoted: the underlying database driver does it for you. Parameter binding
must be done before the SQL statement is executed.

~~~
[php]
// an SQL with two placeholders ":username" and ":email"
$sql="INSERT INTO tbl_user (username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// replace the placeholder ":username" with the actual username value
$command->bindParam(":username",$username,PDO::PARAM_STR);
// replace the placeholder ":email" with the actual email value
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// insert another row with a new set of parameters
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

The methods [bindParam()|CDbCommand::bindParam] and
[bindValue()|CDbCommand::bindValue] are very similar. The only difference
is that the former binds a parameter with a PHP variable reference while
the latter with a value. For parameters that represent large blocks of data
memory, the former is preferred for performance consideration.

For more details about binding parameters, see the [relevant PHP
documentation](http://www.php.net/manual/en/pdostatement.bindparam.php).

Binding Columns
---------------

When fetching query results, one can also bind columns with PHP variables
so that they are automatically populated with the latest data each time a
row is fetched.

~~~
[php]
$sql="SELECT username, email FROM tbl_user";
$dataReader=$connection->createCommand($sql)->query();
// bind the 1st column (username) with the $username variable
$dataReader->bindColumn(1,$username);
// bind the 2nd column (email) with the $email variable
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username and $email contain the username and email in the current row
}
~~~

Using Table Prefix
------------------

Yii provides integrated support for using
table prefix. Table prefix means a string that is prepended to the names of
the tables in the currently connected database. It is mostly used in a shared
hosting environment where multiple applications share a single database and use
different table prefixes to differentiate from each other. For example, one
application could use `tbl_` as prefix while the other `yii_`.

To use table prefix, configure the [CDbConnection::tablePrefix] property to be
the desired table prefix. Then, in SQL statements use `{{TableName}}` to refer
to table names, where `TableName` means the table name without prefix. For example,
if the database contains a table named `tbl_user` where `tbl_` is configured as the
table prefix, then we can use the following code to query about users:

~~~
[php]
$sql='SELECT * FROM {{user}}';
$users=$connection->createCommand($sql)->queryAll();
~~~

<div class="revision">$Id$</div>