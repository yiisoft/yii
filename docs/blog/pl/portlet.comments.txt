Tworzenie portletu ostatnich komentarzy
================================

W części tej utworzymy portlet, który wyświetli listę komentarzy, które zostały ostatnio opublikowane. 


Tworzenie klasy `RecentComments`
-------------------------------

Tworzymy klasę `RecentComments` w pliku `/wwwroot/blog/protected/components/RecentComments.php`. Plik ten ma następującą zawartość:

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

W powyższym kodzie wywołaliśmy metodę `findRecentComments`, która jest zdefiniowana w klasie `Comment` w następujący sposób:

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


Tworzenie widoku `recentComments`
-------------------------

Widok `recentComments` jest zapisany w pliku `/wwwroot/blog/protected/components/views/recentComments.php`. Widok po prostu wyświetla każdy komentarz zwrócony przez metodę `RecentComments::getRecentComments()`.


Używanie portletu `RecentComments`
------------------------------

Zmodyfikujemy plik układu `/wwwroot/blog/protected/views/layouts/column2.php` by osadzić w nim ten ostatni portlet.

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