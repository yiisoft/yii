エクステンションを使う
================

エクステンションを使うには、通常、次の3つのステップが必要です:

  1. Yii's [extension repository](http://www.yiiframework.com/extensions/) より、
     エクステンションをダウンロードします。
  2. エクステンションを [アプリケーションベースディレクトリ](/doc/guide/basics.application#application-base-directory)
     のサブディレクトリである `extensions/xyz` 以下に解凍します。
     `xyz` にはエクステンション名が入ります。
  3. インポート、初期構成を行い、エクステンションを使用します。

各エクステンションは全てのエクステンション中でそれを特定するためのユニークな名前を持ちます。
エクステンション名を `xyz` にした場合、`xyz` のすべてのファイルを含む
ベースディレクトリを示す、`ext.xyz` というパスエイリアスを
いつでも使用できます。

異なるエクステンションは、インポート、初期構成、使用方法に関する異なる要件があります。
以下では、エクステンションに関して、[概要](/doc/guide/extension.overview) で記述したカテゴリに従い、
一般的な使用方法のシナリオをまとめます。


Ziiエクステンション
-------------------

サードパーティー製のエクステンションの使い方を説明する前に、Ziiエクステンションライブラリを紹介したいと思います。
これはYii開発チームによって開発された一連のエクステンションで、すべてのリリースに含まれています。

Ziiエクステンションを使う場合は、対応するクラスを`zii.path.to.ClassName`という形式のパスエイリアスで指定しなければなりません。
ここでルートのエイリアス`zii`はYiiによってあらかじめ定義されたもので、Ziiライブラリのルートディレクトリを示すものです。
例えば、[CGridView]を使う場合は、このエクステンションを参照するときにビュースクリプトの中で次のようなコードを使用します。

~~~
[php]
$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
));
~~~


アプリケーションコンポーネント
---------------------

[アプリケーションコンポーネント](/doc/guide/basics.application#application-component) を使用するために、
最初に [アプリケーション初期構成](/doc/guide/basics.application#application-configuration) へ、
`components` プロパティを以下のように追加する必要があります:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'ext.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // 他のコンポーネント初期構成
    ),
);
~~~

その後に、どこでも `Yii::app()->xyz` を使って、コンポーネントにアクセスできます。
コンポーネントは `preload` プロパティにリストしない限り、
初めてアクセスした時に生成されます。

ビヘイビア
----------

[ビヘイビア](/doc/guide/basics.component#component-behavior) はすべての種類のコンポーネントの中で使用することが出来ます。
ビヘイビアの使用方法は二つのステップから成ります。最初のステップで、ビヘイビアが対象のコンポーネントにアタッチされます。
第二のステップで、対象のコンポーネントを通じてビヘイビアのメソッドが呼ばれます。
例えば

~~~
[php]
// $name がコンポーネントの中におけるビヘイビアを特定する
$component->attachBehavior($name,$behavior);
// test() は $behavior のメソッド
$component->test();
~~~

しばしば、ビヘイビアは`attachBehavior`メソッドを使うのでなく、構成的な方法によってコンポーネントにアタッチされます。
例えば、[アプリケーションコンポーネント](/doc/guide/basics.application#application-component)にビヘイビアをアタッチするために、
次のような, [アプリケーション構成](/doc/guide/basics.application#application-configuration)を使うことが出来ます。:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'behaviors'=>array(
				'xyz'=>array(
					'class'=>'ext.xyz.XyzBehavior',
					'property1'=>'value1',
					'property2'=>'value2',
				),
			),
		),
		//....
	),
);
~~~

上記のコードは、`xyz`ビヘイビアを`db`アプリケーションコンポーネントにアタッチします。
このように出来るのは、[CApplicationComponent]が`behaviors`という名前のプロパティを定義しているからです。
このプロパティにビヘイビア設定のリストをセットすると、コンポーネントは初期化されるときに対応するビヘイビアをアタッチします。

[CController]、[CFormModel]、そして、[CActiveRecord]は、通常、継承して使用されるべきクラスですが、これらのクラスでは`behaviors()`メソッドをオーバーライドすることによってビヘイビアをアタッチすることが出来ます。これらのクラスは、このメソッドの中で宣言されているすべてのビヘイビアを、初期化の最中に自動的にアタッチします。
例えば、

~~~
[php]
public function behaviors()
{
	return array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzBehavior',
			'property1'=>'value1',
			'property2'=>'value2',
		),
	);
}
~~~


ウィジェット
------

[ウィジェット](/doc/guide/basics.view#widget) は主に
[ビュー](/doc/guide/basics.view) 中で使用されます。
ウィジットクラス `XyzClass` が 'xyz' エクステンションに属している場合、
ビュー中で下記のように使用できます。

~~~
[php]
// 本文（body content）を必要としないウィジェット
<?php $this->widget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// 本文（body content）を含められるウィジェット
// widget that can contain body content
<?php $this->beginWidget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...ウィジェットの本文（body content）...

<?php $this->endWidget(); ?>
~~~

アクション
------

[アクション](/doc/guide/basics.controller#action) は、
特定のユーザーリクエストに応答するために [コントローラ](/doc/guide/basics.controller) により使用されます。
アクションクラス `XyzClass` が `xyz` エクステンションに属している場合、
コントローラクラスで、[CController::actions] メソッドを上書き（オーバーライド）すれば
それを使用できます:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// other actions
		);
	}
}
~~~

その後、 [ルート（道筋）](/doc/guide/basics.controller#route) `test/xyz`
を用いて、アクションにアクセスできます。

フィルタ
------
[フィルタ](/doc/guide/basics.controller#filter) もまた、
[コントローラ](/doc/guide/basics.controller) により使用されます。
それらは主に、[アクション](/doc/guide/basics.controller#action) により処理される
ユーザーリクエストの前処理や後処理です。
フィルタクラス `XyzClass` が `xyz` エクステンションに属している場合、
コントローラクラスで、[CController::filters] メソッドを上書き（オーバーライド）すれば
それを使用できます:

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// 他のフィルタ
		);
	}
}
~~~

上記では、限られたアクションだけにフィルタに適用するために、
最初の配列要素でプラスやマイナス演算子を使用できます。
詳細については、[CController] ドキュメントを参照してください。

コントローラ
----------
[コントローラ](/doc/guide/basics.controller) は、
ユーザーが要求できるアクションのセットを提供します。
コントローラエクステンションを使用するために、
[アプリケーション初期構成](/doc/guide/basics.application#application-configuration) 中の
[CWebApplication::controllerMap] プロパティへの初期構成が必要です:

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// 他のコントローラ
	),
);
~~~

その後、[ルート（道筋）](/doc/guide/basics.controller#route) `xyz/a` を
用いてコントローラのアクション `a` にアクセスできます。

バリデータ
---------
バリデータは主に、 [モデル](/doc/guide/basics.model) クラス
（[CFormModel] か [CActiveRecord] のどちらかから継承された）により使用されます。
バリデータクラス `XyzClass` が `xyz` エクステンションに属している場合、
モデルクラスで、[CModel::rules] メソッドを上書き（オーバーライド）すれば
それを使用できます:

~~~
[php]
class MyModel extends CActiveRecord // または CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// 他のバリデーションルール
		);
	}
}
~~~

コンソールコマンド
---------------
[コンソールコマンド](/doc/guide/topics.console) エクステンションは、通常、
追加コマンドにより `yiic` ツールを強化します。
コンソールコマンド `XyzClass` が `xyz` エクステンションに属している場合、
コンソールアプリケーション用の初期構成を設定することで
それを使用できます:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// 他のコマンド
	),
);
~~~

その後、追加コマンド `xyz` を備えている `yiic` ツールを使用できます。

> Note|注意: コンソールアプリケーションは、通常、ウェブアプリケーションで
使用されるものとは異なる初期構成ファイルを使用します。
もし、アプリケーションを `yiic webapp` コマンドを使用して作成した場合、
コンソールアプリケーション `protected/yiic` 用の初期構成ファイルは、
`protected/config/console.php`、また、ウェブアプリケーション用の
初期構成ファイルは `protected/config/main.php` です。

モジュール
------
モジュールの使用方法については、[モジュール](/doc/guide/basics.module#using-module)
に関するセクションを参照してください。


一般的なコンポーネント
-----------------
一般的な [コンポーネント](/doc/guide/basics.component) を使用するには、
はじめに、下記のようにそのクラスファイルをインクルードする必要があります。

~~~
Yii::import('ext.xyz.XyzClass');
~~~

その後、クラスのインスタンスの生成やプロパティの設定、メソッドのコールを
行えます。それを継承して新しい子クラスを作成してもいいです。

<div class="revision">$Id: extension.use.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>