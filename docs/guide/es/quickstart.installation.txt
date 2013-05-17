Instalación
===========

Para instalar Yii solo debe seguir los siguientes 2 pasos:

   1. Descargar el framework Yii de [yiiframework.com](http://www.yiiframework.com/).
   2. Descomprimir el archivo a un directorio accesible por el servicio Web.

> Tip: Yii no necesita ser instalado en un directorio accesible via web. La aplicacion
Yii tiene un script de entrada la cual usualmente es el único archivo que debe ser expuesto
a los usuarios Web. Otros scripts PHP , incluidos los de Yii, pueden (y se recomienda)
estar protegidos del acceso Web ya que esos pueden intentar ser explotado para Hackeo.

Requerimiento
-------------

Luego de instalar Yii, ustede puede verificar si su server satisface todos
los requerimientos para utilizar Yii. Para hacerlo debe hacer accesible
el script de verificación de requerimientos para utilizar Yii. Usted puede
acceder al script de verificación de requerimientos en la siguiente URL en
un explorador Web:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

El requerimiento mínimo de Yii es que su server soporte PHP 5.1.0 o superior.
Yii ha sido testeado con  [Apache HTTP server](http://httpd.apache.org/) en 
los sistemas operativos Windows y Linux. También puede funcionar en otras 
plataformas que soporten PHP 5.

<div class="revision">$Id: quickstart.installation.txt 359 2008-12-14 19:50:41Z sebathi $</div>