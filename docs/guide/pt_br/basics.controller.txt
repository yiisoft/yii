Controle
==========

Um `controle` é uma instância de [CController] ou uma de suas classes derivadas. 
Ele é criado pela aplicação durante a requisição do usuário. Quando um controle 
entra em execução, ele também executa a ação requisitada, que, geralmente, recupera os modelos 
necessários e exibe a visão apropriada. Uma `ação`, em sua forma mais simples, 
nada mais é do que um método na classe do controle, cujo nome começa com `action`.

Um controle tem uma ação padrão. Quando a requisição do usuário não especifica qual 
ação executar, a ação padrão será utilizada. Por padrão, essa ação é chamada `index`. 
Ela pode ser alterada através da propriedade [CController::defaultAction].

Abaixo temos o código mínimo necessário para uma classe de controle. Uma vez que 
esse controle não define nenhuma ação, qualquer requisição feita para ele irá disparar 
uma exceção.

~~~
[php]
class SiteController extends CController
{
}
~~~


Rota
-----

Controles e ações são identificados por seus IDs. O ID de um controle é representado 
no formato `caminho/para/xyz`, que corresponde ao arquivo de classe do controle em 
`protected/controllers/caminho/para/XyzController.php`, onde o `xyz` deve ser 
substituído pelos nomes dos controles (por exemplo, `post` corresponde a 
`protected/controllers/PostController.php`). O ID de uma ação é o nome de seu método 
sem o prefixo `action`. Por exemplo, se um controle contém o método chamado 
`actionEdit`, o ID da ação correspondente será `edit`.

> Note|Nota: Antes da versão 1.0.3, o formato do ID para os controles era `caminho.para.xyz` 
em vez de `caminho/para/xyz`.

Usuários fazem requisições para uma ação e um controle em particular, por meio um rota. 
Uma rota, é formada concatenando-se o ID de um controle e o ID de uma ação, separados 
por uma barra. Por exemplo, a rota `post/edit` refere-se ao controle `PostController` 
e sua ação `edit`. Por padrão, a URL `http://hostname/index.php?r=post/edit` 
irá requisitar esse controle e essa ação.

> Note|Nota: Por padrão, as rotas fazem diferença entre maiúsculas e minúsculas. 
Desde a versão 1.0.1, é possível eliminar esse comportamento alterando o valor 
da propriedade [CUrlManager::caseSensitive] para false, na configuração da aplicação. 
Configurada dessa forma, tenha certeza de que você irá seguir a convenção de manter 
os nomes dos diretórios onde estão os controles e as chaves dos vetores em 
[controller map|CWebApplication::controllerMap] e [action map|CController::actions] 
em letras minúsculas.

A partir da versão 1.0.3, uma aplicação pode conter [módulos](/doc/guide/basics.module). 
A rota para a ação de um controle dentro de um módulo deve estar no formato `IDmodulo/IDcontrole/IDacao`. 
Para mais detalhes, veja a [seção sobre módulos](/doc/guide/basics.module).


Instanciação do Controle
------------------------

Uma instância do controle é criada no momento em que a [CWebApplication] 
recebe um requisição do usuário. Dado o ID do controle, a aplicação irá utilizar 
as seguintes regras para determinar qual classe deve ser utilizada e onde 
ela está localizada:

   - Se a propriedade [CWebApplication::catchAllRequest] for especificada, o controle 
será criado com base nela, ignorando o ID do controle requisitado pelo usuário. 
Essa propriedade é utilizada principalmente para colocar a aplicação em modo de 
manutenção e exibir uma página estática de notificação.

   - Se o ID for encontrado em [CWebApplication::controllerMap], a configuração 
do controle correspondente será utilizada para instanciar o controle.
   
   - Se o ID estiver no formato `caminho/para/xyz`, será assumido que o nome da 
classe é `XyzController` e seu arquivo correspondente está em 
`protected/controllers/caminho/para/XyzController.php`. Por exemplo, o ID de controle 
`admin/user` será resolvido para a classe `UserController` e seu arquivo correspondente 
será  `protected/controllers/admin/UserController.php`. Se o arquivo não existir, um 
erro 404 [CHttpException] será disparado.

Nos casos em que [módulos](/doc/guide/basics.module) são utilizados (a partir da versão 1.0.3), 
o processo acima é um pouco diferente. Nessa situação em particular, a aplicação irá verificar se o ID 
refere-se a um controle dentro de um módulo e, caso positivo, o módulo será instanciado, primeiro, seguido 
da instanciação do controle.


Ação
----

Como já mencionado, uma ação pode ser definida como um método cujo nome começa 
com a palavra `action`. De uma maneira mais avançada, podemos definir uma ação 
como uma classe, e pedir para que o controle a instancie quando requisitada. Dessa 
forma, as ações podem ser reutilizadas com facilidade.

Para definir uma ação como uma classe, faça como no exemplo a seguir:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// coloque a lógica aqui
	}
}
~~~

Para que o controle tenha conhecimento dessa ação, devemos sobrescrever o método 
[actions()|CController::actions] na classe do controle:

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

No exemplo acima, utilizamos o path alias `application.controllers.post.UpdateAction` 
para especificar que a classe de ação está em `protected/controllers/post/UpdateAction.php`.

Trabalhando com ações baseadas em classes, podemos organizar nossa aplicação de forma 
modular. Por exemplo, a estrutura de diretórios a seguir pode ser utilizada para 
organizar o código para os controles:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~

Filtro
------

Um filtro é uma porção de código, configurada para ser executada antes e/ou depois 
que uma ação de um controle. Por exemplo, o filtro de controle de acesso deve ser 
utilizado para assegurar que o usuário está autenticado, antes de executar a ação 
requisitada; um filtro de desempenho pode ser utilizado para medir o tempo gasto 
para a execução de uma ação.

Uma ação pode ter vários filtros. Os filtros são executados na ordem em que aparecem 
na lista de filtros. Um filtro pode prevenir a execução da ação e dos demais filtros 
ainda não executados.

Um filtro pode ser definido como um método na classe do controle. O nome desse 
método deve começar como `filter`. Por exemplo, o método `filterAccessControl` define 
um filtro chamado `accessControl`. O método do filtro deve ter a seguinte assinatura:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// utilize $filterChain->run() para continuar o processo de filtragem e executar a ação
}
~~~

Acima, `$filterChain` é uma instância da classe [CFilterChain], que representa a lista 
de filtros associados com a ação requisitada. Dentro do método do filtro, podemos 
utilizar `$filterChain->run()` para continuar o processo de filtragem e executar a 
ação.

Um filtro também pode ser uma instância de [CFilter], ou de uma de suas classes derivadas. 
No código abaixo, vemos como definir um filtro como uma classe:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// lógica que será executada antes da ação
		return true; // deve retornar false caso a ação não deva ser executada
	}

	protected function postFilter($filterChain)
	{
		// lógica que será executada depois da ação
	}
}
~~~

Para aplicar os filtros às ações, precisamos sobrescrever o método 
`CController::filters()`. Esse método deve retornar um vetor com as configurações 
dos filtros. Por exemplo:

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

No código acima, especificamos dois filtros: `postOnly` e `PerformanceFilter`.
O filtro `postOnly` é baseado em um método (o método correspondente já está 
definido na classe [CController]); enquanto o filtro `PerformanceFiler` é baseado 
em uma classe. O alias `application.filters.PerformanceFilter` especifíca que a 
classe desse filtro está em `protected/filters/PerformanceFilter`. Utilizamos um 
vetor para configurar o filtro `PerformanceFilter`, assim podemos inicializar devidamente 
os valores de suas propriedades. Nesse caso, a propriedade `unit` dessa classe 
será inicializada com o valor `second`.

Utilizando-se os operadores `+` e `-`, podemos especificar a quais ações os filtros 
devem ou não ser aplicados. No último exemplo, o filtro `postOnly` deverá ser aplicado 
as ações `edit` e `create`, enquanto o `PerformanceFilter` deve ser aplicado a 
todas as ações, EXCETO nas ações `edit` e `create`. Caso nenhum desses operadores 
seja especificado o filtro será aplicado a todas as ações.

<div class="revision">$Id: basics.controller.txt 1264 2009-07-21 19:34:55Z qiang.xue $</div>
