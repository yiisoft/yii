初めてのYiiアプリケーションの作成
==============================

Yiiで最初の経験を積んでもらうために、この章では最初のYiiアプリケーションの作成法を説明します。
新しい Yii アプリケーションを作成するために `yiic` (コマンドラインツール)を使用し、
いくつかのタスクについてコード生成を自動化するために `Gii` (強力なウェブベースのコードジェネレータ)
を使用します。便宜上、`YiiRoot` は Yii をインストールしたディレクトリと仮定し、`WebRoot`
はウェブサーバのドキュメントルートします。


コマンドラインから `yiic` を次のように実行します。

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|注意: `yiic`をMac OSやLinuxやUnix上で動かす場合には、`yiic`ファイルを実行可にします。
>または、以下のようにツールを動作させます。
> ~~~
> % cd WebRoot
> % php YiiRoot/framework/yiic.php webapp testdrive
> ~~~

これを実行すると`WebRoot/testdrive`の下に骨格のYiiアプリケーションが作成されます。
このアプリケーションはほとんどのYiiアプリケーションに必要とされるディレクトリ構造を持ちます。


コードを一行も書かなくても、ウェブブラウザで以下のURLをアクセスすることで、最初のYiiアプリケーションをテストすることができます。
~~~
http://hostname/testdrive/index.php
~~~

これで見るように、このアプリケーションは、ホームページ、「について(about)」ページ、コンタクトページ、
ログインぺージの4ページからなります。コンタクトページはユーザがウェブマスターに問い合わせを
入力して送信するためのフォームを表示します。ログインページはユーザが特権内容にアクセスする前に
認証を受けるために使用されます。詳しい情報は以下の画面情報を見てください。

![ホームページ](first-app1.png)

![コンタクトページ](first-app2.png)

![入力エラーがあるコンタクトページ](first-app3.png)

![成功のメッセージがあるコンタクトページ](first-app4.png)

![ログインページ](first-app5.png)


以下はアプリケーションのディレクトリ構造を示します。詳細な説明は
[取り決め(Conventions)](/doc/guide/basics.convention#directory)を見てください。

~~~
testdrive/
   index.php                 ウェブアプリケーションのエントリスクリプト
   index-test.php            機能テストのためのエントリスクリプト
   assets/                   発行されたリソースファイルを含む
   css/                      CSSファイルを含む
   images/                   イメージファイルを含む
   themes/                   アプリケーションテーマを含む
   protected/                保護されたアプリケーションファイルを含む
      yiic                   Unix/Linux用 yiic コマンドラインスクリプト
      yiic.bat               ウインドウズ用 yiic コマンドラインスクリプト
      yiic.php               yiic コマンドライン PHP スクリプト
      commands/              カスタム化した'yiic'コマンドを含む
         shell/              カスタム化した'yiic shell'コマンドを含む
      components/            再利用可能なユーザコンポーネントを含む
         Controller.php      全てのコントローラクラスの基本クラス
         UserIdentity.php    認証のための 'UserIdentity' クラス
      config/                構成ファイルを含む
         console.php         コンソールアプリケーション構成ファイル
         main.php            ウェブアプリケーション構成ファイル
         test.php            機能テストのための構成ファイル
      controllers/           コントローラクラスファイルを含む
         SiteController.php  デフォルトコントローラクラスファイル
      data/                  サンプルデータベースを含む
         schema.mysql.sql    サンプルの MySQL データベースの DB スキーマ
         schema.sqlite.sql   サンプルの SQLite データベースの DB スキーマ
         testdrive.db        サンプルの SQLite データベースファイル
      extensions/            サードパーティ拡張を含む
      messages/              翻訳されたメッセージを含む
      models/                モデルクラスファイルを含む
         LoginForm.php       'login'アクションのためのフォームモデル
         ContactForm.php     'contact'アクションのためのフォームモデル
      runtime/               一時的に生成されたファイルを含む
      views/                 コントローラビューとレイアウトを含む
         layouts/            レイアウトビューファイルを含む
            main.php         全てのページで共有される基本レイアウト
            column1.php      単一カラムを使うページ用のレイアウト
            column2.php      2カラムを使うページ用のレイアウト
         site/               'site'コントローラのためのビューを含む
            pages/           "静的"なページを含む
               about.php     "について(about)"ページのビュー
            contact.php      'contact'アクションのためのビュー
            error.php        'error'アクションの為のビュー (外部エラーを表示)
            index.php        'index'アクションのためのビュー
            login.php        'login'アクションのためのビュー
~~~

データベースへの接続
----------------------

ほとんどのウェブアプリケーションの裏側にはデータベースがあります。この最初のウェブアプリケーションも例外ではありません。
データベースを利用するには、まずアプリケーションにどのようにデータベースに接続するかを指示しなければなりません。
これはアプリケーション構成ファイル`WebRoot/testdrive/protected/config/main.php`を以下のように修正することで行います。

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/testdrive.db',
		),
	),
	......
);
~~~

上記のコードは Yii に対して、アプリケーションは必要に応じて SQLite データベース `WebRoot/testdrive/protected/data/testdrive.db`
に接続すべきことを指示しています。この SQLite データベースは、作成したばかりの骨組みアプリケーションに既に含まれていることに留意して下さい。
このデータベースには、下記のような `tbl_user` という名前のテーブルが一つだけ含まれています。


~~~
[sql]
CREATE TABLE tbl_user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

代りに MySQL のデータベースを試したい場合は、付属している MySQL のスキーマファイル `WebRoot/testdrive/protected/data/schema.mysql.sql`
を使ってデータベースを作成することが出来ます。

> Note|注意: Yiiのデータベース機能を使うためにはPHP PDO拡張とドライバ固有のPDO拡張を有効にする必要があります。
このtest-driveアプリケーションでは`php_pdo`と`php_pdo_sqlite`拡張がオンになっている必要があります。


CRUD操作の実装
----------------------------

これからが面白い部分です。
CRUD操作(作成、読み出し、修正、削除)を今作成したばかりの `tbl_user` テーブルに対して実装します。
これは実際のアプリケーションでも共通に必要となる機能です。実際にコードを書く苦労をする代りに、
`Gii` -- 強力なウェブベースのコードジェネレータを使用します。

> Info|情報: Gii はバージョン 1.1.2 以降で使用出来ます。それより前のバージョンでは、同じ目的を達するために、既に述べた `yiic` ツールを使うことが出来ました。詳細については、[yiic シェルで CRUD 操作を実装する](/doc/guide/quickstart.first-app-yiic) を参照して下さい。.

### Gii を構成する

Gii を使うためには、最初に `WebRoot/testdrive/protected/config/main.php` を次のように編集する必要があります。これは [アプリケーション構成](/doc/guide/basics.application#application-configuration) ファイルとして知られるファイルです。

~~~
[php]
return array(
	......
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'pick up a password here',
		),
	),
);
~~~

次に、ブラウザで URL `http://hostname/testdrive/index.php?r=gii` にアクセスします。パスワードを求められますので、上記のアプリケーション構成に記述したばかりのパスワードを入力して下さい。

### User モデルを生成する

ログイン後、`Model Generator` のリンクをクリックします。すると、下のようなモデル生成ページに移動します。

![Model Generator](gii-model.png)

`Table Name` フィールドに `tbl_user` と入力します。`Model Class` フィールドには `User` と入力します。そして `Preview` ボタンを押すと、
新しく生成されるコードファイルを見ることが出来ます。さあ、`Generate` ボタンを押しましょう。`User.php` という新しいファイルが `protected/models` の下に生成されます。
後にこのガイドの中で説明するように、この `User` モデルクラスによって、その下層を構成するデータベースの `tbl_user` テーブルとオブジェクト指向の流儀で対話をすることが可能になります。

### CRUD コードを生成する

モデルクラスファイルを生成した後で、ユーザデータに関する CRUD 操作を実装するコードを生成します。下の図のように、Gii で `Crud Generator` を選択します。

![CRUD Generator](gii-crud.png)

`Model Class` フィールドに `User` と入力します。`Controller ID` フィールドは `user` (小文字) を入力します。そして、`Preview` ボタンを押した後で `Generate` ボタンを押します。これで CRUD コードの生成は完了しました。

### CRUD ページにアクセスする

下記の URL をブラウズして、仕事の結果を楽しみましょう。

~~~
http://hostname/testdrive/index.php?r=user
~~~

このページは `tbl_user` テーブルのユーザのエントリ一覧を表示します。

`Create User` のリンクをクリックしてみてください。もし既にログインしていなければログインページに飛ばされます。
ログイン後は新規ユーザを追加するための入力フォームが現れます。入力を完了して `Create` ボタンをクリックしてください。
何か入力にエラーがある場合は、親切なエラーメッセージが表示されて、入力内容が保存されることを防いでくれます。
ユーザ一覧に戻ると、新しく追加されたユーザをリスト上で見ることが出来るはずです。

上記のステップを繰り返してユーザをたくさん追加してみてください。1ページに表示するには多過ぎるようになると、
ユーザ一覧のページが自動的にユーザエントリをページ分割して表示することに注目して下さい。

`admin/admin` を使って管理者としてログインすると、以下の URL によってユーザ管理ページを見ることができます。

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

このページは、便利なテーブル形式でユーザの一覧表を表示します。テーブルのヘッダセルをクリックすると、
対応するカラムをソートすることが出来ます。また、データの各行にあるボタンをクリックすると、対応する行のデータを
閲覧、更新、または削除することが出来ます。別のページへ移動することも出来ます。さらに、関心のあるデータを探すために、
フィルターを使ったり検索をしたりすることも出来ます。

これら全ての便利な機能が、一行のコードを書く必要もなく、手に入るのです。

![User admin page](first-app6.png)

![Create new user page](first-app7.png)


<div class="revision">$Id: quickstart.first-app.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>
