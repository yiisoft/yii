Registro Activo
===============

Aunque la DAO de Yii puede manejar virtualmente cualquier tarea relacionada
con la base de datos, lo más probable es que gastemos el 90% de nuestro tiempo
escribiendo algunas sentencias SQL relacionadas con la ejecución de las operaciones
CRUD comunes. Es tambien dificil mantener nuestro código cuando éste está mezclado
con sentencias SQL. Para solucionar estos problemas, podemos usar los Registros Activos
(Active Record).

Registro Activo (AR) es una técnica popular de Mapeo Objeto-Relacional (ORM).
Cada clase AR representa una tabla de la base de datos (o vista) cuyos atributos
son representados como las propiedades de la clase AR, y una instancia AR representa
una fila en esa tabla. La operaciones CRUD comunes son implementadas como metodos de
la clase AR. Como resultado, podemos acceder a nuestros datos de una manera más
orientada a objetos. Por ejemplo, podemos usar el siguiente código para insertar
una nueva fila a la tabla `Post`:

~~~
[php]
$post=new Post;
$post->title='post ejemplo';
$post->content='contenido del cuerpor del post';
$post->save();
~~~

A continuación describiremos como configurar un AR y usarlo para ejecutar
las operaciones CRUD. Mostraremos como usar un AR para tratar con relaciones
en la base de datos en la siguiente sección. Por sencillez, usamos la
siguiente tabla de la base de datos para nuestros ejemplo en esta sección.

~~~
[sql]
CREATE TABLE Post (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	createTime INTEGER NOT NULL
);
~~~

> Note|Nota: AR no pretende resolver todas las tareas relacionadas con la
base de datos. Lo mejor es usarlo para modelar tablas de bases de datos en
construcciones PHP y ejecutar consultas que no involucren SQLs complejas.
Para esos escenarios complejos debe usarse el DAO de Yii.

Estableciendo la Conexión con la BD
-----------------------------------

Los AR dependen de una conexión con una BD para ejecutar operaciones relacionadas
con la BD. Por defecto, asumimos que el componente de aplicación `db` nos da la
instancia [CDbConnection] necesaria que nos sirve como la conexión de la BD. La
siguiente configuración de aplicación muestra un ejemplo:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:path/to/dbfile',
			// activar el cacheo de esquema para mejorar el rendimiento
			// 'schemaCachingDuration'=>3600,
		),
	),
);
~~~

> Tip|Consejo: Puesto que AR depende los metadatos de las tablas para
determinar la información de la columna, toma tiempo leer los metadatos
y analizarlos. Si el esquema de tu base de datos es menos probable que
sea cambiado, deberías activar el caché de esquema configurando la
propiedad [CDbConnection::schemaCachingDuration] a un valor mayor que 0.

El soporte para AR está limitado por el DBMS. Actualmente, solo los siguientes
DBMS están soportados:

   - [MySQL 4.1 o superior](http://www.mysql.com)
   - [PostgreSQL 7.3 o superior](http://www.postgres.com)
   - [SQLite 2 y 3](http://www.sqlite.org)

Si querés usar un componente de aplicación diferente de `db`, o si querés
trabajar con múltiples bases de datos usando AR, deberías sobreescribir
[CActiveRecord::getDbConnection()]. La clase [CActiveRecord] es la clase
base para todas las clases AR.

> Tip|Consejo: Existen dos maneras de trabajar con multiples bases de datos
con AR. Si los esquemas de las bases de datos son diferentes, puedes crear
diferentes clases base AR con diferentes implementaciones de [getDbConnection()|CActiveRecord::getDbConnection].
De otra manera, cambiar dinámicamente la variable estática [CActiveRecord::db]
es una mejor idea.

Definiendo la Clase AR
----------------------

Para acceder a una tabla de la base de datos, primero necesitamos definir
una clase AR extendiendo [CActiveRecord]. Cada clase AR representa una
única tabla de la base de datos, y una instancia AR representa una fila en
esa tabla. El siguiente ejemplo muestra el código mínimo necesario para la
clase AR que representa la tabla `Post`.

~~~
[php]
class Post extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
~~~

> Tip|Consejo: Puesto que las clases AR son referencidas frecuentemente
> en varios lugares, podemos importar todo el directorio que contiene las
> clases AR, en vez de incluirlas una a una. Por ejemplo, si todos nuestros
> archivos de clases AR estan bajo `protected/models`, podemos configurar la
> aplicación como sigue:
> ~~~
> [php]
> return array(
> 	'import'=>array(
> 		'application.models.*',
> 	),
> );
> ~~~

Por defecto, el nombre de la clase AR es el mismo que el nombre de la tabla de
la base de datos. Sobreescribir el método [tableName()|CActiveRecord::tableName]
si son diferentes. El método [model()|CActiveRecord::model] está declarado para
cada clase AR (será explicado en breve).

Los valores de las columnas de una fila de la tabla pueden ser accedidos como
propiedades de la correspondiente instancia de la clase AR. Por ejemplo, el
siguiente código establece la columna (atributo) `title`:

~~~
[php]
$post=new Post;
$post->title='un post de ejemplo';
~~~

Aunque nunca declaramos explicitamente la propiedad `title` en la clase
`Post`, podemos aún accederla en el código anterior. Esto es debido a que
`title` es una columna en la tabla `Post`, y [CActiveRecord] la hace
accesible como una propiedad con la ayuda del método mágico de PHP `__get()`.
Será arrojada una excepción si intentamos acceder a una columna no existente
de la misma manera.

> Info|Información: Para una mejor legibilidad, sugerimos nombrar las tablas
de la base de datos y las columnas con las primeras letras de cada palabra distinta
en mayúsculas. En particular, los nombres de tablas estan formados poniendo en
mayúsculas la primera letra de cada palabra y juntándolas sin espacios; los
nombres de las columnas son similares a los de las tablas, excepto que la primer letra
de la primer palabra debe permanecer en minúsculas. Por ejemplo, usamos `Post` como
nombre de la tabla que almacena los posts; y usamos `createTime` para nombrar
a la columna de la clave primaria. Esto hace que las tablas luzcan más como
tipos de clases y las columnas más como variables. Notar, sin embargo, que
usar esta convención puede traer inconvenientes para algunos DBMS como MySQL,
que puede comportarse de forma diferente en diferentes sistemas operativos.

Creando Registros
-----------------

Para insertar una nueva fila en una tabla de la base de datos, creamos una
nueva instancia de la correspondiente clase AR, establecemos sus propiedades
asociadeas con las columnas de la tabla, y llamamos al método
[save()|CActiveRecord::save] para finalizar la inserción.

~~~
[php]
$post=new Post;
$post->title='post ejemplo';
$post->content='contenido del post ejemplo';
$post->createTime=time();
$post->save();
~~~

Si la clave primaria de la tabla se autoincrementa, luego de la inserción
la instancia AR contendrá la clave primaria actualizada. En el ejemplo
anterior, la propiedad `id` reflejará el valor de la clave primaria del
post recien insertado, aún cuando nunca la cambiamos explicitamente.

Si una columna está definida con algún valor estático por defecto (ej.: una
string, un número) en el esquema de la tabla, la propiedad correspondiente en la
instancia AR tendrá automáticamente un valoar luego de crear la instancia.
Una manera de cambiar este valor por defecto es declarando explicitametne la propiedad
en la clase AR:

~~~
[php]
class Post extends CActiveRecord
{
	public $title='por favor ingrese un título';
	......
}

$post=new Post;
echo $post->title;  // esto mostrará: por favor ingrese un título
~~~

Desde la versión 1.0.2, a un atributo se le puede asignar un valor de tipo
[CDbExpression] antes de que el registro sea guardado (tante en la inserción
como en la actualización) en la base de datos. Por ejemplo, para guardar
el timestamp devuelto por la funcion `NOW()` de MySQL, podemos usar el
siguiente código:

~~~
[php]
$post=new Post;
$post->createTime=new CDbExpression('NOW()');
// $post->createTime='NOW()'; no funcionará porque
// 'NOW()' será tratado como una string
$post->save();
~~~


Leyendo Registros
-----------------

Para leer datos en una base de datos, podemos llamar a uno de los métodos
`find` como sigue.

~~~
[php]
// encontrar el primer registro que cumpla la condición especificada
$post=Post::model()->find($condition,$params);
// encontrar la fila con la clave primaria especificada
$post=Post::model()->findByPk($postID,$condition,$params);
// encontrar la fila con los valores de los atributos especificados
$post=Post::model()->findByAttributes($attributes,$condition,$params);
// encontrar la primer fila usando la sentencia SQL especificada
$post=Post::model()->findBySql($sql,$params);
~~~

En lo anterior, llamamos al método `find` con `Post::model()`. Recordemos
que el método estático `model()` es requerido por toda clase AR. El método
devuelve una instancia que es usada para acceder a los métodos de nivel de
clase (algo similar a los métodos de clase estáticos) en un contexto de objetos.

Si el método `find` encuentra una fila que cumpla con las condiciones de la consulta,
devolverá una instancia de `Post` cuyas propiedades contendran los correspondientes
valores de las columnas en la fila de la tabla. Podemos entonces leer los valores
cargados como lo hacemos con las propiedades de objetos normales, por ejemplo,
`echo $post->title;`

El método `find` devolverá `null` si nada puede ser encontrado en la base de datos
con las condiciones de la consulta dada.

Cuando llamammos a `find`, usamos `$condition` y `$params` para especificar
las condiciones de la consulta. Aquí, `$condition` puede ser una string representando
la cláusula `WHERE` en una sentencia SQL, y ``$params` es un arreglo de parámetros
cuyos valores deben ser enlazados a los marcadores de posición en `$condition`.
Por ejemplo,

~~~
[php]
// find the row with postID=10
$post=Post::model()->find('postID=:postID', array(':postID'=>10));
~~~

Podemos tambien usar `$condition` para especificar condiciones de consultas más complejas.
En vez de una strign, dejamos a `$condition` ser una instancia de [CDbCriteria],
que nos permite especificar otras condiciones ademas de la cláusula `WHERE`.
Por ejemplo,

~~~
[php]
$criteria=new CDbCriteria;
$criteria->select='title';  // seleccionar solo la columna 'title'
$criteria->condition='postID=:postID';
$criteria->params=array(':postID'=>10);
$post=Post::model()->find($criteria); // $params no es necesario
~~~

Notar que, cuando usamos [CDbCriteria] como condición de la consulta, el parámetro
`$params` ya no es necesario, puesto que puede ser especificado en [CDbCriteria],
como se muestra arriba.

Una forma alternativa a [CDbCriteria] es pasar un arreglo al método `find`.
Las claves y los valores del arreglo corresponden a las propiedades del criterio y sus
valores respectivamente. El ejemplo anterior puede ser reescrito como sigue,

~~~
[php]
$post=Post::model()->find(array(
	'select'=>'title',
	'condition'=>'postID=:postID',
	'params'=>array(':postID'=>10),
));
~~~

> Info|Información: Cuando una condición de consulta es sobre
que algunas columnas tengan valores específicos, podemos usar
[findByAttributes()|CActiveRecord::findByAttributes]. Dejaremos
al parámetro `$attributes` ser un arreglo de los valores indexados
por los nombres de las columnas. En algunos frameworks, esta tarea
puede ser lograda llamando métodos como `findByNameAndTitle`. Aunque
este enfoque parece atractivo, frecuentemente causa confusión, conflictos
y cuestiones como sensibilidad a mayúsculas/minúsculas de los nombres
de columna.

Cuando múltiples filas de datos coinciden con una condidición de consulta
especificada, podemos traerlas todas juntas usando los siguientes métodos
`findAll`, cada uno de los cuales tiene su método contraparte `find`, que
ya mencionamos anteriormente.

~~~
[php]
// encontrar todas las filas que cumplan la condición especificada
$posts=Post::model()->findAll($condition,$params);
// encontrar todas las filas con la clave primaria especificada
$posts=Post::model()->findAllByPk($postIDs,$condition,$params);
// encontrar todas las filas con los valores de atributos especificados
$posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
// encontrar todas las filas usando la sentencia SQL especificada
$posts=Post::model()->findAllBySql($sql,$params);
~~~

Si nada coincide con la condición de la consulta, `findAll` devolverá un
arreglo vacío. Esto es diferente a `find`, quien devuelve `null` si no se
encuentra cosa alguna.

Además de los métodos `find` y `findAll` descriptos anteriormente, por
conveniencia también se proveen los siguientes métodos:

~~~
[php]
// obtener el número de filas que cumplan la condición especificada
$n=Post::model()->count($condition,$params);
// obtener el número de filas usando la sentencia SQL especificada
$n=Post::model()->countBySql($sql,$params);
// comprobar si existe al menos una fila que cumpla la condición especificada
$exists=Post::model()->exists($condition,$params);
~~~

Actualizando Registros
----------------------

Luego de que una isntancia AR sea rellenada con valores, podemos cambiarlos
y volver a guardarlos en la tabla de la base de datos.

~~~
[php]
$post=Post::model()->findByPk(10);
$post->title='nuevo titulo del post';
$post->save(); // guardar cambios en la base de datos
~~~

Como podemos ver, usamos el mismo método [save()|CActiveRecord::save]
para ejecutar las operaciones de inserción y actualización. Si una instancia
AR es creada usando el operador `new`, llamar a [save()|CActiveRecord::save]
insertará una nueva fila en la tabla de la base de datos; si la instancia AR
es el resultado de la llamada a algún método `find` o `findAll`, llamar a
[save()|CActiveRecord::save] actualizará la fila existente en la tabla. De
hecho, podemos usar [CActiveRecord::isNewRecord] para decir si una instancia
AR es nueva o no.

También es posible actualizar una o varias filas en una tabla de la base de datos
sin cargarlas primero. AR provee los siguientes convenientes métodos de nivel de
clase para este propósito:

~~~
[php]
// actualizar las filas que coincidan con la condición especificada
Post::model()->updateAll($attributes,$condition,$params);
// actualizar las filas que coincidan con la condición especificada y con la(s) clave(s) primaria(s)
Post::model()->updateByPk($pk,$attributes,$condition,$params);
// update counter columns in the rows satisfying the specified conditions
Post::model()->updateCounters($counters,$condition,$params);
~~~

En lo anterior, `$attributes` es un arreglo de valores de columna indexado por nombres de columna;
`$counters` es un arreglo de valores incrementales indexados por nombres de columna;
y `$condition` y `$params` son como se describió en las subsecciones previas.

Borrando Registros
------------------

Podemos también borrar una fila de datos si una instancia AR ha sido rellenada
con esa fila.

~~~
[php]
$post=Post::model()->findByPk(10); // asumiendo que existe un post cuyo ID es 10
$post->delete(); // borra la fila de la tabla de la base de datos
~~~

Nota, luego del borrado, la instancia AR permanece intacta, pero la correspondiente
fila en la tabla de la base de datos ya no está.

Los siguientes métodos de nivel de clase se proveen para borrar filas sin la necesidad
de cargarlas primero:

~~~
[php]
// borra todas las filas que coincidan con la condición especificada
Post::model()->deleteAll($condition,$params);
// borra todas las filas que coincidan con la condición especificada y con la(s) clave(s) primaria(s)
Post::model()->deleteByPk($pk,$condition,$params);
~~~

Validación de Datos
-------------------

Cuando insertamos o actualizamos una fila, frecuentemente necesitamos comprobar
que los valores de las columnas cumplen ciertas reglas. Esto es especialmente
importante si los valores de la columna son provistos por usuarios finales. En
general, nunca debemos confiar en nada que provenga del lado del cliente.

AR ejecuta la validación de datos automáticamente cuando se invoca a
[save()|CActiveRecord::save]. La validación está basada en las reglas
especificadas en el método [rules()|CModel::rules] de la clase AR.
Para más detalles acerca de como especificar reglas de validación, ver la sección
[Declarando Relgas de Validación](/doc/guide/form.model#declaring-validation-rules).
A continuación está el flujo de trabajo típico necesario para guardar un registro:

~~~
[php]
if($post->save())
{
	// los datos son válidos y están insertados/actualizados exitosamente
}
else
{
	// los datos no son válidos. Llamar a  getErrors() para obtener los mensajes de error
}
~~~

Cuando los datos a insertar o actualizar son enviados por usarios finales en un
formulario HTML, necesitamos asignarlos a las correspondientes propiedades AR.
Podemos hacerlo como sigue:

~~~
[php]
$post->title=$_POST['title'];
$post->content=$_POST['content'];
$post->save();
~~~

Si existen muchas columnas, veremos una larga lista de dichas asignaciones.
Esto se puede aliviar haciendo uso de la propiedad [attributes|CActiveRecord::attributes]
como se muestra a continuación. Más detalles pueden ser encontrados en la sección
[Asegurando las Asignaciones de Atributos](/doc/guide/form.model#securing-attribute-assignments)
y en la sección [Creating Action](/doc/guide/form.action).

~~~
[php]
// asumimos que $_POST['Post'] es un arreglo de valores de columna indexados por nombres de columna
$post->attributes=$_POST['Post'];
$post->save();
~~~


Comparando Registros
--------------------

Como las filas de las tablas, las instancias AR están identificadas de manera única
por los valores de su clave primaria. Por lo tanto, para comparar dos instancias AR,
solo es necesario comparar los valores de sus claves primarias, asumiendo que pertenezcan
a la misma clase AR. Sin embargo, una manera más simple es llamar a [CActiveRecord::equals()].

> Info:Información: A diferencia de la implementación de AR en otros frameworks, Yii
soporta claves primaris compuestas en su AR. Una clave primaria consiste de dos o más
columnas. Correspondientemente, en Yii el valor de la clave primaria está representado
como un arreglo. La propiedad [primaryKey|CActiveRecord::primaryKey] nos da el valor de
la clave primaria de una instancia AR.

Personalización
---------------

[CActiveRecord] provee algunos métodos que pueden ser sobreescritos en las clases
que la heredan para personalizar su flujo de trabajo.

   - [beforeValidate|CModel::beforeValidate] y
[afterValidate|CModel::afterValidate]: estos métodos son invocados antes y después
de que la validación sea ejecutada.

   - [beforeSave|CActiveRecord::beforeSave] y
[afterSave|CActiveRecord::afterSave]: estos métodos son invocados antes y después
de que la instancia AR sea guardada.

   - [beforeDelete|CActiveRecord::beforeDelete] y
[afterDelete|CActiveRecord::afterDelete]: estos métodos son invocados antes y después
de que la instancia AR sea borrada.

   - [afterConstruct|CActiveRecord::afterConstruct]: este método es invocado por cada
instancia AR creada usando el operador `new`.

   - [afterFind|CActiveRecord::afterFind]: este método es invocado por cada instancia AR
creada como resultado de una búsqueda.


Usando Transacciones con AR
---------------------------

Cada instancia AR contiene una propiedad llamada [dbConnection|CActiveRecord::dbConnection]
que es una instancia de [CDbConnection]. Por lo tanto podemos utilizar la característica
[transaction](/doc/guide/database.dao#using-transactions) provista por el DAO de Yii
si se desea cuando trabajamos con AR:

~~~
[php]
$model=Post::model();
$transaction=$model->dbConnection->beginTransaction();
try
{
	// encontar y guardad son dos pasos que pueden ser intervenidos por otra solicitud
	// por lo tanto usaremos una transacción para asegurar su consistencia e integridad
	$post=$model->findByPk(10);
	$post->title='nuevo título del post';
	$post->save();
	$transaction->commit();
}
catch(Exception $e)
{
	$transaction->rollBack();
}
~~~

<div class="revision">$Id: database.ar.txt 688 2009-02-17 02:57:56Z freakpol $</div>