スキャフォールディング
===========

作成、読み出し、更新、そして削除 (CRUD) はアプリケーションにおけるデータオブジェクトに対する4つの基本操作です。
ウェブアプリケーション開発において、CRUDオペレーションを実装するタスクは共通であるため、Yiiは*Gii*という名前でコード生成ツールを提供し、それはこのプロセス(*スキャフォールディング*としても知られる)を自動化できます。

> Note|注: Giiは1.1.2版から利用可能です。それまではこの目的には[yiicシェルツール](http://www.yiiframework.com/doc/guide/quickstart.first-app-yiic)を使用する必要があります。

以降では、ブログアプリケーションの記事やコメントのCRUDオペレーションをこのツールにより実装する方法を紹介します。


Giiのインストール
--------------

最初にGiiをインストールする必要があります。ファイル`/wwwroot/blog/protected/config/main.php`を開き、以下のコードを追加してください。

~~~
[php]
return array(
        ......
        'import'=>array(
                'application.models.*',
                'application.components.*',
        ),

        'modules'=>array(
                'gii'=>array(
                        'class'=>'system.gii.GiiModule',
                        'password'=>'pick up a password here',
                ),
        ),
);
~~~

上記のコードは`gii`という名のモジュールをインストールし、それは以下のURLをブラウザに入力することでアクセスすることができます。

~~~
http://www.example.com/blog/index.php?r=gii
~~~

パスワード入力が指示されます。`/wwwroot/blog/protected/config/main.php`であらかじめ指定したパスワードを入力することにより、全ての利用可能なコード生成ツールがリストされたページが表示されます。

> Note|注: 上記コードは製品機械においては取り除くべきです。コード生成ツールは開発機械でのみ使われるべきです。


モデルの作成
---------------

最初にそれぞれのデータベーステーブルの[モデル](http://www.yiiframework.com/doc/guide/basics.model)クラスを作成する必要があります。このチュートリアルの後半に表れるように、モデルクラスによって直観的なオブジェクト指向のやり方でデータベースにアクセスすることができます。

モデル生成ツールを使用するためには、`モデルジェネレータ`リンクをクリックしてください。

`モデルジェネレータ`ページでは、`Table Name`フィールドに`tbl_user` (ユーザテーブル名)、`Table Prefix`フィールドに`tbl_`と入力し、`プレビュー`ボタンを押します。
プレビューテーブルが表れます。テーブルのリンクをクリックすることで、生成されるコードをプレビューすることができます。
もし、全てがOKであれば、`生成`ボタンを押すことによりコードを生成し、ファイルに保存することができます。

> Info|情報: コードジェネレータは生成されたコードをファイルに保存する必要があるので、ウェブプロセスから対応するファイルを生成し、更新するためのパーミッションが必要になります。
簡単のため、`/wwwroot/blog`ディレクトリ全体をウェブプロセスから書込み可能とすることがあります。これは`Gii`を使用するときの開発マシンのみ必要であることに注意してください。

残りのデータベーステーブル、つまり`tbl_post`、`tbl_comment`、`tbl_tag`、`tbl_lookup`について同じステップを繰り返します。

> Tip|ヒント: アスタリスクキャラクタ'\*'を`Table Name`フィールドに入力することが可能です。これにより一発で、*全ての*データベーステーブルのモデルクラスが生成されます。

この段階において、新しく生成されたファイルは以下のとおりです。

 * `models/User.php`ファイルは[CActiveRecord]を継承する`User`クラスを含みます。このクラスは`tbl_user`データベーステーブルにアクセスするのに使われます。
 * `models/Post.php`ファイルは[CActiveRecord]を継承する`Post`クラスを含みます。このクラスは`tbl_post`データベーステーブルにアクセスするのに使われます。
 * `models/Tag.php`ファイルは[CActiveRecord]を継承する`Tag`クラスを含みます。このクラスは`tbl_tag`データベーステーブルにアクセスするのに使われます。
 * `models/Comment.php`ファイルは[CActiveRecord]を継承する`Comment`クラスを含みます。このクラスは`tbl_comment`データベーステーブルにアクセスするのに使われます。
 * `models/Lookup.php`ファイルは[CActiveRecord]を継承する`Lookup`クラスを含みます。このクラスは`tbl_lookup`データベーステーブルにアクセスするのに使われます。


CRUDオペレーションの実装
----------------------------

モデルクラスが生成された後は、これらのモデルについてCRUDオペレーションを実装するコードを生成する`Crudジェネレータ`が使用可能です。
`Post`と`Comment`モデルについてこれを行います。

`Crudジェネレータ`のページにおいて、`Post`(たった今作成した記事のモデルクラス名)を`Model Class`フィールドに入力し`Preview`ボタンを押します。たくさんのファイルが生成されるのが見られます。それらを生成するために`生成`ボタンを押します。


`Comment`モデルについても同じステップを繰り返します。

CRUDジェネレータによって生成されたファイルを見てみましょう。全てのファイルは`/wwwroot/blog/protected`以下に生成されます。
便宜上それらのファイルを[コントローラ](http://www.yiiframework.com/doc/guide/basics.controller)ファイルと[ビュー](http://www.yiiframework.com/doc/guide/basics.view)ファイルに分類します。

 - コントローラファイル:
	 * `controllers/PostController.php`ファイルは`PostController`クラスを含みます。このコントローラは記事のCRUD操作を受け持ちます。
	 * `controllers/CommentController.php`ファイルは`CommentController`クラスを含みます。このコントローラはコメントのCRUD 操作を受け持ちます。

 - ビューファイル:
	 * `views/post/create.php`ファイルは新しい記事を作るHTMLフォームのビューファイルです。
	 * `views/post/update.php`ファイルは記事の更新を行うHTMLフォームのビューファイルです。
	 * `views/post/view.php`ファイルは記事の詳細情報を表示するビューファイルです。
	 * `views/post/index.php`ファイルは記事のリストを表示するビューファイルです。
	 * `views/post/admin.php`ファイルは管理コマンドと一緒に表形式で記事を表示するビューファイルです。
	 * `views/post/_form.php`ファイルは部分ビューファイルであり`views/post/create.php`と`views/post/update.php`に埋め込まれます。これは記事情報を集めるHTMLフォームを表示します。
	 * `views/post/_view.php`ファイルは部分ビューファイルであり`views/post/index.php`で使用されます。これは単一の記事の簡単なビューを表示します。
	 * `views/post/_search.php`ファイルは部分ビューファイルであり`views/post/admin.php`で使用されます。検索フォームを表示します。
	 * 同様のビューファイル一式がコメント用にも生成されます。

試験
-------

以下のURLにアクセスすることにより、たった今生成したコードを試験することができます。

~~~
http://www.example.com/blog/index.php?r=post
http://www.example.com/blog/index.php?r=comment
~~~

自動生成されたコードによる記事とコメントの機能は、それぞれ完全に独立していることに注意してください。また、新しい記事やコメントを作る際に、`authId`や`createTime`といった情報を入力する必要があります。これらの情報は実際のアプリケーションではプログラムによって設定するべきです。しかし心配することはありません。次のマイルストーンでこれらの問題を解決します。今のところは、このプロトタイプがブログアプリケーションに必要なほぼすべての機能をそなえていることに、それなりに満足すべきでしょう。

上記ファイルがどう使われるかをよりよく理解するために、ブログアプリケーションで記事の一覧が表示される場合のワークフローを示します。

 0. ユーザがURL `http://www.example.com/blog/index.php?r=post`を要求します。
 1. ウェブサーバにより [エントリスクリプト](http://www.yiiframework.com/doc/guide/basics.entry) が実行され、[アプリケーション](http://www.yiiframework.com/doc/guide/basics.application)インスタンスが作成・初期化された後、リクエストを処理します。
 2. アプリケーションは `PostController` のインスタンスを作成し、実行します。
 3. `PostController` インスタンスは要求された `index` アクションを、`actionIndex()` メソッドを呼ぶことで実行します。もしユーザがアクションを明示的にURL中で示さなければ、`index`がデフォールトのアクションとなります。
 4. `actionIndex()` メソッドはデータベースに問い合わせを行い、最近の記事リストを取り出します。
 5. `actionIndex()` メソッドは、記事データを `index` ビューで描画します。

<div class="revision">$Id: prototype.scaffold.txt 3332 2011-06-28 20:07:38Z alexander.makarow $</div>
