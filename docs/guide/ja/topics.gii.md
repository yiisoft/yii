自動的なコード生成
=========================

バージョン 1.1.2 から、Yii は *Gii* と呼ばれるウェブベースのコード生成ツールを持つようになりました。
これは以前のコマンドラインで走る `yiic shell` 生成ツールに取って代るものです。
このセクションでは、開発の生産性を高めるために、どのようにして Gii を使うか、そして、どのようにして Gii を拡張するかを説明します。

Gii を使う
---------

Gii はモジュールの形式で実装されており、既存の Yii アプリケーションの中で使用されなければなりません。Gii を使うためには、最初にアプリケーション構成を次のように修正します。

~~~
[php]
return array(
	......
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'pick up a password here',
			// 'ipFilters'=>array(...a list of IPs...),
			// 'newFileMode'=>0666,
			// 'newDirMode'=>0777,
		),
	),
);
~~~

上記のように、`gii` という名前のモジュールを宣言し、そのクラスが [GiiModule] であることを指定します。同時に、Gii にアクセスするときに尋ねられる、モジュール用のパスワードも設定します。

セキュリティ上の理由から、デフォルトでは、Gii は localhost でのみアクセス可能なように構成されます。
もし、他の信頼できるコンピュータからもアクセス可能なようにしたいのであれば、
上記のコードで示されているように、[GiiModule::ipFilters] プロパティを構成することが出来ます。

Gii は既存のアプリケーションの中で新しいコードファイルを生成して保存する場合がありますので、ウェブサーバのプロセスがそのようにするための適切な権限を持っていることを確かめる必要があります。上記の [GiiModule::newFileMode] と [GiiModule::newDirMode] のプロパティは、新しいファイルとディレクトリがどういうモードで作成されるべきかを制御するものです。

> Note|注意: Gii は主として開発ツールとして提供されています。従って、開発マシンにのみインストールされるべきです。Gii は新しい PHP スクリプトファイルをアプリケーションの中に生成することが出来るものですから、そのセキュリティの確保(パスワードや IP フィルターなど)については十分な注意を払わなければなりません。

さて、以上で、URL `http://hostname/path/to/index.php?r=gii` によって Gii にアクセスすることが出来ます。ここで `http://hostname/path/to/index.php` は既存の Yii アプリケーションにアクセスするための URL であると仮定しています。

もし既存の Yii アプリケーションが `path` 形式の URL ([URL management](/doc/guide/topics.url) を参照)を使っているのであれば、URL `http://hostname/path/to/index.php/gii` によって Gii にアクセス出来ます。
場合によっては、既存の URL 規則の先頭に次の URL 規則を追加する必要があるかもしれません。

~~~
[php]
'components'=>array(
	......
	'urlManager'=>array(
		'urlFormat'=>'path',
		'rules'=>array(
			'gii'=>'gii',
			'gii/<controller:\w+>'=>'gii/<controller>',
			'gii/<controller:\w+>/<action:\w+>'=>'gii/<controller>/<action>',
			...既存の規則...
		),
	),
)
~~~

Gii にはデフォルトでいくつかのコードジェネレータが含まれています。各コードジェネレータは特定のタイプのコードを生成する役目を持っています。
例えば、コントローラジェネレータは、コントローラクラスといくつかのアクションのビュースクリプトを生成します。
また、モデルジェネレータは指定されたデータベーステーブルのためのアクティブレコードクラスを生成します。

ジェネレータを使用するときの基本的なワークフローは以下の通りです。

1. ジェネレータのページに入る。
2. コード生成のパラメータを指定するフィールドに入力する。例えば、新しいモジュールを作成するためのモジュールジェネレータを使用する場合は、モジュール ID を入力する必要があります。
3. `Preview` ボタンを押して生成されるコードをプレビューする。生成されるコードファイルの一覧がテーブルで表示されます。どれでもクリックすればコードをプレビューすることが出来ます。
4. `Generate` ボタンを押してコードファイルを生成する。
5. コード生成ログを確認する。


Gii を拡張する
-------------

Gii が内蔵しているデフォルトのコードジェネレータは非常に強力なコードを生成することが出来ますが、自分自身の好みや必要に応じて、機能をカスタマイズしたり、新しい機能を作成したりしたいこともしばしばあります。例えば、生成されるコードを自分の好みのコーディングスタイルにしたい、とか、生成されるコードが複数の言語をサポートするようにしたい、とかです。Gii ではこのようなことはすべて簡単にできます。

Gii は二つの方法で拡張できます。既存のコードジェネレータのコードテンプレートをカスタマイズするという方法と、新しいコードジェネレータを書くという方法です。

### コードジェネレータの構造

コードジェネレータは、それぞれ、一つのディレクトリの下に格納されています。そのディレクトリの名前がジェネレータの名前として扱われます。ディレクトリは通常下記の内容から構成されます。

~~~
model/                       モデルジェネレータのルートフォルダ
   ModelCode.php             コード生成に使われるコードのモデル
   ModelGenerator.php        コード生成コントローラ
   views/                    ジェネレータのためのビュースクリプト
      index.php              デフォルトのビュースクリプト
   templates/                コードテンプレートセットを格納
      default/               'default' のコードテンプレートセット
         model.php           モデルクラスのコードを生成するためのコードテンプレート
~~~

### ジェネレータのサーチパス

Gii は利用可能なジェネレータを [GiiModule::generatorPaths] プロパティで指定される一連のディレクトリから探します。
カスタマイズが必要な場合は、次のようにして、アプリケーション構成の中でこのプロパティを構成することが出来ます。

~~~
[php]
return array(
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'generatorPaths'=>array(
				'application.gii',   // パスエイリアス
			),
		),
	),
);
~~~

上記の構成は、Gii に対して、デフォルトの場所である `system.gii.generator` に加えて `application.gii` というエイリアスのディレクトリの下からもジェネレータを探すように指示するものです。

異なるサーチパスの下に同じ名前の二つのジェネレータを置くことも可能です。その場合は、[GiiModule::generatorPaths] で先に指定されたパスの下にあるジェネレータが優先されます。


### コードテンプレートをカスタマイズする

この方法が Gii を拡張するもっとも簡単でもっともよく使われる方法です。
例を使ってコードテンプレートをカスタマイズする方法を説明しましょう。
モデルジェネレータによって生成されるコードをカスタマイズしたいとします。

最初に、`protected/gii/model/templates/compact` というディレクトリを作成します。ここで `model` は、デフォルトのモデルジェネレータを *オーバーライド* しようとしていることを意味します。そして `templates/compact` は、`compact` という名前の新しいコードテンプレートセットを追加しようとしていることを意味します。

次に、前のサブセクションで見たように、アプリケーション構成を修正して、[GiiModule::generatorPaths] に `application.gii` を追加します。

ここでモデルのコードジェネレータのページを開いて下さい。`Code Template` フィールドをクリックすると、新しく作ったテンプレートディレクトリ `compact` を含んだドロップダウンリストが表示される筈です。ただし、このテンプレートを選んでコードを生成しようとすると、エラーが表示されます。これは、この新しい `compactp テンプレートセットには、まだ一つも実際のコードテンプレートファイルを入れていないからです。

ファイル `framework/gii/generators/model/templates/default/model.php` を `protected/gii/model/templates/compact` にコピーします。もう一度 `compact` テンプレートで生成を試みると、今度は成功します。ただし、生成されるコードは `default` テンプレートセットで生成されるものと違いはありません。

今こそ実際にカスタマイズの仕事をすべき時です。ファイル `protected/gii/model/templates/compact/model.php` を開いて編集します。このファイルはビュースクリプトのように使われること、すなわち、PHP の式や文を含むことが出来るということを憶えておいて下さい。テンプレートを修正して、生成されるコードの `attributeLabels()` メソッドが `Yii::t()` を使って属性のラベルを翻訳するようにしましょう。

~~~
[php]
public function attributeLabels()
{
	return array(
<?php foreach($labels as $name=>$label): ?>
			<?php echo "'$name' => Yii::t('application', '$label'),\n"; ?>
<?php endforeach; ?>
	);
}
~~~

それぞれのコードテンプレートの中では、上記の例の `$label` のような、いくつかの定義済み変数にアクセスすることが出来ます。これらの変数は対応するコードジェネレータによって提供されます。コードジェネレータが異なれば、そのコードテンプレートで提供される変数のセットも異なるものになり得ます。default コードテンプレートにある説明を注意深く読んで下さい。


### 新しいジェネレータを作成する

このサブセクションでは、新しいウィジェットクラスを生成する事が出来る新しいウィジェットジェネレータの作り方を説明します。

最初に `protected/gii/widget` という名前のディレクトリを作成します。このディレクトリの下に、以下のファイルを作成することになります。

* `WidgetGenerator.php`: `WidgetGenerator` コントローラクラスを含むファイル。このクラスがウィジェットジェネレータのエントリーポイントです。
* `WidgetCode.php`: `WidgetCode` モデルクラスを含むファイル。このクラスがコード生成のための主なロジックを持ちます。
* `views/index.php`: コードジェネレータの入力フォームを表示するビュースクリプト。
* `templates/default/widget.php`: ウィジェットのクラスファイルのための default コードテンプレート。


#### `WidgetGenerator.php` を作成する

`WidgetGenerator.php` というファイルは極端なまでに簡単です。それは下記のコードを含むだけのものです。

~~~
[php]
class WidgetGenerator extends CCodeGenerator
{
	public $codeModel='application.gii.widget.WidgetCode';
}
~~~

上記のコードで、このジェネレータが `application.gii.widget.WidgetCode` というパスエイリアスのモデルクラスを使用することを指定しています。`WidgetGenerator` クラスは [CCodeGenerator] を継承するものですが、この `CCodeGenerator` が、コード生成のプロセスを統合するのに必要となるコントローラアクションを含めて、数多くの機能を実装しています。

#### `WidgetCode.php` を作成する

`WidgetCode.php` ファイルは `WidgetCode` モデルクラスを格納しますが、このモデルクラスが、ユーザの入力に基づいてウィジェットクラスを生成するための主なロジックを持ちます。この例においては、ユーザに求める唯一の入力はウィジェットクラス名であると仮定します。このとき、`WidgetCode` は以下のようなものになります。

~~~
[php]
class WidgetCode extends CCodeModel
{
	public $className;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('className', 'required'),
			array('className', 'match', 'pattern'=>'/^\w+$/'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'className'=>'ウィジェットクラス名',
		));
	}

	public function prepare()
	{
		$path=Yii::getPathOfAlias('application.components.' . $this->className) . '.php';
		$code=$this->render($this->templatepath.'/widget.php');

		$this->files[]=new CCodeFile($path, $code);
	}
}
~~~

`WidgetCode` クラスは [CCodeModel] からの継承です。通常のモデルクラスと同じように、このクラスの中で `rules()` や `attributeLabels()` を宣言して、ユーザ入力を検証したり、属性のラベルを提供したりすることが出来ます。基本クラスである [CCodeModel] が既にいくつかのルールやラベルを定義しているため、それらをここで新しいルールやラベルとマージしなければならないことに注意して下さい。

`prepare()` メソッドが生成されるコードを準備します。その主な仕事は、[CCodeFile] オブジェクトのリストを準備することです。このオブジェクトのそれぞれが生成される一つのコードファイルを表します。この例では、生成されるウィジェットクラスファイルを表す [CCodeFile] オブジェクトを一つだけ作成すれば十分です。新しいウィジェットクラスは `protected/components` ディレクトリの下に生成されます。そして、[CCodeFile::render] メソッドを呼んで、実際のコードを生成する事が出来ます。このメソッドはコードテンプレートを PHP スクリプトとしてインクルードして、echo で出力された内容を生成されたコードとして返します。


#### `views/index.php` を作成する

コントローラ(`WidgetGenerator`)とモデル(`WidgetCode`)が揃ったので、今度はビュー `views/index.php` を作成する時です。

~~~
[php]
<h1>ウィジェットジェネレータ</h1>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'className'); ?>
		<?php echo $form->textField($model,'className',array('size'=>65)); ?>
		<div class="tooltip">
			ウィジェットクラス名は単語構成文字だけで指定して下さい。
		</div>
		<?php echo $form->error($model,'className'); ?>
	</div>

<?php $this->endWidget(); ?>
~~~

上記においては、主として [CCodeForm] ウィジェットを使ってフォームを表示しています。このフォームは `WidgetCode` の `className` 属性に対する入力を収集するフィールドを表示を表示します。

フォームを作成するときには、[CCodeForm] ウィジェットによって提供される二つの便利な機能を利用することが出来ます。一つは入力ツールチップ、もう一つは付箋(sticky)インプットです。

どれか default のコードジェネレータで試してみると分りますが、一つの入力フィールドにフォーカスをセットすると、フィールドの隣に便利なツールチップが出てきます。これは、入力フィールドの次に、CSS クラスを `tooltip` にした `div` を書くことによって、簡単に実現することができます。

いくつかの入力フィールドでは、最後に入力された正しい値を記憶しておきたいと思うでしょう。そうすることで、ユーザがジェネレータを使ってコードを生成するたびに、毎回同じ値を入力しなければならないという苦労を無くすことが出来ます。その例は、default のコントローラジェネレータがコントローラの基本クラス名を収集する入力フィールドです。これらの付箋フィールドは初期状態ではハイライトされた静的テキストとして表示されますが、クリックすると、ユーザ入力を受け付ける入力フィールドに変ります。

入力フィールドを付箋タイプとして宣言したい場合は、二つのことをする必要があります。

最初に、対応するモデル属性に対して、`sticky` というバリデーションルールを宣言しなければなりません。例えば、default のコントローラジェネレータは、`baseClass` および `actions` の属性が sticky であると宣言するために、次のようなルールを持っています。

~~~
[php]
public function rules()
{
	return array_merge(parent::rules(), array(
		......
		array('baseClass, actions', 'sticky'),
	));
}
~~~

第二に、ビューの中で、入力フィールドのコンテナ `div` に `sticky` という名前の CSS クラスを追加しなければなりません。以下のようにします。

~~~
[php]
<div class="row sticky">
	...input field here...
</div>
~~~

#### `templates/default/widget.php` を作成する

最後に、コードテンプレート `templates/default/widget.php` を作成します。
前に述べたように、このファイルはビュースクリプトのように使われるもので、PHP の式や文を含むことが出来ます。コードテンプレートの中では、いつでも `$this` という変数にアクセスして、コードモデルのオブジェクトを参照することが可能です。この例では、`$this` は `WidgetModel` オブジェクトを参照します。従ってユーザが入力したウィジェットクラス名は、`$this->className` によって取得出来ます。

~~~
[php]
<?php echo '<?php'; ?>

class <?php echo $this->className; ?> extends CWidget
{
	public function run()
	{

	}
}
~~~

これで新しいコードジェネレータの作成は完了です。
URL `http://hostname/path/to/index.php?r=gii/widget` によって、ただちにこのコードジェネレータにアクセスすることが出来ます。

<div class="revision">$Id: topics.gii.txt 3223 2010-04-17 22:33:46Z qiang.xue $</div>
