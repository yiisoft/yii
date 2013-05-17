Registro de Errores
===================

Una aplicación Web en producción por lo general necesita un mecanismo de registro de errores sofisticado para varios eventos. En nuestra aplicación, quisiéramos registrar los errores que suceden cuando está siendo usada. Estos errores pueden ser errores de programación o mal uso del sistema por parte de los usuarios. Registrar estos errores va a ayudar a mejorar la Aplicación de Blog.
Podemos habilitar el registro de errores modificando la [configuración de la aplicación](http://www.yiiframework.com/doc/guide/es/basics.application#application-configuration) como sigue,

~~~
[php]
return array(
	'preload'=>array('log'),

	......

	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
		......
	),
);
~~~

Con esta configuración, si sucede un error o notificación, información detallada va a ser registrada y salvada en un archivo ubicado en el directorio `/wwwroot/blog/protected/runtime`.

El componente `log` ofrece otras funciones avanzadas, como enviar mensajes de error a una lista de direcciones de correo electrónico, mostrando los mensajes registrados en una ventana de consola JavaScript, etc. Para más detalles, por favor consulta [La Guía](http://www.yiiframework.com/doc/guide/es/topics.logging).


<div class="revision">$Id$</div>