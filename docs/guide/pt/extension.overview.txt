Visão Geral
===========

Estender o Yii é uma atividade comum durante o desenvolvimento. Por exemplo, 
quando você cria um novo controle, você estende o framework herdando da classe 
[CController]; quando você cria um novo widget, você está estendendo a classe [CWidget] 
ou outro widget existente. Se o código estendido for projetado para a reutilização 
por terceiros, o chamamos de *extensão*.

Uma extensão normalmente atende a um único propósito. No Yii, ela pode ser classificada 
como:

 * [componente da aplicação](/doc/guide/basics.application#application-component)
 * [comportamento](/doc/guide/basics.component#component-behavior)
 * [widget](/doc/guide/basics.view#widget)
 * [controle](/doc/guide/basics.controller)
 * [ação](/doc/guide/basics.controller#action)
 * [filtro](/doc/guide/basics.controller#filter)
 * [comando de console](/doc/guide/topics.console)
 * validador: um validador é uma classe que estende de [CValidator].
 * helper: um helper é uma classe somente com métodos estáticos. São como funções 
 globais, que utilizam o nome da classe como seu namespace.
 * [módulo](/doc/guide/basics.module): um módulo é uma unidade de software independente, 
 que contém [modelos](/doc/guide/basics.model), [visões](/doc/guide/basics.view), 
 [controles](/doc/guide/basics.controller) e outros componentes de suporte. Em 
 diversos aspectos, um módulo lembra uma [aplicação](/doc/guide/basics.application).
 A principal diferença é que um módulo está dentro de uma aplicação. Por exemplo, 
 podemos ter um módulo com funcionalidades para o gerenciamento de usuários.
 
Uma extensão também pode ser um componente que não se encaixe em nenhuma das categorias 
acima. Na verdade, o Yii é cuidadosamente projetado de forma que, praticamente 
todo seu código possa ser estendido e customizado para atender necessidades individuais.

<div class="revision">$Id: extension.overview.txt 1398 2009-09-06 01:15:01Z qiang.xue $</div>
