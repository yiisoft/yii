Caching
=======

Caching é uma maneira rápida e efetiva de aumentar a performances de aplicações 
Web. Ao armazenar em cache dados relativamente estáticos, e utiliza-lo quando 
esses dados forem requisitados, economizamos o tempo necessário para gerar esses 
dados.

A utilização de cache no Yii consiste em configurar e acessar um componente de cache. 
A configuração de aplicação exibida a seguir, especifica um componente de cache que 
utiliza memcache com dois servidores:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'system.caching.CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
);
~~~

Quando a aplicação está em execução, o componente pode ser acessado via:
`Yii::app()->cache`.

O Yii fornece diversos componentes de cache que podem armazenar dados 
em diferentes meios. Por exemplo, o componente [CMemCache] encapsula a 
extensão memcache do PHP e utiliza a memória como meio para armazenar os dados; 
o componente [CApcCache] encapsula a extensão APC; e o componente [CDbCache] 
armazena os dados do cache em um banco de dados. Abaixo temos um resumo dos 
componentes de cache disponíveis:

   - [CMemCache]: utiliza a extensão PHP [memcache](http://www.php.net/manual/en/book.memcache.php).
   
   - [CApcCache]: utiliza a extensão PHP [APC](http://www.php.net/manual/en/book.apc.php).
   
   - [CXCache]: utiliza a extensão PHP [XCache](http://xcache.lighttpd.net/).
Nota: esse componente está disponível a partir da versão 1.0.1.

   - [CEAcceleratorCache]: utiliza a extensão PHP [EAccelerator](http://eaccelerator.net/).

   - [CDbCache]: utiliza uma tabela no banco de dados para armazenar os dados. 
Por padrão, ele irá criar e utilizar um banco de dados SQLite3 no diretório runtime de sua aplicação. 
Você pode especifcar explicitamente um banco de dados por meio da propriedade [connectionID|CDbCache::connectionID].

   - [CZendDataCache]: utiliza o Zend Data Cache como meio de armazenamento.
Nota: esse componente está disponível a partir da versão 1.0.4.

   - [CFileCache]: utiliza arquivos para armazenar os dados em cache. Esse método 
é particularmente útil para armazenar grandes quantidades de dados em cache (por exemplo, 
páginas).
Nota: esse componente está disponível a partir da versão 1.0.6.

   - [CDummyCache]: é um cache falso, que na realidade não realiza caching algum. 
A finalidade deste componente é simplificar o desenvolvimento de código que precisa 
trabalhar com dados em cache. Por exemplo, durante o desenvolvimento ou quando 
o servidor atual não tem suporte a cache, podemos utilizar esse componente. Assim, 
quando o suporte a cache estiver disponível basta apenas trocar o componente. 
Em ambos os casos, podemos utilizar utilizar o `Yii::app()->cache->get($key)` 
para recuperar dados do cache, sem se preocupar se `Yii::app()->cache` é `null`. 
Este componente está disponível a partir da versão 1.0.5;

> Tip|Dica: Como todos os componentes de cache são derivados da classe [CCache],
você pode alterar entre diversos tipos de cache sem modificar o código que os 
utilizam.

O Caching pode ser utilizado em diferentes níveis. No mais baixo nível, podemos 
utilizar cache para armazenar pequenos dados, tal como uma variável. Chamamos isso 
de *data caching* (Caching de dados). No próximo nível, podemos utiliza-lo para 
armazenar fragmentos de uma página que é gerada por uma visão. No nível mais alto, 
armazenamos toda a página no cache e a servimos de la, quando necessário.

Nas próximas subseções, falaremos mais sobre como utilizar o cache nesses níveis.

> Note|Nota: Por definição, o cache é um meio de armazenamento volátil. Ele não 
garante a existência dos dados em cache, mesmo que eles não expirem. Portanto, 
não utilize cache como um meio de armazenamento persistente (por exemplo, não o 
utilize para armazenar dados da sessão.

<div class="revision">$Id: caching.overview.txt 1315 2009-08-09 04:07:35Z qiang.xue $</div>
