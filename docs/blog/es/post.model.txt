Personalizando el Modelo Post
=============================

La clase del modelo `Post` generada por la herramienta `Gii` necesita principalmente ser modificada en dos partes:

 - el método `rules()`: especifica las reglas de validación para los atributos del modelo;
 - el método `relations()`: especifica los objetos relacionados;

> Info: Un [modelo](http://www.yiiframework.com/doc/guide/es/basics.model) consiste de una lista de atributos, cada uno asociado a una columna en la correspondiente tabla de la base de datos. Los atributos pueden ser declarados explícitamente como variables de clase o implícitamente sin ninguna declaración.


Personalizando el método `rules()`
----------------------------------

Primero especificamos las reglas de validación que nos aseguran que el los valores del atributo ingresado por los usuarios son correctos antes de guardarlos en la base de datos. Por ejemplo, el atributo `status` de `Post` debe ser un entero(integer) 1, 2 o 3. La herramienta `Gii` también genera reglas de validación para cada modelo. De todas formas, estas reglas se basan en la información de la de la columna de la tabla y pueden no ser apropiadas.

Basados en el análisis de requerimientos, modificamos el método `rules` de la siguiente forma:

~~~
[php]
public function rules()
{
	return array(
		array('title, content, status', 'required'),
		array('title', 'length', 'max'=>128),
		array('status', 'in', 'range'=>array(1,2,3)),
		array('tags', 'match', 'pattern'=>'/^[\w\s,]+$/',
			'message'=>'Tags can only contain word characters.'),
		array('tags', 'normalizeTags'),

		array('title, status', 'safe', 'on'=>'search'),
	);
}
~~~

En éste código, especificamos que los atributos `title`, `content` y `status` son requeridos; el largo de `title` no debe exceder 128; el valor del atributo `status` debe ser 1 (borrador), 2 (publicado) o 3 (archivado); y el atributo `tags` debe contener solamente letras y comas. Además, usamos `normalizeTags` para normalizar las etiquetas ingresadas por los usuarios para que sean únicas y estén bien separadas por comas. La última regla es usada para la función de búsqueda, que describiremos después.
Los validadores como `required`, `length`, `in` y `match` son todos validadores ya definidos por Yii. El validador `normalizeTags` es un validador basado en método, que necesitamos definir en la clase `Post`. Para más información acerca de cómo especificar reglas de validación, por favor consulte [La Guía](http://www.yiiframework.com/doc/guide/es/form.model#declaring-validation-rules).


~~~
[php]
public function normalizeTags($attribute,$params)
{
	$this->tags=Tag::array2string(array_unique(Tag::string2array($this->tags)));
}
~~~

donde `array2string` y `string2array` son ahora dos métodos que necesitamos definir en la clase modelo `Tag`:

~~~
[php]
public static function string2array($tags)
{
	return preg_split('/\s*,\s*/',trim($tags),-1,PREG_SPLIT_NO_EMPTY);
}

public static function array2string($tags)
{
	return implode(', ',$tags);
}
~~~

Las reglas declaradas en el método `rules()` son ejecutadas una por una cuando llamamos al método [validate()|CModel::validate] o [save()|CActiveRecord::save] de la instancia del modelo.

> Note|Nota: Es muy importante recordar que los atributos que aparecen en `rules()` deben ser aquellos cuyos valores van a ser ingresados por usuarios finales. Otros atributos, como `id` y `create_time` en el modelo `Post`, que son configurados por nuestro código en la base de datos, no deben aparecer en `rules()`. Para más detalles, por favor consulte [Asegurando Asignaciones de Atributo](http://www.yiiframework.com/doc/guide/form.model#securing-attribute-assignments).

Luego de hacer estos cambios, podemos visitar la página de creación de posts nuevamente y verificar que las nuevas reglas de validación están funcionando correctamente.


Personalizando el método `relations()`
--------------------------------

En la última personalización, modificamos el método `relations()` para especificar los objetos relacionados de un post. Declarando estos objetos relacionados en `relations()`, podemos aprovechar la potente función de [Relational ActiveRecord (RAR)](http://www.yiiframework.com/doc/guide/database.arr) para acceder a la información de los objetos relacionados de un post, como su autor y comentarios, sin la necesidad de escribir sentencias SQL JOIN complejas.

Personalizamos el método `relations()` de la siguiente manera:

~~~
[php]
public function relations()
{
	return array(
		'author' => array(self::BELONGS_TO, 'User', 'author_id'),
		'comments' => array(self::HAS_MANY, 'Comment', 'post_id',
			'condition'=>'comments.status='.Comment::STATUS_APPROVED,
			'order'=>'comments.create_time DESC'),
		'commentCount' => array(self::STAT, 'Comment', 'post_id',
			'condition'=>'status='.Comment::STATUS_APPROVED),
	);
}
~~~

También introducimos en la clase del modelo `Comment` dos constantes que son usadas en el siguiente método:

~~~
[php]
class Comment extends CActiveRecord
{
	const STATUS_PENDING=1;
	const STATUS_APPROVED=2;
	......
}
~~~

Las relaciones declaradas en `relations()` indican que

 * Un post pertenece a un autor cuya clase es `User` y la relación es establecida en base al valor del atributo `author_id` del post;
 * Un post tiene muchos comentarios cuya clase es `Comment` y la relación es establecida basándose en el valor del atributo `post_id` de los comentarios. Estos comentarios deben ser ordenados de acuerdo a su hora de creación y los comentarios deben ser aprobados.
 * La relación `commentCount` es un poco especial ya que retorna un resultado de agregación que indica cuántos comentarios tiene un post.


Con la declaración de la relación, podemos fácilmente acceder al autor y comentarios de un post de la siguiente forma:

~~~
[php]
$author=$post->author;
echo $author->username;

$comments=$post->comments;
foreach($comments as $comment)
	echo $comment->content;
~~~

Para más detalles sobre cómo declarar y usar relaciones, por favor consultar [La Guía](http://www.yiiframework.com/doc/guide/es/database.arr).

Agregando la propiedad `url`
----------------------------

Un post es un contendio que se encuentra asociado a una URL única para verlo. En vez de llamar [CWebApplication::createUrl] desde cualquier parte de nuestro código para obtener esta URL, podemos agregar una propiedad `url` en el modelo `Post` de forma que el mismo código de creación de URL puede ser reutilizado. Luego cuando describamos cómo mejorar las URLs, vamos a ver que agregar esta propiedad va a ser de gran ayuda.

Para agregar la propiedad `url`, modificamos la clase `Post` agregando un método `get` como sigue:

~~~
[php]
class Post extends CActiveRecord
{
	public function getUrl()
	{
		return Yii::app()->createUrl('post/view', array(
			'id'=>$this->id,
			'title'=>$this->title,
		));
	}
}
~~~

Además del ID del post, agregamos también el título como un parámetro GET en la URL. Esto es principalmente con el propósito de optimización en motores de búsqueda (SEO por sus siglas en inglés), como vamos a describir en [Mejoramiento de URLs](/doc/blog/final.url).

Como [CComponent] es la última clase ancestro de `Post`, agregando el método `getUrl()` nos habilita a usar la expresión como `$post->url`. Cuando accedemos a `$post->url`, el método `get` va a ser ejecutado y su resultado retornado como el valor de la expresión. Para más detalles sobre funciones de este tipo, por favor consulta [La Guía] (/doc/guide/es/basics.component).


Representando el estado en texto
--------------------------------

Ya que el estado de un post es guardado en la base de datos como un entero, necesitamos proveer una representación en texto para que sea más intuitivo cuando sea mostrado a los usuarios finales. En un sistema de gran tamaño, requisitos similares son muy comunes.

Como una solución genérica, usamos la tabla `tbl_lookup` para guardar el equivalente entre los dos valores entero y la representación en texto que son necesitadas por otros objetos de datos. Modificamos la clase del modelo`Lookup` de la siguiente forma para acceder más fácilmente a los datos en texto de la tabla,

~~~
[php]
class Lookup extends CActiveRecord
{
	private static $_items=array();

	public static function items($type)
	{
		if(!isset(self::$_items[$type]))
			self::loadItems($type);
		return self::$_items[$type];
	}

	public static function item($type,$code)
	{
		if(!isset(self::$_items[$type]))
			self::loadItems($type);
		return isset(self::$_items[$type][$code]) ? self::$_items[$type][$code] : false;
	}

	private static function loadItems($type)
	{
		self::$_items[$type]=array();
		$models=self::model()->findAll(array(
			'condition'=>'type=:type',
			'params'=>array(':type'=>$type),
			'order'=>'position',
		));
		foreach($models as $model)
			self::$_items[$type][$model->code]=$model->name;
	}
}
~~~

Nuestro código provee principalmente dos métodos estáticos: `Lookup::items()` y `Lookup::item()`. El primero retorna una lista de palabras pertenecientes al tipo de datos específico, mientras el segundo retorna una palabra particular para el tipo de datos dado y el valor dado.
Nuestra base de datos del blog e pre-poblada con dos tipos de `lookup`: `PostStatus` y `CommentStatus`. El primero hace referencia a los posibles estados de posts mientras que el segundo a los estados de comentarios.

Para hacer nuestro código más fácil de leer, vamos a declarar una serie de constante para representar los valores de estados enteros. Deberíamos utilizar éstas constantes a lo largo de nuestro código cuando hagamos referencia a los valores de estado correspondientes.
~~~
[php]
class Post extends CActiveRecord
{
	const STATUS_DRAFT=1;
	const STATUS_PUBLISHED=2;
	const STATUS_ARCHIVED=3;
	......
}
~~~

De esta forma, podemos llamar a `Lookup::items('PostStatus') para obtener una lista de los posibles estados de post (palabras en texto indexadas por su correspondiente valor entero), y llamar a `Lookup::item('PostStatus', Post::STATUS_PUBLISHED)` para obtener una representación de las palabras de los estados publicados.

<div class="revision">$Id$</div>