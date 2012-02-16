データアクセスオブジェクト (DAO)
=========================

データアクセスオブジェクト (DAO) は、異なるデータベース管理システム (DBMS) 上に保存されたデータに
接続するための包括的なAPIを提供します。
DAO を用いてデータにアクセスすることで、コードを変更せずに異なる DBMS を利用する事が可能になります

Yii DAO は MySQL や PostgreSQL といった多くのポピュラーな DBMS への統一的なデータアクセスを提供する
[PHP Data Objects (PDO)](http://php.net/manual/en/book.pdo.php) 拡張を用いて構築されています。
そのため、Yii DAO を利用するには、PDO 拡張と特定の PDO データベースドライバ
(たとえば `PDO_MYSQL`)がインストールされている必要があります。

Yii DAO は、主に以下の 4 つのクラスから構成されています: 

   - [CDbConnection]: データベースとの接続を表します。
   - [CDbCommand]: データベースに対して実行する SQL 文を表します。
   - [CDbDataReader]: クエリ結果セットから後戻りしない行列を表します。
   - [CDbTransaction]: DB トランザクションを表します。

以下に、違うシナリオ中での Yii DAO の使用方法を紹介します。

データベース接続の確立
--------------------------------

データベース接続を確立させるには、[CDbConnection] のインスタンスを作成し、
それを active にします。データソース名 (DSN) はデータベースに接続するために
要求される情報を指定するために必要です。おそらく username と password も
接続を確立させるために必要でしょう。接続を確立する際にエラーが起こると
例外が発生します。(たとえば、間違った DSN や無効な username/password)

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// 接続を確立する。try...catch で例外処理を行う事もできます
$connection->active=true;
......
$connection->active=false;  // 接続を閉じる
~~~

DSN のフォーマットは、使用する PDO データベースドライバに依存します。
一般的には、DSN はその PDO ドライバ名に続けてコロン、その後に、
ドライバ個別の接続シンタックスを指定します。詳細な情報は、
[PDO documentation](http://www.php.net/manual/en/pdo.construct.php) を参照してください。
以下に、一般に用いられる DSN フォーマットのリストを示します:

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`
   - SQL Server: `mssql:host=localhost;dbname=testdb`
   - Oracle: `oci:dbname=//localhost:1521/testdb`

[CDbConnection] は [CApplicationComponent] から拡張されているため、
[アプリケーションコンポーネント](/doc/guide/basics.application#application-component)
として使用できます。そうするには、
[application configuration](/doc/guide/basics.application#application-configuration) 内で
`db` (もしくは他の名前の)アプリケーションコンポーネントを下記のように設定します。

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
			'emulatePrepare'=>true,  // MySQL の設定によっては必要
		),
	),
)
~~~

その後、[CDbConnection::autoConnect] 設定が flase になっていない限り、
すでに自動的にアクティブになっている `Yii::app()->db` を利用して
DB 接続にアクセスできます。このアプローチを使うと、単一の DB 接続を、
コード中の色々な場所で共有することができます。

SQL 文の実行
------------------------

一度データベース接続を確立すれば、[CDbCommand] を使用して SQL 文を実行できます。
まず、特定の SQL 文を [CDbConnection::createCommand()] を呼び、[CDbCommand]
のインスタンスを作成します:

~~~
[php]
$connection=Yii::app()->db;   // "db" 接続を構成したと仮定した場合
// もしくは、明示的に接続を作成してもよい
// $connection=new CDbConnection($dsn,$username,$password);
$command=$connection->createCommand($sql);
// もし必要なら、SQL 文を下記のように更新できます:
// $command->text=$newSQL;
~~~

SQL 文は、次の2つの方法のうちのいずれかで、[CDbCommand] によって実行されます:

   - [execute()|CDbCommand::execute]: `INSERT`, `UPDATE`, `DELETE` のような、
non-query型の SQL 文を実行します。成功した場合、SQL 文の実行によって影響された
行数を返します。

   - [query()|CDbCommand::query]: `SELECT` のような、データ行を返す SQL 文を実行します。
成功した場合、結果行列を行き来できる [CDbDataReader] インタンスが返されます。
便宜上、直接クエリ結果を返す、`queryXXX()` メソッドのセットも実装されます。

SQL 文の実行中にエラーが発生した場合は、例外が発生します。

~~~
[php]
$rowCount=$command->execute();   // non-query 型 SQL の実行
$dataReader=$command->query();   // query 型 SQL の実行
$rows=$command->queryAll();      // クエリを行い、結果の全行を返す
$row=$command->queryRow();       // クエリを行い、結果の最初の行を返す
$column=$command->queryColumn(); // クエリを行い、結果の最初の列を返す
$value=$command->queryScalar();  // クエリを行い、最初の行の最初の項目を返す
~~~

クエリ結果の取得
----------------------

[CDbCommand::query()] により [CDbDataReader] インスタンスを生成した後に、
[CDbDataReader::read()] を繰り返し呼ぶことで、結果データの行を取り出せます。
データを一行ずつ取り出すために、PHP の `foreach` 文の中で [CDbDataReader] を
使用できます。

~~~
[php]
$dataReader=$command->query();
// false が返るまで、繰り返し read() を呼び出します
while(($row=$dataReader->read())!==false) { ... }
// foreach を用いてデータの全行を取り出します
foreach($dataReader as $row) { ... }
// 1つの配列として、一回で全行を取り出します
$rows=$dataReader->readAll();
~~~

> Note|注意: [query()|CDbCommand::query] と異なり、全ての `queryXXX()` メソッドは
直接データを返します。たとえば、[queryRow()|CDbCommand::queryRow] は、
クエリ結果の最初の行に相当する配列を返します。

トランザクションの使用
------------------

アプリケーションがいくつかのクエリを（各クエリがデータベース中の情報を読み書きして）実行する場合、
データベースが全てのクエリを確実に実行したかどうかを確認することは重要です。
Yii の [CDbTransaction] インスタンスとして表されるトランザクションは、このような場合に開始できます:

   - トランザクションを開始する。
   - 1つずつクエリを実行する。データベースへのどんな更新も外の世界には見えません。
   - トランザクションをコミットする。処理が成功した場合、更新が適用されます。
   - もしクエリのひとつが失敗した場合、全処理がロールバックされます。

上記のワークフローは次のコードを使用して実装できます:

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... 他の SQL の実行
	$transaction->commit();
}
catch(Exception $e) // クエリの実行に失敗した場合、例外が発生します
{
	$transaction->rollBack();
}
~~~

パラメータのバインディング
------------------

[SQL インジェクション攻撃](http://ja.wikipedia.org/wiki/SQL%E3%82%A4%E3%83%B3%E3%82%B8%E3%82%A7%E3%82%AF%E3%82%B7%E3%83%A7%E3%83%B3)
を避け、繰り返し使用される SQL 文の実行パフォーマンスの改善のために、
パラメータバインドプロセスの間に実引数と置き換えられることになっている
オプションのパラメータプレースホルダと SQL 文を“準備”できます。

パラメータプレースホルダは名前をつける（ユニークなトークンとして表す）か、
つけない（クエスチョンマークとして表す）事が可能です。
[CDbCommand::bindParam()] か [CDbCommand::bindValue()] を呼び出す事で、
これらのプレースホルダを実引数に置き換えます。
パラメータをクオート（引用符で囲む）する必要はありません: 
データベースドライバにより、その処理は行われます。SQL 文が実行される前に、パラメータバインディングが行われます。

~~~
[php]
// ":username" と ":email" の 2 つのプレースホルダを利用する SQL
$sql="INSERT INTO tbl_user (username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// プレースホルダ ":username" を実際の username 値で置き換える
$command->bindParam(":username",$username,PDO::PARAM_STR);
// プレースホルダ ":email" を実際の email 値で置き換える
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// 別のパラメータを使って別の行を INSERT する
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~
[bindParam()|CDbCommand::bindParam] と [bindValue()|CDbCommand::bindValue]
メソッドは、とても似ています。唯一の違いは、前者は PHP 変数のリファレンスを、
後者は値をパラメータにバインドするということです。
大きなデータをパラメータに指定する場合は、パフォーマンス的に
前者の方法を利用する事を推奨します。

バインディングパラメータについての詳細については、
[関連する PHP ドキュメント](http://www.php.net/manual/ja/pdostatement.bindparam.php)
を参照してください。

カラムのバインディング
---------------

クエリの結果を抽出（フェッチ）する場合、カラムと PHP 変数をバインドすることで、
一行抽出されるごとに、変数に最新のデータが自動的に入るようにする事ができます。

~~~
[php]
$sql="SELECT username, email FROM tbl_user";
$dataReader=$connection->createCommand($sql)->query();
// 1つめのカラム (username) を $username 変数にバインドする
$dataReader->bindColumn(1,$username);
// 2つめのカラム (email) を $email 変数にバインドする
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username と $email には、現在の行の username と email の内容が入っています
}
~~~

テーブルプレフィックスを使う
------------------

Yii はテーブルプレフィックスの使用について、統合的なサポートを提供しています。
テーブルプレフィックスとは、現在接続されているデータベースのテーブル名の前に付加されている文字列を意味します。
たいていは、共有ホスティング環境において使われます。複数のアプリケーションが単一のデータベースを共有しつつ、お互いを区別するために違うテーブルプレフィックスを使うという形です。
例えば、あるアプリケーションは `tbl_` をプレフィックスとして使い、他のアプリケーションは `yii_` を使うという具合です。

テーブルプレフィックスを使うためには、[CDbConnection::tablePrefix] プロパティを望みのテーブルプレフィックスに構成します。
そして、SQL 文においてテーブル名を指定するのに `{{TableName}}` という書式を使います。
ここで `TableName` はプレフィックスを除外したテーブル名を指します。
例えば、データベースが `tbl_user` という名前のテーブルを持っていて、`tbl_` がテーブルプレフィックスとして構成されている場合、ユーザに関するクエリのコードとして下記を使うことが出来ます。

~~~
[php]
$sql='SELECT * FROM {{user}}';
$users=$connection->createCommand($sql)->queryAll();
~~~

<div class="revision">$Id: database.dao.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>