Tworzenie portletu chmurki tagów
==========================

[Chmurka tagów](http://en.wikipedia.org/wiki/Tag_cloud) wyświetla listę tagów wiadomości wraz z wizualnymi ozdobnikami, podpowiadającymi jak bardzo popularny jest każdy z tagów. 


Tworzenie klasy `TagCloud`
-------------------------

Tworzymy klasę `TagCloud` w pliku `/wwwroot/blog/protected/components/TagCloud.php`. Plik ten ma następującą zawartość:

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

W odróżnieniu od portletu `UserMenu`, portlet chmury tagów `TagCloud` nie używa widoku. W zamian, jego wyświetlana zawartość jest tworzona w metodzie `renderContent()`. To dlatego, że warstwa prezentacji nie zawiera zbyt wiele tagów HTML.

Wyświetlamy każdy tag jako hiperłącze do strony z listą wiadomości wraz z odpowiadającym mu parametrem tagu. Rozmiar czcionki dla każdego linku jest dostosowywany odpowiednio do wagi względem pozostałych tagów. Jeśli tag posiada większą częstotliwość wystąpień niż inny, będzie on posiadał większy rozmiar czcionki. 


Używanie portletu `TagCloud`
-------------------------

Używanie portletu chmurki tagów `TagCloud` jest bardzo proste. Modyfikujemy plik układu `/wwwroot/blog/protected/views/layouts/column2.php` w następujący sposób:

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