Configurando la Base de Datos
=============================

Luego de haber creado un esqueleto para la aplicación y terminado el diseño de la base de datos, en esta sección vamos a crear la base de datos del blog y estableceremos una conexión en el esqueleto de la aplicación


Creando la Base de Datos
------------------------

Elegimos crear una base de datos SQLite. Como el soporte de bases de datos en Yii está hecho por encima de [PDO](http://www.php.net/manual/en/book.pdo.php), podemos fácilmente cambiar entre diferentes tipos de SGBD (e.g. MySQL, PostgreSQL) sin la necesidad de cambiar código en nuestra aplicación.

Creamos el archivo de base de datos `blog.db` en el directorio `/wwwroot/blog/protected/data`. Notar que tanto el directorio y el archivo de la base de datos deben tener permisos de escritura por el Servidor Web, como lo requiere SQLite. Podemos simplemente copiar el archivo de base de datos desde el demo de blog en nuestra instalación de Yii que se encuentra en `/wwwroot/yii/demos/blog/protected/data/blog.db`. También podemos generar la base de datos ejecutando las sentencias SQL del archivo `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql`.

> Tip|Consejo: Para ejecutar sentencias SQL, podemos usar la herramienta de línea de comando `sqlite3` que se encuentra en [Sitio Oficial de SQLite](http://www.sqlite.org/download.html).

Estableciendo Conexión
----------------------

Para usar la base de datos del blog en el esqueleto de la aplicación que creamos, necesitamos modificar la [configuración de la aplicación](http://www.yiiframework.com/doc/guide/es/basics.application#application-configuration) que se encuentra en el script PHP `/wwwroot/blog/protected/config/main.php`. El script retorna un arreglo asociativo consistiendo de pares nombre-valor, cada uno de los cuales es usado para inicializar una propiedad con permisos de escritura de la [instancia de la aplicación](http://www.yiiframework.com/doc/guide/es/basics.application).

Configuramos el componente `db` como sigue,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:/wwwroot/blog/protected/data/blog.db',
			'tablePrefix'=>'tbl_',
		),
	),
	......
);
~~~

La configuración anterior dice que tenemos un [componente de aplicación](http://www.yiiframework.com/doc/guide/es/basics.application#application-component) `db` donde la propiedad `connectionString` debe ser inicializada como `sqlite:/wwwroot/blog/protected/data/blog.db` y la propiedad `tablePrefix` debe ser `tbl_`.

Con esta configuración, podemos acceder al objeto de conexión de la BD usando `Yii::app()->db` en cualquier lugar de nuestro código. Notar que `Yii::app()`retorna una instancia de la aplicación que creamos en el script de entrada. Si se está interesado en los posibles métodos y propiedad que una conexión a BD tiene, hay que consultar su [Referencia de Clase|CDbConnection]. De todas formas, en la mayoría de los casos no vamos a usar el objeto de conexión a BD directamente. En vez de eso, vamos a utilizar [ActiveRecord](http://www.yiiframework.com/doc/guide/es/database.ar) para acceder a la base de datos.

La propiedad `tablePrefix` que configuramos indica que la conexión `db` debe respetar el hecho que estamos usando `tbl_` como el prefijo de los nombres de las tablas de base de datos. En particular, si en una sentencia SQL hay una palabra entre llaves dobles (e.g. `{{post}}`), entonces la conexión `db` debe traducirlo al nombre de la tabla con el prefijo (e.g. `tbl_post`) antes de enviarlo al SGBD para su ejecución. Esta característica es útil especialmente si en un futuro necesitamos modificar el prefijo de los nombres de las tablas sin modificar el código fuente. Por ejemplo, si estamos desarrollando un Gestor de Contenido (CMS), podemos explotar esta característica para que cuando sea instalado en un nuevo entorno, los usuarios puedan elegir el prefijo de tabla que deseen.

> Tip|Consejo: Si se quiere usar MySQL en vez de SQLite para almacenar los datos, 
> se puede crear una base de datos MySQL llamada `blog` usando las sentencias 
> SQL que se encuentran en `/wwwroot/yii/demos/blog/protected/data/schema.mysql.sql`.
> Luego, modificar la configuración de la aplicación de la siguiente forma,
>
> ~~~
> [php]
> return array(
>     ......
>     'components'=>array(
>         ......
>         'db'=>array(
>             'connectionString' => 'mysql:host=localhost;dbname=blog',
>             'emulatePrepare' => true,
>             'username' => 'root',
>             'password' => '',
>             'charset' => 'utf8',
>             'tablePrefix' => 'tbl_',
>         ),
>     ),
> 	......
> );
> ~~~

<div class="revision">$Id$</div>