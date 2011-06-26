フォームの作成
=============

`login` ビューを書くのは簡単な事です。始めに、先ほど述べた `login` 
アクションのURLを属性とする `form` タグを書きます。次に `LoginForm` 
クラスで定義された属性の為のラベルとインプットフィールドを挿入します。
最後にユーザがクリックしてフォームの内容を送信する為の送信ボタンを
挿入します。これらは全て、純粋なHTMLコードで成し遂げられます。

Yiiはビューの作成を手助けするいくつかのヘルパークラスを提供します。
例えば、テキスト入力フィールドを作成する為に、[CHtml::textField()] 
をコールする事が出来ます; ドロップダウンリストの作成の為には、
[CHtml::dropDownList()] をコールできます。

> Info|情報: 素のHTMLコードと比べて、同じような量のコードが必要なら、
>ヘルパーを使う事に何の利益があるのかと不思議に思うかも知れません。
>それに対する回答は、ヘルパーは、ただのHTMLコードよりも多くの機能を
>もたらすと言う事です。例えば下記のコードは、もしユーザが値を変更した時に、
>送信をトリガーする事が可能なテキスト入力フィールドを生成します。
> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
> このヘルパーが無ければ、ごちゃごちゃしたJavaScriptをあちこちへ書く必要があるでしょう。


下記では [CHtml] をログインフォームの作成に使用しています。ここでは
 `$model` という変数が `LoginForm` のインスタンスを表していると仮定します。

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>

	<?php echo CHtml::errorSummary($model); ?>

	<div class="row">
		<?php echo CHtml::activeLabel($model,'username'); ?>
		<?php echo CHtml::activeTextField($model,'username') ?>
	</div>

	<div class="row">
		<?php echo CHtml::activeLabel($model,'password'); ?>
		<?php echo CHtml::activePasswordField($model,'password') ?>
	</div>

	<div class="row rememberMe">
		<?php echo CHtml::activeCheckBox($model,'rememberMe'); ?>
		<?php echo CHtml::activeLabel($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

上記のコードは、よりダイナミックなフォームを生成します。例えば、
[CHtml::activeLabel()] はモデルの属性で定められたものに結びついたラベル
を生成します。もし属性が入力エラーを持っていた場合、ラベルのCSSクラスは
 `error` に変更され、表示はラベルに適したCSSスタイルへ変更されるでしょう。
同様に、[CHtml::activeTextField()] は、定義されたモデルの属性の為の
テキスト入力フィールドを生成し、あらゆる入力エラー時に、
そのCSSクラスを変更します。

`yiic` スクリプトで供給される、`form.css` というCSSスタイルファイル
を使用した場合、それによって生成されるフォームは下記の様になるでしょう:

![The login page](login1.png)

![The login with error page](login2.png)

バージョン 1.1.1 以降、[CActiveForm] と呼ばれる新しいウィジェットが提供されて、
フォーム作成がさらに容易になりました。このウィジェットは、クライアントとサーバーの両サイドで
継ぎ目のない一貫したバリデーションをサポートすることが出来ます。[CActiveForm] を使うと、
上記のビューのコードは下記のように書き換えることが出来ます。

~~~
[php]
<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->label($model,'username'); ?>
		<?php echo $form->textField($model,'username') ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'password'); ?>
		<?php echo $form->passwordField($model,'password') ?>
	</div>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
~~~
<div class="revision">$Id: form.view.txt 1751 2010-01-25 17:21:31Z qiang.xue $</div>