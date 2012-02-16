他のテンプレートシステムを使う
=================================

Yiiでは開発者が好みのテンプレートを使ってビューを書くことができます。(例：PradoやSmartyなど)
これは[viewRenderer|CWebApplication::viewRenderer]を作成して、コンポーネントに追加することで可能になります。
ビューレンダラは[CBaseController::renderFile]の呼び出しを横取りして、
ビューファイルをカスタマイズされたテンプレート文法でコンパイルし、結果を表示します。

> 情報: カスタマイズされたテンプレート文法を利用するのは、
ビューが再利用されないような場合に限ることをおすすめします。
そうしないと、他の開発者がビューを利用する際にカスタマイズされたテンプレート文法を、
必ず利用しなければならなくなるからです。

以下に[CPradoViewRenderer]を使う方法を示します。
ビューを[Prado framework](http://www.pradosoft.com/)と似た文法で書くことができます。
独自のレンダラを開発したい場合は、 [CPradoViewRenderer]は良い参考になるでしょう。

`CPradoViewRenderer`を使う
--------------------------

[CPradoViewRenderer]を使うには、以下のようにアプリケーション設定を変更する必要があります。

~~~
[php]
return array(
	'components'=>array(
		......,
		'viewRenderer'=>array(
			'class'=>'CPradoViewRenderer',
		),
	),
);
~~~

デフォルトでは、[CPradoViewRenderer]はビューファイルをコンパイルした結果を
[runtime](/doc/guide/basics.convention#directory)ディレクトリ以下に保存します。
ビューテンプレートが変更された場合のみ、コンパイルされたファイルが再生成されるので、
[CPradoViewRenderer]を使ってもパフォーマンスの低下はほんの少しです。

> ヒント: [CPradoViewRenderer] では、主にビューの記述を簡単かつすばやくできる新しいタグを
導入しますが、それらを使わずにPHPコードをそのまま書くことも可能です。

以下では[CPradoViewRenderer]で利用可能なテンプレートタグを紹介します。

### ショートPHPタグ

ショートPHPタグは、ビューでPHPコードを書く際のショートカットです。
`<%= expression %>`という出力タグは、`<?php echo expression ?>`と同じ意味です。
`<% statement %>`という宣言タグは、`<?php statement ?>`と同じ意味です。
たとえば、

~~~
[php]
<%= CHtml::textField($name,'value'); %>
<% foreach($models as $model): %>
~~~

というタグは、

~~~
[php]
<?php echo CHtml::textField($name,'value'); ?>
<?php foreach($models as $model): ?>
~~~
と同じです。

### コンポーネントタグ

コンポーネントタグは[widget](/doc/guide/basics.view#widget)をビューに挿入する際に利用されます。
以下のような文法で利用します。

~~~
[php]
<com:WidgetClass property1=value1 property2=value2 ...>
	// ウィジェットのbody content
</com:WidgetClass>

// body contentなしのウィジェット
<com:WidgetClass property1=value1 property2=value2 .../>
~~~

`WidgetClass`がウィジェットクラスの名前かクラスを指定する一方で、
初期プロパティ値は文字列か、中括弧で囲まれたPHPコードで指定可能です。
例えば、

~~~
[php]
<com:CCaptcha captchaAction="captcha" showRefreshButton={false} />
~~~

この内容は、

~~~
[php]
<?php $this->widget('CCaptcha', array(
	'captchaAction'=>'captcha',
	'showRefreshButton'=>false)); ?>
~~~

このように翻訳されます。

> 注意: `showRefreshButton`の値が、`"false"`ではなく、`{false}`と指定されていることに注意してください。
なぜなら、後者の書き方では単なる文字列を意味し、ブール値ではなくなってしまうからです。

### キャッシュタグ

キャッシュタグは[fragment
caching](/doc/guide/caching.fragment)を利用するためのショートカットです。
以下のように書きます。

~~~
[php]
<cache:fragmentID property1=value1 property2=value2 ...>
	// キャッシュされる内容
</cache:fragmentID >
~~~

`fragmentID`がキャッシュされる内容を一意に決定する識別子です。
プロパティと値のペアでキャッシュの設定を指定します。
例えば、

~~~
[php]
<cache:profile duration={3600}>
	// ユーザプロファイル情報
</cache:profile >
~~~

この記述は、

~~~
[php]
<?php if($this->beginCache('profile', array('duration'=>3600))): ?>
	// ユーザプロファイル情報
<?php $this->endCache(); endif; ?>
~~~

このように翻訳されます。

### クリップタグ

キャッシュタグと同じように、クリップタグはビューファイルで
[CBaseController::beginClip] と [CBaseController::endClip] を呼ぶためのショートカットです。
以下のように記述します。

~~~
[php]
<clip:clipID>
	// クリップされる内容
</clip:clipID >
~~~

`clipID`がクリップされる内容を一意に決定する識別子です。

~~~
[php]
<?php $this->beginClip('clipID'); ?>
	// クリップされる内容
<?php $this->endClip(); ?>
~~~

### コメントタグ

コメントタグはビューで開発者のみが読めるコメントを書くために使われます。
コメントタグはビューがユーザに表示されたときにはなくなっています。
以下のように記述します。

~~~
[php]
<!---
ビューコメントは取り除かれます
--->
~~~

テンプレート形式を混用する
-----------------------

バージョン 1.1.2 以降、何らかの代替テンプレート文法を通常の PHP 文法と併用することが可能になりました。
そうするためには、インストールされているビューレンダラの [CViewRenderer::fileExtension]
プロパティを `.php` 以外の値に構成する必要があります。例えば、プロパティを `.tpl` と設定すると、
`.tpl` で終るすべてのビューファイルがインストールされたビューレンダラを使って表示され、一方、
`.php` で終るその他のすべてのビューファイルは通常の PHP ビュースクリプトとして取り扱われる
ことになります。

<div class="revision">$Id: topics.prado.txt 3226 2010-03-31 19:46:37Z qiang.xue $</div>
