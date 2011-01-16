Componente
==========

As aplicações feitas com o Yii são construídas por componentes, que são objetos 
desenvolvidos para um fim específico. Um componente é uma instância da 
classe [CComponent], ou uma de suas classes derivadas. A utilização 
de um componente basicamente envolve o acesso à suas propriedades e 
a execução/manipulação de seus eventos. A classe base [CComponent] 
especifica como definir propriedades e eventos.

Propriedade de um Componente
----------------------------

Uma propriedade de um componente é como uma variável membro pública de um objeto. 
Nós podemos ler seu conteúdo ou lhe atribuir novos valores. Por exemplo:

~~~
[php]
$width=$component->textWidth;     // acessa a propriedade textWidth
$component->enableCaching=true;   // altera a propriedade enableCaching
~~~

Para definir uma propriedade em um componente, podemos simplesmente declarar 
uma variável membro pública na classe do componente. No entanto, existe uma 
maneira mais flexível, que consiste em definir métodos acessores 
(getters e setters), como no exemplo a seguir:

~~~
[php]
public function getTextWidth()
{
    return $this->_textWidth;
}

public function setTextWidth($value)
{
    $this->_textWidth=$value;
}
~~~

No código acima, definimos uma variável alterável chamada `textWidth` 
(o nome é case-insensitive, não faz diferença entre maiúsculas e minúsculas). 
Ao acessar a propriedade, o método `getTextWidth()` é executado e seu valor 
de retorno é o valor da propriedade. De maneira parecida, ao alterar o valor 
da propriedade, utilizamos o método `setTextWidth()`. Se o método setter não 
não for definido, a propriedade será do tipo somente leitura e a tentativa de 
alterar seu valor lançará uma exceção.

>Note|Nota: Existe uma pequena diferença entre uma propriedade definida via 
métodos acessores (getters e setters) e variáveis membros de classe. No primeiro 
caso, o nome da variável é case-insensitive, enquanto que, no último, o nome é
case-sensitive (há diferença entre maiúsculas de minúsculas).

Eventos de um Componente
------------------------

Os eventos de um componente são propriedades especiais que aceitam métodos 
(chamados de `event handlers`, manipuladores de eventos) como seus valores. 
Ao atribuir um método a um evento, fará com que ele seja executado cada vez 
que o evento for disparado. Portanto, o comportamento de um componente pode ser 
alterado para funcionar de maneira diferente de como foi desenvolvido.

Um evento pode ser criado definindo-se um método cujo nome inicie com `on`. 
Assim como as propriedades definidas via métodos acessores, os nomes de eventos 
também são case-insensitive. O código abaixo define um evento chamado `onClicked`:

~~~
[php]
public function onClicked($event)
{
	$this->raiseEvent('onClicked', $event);
}
~~~

No exemplo acima, `$event` é uma instância da classe [CEvent], ou de uma de 
suas classes derivadas, e está representando o parâmetro do evento.

Podemos atribuir um método para esse evento da seguinte maneira:

~~~
[php]
$component->onClicked=$callback;
~~~

Onde `$callback` refere-se a um callback válido em PHP. Ele pode ser uma função 
global ou um método de classe. No último caso, o callback deve ser passado como 
um vetor no formato: `array($objeto,'nomeDoMetodo')`.

A assinatura de um manipulador de evento deve ser a seguinte: 

~~~
[php]
function nomeDoMetodo($event)
{
    ......
}
~~~

Onde `$event` é o parâmetro descrevendo o evento ocorrido (originado na chamada 
do método `raiseEvent()`). O parâmetro `$event` é uma instância da classe [CEvent], 
ou uma de suas classes derivadas, e, no mínimo, ele contém a informação sobre quem 
originou o evento.

A partir da versão 1.0.10, um manipulador de eventos também pode ser uma função
anônima, suportada a partir do php 5.3. Por exemplo:

~~~
[php]
$component->onClicked=function($event) {
	......
}
~~~

Agora, se executarmos o método `onClicked()`, o evento `onClicked` será disparado 
e o manipulador de evento a ele atribuído será invocado automaticamente.

Um evento pode ter diversos manipuladores. Quando o evento é disparado, 
os manipuladores serão executados, um a um, na ordem em que foram atribuídos ao 
evento. Um manipulador pode impedir que os manipuladores restantes sejam executados, 
para isso altere o valor de [$event->handled|CEvent::handled] para `true`.

Comportamento de um Componente
------------------------------

A partir da versão 1.0.2, os componentes passaram a suportar [mixin](http://en.wikipedia.org/wiki/Mixin), 
e, portanto, ganharam a possibilidade de receber um ou mais comportamentos. Um *comportamento* (behavior) 
é um objeto cujo os métodos podem ser "herdados" pela classe que o anexou, com a finalidade de 
coleta de funcionalidades em vez de especialização (por exemplo, a herança normal de classes). 
Um componente pode receber diversos comportamentos e, assim, utilizar a "herança múltipla".

Classes de comportamento devem implementar a interface [IBehavior]. A maioria dos comportamentos 
podem utilizar a classe [CBehavior] como base, estendendo-a. Se um comportamento precisar ser atribuído 
a um [modelo](/doc/guide/basics.model), ele deve estender as classes [CModelBehavior] ou 
[CActiveRecordBehavior], que implementam características específicas para modelos.

Para utilizar um comportamento, primeiro ele deve ser atribuído a um componente, utilizando o método
[attach()|IBehavior::attach]. Em seguida, podemos executar o comportamento através do componente, da 
seguinte maneira:

~~~
[php]
// $nome identifica únicamente o comportamento dento do componente
$componente->attachBehavior($nome, $comportamento);
// test() é um método de $comportamento
$componente->test();
~~~

Um comportamento atribuído pode ser acessado normalmente, como uma propriedade do componente. 
Por exemplo, se um comportamento denominado `tree` é atribuído a um componente, podemos obter 
obter uma referência dele da seguinte maneira:

~~~
[php]
$behavior=$component->tree;
// equivalente a:
// $behavior=$component->asa('tree');
~~~

Podemos desabilitar um comportamento temporariamente, para que seus métodos não estejam disponíveis 
através do componente:

~~~
[php]
$component->disableBehavior($name);
// a linha abaixo irá gerar uma exceção
$component->test();
$component->enableBehavior($name);
// agora funciona
$component->test();
~~~

É possível que dois comportamentos atribuídos ao mesmo componente tenham métodos com o mesmo nome. 
Nesse caso, o método do comportamento atribuído primeiro terá precedência.

Os comportamentos são ainda mais poderosos quando utilizado com [eventos](/doc/guide/basics.component#component-event). Um
comportamento, quando atribuído a um componente, pode utilizar alguns de seus métodos como callbacks 
para os eventos do componente. Dessa forma, o comportamento tem a possibilidade de observar ou alterar 
o fluxo de execução do componente.

<div class="revision">$Id: basics.component.txt 2346 2010-08-28 13:12:27Z mdomba $</div>
