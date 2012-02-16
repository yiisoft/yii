Creación de Portlet de Comentarios Recientes
============================================

En esta sección, creamos el último portlet que muestra una lista de los comentarios recientes publicados.

Creando la clase `RecentComments`
---------------------------------

Creamos la clase `RecentComments` en el archivo `/wwwroot/blog/protected/components/RecentComments.php`. El archivo tendrá el siguiente contenido:

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

En el código anterior, invocamos el método `findRecentComments` que es el definido en la clase `Comment` como sigue,

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

Creando la vista `recentComments`
---------------------------------

La vista `recentComments` se guarda en el archivo `/wwwroot/blog/protected/components/views/recentComments.php`. Simplemente muestra cada comentario retornado por el método `RecentComments::getRecentComments()`.

Usando el Portlet `RecentComments`
----------------------------------

Modificamos el archivo de diseño `/wwwroot/blog/protected/views/layouts/column2.php` para embeber este último portlet,

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