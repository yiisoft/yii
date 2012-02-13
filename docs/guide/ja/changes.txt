新機能
============

このページは、各々のYiiリリースにおいて導入された主な新機能をまとめています。

Version 1.1.9
-------------

Version 1.1.8
-------------
 * [カスタムURLルールクラスのサポートを追加](/doc/guide/topics.url#using-custom-url-rule-classes)

Version 1.1.7
-------------
 * [RESTful URL に対するサポートを追加](/doc/guide/topics.url#user-friendly-urls)
 * [クエリキャッシングに対するサポートを追加](/doc/guide/caching.data#query-caching)
 * [リレーショナル named scope にパラメータを渡すことが可能になった](/doc/guide/database.arr#relational-query-with-named-scopes)
 * [関連するモデルを取得することなくリレーショナルクエリを実行する機能を追加](/doc/guide/database.arr#performing-relational-query-without-getting-related-models)
 * [HAS_MANY through と HAS_ONE through の AR リレーションに対するサポートを追加](/doc/guide/database.arr#relational-query-with-through)
 * [DB 移行変換機能においてトランザクションのサポートを追加](/doc/guide/database.migration#transactional-migrations)
 * [クラスベースのアクションにおけるパラメータバインディングの使用に対するサポートを追加](/doc/guide/basics.controller#action-parameter-binding)
 * [CActiveForm]によるシームレスなクライアント側のデータ検証に対するサポートを追加

 Version 1.1.6
-------------
 * [クエリビルダーを追加](/doc/guide/database.query-builder)
 * [データベース移行変換機能を追加](/doc/guide/database.migration)
 * [MVC のベストプラクティス](/doc/guide/basics.best-practices)
 * [コンソールコマンドで無名パラメータとグローバルオプションの使用に対するサポートを追加](/doc/guide/topics.console)

Version 1.1.5
-------------

 * [コンソールコマンドアクションとパラメータバインディングに対するサポートを追加](/doc/guide/topics.console)
 * [ネームスペースを持つクラスのオートローディングに対するサポートを追加](/doc/guide/basics.namespace)
 * [ウィジェットのビューに対するテーマ適用のサポートを追加](/doc/guide/topics.theming#theming-widget-views)

Version 1.1.4
-------------

 * [自動的なアクションのパラメータバインディングに対するサポートを追加](/doc/guide/basics.controller#action-parameter-binding)

Version 1.1.3
-------------

 * [アプリケーション構成でウィジェットのデフォルト値を構成する機能の追加](/doc/guide/topics.theming#customizing-widgets-globally)

Version 1.1.2
-------------

 * [Gii と呼ばれるウェブベースのコード生成ツールを追加](/doc/guide/topics.gii)

Version 1.1.1
-------------

 * CActiveForm を追加。CActiveForm は、フォームに関係するコードの作成を単純化し、クライアントとサーバーの両側においてシームレスで一貫性のあるバリデーションをサポートする。

 * yiic ツールによって生成されるコードを見直した。
 具体的には、スケルトンアプリケーションは複数のレイアウトを持つようになった。
 操作メニューは CRUD のページのために再構成された。
 crud コマンドで生成される admin ページは検索とフィルターリングの機能を持つようになった。
 フォームの表示に CActiveForm を使った。

 * [グローバルな yiic コマンドを定義することを可能にした](/doc/guide/topics.console)

Version 1.1.0
-------------

 * [ユニットテストと機能テストに対するサポートの追加](/doc/guide/test.overview)

 * [ウィジェットスキンを使用するためのサポートの追加](/doc/guide/topics.theming#skin)

 * [拡張性のあるフォームビルダの追加](/doc/guide/form.builder)

 * 安全なモデルアトリビュートを宣言する方法の改善。以下を参照してください。
 [安全なアトリビュート割り当て](/doc/guide/form.model#securing-attribute-assignments).

 * 全てのテーブルが一つのSQL文でジョインできるように、デフォルトのリレーショナルアクティブレコードクエリを変更した。

 * デフォールトのテーブルエイリアスを、アクティブレコードのリレーション名をとるように変更

 * [テーブルプレフィクスを使用するためのサポートを追加](/doc/guide/database.dao#using-table-prefix).

 * [Ziiライブラリ](http://code.google.com/p/zii/)として知られる新しいエクステンションを追加

 * AR クエリにおける主テーブルのエイリアスを 't' に固定

<div class="revision">$Id: changes.txt 3526 2012-01-01 03:18:43Z qiang.xue $</div>
