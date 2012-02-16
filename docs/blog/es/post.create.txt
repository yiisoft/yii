Creando y Actualizando Posts
============================

Con el modelo `Post` listo, necesitamos afinar las acciones y vistas para el controlador `PostController`. En esta sección, primero personalizamos el acceso de las operaciones CRUD; luego modificamos el código implementando las operaciones `create` y `update`.

Personalizando el Control de Acceso
-----------------------------------

Lo primero que vamos a querer personalizar es el [control de acceso](http://www.yiiframework.com/doc/guide/topics.auth#access-control-filter) porque el código generado por `gii` no se adapta a nuestras necesidades.

Modificamos el método `accessRules()` en el archivo `/wwwroot/blog/protected/controllers/PostController.php` como sigue,

~~~
[php]
public function accessRules()
{
	return array(
		array('allow',  // allow all users to perform 'list' and 'show' actions
			'actions'=>array('index', 'view'),
			'users'=>array('*'),
		),
		array('allow', // allow authenticated users to perform any action
			'users'=>array('@'),
		),
		array('deny',  // deny all users
			'users'=>array('*'),
		),
	);
}
~~~

Estas reglas indican que todos los usuarios pueden acceder a las acciones `index` y `vista`, y los usuarios autenticados pueden acceder a cualquier acción, incluyendo la acción `admin`. El usuario debe ver denegado su acceso en cualquier otro escenario. Estas reglas son evaluadas en el orden que se listan aquí. La primer regla que concuerde el contexto actual hace la decisión del acceso. Por ejemplo, si el usuario actual es el propietario del sistema que intenta visitar la página de creación de posts, la segunda regla va a concordar y se le va a dar acceso al usuario.


Personalizando las operaciones de `create` y `update`
-----------------------------------------------------

Las operaciones de `create` y `update` son muy similares. Las dos necesitas mostrar un formulario HTML para recolectar los ingresos del usuario, validarlos y guardarlos en la base de datos. La principal diferencia es que la operación `update` va a pre-poblar el formulario con los datos encontrados en la base de datos que existen del post. Por esta razón, `gii` genera una vista parcial `/wwwroot/blog/protected/views/post/_form.php` que es embebida en las vistas de `create` y `update` para generar el formulario HTML buscado.

Primero cambiamos el archivo `_form.php` para que el formulario HTML junte solamente los ingresos que queremos: `title`, `content`, `tags` y `status`. Luego usamos campos de texto plano para colectar ingresos para los primeros tres atributos, y una lista desplegable para obtener el ingreso para `status`. Las opciones de la lista desplegable son los textos mostrados de los posibles estados de un post:

~~~
[php]
<?php echo $form->dropDownList($model,'status',Lookup::items('PostStatus')); ?>
~~~

En este llamada `Lookup::items('PostStatus')` recuperamos la lista de los posibles estados de un post.

Luego modificamos la clase `Post` para que pueda configurar automáticamente algunos atributos (e.g. `create_time`, `author_id`) antes que un post sea guardado en la base de datos. Sobrecargamos el método `beforeSave()` como sigue,

~~~
[php]
protected function beforeSave()
{
	if(parent::beforeSave())
	{
		if($this->isNewRecord)
		{
			$this->create_time=$this->update_time=time();
			$this->author_id=Yii::app()->user->id;
		}
		else
			$this->update_time=time();
		return true;
	}
	else
		return false;
}
~~~

Cuando guardamos un post, queremos actualizar la tabla `tbl_tag` para reflejar el cambio en las frecuencias de etiquetas. Podemos hacer esto en el método `afterSave()`, quien es automáticamente invocado por Yii después de que un post se guarda exitosamente en la base de datos.

~~~
[php]
protected function afterSave()
{
	parent::afterSave();
	Tag::model()->updateFrequency($this->_oldTags, $this->tags);
}

private $_oldTags;

protected function afterFind()
{
	parent::afterFind();
	$this->_oldTags=$this->tags;
}
~~~

En la implementación, como queremos detectar si un usuario cambia las etiquetas en el caso de que esté actualizando un post existente, necesitamos saber cuáles son las etiquetas viejas. Por esta razón, también escribimos el método `afterFind()` para mantener las etiquetas viejas en la variable `_oldTags`. El método `afterFind()` es invocado automáticamente por Yii cuando un registro AR es completado con la información de la base de datos.

No vamos a dar detalles sobre el método `Tag::updateFrequency()` aquí. Lectores interesados pueden consultar el archivo `/wwwroot/yii/demos/blog/protected/models/Tag.php`.


<div class="revision">$Id$</div>