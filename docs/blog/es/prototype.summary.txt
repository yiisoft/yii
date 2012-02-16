Resumen
=======

Hemos completado el hito 1. Vamos a hacer un resumen de lo que hemos hecho hasta ahora:

 1. Identificamos los requerimientos a ser completados;
 2. Instalamos el framework Yii;
 3. Creamos una aplicación esqueleto;
 4. Diseñamos y creamos la base de datos del blog;
 5. Modificamos la configuración de la aplicación agregando la conexión a la base de datos;
 6. Generamos el código que implementa las operación CRUD básicas para los posts y comentarios;
 7. Modificamos el método de autenticación para validar sobre la tabla `tbl_user`.

Para un nuevo proyecto, los pasos del 1 al 4 son los que llevan más tiempo para este primer hito.

Aunque el código generado por la herramiento `gii` implementa operaciones CRUD funcionales completamente para una tabla de de base de datos, por lo general necesita ser modificada en aplicaciones prácticas. Por esta razón, en el siguiente hito, nuestro trabajo es personalizar el código CRUD generado sobre los posts y comentarios para que se ajusten a nuestros requerimientos iniciales.

Por lo general, primero modificamos la clase [modelo](http://www.yiiframework.com/doc/guide/es/basics.model) agregándo reglas de [validación](http://www.yiiframework.com/doc/guide/form.model#declaring-validation-rules) y declarando [objetos relacionales](http://www.yiiframework.com/doc/guide/es/database.arr#declaring-relationship). Luego modificamos el código de la [acción del controlador](http://www.yiiframework.com/doc/guide/es/basics.controller) y [vista](http://www.yiiframework.com/doc/guide/es/basics.view) para cada operación individual CRUD.

<div class="revision">$Id$</div>