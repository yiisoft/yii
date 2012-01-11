ユーザメニューポートレットの作成
==========================

要求分析に基けば、3種のポートレットが必要だと判明しました。
それらは「ユーザメニュー」ポートレット、「タグクラウド」ポートレット、「最近のコメント」ポートレットです。
これらのポートレットを、Yiiが提供する[CPortlet]ウイジェットを拡張して開発します。

この章では、最初の具体的なポートレットを開発します。それはユーザメニューポートレットで、認証されたユーザにのみ
提供され、メニュー項目のリストが表示されるものです。メニューは以下の4項目です。

 * コメント許可: 許可待ちとなっているコメントのリストへのハイパーリンク
 * 新しいポストの作成: 新しいポスト作成ページへのハイパーリンク
 * ポストの管理: ポスト管理ページへのハイパーリンク
 * ログアウト: 現在のユーザに対するログアウトボタン


`UserMenu`クラスの作成
-------------------------

ユーザメニューポートレットの論理部分を表現する`UserMenu`クラスを作成します。 
このクラスはファイル`/wwwroot/blog/protected/components/UserMenu.php`に格納され、
以下のような内容を持ちます。

~~~
[php]
Yii::import('zii.widgets.CPortlet');

class UserMenu extends CPortlet
{
	public function init()
	{
		$this->title=CHtml::encode(Yii::app()->user->name);
		parent::init();
	}

	protected function renderContent()
	{
		$this->render('userMenu');
	}
}
~~~

`UserMenu`クラスは、`zii`ライブラリのクラスである`CPortlet`クラスを拡張します。
`CPortlet`の`init()`メソッドと`renderContent()`メソッドをオーバライドします。
前者はポートレットタイトルを現在のユーザ名に設定します。
後者は`userMenu`というビューを描画することによりポートレットのボディ内容を生成します。

> Tip|情報: 最初に`CPortlet`クラスにアクセスする前に`Yii::import()`を呼んで、明示的に`CPortlet`クラスを含めなければならないことに注意してください。
これは`CPortlet` が`zii`プロジェクトの一部であり、それはYiiの公式な拡張ライブラリであるためです。
性能を考慮するため、このプロジェクトはコアクラスとしてリストされていません。そのため、最初に使う前にはインポートする必要があります。


`userMenu`ビューの作成
------------------------

次に、`/wwwroot/blog/protected/components/views/userMenu.php`として格納される`userMenu`ビューを作成します。

~~~
[php]
<ul>
	<li><?php echo CHtml::link('Create New Post',array('post/create')); ?></li>
	<li><?php echo CHtml::link('Manage Posts',array('post/admin')); ?></li>
	<li><?php echo CHtml::link('Approve Comments',array('comment/index'))
		. ' (' . Comment::model()->pendingCommentCount . ')'; ?></li>
	<li><?php echo CHtml::link('Logout',array('site/logout')); ?></li>
</ul>
~~~

> Info|情報: デフォルトでは、ウィジェットのビューファイルは、ウィジェットクラスファイルを含むディレクトリの`views`サブディレクトリの下に配置されるべきです。
ファイル名はビュー名と等しい必要があります。


`UserMenu`ポートレットの使用
------------------------

新しく完成した`UserMenu`ポートレットを使うときがきました。
レイアウトビューファイル`/wwwroot/blog/protected/views/layouts/column2.php`を以下のように変更します。

~~~
[php]
......
<div id="sidebar">
	<?php if(!Yii::app()->user->isGuest) $this->widget('UserMenu'); ?>
</div>
......
~~~

上記においては、`widget()`メソッドを`UserMenu`クラスインスタンスの生成と実行のためにコールしています。
このポートレットは認証済みユーザにのみ表示されるべきであるため、現行ユーザの`isGuest`プロパティが偽
(このユーザが認証されていることを意味する)の場合にのみ`widget()`をコールします。

`UserMenu`ポートレットの試験
--------------------------

今迄に開発したものの試験をしましょう。

 1. ブラウザウインドウを開き、URL`http://www.example.com/blog/index.php`を入力してください。
ページのサイドバーセクションに何も表示されないことを確認してください。
 2. `Login`ハイパーリンクをクリックし、ログインフォームに入力してログインしてください。
もしログイン成功の場合は、`UserMenu`ポートレットがサイドバーに表れ、ポートレットタイトルがユーザ名となっていることを
確認してください。
 3. `UserMenu`ポートレットの'Logout'ハイパーリンクをクリックしてください。
ログアウト動作が成功し、`UserMenu`ポートレットが消えることを確認してください。


まとめ
-------

今まで開発したものはポートレットであり、とても再利用可能なものです。
別のプロジェクトにおいて少々の修正あるいは全く修正せずに再利用が容易に可能です。
それだけでなく、このポートレットは論理と表現は分離されるべきであるという哲学に非常にあう設計となっています。
この点に関して以前の章ではことさら指摘しませんでしたが、このようなことは典型的なYiiアプリケーションのほとんどすべてで使用されています。

<div class="revision">$Id: portlet.menu.txt 1739 2010-01-22 15:20:03Z qiang.xue $</div>
