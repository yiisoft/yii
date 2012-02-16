Yiiの試運転
====================

このセクションでは、出発点となるスケルトンアプリケーションを作る方法を解説します。
説明を簡単にするために、ウェブサーバのドキュメントルートを `/wwwroot` であると仮定し、対応するURLを `http://www.example.com/` であるとします。 


Yiiのインストール
--------------

まずはじめに Yii Framework をインストールします。
Yiiのリリースファイル(バージョン 1.1.1以上)を[www.yiiframework.com](http://www.yiiframework.com/download)から取得し、`/wwwroot/yii` ディレクトリに解凍します。
/wwwroot/yii/frameworkというディレクトリが存在することを確認してください。

> Tip|ヒント: YiiFrameworkのインストール先はどこでもかまいません。`framework` ディレクトリはすべてのフレームワークコードを含み、Yiiアプリケーションを配布する際に唯一必要となるディレクトリです。インストールした Yii を複数のアプリケーションから利用することが可能です。

Yiiをインストールした後、ブラウザ窓から`http://www.example.com/yii/requirements/index.php`を入力して開いてください。
Yiiのリリースに含まれる要求チェッカが表示されます。
ブログアプリケーションのためには最小限の要求の他に`pdo 拡張`と`pdo_sqlite拡張`がSQLiteにアクセスするために必要となります。


スケルトンアプリケーションを作る
-----------------------------

次に、 `yiic` ツールを使って、`/wwwroot/blog` ディレクトリにスケルトンアプリケーションを作ります。
`yiic` ツールは Yii のリリースに含まれるコマンドラインツールです。
このツールはコードを生成するのに使用しますが、繰返し必要になるコード記述タスクを減らすことができます。

コマンドプロンプトを開き、以下のコマンドを実行します:

~~~
% /wwwroot/yii/framework/yiic webapp /wwwroot/blog
Create a Web application under '/wwwroot/blog'? [Yes|No]y
......
~~~

> Tip|ヒント: 上で示したように `yiic` ツールを使うには、CLI PHPプログラムにパスが通っていなければなりません。もしそうでない場合は、次のようにすることでコマンドを利用できます:
>
>~~~
> path/to/php /wwwroot/yii/framework/yiic.php webapp /wwwroot/blog
>~~~

作ったばかりのアプリケーションを試してみるには、ブラウザを開き、`http://www.example.com/blog/index.php` にアクセスします。
4つの機能を持ったアプリケーションが表示されるでしょう。
ホームページ、アバウトページ、コンタクトページ、そしてログインページです。 

以下では、簡単にこのスケルトンアプリケーションが持っているものについて説明します。

###エントリスクリプト

We have an [entry script]() filewhich has the following content:
[エントリスクリプト](http://www.yiiframework.com/doc/guide/basics.entry)は `/wwwroot/blog/index.php` で、以下のような内容です:

~~~
[php]
<?php
$yii='/wwwroot/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
Yii::createWebApplication($config)->run();
~~~

このエントリスクリプトは、ウェブユーザが直接アクセスできる唯一のファイルです。
まず Yii ブートストラップファイルの `yii.php` が読み込まれ、指定した設定で [アプリケーション](http://www.yiiframework.com/doc/guide/basics.application) インスタンスが作成されたのち、実行されます。 


###ベースアプリケーションディレクトリ

`/wwwroot/blog/protected` が[アプリケーションベースディレクトリ](http://www.yiiframework.com/doc/guide/basics.application#application-base-directory)です。
これから作成するコードとデータのほとんどがこのディレクトリ以下に配置されます。
このディレクトリはウェブユーザのアクセスから保護されなければなりません。
[Apache httpd Web server](http://httpd.apache.org/) を使っているなら、以下のような `.htaccess` ファイルを置くことでこれを達成できます:

~~~
deny from all
~~~

他のウェブサーバに関しては、ウェブユーザからディレクトリを保護するにはどうするかについて、マニュアルを参照してください。 


アプリケーションワークフロー
--------------------

Yiiがどのように動くか、理解するのを助けるために、ユーザーがコンタクトページにアクセスした際に、スケルトンアプリケーションでの主なワークフローを解説します:

 0. ユーザがURL `http://www.example.com/blog/index.php?r=site/contact`を要求します。
 1. [エントリスクリプト](http://www.yiiframework.com/doc/guide/basics.entry)がウェブサーバにより実行され、リクエストを処理します。
 2. [アプリケーション](http://www.yiiframework.com/doc/guide/basics.application) インスタンスが作成され、アプリケーション初期構成ファイル `/wwwroot/blog/protected/config/main.php` で指定された初期プロパティ値が設定されます。
 3. アプリケーションは[コントローラ](http://www.yiiframework.com/doc/guide/basics.controller)と[コントローラアクション](http://www.yiiframework.com/doc/guide/basics.controller#action)にリクエストを解決します。コンタクトページへのリクエストは、`site` とコントローラと `contact` アクションに解決されます (`/wwwroot/blog/protected/controllers/SiteController.php`中の`actionContact`メソッド)。
 4. アプリケーションは `SiteController` インスタンスとして、`site` コントローラを作成し、実行します。
 5. `SiteController` インスタンスは自身の `actionContact()` メソッドを呼ぶことで、 `contact` アクションを実行します。
 6. `actionContact` メソッドは `contact` という名前の[ビュー](http://www.yiiframework.com/doc/guide/basics.view)をレンダリングし、ウェブユーザに提示します。内部的には、ビューファイル `/wwwroot/blog/protected/views/site/contact.php` を読み込み、[レイアウト](http://www.yiiframework.com/doc/guide/basics.view#layout)ファイル `/wwwroot/blog/protected/views/layouts/column1.php` にその結果を埋め込むことで、達成されます。

<div class="revision">$Id: start.testdrive.txt 1734 2009-02-16 05:20:17Z qiang.xue $</div>
