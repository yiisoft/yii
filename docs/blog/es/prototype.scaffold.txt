Scaffolding
=========================

Crear, leer, actualizar y borrar (CRUD por sus siglas en inglés) son las cuatro operaciones básicas de los objetos de datos en una aplicación. Ya que la tarea de implementar las operaciones CRUD es tan común en el desarrollo de aplicaciones Web, Yii provee herramientas de generación de código bajo el nombre de *Gii* que pueden automatizar este proceso (también conocido como *scaffolding*) para nosotros.

> Note|Nota: Gii está disponible desde la versión 1.1.2. Antes, se tenía que usar la [herramienta de shell yiic](http://www.yiiframework.com/doc/guide/es/quickstart.first-app-yiic) para efectuar la misma tarea.

A continuación, vamos a describir cómo usar esta herramienta para implementar las operaciones CRUD para los posts y comentarios de nuestra aplicación de blog.

Instalando Gii
--------------

Primero necesitamos instalar Gii. Abrimos el archivo `/wwwroot/blog/protected/config/main.php` y agregamos el siguiente código:

~~~
[php]
return array(
	......
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'pick up a password here',
		),
	),
);
~~~

El código agregado instala un módulo llamado `gii`, que nos habilita a acceder al módulo Gii visitando la siguiente URL en nuestro navegador:

~~~
http://www.example.com/blog/index.php?r=gii
~~~

Se nos va a pedir que ingresemos una contraseña. Ingresamos la contraseña que configuramos en `/wwwroot/blog/protected/config/main.php` anteriormente, y deberíamos ver una lista con todas las herramientas de generación de código disponibles.

> Note|Nota: El código anterior debe ser eliminado cuando se ejecute la aplicación en fase de producción. Las herramientas de generación de código deben ser utilizadas solamente en la fase de desarrollo.

Creando los Modelos
-------------------

Primero necesitamos crear una clase [modelo](http://www.yiiframework.com/doc/guide/es/basics.model) para cada una de las tablas de la base de datos. Las clases modelo nos permitirán acceder a la base de datos de una forma orientada a objetos intuitiva, como veremos más adelante en este tutorial.

Hacer click en el enlace `Model Generator` para empezar a usar la herramienta de generación de modelos.

En la página `Model Generator`, ingresar `tbl_user` (el nombre de la tabla usuario) en el campo `Table name`, `tbl_` en el campo `Table Prefix`y luego presionar el botón `Preview`. Una tabla de vista previa se mostrará. Podemos hacer click en el enlace de la tabla para ver el código que va a ser generado. Si todo está bien, haciendo click en el botón `Generate` generamos el código que se guarda en un archivo.

> Info: Como cada generador de código necesita que se guarde el código generado en archivos, el proceso Web debe tener permisos de escritura y modificar estos archivos. Para simplificar, podemos otorgarle al proceso Web permisos de escritura en todo el directorio `/wwwroot/blog`. Esto sólo es necesario en los equipos de desarrollo que utilicen `Gii`.

El mismo procedimiento se repite para el resto de las tablas de la base de datos, incluyendo `tbl_post`, `tbl_comment`, `tbl_tag` y `tbl_lookup`.

>Tip|Consejo: También se puede ingresar un asterisco '\*' en el campo de `Table Name` para que genere una clase modelo para *cada* tabla de la base de datos en un sólo paso.

En esta instancia, tenemos que tener creados los siguientes archivos:

 * `models/User.php` contiene la clase `User` que extiende de [CActiveRecord] y puede ser utilizada para acceder a la tabla de la base de datos `tbl_user`;
 * `models/Post.php` contiene la clase `Post` que extiende de [CActiveRecord] y puede ser utilizada para acceder a la tabla de la base de datos `tbl_post`;
 * `models/Tag.php` contiene la clase `Tag` que extiende de [CActiveRecord] y puede ser utilizada para acceder a la tabla de la base de datos `tbl_tag`;
 * `models/Comment.php` contiene la clase `Comment` que extiende de [CActiveRecord] y puede ser utilizada para acceder a la tabla de la base de datos `tbl_comment`;
 * `models/Lookup.php` contiene la clase `Lookup` que extiende de [CActiveRecord] y puede ser utilizada para acceder a la tabla de la base de datos `tbl_Lookup`;

Implementando las operaciones CRUD
----------------------------------

Después de que están creadas las clases modelos, podemos usar el `Crud Generator` para generar el código implementando las operaciones de CRUD para esos modelos. Vamos a hacer esto para los modelos de `Post` y `Comment`.

En la página de `Crud Generator`, ingresamos `Post` (el nombre de la clase modelo de post que creamos recién) en el campo `Model Class`, y presionamos el botón `Preview`. Vamos a ver que se crean más archivos. Presionamos el botón `Generate` para generarlos.

Repetimos el mismo procedimiento para el modelo `Comment`.

Veamos los archivos generadores por el generador de CRUD. Todos los archivos son generados bajo `/wwwroot/blog/protected`. Por conveniencia, los agrupamos en un archivo [controlador](http://www.yiiframework.com/doc/guide/basics.controller) y archivos de [vista] (http://www.yiiframework.com/doc/guide/basics.view):

 - archivos controladores:
	* `controllers/PostController.php` contiene la clase `PostController` que es el controlador a cargo de todas las operaciones de CRUD sobre posts;
	* `controllers/CommentController.php` contiene la clase `CommentController` que es el controlador a cargo de todas las operaciones de CRUD sobre comentarios;
 - archivos de vista:
	* `views/post/create.php` es el archivo de vista que muestra un formulario HTML para crear un nuevo post;
	* `views/post/update.php` es el archivo de vista que muestra un formulario HTML para actualizar un post existente;
	* `views/post/view.php` es el archivo de vista que muestra información detallada sobre un post;
	* `views/post/index.php` es el archivo de vista que muestra una lista de todos los posts;
	* `views/post/admin.php` es el archivo de vista que muestra los posts en una tabla con comandos administrativos;
	* `views/post/_form.php` es el archivo de vista parcial embebido en `views/post/create.php` y `views/post/update.php`. Muestra un formulario HTML para recolectar información de un post;
	* `views/post/_view.php` es el archivo de vista parcial usado por `views/post/index.php`. Muestra una vista concisa de un post;
	* `views/post/_search.php` es el archivo de vista parcial usado por `views/post/admin.php`. Muestra un formulario de búsqueda;
	* un conjunto similar de otros archivos de vista son generados para el modelo de comentarios.

Test
----

Podemos hacer un test de las funciones implementadas en el código que acabamos de generar accediendo a las siguientes URLs:

~~~
http://www.example.com/blog/index.php?r=post
http://www.example.com/blog/index.php?r=comment
~~~

Las funciones de post y comentario implementadas en el código generado son completamente independientes entre si. También, cuando creamos un nuevo post o comentarios, se nos pide ingresar información como, `author_id` y `create_time`, que en una aplicación real deberían ser configuradas por el programa. No hay que preocuparse. Vamos a solucionar estos problemas en el siguiente hito. Por ahora, deberíamos estar bastantes satisfechos ya que nuestro pototipo contiene muchas de las funciones que necesitamos implementar para la Aplicaicón de Blog.

Para entender mejor cómo son utilizados los archivos anteriores, vamos a mostrar el siguiente flujo de trabajo que sucede en la Aplicación de Blog cuando muestra una lista de posts:

 0. El usuario hace un pedido(request) de la URL `http://www.example.com/blog/index.php?r=post`;
 1. El [script de entrada](http://www.yiiframework.com/doc/guide/basics.entry) es ejecutado por el servidor Web quien crea e inicializa una instancia de la [aplicación](http://www.yiiframework.com/doc/guide/basics.application) para manejar el pedido;
 2. La aplicación crea una instancia de `PostController`y la ejecuta;
 3. La instancia de `PostController` ejecuta la acción `index` llamando a su método `actionIndex()`. Notar que `index` es la acción por defecto si el usuario no especifica una acción para ejecutar en la URL;
 4. El método `actionIndex()` pide a la base de datos que devuelva una lista de los post más recientes;
 5. El método `actionIndex()` crea la vista `index` con la información del post

<div class="revision">$Id$</div>