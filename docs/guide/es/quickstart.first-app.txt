Creando primera aplicación Yii
==============================

Para ingresar al mundo de Yii, en esta scción le indicamos como crear
nuestra primera aplicación Yii. Usaremos la poderosa herramienta `yiic`
que puede ser utilizadapara automatizar la creación del códgo de ciertas
tareas. Por conveniencia asumimos que `YiiRoot` es el directorio donde Yii 
se encuentra instalado y `WebRoot` es la ruta del documento de tu Web Server.

Ejecute `yiic` en la linea de comandos de la siguiente manera:

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Nota: Cuando ejecuta `yiic` en Mac OS, Linux o Unix, usted deberá 
> modificar los permisos del archivo `yiic` para poder ejecutarlo.
> Alternativamente puede correr la herramienta de la siguiente manera,
>
> ~~~
> % cd WebRoot/testdrive
> % php YiiRoot/framework/yiic.php webapp WebRoot/testdrive
> ~~~

Esto creará una aplicación Yii esqueleto en el directorio
`WebRoot/testdrive`. Esta aplicación contiene la estructura de directorios
requerida por la mayoría de las aplicaciones Yii.

Sin escribir ni una sola linea de código, nosotros podemos probar nuestra
primera aplicación Yii ingresando a la siguiente URL en un explorador Web:

~~~
http://hostname/testdrive/index.php
~~~

Como vemos, la aplicación contiene tres páginas: homepage (la página inicial),
contact (página de contacto) y login (página de login de usuario).
La página inicial muestra información de la aplicación y del estado del usuario logueado,
la página de contacto contiene un formulario para rellenar y enviar sus consultas y
la página de login de usuario permite a los mismos autenticarse para acceder a contenidos
que necesitan privilegios de acceso.
Mire las siguientes pantallas para más detalles.

![Home page](first-app1.png)

![Contact page](first-app2.png)

![Contact page with input errors](first-app3.png)

![Contact page with success](first-app4.png)

![Login page](first-app5.png)


El siguiente diagrama muestra la estructura de directorios de nuestra aplicación.
Por favor mire [Convenciones](/doc/guide/basics.convention#directory) para una explicación
detallada acerca de esta estructura.

~~~
testdrive/
   index.php                 archivo de entrada de la aplicación Web
   assets/                   contiene archivos de recursos públicos
   css/                      contiene archivos CSS
   images/                   contiene archivos de imágenes
   themes/                   contiene temas de la aplicación
   protected/                contiene los archivos protegidos de la aplicación
      yiic                   script de linea de comandos yiic
      yiic.bat               script de linea de comandos yiic para Windows
      commands/              contiene comandos 'yiic' personalizados
         shell/              contiene comandos 'yiic shell' personalizados
      components/            contiene componentes reusables
         MainMenu.php        clase de widget 'MainMenu'
         Identity.php        clase 'Identity' utilizada para autenticación
         views/              contiene los archivos de vistas para los widgets
            mainMenu.php     el archivo vista para el widget 'MainMenu'
      config/                contiene archivos de configuración
         console.php         configuración aplicación consola
         main.php            configuración de la aplicación Web
      controllers/           contiene los archivos de clase de controladores
         SiteController.php  la clase controlador predeterminada
      extensions/            contiene extensiones de terceros
      messages/              contiene mensajes traducidos
      models/                contiene archivos clase de modeloscontaining model class files
         LoginForm.php       el formulario modelo para la acción 'login'
         ContactForm.php     el formulario modelo para la acción 'contact'
      runtime/               contiene archivos temporarios generados
      views/                 contiene archivos de vista de controladores y de diseño
         layouts/            contiene archivos de diseño
            main.php         el diseño default para todas las vistas
         site/               contiene archivos vista para el controlador 'site'
            contact.php      contiene la vista para la acción 'contact'
            index.php        contiene la vista para la acción 'index'
            login.php        contiene la vista para la acción 'login'
         system/             contiene archivos de vista del sistema
~~~

Conectandose a Base de Datos
----------------------------

La mayoría de las aplicaciónes Web utilizan bases de datos. Nuestra aplicación 
test-drive no es una excepción. Para utilizar una base de datos, primero se debe
decir a la aplicación como conectarse a la misma. Esto se realiza modificando el archivo
de configuración de aplicación `WebRoot/testdrive/protected/config/main.php` como se 
muestra a continuación.

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/source.db',
		),
	),
	......
);
~~~

En el ejemplo anterior agregamos la entrada `db` al arreglo de `components` (componentes)
el cual indica a la aplicación que se conecte a la base de datos 
`WebRoot/testdrive/protected/data/source.db` cuando sea necesario.

> Note|Nota: Para utilizar la característica de base de datos de Yii necesitamos habilitar la 
extensión PHP PDO y el driver especifico de la extensión PDO. Para la aplicación test-drive
se necesitará habilitar las extensiones `php_pdo` y `php_pdo_sqlite`.

En este momento tenemos que preparar una base de datos SQLite para que la configuración
anterior sea correcta. Usando alguna herramienta de administración SQLite podemos crear 
la base de datos con la siguiente definición de tablas:

~~~
[sql]
CREATE TABLE User (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

Para simplificar el ejemplo solo creamos la tabla `User` en nuestra base de datos.
El archivo de base de datos SQLite debe ser salvado como
`WebRoot/testdrive/protected/data/source.db`. Nota: tanto el archivo 
como el directorio deben tener permisos de escritura para el proceso de servidor
Web como lo requiere SQLite.

Implementando operaciones CRUD
------------------------------

Ahora comienza la parte divertida. Queremos implementar las operaciones CRUD
para la tabla `User` que acabamos de crear. Esto es una práctica común en 
aplicaciónes prácticas.

En vez de estar lidiando con escribir el codigo actual podemos utilizar la poderosa
herramienta `yiic` nuevamente para automaticar la generación de codigo por nosotros.
Este proceso es tambien conocido como *scaffolding*. Abre una ventana de linea de comandos
y executa los comando listados a continuación:

~~~
% cd WebRoot/testdrive
% protected/yiic shell
Yii Interactive Tool v1.0
Please type 'help' for help. Type 'exit' to quit.
>> model User
   generate User.php

The 'User' class has been successfully created in the following file:
    D:\wwwroot\testdrive\protected\models\User.php

If you have a 'db' database connection, you can test it now with:
    $model=User::model()->find();
    print_r($model);

>> crud User
   generate UserController.php
   generate create.php
      mkdir D:/wwwroot/testdrive/protected/views/user
   generate update.php
   generate list.php
   generate show.php

Crud 'user' has been successfully created. You may access it via:
http://hostname/path/to/index.php?r=user
~~~

En el código anterior utilizamos el comando `yiic shell` para interactuar
con la aplicación esqueleto. Hemos ejecutado dos comandos: `model User` y
`crud User`. El primero genera la clase Modelo para la tabla `User` mientras 
que el segundo lee el modelo `User` y genera el código necesario para las 
operaciones CRUD.

> Note|Nota: Usted se puede encontrar con errores del estilo "...could not 
> find driver", a pesar de que el script de verificación de requerimientos
> le haya indicado que tiene habilitado PDO y el driver PD correspondiente.
> Si esto ocurre puede intentar correr la herramienta `yiic` de la siguiente
> manera:
>
> ~~~
> % php -c path/to/php.ini protected/yiic.php shell
> ~~~
>
> donde `path/to/php.ini` representa el archivo PHP ini correcto.

Vamos a disfrutar de nuestro trabajo navegando a la siguiente URL:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Esto nos mostrará una listado de usuarios que se encuentran como entradas
de la tabla `User`. Como nuestra tabla se encuentra vacía en este momento
no verá ningún dato.

Haga click en el enlace `New User` de la página. Si no estamos logueados
con anterioridad se nos redireccionará a la página de login de usuario.
Luego de loguearse usted verá un formulario de entrada que nos permitirá 
agregar un nuevo usuario a nuestra tabla. Complete el formulario y haga 
click en el botón `Create`. Si tiene algún tipo de error de ingreso, 
un bonito error se le mostrará que previene que grabemos nuestro usuario
hasta que no sea correcto. Volviendo a la lista de usuarios podremos ver
el nuevo usuario agregado en la lista.

Repita el paso anterior para agregar más usuarios. Fijese que la lista de
usuarios contiene paginación automática de los datos de usuario si agrega
muchos para ser mostrados en una sola página.

Si nos logueamos como administrador utilizando `admin/admin` podremos ver
la página de administración en la siguiente URL:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Esto nos mostrará una tabla de entradas de usuarios. Podemos clickear en 
las celdas de los titulos para ordenar los datos de acuerdo a esa columna.
Este cuadro también contiene paginación en caso de que la cantidad de entradas 
de usuarios sea mayor a las que se muestran en una página.

Todas estas bellas características han sido creadas sin que tengamos que
escribir ni una sola linea de código!

![User admin page](first-app6.png)

![Create new user page](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 723 2009-02-21 18:14:05Z sebathi $</div>