Administración de Posts
=======================

La administración de posts se refiere principalmente a listar posts en una vista administrativa que nos permita ver posts con todos sus estados, actualizarlos y borrarlos. Esto se logra con la operación `admin` y la operación `delete`. El código generado por `yiic` no necesita demasiadas modificaciones. A continuación explicamos principalmente cómo están implementadas estas operaciones.

Listando Posts de Forma Tabular
-----------------------------

La operación `admin` muestra posts con todos los estados en una vista tabular. La vista puede ordenar y paginar. Lo siguiente es el método `actionAdmin()` en `PostController`:

~~~
[php]
public function actionAdmin()
{
	$model=new Post('search');
	if(isset($_GET['Post']))
		$model->attributes=$_GET['Post'];
	$this->render('admin',array(
		'model'=>$model,
	));
}
~~~

Este código es generado por la herramiento `yiic` sin ninguna modificación. Primero crea un modelo `Post` bajo el escenario `búsqueda` [escenario](/doc/guide/es/form.model). Vamos a usar este modelo para juntar las condiciones de búsqueda que un usuario especifica. Luego asignamos al modelo la información suministrada por el usuari, si es que hay alguna. Finalmente, mostramos la vista `admin` con el modelo.

A continuación tenemos el código para la vista `admin`:

~~~
[php]
<?php
$this->breadcrumbs=array(
	'Manage Posts',
);
?>
<h1>Manage Posts</h1>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'title',
			'type'=>'raw',
			'value'=>'CHtml::link(CHtml::encode($data->title), $data->url)'
		),
		array(
			'name'=>'status',
			'value'=>'Lookup::item("PostStatus",$data->status)',
			'filter'=>Lookup::items('PostStatus'),
		),
		array(
			'name'=>'create_time',
			'type'=>'datetime',
			'filter'=>false,
		),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
~~~

Usamos [CGridView] para mostrar los posts. Nos permite ordenar por columna y paginar a través de los posts si es que hay muchos para ser mostrados en una página sola. Nuestro cambio principalmente acerca de cómo mostrar cada columna. Por ejemplo, para la columna `title`, especificamos que debería ser mostrada con un hipervínculo que apunte a la vista detallada de un post. La expresión `$data->url` retorna el valor de la propiedad `url` que definimos en la clase `Post`.

> Tip|Consejo: Cuando se muestra texto, llamamos a [CHtml::encode()] para codificar entidades HTML en él. Esto previene de ataques del tipo [cross-site scripting](http://www.yiiframework.com/doc/guide/topics.security).


Borrando Posts
--------------

En la grilla de datos `admin`, hay un botón de borrar en cada fila. Haciendo click sobre el botón debería borrar el post correspondiente. Internamente, dispara la acción `delete` implementada de la siguiente forma:

~~~
[php]
public function actionDelete()
{
	if(Yii::app()->request->isPostRequest)
	{
		// we only allow deletion via POST request
		$this->loadModel()->delete();

		if(!isset($_POST['ajax']))
			$this->redirect(array('index'));
	}
	else
		throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
}
~~~

Este código es uno de los generados por la herramienta `yiic` sin ningún cambio. Vamos a explicar con más detalle sobre el chequeo de `$_POST['ajax']`. El widget [CGridView] tiene una función muy interesante en el que sus operaciones de ordenamiento, paginación y borrado son todas hechas en el modo AJAX por defecto. Esto significa, que toda la página no es recargada si alguna de estas operaciones se ejecuta. De todas formas, es posible también que el widget corra en modo no-AJAX (configurando su propiedad `ajaxUpdate` en falso o deshabilitando Javascript del lado del cliente). Es necesario para la acción `delete` diferenciar entre estos dos escenarios: si el pedido de borrar es hecho a través de AJAX, entonces no debemos redireccionar el navegador del usuario; de otro modo, deberíamos.

Borrando un post debería también causar el borrado de todos los comentarios para ese post. Además, deberíamos actualizar la tabla `tbl_tag` de acuerdo a los tags del post borrado. Ambas tareas se pueden lograr escribiendo el método `afterDelete`en el modelo `Post` como sigue,

~~~
[php]
protected function afterDelete()
{
	parent::afterDelete();
	Comment::model()->deleteAll('post_id='.$this->id);
	Tag::model()->updateFrequency($this->tags, '');
}
~~~

El código es muy simple: borra todos los comentarios cuyo `post_id` sea igual al ID del post borrado; luego actualiza la tabla `tbl_tag` para las etiquetas del post borrado.

> Tip|Consejo: Debemos borrar explícitamente todos los comentarios del post borrado porque SQLite no implementa reglas de clave foránea. En un SGDB que tenga esta capacidad (como MySQL, PostgreSQL), las restricciones de clave foránea se pueden configurar para que el SGBD aumáticamente borre los comentarios relaciones si el post es borrado. En ese caso, no necesitamos especificar explícitamente el llamado de borrado en nuestro código. 

<div class="revision">$Id$</div>