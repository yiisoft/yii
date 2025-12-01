Membuat Recent Comments Portlet
================================

Pada bagian ini, kita membuat portlet terakhir yang menampilkan daftar comment yang dipublikasi baru-baru ini.


Membuat Class `RecentComment`
-------------------------------

Kita membuat class `RecentComments` di dalam file `/wwwroot/blog/protected/components/RecentComments.php`. File ini memiliki isi:

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

Di atas, kita memanggil method `findRecentComments` yang mendefinisikan class `Comment` sebagai berikut,

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


Membuat View `recentComments`
-------------------------

View `recentComments` disimpan di dalam file `/wwwroot/blog/protected/components/views/recentComments.php`. File view ini hanya menampilkan setiap comment yang dihasilkan oleh method `RecentComments::getRecentComments()`.


Menggunakan Portlet `RecentComments`
------------------------------

Kita memodifikasi file layout `/wwwroot/blog/protected/views/layouts/column2.php` untuk menempelkan portlet terakhir ini.

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