URLの取り扱い
============

Webアプリケーションの包括的なURLの取り扱いには2つの側面があります。1つ目は、
ユーザのリクエストが URL の形式で来たときに、アプリケーションはそれを解析して理解できるパラメータに変換する必要がある、ということです。そして、2つ目は、
アプリケーションが理解できる形式の URL を作成する方法を、アプリケーション自身が提供する必要がある、ということです。
Yii アプリケーションでは、この二つは [CUrlManager] の手助けによって行われます。

URLの作り方
-------------

URLはコントローラービューの中に直接コーディングしてしまう事も出来ますが、
次の様に動的な書き方をすることで融通が利く場合が多いでしょう:

~~~
[php]
$url=$this->createUrl($route,$params);
~~~

`$this` はコントローラのインスタンス;
`$route` はリクエストの[route] (/doc/guide/basics.controller#route);
そして `$param` は URL に付加される`GET`パラメータのリストです。

デフォルトでは、[createUrl|CController::createUrl] によって作成される URL は
いわゆる `get` クエリの書式になります。例えば、`$route='post/read'` かつ
 `$params=array('id'=>100)` の場合の URL は以下の通りです:

~~~
/index.php?r=post/read&id=100
~~~

GET クエリの書式は `パラメータ名=値` の一組をアンパサンド(&)で繋げたリストになっていて、
 `r` のパラメータはリクエストされた [route](/doc/guide/basics.controller#route) を示しています。このURLの書式は
いくつか単語構成文字以外の文字を含んでいる為、あまりユーザフレンドリーとは
言えないでしょう。

上記の URL は、いわゆる `path` 形式を使って、もっとすっきりして分り易いものにすることも出来ます。
`path` 形式は、クエリ文字列を除去して、GET パラメータを URL のパス情報の部分に入れるものです。

~~~
/index.php/post/read/id/100
~~~

URL の書式を変えるには、アプリケーションコンポーネントの
[urlManager|CWebApplication::urlManager] を以下で記述する様に設定します。
設定によって [createUrl|CController::createUrl] は自動的に新しい書式の
URL を作成するようになり、アプリケーションは作成された URL を正しく認識
出来るようになります:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
		),
	),
);
~~~

[urlManager|CWebApplication::urlManager] コンポーネントのクラスを指定する必要はありません。[CWebApplication] の中で [CurlManager] であることがすでに宣言済みです。

> Tip|ヒント: [createUrl|CController::createUrl]で作成された URL は相対 URL です。
絶対 URL が必要な時は、`Yii::app()->request->hostInfo`をプリフィックスにするか、
[createAbsoluteUrl|CController::createAbsoluteUrl] をコールします。

ユーザフレンドリーなURL
------------------

`path` が URL 形式として用いられる場合、いくつかのルールを指定する事で URL をもっと
ユーザフレンドリーにできます。例えば `/index.php/post/read/id/100`
といった長ったらしい URL の代わりに `/post/100` の様な短い URL を作成することができます。
URL の規則が、[CUrlManager] によって、URL の作成と解析の両方の目的で使用されます。

URL の規則を指定するには、[urlManager|CWebApplication::urlManager] アプリケーションコンポーネントの [rules|CUrlManager::rules] プロパティを構成する必要があります:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'pattern1'=>'route1',
				'pattern2'=>'route2',
				'pattern3'=>'route3',
			),
		),
	),
);
~~~

URL の規則は、パターン-route の組み合わせから成る配列によって指定されます。
配列の一つ一つが一つの規則に対応します。規則のパターンは、
URL のパス情報の部分とのマッチに使用される文字列です。そして規則の route は、
正しいコントローラの [route](/doc/guide/basics.controller#route) を示すものでなければなりません。

上記の パターン-route 形式の他に、以下のように、カスタマイズしたオプションによって規則を指定することも可能です。

~~~
[php]
'pattern1'=>array('route1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

バージョン 1.1.7 以降、下記の形式(すなわち、パターンを配列の要素として指定する形式)も使うことが出来ます。
これによって、同じパターンを使う複数の規則を指定することが可能になります。

~~~
[php]
array('route1', 'pattern'=>'pattern1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

上記においては、規則の追加オプションのリストが配列として指定されています。
使用できるオプションは以下の通りです。

   - [pattern|CUrlRule::pattern]: URL のマッチングと作成に使用されるパターン。
このオプションは、バージョン 1.1.7 以降で使用可能です。

   - [urlSuffix|CUrlRule::urlSuffix]: この規則にだけ使用される URL サフィックス。デフォルトは null で、[CUrlManager::urlSuffix] を使用することを意味します。

   - [caseSensitive|CUrlRule::caseSensitive]: この規則が大文字と小文字を区別するか否か。デフォルトは null で、[CUrlManager::caseSensitive] の値を使用することを意味します。

   - [defaultParams|CUrlRule::defaultParams]: この規則が提供するデフォルトの GET パラメータ (name=>value)。入ってくるリクエストの解析にこの規則が用いられると、このプロパティで宣言された値が $_GET に差し込まれます。 

   - [matchValue|CUrlRule::matchValue]: URL の作成時に、GET パラメータの値が規則の対応するサブパターンに一致すべきか否か。デフォルトは null で、[CUrlManager::matchValue] の値を使用することを意味します。
このプロパティが false である場合は、規則の route とパラメータ名が与えられたものと一致すると、その規則が URL の作成に使用されます。
このプロパティが true に設定された場合は、与えられたパラメータの値も対応するパラメータのサブパターンと一致しなければなりません。
このプロパティを true に設定するとパフォーマンスが低下することに注意してください。

   - [verb|CUrlRule::verb]: 現在のリクエストの解析にこの規則を使用するために、一致しなければならない HTTP 動詞(verb) (例えば `GET`、`POST`、`DELETE`)。
規則が複数の動詞にマッチしうる場合は、動詞をカンマで区切ってください。
規則が指定された動詞にマッチしない場合は、リクエストの解析プロセスでスキップされます。
このオプションは主として RESTful URL をサポートするために提供されています。
このオプションはバージョン 1.1.7 以降で利用可能です。

   - [parsingOnly|CUrlRule::parsingOnly]: この規則がリクエストの解析だけに使用されるか否か。デフォルトは false で、規則が URL の解析と作成の両方に使われることを意味します。
このオプションはバージョン 1.1.7 以降で利用可能です。

名前付きのパラメータの使用
--------------------------

規則は、いくつかの GET パラメータと関連付ける事が出来ます。これらの GET パラメータは、規則のパターンの中に、次のような形式の特殊なトークンとして出現します。
その場合の書式は以下の様になります:

~~~
<ParamName:ParamPattern>
~~~

`ParamName` には GET パラメータの名前、オプションの `ParamPattern` には
GET パラメータの値とのマッチに使用すべき正規表現を指定します。
`ParamPattern` が省略された場合は、パラメータがスラッシュ(`/`)以外のすべての文字にマッチすべきことを意味します。
URL を作成する時には、このパラメータトークンが対応するパラメータの値で置き換えられます。
URL を解析するときには、解析結果が対応する GET パラメータに入れられます。

では、URL の規則がどのように働くのか、例を挙げて説明しましょう。
ここでは3つの規則があると仮定します:

~~~
[php]
array(
	'posts'=>'post/list',
	'post/<id:\d+>'=>'post/read',
	'post/<year:\d{4}>/<title>'=>'post/read',
)
~~~

   - `$this->createUrl('post/list')` をコールした場合、1番目のルールが
適用されて `/index.php/posts` が作成されます。

   - `$this->createUrl('post/read', array('id'=>100))` をコールした場合、
2番目のルールが適用されて `/index.php/post/100` が作成されます。

   - `$this->createUrl('post/read', array('year'=>2008, 'title'=>'a 
sample post'))` をコールした場合、3番目のルールが適用される事になり 
`/index.php/post/2008/a%20sample%20post` が作成されます。

   - `$this->createUrl('post/read')`をコールした場合、どのルールも適用
されないので `/index.php/post/read` が作成されます。


要点をまとめると、[createUrl|CController::createUrl] でURLを
作成する時、メソッドに渡される route パラメータと GET パラメータによって、どの URL 規則が適用されるかが決定されます。
すなわち、規則に関連付けられたパラメータがすべて [createUrl|CController::createUrl] に渡された GET パラメータの中にあって、さらに規則の route も route パラメータと一致している場合に、その規則が URL の生成に使用されます。

[createUrl|CController::createUrl] メソッドに、ルールが要求する以上の GET
パラメータが渡された場合は、追加のパラメータはクエリ文字列として表示
されます。例えば、`$this->createUrl('post/read', array('id'=>100, 'year'=>2008))` とした場合、URLは `/index.php/post/100?year=2008` になります。
これらの追加のパラメータもスラッシュ区切りのパス情報形式の URL として表示したい場合は、規則に `/*` を追加します。
つまりこの場合は、規則を `post/<id:\d+>/*` とすることで
`/index.php/post/100/year/2008` という URL を作成させるようにできます。

既に述べたように、URL の規則のもう一つの目的は、リクエストされた URL を解析する事です。これは当然ですが、URL の作成の逆の動作です。例えば、
ユーザが `/index.php/post/100` という URL をリクエストした時、上記の例の2番目のルールが適用される事になり、
`post/read` の route が呼ばれ、GET パラメータの中には `array('id'=>100)`というパラメータ (`$_GET`でアクセス可能) が入ります。


> Note|注意: URL 規則の使用は、アプリケーションパフォーマンスを低下させます。
これは、リクエストされた URL を解析する際、[CUrlManager] が適用できる規則を
見つけるまで、URL と規則をマッチさせようとするからです。
規則の数が多ければ多いほど、パフォーマンスへの影響は強くなります。
その為、通信量の大きいWebアプリケーションの作成時は、URL 規則の使用を最小限に留めるべきです。


Route のパラメータ化
---------------------

規則の route 部分で名前付きパラメータを参照することが出来ます。
これによって、マッチングの基準に基づいて、一つの規則を複数の route に適用することが可能になります。
さらに、このことは、アプリケーションが必要とする規則の数を減らし、ひいては、全体としてのパフォーマンスを向上させる助けにもなるでしょう。

次の規則の例を使って、名前付きパラメータで route をパラメータ化する方法を説明します。

~~~
[php]
array(
	'<_c:(post|comment)>/<id:\d+>/<_a:(create|update|delete)>' => '<_c>/<_a>',
	'<_c:(post|comment)>/<id:\d+>' => '<_c>/read',
	'<_c:(post|comment)>s' => '<_c>/list',
)
~~~

上記では、規則の route 部分で、`_c` と `_a` の 2 つの名前付きパラメータを使用します。
前者は、コントローラ ID `post` か `comment` のどちらかにマッチし、
後者は、アクション ID `create`、`update`、`delete` のいずれかにマッチします。
パラメータの名前は、URL に現れるかもしれない GET パラメータと衝突しない限り、どのような名前を付けても構いません。

上記の規則を使用すると、`/index.php/post/123/create` という URL は、
GET パラメータ `id=123` を伴った route `post/create` であると解析されます。
また、逆に、route `comment/list` と GET パラメータ `page=2` を与えて、`/index.php/comments?page=2` という URL を作成することが出来ます。


ホスト名のパラメータ化
------------------------

さらに、URL の解析・作成の規則にホスト名を含ませることが可能です。
まず、ホスト名の一部を GET パラメータとして抽出することが出来ます。
例えば、URL `http://admin.example.com/en/profile` を解析して、GET パラメータとして `user=admin` と `lang=en` を取得するようなことが可能です。
また一方、ホスト名を持つ規則を使って、パラメータ化されたホスト名を持つ URL を作成することも可能です。

パラメータ化されたホスト名を使うために必要なことは、ホスト情報を持つ URL 規則を宣言することだけです。例えば

~~~
[php]
array(
	'http://<user:\w+>.example.com/<lang:\w+>/profile' => 'user/profile',
)
~~~
上記の例は、ホスト名の最初のセグメントを `user` パラメータとして扱うべき事、そして、パス情報の最初のセグメントを `lang` パラメータとして扱うべき事を述べています。
そしてこの規則は `user/pforile` という route に対応しています。

ただし、パラメータ化されたホスト名を持つ規則を使って URL を作成すると、[CUrlManager::showScriptName] が有効にならないことに注意して下さい。

さらにまた、アプリケーションが Web root のサブフォルダにある場合でも、パラメータ化されたホスト名を持つ規則はサブフォルダを含んではならない、という事に注意して下さい。
例えば、アプリケーションが `http://www.example.com/sandbox/blog` の下にある場合、上で述べたのと同じ URL 規則を `sandbox/blog` というサブフォルダを付けずに使わなければなりません。

`index.php`の隠し方
-----------------

URL をもっと綺麗にするために、さらにもう一つの方法があります。すなわち、
エントリースクリプトの `index.php` を URL から隠すことです。そのためには、
[urlManager|CWebApplication::urlManager] アプリケーションコンポーネントを構成するのと同時に、Web サーバの設定も必要になります。

最初に Web サーバを構成して、エントリースクリプト無しの URL が、引き続きエントリースクリプトによって取り扱われるようにする必要があります。
[Apache HTTP server] (http://httpd.apache.org/)の場合、URL リライティングエンジンを有効化し、
幾つかのリライティングルールを指定することで実現出来ます。
下記の内容を持つ `/wwwroot/blog/.htaccess` ファイルを作ります。
同じ内容を Apache の設定ファイルで、`/wwwroot/blog` の `Directory` 要素の中に記述しても構いません。

~~~
RewriteEngine on

# ディレクトリまたはファイルが存在する場合は、それを直接に使う
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# それ以外は index.php に転送する
RewriteRule . index.php
~~~

そして、次に、[urlManager|CWebApplication::urlManager] コンポーネントの
[showScriptName|CUrlManager::showScriptName] プロパティを `false` に設定
します。

さあ、これで `$this->createUrl('post/read',array('id'=>100))` をコールすると
`/post/100` というURLが作成される様になりました。
ここで更に重要な点は、この URL が Web アプリケーションにも正しく認識されるものであるという事です。


URL の拡張子を擬装する
-----------------

URL に何らかの拡張子を付加することもできます。例えば、`/post/100` の代わりに
`/post/100.html` の形にする事ができます。これによって、より一層、静的なウェブページの URL であるように見せることができます。そうするためには、単純に、
[urlManager|CWebApplication::urlManager] コンポーネントの
[urlSuffix|CUrlManager::urlSuffix] プロパティを使用したい拡張子に設定する
だけです。

カスタム URL 規則クラスを使う
-----------------------------

> Note|注意: カスタム URL 規則クラスの使用はバージョン 1.1.8 以降でサポートされています。

デフォルトでは、[CUrlManager] で宣言される個々の URL 規則はそれぞれ一つの [CUrlRule] オブジェクトを表します。
このオブジェクトが指定された規則に基づいてリクエストを解析し、URL を生成する仕事を実行します。
[CUrlRule] は柔軟性が高いのでたいていの URL 形式を取り扱うことが出来ますが、
それでも、時によっては、特別な機能を追加してこれを機能拡張したいと思う場合もあります。

例えば、自動車ディーラーのウェブサイトにおいて、`/Manufacturer/Model` のような
URL 形式をサポートしたい、けれども `Manufacturer` と `Model` は両方ともデータベーステーブルにある何らかのデータと合致するものでなければならない、というような場合です。
[CUrlRule] クラスはここでは役に立ちません。
なぜなら、[CUrlRule] が主として依存している静的に宣言された正規表現からは、データベースの中身を知ることが出来ないからです。

私たちは [CBaseUrlRule] から拡張した URL 規則クラスを新しく書いて、
それを一つまたは複数の URL 規則の中で使用することが出来ます。
上記の自動車ディーラーのウェブサイトを例にすると、以下のような URL 規則を宣言することが出来ます。

~~~
[php]
array(
	// '/' を 'site/index' アクションにマップする標準的な規則
	'' => 'site/index',

	// '/login' を 'site/login' にマップする等の標準的な規則
	'<action:(login|logout|about)>' => 'site/<action>',

	// '/Manufacturer/Model' を処理するカスタム規則
	array(
	    'class' => 'application.components.CarUrlRule',
	    'connectionID' => 'db',
	),

	// 'post/update' 等を扱う標準的な規則
	'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
),
~~~

上記では、`/Manufacturer/Model` の URL 形式を扱うために、`CarUrlRule` という
カスタム URL 規則クラスを使っています。
このクラスは下記のように書くことが出来ます。

~~~
[php]
class CarUrlRule extends CBaseUrlRule
{
	public $connectionID = 'db';

	public function createUrl($manager,$route,$params,$ampersand)
	{
		if ($route==='car/index')
		{
			if (isset($params['manufacturer'], $params['model']))
				return $params['manufacturer'] . '/' . $params['model'];
			else if (isset($params['manufacturer']))
				return $params['manufacturer'];
		}
		return false;  // この規則は適用されない
	}

	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
	{
		if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches))
		{
			// $matches[1] と $matches[3] を見て、データベースの中にある
			// 製造者とモデルに一致するかどうかを調べる。
			// 一致したら、$_GET['manufacturer'] または $_GET['model']、
			// あるいはその両方をセットして、'car/index' を返す
		}
		return false;  // この規則は適用されない
	}
}
~~~

カスタム URL 規則クラスは、[CBaseUrlRule] で宣言されている二つの抽象メソッドを実装しなければなりません。

* [createUrl()|CBaseUrlRule::createUrl()]
* [parseUrl()|CBaseUrlRule::parseUrl()]

上記の典型的な使用方法の他にも、カスタム URL 規則クラスを別のさまざまな目的のために実装することが可能です。
例えば、URL の解析と生成のリクエストをログに記録するための規則クラスを書くことが出来ます。これは開発段階において有益かもしれません。
また、他の全ての URL 規則が現在のリクエストを解決できなかった場合に、特別な 404 エラーページを表示するための規則クラスを書くことも可能です。
この場合、この特別なクラスの規則を最後の規則として宣言しなければならないことに注意して下さい。

<div class="revision">$Id: topics.url.txt 3329 2011-06-28 08:31:35Z mdomba $</div>
