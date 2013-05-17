Creación de Portlet Menú de Usuarios
====================================

Basados en el análisis de requerimientos, necesitamos tres portlets diferentes: el portlet de "menú de usuario", el portlet de "nube de etiquetas" y el portlet de "comentarios recientes". Vamos a desarrollar estos portlets extendiendo el widget [CPortlet] que provee Yii.

En esta sección, vamos a desarrollar nuestro primer portlet concreto - el portlet de menú de usuario que despliega una lista de items de menú que son accesibles solamente a usuarios autenticados. El menú contiene cuatro items:

 * Comentarios aprobados: un hipervínculo que lleva a una lista de comentarios pendientes de aprobación;
 * Crear Nuevo Post: un hipervínculo que lleva a la página de creación de un post;
 * Gestionar Posts: un hipervínculo que lleva a la página de gestión de posts;
 * Logout: un botón que hace el logout del usuario actual.

Creando la clase `UserMenu`
---------------------------

Creamos la clase `UserMenu` para representar la parte lógica del portlet de menú de usuario. La clase es guardada en el archivo `/wwwroot/blog/protected/components/UserMenu.php` con el siguiente contenido:

~~~
[php]
Yii::import('zii.widgets.CPortlet');

class UserMenu extends CPortlet
{
	public function init()
	{
		$this->title=CHtml::encode(Yii::app()->user->name);
		parent::init();
	}

	protected function renderContent()
	{
		$this->render('userMenu');
	}
}
~~~

La clase `UserMenu` extiende de la clase `CPortlet` desde la librería `zii`. Sobrecarga los métodos `init()` y `renderContent()` de `CPortlet`. El primero configura el título del portlet para que sea el nombre del usuario actual; el segundo genera el cuerpo del portlet mostrando una vista llamada `userMenu`.

> Tip|Consejo: Notar que debemos incluir explícitamente la clase `CPortlet` llamando a `Yii::import()` antes de que hagamos referencia por primera vez. Esto se debe a que `CPortlet` es parte del project `zii` -- la librería oficial de extensiones para Yii. Por cuestiones de rendimiento, las clases en este proyecto no son listadas como clases del núcleo. Por ello, debemos importarla antes de usarla por primera vez.

Creando la vista `userMenu`
---------------------------

Luego, creamos la vista `userMenu` que es guardada en el archivo `/wwwroot/blog/protected/components/views/userMenu.php`:

~~~
[php]
<ul>
	<li><?php echo CHtml::link('Create New Post',array('post/create')); ?></li>
	<li><?php echo CHtml::link('Manage Posts',array('post/admin')); ?></li>
	<li><?php echo CHtml::link('Approve Comments',array('comment/index'))
		. ' (' . Comment::model()->pendingCommentCount . ')'; ?></li>
	<li><?php echo CHtml::link('Logout',array('site/logout')); ?></li>
</ul>
~~~

> Info: Por defecto, los archivos de vista para un widget deben ser ubicados en el subdirectorio `views` conteniendo el archivo de clase del widget. El nombre del archivo debe ser el mismo que el nombre de la vista.

Usando el Portlet `UserMenu`
----------------------------

Es hora de hacer uso de nuestro recién creado portlet `UserMenu`. Modificamos el archivo de vista del diseño `/wwwroot/blog/protected/views/layouts/column2.php` como sigue:

~~~
[php]
......
<div id="sidebar">
	<?php if(!Yii::app()->user->isGuest) $this->widget('UserMenu'); ?>
</div>
......
~~~

En lo anterior, llamamos al método `widget()` para generar y ejecutar una instancia de  la clase 'UserMenu'. Como el portlet debe ser mostrado solamente a usuarios autenticados, llamamos a `widget()` solamente cuando la propiedad `isGuest` del usuario actual es falsa (significando que el usuario está autenticado).

Probando el Portlet `UserMenu`
------------------------------

Vamos a probar lo que hemos hecho hasta ahora.

 1. Abre una ventana del navegador e ingresa la URL `http://www.example.com/blog/index.php`. Verifica que no se muestre nada en la sección de la barra lateral de la pagina.
 2. Haz click en el hipervínculo `Login` e ingresa los datos en el formulario para ingresar. Si el ingreso es exitoso, verifica que el portlet `UserMenu` aparezca en la barra lateral y el portlet tenga el nombre de usuario como su título.
 3. Haz click en el hipervínculo de `Logout` en el portlet `UserMenu`. Verifica que la acción de logout sea exitosa y que el portlet `UserMenu`desaparece.


Resumen
-------

Lo que creamos es un portlet que es altamente reusable. Podemos fácilmente reusarlo en un proyecto diferente con pocas o ninguna modificación. Más aún, el diseño de este portlet sigue de cerca la filosofía en que la lógica y la presentación deben estar separadas. Mientras no mencionamos esto anteriormente, esta práctica es usada casi en todos lados de una aplicación Yii típica.

<div class="revision">$Id$</div>