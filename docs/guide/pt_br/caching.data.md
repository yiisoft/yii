Data Caching (Caching de Dados)
===============================

O caching de dados consiste em armazenar e recuperar variáveis PHP no cache. 
Para essa finalidade, a classe base [CCache] fornece dois métodos que são 
utilizados na maioria dos casos: [set()|CCache::set] e [get()|CCache::get].

Para armazenar a variável `$value` em cache, escolhemos um ID único e 
utilizamos o método [set()|CCache::set] para armazena-lo:

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

O dado armazenado ficará em cache para sempre, a não ser que ele seja eliminado 
por alguma regra de caching (por exemplo, o espaço para caching esteja cheio e, 
então, os dados mais velhos são removidos). Para alterar esse comportamento, 
ao executar o método [set()|CCache::set], podemos especificar também um parâmetro 
de tempo de expiração, de forma a indicar que o dado deve ser removido do cache 
depois de um certo período de tempo.

~~~
[php]
// Mantém o valor no cache por 30 segundos
Yii::app()->cache->set($id, $value, 30);
~~~

Mais tarde, quando precisarmos acessar essa variável (nessa requisição, ou em outras) 
utilizamos o método [get()|CCache::get] informando o ID da variável desejada. 
Se o valor retornado for false, significa que a variável não está disponível no cache, 
e devemos armazena-la novamente.

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
	// re-armazena $value porque seu valor não foi encontrado
	// Yii::app()->cache->set($id,$value);
}
~~~

Ao escolher o ID para variável a ser armazenada, tenha certeza de que ele seja único 
entre todos os outros utilizados pelas demais variáveis em cache na aplicação. NÃO é 
necessário que o ID seja único entre aplicações diferentes, uma vez que o componente 
de cache é inteligente o suficiente para diferenciar IDs de aplicações diferentes. 

Alguns sistemas de cache, tais como MemCache e APC, suportam a recuperação em lote 
de vários valores em cache, podendo reduzir a sobrecarga involvida nesse processo. A 
partir da versão 1.0.8, um novo método chamado [mget()|CCache::mget] explora essa 
funcionalidade. Caso o sistema de cache utilizado não suporte este recurso, o 
método [mget()|CCache::mget] irá simula-lo.

Para remover uma variável do cache, utilizamos o método [delete()|CCache::delete]. 
Para remover tudo do cache, utilizamos o método [flush()|CCache::flush]. Tenha muito 
cuidado ao utilizar o método [flush()|CCache::flush] porque ele também irá remover 
dados de outras aplicações.

> Tip|Dica: Como a classe [CCache] implementa `ArrayAccess`, um componente de cache 
> pode ser utilizado como um vetor. Abaixo, alguns exemplos:
> ~~~
> [php]
> $cache=Yii::app()->cache;
> $cache['var1']=$value1;  // equivalente a: $cache->set('var1',$value1);
> $value2=$cache['var2'];  // equivalente a: $value2=$cache->get('var2');
> ~~~

Dependências do Cache
---------------------

Além do tempo de expiração, os dados em cache podem ser invalidados de acordo 
com a alteração de algumas dependências. Por exemplo, se estamos fazendo cache do 
conteúdo de uma arquivo e ele é alterado, devemos invalidar o cópia em cache 
e recuperar o conteúdo atualizado diretamente do arquivo, em vez do cache.

Representamos uma dependência como uma instância de [CCacheDependency], ou uma 
de suas classes derivadas. Passamos essa instância, juntamente com os dados que 
devem ser armazenados, para o método [set()|CCache::set].

~~~
[php]
// value irá expirar em 30 segundos
// ela também deverá se tornar inválida se o conteúdo de 'FileName' for alterado
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('FileName'));
~~~

Agora, ao recuperar a variável `$value` do cache, com o método [get()|CCache::get], 
a dependência será verificada e, se o arquivo foi alterado, retornará um false, 
indicando que a informação precisa ser re-armazenada.

Abaixo temos um resumo com todas as dependências do cache:

   - [CFileCacheDependency]: a dependência é alterada caso a hora de última 
modificação do arquivo tenha sido alterada.

   - [CDirectoryCacheDependency]: a dependência é alterada se qualquer um dos 
arquivos ou subdiretórios do diretório informado sofrer alterações.

   - [CDbCacheDependency]: a dependência é alterada se o resultado da consulta 
informada sofre alterações.

   - [CGlobalStateCacheDependency]: a dependência é alterada se o valor do 
estado global informado for alterado. Um estado global é uma variável que é 
persistente entre múltiplas requisições e sessões de uma aplicação. Ele é 
definido através do método [CApplication::setGlobalState()].

   - [CChainedCacheDependency]: a dependência é alterada se qualquer uma das 
dependências na cadeia informada sofrer alteração.

   - [CExpressionDependency]: a dependência é alterada se o resultado da expressão 
PHP informada for alterado. Essa classe está disponível a partir da versão 1.0.4.

<div class="revision">$Id: caching.data.txt 1855 2010-03-04 22:42:32Z qiang.xue $</div>
