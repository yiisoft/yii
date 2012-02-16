データベースのセットアップ
===================

スケルトンアプリケーションを作り、データベースの設計が終わりました。 このセクションでは実際にブログデータベースを作成し、スケルトンアプリケーションとの接続を確立します。 


データベースの作成
-----------------

 データベースはSQLiteを使います。 Yiiのデータベースサポートは [PDO](http://www.php.net/manual/en/book.pdo.php) の上に構築されているため、 アプリケーションコードを変更することなく、MySQLやPostgreSQLといった異なるDBMSを利用することができます。

ディレクトリ`/wwwroot/blog/protected/data`にデータベースファイル`blog.db`を作ります。
ディレクトリとファイルはともにWebサーバプロセスからSQLiteを通して書き込み可能である必要があります。
ここでは単に `/wwwroot/yii/demos/blog/protected/data/blog.db` にあるブログデモのデータベースファイルをコピーすることにします。
もしくは `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql` にある、SQLファイルを実行することでもデータベースを生成できます。

> Tip|ヒント: SQL文の実行には、 `sqlite3` コマンドラインツールを利用できます。 詳しくは [SQLite 公式ウェブサイト](http://www.sqlite.org/download.html)を参照してください。 


データベースとの接続を確立する
--------------------------------

作ったスケルトンアプリケーションでブログデータベースを使うには、`/wwwroot/blog/protected/config/main.php` に保存されている PHP スクリプトで[アプリケーション初期構成](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) を変更する必要があります。
このスクリプトはキーと値のペアで構成された連想配列を返します。 これらの値は[アプリケーションインスタンス](http://www.yiiframework.com/doc/guide/basics.application)を初期化するために使われます。

`db`コンポーネントを以下のように構成します。

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

上記の設定は、`db` [アプリケーションコンポーネント](http://www.yiiframework.com/doc/guide/basics.application#application-component)の`connectionString` プロパティが `sqlite:/wwwroot/blog/protected/data/blog.db` に初期化されることを示します。

この設定の場合、コードのどこからでも、`Yii::app()->db` を通じて DB コネクションオブジェクトにアクセスすることができます。
`Yii::app()` は、エントリスクリプトで作成されたアプリケーションインスタンスを返すことに注意して下さい。
DB コネクションのメソッドやプロパティに興味があれば、[クラスリファレンス|CDbConnection]を参照して下さい。
しかし、多くの場合このDBコネクションを直接利用することはありません。
そのかわりにいわゆる [ActiveRecord](http://www.yiiframework.com/doc/guide/database.ar) を利用してデータベースにアクセスします。 

構成ファイルで設定した`tablePrefix`プロパティについて、もう少し説明したいと思います。
これはデータベーステーブル名のプレフィクスとして`tbl_`を使用することを`db`コネクションオブジェクトに伝えます。
具体的には、もしSQL文のなかにトークンがあり、それが二重波括弧(例えば`{{post}}`)で囲まれていた場合、`db`コネクションは
実行のためにDBMSに送信する前にそれをテーブルプレフィクスを付けた名前(例えば`tbl_post`)に変換します。
この機能は、もし将来テーブルプレフィクスを変更することになっても、ソースコードを触る必要がないため非常に有用です。
例えば、コンテンツ管理システム(CMS)を開発しており、それが新しい環境にインストールされる場合に、ユーザはテーブルプレフィクスを自由に選択することが可能となります。

> Tip|ヒント: もしSQLiteではなくMySQLをデータ格納に使う場合には、`blog`という名前のMySQLデータベースを、`/wwwroot/yii/demos/blog/protected/data/schema.mysql.sql`を使用して作成します。その後、アプリケーション構成ファイルを以下のように修正します。
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
>       ......
> );
> ~~~


<div class="revision">$Id: prototype.database.txt 2332 2009-02-16 05:20:17Z qiang.xue $</div>
