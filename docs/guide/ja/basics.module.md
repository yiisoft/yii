モジュール
======

モジュールは、[モデル](/doc/guide/basics.model)、
[ビュー](/doc/guide/basics.view)、[コントローラ](/doc/guide/basics.controller)
とその他のサポートしているコンポーネントから構成される自己完結した
ソフトウェアユニットです。
多くの面でモジュールは、 [アプリケーション](/doc/guide/basics.application)
に似ています。主な違いは、モジュールは単独では配置せず、アプリケーションの内部に
存在しなければならないという点です。
ユーザーは、通常のアプリケーションコントローラでするように、
そのコントローラにアクセスすることができます。

モジュールはいくつかのシナリオで役立ちます。
大規模アプリケーションでは、それをいくつかのモジュールに分け、
各々独立して開発と保守されるかもしれません。
ユーザー管理やコメント管理のような一般的に用いられる機能を、
将来のプロジェクトで簡単に再利用できるように、
モジュールで開発されるかもしれません。

モジュールの作成
---------------

モジュールはユニークな [ID|CWebModule::id] となる名前のディレクトリ下に
まとめられます。モジュールのディレクトリ構成は、
[アプリケーションベースディレクトリ](/doc/guide/basics.application#アプリケーションベースディレクトリ)
と似ています。
以下に、 `forum` という名前のモジュールのディレクトリ構成を示します:

~~~
forum/
   ForumModule.php            モジュールクラスファイル
   components/                再利用可能なユーザコンポーネントを含む
      views/                  ウイジェットのためのビューを含む
   controllers/               コントローラクラスファイルを含む
      DefaultController.php   デフォルトコントローラクラスファイル
   extensions/                サードパーティエクステンションを含む
   models/                    モデルクラスファイルを含む
   views/                     コントローラビューとレイアウトファイルを含む
      layouts/                レイアウトビューファイルを含む
      default/                デフォルトコントローラのためビューファイルを含む
         index.php            インデックスビューファイル
~~~

モジュールは [CWebModule] より継承されたモジュールクラスを持つ必要があります。
クラス名は `$id` にモジュールID（もしくは、モジュールディレクトリ名）を入れ、
`ucfirst($id).'Module'` という形式を用いて決定します。
モジュールクラスは、モジュールコード間で共通して使用される情報を格納する
中心部分となります。
たとえば、モジュールパラメータを格納するために [CWebModule::params] を、
モジュールレベルで
[アプリケーションコンポーネント](/doc/guide/basics.application#アプリケーションコンポーネント)
を共有するために [CWebModule::components] を使用できます。

> Tip|ヒント: 新しいモジュールの基本的なスケルトンを作成するためにGiiのモジュールジェネレータを使うことができます。


モジュールの使用
------------

モジュールを使用するには、まず
[アプリケーションベースディレクトリ](/doc/guide/basics.application#アプリケーションベースディレクトリ)
の `modules` ディレクトリの下にそのモジュールディレクトリを配置します。
次に、アプリケーションの [modules|CWebApplication::modules] プロパティで、
モジュール ID を宣言します。
たとえば、上記 `forum` モジュールを使用するために、
[アプリケーション初期構成](/doc/guide/basics.application#アプリケーション初期構成)
で下記のようにします。

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

モジュールは初期プロパティ値で設定することも可能です。
使用方法は、
[アプリケーションコンポーネント](/doc/guide/basics.application#アプリケーションコンポーネント)
の設定と非常に似ています。
たとえば、`forum` モジュールがそのモジュールクラス中に `postPerPage`
という名前のプロパティを持っていれば、下記のように
[アプリケーション初期構成](/doc/guide/basics.application#アプリケーション初期構成)
の中で設定できます。

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

モジュールのインスタンスは現在のアクティブなコントローラ中の
[module|CController::module] プロパティによってアクセスできます。
モジュールインスタンスを通じて、モジュールレベルで共有されている情報に
アクセスすることができます
たとえば、上記の `postPerPage` 情報にアクセスするために、
下記の表現を使用できます:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// もしくは、もし $this がコントローラインスタンスを参照するなら下記のように
// $postPerPage=$this->module->postPerPage;
~~~

モジュールでのコントローラアクションは
[ルート（道筋）](/doc/guide/basics.controller#ルート（道筋）)
`moduleID/controllerID/actionID` を使用してアクセスできます。
たとえば、上記の `forum` モジュールが `PostController` という名前の
コントローラを持っていれば、このコントローラの `create` アクションを
参照するために、
[ルート（道筋）](/doc/guide/basics.controller#ルート（道筋）)
`forum/post/create` を使用できます。
このルートに対応する URL は
`http://www.example.com/index.php?r=forum/post/create` になります。

> Tip|ヒント: コントローラが `controllers` のサブディレクトリにあるなら、
上記の [ルート（道筋）](/doc/guide/basics.controller#ルート（道筋）)
フォーマットをまだ使用できます。
たとえば、`PostController` が `forum/controllers/admin` 下にある場合、
`forum/admin/post/create` を使用している `create` アクションを参照できます。

モジュールのネスト化
-------------

モジュールは無限にネスト化することができます。
これは、モジュールが、別のモジュールを含むような他のモジュールを含むことができるということです。
前者を*親モジュール*と呼び、後者を*子モジュール*と呼びます。
子モジュールは、上記のアプリケーション構成に見られるように、親モジュールの[modules|CWebModule::modules]プロパティ中で宣言されなければなりません。

子モジュールでコントローラアクションにアクセスするために、
ルート `parentModuleID/childModuleID/controllerID/actionID` を
使用しなければなりません。

<div class="revision">$Id: basics.module.txt 2890 2009-02-25 21:45:42Z qiang.xue $</div>
