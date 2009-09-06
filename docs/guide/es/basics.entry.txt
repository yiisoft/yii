Script de entrada
=================

El script de entrada es el script de inicio y es el que se ocupa de procesar
el pedido del usuario inicialmente. Es el único script PHP que el usuario puede
pedir directamente para ejecutarse.

En la mayoría de los casos, el escript de entrada de una aplicación Yii contiene
un código tán simple como el siguiente,

~~~
[php]
// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// include Yii bootstrap file
require_once('path/to/yii/framework/yii.php');
// create application instance and run
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

Este script incluye el archivo principal de Yii framework `yii.php`, crea la instancia
de aplicación web con la configuración especificada y inicia su ejecución.

Modo Debug
----------

Una aplicación Yii puede correr en modo debug o modo producción según el valor
de la constante `YII_DEBUG`. Por predeterminado el valor de esta constante es `false`
lo que significa modo producción. Para correr su aplicación en modo debug
defina esta constante con el valor `true` antes de incluir el archivo `yii.php`.
Ejecutar aplicaciones en modo debug es menos eficienta ya que debe mantener los logs
internamente. Por otro lado el modo debug es de mucha ayuda durante la etapa de 
desarrollo ya que provee información de debug rica cuando ocurre el error.

<div class="revision">$Id: basics.entry.txt 162 2008-11-05 12:44:08Z sebathi $</div>