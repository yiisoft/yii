Controlador (Controller)
========================

Un controlador es una instancia de [CController] o una de las clases que lo heredan.
Es creado por la aplicación cuando un usuario realiza un pedido para ese controlador.
Cuando un controlador se ejecuta se realizar el pedido de la acción que utiliza los
modelos necesarios y muestra la información a travez de la vista apropiada. Una acción,
en su forma más simple, es un m;etodo de la clase controlador cuyo nombre comienza con 
`action`.

Un controlador tiene un a acción predeterminada. Cuando el usuario no especifica que acción
se debe ejecutar, esta será la que se ejecute. Por predeterminado la acción default tiene
el nombre de `index`. Puede ser personalizada modificando la configuración 
[CController::defaultAction].

Abajo se encuentra el minimo código de una clase controlador. Dado que este controlador
no tiene ninguna acción definida, pedirle resultará en una excepción.

~~~
[php]
class SiteController extends CController
{
}
~~~


Ruta (Route)
------------

Los controladores y acciones están definidas por IDs. El ID del controlador 
se encuentra en la forma de `path/to/xyz` el cual es interpretado como el archivo de clase 
controlador `protected/controllers/path/to/XyzController.php`, donde `xyz` debe ser remplazada
por el nombre de su controlador (ejemplo: `post` corresponde a 
`protected/controllers/PostController.php`). El ID de acción es el nombre del metodo sin
el prefijo `action`. Por ejemplo si el controlador contiene el método `actionEdit`
el ID de la acción correspondiente será `edit`.

> Note|Nota: Antes de la versión 1.0.3, el formato del id del controlador era `path.to.xyz`
en vez de `path/to/xyz`.

Los usuarios realizan pedidos por un controlador y acción en términos de ruta.
Una ruta se encuentra formada por la concatenación de un ID de controlador y un ID de acción
separados por una barra. Por ejemplo la ruta `post/edit` se refiere a `PostController` y a 
su acción `edit. Por predeterminado la url `http://hostname/index.php?r=post/edit` 
realiza el pedido a el ese contlador y esa acción.

>Note|Nota: Por predeterminado las rutas distinguen mayúsculas de minúsculas. Desde la versión 1.0.1
>es posible utilizar rutas que no distingan mayúsculas de minúsculas modificando en la configuración
>de la aplicación la propiedad [CUrlManager::caseSensitive] en `false`.
>Cuando esta propiedad no está activada, asegurese de seguir las convencion de que
>los directorios que contienen controladores deben ser llamados con minúsculas
>y que ambos, [controller map|CWebApplication::controllerMap]
>y [action map|CController::actions] usan claves en minúsculas.

Desde la versión 1.0.3 una aplicación puede contener [modules](/doc/guide/basics.module). 
La ruta de una acción de controlador dentro de un módulo cumple es de la forma
`moduleID/controllerID/actionID`. Para más información y detalle vea la 
[sección acerca de módulos](/doc/guide/basics.module).


Instanciación de Controlador
----------------------------

Una instancia de controlador es creada cuando [CWebApplication] maneja un 
pedido de usuario. Dado el ID del controlador, la aplicación utilizará las siguientes
reglas para determinar cual es la clase del controlador y cual la ruta al archivo
de clase.

   - Si [CWebApplication::catchAllRequest] se encuentra especificado, el controlador
   será creado basado en esta propiedad y se ignorará el ID de controlador especificado
   por el usuario. Esto es usado mayoritariamente para dejar la aplicación en un modo 
   de mantenimiento y muestre una página con información estática.

   - Si el ID se encuentra en [CWebApplication::controllerMap], la configuración
   de controlador correspondiente se utilizará para crear la instancia del controlador.

   - Si el ID se encuentra en el formato `'path/to/xyz'`, la clase de controlador assumida será
   XyzCOntroller y el archivo de clase correspondiente será 
   `protected/controllers/path/to/XyzController.php`. Por ejemplo si el ID del controlador
   es `admin/user` será resuelto por el controlador `UserController` y el archivo de clase 
   `protected/controllers/admin/UserController.php`. En caso de que el archivo de clase no exista, 
   un error 404 [CHttpException] será lanzado.

En el caso que se utilizen [modules](/doc/guide/basics.module) (disponibles desde la versión 1.0.3), 
El proceso descripto anteriormente es ligeramente diferente. En particular, la aplicación verificará si
el ID refiere a un controlador dentro de un módulo y si esto es así, el módulo será instanciado y luego 
se instanciará el controlador.

Accion (Action)
---------------

Como lo mencionamos anteriormente una acción puede ser definida mediante su nombre
y comenzando con la palabra `action`. Una forma más avanzada de realizar esto es definir
una clase acción y pedirle al controlador que la instancie cuando es requerida. Esto permite que 
las acciones sean reusadas y genera más reusabilidad.

Para definir una nueva clase acción, realice lo siguiente:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// place the action logic here
	}
}
~~~

Para que el controlador sepa que debe utilizar esta acción hacemos override 
del método [actions()|CController::actions] en nuestra clase controlador de 
la siguiente manera:

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

En el ejemplo anterior usamos la ruta alias `application.controllers.post.UpdateAction` 
para especificar que el archivo clase de la acción es 
`protected/controllers/post/UpdateAction.php`.

Escribiendo acciones basados en clases podemos organizar la applicación de manera modular.
Por ejemplo, la siguiente estructura de directorios puede ser utilizada para organizar 
el código de los controladores:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~

Filtros
-------

Los filtros son una pieza de codigo que se configura para ser ejecutada antes y/o
después de que una acción del controlador sea ejecutada. Por ejemplo, un filtro de 
control de acceso puede ser ejecutado para asegurarse de que el usuario ha sido 
autenticado con anterioridad antes de ejecutar cierta acción; un filtro de performance
puede ser utilizado para medir el tiempo que tarda una acción en ejecutarse.

Una acción puede tener múltiples filtros. Los filtros son ejecutados en el orden en el 
que aparecen en la lista de filtros. Un filtro puede prevenir la ejecución de la acción y el 
resto de los filtros de la lista que no han sido ejecutados.

Un filtro puede ser definido como un método en la clase controlador. El nombre del método
debe iniciar con `filter`. Por ejemplo, la existencia de un método `filterAccessControl`
define un filtro llamado llamado `accessControl. El método de filtro debe ser definido de la 
siguiente manera:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// call $filterChain->run() to continue filtering and action execution
}
~~~

en donde `$filterChain` es una instancia de [CFilterChain] que representa la lista de filtro 
asociada con la accion pedida. Dentro del método del filtro podemos llamar a `$filterChain->run()` 
para continuar filtrando la ejecución de la acción.

A su vez, un filtro también puede ser un una instancia de [CFilter] o una clase que la herede.
El siguiente código define una nueva clase filtro:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logic being applied before the action is executed
		return true; // false if the action should not be executed
	}

	protected function postFilter($filterChain)
	{
		// logic being applied after the action is executed
	}
}
~~~

Para aplicar filtro a acciones debemos realizar un override del método
`CController::filters()`. El método debe devolver un arreglo de configuraciónes de filtros.
Por ejemplo,

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

En el código del ejemplo anterior se especifican dos filtros: `postOnly` y 
`PerformanceFilter`. El filtro `postOnly` es un filtro basado en métodos 
(es decir, el filtro es un método predefinido en [CController]); mientras que 
el filtro `PerformanceFilter` especifica que el filtro es basado en clases y su 
archivo de clase filtro es `protected/filters/PerformanceFilter`. 
Usamos un arreglo para configurar el filtro `PerformanceFilter` para inicializar los 
valores de las propiedades del objeto filtro. Aquí la propiedad `unit` del 
`PerformanceFilter` será inicializada como `'second'`.

Utilizando el operador más y menos podemos especificar a qué acciones serán aplicadas
y a cuales nó serán aplicadas el filtro. En el ejemplo anterior, el filtro `postOnly`
debe ser aplicado a las acciones `edit` y `create`, mientras que el filtro 
`PerformanceFilter` debe ser aplicado a todas las acciones excepto a `edit` y `create`.
Si los operadores mas y menos no aparecieran en la configuración del filtro el mismo
se aplicaría a todas las acciones.

<div class="revision">$Id: basics.controller.txt 745 2009-02-25 21:45:42Z sebathi $</div>