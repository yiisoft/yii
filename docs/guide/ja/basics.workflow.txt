開発ワークフロー
====================

Yiiの基本的なコンセプトを述べてきましたが、ここでYiiを用いたウェブアプリケーションの共通的なワークフローをご紹介しましょう。
この段階では対象となるアプリケーションの要求分析だけでなく、設計分析もすんでいるものと仮定します。

   1. 骨格となるディレクトリ構造を作成します。[初めてのYiiアプリケーションの作成](/doc/guide/quickstart.first-app)
で示す`yiic`ツールがこのステップに役立ちます。

   2. [アプリケーション](/doc/guide/basics.application)の設定。
これはアプリケーションの設定ファイルを修正することで行います。
このステップでは場合によってはアプリケーションコンポーネントを書くことが必要になります。
これはユーザコンポーネントと呼ばれます。

   3. それぞれのデータタイプに対応する[モデル](/doc/guide/basics.model)クラスを準備します。
[初めてのYiiアプリケーションの作成](/doc/guide/quickstart.first-app#implementing-crud-operations)と
[自動コード生成](/doc/guide/topics.gii)で示すような`Gii`ツールが自動的に、データベースに対応した
[アクティブレコード](/doc/guide/database.ar) クラスを生成します。

   4. それぞれのユーザ要求に対応した、[コントローラ](/doc/guide/basics.controller)クラスを作成します。
ユーザ要求の分類は実際の要求に依存します。
一般的には、モデルクラスがユーザによってアクセスされるならば、対応するコントローラクラスを必要とします。
`Gii`ツールでこのステップも自動化することができます。

   5. [アクション](/doc/guide/basics.controller#action)とそれに対応する[ビュー](/doc/guide/basics.view)を実装します。これは実際の作業において主に実施されるべきステップです。

   6. 必要に応じて、コントローラクラスのアクション[フィルタ](/doc/guide/basics.controller#filter)を初期構成します。

   7. もしテーマ機能を実装する必要があれば、[テーマ](/doc/guide/topics.theming)を作成します。

   8. もし[国際化](/doc/guide/topics.i18n)が必要であれば、翻訳ファイルを作成します。

   9. キャッシュされるべきデータとビューを特定し、[キャッシュ](/doc/guide/caching.overview)を適用します。

   10. 最後に[チューンナップ](/doc/guide/topics.performance)を行い、展開します。

それぞれのステップにおいて、テストケースが必要となります。

<div class="revision">$Id: basics.workflow.txt 2718 2008-12-04 01:40:16Z qiang.xue $</div>
