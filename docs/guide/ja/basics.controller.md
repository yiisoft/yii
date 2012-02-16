コントローラ
==========

`コントローラ` は [CController] か、[CController]を拡張したクラスのインスタンスです。
コントローラは、ユーザが要求した時にアプリケーションオブジェクト
により生成されます。コントローラは、起動されると、要求されたアクションを実行するために、通常は、必要なモデルを取り込んで適切なビューを表示します。
`アクション`は、最も単純化された形式としては、コントローラクラスの `action` で始まる名前のメソッドです。

コントローラは既定のアクション（デフォルトアクション）を持っています。
どのアクションを実行するかをユーザが指定しない場合、デフォルトアクション
が実行されます。デフォルトでは、デフォルトアクション名は `index` です。
これは、インスタンスのパブリック変数、[CController::defaultAction] を設定することで変更できます。

以下のコードは`site`コントローラと、`index`アクション(デフォルトのアクション)と、
`contact`アクションを定義します。
~~~
[php]
class SiteController extends CController
{
	public function actionIndex()
	{
		// ...
	}

	public function actionContact()
	{
		// ...
	}
}
~~~


ルート（道筋）
-----

コントローラとアクションは ID により識別されます。
コントローラ ID は `path/to/xyz` の形式で、コントローラクラスファイル
`protected/controllers/path/to/XyzController.php` に対応します。
（`xyz` を実際の名前に置き換えて考えてください。
  例えば、`post` は `protected/controllers/PostController.php` に対応します。）
また、アクション ID はアクションメソッド名からプレフィックス `action` を
除いたものです。たとえば、コントローラクラスに `actionEdit` という名前の
メソッドがあれば、アクション ID は `edit` になります。

ユーザーはルート（道筋）により、特定のコントローラとアクションをリクエスト
します。ルートはスラッシュによりコントローラ ID とアクション ID を連結する
ことで形成されます。たとえば、ルート `post/edit` は `PostController` の
`edit` アクションを参照します。そして、デフォルトでは
`http://hostname/index.php?r=post/edit` という URL が post コントローラと
edit アクションをリクエストするものになります。

>Note|注意: デフォルトでは、ルートは大文字と小文字を区別します。
>アプリケーション構成で[CUrlManager::caseSensitive] を false に設定することで、大文字と小文字を区別しないようすることも可能です。
>大文字と小文字を区別しないモード（case-insensitive mode）の場合は、
>コントローラクラスファイルを含むディレクトリ名が小文字であること、さらに、
>[controller map|CWebApplication::controllerMap] と
>[action map|CController::actions] の両方でキーが小文字であることという規約を
>必ず守って下さい。

アプリケーションは[モジュール](/doc/guide/basics.module)を含むことができます。
モジュール中のコントローラのアクションは`moduleID/controllerID/actionID`のフォーマットで表されます。
より詳細には[モジュールに関する章](/doc/guide/basics.module)を見てください。


コントローラのインスタンス
------------------------

コントローラのインスタンスは [CWebApplication] が入ってきたリクエストを処理する
際に生成されます。コントローラ ID が与えられると、アプリケーションは
次のルールを用いて、コントローラクラスとクラスファイルを探し出します。

   - [CWebApplication::catchAllRequest] が指定されている場合、コントローラ
はこのプロパティを元に生成され、ユーザの指定したコントローラ ID は無視され
ます。これは主にアプリケーションをメンテナンスモードにし、通知のための静的
ページを表示するために使用します。

   - ID が [CWebApplication::controllerMap] に指定されている場合、対応する
コントローラ設定に基づき、コントローラインスタンスが生成されます。

   - ID が `'path/to/xyz'` 形式の場合、コントローラクラス名は `XyzController`
で、対応するクラスファイルは `protected/controllers/path/to/XyzController.php`
であると仮定されます。たとえば、コントローラ ID が `admin/user` なら、コン
トローラクラス名が `UserController` で、クラスファイルが
`protected/controllers/admin/UserController.php` になります。
もしクラスファイルがなければ、404 [CHttpException] が呼び出されます。

[モジュール](/doc/guide/basics.module)が使われる場合には、上記の
プロセスは若干異ります。具体的には、アプリケーションはIDがモジュール中のコントローラを参照しているかを調べ、
もしそうなら、モジュールインスタンスが最初に生成されコントローラインスタンスが次に生成されます。


アクション
------

前述したとおり、アクションは `action` から始まる名前のメソッドにより定義で
きます。より高度な方法は、アクションクラスを定義し、リクエスト時にインスタ
ンス化するようにコントローラに要求する方法です。この方法を用いる事で、アク
ションの再利用が可能になるため、より再利用性を高められます。

新しいアクションクラスを定義するためには、下記のように行います:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// ここにアクションロジックを記述
	}
}
~~~

コントローラがこのアクションを認識するように、このコントローラクラスの
[actions()|CController::actions] メソッドを上書き定義します。

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

上記で使用されている、`application.controllers.post.UpdateAction` というパ
スは、アクションクラスファイル `protected/controllers/post/UpdateAction.php`
へのパスのエイリアスです。

クラスベースのアクションを書く事で、モジュール方式でアプリケーションを構成
出来ます。たとえば、コントローラのためのコードを構成するために、次のような
ディレクトリ構造を利用出来ます。:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~


### アクションパラメータ結合

バージョン1.1.4からは自動アクションパラメータ結合がサポートされました。
これは、コントローラアクションメソッドにおいて名前付きパラメータを定義し、その値が自動的に$_GETから
代入されるものです。

これがどのように動作するかを説明するために、`PostController`コントローラの`create`アクションを記述することを考えてみましょう。
このアクションは2つのパラメータを必要とします。

* `category`: カテゴリIDを意味する整数で、この元で新規ポストが作成されます。
* `language`: 新規ポストが書かれる言語コードを意味する文字列です。

必要なパラメータ値を`$_GET`から取得するために以下のようなつまらないコードを書くはめになるかもしれません。

~~~
[php]
class PostController extends CController
{
	public function actionCreate()
	{
		if(isset($_GET['category']))
			$category=(int)$_GET['category'];
		else
			throw new CHttpException(404,'invalid request');

		if(isset($_GET['language']))
			$language=$_GET['language'];
		else
			$language='en';

		// ... 面白いコードはここから開始 ...
	}
}
~~~

さて、アクションパラメータ機能を用いると、タスクがもっと楽しいものになります。

~~~
[php]
class PostController extends CController
{
	public function actionCreate($category, $language='en')
	{
		$category=(int)$category;

		// ... 面白いコードはここから開始 ...
	}
}
~~~

2つのパラメータをアクションメソッド`actionCreate`に追加したことに注意してください。
パラメータの名前は`$_GET`から得られるパラメータと全く同じにする必要があります。
`$language`パラメータは、リクエストがそういうパラメータを含んでいない場合は、デフォルト値`en`を取ります。
一方`$category`はデフォルト値が無いため、もしリクエストが`category`パラメータを含んでいない場合は、
[CHttpException] (error code 400) エラーが自動的に発行されます。

バージョン 1.1.5 からは、配列タイプのアクションパラメータをサポートします。これはPHPのタイプヒンティングを利用しており、
以下のような文法により行われます。

~~~
[php]
class PostController extends CController
{
	public function actionCreate(array $categories)
	{
		// Yii は必ず $categories を配列にします
	}
}
~~~

すなわち、メソッドのパラメータ宣言において、`$categories`の直前に`array`キーワードを置きます。
こうすることによって、`$_GET['categories']`が単純な文字列である場合には、
その文字列からなる配列に変換されます。

> Note|注: もしパラメータが`array`タイプヒント無しに宣言された場合は、そのパラメータは
> スカラー(配列でない)でなくてはいけません。この場合、`$_GET`から配列パラメータが渡された場合、
> HTTP例外が発生します。

バージョン 1.1.7 からは、自動パラメータ結合はクラスベースのアクションにも適用されます。
もしアクションクラス`run()`メソッドがパラメータ付きで定義された場合、それらのパラメータには
対応する名前のリクエストパラメータが代入されます。例えば、

~~~
[php]
class UpdateAction extends CAction
{
	public function run($id)
	{
		// $id には $_GET['id'] が代入される
	}
}
~~~


フィルタ
------

フィルタは、コントローラのアクション実行の前か後（もしくはその両方）に実行
されるように構成されるコードの断片です。たとえば、アクセスコントロー
ルフィルタは、ユーザーがリクエストしたアクションを実行する前に、認証済みで
ある事を確実にするために使用されるかもしれません; パフォーマンスフィルタは、
アクションの実行所要時間を計測するために使用されるかもしれません。

一つのアクションは複数のフィルタを持つことが出来ます。フィルタはフィルタリ
ストに登場する順で順次実行されます。フィルタは、アクションと残りの実行され
ていないフィルタの実行を防ぐことが出来ます。

フィルタはコントローラクラスメソッドで定義出来ます。メソッド名は必ず `filter`
で始めます。たとえば、`filterAccessControl` メソッドは、`accessControl` と
いう名前のフィルタを定義します。フィルタメソッドはそのシグネチャでなければ
なりません:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// フィルタリングとアクションの実行を継続するために、$filterChain->run() をコール出来ます
}
~~~

`$filterChain` はリクエストされたアクションに結びついているフィルターリスト
を表した、[CFilterChain] のインスタンスです。フィルタメソッド内で、フィルタ
リングとアクションの実行を継続するために、$filterChain->run() をコール出来
ます。

フィルタもまた [CFilter] かその子クラスのインスタンスにする事が出来ます。
次のコードは新しいフィルタクラスを定義するものです:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// アクションが実行される前に実行されるコード
		return true; // アクションが実行されるべきでない場合は false
	}

	protected function postFilter($filterChain)
	{
		// アクションが実行された後に実行されるコード
	}
}
~~~

アクションにフィルタを適用するために、`CController::filters()` メソッドを
上書きする必要があります。このメソッドはフィルタ構成の配列を返さなくてはな
りません。たとえば、

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

上記のコードは `postOnly` と `PerformanceFilter` という、2つのフィルタを指
定しています。 `postOnly` フィルタは、メソッドベースのフィルタです（対応
するフィルタメソッドは既に [CController] に定義されている）; また、
`PerformanceFilter` フィルタはオブジェクトベースです。
`application.filters.PerformanceFilter` というパスは、フィルタークラスファ
イル `protected/filters/PerformanceFilter` へのパスのエイリアスです。
ここでは、`PerformanceFilter` を配列を用いて指定しています。配列を用いるこ
とで、フィルタオブジェクトのプロパティ値を初期化する事ができます。ここでは、
`PerformanceFilter` の `unit` プロパティを `'second'` に初期化しています。

プラスやマイナス演算子を使用すると、アクションに対してのフィルタ適用の有無
を指定できます。上記の場合、`postOnly` は `edit` と `create` アクションに適
用され、`PerformanceFilter` は `edit` と `create` アクション以外のすべての
アクションに適用されます。もし、プラスとマイナスのどちらも使用されていない
場合、フィルタはすべてのアクションに適用されます。

<div class="revision">$Id: basics.controller.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
