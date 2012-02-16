Lanzamiento de Prueba con Yii
=============================

En ésta sección, se describe cómo crear el esqueleto de una aplicación que nos servirá como punto de partida. Por simplicidad, asumimos que la raíz de nuestro Servidor es `/wwwroot` y que el URL correspondiente es `http://www.example.com/`.

Instalando Yii
--------------

Inicialmente instalamos el framework de Yii. Obtenemos una copia del archivo (versión 1.1.1 o superior) en [www.yiiframework.com](http://www.yiiframework.com/download) y la descomprimimos en el directorio `/wwwroot/yii`. Chequeamos que allí se encuentra un directorio `/wwwroot/yii/framework`.

> Tip|Consejo: El framework de Yii puede instalarse en cualquier lugar del sistema de archivos. Su directorio `framework` contiene todo el código estructural y es el único directorio necesario para implementar una aplicación. Una única instalación de Yii puede ser utilizada por múltiples aplicaciones Yii. 
 
Luego de haber instalado Yii, abrimos una ventana nueva en el explorador y accedemos a la URL `http://www.example.com/yii/requirements/index.php`. Esta página muestra el revisor de requerimientos de la versión de Yii descargada. Para la aplicación de blog, al margen de los requisitos mínimos de Yii, se necesitan habilitar las extensiones PHP `pdo` y `pdo_sqlite` para acceder a las bases de datos SQLite.


Creando el Esqueleto de la Aplicación
-------------------------------------

Luego utilizamos la herramienta `yiic` para crear una aplicación esqueleto bajo el directorio `/wwwroot/blog`. La herramienta `yiic` es una herramienta de línea de comandos incluida en Yii. Puede ser utilizada para generar código para diferentes tareas repetitivas.

Abrimos una ventana de comandos y ejecutamos el siguiente comando:

~~~
% /wwwroot/Yii/framework/yiic webapp /wwwroot/blog
Create a Web application under '/wwwroot/blog'? [Yes|No]y
......
~~~

> Tip|Consejo: Para poder utilizar la herramienta `yiic` tal como se indica, el programa CLI PHP debe estar en el camino (path) de búsqueda de comandos. De lo contrario, se puede utilizar el siguiente commando:
>
>~~~
> path/to/php /wwwroot/Yii/framework/yiic.php webapp /wwwroot/blog
>~~~

Para probar la aplicación que acabamos de crear, abrimos el explorador web y navegamos a la URL `http://example.com/blog/index.php`. Deberíamos ver que nuestra aplicación posee cuatro páginas funcionando: inicio, "Acerca de", contacto y la página para iniciar sesión. 

A continuación, describimos brevemente lo que tenemos en la aplicación esqueleto.


###Script de Entrada

Tenemos un archivo llamado [script de entrada](http://www.yiiframework.com/doc/guide/es/basics.entry) `/wwwroot/blog/index.php` que tiene el siguiente contenido:

~~~
[php]
<?php
$yii='/wwwroot/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
Yii::createWebApplication($config)->run();
~~~

Este es el único script al que los usuarios Web tienen acceso directamente. El script primero incluye el archivo de inicialización de Yii `yii.php`. Luego crea una instancia de la [aplicación](http://www.yiiframework.com/doc/guide/es/basics.application) con la configuración especificada y luego ejecuta la aplicación. 


###Directorio Base de la Aplicación

También tenemos un [directorio base de la aplicación](http://www.yiiframework.com/doc/guide/es/basics.application#application-base-directory) `/wwwroot/blog/protected`. La mayoría de nuestro código y datos estarán ubicados bajo este directorio, y debería estar protegido del accesos de usuarios Web. Para el uso en [Apache httpd Web Server], bajo este directorio creamos un archivo `.htaccess` con el siguiente contenido: 

~~~
deny from all
~~~

Para otros servidores, por favor revisar el manual correspondiente sobre la protección de directorios de usuarios Web. 


Flujo de la Aplicación
----------------------------------

Para facilitar la comprensión del funcionamiento de Yii, describimos el flujo en nuestra aplicación esqueleto cuando un usuario accede a la página de contacto:

 0. El usuario hace una solicitud a la URL `http://www.example.com/blog/index.php?r=site/contact`;
 1. El [script de entrada](http://www.yiiframework.com/doc/guide/es/basics.entry) es ejecutado por el Servidor para procesar la solicitud;
 2. Una instancia de la [aplicación](http://www.yiiframework.com/doc/guide/es/basics.application) es creada y configurada con los valores iniciales especificados en el archivo de configuración de la aplicación `/wwwroot/blog/protected/config/main.php`;
 3. La aplicación resuelve la solicitud en un [controlador](http://www.yiiframework.com/doc/guide/es/basics.controller) y una [acción de controlador](http://www.yiiframework.com/doc/guide/es/basics.controller#action). Para la solicitud de la página de contacto, ésta se resuelve como el controlador de `sitio` (`site`) y la acción de `contacto` (`contact`).
 4. La aplicación crea el controlador `site` en términos de una instancia  `SiteController` y luego la ejecuta; 
 5. La instancia `SiteController` ejecuta la acción `contact` al llamar a su método `actionContact()`;
 6. El método `actionContact` genera una [vista)](http://www.yiiframework.com/doc/guide/es/basics.view) llamada `contact` al usuario Web. Internamente, esto se logra al incluir el archivo de vista `/wwwroot/blog/protected/views/site/contact.php` e incluyendo el resultado en el archivo de [layout](http://www.yiiframework.com/doc/guide/es/basics.view#layout) `/wwwroot/blog/protected/views/layouts/column1.php`.


<div class="revision">$Id$</div>