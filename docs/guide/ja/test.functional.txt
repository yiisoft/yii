機能テスト
==================

このセクションを読む前に、まずは [Selenium documentation](http://seleniumhq.org/docs/) および [PHPUnit documentation](http://www.phpunit.de/wiki/Documentation) を読むことをお奨めします。以下に Yii において機能テストを書くときの基本的な原則を要約します。

 * ユニットテストと同じように、機能テストは [CWebTestCase] クラスを継承した `XyzTest` というクラスの形で書かれます。ここで `Xyz` はテストされるクラスを表します。`PHPUnit_Extensions_SeleniumTestCase` が [CWebTestCase] の親クラスであるため、この親クラスから継承したすべてのメソッドを使うことが出来ます。

 * 機能テストのクラスは `XyzTest.php` という名前の PHP ファイルとして保存されます。規約により、この機能テストのファイルはディレクトリ `protected/tests/functional` の下に保存します。

 * テストクラスは主として `testAbc` と名付けられた一連のテストメソッドを含みます。ここで `Abc` は、多くの場合、テストされる機能の名前です。例えば、ユーザログインの機能をテストするためには、`testLogin` という名前のテストメソッドを作成します。

 * テストメソッドは、通常、一連の文によって Selenium RC に対するコマンドを発し、テストされるアプリケーションとの相互作用を実行します。また、テストメソッドは、アサーション文によって、ウェブアプリケーションが期待どおりの反応を返すことを確認します。

機能テストの書き方を説明する前に、`yiic webapp` コマンドで生成された `WebTestCase.php` を見てみましょう。このファイルは、すべての機能テストクラスの基本クラスとして働く `WebTestCase` を定義しています。

~~~
[php]
define('TEST_BASE_URL','http://localhost/yii/demos/blog/index-test.php/');

class WebTestCase extends CWebTestCase
{
	/**
	 * 個々のテストメソッドが走る前のセットアップ。
	 * 主としてテストアプリケーションのためにベース URL を設定する。
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
	}

	......
}
~~~

`WebTestCase` クラスは、主として、テストされるページのベース URL をセットします。後のテストメソッドにおいては、テストされるページを指定するのに相対 URL を使用することが出来ます。

テストのベース URL において、エントリスクリプトとして `index.php` ではなく `index-test.php` を使っていることにも注意を払わなければなりません。
`index-test.php` と `index.php` の間の唯一の違いは、アプリケーション構成ファイルとして前者は `test.php` を使い、後者は `main.php` を使う、という点です。

では、[ブログデモ](http://www.yiiframework.com/demos/blog) の投稿記事の表示に関する機能についてテストをする方法を説明します。
最初に、下記のように、テストクラスを書きます。このテストクラスが、たった今説明した基本クラスを継承するものであることに注意してください。

~~~
[php]
class PostTest extends WebTestCase
{
	public $fixtures=array(
		'posts'=>'Post',
	);

	public function testShow()
	{
		$this->open('post/1');
		// サンプルの投稿記事のタイトルが存在することを確認
		$this->assertTextPresent($this->posts['sample1']['title']);
		// コメントフォームが存在することを確認
		$this->assertTextPresent('コメントをどうぞ');
	}

	......
}
~~~

ユニットテストのクラスを書く場合と同じように、このテストによって使用されるフィクスチャを宣言します。
ここでは `Post` フィクスチャが使われなければならないと指示しています。
`testShow` というテストメソッドにおいて、最初に Selenium RC に URL `post/1` を開くように指示します。
これが相対 URL であることに注意してください。
完全な URL は、基本クラスで設定したベース URL の後にこれを追加したもの(つまり `http://localhost/yii/demos/blog/index-test.php/post/1`)になります。
そして、投稿記事 `sample1` のタイトルが現在のウェブページにあることを確認します。さらに、ページの中に `コメントをどうぞ` というテキストが含まれていることも確認します。

> Tip|ヒント: 機能テストを走らせる前に、Selenium-RC サーバを起動する必要があります。そうするためには、Selenium サーバをインストールしたディレクトリで、`java -jar selenium-server.jar` というコマンドを実行します。

<div class="revision">$Id: test.functional.txt 1662 2010-01-04 19:15:10Z qiang.xue $</div>