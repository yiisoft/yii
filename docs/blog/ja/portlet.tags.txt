タグクラウドポートレットの作成
==========================

[タグクラウド](http://en.wikipedia.org/wiki/Tag_cloud)は、ポストされたタグを、それぞれのタグの人気のヒントと共に表示します。


`TagCloud`クラスの作成
-------------------------


`TagCloud`クラスを`/wwwroot/blog/protected/components/TagCloud.php`ファイルとして
作成します。このファイルは以下のとおりです。

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

`UserMenu`ポートレットと異り、`TagCloud`ポートレットはビューを使用しません。
その代わり、その表現は`renderContent()`メソッドで行われます。
これは表現が、あまり多くのHTMLタグを含まないためです。

それぞれのタグは対応するタグパラメータと共に、対応するポストのインデクスページへのハイパーリンクとして表示されます。
タグのフォントの大きさは、他のタグとの相対的な重みにより調整されます。
もしあるタグがより頻繁にポストに表れるなら、そのフォントの大きさはより大きく表示されます。

`TagCloud`ポートレットの使用
-------------------------


`TagCloud`ポートレットの使用はとても単純です。
レイアウトファイル`/wwwroot/blog/protected/views/layouts/column2.php`を以下のように修正します。

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

<div class="revision">$Id: portlet.tags.txt 1772 2010-02-01 18:18:09Z qiang.xue $</div>
