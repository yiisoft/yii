Membuat Tag Cloud Portlet
==========================

[Tag cloud](http://en.wikipedia.org/wiki/Tag_cloud) menampilkan daftar tag post dengan tampilan visual yang menunjukkan popularitas tiap tag.


Membuat Class `TagCloud`
-------------------------

Kita membuat class `TagCloud` di dalam file `/wwwroot/blog/protected/components/TagCloud.php`. File tersebut berisi :

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

Tidak seperti portlet `UserMenu`, portlet `TagCloud` tidak menggunakan view. Sistem penampilannya dilakukan oleh method `renderContent()`. Alasannya karena tampilannya yang tidak mengandung terlalu banyak tag HTML.

Kita menampilkan setiap tag sebagai hyperlink ke post halaman index dengan parameter tag bersangkutan. Ukuran font untuk setiap link tag diatur sesuai dengan berat terhadap tag-tag lainnya. Jika sebuah tag memiliki nilai frekuensi yang lebih tinggi dibandingkan yang lainnya, maka ukuran font-nya akan lebih besar.


Menggunakan Portlet `TagCloud`
-------------------------

Penggunaan portlet `TagCloud` sangat sederhana. Kita mengubah file layout `/wwwroot/blog/protected/views/layouts/column2.php` menjadi berikut,

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