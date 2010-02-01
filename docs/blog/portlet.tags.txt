Creating Tag Cloud Portlet
==========================

[Tag cloud](http://en.wikipedia.org/wiki/Tag_cloud) displays a list of post tags with visual decorations hinting the popularity of each individual tag.


Creating `TagCloud` Class
-------------------------

We create the `TagCloud` class in the file `/wwwroot/blog/protected/components/TagCloud.php`. The file has the following content:

~~~
[php]
Yii::import('zii.widgets.CPortlet');

class TagCloud extends CPortlet
{
	public $title='Tags';
	public $maxTags=20;

	protected function renderContent()
	{
		$tags=Tag::model()->findTagWeights($this->maxTags);

		foreach($tags as $tag=>$weight)
		{
			$link=CHtml::link(CHtml::encode($tag), array('post/index','tag'=>$tag));
			echo CHtml::tag('span', array(
				'class'=>'tag',
				'style'=>"font-size:{$weight}pt",
			), $link)."\n";
		}
	}
}
~~~

Unlike the `UserMenu` portlet, the `TagCloud` portlet does not use a view. Instead, its presentation is done in the `renderContent()` method. This is because the presentation does not contain much HTML tags.

We display each tag as a hyperlink to the post index page with the corresponding tag parameter. The font size of each tag link is adjusted according to their relative weight among other tags. If a tag has higher frequency value than the other, it will have a bigger font size.


Using `TagCloud` Portlet
-------------------------

Usage of the `TagCloud` portlet is very simple. We modify the layout file `/wwwroot/blog/protected/views/layouts/column2.php` as follows,

~~~
[php]
......
<div id="sidebar">

	<?php if(!Yii::app()->user->isGuest) $this->widget('UserMenu'); ?>

	<?php $this->widget('TagCloud', array(
		'maxTags'=>Yii::app()->params['tagCloudCount'],
	)); ?>

</div>
......
~~~

<div class="revision">$Id$</div>