まとめ
=======

マイルストーン1が完了しました。これまでにやったことをまとめてみましょう。

 1. 必要機能を洗い出しました
 2. Yii フレームワークをインストールしました
 3. スケルトンアプリケーションを作成しました
 4. ブログデータベースを設計し、作成しました
 5. データベースと接続できるようアプリケーション初期設定を変更しました
 6. 記事とコメントの基本CRUD操作ができるようコードを実装しました
 7. 認証メソッドを変更し、`tbl_user` テーブルに対してチェックするように変更しました

新しいプロジェクトでは、多くの場合、この最初のマイルストーン 1 から 4 の手順をこなすことになるでしょう。

`gii` ツールによって生成されたコードは、データベーステーブルのための完全な CRUD 操作機能を実装しますが、実際に利用する際にはしばしば、変更する必要があります。この理由から、次の2つのマイルストーンでは、初期の要求を満たすように、投稿とコメントについて発生する CRUD コードをカスタマイズします。

一般的には、最初に[モデル](http://www.yiiframework.com/doc/guide/basics.model)クラスファイルで、適切な[バリデーション](http://www.yiiframework.com/doc/guide/form.model#declaring-validation-rules)ルールを加え、[リレーションオブジェクト](http://www.yiiframework.com/doc/guide/database.arr#declaring-relationship)を宣言する変更を行います。その後、それぞれの CRUD 操作のために[コントローラアクション](http://www.yiiframework.com/doc/guide/basics.controller)と[ビュー](http://www.yiiframework.com/doc/guide/basics.view)コードを変更します。


<div class="revision">$Id: prototype.summary.txt 2333 2009-02-16 05:20:17Z qiang.xue $</div>
