最近のコメントポートレットの作成
================================

このセクションでは、最近投稿されたコメントのリストを表示する、最後のポートレットを作成します。


`RecentComments`クラスの作成
-------------------------------

`/wwwroot/blog/protected/components/RecentComments.php`ファイルに`RecentComments`クラスを作成します。このファイルは以下の内容です。

~~~
[php]
Yii::import('zii.widgets.CPortlet');

class RecentComments extends CPortlet
{
	public $title='Recent Comments';
	public $maxComments=10;

	public function getRecentComments()
	{
		return Comment::model()->findRecentComments($this->maxComments);
	}

	protected function renderContent()
	{
		$this->render('recentComments');
	}
}
~~~

上記のように、`findRecentComments`メソッドを呼び出します。それは`Comment`クラスで以下のように定義されます。

~~~
[php]
class Comment extends CActiveRecord
{
	......
	public function findRecentComments($limit=10)
	{
		return $this->with('post')->findAll(array(
			'condition'=>'t.status='.self::STATUS_APPROVED,
			'order'=>'t.create_time DESC',
			'limit'=>$limit,
		));
	}
}
~~~

`recentComments`ビューの作成
-------------------------

`/wwwroot/blog/protected/components/views/recentComments.php`ファイルに`recentComments`ビューは格納されます。
これは単純に`RecentComments::getRecentComments()`メソッドで返されるコメントひとつひとつを表示します。

`RecentComments`ポートレットの使用
------------------------------

レイアウトファイル`/wwwroot/blog/protected/views/layouts/column2.php`を修正し、このポートレットを組込みます。

~~~
[php]
......
<div id="sidebar">

	<?php if(!Yii::app()->user->isGuest) $this->widget('UserMenu'); ?>

	<?php $this->widget('TagCloud', array(
		'maxTags'=>Yii::app()->params['tagCloudCount'],
	)); ?>

	<?php $this->widget('RecentComments', array(
		'maxComments'=>Yii::app()->params['recentCommentCount'],
	)); ?>

</div>
......
~~~

<div class="revision">$Id: portlet.comments.txt 1773 2010-02-01 18:39:49Z qiang.xue $</div>
