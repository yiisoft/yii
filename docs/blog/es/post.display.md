Mostrar Posts
=============

En nuestra aplicación de blog, un post puede ser mostrado entre una lista de posts o por sí mismo. El primero es implementado como la operación `index` mientras el segundo con la operación `view`. En esta sección, personalizamos ambas operaciones para completar nuestros requisitos iniciales.

Personalizando la operación `view`
----------------------------------

La operación `view` es implementada por el método `actionView()` en `PostController`. Su vista es generada por la vista `view` con el archivo `/wwwroot/blog/protected/views/post/view.php`.

A continuación se encuentra el código relevante que implementa la operación `view` en `PostController`:

~~~
[php]
public function actionView()
{
	$post=$this->loadModel();
	$this->render('view',array(
		'model'=>$post,
	));
}

private $_model;

public function loadModel()
{
	if($this->_model===null)
	{
		if(isset($_GET['id']))
		{
			if(Yii::app()->user->isGuest)
				$condition='status='.Post::STATUS_PUBLISHED
					.' OR status='.Post::STATUS_ARCHIVED;
			else
				$condition='';
			$this->_model=Post::model()->findByPk($_GET['id'], $condition);
		}
		if($this->_model===null)
			throw new CHttpException(404,'The requested page does not exist.');
	}
	return $this->_model;
}
~~~

Nuestro cambio radica principalmente en el método `loadModel()`. En este método, consultamos la tabla `Post` de acuerdo al parámetro GET `id`. Si el post no se encuentra o si no está publicado o archivado (cuando el usuario es un invitado), lanzamos un error HTTP 404. De lo contrario el objeto post se devuelve a `actionView()` quien pasa el objeto post al script de la vista para mostrarlo.

> Tip|Consejo: Yii captura excepciones HTTP (instancias de [CHttpException]) y las muestra en plantillas predefinidas o vistas de error personalizadas. El esqueleto de la aplicación generada por `yiic` ya contiene una vista de error personalizada en `/wwwroot/blog/protected/views/site/error.php`. Podemos modificar este archivo si queremos personalizar más cómo se muestra el error.

El cambio en el script `view` es principalmente para ajustar el formato y estilo de mostrar posts. No vamos a entrar en detalles aquí. Lectores interesados pueden consultar `/wwwroot/blog/protected/views/post/view.php`.


Personalizando la operación `index`
-----------------------------------

Como la operación `view`, customizamos la operación `index` en dos lugares: el método `actionIndex()` en `PostController` y el archivo de vista `/wwwroot/blog/protected/views/post/index.php`. Principalmente necesitamos agregar la capacidad para mostrar una lista de posts que estén asociados a una etiqueta específica.

A continuación está el método `actionIndex()` modificado de `PostController`:

~~~
[php]
public function actionIndex()
{
	$criteria=new CDbCriteria(array(
		'condition'=>'status='.Post::STATUS_PUBLISHED,
		'order'=>'update_time DESC',
		'with'=>'commentCount',
	));
	if(isset($_GET['tag']))
		$criteria->addSearchCondition('tags',$_GET['tag']);

	$dataProvider=new CActiveDataProvider('Post', array(
		'pagination'=>array(
			'pageSize'=>5,
		),
		'criteria'=>$criteria,
	));

	$this->render('index',array(
		'dataProvider'=>$dataProvider,
	));
}
~~~

En el código anterior, creamos criterios para la consulta para recuperar una lista de post. Estos criterios indican que sólo los posts publicados deben ser devueltos y deben estar ordenados de acuerdo a su hora de actualización en orden descendente. Como cuando mostramos un post en la lista, queremos mostrar cuántos comentarios ha recibido el post, en el criterio también especificamos que devuelva `commentCount`, quien es una relación declarada en `Post::relations()`.

En caso de que un usuario quiera ver posts de una etiqueta específica, agregaríamos una condición de búsqueda a los criterios para que busque la etiqueta especificada.

Usando los criterios de la consulta, podemos crear un proveedor de datos, quien sirve principalmente para tres propósitos. Primero, hace la paginación de los datos cuando hay muchos resultados que pueden ser devueltos. Aquí personalizamos la paginación pconfigurando el tamaño de página a 5. Segundo, ordena los resultados de acuerdo al pedido del usuario. Y por último, alimenta los datos de paginados y ordenados a los widgets o vistas para la presentación.

Luego de terminar con `actionIndex()`, modificamos la vista `index` de la siguiente forma. Nuestro cambio es principalmente agregar el encabezado `h1` cuando el usuario especifica ver los posts con una etiqueta.
~~~
[php]
<?php if(!empty($_GET['tag'])): ?>
<h1>Posts Tagged with <i><?php echo CHtml::encode($_GET['tag']); ?></i></h1>
<?php endif; ?>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
	'template'=>"{items}\n{pager}",
)); ?>
~~~

Usamos [CListView] para mostrar la lista de posts. Este widget requiere de una vista parcial para mostrar el detalle de cada post individual. Aquí especificamos que la vista parcial sea `_view`, que significa el archivo `/wwwroot/blog/protected/views/post/_view.php`. En este script de vista, podemos acceder a la instancia de post que está siendo mostrada a través de la variable local llamada `$data`.

<div class="revision">$Id$</div>