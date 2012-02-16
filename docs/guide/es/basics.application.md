Aplicación (Application)
==========

Aplicación (Application) representa la el contexto de ejecución de cada pedido a la 
aplicación. Su principal tarea es resolver el pedido del usuario y dispararlo al 
controlador apropiado para procesamiento futuro. También se utiliza como el lugar 
principal para configuraciones que deben estar en el nivel de aplicación. Por esta 
razón application es también llamado `front-controller` (controlador principal).

Application es creado como un singleton por el [script de entrada](/doc/guide/basics.entry).
El singleton Application puede ser accedido en cualquier lugar 
mediante [Yii::app()|YiiBase::app].


Configuración de Aplicación
---------------------------

Por predeterminado, application es una instancia de [CWebApplication]. 
Para customizarlo normalmente se provee un archivo de configuración (o un arreglo)
para inicializar los valores de sus propiedades cuando la instancia application 
es creada. Una alternativa de personalizar la aplicación es extender [CWebApplication].

La configuración es un arreglo de pares llave-valor (key-value). Cada par representa
el nombre de una propiedad de la instancia de la aplicación y cada valor representa
el valor inicial de la correspondiente propiedad. Por ejemplo, la siguiente configuración
configura las propiedades [name|CApplication::name] y 
[defaultController|CWebApplication::defaultController] de application.

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

Usualmente guardamos la configuración en un archivo de script PHP separado
(ejemplo: `protected/config/main.php`). Dentro del script retornamos el 
arreglo de configuración como a continuación:

~~~
[php]
return array(...);
~~~

Para aplicar estas configuraciones pasamos el nombre del archivo de configuración
como parametro al constructor de application o a [Yii::createWebApplication()]
como en el siguiente ejemplo el cual es usualmente utilizado en el 
[Script de entrada](/doc/guide/basics.entry):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip: Si la configuración de la aplicación es muy compleja, podemos dividirla en 
varios archivos en donde cada uno devuelve una parte del arreglo de configuración.
Para eso, en el archivo de configuración llamamos a la funcion PHP `include()` para
incluir el resto de los archivos de configuración y fusionarlos en un arreglo de 
configuración completo.


Directorio Base de Application
------------------------------

El directorio base de Application refiere a la ruta de directorio que contiene
todos los scripts PHP sensibles de seguridad y datos de la misma.
Por predeterminado es un subdirectorio llamado `protected` que se encuentra
bajo el directorio que contiene el Script de Entrada. Puede ser modificado configurando
la propiedad [basePath|CWebApplication::basePath] en la  
[configuración de application](#application-configuration).

Las cosas que contiene el directorio base deben ser protegidas
para que no sean accesibles por usuarios Web. Con el [Apache HTTP
server](http://httpd.apache.org/) esto se realiza facilmente creando un archivo 
`.htaccess` dentro del directorio base. El contenido del archivo `.htaccess`
debe ser el siguiente:

~~~
deny from all
~~~

Componentes de Application
--------------------------

Las funcionalidades de la aplicación pueden ser facilmente customizadas y enriquecidas
con la arquitectura flexible de componentes. Application administra un juego de 
componentes de aplicación  en los que cada uno implementa características específicas.
Por ejemplo, appliction resuleve un pedido de usuario con la ayuda de los componentes
[CUrlManager] y [CHttpRequest].

Configurando la propiedad [components|CApplication::components] de application, 
podemos personalizar la class y propiedades de cada uno de los componentes utilizados
en application. Por ejemplo podemos configurara el componente [CMemCache] para que 
utilice multiples servers memcache para realizar el cacheo,

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

En el ejemplo anterior agregamos el elemento `cache` en el arreglo `components`.
El elemento cache define que la clase del componente será `CMemCache y 
la propiedad `servers` debe ser inicializada como lo indica.

Para acceder a un componente de application utilice `Yii::app()->ComponentID`,
en donde `ComponentID` indica el ID del componente que desea
(ejemplo: `Yii::app()->cache`).

Un componente de aplicación puede ser deshabilitado mediante su configuración indicando
la propiedad `enabled` con un valor false en su configuración.
En el caso de intentar acceder a un componente deshabilitado, application le devolver Null.

> Tip: Por predeterminado, los componentes de application son creados cuando se necesitan.
Esto quiere decir que los componentes no serán creados si estos no son utilizados durante
el request del usuario. Como resultado de esto, la performance no se vera degradada aún
si la aplicación es configuradad con muchos componentes. Algunos componentes de aplicación 
deben ser creados sin importar si ellos son accedidos  o no. Para esto, liste los IDs en la
propiedad [preload|CApplication::preload] de la aplicación.

Componentes del nucleo de Application
-------------------------------------

Yii predefine un juego de compoenentes de aplicación que proveen caracteristicas comunes 
en toda la aplicación Web. Por ejemplo, el componente [request|CWebApplication::request]
es usado para resolver pedidos de usuarios y proveer de información como URL, cookies.
Configurando las propiedades de estos componentes podemos cambiar el comportamiento de casi
todos los aspectos de Yii.

Abajo se encuentra la lista de componentes predeclarados por [CWebApplication].

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
   administra la publicación de archivos privados.

   - [authManager|CWebApplication::authManager]: [CAuthManager] - 
   Administra el control de acceso basado en roles (role-based access control - RBAC).

   - [cache|CApplication::cache]: [CCache] - provee funcionalidad de cacheo de datos. Nota:
   se debe especificar la clase actual (ejemplo: [CMemCache], [CDbCache]) o Null será
   retornado cuando se acceda a este componente.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
   Administra los scripts de cliente (javascripts y CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
   provee de los mensajes de nucleo traducidos utilizados por Yii framework.

   - [db|CApplication::db]: [CDbConnection] - provee la conexión a la base de datos.
   Nota: debe configurar la propiedad [connectionString|CDbConnection::connectionString] 
   para poder utilizar este componente.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - maneja los errores y 
   excepciones PHP no advertidas.

   - [messages|CApplication::messages]: [CPhpMessageSource] - Provee mensajes traducidos
   utilizados por la aplicación Yii.

   - [request|CWebApplication::request]: [CHttpRequest] - 
   Provee información relacionada con el request.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
   provee servicios relacionados con seguridad como son hashing y encriptación.

   - [session|CWebApplication::session]: [CHttpSession] - 
   provee funcionalidades relacionadas con la sesión.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
   provee métodos globles de persistencia de estado.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - 
   provee funcionalidad para parseo de URL y creación.

   - [user|CWebApplication::user]: [CWebUser] - representa la información
   de identidad del usuario actual.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - maneja temas (themes).


Ciclos de vida de la Aplicación
-------------------------------

Cuando se maneja un un pedido de usuario, la aplicación realizará el siguiente ciclo de vida:

   1. Configurará el autocargado de clases y el manejador de errores;

   2. Registrará los componentes del nucleo de la aplicación;

   3. Cargará la configuración de la aplicación;

   4. Inicializará la aplicación mediante [CApplication::init()]
	   - Carga de compoenentes de aplicación static;

   5. Ejecuta el evento [onBeginRequest|CApplication::onBeginRequest];

   6. Procesa el pedido de usuario:;
	   - Resuelve el pedido de usuario;
	   - Crea el controlador
	   - Ejecuta el controlador;

   7.Ejecuta el evento [onEndRequest|CApplication::onEndRequest];

<div class="revision">$Id: basics.application.txt 626 2009-02-04 20:51:13Z sebathi $</div>