Caching de Páginas
==================

O caching de páginas é aquele que armazena o conteúdo de uma página inteira. 
Ele pode ocorrer em diferentes lugares. Por exemplo, ao ajustar corretamente 
os cabeçalhos HTTP para uma página, o navegador do cliente pode armazenar em seu 
cache o conteúdo por um tempo limitado. A aplicação web também pode armazenar 
o conteúdo da pagina em cache. Nessa subseção, veremos como fazer.

O cache de páginas pode ser considerado um caso especial de 
[caching de fragmentos](/doc/guide/caching.fragment). Como o conteúdo de uma 
página geralmente é gerado aplicando-se um layout a uma visão, o cache não irá 
funcionar apenas utilizando os métodos [beginCache()|CBaseController::beginCache] e
[endCache()|CBaseController::endCache] no layout. A razão para isso é que o layout 
é aplicado dentro do método [CController::render()] DEPOIS que o conteúdo da 
visão foi gerado.

Para armazenar a página inteira, devemos pular a execução da ação que 
gera o conteúdo da página. Para isso, podemos utilizar [COutputCache] como um 
[filtro](/doc/guide/basics.controller#filter). O código abaixo mostra 
como configuramos o filtro para o cache:

~~~
[php]
public function filters()
{
	return array(
		array(
			'COutputCache',
			'duration'=>100,
			'varyByParam'=>array('id'),
		),
	);
}
~~~

A configuração acima, faz com que o filtro seja aplicado a todas as ações do 
controle. Podemos limita-lo para uma ou algumas ações utilizando o operador `+`. 
Mais detalhes podem ser encontrados em [filtro](/doc/guide/basics.controller#filter).

> Tip|Dica: Podemos utilizar [COutputCache] como um filtro porque ela estende a 
classe [CFilterWidget], o que faz com que ela seja tanto um widget quanto um filtro. 
De fato, o jeito como um widget funciona é bastante similar a um filtro: um widget 
(filtro) é iniciado antes que conteúdo por ele delimitado (ação) seja gerado, e termina 
depois que seu conteúdo delimitado (ação) foi gerado.

<div class="revision">$Id: caching.page.txt 1014 2009-05-10 12:25:55Z qiang.xue $</div>
