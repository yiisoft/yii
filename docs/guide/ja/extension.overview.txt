エクステンション概要
========

Yiiを拡張することは開発の最中に普通に行われます。
例えば、新しくコントローラを開発するときは[CController]クラスを継承することでYiiを拡張します。
新しくウィジェットを開発するときは[CWidget]を継承するか、既存のウィジェットクラスを拡張します。
もし拡張したコードが他の第3者によって再利用されるように設計されていれば、それは*エクステンション*と呼ばれます。

エクステンションは通常単一の目的で使用されます。Yiiの用語では、以下の種類に分類されます。

 * [アプリケーションコンポーネント](/doc/guide/basics.application#application-component)
 * [ビヘイビア](/doc/guide/basics.component#component-behavior)
 * [ウィジェット](/doc/guide/basics.view#widget)
 * [コントローラ](/doc/guide/basics.controller)
 * [アクション](/doc/guide/basics.controller#action)
 * [フィルタ](/doc/guide/basics.controller#filter)
 * [コンソールコマンド](/doc/guide/topics.console)
 * バリデータ: これは[CValidator]クラスを拡張したコンポーネントクラスです。
 * ヘルパ: これは静的なメソッドのみを持つクラスです。クラス名を名前空間として用いる場合はグローバル関数のようにみえます。
 * [モジュール](/doc/guide/basics.module):モジュールは自分自身を含むことが可能なソフトウエア単位であり、
[モデル](/doc/guide/basics.model)や[ビュー](/doc/guide/basics.view)や[コントローラ](/doc/guide/basics.controller)
や他のサポートするコンポーネントを含みます。多くの点においてモジュールは[アプリケーション](/doc/guide/basics.application)
に似ています。一番の違いは、モジュールはアプリケーションの内部にあるということです。例えば、ユーザ管理機能を提供するモジュールが挙げられます。

エクステンションは上のどれにも当てはまらないコンポーネントの場合があります。
実際に、コードのほとんどすべての部分が個々のニーズに適するために拡張でき、カスタマイズできるように、Yiiは注意深く設計されています。

<div class="revision">$Id: extension.overview.txt 2739 2010-12-14 01:50:04Z weizhuo $</div>
