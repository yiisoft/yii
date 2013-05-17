Convenciones
============

Yii favorece convenciones sobre configuraciones. Siga las convencions y uno podrá
crear aplicaciones Yii sofisticadas sin escribir y administrar configuraciones 
complejas. Obviamente Yii necesitara ser personalizado en casi cada aspecto con
las configuraciones que son necesarias para su aplicación.

Abajo describimos las convenciones que recomendamos para programar en Yii.
Por conveniencia asumimos que `WebRoot` es el directorio en el que se encuentra
instalada la aplicación Yii.

URL
---

Por predeterminado Yii reconoce URLs con el siguiente formato:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

La variable GET `r` refiere a la [ruta](/doc/guide/basics.controller#route) 
que puede ser resuelta por Yii en controlador y acción. Si `ActionID` es omitido
el controlador ejecutará la acción predeterminada (definida via [CController::defaultAction]); 
y si `ControllerID` es omitida (o  la variable `r` ausente), la aplicación usará el controlador
predeterminado (definido via [CWebApplication::defaultController]).

Con la ayuda de [CUrlManager[ es posible crear y reconocer URLs mas amigables SEO como
`http://hostname/ControllerID/ActionID.html`. Esta característica se cubre en detalle
en [Administración URL](/doc/guide/topics.url).

Codigo
------

Yii recomienda nombrar variables, funciones y clases en camel Case lo que significa
poner mayúscula en la primer letra de cada palabra y juntarlas sin espacios. Las variables
y funciones deben tener su primer letra en minúscula y para diferenciarla de los nombres de
las clases (ejemplo: `$basePath`, `runController()`, `LinkPager`). Para miembros de clase
privado es recomendado ponerles de prefijo a sus nombres un guión bajo (underscore `_`)
(ejemplo: `$_actionsList`).

Como los namespace no estan soportados por PHP anteriores a 5.3.0
es recomendado que las clases se llamen de forma única para evitar conflicto
de nombres con clases de terceros. Por esta razón todas las clases de Yii framework
tienen como prefijo la letra "C".

Una regla especial para las clases Controller es que deben finalizar con la palabra
`Controller`. El ID del controlador será definido por el nombre de la clase con su primer
letra en minúscula y la palabra `Controller` truncada del mismo. Por ejemplo la clase 
`PageController` tendra el ID `page`. Esta regla se aplica para hacer más segura la aplicación.
Esta regla también hace que las URLs relacionada con los controladores sean más claras
(ejemplo `/index.php?r=page/index` en vez de `/index.php?r=PageController/index`).

Configuración
-------------

La configuración es un arreglo de llave-valor (key-values). Cada llave (key)
representa el nombre de una propiedod del objeto a configurar mientras que cada
valor corresponde al valor inicial de sus propiedades. Por ejemplo: `array('name'=>'My
application', 'basePath'=>'./protected')` incializa las propiedades `name` y `basePath` 
a sis valores correspondientes del array.

Cualquier propiedad con permisos de escritura de un objeto puede ser configurada.
Si no se configura las propiedades estarán inicializadas en su valor predeterminado.
Cuando configuramos una propiedades recomendable leer la documentación correspondiente
para darle los valores iniciales apropiadamente.

Archivo
--------

Convenciones para el nombramineto y el uso de archivo dependiendo de su tipo.

Archivos de clase deben ser llamados como la clase que contienen. Por ejemplo,
la clase [CController] se encuentra en el archivo `CController.php`. Una clase pública
es una clase que puede ser utilizada por otras clases. Cada archivo de clase debe contener 
al menos una clase pública. Las clases privadas (clases que solo son utilizadas
por una única clase pública) deben residir en el mismo archivo que la clase pública.

Los archivos de vistas deben ser llamados con el nombre de la vista. Por ejemplo, 
la vista `index` debe encontrase en el archivo `index.php`. Un archivo de vista es un 
archivo script PHP que contiene HTML y codigo PHP  principalmente con propositos de
presentación.

Los archivos de configuración puede ser nombrados arbitrariamente. Un archivo de 
configuración es un script PHP con el solo proposito de devolver un arreglo representando
la configuración.

Directorios
-----------

Yii asume un juego default de directorios que es utilizado para cumplir varios propositos.
Cada uno de estos puede ser customizado en caso de necesitarse.

   - `WebRoot/protected`: Este es el [directorio base de 
   aplicación](/doc/guide/basics.application#application-base-directory) el cual contiene
   todos los archivos de scripts PHP y de datos sensibles a la seguridad. Yii crea un alias
   predeterminado llamado `application` asociado con esta ruta. Este directorio y todo lo 
   que se encuentra dentro de el debe ser protejido de poder ser accedido por los usuarios
   Web. Puede ser personalizado via [CWebApplication::basePath].

   - `WebRoot/protected/runtime`: Este direcotiro contiene archivos privados y temporarios
   generados durante el tiempo de ejecución de la aplicación. El proceso de servidor Web
   debe tener acceso de escritura en el mismo. Puede ser personalizado via
   [CApplication::runtimePath].

   - `WebRoot/protected/extensions`: Este directorio contiene todas las extensiones de 
   terceros. Puede ser personalizado via [CApplication::extensionPath].

   - `WebRoot/protected/modules`: Este directorio contiene todos los [módulos](/doc/guide/basics.module)
   de la aplicación cada uno representado por un subdirectorio.


   - `WebRoot/protected/controllers`: este directorio contiene todos los archivos de clase
   controlador. Puede ser personalizado via [CWebApplication::controllerPath].

   - `WebRoot/protected/views`: Este directorio contiene todos los archivos de vista de controladores,
   archivos de vista de esquema (layout) y de sistema (system). Puede ser personalizado 
   via [CWebApplication::viewPath].

   - `WebRoot/protected/views/ControllerID`: Este directorio contiene los archivos de vista
   de un solo controlador. Aquí `ControllerID` se modificará por el ID del controlador
   Puede ser personalizado via [CController::getViewPath].

   - `WebRoot/protected/views/layouts`: Este directorio contiene todos los archivos de vista
   del esquema (layout). Puede ser personalizado via [CWebApplication::layoutPath].

   - `WebRoot/protected/views/system`: Este directorio contiene todos los archivos de vista
   de sistema (system). Los archivos de vista de sistema son templates utilizados para mostrar
   excepciones y errores. Puede ser personalizado via [CWebApplication::systemViewPath].

   - `WebRoot/assets`: este directorio contiene los archivos asset publicados. Un archivo asset es un 
   archivo privado que puede ser publicado para convertirse en accesible para los usuarios Web. Este
   directorio debe tener permisos de escritura habilitados para el proceso de servidor Web. Puede ser
   modificado via [CAssetManager::basePath].

   - `WebRoot/themes`: este directorio contiene varios temas (themes) que pueden ser aplicados a la 
   aplicación. Cada subdirectorio representa a un solo tema (theme) cuyo nombre es el snombre de ese
   subdirectorio. Puede ser customizado via [CThemeManager::basePath].

<div class="revision">$Id: basics.convention.txt 749 2009-02-26 02:11:31Z sebathi $</div>