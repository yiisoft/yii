アプリケーション
===========

アプリケーションはリクエストが処理される実行コンテキストをカプセル化したオブジェクトです。
その主なタスクは、リクエストに関するいくつかの基本的な情報を収集して、その先の処理を行うために適切なコントローラへリクエストを送出することです。
さらにアプリケーションレベルの初期構成を保つための中心的な場所としても振舞います。
この理由からアプリケーションオブジェクトは`フロントコントローラ`とも呼ばれます。

アプリケーションは[エントリースクリプト](/doc/guide/basics.entry)により、シングルトンとして生成されます。
アプリケーションシングルトンはどの場所からでも[Yii::app()|YiiBase::app]としてアクセスすることができます。

アプリケーション初期構成
-------------------------

デフォルトでは、アプリケーションオブジェクトは[CWebApplication]のインスタンスです。
これをカスタマイズするために、通常は初期構成ファイル(または配列)を提供して、インスタンスの生成時にプロパティ値を初期化します。
アプリケーションをカスタマイズする別の方法は、[CWebApplication]を拡張することです。

初期構成はキーと値のペアの要素を持つ配列です。キーはアプリケーションインスタンスのプロパティ名を表します。
値は対応するプロパティの初期値です。
例えば、以下の初期構成ファイルは[name|CApplication::name]及び[defaultController|CWebApplication::defaultController]プロパティを構成します。

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

一般には構成は別のPHPスクリプトに格納されます(例えば`protected/config/main.php`)。
このスクリプトの中で、以下のように構成の配列を返します。

~~~
[php]
return array(...);
~~~

構成を適用するには、アプリケーションのコンストラクタに構成ファイル名をパラメータとして渡すか、あるいは以下のように
[エントリースクリプト](/doc/guide/basics.entry)中にもよく見られるように、[Yii::createWebApplication()]に渡します。

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|ヒント: もしアプリケーション構成が非常に複雑な場合は、複数のファイルに分割することができます。
それぞれのファイルは構成配列の一部をリターンします。そしてメインの構成ファイルではPHPの`include()`により
他の構成ファイルを組み込み、完全な構成配列にマージします。


アプリケーションベースディレクトリ
--------------------------

アプリケーションベースディレクトリは、セキュリティ上注意を要する全てのPHPスクリプトとデータの格納場所のルートディレクトリです。
デフォルトでは、エントリスクリプトを含むディレクトリの下にある`protected`という名前のサブディレクトリです。
この場所は、[アプリケーション構成](/doc/guide/basic.application#application-configuration)の[basePath|CWebApplication::basePath]プロパティ値を変更することでカスタマイズが可能です。

アプリケーションベースディレクトリより下の内容はウェブユーザによってアクセスされないように保護される必要があります。
[Apache HTTP server](http://httpd.apache.org/)を用いる場合、`.htaccess`ファイルをベースディレクトリの下に置くことで容易に実現可能です。`.htaccess`ファイルの内容は以下のようになります。

~~~
deny from all
~~~

アプリケーションコンポーネント
---------------------

アプリケーションオブジェクトの機能はフレキシブルなコンポーネントアーキテクチャによって容易にカスタマイズでき、また充実させることができます。アプリケーションは一連のアプリケーションコンポーネントを管理し、それぞれが個別の機能を実装します。
例えば、アプリケーションはユーザリクエストに関するいくつかの初期処理を実行するために、[CUrlManager]コンポーネントと[CHttpRequest]コンポーネントの助けを借ります。

アプリケーションインスタンスの[components|CApplication::components]プロパティを構成することによって、
どのアプリケーションコンポーネントを使用する場合でも、クラスとプロパティ値をカスタマイズすることが出来ます。
例えば、以下のように、複数のmemcacheサーバを使用するように[CMemCache]コンポーネントを構成することが可能です。

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

上においては`components`配列に`cache`要素を追加しました。そして`cache`要素は
コンポーネントのクラスが`CMemCache`であり、`servers`プロパティ値がこのようであると記述します。

アプリケーションコンポーネントにアクセスするには`Yii::app()->ComponentID`を用いてください。
ここで、`ComponentID`はコンポーネントのID(例えば`Yii::app()->cache`)を参照します。

アプリケーションコンポーネントは構成ファイル中で`enabled`プロパティを偽にセットすることで無効にすることができます。
無効にされたコンポーネントにアクセスするとnullが返されます。

> Tip|ヒント: デフォルトでは、アプリケーションコンポーネントは必要に応じて生成されます。
これは、アプリケーションコンポーネントはユーザリクエストの間にアクセスされなければ全く生成されないことを意味します。
結果として、アプリケーションが多量のコンポーネントを持つように構成されても全体としての性能は落ちません。
いくつかのアプリケーションコンポーネント(例えば[CLogRouter])は、アクセスされるか否かに関わりなく生成される必要があるでしょう。
そうするためには、それらのIDを[preload|CApplication::preload]アプリケーションプロパティに列挙して下さい。

コアアプリケーションコンポーネント
---------------------------

Yiiは、ウェブアプリケーションに共通な機能を提供するために、一連のコアアプリケーションコンポーネントをあらかじめ定義しています。
例えば、[request|CWebApplication::request]コンポーネントはユーザリクエストに関する情報を収集し、要求されたURLやクッキーの情報を提供するために使用されます。このようなコアコンポーネントのプロパティ値を構成することによって、我々はYiiのデフォルト動作のほとんど全てを変更することが可能です。

以下は[CWebApplication]によってあらかじめ宣言されたコアコンポーネントのリストです。

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
プライベートアセットファイルの発行を管理します。

   - [authManager|CWebApplication::authManager]: [CAuthManager] - 役割ベースアクセス制御(RBAC)を管理します。

   - [cache|CApplication::cache]: [CCache] - データキャッシュ機能を提供します。注意:実際のクラス(例:[CMemCache], [CDbCache])
を指定してください。さもないとこのコンポーネントをアクセスした場合にnullが返ります。

   - [clientScript|CWebApplication::clientScript]: [CClientScript] - クライアントスクリプト(javascriptやCSS)を管理します。

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
Yiiフレームワークで使用される翻訳されたコアメッセージを提供します。

   - [db|CApplication::db]: [CDbConnection] - データベース接続を提供します。注意: このコンポーネントを使うためには[connectionString|CDbConnection::connectionString]プロパティを構成しなければなりません。

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - キャッチされていないPHPエラーや例外を扱います。

   - [format|CApplication::format]: [CFormatter] - 表示目的のため、データ値をフォーマットします。

   - [messages|CApplication::messages]: [CPhpMessageSource] - Yiiアプリケーションによって使用される翻訳されたメッセージを提供します。

   - [request|CWebApplication::request]: [CHttpRequest] - ユーザリクエストに関連した情報を提供します。

   - [securityManager|CApplication::securityManager]: [CSecurityManager] - セキュリティ関連のサービス（例えばハッシュ化、暗号化）を提供します。

   - [session|CWebApplication::session]: [CHttpSession] - セッションに関連した機能を提供します。

   - [statePersister|CApplication::statePersister]: [CStatePersister] - グローバルな状態を持続させる機構を提供します。

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - URLの分析と生成の機能を提供します。

   - [user|CWebApplication::user]: [CWebUser] - 現在のユーザのアイデンティティに関連した情報を保持します。

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - テーマを管理します。


アプリケーションライフサイクル
----------------------

ユーザリクエストを取り扱うとき、アプリケーションは次のライフサイクルを経ます:

   0. [CApplication::preinit()]によりアプリケーションをプレ初期化します;

   1. クラス自動ローダとエラー処理を準備します;

   2. コアアプリケーションコンポーネントを登録します;

   3. アプリケーション構成をロードします;

   4. [CApplication::init()]によりアプリケーションを初期化します;
           - アプリケーションビヘイビアを登録します;
	   - 静的なアプリケーションコンポーネントをロードします;

   5. イベント[onBeginRequest|CApplication::onBeginRequest]を発行します;

   6. ユーザリクエストを処理します:
	   - リクエストに関する情報を収集します;
	   - コントローラを生成します;
	   - コントローラを実行します;

   7. イベント[onEndRequest|CApplication::onEndRequest]を発行します;

<div class="revision">$Id: basics.application.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
