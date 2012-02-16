Diseño General
==============

Basado en el análisis de requerimientos, decidimos usar las siguientes tablas en la base de datos para guardar los datos persistentes de nuestra Aplicación de blog:

 * `tbl_user` guarda la información del usuario, incluyendo el nombre de usuario y contraseña.
 * `tbl_post` guarda la información del post. Consiste principalmente de las siguientes columnas:
	 - `title`: requerido, titulo del post;
	 - `content`: requerido, contenido del post, el cual usa: [formato Markdown](http://daringfireball.net/projects/markdown/syntax);
	 - `status`: requerido, estado del post, puede contener uno de los siguientes valores:
		 * 1, significa que el post es un borrador y no es visible al público;
		 * 2, significa que el post está disponible al público;
		 * 3, significa que el post está desactualizado y no es visible en la lista de posts (aunque sí se puede acceder individualmente)
	 - `tags`: opcional, una lista de palabras separadas por comas, que categorizan al post.
 * `tbl_comment`guarda la información del comentario del post. Cada comentario está asociado con un post y principalmente consiste en las siguientes columnas:
	 - `name`: requerido, el nombre del autor;
	 - `email`: requerido, el correo electrónico del autor;
	 - `website`: opcional, la URL del sitio web del autor;
	 - `content`: requerido, el contenido del comentario en formato de texto plano.
	 - `status`: requerido, estado del comentario, indica si el comentario está aprobado (valor 2) o no (valor 1).
 * `tbl_tag` guarda información sobre la frecuencia de la etiqueta del post, necesario para implementar la nube de etiquetas. La tabla contiene principalmente las siguientes columnas:
	 - `name`: requerido, el nombre único de la etiqueta;
	 - `frequency`: requerido, la cantidad de veces que la etiqueta aparece en los posts.
 * `tbl_lookup`guarda información genérica para la localizar información. Es esencialmente un mapeo entre valores enteros y cadenas de texto. El primero se refiere a la representación en nuestro código, mientras el segundo se corresponde con la presentación a los usuarios finales. Por ejemplo, usamos el entero 1 para representar el estado de un post en borrador y una cadena `Draft` mostrar éste estado a los ususarios finales. Esta tabla consiste principalmente de las siguientes columnas:
	 - `name`: la representación textual del item de datos que va a ser desplegada al usuario final;
	 - `code`: la representación numérica del item de datos;
	 - `type`: el tipo del item de datos;
	 - `position`: el orden relativo en el que se muestran los items de datos entre los demás items del mismo tipo.

El siguiente diagrama de Entidad-Relación (ER) muestra la estructura de la tablas y las relaciones entre ellas.

![Entity-Relation Diagram of the Blog Database](schema.png)

Sentencias SQL completas correspondientes al diagrama pueden ser encontradas en [el blog demo](http://www.yiiframework.com/demos/blog/). En nuestra instalación de Yii, se encuentran en el archivo `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql`.



> Info: Nombramos todos los nombres de tablas y columnas en minúscula. Esto se debe a que diferentes Sistemas de Gestión de Bases de Datos, cuentan con diferentes criterios para el trato de mayúsculas y minúsculas, por lo que evitamos este tipo de problemas.
>
> También agregamos un prefijo `tbl_` a todas nuestras tablas. Esto se hace con dos propósitos. Primero, el prefijo introduce un Espacio de Nombres (namespace) a las tablas en caso de que necesiten coexistir con otras tablas en la misma base de datos, lo cual sucede seguido en entornos compartidos donde una base de datos es usada por múltiples aplicaciones. Segundo, usando prefijos en las tablas reducimos la posibilidad de tener algunos nombres de tablas que sean palabras reservadas en el Sistema de Gestión de Base de Datos que utilicemos.

Dividimos el desarrollo de nuestra aplicación de blog en los siguientes hitos.

 * Hito 1: crear un prototipo del sistema de blog. Debe consistir de las funcionalidades más requeridas.
 * Hito 2: completar el gestor de posts. Esto incluye, listar, mostrar, actualizar y borrar posts.
 * Hito 3: completar el gestor de comentarios. Esto incluye crear, listar, aprobar, actualizar y borrar comentarios de posts.
 * Hito 4: implementar portlets. Esto incluye a los portlets de menú, login, nube de etiquetas y de comentarios recientes
 * Hito 5: Ajustes finales y puesta en producción.

<div class="revision">$Id$</div>