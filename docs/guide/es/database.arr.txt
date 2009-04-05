Registro Activo Relacional
==========================

Ya hemos visto como usar Registro Activo (AR) para seleccionar datos desde
una tabla sencilla de la base de datos. En esta sección, describiremos como
usar AR para unir varias tablas relacionadas de la base de datos y obtener
de vuelta el conjunto de datos unidos.

Para usar AR relacional, se requiere que las relaciones claver primaria-foránea
estén bien definidas entre las tablas que necesitan ser unidas. AR depende de
los metadatos acerca de estas relaciones para determinar como unir las tablas.

> Note|Nota: Comenzando desde la versión 1.0.1, podemos usar AR relacional aún
> si no definimos ninguna clave foránea en nuestra base de datos.

Por sencillez, usamos el esquema de la base de datos mostrado en el siguiente
diagrama entidad-relacion (ER) para ilustrar ejemplos en esta sección.

![Diagrama ER](er.png)

> Info|Información: El soporte para la clave foránea varía en diferentes DBMS.
>
> SQLite no soporta claves foráneas, pero podemos todavía declararlas cuando
> creamos las tablas. AR puede aprovechar estas declataciones para soportar
> correctamente las consultas relacionales.
>
> MySQL soporta claves foráneas con el motor InnoDB, pero no con MyISAM. Es
> recomendable usar InnoDB para nuestra base de datos. Cuando usamos MyISAM,
> podemos aprovechar el siguiente truco para que podamos ejecutar las consultas
> relacionales usando AR:
> ~~~
> [sql]
> CREATE TABLE Foo
> (
>   id INTEGER NOT NULL PRIMARY KEY
> );
> CREATE TABLE bar
> (
>   id INTEGER NOT NULL PRIMARY KEY,
>   fooID INTEGER
>      COMMENT 'CONSTRAINT FOREIGN KEY (fooID) REFERENCES Foo(id)'
> );
> ~~~
> En lo anterior, usamos la palabra clave `COMMENT` para describir la clave foránea
> el cual puede ser leído por AR para reconocer la relación descripta.



Declarando Relaciones
---------------------

Antes de usar AR para ejecutar consultas relacionales, necesitamos darle
conocer a AR como una clase AR se relaciona con otra.

La relación entre dos clases AR está directamente asociada con la relación
entre las tablas de la base de datos representadas por esas clases.
Desde el punto de vista de la base de datos, una relación entre dos tablas
A y B tiene tres tipos: uno-a-muchos (ej.: `User` y `Post`), uno-a-uno (ej.: `User`
y `Profile`) y muchos-a-muchos (ej.: `Category` y `Post`). En AR, hay cuatro
tipo de relaciones:

   - `BELONGS_TO`: si la relación entre la tabla A y B es uno-a-muchos,
entonces B pertenece a A (ej.: `Post` pertenece a `User`);

   - `HAS_MANY`: si la relación entre la tabla A y B es uno-a-muchos,
entonces A tiene muchos B (ej.: `User` tiene muchos `Post`);

   - `HAS_ONE`: este es un caso especial de `HAS_MANY` donde A tiene a lo sumo
un B (ej.: `User` tiene a lo sumo un `Profile`);

   - `MANY_MANY`: corresponde a la relación muchos-a-muchos en la base de datos
Una tabla asociativa es necesaria para romper una relación muchos-a-muchos en
relaciones uno-a-muchos, ya que la mayoría de los DBMS no soportan directamente
la relación muchos-a-muchos. En nuestro esquema de la base de datos de ejemplo,
la tabla `PostCategory` sirve para este propósito. En terminología AR, podemos
explicar `MANY_MANY` como la combinación de `BELONGS_TO` y `HAS_MANY`. Por
ejemplo, `Post` pertenece a muchas `Category` y `Category` tiene muchos `Post`.

Declarar relaciones en AR involucra sobreescribir el método
[relations()|CActiveRecord::relations] de [CActiveRecord]. El método devuelve
un arreglo de configuraciones de relaciones. Cada elemento del arreglo representa
una sola relación con el siguiente formato:

~~~
[php]
'VarName'=>array('RelationType', 'ClassName', 'ForeignKey', ...additional options)
~~~

donde `VarName` es el nombre de la relación; `RelationType` especifica el tipo de
relación, que puede ser una de las cuatro constantes: `self::BELONGS_TO`,
`self::HAS_ONE`, `self::HAS_MANY` y `self::MANY_MANY`; `ClassName` es el nombre de
la clase relacionada a ésta clase AR; y `ForeignKey` especifica la(s) clave(s)
foránea(s) involucrada(s) en la relación. Pueden ser especificadas opciones adicionales
al finad de cada relación (será descripto luego).

El siguiente código muestra como declarar las relaciones para las clases
`User` y `Post`.

~~~
[php]
class Post extends CActiveRecord
{
	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO, 'User', 'authorID'),
			'categories'=>array(self::MANY_MANY, 'Category', 'PostCategory(postID, categoryID)'),
		);
	}
}

class User extends CActiveRecord
{
	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'authorID'),
			'profile'=>array(self::HAS_ONE, 'Profile', 'ownerID'),
		);
	}
}
~~~

> Info|Información: Una clave foránea puede ser conpuesta, cosistiendo de
dos o más columnas. En este caso, debemos concatenar los nombres de la
clave foránea y separarlos con un espacio o coma. Para las relaciones de
tipo `MANY_MANY`, el nombre de la tabla asociativa también debe ser especificado
en la clave foránea. Por ejemplo, la relación `categories` en `Post` está
especificada con la clave foránea `PostCategory(postID, categoryID)`.

La declaración de relaciones en las clases AR implicitamente agrega una propiedad
a la clase por cada relación. Luego de que una consulta relacional es ejecutada,
la correspondiente propiedad será rellenada con la(s) instancia(s) AR relacionada(s).
Por ejemplo, si `$author` representa una instancia AR `User`, podemos usar 
`$author->posts` para acceder a sus instancias `Post` relacionadas.

Ejecutando Consultas Relacionales
---------------------------------

La manera más sencilla de ejecutar consultas relacionales es leer una propiedad
relacional en una instancia AR. Si la propiedad no fue accedida previamente, será
iniciada una consulta relacional, la cual unirá las dos tablas relacionadas y las
filtrará con la clave primaria de la instancia AR actual. El resultado de la consulta
será guardado en la propiedad como instancia(s) de la clase AR relacionada. Esto
se conoce como enfoque *lazy loading*, es decir, la consulta relacional es ejecutada
sólo cuando los objetos relacionados son accedidos por primera vez. El ejemplo
siguiente muestra como usar este enfoque:

~~~
[php]
// recuperar el post cuyo ID es 10
$post=Post::model()->findByPk(10);
// recuperar el autor del post: una consulta relacional se ejecutará aquí
$author=$post->author;
~~~

> Info|Información: Si no hay una instancia relacionada para la relación, la
correspondiente propiedad podría ser `null` o un arreglo vacío. Para las
relaciones `BELONGS_TO` y `HAS_ONE`, el resultado es `null`; para las relaciones
`HAS_MANY` y `MANY_MANY`, el resultado es un arreglo vacío.

El enfoque *lazy loading* es muy conveniente de usar, pero no es eficiente en
algunos escenarios. Por ejemplo, si queremos acceder a la información del autor
para `N` posts, usar el enfoque *lazy loading* podría involucrar ejecutar `N`
consultas de unión. Bajo estas circunstancias debemos recurrir al enfoque
llamado *eager loading*.

El enfoque *eager loading* recupera las instancias AR relacionadas junto con la(s)
instancia(s) AR principal(es). Esto se logra mediante el uso del método
[with()|CActiveRecord::with] junto con uno de los métodos
[find|CActiveRecord::find] o [findAll|CActiveRecord::findAll] de AR. Por ejemplo,

~~~
[php]
$posts=Post::model()->with('author')->findAll();
~~~

El código anterior devolverá un arreglo de instancias `Post`. A diferencia del
enfoque *lazy loading*, la propiedad `author` en cada `Post` ya está rellenada
con la instancia `User` relacionada antes de acceder a la propiedad. En vez de
ejecutar una consulta de join por cada post, el enfoque *eager loading* traerá
todos los posts juntos con sus autores en una sola consulta de union!

Podemos especificar multiples nombres de relación en el método [with()|CActiveRecord::with]
y el enfoque *eager loading* los traerá de una sola vez. Por ejemplo, el siguiente código
traerá los posts juntos con sus autores y sus categorías:

~~~
[php]
$posts=Post::model()->with('author','categories')->findAll();
~~~

Podemos también anidar los *eager loading*. En vez de una lista de nombres
de relación, pasamos una representación jerárquica de nombres de relación al
método [with()|CActiveRecord::with], como la siguiente,

~~~
[php]
$posts=Post::model()->with(
	'author.profile',
	'author.posts',
	'categories')->findAll();
~~~

Lo anterior nos traerá todos los posts con sus autores y categorías. También traerá
cada perfil de autor y sus posts.

> Note|Nota: El uso del método [with()|CActiveRecord::with] ha sido cambiado desde la
> versión 1.0.2. Por favor lee la correspondiente documentación de la API cuidadosamente.

LA implementación de AR en Yii es muy eficiente. Cuando se usa *eager loading* en una
jerarquía de objetos relacionados que involucran `N` relaciones `HAS_MANY` o `MANY_MANY`,
tomará `N+1` consultas SQL para obtener el resultado necesario. Esto significa que en el
ejemplo anterior necesita tres consultas SQL debido a las propiedades `posts` y `categories`.
Otros frameworks toman un enfoque más radical usando solo una consulta SQL. A primera vista
el enfoque radical parece ser más eficiente porque menos consultas son analizadas y ejecutadas
por el DBMS. Esto es de hecho poco práctico en la realidad por dos razones: Primero, existen
muchos datos repetitivos en el resultado que toman tiempo extra para transmitir y procesar;
Segundo, el número de filas en el resultado crece exponencialmente con el número de tablas
involucradas, lo que las hace simplemente inmanejable cuanto más relaciones estén involucradas.

Desde la versión 1.0.2, podemos además obligar a la consulta relacional a ser hecha con sólo
con una consulta SQL. Simplemente agregamos una llamada a [together()|CActiveFinder::together]
luego de [with()|CActiveRecord::with]. Por ejemplo,

~~~
[php]
$posts=Post::model()->with(
	'author.profile',
	'author.posts',
	'categories')->together()->findAll();
~~~

La consulta anterior será hecha en una sola consulta SQL. Sin llamar a 
[together|CActiveFinder::together], serán necesarias dos consultas SQL:
una junta las tablas `Post`, `User` y `Profile`, y la otra junta las
tablas `User` y `Post`.

Opciones de la Consulta Relacional
----------------------------------

Mencionamos que las opciones adicionales pueden ser especificadas en la
declaración de la relación. Estas opciones, especificadas como pares
nombre-valor, son usadas para personalizar la consulta relacional. 

We mentioned that additional options can be specified in relationship
declaration. These options, specified as name-value pairs, are used to
customize the relational query. Se resumen a continuación:

   - `select`: una lista de columnas a ser seleccionadad para la clase AR
relacionada. Por defecto es `'*'`, lo que significa todas las columnas. Los
nombres de las columnas deben ser desambiguados usando `aliasToken` si aparecen
en una expresión (ej.: `COUNT(??.name) AS nameCount`).

   - `condition`: la cláusula `WHERE`. Por defecto vacía. Notar que las referencias
a las columnas deber ser desambiguadas usando `aliasToken` (ej.: `??.id=10`).

   - `params`: los parámetros a ser enlazados en la sentencia SQL generada. Éstos
deben ser dados como un arreglo de pares nombre-valor. Esta opción está disponible
desde la versión 1.0.3.

   - `on`: la cláusula `ON`. La condición especificada aquí será agregada a la
condición de union (del join) usando el operador `AND`. Esta opción está disponible
desde la versión 1.0.2.

   - `order`: la cláusula `ORDER BY`. Por defecto vacía. Notar que las referencias
a las columnas deber ser desambiguadas usando `aliasToken` (ej.: `??.age DESC`).

   - `with`: una lisata de objetos relacionados que deben ser cargados junto con
este objeto. Note, this is only honored by lazy loading, not eager loading. 

   - `joinType`: tipo de union (join) para esta relación. Por defecto es
`LEFT OUTER JOIN`.

   - `aliasToken`: el marcador profijo de columna. Será reemplazado por el alias
de la tabla para desambiguar las referencias a las columnas. Por defecto es `'??.'`.

   - `alias`: el alias de la tabla asociada con esta relación. Esta opcion está disponible
desde la versión 1.0.1. Por defecto en `null`, lo que significa que el alias de la tabla
es generado automáticamente. Difiere de `aliasToken` en que `aliasToken` es sólo un
marcador de posición y será reemplazado por el alias real de la tabla.

   - `together`: si la tabla asociada con esta relación debe ser forzada a unirse junto con la
tabla primaria. Esta opcion sólo tiene sentido para relaciones `HAS_MANY` y `MANY_MANY`. Si
esta opción no esta establecida en falso, cada relación `HAS_MANY` o `MANY_MANY` terndra su
propia sentencia `JOIN` para mejorar el desempeño. Esta opción está disponible desde la versión
1.0.3

Además, las siguientes opciones están disponibles para ciertas relación durante el *lazy loading*:

   - `group`: la cláusula `GROUP BY`. Por defecto vacía. Notar que las referencias a las columnas
deben ser desambiguadas usando `aliasToken` (ej.: `??.age`). Esta opción solo se aplica a relaciones
`HAS_MANY` y `MANY_MANY`.

   - `having`: la cláusula `HAVING`. Por defecto vacía. Notar que las referencias a las columnas
deben ser desambiguadas usando `aliasToken` (ej.: `??.age`). Esta opción solo se aplica a relaciones
`HAS_MANY` y `MANY_MANY`. Nota: esta opción está disponible desde la versión 1.0.1.

   - `limit`: el límite de las filas a ser seleccionadas. Esta opción NO se aplica a la relación `BELONGS_TO`.

   - `offset`: desplazamiento de las filas a ser seleccionadas.  Esta opción NO se aplica a la relación `BELONGS_TO`.

A continuación modificamos la declaración de la relación `posts` en `User` incluyendo algunas de las opción anteriores:

~~~
[php]
class User extends CActiveRecord
{
	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'authorID'
							'order'=>'??.createTime DESC',
							'with'=>'categories'),
			'profile'=>array(self::HAS_ONE, 'Profile', 'ownerID'),
		);
	}
}
~~~

Ahora si accedemos a `$author->posts`, obtendremos los posts del autor
ordenados de acuerdo a su hora de creación en orden descendiente. Cada
instancia post también tiene cargadas sus categorías.

> Info|Información: Cuando un nombre de columna aparece en dos o más tablas
a ser unidas, es necesario desambiguarlas. Esto se hace poniendo como prefijo
el nombre de la tabla al nombre de la columa. Por ejemplo, `id` se vuelve
`Team.id`. En las consultas relacionales de AR, sin embargo, no tenemos esta
libertad puesto que las sentencias SQL son generadas automáticamente por AR,
que, sistemáticamente, le da a cada tabla un alias. Por lo tanto, para evitar
conflictos con los nombres de columnas, debemos usar un marcador de posición
para indicar la existencia de una columna que necesita ser desambiguada. AR
reemplazará el marcador con un alias de tabla adecuado y desambiguar la columna
correctamente.

Opciones Dinámicas de Consultas Relacionales
--------------------------------------------

Comenzando desde la versión 1.0.2, podemos usar opciones dinámicas de consultas
relacionales tanto en [with()|CActiveRecord::with] como en la opcion `with`. Las
opciónes dinámicas sobreescribirán las opciones existentes como se especifica
en el método [relations()|CActiveRecord::relations]. Por ejemplo, con el anterior
modelo `User`, si queremos usar el enfoque *eager loading* para traer de vuelta
los posts pertenecientes al autor en órden ascendiente (la opción `order` en la
especificación de la relación es órden descendiente), podemos hacer lo siguiente:

~~~
[php]
User::model()->with(array(
	'posts'=>array('order'=>'??.createTime ASC'),
	'profile',
))->findAll();
~~~

<div class="revision">$Id: database.arr.txt 6834 2009-02-16 05:20:17Z freakpol $</div> 