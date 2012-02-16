Creating Recent Comments Portlet
================================

In this section, we create the last portlet that displays a list of comments recently published.


Creating `RecentComments` Class
-------------------------------

We create the `RecentComments` class in the file `/wwwroot/blog/protected/components/RecentComments.php`. The file has the following content:

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

In the above we invoke the `findRecentComments` method which is defined in the `Comment` class as follows,

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


Creating `recentComments` View
-------------------------

The `recentComments` view is saved in the file `/wwwroot/blog/protected/components/views/recentComments.php`. It simply displays every comment returned by the `RecentComments::getRecentComments()` method.


Using `RecentComments` Portlet
------------------------------

We modify the layout file `/wwwroot/blog/protected/views/layouts/column2.php` to embed this last portlet,

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

<div class="revision">$Id$</div>