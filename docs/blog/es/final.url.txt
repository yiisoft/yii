Optimización de URLs
====================

Las URLs apuntando a varias páginas de nuestra Aplicación de Blog actualmente se ven feas. Por ejemplo, la URL para mostrar la página de un post se ve como:

~~~
/index.php?r=post/show&id=1&title=A+Test+Post
~~~

En esta sección, describimos cómo optimizar estas URLs y hacerlas SEO amigables. Nuestro objetivo es ser capaces de usar las siguientes URLs en la aplicación:

 1. `/index.php/posts/yii`: lleva a la pagina mostrando una lista de posts con la etiqueta `yii`;
 2. `/index.php/post/2/A+Test+Post`: lleva a la página que muestra el detalle del post con ID 2 cuyo título es `A Test Post`;
 3. `/index.php/post/update?id=1`: lleva a la página que permite actualizar el post con ID 1.

Nota que en el formato de la segunda URL, incluimos el título de post en la URL. Esto es principalmente para hacer las URL SEO amigables. Se dice que los motores de búsqueda respetan las palabras encontradas en la URL cuando son indexadas.

Para cumplir nuestro objetivo, modificamos la [configuración de la aplicación](http://www.yiiframework.com/doc/guide/es/basics.application#application-configuration) como sigue:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
        		'post/<id:\d+>/<title:.*?>'=>'post/view',
        		'posts/<tag:.*?>'=>'post/index',
        		'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
	),
);
~~~

En lo anterior, configuramos el componente de [urlManager](http://www.yiiframework.com/doc/guide/es/topics.url) configurando su propiedad `urlFormat` que sea `path` y agregando una serie de `rules` (reglas).

Las reglas son usadas por `urlManager` para clasificar y crear las URLs en el formato deseado. Por ejemplo, la segunda regla dice que si una URL `/index.php/posts/yii` es pedida, el componente `urlManager` debería ser responsable de despachar el pedido a la [ruta](http://www.yiiframework.com/doc/guide/es/basics.controller#route) `post/index` y generar un parámetro GET `tag` con el valor `yii`. Por otro lado, cuando creamos una URL con la ruta `post/index` y parámetro `tag`, el componente `urlManager` va a usar ésta regla para generar la URL deseada `/index.php/posts/yii`. Por esta razón, decimos que `urlManager` es un gestor de URL de doble-camino.
El componente `urlManager` puede optimizar todavía más nuestras URLs, como ocultar `index.php` en las URLs, agregando un sufijo `.html` a las URLs. Podemos obtener estas funciones fácilmente con la configuración de varias propiedades de `urlManager` en la configuración de la aplicación. Para más detalles, por favor consulta [La Guía][the Guide](http://www.yiiframework.com/doc/guide/es/topics.url).

<div class="revision">$Id$</div>