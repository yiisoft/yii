Modelo-Vista-Controlador (Model-View-Controller MVC)
====================================================

Yii implementa el diseño de patron modelo-vista controlador (model-view-controller MVC)
el cual es adoptado ampliamente en la programación Web. MVC tiene por objeto separar la
lógica del negocio de las consideraciones de la interfaz de usuario para que los desarrolladores
puedan modificar cada parte más fácilmente sin afectar a la otra. En MVC el modelo representa la 
información (los datos) y las reglas del negocio; la vista contiene elementos de la interfaz de 
usuario como textos, formularios de entrada; y el controlador administra la comunicación entre 
la vista y el modelo.

Más alla del MVC, Yii tambien introduce un front-controller llamado aplicación
el cual representa el contexto de ejecución del  procesamiento del pedido.
La aplicación resuelve el pedido del usuario y la dispara al controlador apropiado
para tratamiento futuro.

El siguiente diagrama muestra la estructura estática de una aplicación Yii"

![Estructura estática de aplicación Yii](structure.png)


Un flujo de tareas típico
-------------------------

El siguiente diagrama muestra un típico flujo de tareas de una aplicación Yii cuando
resuelve un pedido de usuario:

![Un típico flujo de tareas de una aplicación Yii](flow.png)

   1. Un usuario realiza un pedido con la siguiente URL `http://www.example.com/index.php?r=post/show&id=1`
   y el servidor Web se  encarga de la solicitud mediante la ejecución del script de arranque en  index.php.
   2. El script de entrada crea una instancia de [applicación](/doc/guide/basics.application)
   y la ejecuta.
   3. La aplicación obtiene la información detallada del pedido del usuario del 
   [componente de aplicación](/doc/guide/basics.application#application-component) `request`.
   4. El controlador determina le [controlador](/doc/guide/basics.controller) y la 
	[acción](/doc/guide/basics.controller#action) pedido con ayuda del componente de aplicación
	llamado `urlManager`. Para este ejemplo el controlador es `post` que refiere a la clase `PostController`
	y la acción es `show` que su significado es determinado por el controlador.
   5. La aplicación crea una instancia del controlador pedido para resolver el pedido del usuario.
   El controlador determina que la acción `show` refiere al nombre de método `actionShow` en la clase
   controlador. Entonces crea y ejecuta los filtros asociados con esta acción (ejemplo: control de acceso,
   benchmarking). La acción es ejecutado si los filtros lo permiten.
   6. La acción lee el [modelo](/doc/guide/basics.model) `Post` cuyo ID es `1` de la base de datos. 
   7. La acción realiza la [vista](/doc/guide/basics.view) llamada `show` con el modelo `Post`
   8. La vista lee y muestra los atributos del modelo `Post`.
   9. La vista ejecuta algunos [widgets](/doc/guide/basics.view#widget).
   10. El resultado realizado es embebido en un [esquema (layout)](/doc/guide/basics.view#layout).
   11. La acción completa la vista realizada y se la muestra al usuario.


<div class="revision">$Id: basics.mvc.txt 419 2008-12-28 05:35:39Z sebathi $</div>