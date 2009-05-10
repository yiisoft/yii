Vista (View)
============

Una vista es un script PHP que consiste basicamente en elementos de la interfaz
de usuario (user interface - UI). La misma puede contener expresiones PHP, pero
es recomendable que estas expresiones no modifiquen los datos del modelo y se 
mantengan relativamente simples. Para el mantener la separación de la lógica y 
la presentación se recomienda que la gran parte de la lógica se encuentre
en el modelo y no en la vista.

Una vista tiene el mismo nombre que es utilizada para identificar un archivo 
script de vista cuando se presenta. El nombre de la vista es el mismo que el nombre
del archivo de la vista. Por ejemplo, la vista `edit`se refiere a el archivo
script de vista llamado `edit.php`. Para presentar una vista llame a 
[CController::render()] con el nombre de la vista. Este método buscara la vista
dentro del directorio `protected/views/ControllerID`.

Dentro del script de vista podemos acceder al controlador utilizando `$this`. 
De esta forma podemos pasmar cualquier propiedad del controlador en la vista
evaluando `$this->propertyName`.

También podemos utilizar la siguiente forma de llamado a la función render del
controlador para pasar datos a la vista.

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

En el ejemplo anterior, el método [render()|CController::render] extraera el segundo parametro array
en el script de vista para que lo podamos acceder como variables locales  `$var1` y `$var2`.

Esquema (Layout)
----------------

El esquema o layout es un tipo de vista especial que es utilizado para decorar vistas.
El mismo contiene usualmente porciones de la interfaz de usuario que son comunes a travez de 
muchas vistas. Por ejemplo, el esquema o layout puede contener la porción de header y footer
y embeber dentro el contenido de la vista,

~~~
[php]
......header here......
<?php echo $content; ?>
......footer here......
~~~

en donde `$content` contiene el resultado de la presentación de la vista contenida.

El esquema o layout es aplicado implicitamente cuando se llama a la funcion 
[render()|CController::render]. Por predeterminado, el script de la vista 
`protected/views/layouts/main.php` es utilizado como el esquema. Esto puede ser
personalizado modificando [CWebApplication::layout] o [CController::layout]. 
Para presentar una vista sin aplicarle ningún esquema, llame a la funcion 
[renderPartial()|CController::renderPartial] en vez de la función `render()`.

Widget
------

Un widget es una instancia de [CWidget] o una clase que lo hereda. Es un componente
con proposito presentacional principalmente. Los widgets son usualmente embebidos 
en los scripts de vista para generar interfaces de usuarios complejas y contenidas
en los mismos widgets. Por ejemplo, un widget calendario puede ser utilizado para 
presentar una interfaz de usuario compleja de calendario. Los widgets nos ayudan a
tener mayor reusabilidad de la interfaz de usuario.

Para utilizar un widget realize lo siguiente en un script de vista:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...body content that may be captured by the widget...
<?php $this->endWidget(); ?>
~~~

o

~~~
[php]
<?php $this->widget('path.to.WidgetClass'); ?>
~~~

El segundo se utiliza cuando el widget no necesita ninguno contenido es su cuerpo.

Los widgets pueden ser configurados para customizarse según su comportamiento.
Esto es realizado mediante la configuración de sus valores de propiedades iniciales
cuando se llama al método [CBaseController::beginWidget] o al método
[CBaseController::widget]. Por ejemplo, cuando se utiliza el widget [CMaskedTextField],
se puede identificar que máscara se desea utilizar. Podemos hacerlo pasandole 
un array con los valores de las propiedades incialmente de la siguiente forma, donde
las claves del array son los nombres de las propiedades y los valores del array los
vlores iniciales de las correspondientes propiedades del widget:

~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

Para definir un nuevo widget extienda [CWidget] y sobrecarge los métodos
[init()|CWidget::init] y [run()|CWidget::run]:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// this method is called by CController::beginWidget()
	}

	public function run()
	{
		// this method is called by CController::endWidget()
	}
}
~~~

Como un controlador el widget también puede tener sus propias vistas. Por predeterminado,
los archivos de vista de un widget se encuentran dentro del subdirectorio `views` del 
directorio que contiene el archivo de clase widget. Estas vistas pueden ser presentadas
llamando al método [CWidget::render()], similarmente a como se realiza en un controlador.
La única diferencia es que no se le aplicará ningún esquema o layout a la vista de un widget.

Vistas de sistema
-----------------

Las vistas de sistema es la forma de referirse a las vistas utilizadas por Yii
para mostrar los errores y la informaccion del logueo. Por ejemplo, cuando
un se realiza un pedido de un controlador o una accion inexistente, Yii lanzará una
excepción explicando el error. Yii mostrará el error utilizando la vista del sistema
especifica para el mismo.

Los nombres de las vistas del sistema siguen ciertas reglas. Nombres como 
`errorXXX` refieren a vistas que muestran las CHttpException con código de error 
`XXX`. Por ejemplo, si [CHttpException] es lanzada con el código de error 404, 
la vista `error404` será la que se mostrará.

Yii provee un conjunto de vistas de sistema predeterminados que se pueden localizar en
`framework/views`. Las mismas pueden ser personalizadas creando las vistas con el mismo
nombre de archivo dentro de `protected/views/system`.

<div class="revision">$Id: basics.view.txt 416 2008-12-28 05:28:11Z sebathi $</div>