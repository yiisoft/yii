Conteúdo Dinâmico
=================

Quando utilizamos [caching de fragmentos](/doc/guide/caching.fragment) ou 
[caching de páginas](/doc/guide/caching.page), por diversas vezes nos 
encontramos em uma situação em que todo o conteúdo de uma página é estático, 
exceto em alguns lugares. Por exemplo, uma página de ajuda pode exibir a 
informação de ajuda, estática, com o nome do usuário atualmente logado, no topo.

Para resolver esse problema, podemos variar o cache de acordo com o nome do 
usuário, mas isso seria um desperdício de precioso espaço em cache, uma vez que, 
exceto pelo nome do usuário, todo o conteúdo é o mesmo. Poderíamos também 
dividir a página em vários fragmentos e armazena-los individualmente, mas 
isso tornaria nossa visão mais complexa. Uma técnica melhor é utilizar o 
recurso de *conteúdo dinâmico* fornecido pela classe [CController].

Um conteúdo dinâmico é um fragmento que não deve ser armazenado, mesmo que 
o conteúdo que o contém seja armazenado em cache. Para tornar o conteúdo 
dinâmico para sempre, ele deve ser gerado todas as vezes, mesmo quando o 
conteúdo é servido do cache. Por esse motivo, precisamos que esse conteúdo 
seja gerado por algum método ou função.

Utilizamos os método [CController::renderDynamic()] para inserir o conteúdo 
dinâmico no lugar desejado.

~~~
[php]
...outro conteúdo HTML...
<?php if($this->beginCache($id)) { ?>
...fragmento que deve ser armazenado em cache...
	<?php $this->renderDynamic($callback); ?>
...fragmento que deve ser armazenado em cache...
<?php $this->endCache(); } ?>
...outro conteúdo HTML...
~~~

No exemplo acima, `$callback` é um callback válido em PHP. Ele pode ser uma 
string com o nome de um método na classe do controle ou uma função global. 
Ele também pode ser um vetor indicando uma método na classe. Qualquer parâmetro 
adicional para o método [renderDynamic()|CController::renderDynamic()] será 
passado para o callback. O callback deve retornar o conteúdo dinâmico em vez de
exibi-lo.

<div class="revision">$Id: caching.dynamic.txt 163 2008-11-05 12:51:48Z weizhuo $</div>
