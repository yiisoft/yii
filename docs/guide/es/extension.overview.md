Descripción
===========

Extender Yii es una actividad común durante la etapa de desarrollo. Por ejemplo,
cuando escribimos un controlador nuevo, extendemos a Yii heredando de la clase
[CController]; cuando escribimos un nuevo widget, extendemos [CWidget] o alguna
clase widget existente. Si el código extendido es diseñado para ser reusado por
terceros, podemos llamarlo *extensión*.

Una extensión usualmente sirve para un propósito sencillo. En terminología Yii,
puede ser clasificada como sigue,

 * [componente de aplicación](/doc/guide/basics.application#application-component)
 * [widget](/doc/guide/basics.view#widget)
 * [controlador](/doc/guide/basics.controller)
 * [acción](/doc/guide/basics.controller#action)
 * [filtro](/doc/guide/basics.controller#filter)
 * [comando de consola](/doc/guide/topics.console)
 * validador: un validador es un componente que extiende la clase [CValidator].
 * helper: un helper (asistente) es una clase con sólo métodos estáticos. Es como funciones globales
que usan el nombre de la clase como su namespace.
 * [módulo](/doc/guide/basics.module): un módulo es una unidad de software auto-contenido que consiste de
   [modelos](/doc/guide/basics.model), [vistas](/doc/guide/basics.view), [controladores](/doc/guide/basics.controller)
y otros componentes de soporte. En muchos aspectos, un módulo se asemeja a una [aplicación](/doc/guide/basics.application).
La diferencia principal es que un módulo está dentro de una aplicación. Por ejemplo, podemos tener un módulo que
provee funcionalidades para el manejo de usuarios.

Una extensión puede también ser un componente que no cae en ninguna de las categorías anteriores.
De hecho, Yii está cuidadosamente diseñado de tal manera que casi todas las piezas de código pueden
ser extendidas y personalizadas para satisfacer las necesidades individuales.

<div class="revision">$Id: extension.overview.txt 760 2009-02-26 21:23:53Z freakpol $</div> 