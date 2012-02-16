Usando Extensiones
==================

Usar una extensión frecuentemente involucra los siguientes tres pasos:

  1. Descargar la extensión desde el [repositorio de extensiones](http://www.yiiframework.com/extensions/) de Yii.
  2. Desempaquetar la extensión bajo el subdirectorio `extensions/xyz` de
     [directorio base de la aplicación](/doc/guide/basics.application#application-base-directory),
     donde `xyz` es el nombre de la extensión.
  3. Importar, configurar y usar la extensión.

Cada extensión tiene un nombre que la identifica de manera única contra otras extensiones.
Dada una extensión llamada `xyz`, podemos siempre usar el alias de ruta `application.extensions.xyz`
para encontrar el directorio base que la contiene todos los archivos de `xyz`.

Las diferentes extensiones tienen diferentes requerimientos de importación, configuración y uso.
En lo que sigue, listaremos escenarios de uso común de extensiones, de acuerdo a la categorización
descripta en la [introducción](/doc/guide/extension.overview).

Componente de Aplicación
------------------------

Para usar un [componente de aplicación](/doc/guide/basics.application#application-component),
necesitamos primero cambiar la [configuración de la aplicación](/doc/guide/basics.application#application-configuration)
agregando una nueva entrada a la propiedad `components`, como la siguiente:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'application.extensions.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // otras configuraciones de components
    ),
);
~~~

Entonces, podemos acceder al componente en cualquier lugar, usando `Yii::app()->xyz`.
El componente será creado cuando se lo acceda por primera vez a menos que lo listemos
en la propiedad `preload`.

Widget
------

Los [widgets](/doc/guide/basics.view#widget) son principalmente usados en las [vistas](/doc/guide/basics.view).
Dada una clase widget `XyzClass` pertenceciente a la extensión `xyz`, podemos usarla en una vista como sigue:

~~~
[php]
// widget que no necestia contenido del cuerpo
<?php $this->widget('application.extensions.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// widget que puede obtener contenido del cuerpo
<?php $this->beginWidget('application.extensions.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...contenido del cuerpo del widget...

<?php $this->endWidget(); ?>
~~~

Acción
------

Las [acciones](/doc/guide/basics.controller#action) son usadas por un [controlador](/doc/guide/basics.controller)
para responder a una solicitud específica del usuario. Dada una clase acción `XyzClass` perteneciente a la
extensión `xyz`, podemos usarla sobreescribiendo el método [CController::actions] en nuestra clase controlador:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// otras acciones
		);
	}
}
~~~

Entonces, la acción puede ser accedida a través de la [ruta](/doc/guide/basics.controller#route)
`test/xyz`.

Filtro
------

Los [filtros](/doc/guide/basics.controller#filter) son también usados por un
[controlador](/doc/guide/basics.controller). Principalmente pre y post procesan
la solicitud del usuario cuando ésta es manipulada por una [acción](/doc/guide/basics.controller#action).
Dado un filtro de clase `ZyzClass` perteneciente a la extensión `xyz`, podemos
usarlo sobreescribiendo el método [CController::filters] en nuestra clase controlador.

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// otros filtros
		);
	}
}
~~~

En lo anterior, podemos usar los operadores más y menos en el primer elemento del arreglo
para aplicar el filtro a ciertas acciones solamente. Para más detalles, ver la documentación
de [CController].

Controlador
-----------

Un [controlador](/doc/guide/basics.controller) provee un conjunto de acciones que pueden ser
solicitadas por los usuarios. Para usar una extensión controlador, necesitamos configurar la
propiedad [CWebApplication::controllerMap] en la [configuración de la aplicación](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'application.extensions.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// otros controladores
	),
);
~~~

Entonces, una accion `a` en el controlador puede ser accedida
a través de la [ruta](/doc/guide/basics.controller#route) `xyz/a`.

Validador
---------

A validador es principalmente usado en una clase [modelo](/doc/guide/basics.model)
(una que herede tanto de [CFormModel] como de [CActiveRecord]).
Dado un validador de clase `XyzClass` perteneciente a la extensión `xyz`, podemos
usarlo sobreescribiendo el método [CModel::rules] en nuestra clase modelo:

~~~
[php]
class MyModel extends CActiveRecord // o CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// otras reglas de validación
		);
	}
}
~~~

Comando de Consola
------------------

Una extensión de [comando de consola](/doc/guide/topics.console)
usualmente mejora la herramienta `yiic` con un comando adicional.
Dado un comando de consola `XyzClass` perteneciente a la extensión `xyz`,
podemos usarlo configurando la configuración de la aplicación de consola:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'application.extensions.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// otros comandos
	),
);
~~~

Entonces, podemos usar la herramienta `yiic` equipada con un
comando adicional `xyz`.

> Note|Nota: Una aplicación de consola usualmente usa un archivo de
configuración que es diferente del usado por una aplicación web. Si
una aplicación es creada usando el comando `yiic webapp`, entonces el
archivo de configuración para la aplicación de consola `protected/yiic`
es `protected/config/console.php` mientras que el archivo de configuración
para la aplicación Web es `protected/config/main.php`.

Modulo
------

Por favor referirse a la sección acerca de [modulos](/doc/guide/basics.module#using-module)
acerca de como usar un módulo.

Componente Genérico
-------------------

Para usar un [componente](/doc/guide/basics.component) genérico, primero necesitamos
incluir su clase usando

~~~
Yii::import('application.extensions.xyz.XyzClass');
~~~

Entonces, podemos crear una instancia de la clase, configurar sus propiedades,
y llamar a sus métodos. Podemos tambien heredar de ella para crear nuevas clases
hijas.

<div class="revision">$Id: extension.use.txt 750 2009-02-26 02:11:31Z freakpol $</div> 