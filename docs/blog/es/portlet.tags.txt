Creación de Portlet de Nube de Etiquetas (Tag Cloud)
==========================

[Nube de etiquetas](https://es.wikipedia.org/wiki/Nube_de_palabras) muestra una lista de etiquetas de post con elementos visuales diferenciando la popularidad de cada etiqueta individualmente.

Creando la clase `TagCloud`
---------------------------

Creamos la clase `TagCloud` en el archivo `/wwwroot/blog/protected/components/TagCloud.php`. El archivo tendrá el siguiente contenido:

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

Al contrario del portlet `UserMenu`, el portlet `TagCloud` no usa una vista. Su presentación se realiza en el método `renderContent()`. Esto se debe a que la presentación no contiene muchas etiquetas HTML.

Mostramos cada etiqueta como un hipervínculo a la página `index` del post con su correspondiente etiqueta de parámetro. El tamaño de la fuente para cada enlace de la etiqueta se ajusta de acuerdo al peso relativo con las otras etiquetas. Si una etiqueta tiene una frecuencia más alta que la otra, va a tener un tamaño de fuente mayor.

Usando el Portlet `TagCloud`
----------------------------

El uso del portlet `TagCloud` es muy simple. Modificamos el diseño del archivo `/wwwroot/blog/protected/views/layouts/column2.php` como sigue,

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