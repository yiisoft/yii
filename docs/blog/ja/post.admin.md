記事の管理
==========

記事の管理というのは主に、管理用のビューで記事を一覧表示し、全ステータスの記事を閲覧・更新・削除できるようにすることを意味します。これらの操作はそれぞれ`admin`操作や`delete`操作を行うことで達成されます。`yiic`により生成されたコードはあまり修正の必要がありません。以下においては、これら2つの操作がどのように実装されているかを説明します。


表形式による記事の一覧表示
--------------------------

`admin`操作により、全ステータスの記事が表形式で表示されます。この画面はソートとページングをサポートします。以下は`PostController`の`actionAdmin()`メソッドです。

~~~
[php]
public function actionAdmin()
{
	$model=new Post('search');
	if(isset($_GET['Post']))
		$model->attributes=$_GET['Post'];
	$this->render('admin',array(
		'model'=>$model,
	));
}
~~~

上記のコードは`yiic`ツールにより生成されたものであり、どこも修正していません。コード中では最初に`Post`モデルを`search` [シナリオ](/doc/guide/form.model)により作成します。このモデルを使って、ユーザが指定した検索条件を集めます。次にユーザが指定したデータがあればモデルに割り当てます。最後にモデルに対して`admin`ビューを表示します。

以下は`admin`画面に対するコードです。

~~~
[php]
<?php
$this->breadcrumbs=array(
	'Manage Posts',
);
?>
<h1>Manage Posts</h1>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'title',
			'type'=>'raw',
			'value'=>'CHtml::link(CHtml::encode($data->title), $data->url)'
		),
		array(
			'name'=>'status',
			'value'=>'Lookup::item("PostStatus",$data->status)',
			'filter'=>Lookup::items('PostStatus'),
		),
		array(
			'name'=>'create_time',
			'type'=>'datetime',
			'filter'=>false,
		),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
~~~

記事の表示のために[CGridView]を使用します。これを使うとカラムでソートしたり、記事が多くて1ページで表示しきれないときにページング処理をしたりできます。主な変更点は、各カラムの表示方法に関することです。例えば、`title`カラムに対しては、記事の詳細表示へのハイパーリンクになるように指定します。`$data->url`という表現は`Post`クラスで定義した`url`プロパティの値を返します。

> Tip: テキストを表示するときは、テキストに含まれるHTMLエンティティ（特殊文字）をエンコードするために[CHtml::encode()]を呼びます。[クロスサイトスクリプティング](http://www.yiiframework.com/doc/guide/1.1/ja/topics.security)を防ぐためです。


記事の削除
----------

`admin`データグリッドには各行に削除ボタンがあります。削除ボタンをクリックすると対応する記事が削除されます。内部的には、クリックによって引き起こされる`delete`アクションを、以下のように実装します。

~~~
[php]
public function actionDelete()
{
	if(Yii::app()->request->isPostRequest)
	{
		// we only allow deletion via POST request
		$this->loadModel()->delete();

		if(!isset($_POST['ajax']))
			$this->redirect(array('index'));
	}
	else
		throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
}
~~~

上記のコードは`yiic`ツールにより生成されたものであり、どこも修正していません。`$_POST['ajax']`のチェックについて少し説明します。[CGridView]ウィジェットにはとても良い機能があって、初期設定では、ソート・ページング・削除の操作がAJAXモードで実行されます。つまり、このような操作をしてもページの再読み込みが発生しないということです。とはいえ、AJAXではないモードでウィジェットを動かすこともできます（ウィジェットの`ajaxUpdate`プロパティをfalseに設定した場合、またはクライアントのJavaScriptが無効になっている場合）。この2つのシナリオのうちどちらの使い方をするかによって`delete`アクションを次のように変える必要があります。削除要求がAJAX経由で来る場合はブラウザをリダイレクトしませんが、そうでない場合はリダイレクトします。

記事を削除するということは、記事についたコメントもすべて削除することになります。加えて、削除した記事で使っていたタグに関して`tbl_tag`テーブルを更新する必要があります。これらのタスクは両方とも、`Post`モデルクラスの`afterDelete`メソッドを以下のように書くことで実現できます。

~~~
[php]
protected function afterDelete()
{
	parent::afterDelete();
	Comment::model()->deleteAll('post_id='.$this->id);
	Tag::model()->updateFrequency($this->tags, '');
}
~~~

上記コードは見たままの内容です。最初に、削除記事のIDと同じ`post_id`を持つコメントをすべて削除します。次に、削除記事の`tags`に関して`tbl_tag`テーブルを更新します。

> Tip: ここでは削除記事の全コメントを明示的に削除する必要があります。なぜならSQLiteが外部キー制約をサポートしていないからです（訳注：この記事が書かれたのは2010年09月05日）。この制約をサポートしているDBMS（MySQLやPostgreSQLなど）を使う場合は、記事の削除時に関連コメントが自動的に削除されるように外部キー制約をセットアップすることができます。この場合、コード中で明示的に削除処理を呼ぶ必要はありません。

<div class="revision">$Id: post.admin.txt 2425 2010-09-05 01:30:14Z qiang.xue $</div>