パフォーマンスチューニング
==================

ウェブアプリケーションのパフォーマンスはさまざまな要因に影響されます。
データベースへのアクセス、ファイルシステム操作、ネットワークの帯域などはすべて潜在的影響要因です。
Yiiはフレームワークによって生じるパフォーマンスへの影響をあらゆる側面に渡って少なくするよう努力していますが、
そでもユーザアプリケーションにおいてはパフォーマンスを改善するさまざまな場所があります。

APCエクステンションを有効にする
----------------------


[PHP APC
extension](http://www.php.net/manual/en/book.apc.php) を有効にすることは、
アプリケーションのパフォーマンスを改善する最も容易な手段でしょう。
APCはPHPの中間コードを最適化し、キャッシュすることで、リクエストごとにPHPスクリプトが解析されるのを避けます。

デバッグモードを無効にする
--------------------

デバッグモードを無効にすることも、パフォーマンスを改善するもうひとつの簡単な方法です。
Yiiアプリケーションは `YII_DEBUG` 定数が true に設定されていると、デバッグモードで動きます。
しかし、デバッグモードではいくつかのコンポーネントが余分な作業を発生させるので、パフォーマンスが低下します。
たとえば、メッセージロガーはあらゆるメッセージが記録される際に、追加のデバッグ情報も記録します。


`yiilite.php`を使う
-------------------

[PHP APC extension](http://www.php.net/manual/en/book.apc.php) が有効である場合、
`yii.php`を `yiilite.php` で置き換えることがで、さらにパフォーマンスを改善できます。

`yiilite.php`はすべてのYiiリリースに含まれています。
その内容はYiiの基本クラスをひとつにまとめたものです。
コメントとトレース命令はすべて取り除かれています。
したがって、`yiilite.php` を使うことで、インクルードされるファイルの数と、トレース命令の実行を減らすことになります。

APC無しで`yiilite.php`を使うことはパフォーマンスを低下させる可能性があることに注意してください。
`yiilite.php`には必ずしもすべてのリクエストで必要ではないクラスが含まれており、追加の解析時間がかかります。
また、たとえAPCが有効であっても、サーバの設定によっては`yiilite.php`を使うとパフォーマンスが低下することがあります。
`yiilite.php`を使うかどうか決める最良の方法は、YiiFrameworkに同梱の`hello world`デモを使ってベンチマークをすることです。

キャッシュ機構を利用する
------------------------

[Caching](/doc/guide/caching.overview)セクションで解説したように、Yiiはいくつかのキャッシュソリューション備えており、
それらを利用することで、ウェブアプリケーションのパフォーマンスを大幅に改善できる可能性があります。
あるデータの生成に長い時間がかかっているなら、[data caching](/doc/guide/caching.data)を使ってデータ生成回数を減らすことができます。
ページの一部があまり変更されないなら、[fragment caching](/doc/guide/caching.fragment)を使って描画される回数を減らすことができます。
ページ全体があまり変更されないなら、[page caching](/doc/guide/caching.page)を使ってページ描画コストを軽減できます。

アプリケーションで [Active Record](/doc/guide/database.ar)を使っているなら、
スキーマキャッシュを有効にして、データベーススキーマの解析時間を減らすべきです。
これは、[CDbConnection::schemaCachingDuration] プロパティを0以上の値にすることで可能です。

これらアプリケーションレベルのキャッシュテクニックとは別に、サーバレベルでのキャッシュソリューションを使ってアプリケーションのパフォーマンスを増加させることも可能です。
実のところ、先ほど述べた[APC caching](/doc/guide/topics.performance#enabling-apc-extension)はこのカテゴリに含まれます。
ほかにも2,3の例を挙げると、[Zend Optimizer](http://www.zend.com/en/products/guard/zend-optimizer),
[eAccelerator](http://eaccelerator.net/),
[Squid](http://www.squid-cache.org/) といったものがあります。

データベースの最適化
---------------------

データベースアクセスはウェブアプリケーションにおいてしばしば主なパフォーマンスボトルネックになります。
キャッシュを利用することで、パフォーマンスの頭打ちを軽減することはできるでしょうが、根本的な解決ではありません。
データベースとクエリを適切にデザインしないと、
データベースに膨大なデータがあって、しかもキャッシュの有効期限が切れている場合、
最新のデータを取得するのは法外なコストがかかります。

データベースのインデックスをかしこく作成しましょう。
インデックスを使えば、`SELECT`クエリはより速くなります。
しかし`INSERT`, `UPDATE`, `DELETE` クエリは遅くなるでしょう。

複雑なクエリには、PHPコードでクエリを発行してDBMSに解析を任せるより、データベースビューを作ることをすすめます。

[Active Record](/doc/guide/database.ar)を使いすぎないでください。
[Active Record](/doc/guide/database.ar) はOOP流にデータをモデリングするには便利ですが、
クエリ結果に対してひとつまたは複数のオブジェクトを作る必要があるため、パフォーマンスを低下させます。
膨大なデータを扱うアプリケーションには、[DAO](/doc/guide/database.dao) を使うか、直接データベースAPIを使うのが賢明な選択でしょう。

言い忘れましたが、`SELECT`クエリで`LIMIT`を使ってください。
こうすることで、大量のデータが返されて、メモリを使い尽くすということがなくなります。

スクリプトファイルを最小化する
-----------------------

複雑なページでは、たくさんの外部JavaScriptファイルやCSSファイルを読み込む必要があります。
各ファイルを読み込むごとにサーバとの通信が発生するので、ファイルはなるべくまとめて少なくするべきです。
ファイルのサイズ自体を減らして、通信時間を減らすことも考えるべきでしょう。
このような作業を助けてくれるツールはたくさんあります。

Yiiでページを表示する場合、コンポーネントが出力するファイルは変更したくない場合があるでしょう。
(例：コアコンポーネントやサードパーティコンポーネントなど)
このようなファイルを最小化するため、二つのステップを踏む必要があります。

まず、[clientScript|CWebApplication::clientScript]コンポーネントの、 [scriptMap|CClientScript::scriptMap]プロパティを設定することで、
最小化するファイルを設定します。
これはPHPコードの中でも設定できます。
例：
~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>'/js/all.js',
	'jquery.ajaxqueue.js'=>'/js/all.js',
	'jquery.metadata.js'=>'/js/all.js',
	......
);
~~~

上記のコードが実行されると、これらのJavaScriptファイルは、`/js/all.js`というURLに割り当てられます。
これらのJavaScriptファイルのいずれかが必要になった場合、Yiiは個別のファイルを読み込むのではなく、
URLを一度だけ読み込みます。

次に、JavaScriptファイルをひとつのファイルにまとめる(そしておそらく圧縮する)ツールを使って、`js/all.js`
というファイルを作ります。

CSSファイルも同じようにします。

さらに、[Google AJAX Libraries API](http://code.google.com/apis/ajaxlibs/)を使って、ページのロード時間を改善することができます。
自分のサーバからではなく、Googleのサーバから`jquery.js`を読み込むことができます。
そのために、まず`scriptMap`を以下のように設定します。

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>false,
	'jquery.ajaxqueue.js'=>false,
	'jquery.metadata.js'=>false,
	......
);
~~~

こうすることで、Yiiが個別のファイルを読み込むのを防ぎます。
代わりに以下のコードを明示的にページに記述します。

~~~
[php]
<head>
<?php echo CGoogleApi::init(); ?>

<?php echo CHtml::script(
	CGoogleApi::load('jquery','1.3.2') . "\n" .
	CGoogleApi::load('jquery.ajaxqueue.js') . "\n" .
	CGoogleApi::load('jquery.metadata.js')
); ?>
......
</head>
~~~

<div class="revision">$Id: topics.performance.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>