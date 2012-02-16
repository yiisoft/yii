認証と権限付与
================================

認証と権限付与は、特定のユーザにだけ公開したいウェブページに必要です。
*認証*とは、ある人物が主張するとおりに、確かにその当人であるかどうかを確かめることです。
多くの場合ユーザ名とパスワードを用いますが、スマートカードや指紋など他の身分照明方法でもかまいません。
*権限付与*とは、いったんある人物が特定された(すなわち、認証された)後、特定のリソースの操作を許可されているかどうかを判断することです。
たいていこれはその人物がリソースにアクセスする特定のロールを持っているかどうかで決定されます。

Yiiには組み込みの認証・権限付与(ここでは両方をあわせてauthと呼びます)機構が備わっており、
簡単に利用できます。特別なニーズに対応することも可能です。

auth機構の中心部分は、[IWebUser]インターフェイスを実装するあらかじめ宣言済みの *user アプリケーションコンポーネント*です。
この user コンポーネントが現在のユーザの持続的な個人情報を保持します。
`Yii::app()->user`とするとどこからでもこの情報にアクセスできます。

この user コンポーネントを用いることで、ユーザがログインしているかどうかを[CWebUser::isGuest]で確認できます。
ユーザの[login|CWebUser::login]や[logout|CWebUser::logout]、
ある操作が許可されているかどうかのチェック[CWebUser::checkAccess]、
ユーザID[unique identifier|CWebUser::name]やその他の情報へのアクセスが可能です。

Identityクラスを定義する
-----------------------

上で述べたように、認証はユーザの同一性の検証に関するものです。
典型的なウェブアプリケーションの認証の実装は、通常、一対のユーザ名とパスワードを使って、ユーザの同一性を検証するものです。
しかし、その他の手段を使う場合もあり、異なった実装方法が要求される場合もあります。
さまざまな認証手段に対応できるように、Yii の auth フレームワークは identity クラスを導入しています。

実際の認証ロジックを含む identity クラスを定義しますが、
identity クラスは [IUserIdentity] インターフェイスを実装しなければなりません。
異なった認証方法(例：OpenID、LDAP、Twitter OAuth、Facebook Connect など)に対して、それぞれ異なったクラスを実装することが出来ます。
自分自身の実装を書くときには、[CUserIdentity] を継承すると始めやすいでしょう。
このクラスはユーザ名とパスワードを使う認証手段のための基本クラスです。

identity クラス定義の主な作業は [IUserIdentity::authenticate] メソッドの実装です。
このメソッドが認証手段の主要な内容をカプセル化するために使われます。
また identity クラスでは、ユーザセッションの間保持される必要がある追加の個人情報も宣言することが出来ます。

#### 例

以下において、認証にデータベースを用いる identity クラスを例示します。
非常に典型的な方法で、ほとんどのウェブアプリケーションはこの方法を取ります。
ユーザがログインフォームにユーザ名とパスワードを入力すると、その認証情報を検証するために、[ActiveRecord](/doc/guide/database.ar) を使って、データベースにある user テーブルに問い合わせをする、というものです。
実際には、この単純な例の中で、いくつかの事柄が示されています。

1. データベースを使って認証情報を検証するように `authenticate()` を実装している。
2. デフォルトの実装ではユーザ名を ID として返すので、`CUserIdentity::getId()` メソッドをオーバーライドして、`_id` プロパティを返している。
3. 後に続くリクエストにおいて簡単に引き出せるように、`setState()` ([CBaseUserIdentity::setState]) メソッドを使ってその他の情報を保存している。

~~~
[php]
class UserIdentity extends CUserIdentity
{
	private $_id;
	public function authenticate()
	{
		$record=User::model()->findByAttributes(array('username'=>$this->username));
		if($record===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($record->password!==md5($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$record->id;
			$this->setState('title', $record->title);
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
    }

	public function getId()
	{
		return $this->_id;
	}
}
~~~

次のセクションでログインとログアウトを扱うときに、この identity クラスが user  の login メソッドに引き渡されることを見ます。[CBaseUserIdentity::setState] を呼んで保存されるすべての情報が [CWebUser] に渡され、[CWebUser] がそれをセッションなどの持続的ストレージに保存します。
するとこの情報は [CWebUser] のプロパティのようにアクセスすることが出来るようになります。
上記の例では、ユーザの `title` 情報を `$this->setState('title', $record->title);` によって保存しましたが、ログインのプロセスを完了した後は、現在のユーザの `title` 情報を `Yii::app()->user->title` を使うだけで取得することが出来ます。

> Info|情報：デフォルトでは [CWebUser] はユーザの個人情報を保存するのに、
持続的ストレージとしてセッションを使います。
クッキーベースのログインが有効([CWebUser::allowAutoLogin]がtrue)になっていると、
ユーザの個人情報がクッキーにも保存される可能性があります。
パスワードのような取り扱いに注意を要する個人情報を保存しないよう気をつけてください。

ログインとログアウト
----------------

ユーザの identity を作成する例が分りましたので、今度はこれを使って、必要になる login と logout のアクションを簡単に実装することが出来ます。以下のコードが、それがどのように達成されるかを示します。

~~~
[php]
// 入力されたユーザ名とパスワードでユーザをログインする
$identity=new UserIdentity($username,$password);
if($identity->authenticate())
	Yii::app()->user->login($identity);
else
	echo $identity->errorMessage;
......
// 現在のユーザをログアウトする
Yii::app()->user->logout();
~~~

ここでは、まず、新しい UserIdentity オブジェクトを作成して、認証情報(すなわち、ユーザによって送信された `$username` と `$password`)をコンストラクタに渡しています。
そして、次に、ただ単に `authenticate()` メソッドを呼んでいます。
成功した場合は、identity 情報を [CWebUser::login] メソッドに渡します。`login` メソッドは identity 情報を持続的ストレージ(デフォルトでは PHP セッション)に保存して、後のリクエストで参照できるようにします。
認証が失敗した場合は、失敗した理由の詳細について、`errorMessage` プロパティを取り調べることが出来ます。

ユーザが認証されたか否かは、`Yii::app()->user->isGuest` を使えば、アプリケーションから簡単にチェックできます。
identity 情報の保存に、セッション(これがデフォルトです)および/またはクッキー(後述します)のような持続的ストレージを使う場合は、
ユーザは後続するリクエストにおいてログインされた状態を継続することが出来ます。
この場合は、すべてのリクエストに対して UserIdentity クラスやログインのプロセス全体を使用する必要はありません。そうしなくても、CWebUser がこの持続的ストレージから自動的に identity 情報を読み込み、その情報に従って、`Yii::app()->user->isGuest` が true または false のどちらを返すべきかを決定します。

クッキーベースのログイン
------------------

デフォルトでは、[セッションの構成](http://www.php.net/manual/en/session.configuration.php)によって、
アクティブでない時間が一定期間以上経過すると、ユーザはログアウトされます。
この仕様を変更するために、user コンポーネントの [allowAutoLogin|CWebUser::allowAutoLogin] を true にセットし、
持続期間のパラメータを [CWebUser::login] に渡すことができます。
こうすると、ブラウザを閉じてもユーザは設定した持続期間の間はログインされたままになります。
この機能はユーザのブラウザがクッキーを受け入れる設定になっている必要があることに注意してください。


~~~
[php]
// 7日間ログイン状態を保持する
// user コンポーネントの allowAutoLogin が true であることを確認
Yii::app()->user->login($identity,3600*24*7);
~~~

既に言及したように、クッキーベースのログインが有効な場合、[CBaseUserIdentity::setState]
によって保存された情報は、クッキーにも保存されます。
ユーザが次回ログインされたときには、その情報がクッキーから読み出されて、
`Yii::app()->user` によってアクセス可能になります。

Yii はこの情報のクッキーがクライアント側で改竄されるのを防止する手段を備えていますが、
セキュリティ上重要な情報は [CBaseUserIdentity::setState] によって保存しないことを強く推奨します。
セキュリティ上重要な情報は、サーバー側の持続的ストレージ(例えばデータベース)から読み出して、サーバー側で復元するようにすべきです。

それに加えて、すべての本格的なウェブアプリケーションでは、次のような方策を講じて、クッキーベースのログインのセキュリティを強化することを推奨します。

* ユーザがログインフォームに入力してログインに成功したときに、ランダムなキーを生成して、クッキーとサーバー側の持続的ストレージ(例えばデータベース)の両方に保存する。

* 後のリクエストにおいて、ユーザ認証がクッキー情報によって行われる場合は、ユーザをログインする前に、このランダムなキーの二つのコピーを比較して、一致することを確認する。

* ユーザが再びログインフォームからログインした場合は、キーは再生成される必要がある。

上記の方策を使うことで、ユーザが期限切れになった情報を含む可能性のある古いクッキーを再使用する可能性を除去することが出来ます。

この方策を実装するためには、以下の二つのメソッドをオーバーライドする必要があります。

* [CUserIdentity::authenticate()]: ここが本当の認証が実行される場所です。
ユーザが認証された場合は、新しいランダムなキーを生成して、[CBaseUserIdentity::setState] によって identity 情報として保存すると同時に、データベースにも保存します。

* [CWebUser::beforeLogin()]: ユーザがログインされる前に呼び出されます。
ここでクッキーから取得したキーがデータベースから取得したキーと同じかどうかチェックします。


アクセスコントロールフィルタ
---------------------

アクセスコントロールフィルタは予備的な権限付与スキームです。
現在のユーザが要求されたコントローラのアクションを実行できるかどうかをチェックします。
権限付与はユーザ名、クライアントのIPアドレス、リクエストタイプなどに基づいて行われます。
これは["accessControl"|CController::filterAccessControl]という名前のフィルタとして提供されています。

> ヒント: アクセスコントロールフィルタは単純なケースでは十分に役に立ちます。
より複雑なアクセスコントロールには、次のサブセクションで紹介するロールベースアクセス(RBAC)が使えるでしょう。

コントローラのアクションへのアクセスを制御するために、[CController::filters]をオーバーライドして、
アクセスコントロールフィルタをインストールします。
(フィルタインストールの詳細については[Filter](/doc/guide/basics.controller#filter)を参照してください)

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
}
~~~

上記の例では、[access control|CController::filterAccessControl]フィルタが`PostController`のすべてのアクションに適用されるよう指定しています。
フィルタによって実行される実際の権限付与ルールは[CController::accessRules]をオーバーライドすることで指定します。
以下に例を示します。

~~~
[php]
class PostController extends CController
{
	......
	public function accessRules()
	{
		return array(
			array('deny',
				'actions'=>array('create', 'edit'),
				'users'=>array('?'),
			),
			array('allow',
				'actions'=>array('delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'actions'=>array('delete'),
				'users'=>array('*'),
			),
		);
	}
}
~~~

上記のコードでは三つのルールが指定されています。
ルールは配列で表されます。
ルールの最初の要素は`'allow'` か `'deny'`で、残りはルールのパラメタを指定するキーと値のペアです。
この例では、
`create` と`edit`アクションはアノニマスユーザは実行できず、
`delete`アクションは`admin`ロールを持ったユーザが実行でき、
`delete`アクションは誰にも実行できない。
と設定されています。

アクセスルールは上から順番にひとつずつ評価されるので、現在のパターン(例：ユーザ名、ロール、IPアドレス)
に一致した最初のルールが権限付与の結果を決定します。
マッチしたルールが`allow`なら、アクションは実行されます。
`deny`ならアクションは実行されません。
すべてのルールに一致しない場合、アクションは実行されます。

> ヒント: 予期せぬアクションの実行を防ぐため、ルールセットの最後に常に`deny`をおくことが有効です。
> 以下に例を示します。
> ~~~
> [php]
> return array(
>     // ... ルール...
>     // 最後のルールで必ず実行を阻止する
>     array('deny',
>         'action'=>array('delete'),
>     ),
> );
> ~~~
> こうする理由は、すべてのルールに一致しない場合は、アクションが実行されるからです。

アクセスルールは以下のコンテクストパラメータと一致する可能性があります。
An access rule can match the following context parameters:

   - [actions|CAccessRule::actions]: どのアクションにルールが適用されるか決める。
アクションIDの配列。比較は大文字小文字を区別しない。

   - [controllers|CAccessRule::controllers]: どのコントローラにルールが適用されるか決める。
コントローラIDの配列。比較は大文字小文字を区別しない。

   - [users|CAccessRule::users]: どのユーザにルールが適用されるか決める。
現在のユーザの[name|CWebUser::name]が判断基準に使われる。
比較は大文字小文字を区別しない。
三種類の特別な意味を持つ文字を使うことができる。

	   - `*`: あらゆるユーザ。アノニマスユーザも認証済みのユーザも含む。
	   - `?`: アノニマスユーザ
	   - `@`: 認証済みのユーザ

   - [roles|CAccessRule::roles]: どのロールにルールが適用されるか決める。
これは次のサブセクションで説明する[role-based access control](/doc/guide/topics.auth#role-based-access-control)機能を利用する。
具体的には、ロールに対して[CWebUser::checkAccess]がtrueを返したときにルールが適用される。
注意：ロールは主に`allow`にたいして使うべきです。その定義から言ってロールとは何かをする許可のことだからです。さらに、ここでは `ロール(roles)` という用語を使っていますが、その値は実際には、ロール(role)、タスク(task)、オペレーション(operation)のうちの、どの権限アイテムでも構いません。

   - [ips|CAccessRule::ips]: どのクライアントIPアドレスにルールが適用されるか決める

   - [verbs|CAccessRule::verbs]: どのリクエストタイプ(例：`GET`, `POST`)にルールが適用されるか決める。
比較は大文字小文字を区別しない。

   - [expression|CAccessRule::expression]: PHPの評価式でルールが適用されるか決める。
評価式の中で、`$user`という変数を使って`Yii::app()->user`を参照できる。


権限付与結果の取り扱い
-----------------------------

権限付与が失敗すると、すなわちユーザがアクションの実行を許可されないと、以下の二つのシナリオのうちどちらかが発生します。

   - ユーザがログインしておらず、ユーザコンポーネントの [loginUrl|CWebUser::loginUrl]プロパティがログインページのURLに設定されていると、
ブラウザはそのページにリダイレクトされる。
[loginUrl|CWebUser::loginUrl] はデフォルトでは `site/login` ページを指していることに注意。

   - それ以外の場合はHTTP例外がエラーコード403で表示される。

[loginUrl|CWebUser::loginUrl]を設定する際に、相対URLか絶対URLのどちらかを指定できます。
あるいはまた、[CWebApplication::createUrl]によってURLを生成する文字列も利用可能です。
配列の最初の要素はログインコントローラアクションへの[route](/doc/guide/basics.controller#route)であり、
残りはGETパラメータとして渡されるキーと値のペアです。
例えば、

~~~
[php]
array(
	......
	'components'=>array(
		'user'=>array(
			// 実際のデフォルト値
			'loginUrl'=>array('site/login'),
		),
	),
)
~~~

ブラウザがログインページリダイレクトされ、ログインが成功したら、
権限付与を必要とした元のページに戻りたいでしょう。
どうしたら元のページのURLがわかるでしょうか？
ユーザコンポーネントの[returnUrl|CWebUser::returnUrl]プロパティからこの情報を得ることができます。
したがって、リダイレクトを以下のようにして実行できます。

~~~
[php]
Yii::app()->request->redirect(Yii::app()->user->returnUrl);
~~~

ロールベースアクセスコントロール
-------------------------

ロールベースアクセスコントロール(RBAC)はシンプルで強力な集中的アクセスコントロールを提供します。
RBACと他の伝統的なアクセスコントロールスキーマとの比較については[Wiki
article](http://en.wikipedia.org/wiki/Role-based_access_control)を参照してください。

Yiiは[authManager|CWebApplication::authManager]アプリケーションコンポーネントで、階層的RBACスキームを実装しています。
以下でこのスキームで使われる主な概念を紹介します。
次に権限付与データをどう定義するかを説明します。
最後に権限付与データをアクセスチェックに利用するやり方を説明します。

### 概要

YiiのRBACの基本概念は、*権限付与アイテム*です。
権限付与アイテムとは何かをする許可のことです。(例：新しいブログ記事を作る、ユーザを管理する)
粒度と対象者によって、権限付与アイテムは*オペレーション*、*タスク*、*ロール*に分類されます。
ロールは複数のタスクからなり、タスクは複数のオペレーションからなります。
そして、オペレーションが一番小さな許可単位です。
例えば、`administrator`ロールが`post management`タスクと`user management`タスクを含むようなシステムを作ることができます。
`user management`タスクは`create user`、`update user`、`delete user`などのオペレーションから構成されるでしょう。
更なる柔軟性のために、Yiidではロールに他のロールを含めたり、タスクに他のタスクを含めたりできます。
さらにオペレーションも他のオペレーションを含むことができます。

権限付与アイテムは名前によって一意に識別されます。

権限付与アイテムは*ビジネスルール*に関連付けられることがあります。
ビジネスルールとは、小さなPHPコードで、アクセスチェックの際に実行されます。
ビジネスルールの実行結果がtrueを返したときだけ、
ユーザは権限付与アイテムが表す実行許可を持っていると考えられます。
例えば、`updatePost`というオペレーションを作るときに、記事作成者本人だけに更新許可を与えるために、
ユーザのIDが記事作成者のIDと同じであるかどうか確認するビジネスルールを付け加えたいことがあるでしょう。


権限付与アイテムを使うことで、*権限付与階層*を構築することができます。
アイテム`A`がアイテム`B`を含むとき、`A`は`B`の親になります。（つまり、`A`は`B`の権限をすべて含みます)
アイテムは複数の子を持つことができ、また複数の親を持つこともできます。
したがって、権限付与階層はツリー構造ではなく半順序グラフです。
階層構造の中で、ロールアイテムは最上位に位置します。
オペレーションアイテムは最下層です。
タスクアイテムはそれらの中間です。

権限付与階層を作った後、階層の中にあるロールをユーザに割り当てることができます。
ユーザはいったんロールを割り当てられると、ロールによって表される権限を持つことになります。
例えば、`administrator`ロールをあるユーザに割り当てると、
そのユーザはアドミニストレータロールに含まれる`post management`と`user management`権限を持つことになります。
そして`user management`には`create user`のようなオペレーションが含まれます。

ここからが面白いところです。コントローラのアクションでユーザがある記事を削除できるかどうかチェックしたいとしましょう。
RBAC階層と割り当てを使うとこれは以下のように簡単になります。


~~~
[php]
if(Yii::app()->user->checkAccess('deletePost'))
{
	// 記事の削除
}
~~~

権限付与マネージャの設定
-------------------------

権限付与階層を定義してアクセスチェックを始める前に、[authManager|CWebApplication::authManager]
アプリケーションコンポーネントを設定する必要があります。
Yiiは二つのタイプの権限付与マネージャを提供します。
[CPhpAuthManager] と [CDbAuthManager]です。
前者は権限付与データを格納するのにPHPファイルを使い、後者はデータベースを使います。
[authManager|CWebApplication::authManager]
アプリケーションコンポーネントを設定するさいに、どちらのクラスを使い、初期値をどうするのか指定しなければなりません。
例えば、

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'sqlite:path/to/file.db',
		),
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'connectionID'=>'db',
		),
	),
);
~~~

この設定で、`Yii::app()->authManager`として[authManager|CWebApplication::authManager]
アプリケーションコンポーネント
にアクセスできます。

権限付与階層を定義する
-------------------------

権限付与階層の定義には三つのステップがあります。
権限付与アイテムを定義し、アイテム同士の関係を設定し、最後にユーザにロールを割り当てます。
[authManager|CWebApplication::authManager]
アプリケーションコンポーネントはこれらのタスクを実行するための完全なAPIセットを提供します。

権限付与アイテムの定義には、アイテムの種類によって以下のメソッドにいずれかを使います。

   - [CAuthManager::createRole]
   - [CAuthManager::createTask]
   - [CAuthManager::createOperation]

権限付与アイテムのセットができたら、以下のメソッドでアイテム間の関係を設定します。

   - [CAuthManager::addItemChild]
   - [CAuthManager::removeItemChild]
   - [CAuthItem::addChild]
   - [CAuthItem::removeChild]

そして最後に、以下のメソッドでロールを個々のユーザに割り当てます。

   - [CAuthManager::assign]
   - [CAuthManager::revoke]

以下にこれらのAPIを使った例を示します。

~~~
[php]
$auth=Yii::app()->authManager;

$auth->createOperation('createPost','create a post');
$auth->createOperation('readPost','read a post');
$auth->createOperation('updatePost','update a post');
$auth->createOperation('deletePost','delete a post');

$bizRule='return Yii::app()->user->id==$params["post"]->authID;';
$task=$auth->createTask('updateOwnPost','update a post by author himself',$bizRule);
$task->addChild('updatePost');

$role=$auth->createRole('reader');
$role->addChild('readPost');

$role=$auth->createRole('author');
$role->addChild('reader');
$role->addChild('createPost');
$role->addChild('updateOwnPost');

$role=$auth->createRole('editor');
$role->addChild('reader');
$role->addChild('updatePost');

$role=$auth->createRole('admin');
$role->addChild('editor');
$role->addChild('author');
$role->addChild('deletePost');

$auth->assign('reader','readerA');
$auth->assign('author','authorB');
$auth->assign('editor','editorC');
$auth->assign('admin','adminD');
~~~

一旦この階層を構築した後は、[authManager|CWebApplication::authManager] コンポーネント(例えば [CPhpAuthManager] や [CDbAuthManager])が自動的に権限付与アイテムを読み出します。従って、上記のコードは一回だけ実行すればよく、リクエストごとに実行する必要はありません。

> Info|情報: この例は長くて退屈に見えますが、それは説明が目的だからです。
普通、開発者は管理者用のユーザインターフェイスを作って、エンドユーザが権限付与階層をもっと直感的に構築できるようにする必要があるでしょう。


ビジネスルールを使う
--------------------

権限階層を定義する際に、ロール・タスク・オペレーションにいわゆる*ビジネスルール*を関連付けることができます。
さらにまた、ユーザにロールを割り当てる際にも、ビジネスルールを関連付けることができます。
ビジネスルールとは、アクセスチェックが行われる際に実行される PHP コードの断片です。
ビジネスルールの戻り値が、ロールや割り当てが現在のユーザに適応されるかどうかの判断に使われます。
上記の例では`updateOwnPost`タスクにビジネスルールを関連付けていますが、その
ビジネスルールでは単に現在のユーザIDと記事作成者のIDが同じかどうかをチェックしています。
`$params`配列の記事情報は、開発者自身がアクセスチェックを実行する際に設定します。


### アクセスチェック

アクセスチェックを実行するため、まず権限付与アイテムの名前を知る必要があります。
たとえば、ユーザが記事の新規作成ができるかどうかをチェックするには、
`createPost`オペレーションで表される権限を持っているかどうかを調べます。
次に[CWebUser::checkAccess]を呼ぶことでアクセスチェックを実行します。

~~~
[php]
if(Yii::app()->user->checkAccess('createPost'))
{
	// 記事の作成
}
~~~

権限付与ルールが追加のパラメータを必要とするビジネスルールに関連付けられている場合には、
パラメータを渡すことができます。
例えば、ユーザが記事の更新が可能かどうかを調べるために、以下のように `$params` に記事データを入れて渡します。

~~~
[php]
$params=array('post'=>$post);
if(Yii::app()->user->checkAccess('updateOwnPost',$params))
{
	// 記事の更新
}
~~~


### デフォルトロールを使う

多くのウェブアプリケーションでは、すべてもしくはほとんどのユーザに割り当てるいくつかのとても特殊なロールが必要です。
例えば、ある特権を認証済みのすべてのユーザに割り当てたいとします。
これらのロール割り当てを明示的に指定し、保存すると、大変なメンテナンストラブルをもたらします。
この問題を解決するために、*デフォルトロールを*活用することができます。

デフォルトロールは、認証済みのユーザとゲストユーザを含むすべてのユーザに、黙示的に割り当てられるロールです。
明示的にユーザに割り当てる必要はありません。
 [CWebUser::checkAccess]が呼び出されると、デフォルトロールはまるでユーザに割り当てられているかのようにまずチェックされます。

デフォルトロールは[CAuthManager::defaultRoles] プロパティで宣言されなければなりません。
例えば、以下の設定では`authenticated`と`guest`という二つのロールをデフォルトロールとして宣言しています。

~~~
[php]
return array(
	'components'=>array(
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'defaultRoles'=>array('authenticated', 'guest'),
		),
	),
);
~~~

デフォルトロールはすべてのユーザに割り当てられるため、
たいていビジネスルールでロールが本当にユーザに適用されるかどうか決定する必要があります。
例えば、以下のコードでは`authenticated`と`guest`という二つのロールを定義し、
それぞれを認証済みユーザとゲストユーザに割り当てています。

~~~
[php]
$bizRule='return !Yii::app()->user->isGuest;';
$auth->createRole('authenticated', 'authenticated user', $bizRule);

$bizRule='return Yii::app()->user->isGuest;';
$auth->createRole('guest', 'guest user', $bizRule);
~~~

<div class="revision">$Id: topics.auth.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
