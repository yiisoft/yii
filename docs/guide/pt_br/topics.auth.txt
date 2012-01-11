Autenticação e Autorização
==========================

Autenticação e autorização são necessárias para uma página Web que deve
ser limitada a determinados usuários. *Autenticação* serve para verificar
se alguém é quem diz ser. Isto geralmente envolve a utilização de um usuário e  
senha, mas pode incluir outros métodos de validação da identidade, como
cartão inteligente (smart card), impressões digitais, etc. *Autorização* serve para descobrir se uma
pessoa, uma vez identificada (e autenticada), tem permissão para manipular
recursos específicos. Isto geralmente é utilizado para descobrir se esta
pessoa está em uma regra específica que possui acesso aos recursos.

O Yii tem embutido um framework de autenticação/autorização (auth) que é
fácil de usar e pode ser customizado para necessidades específicas.

A parte central no framework auth do Yii é a pré-declaração do *componente
de aplicação do usuário* que é um objeto implementando a interface
[IWebUser]. O componente de usuário representa a persistência da informação
de identidade para o usuário corrente. Pode ser acessado de qualquer lugar, utilizando
`Yii::app()->user`.

Utilizando o componente de usuário, podemos verificar se um usuário está conectado ou não através de
[CWebUser::isGuest]; podemos [conectar|CWebUser::login] e 
[desconectar|CWebUser::logout] um usuário; podemos verificar se um usuário pode executar
operações específicas, chamando [CWebUser::checkAccess]; e podemos também
obter o [identificador único|CWebUser::name] e outras informações armazenadas
sobre a identidade do usuário.

Definindo a Classe de Identidade
--------------------------------

Como mencionado acima, autenticação é como validar a identidade do usuário. A autenticação em uma aplição Web típica, geralmente é realizada pela combinação de usuário e senha, para verificar a identidade do usuário. Entretanto, pode ser incluído outros métodos e implementações diferentes podem ser necessárias. Para prover os diferentes métodos de autenticação, o framework auth do Yii introduz a classe de identidade.

Podemos definir uma classe de identidade que contenha a autenticação lógica atual. A classe de identidade deve implementar a interface [IUserIdentity]. Diferentes classes de identidade podem ser
implementadas para diferentes abordagens de autenticação (por exemplo, OpenID, LDAP, Twitter OAuth, Facebook Connect). Um bom começo quando escrevermos nossa própria implementação é extender a classe [CUserIdentity] que é uma classe básica para o modelo de autenticação utilizando usuário e senha.

O principal trabalho na definição da classe de identidade é a implementação do
método [IUserIdentity::authenticate]. Este método é utilizando para encapsular os principal detalhes da abordagem da autenticação. Uma classe de identidade também pode declarar
informações adicionais de identidade que precisam ser armazenadas durante a sessão do 
usuário.

#### Um Exemplo

No exemplo a seguir, utilizamos uma classe de identidade para demonstrar a abordagem de autenticação para banco de dados. Isto é muito comum em aplicações Web. O usuário informa um usuário e senha em um formulário de login, e então validamos a credencial utilizando [ActiveRecord](/doc/guide/database.ar), comparando em uma tabela de usuário no banco de dados. Há algumas coisas sendo demonstradas neste simples exemplo:

1. A implementação do método `authenticate()` para utilizar banco de dados para validação das credenciais.
2. Sobreescrita do método `CUserIdentity::getId()` para retornar a propriedade `_id`, porque na implementação padrão retorna o nome de usuário como ID.
3. Utilização do método `setState()` ([CBaseUserIdentity::setState]) para demonstrar o armazenamento de outras informações que podem ser facilmente recuperadas em requisições posteriores.

~~~
[php]
class UserIdentity extends CUserIdentity
{
	private $_id;
	public function authenticate()
	{
		$record=User::model()->findByAttributes(array('username'=>$this->username));
		if($record===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($record->password!==md5($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$record->id;
			$this->setState('title', $record->title);
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
    }

	public function getId()
	{
		return $this->_id;
	}
}
~~~

Quando falarmos sobre login e logout na próxima seção, veremos que passamos a classe identidade como parâmetro ao método login de um usuário. Qualquer informação que precisarmos armazenar no estado (apenas chamando [CBaseUserIdentity::setState])) serão passadas para a classe [CWebUser], que por sua vez irá armazenar de forma persistente, como uma sessão.
Esta informação pode ser acessada como uma propriedade da classe [CWebUser]. No nosso exemplo, armazenamos a informação do título do usuário através de `$this->setState('title', $record->title);`. Uma vez finalizado o processo de login, podemos obter a informação `title` do usuário corrente, simplesmente usando `Yii::app()->user->title`.

> Info: Por padrão, [CWebUser] utiliza sessão para armazenamento persistente de informação 
sobre identidade de usuário. Caso o login estiver ativo e baseado na utilização de cookies (a configuração
[CWebUser::allowAutoLogin] for verdadeira), a informação de identidade do usuário também 
será salva como um cookie. Tenha certeza de não declarar informações sensíveis
(por exemplo, senha).

Login e Logout
--------------

Agora que já vimos um exemplo de criação de identidade de usuário, usaremos isto para ajudar a avaliar a implementação das ações de login e logout que necessitamos. O código a seguir demonstra como isto é feito:

~~~
[php]
// Login de um usuário com usuário e senha fornecidos.
$identity=new UserIdentity($username,$password);
if($identity->authenticate())
	Yii::app()->user->login($identity);
else
	echo $identity->errorMessage;
......
// Logout do usuário corrente
Yii::app()->user->logout();
~~~

Estamos criando um novo objeto de UserIdentity e passando as credenciais de autenticação (ou seja, os valores de `$username` e `$password` enviados pelo usuário) para o construtor. Então simplesmente chamamos o método `authenticate()`. Se bem sucedido, passamos a informação de identidade no método [CWebUser::login], e armazenamos a informação de identidade em um armazenamento persistente (Por padrão, sessão do PHP) para recuperação em uma requisição subsequente. Caso a autenticação falhe, podemos verificar a propriedade `errorMessage` para obter mais informações sobre a falha.

Seja autenticado ou não, um usuário pode ser verificado em qualquer parte do aplicativo usando `Yii::app()->user->isGuest`. Se usarmos armazenamento persistente como sessão (o padrão) e/ou cookie (discutiremos abaixo) para armazenar a informação de identidade, o usuário pode ficar conectado e responder as requisições subsequentes. Neste caso, não precisamos utilizar a classe UserIdentity e todo o processo de login em casa solicitação. Por sua vez a classe CWebUser irá cuidar automaticamente do carregamento das informações de identidade do armazenamento persistente e podemos utilizá-lo para determinar se `Yii::app()->user->isGuest` retorna verdadeiro (true) ou falso (false).

Login baseado em Cookie
-----------------------

Por padrão, um usuário será desconectado após determinado período de inatividade,
que depende da [configuração da sessão](http://www.php.net/manual/en/session.configuration.php).
Para alterar este comportamento, podemos setar a propriedade [allowAutoLogin|CWebUser::allowAutoLogin]
do componente de usuário para verdadeiro (true) e passar o parâmetro duração para
o método [CWebUser::login]. O usuário permanecerá conectado para a
duração especificada mesmo que ele feche a janela do navegador. Note que
esta funcionalidade requer que o navegador do usuário aceite cookies.

~~~
[php]
// Manter o usuário conectado por 7 dias.
// Tenha certeza de que allowAutoLogin está setado como verdadeiro (true) no componente de usuário.
Yii::app()->user->login($identity,3600*24*7);
~~~

Conforme mencionado acima, quando o login baseado em cookie estiver habilitado, os estados
armazenados através de [CBaseUserIdentity::setState] serão gravados no cookie.
Na próxima vez que o usuário conectado entrar, estes estados serão lidos do
cookie e preparados para serem acessados através de `Yii::app()->user`.

Embora o Yii possua medidas para previnir que o estado do cookie sejam alterados
no lado do cliente, sugerimos fortemente que informações sensíveis de seguranção não sejam
armazenadas como estados. Em vez disso, estas informações devem ser restauradas no lado
do servidor através da leitura de algum armazenamento persistente (por exemplo, banco de dados).

Além disso, para qualquer aplicação Web séria, recomendamos a utilização das estratégias
a seguir para aumentar a segurança de login baseado em cookie.

* Quando um usuário efetua login com sucesso através de um formulário de login, geramos e
armazenamos uma chave aleatória tando no estado do cookie quanto no armazenamento persistente do lado do servidor
(por exemplo, banco de dados).

* Mediante a uma requisição subsequente, quando a autenticação do usuário estiver sendo realizada via informação de cookie, podemos comparar as duas cópias
da chave aleatória e assegurar uma validação anterior a entrada do usuário.

* Se o usuário realizar login através de formulário novamente, a chave precisa ser regerada.

Utilizando a estratégia acima, eliminamos a possibilidade que determinado usuário reutilize um
estado antigo de um cookie, que possa conter informações de estado desatualizadas.

Para implementar a estratégia acima, precisamos sobreescrever os dois métodos a seguir:

* [CUserIdentity::authenticate()]: nele é onde a autenticação real é realizada.
Se o usuário é autenticado, podemos regerar uma chave aletória e armazená-la
na base de dados, bem como no estado da identidade com [CBaseUserIdentity::setState].

* [CWebUser::beforeLogin()]: nele é realizado a chamada quando o usuário será sendo conectado.
Devemos verificar se a chave obtida do estado do cookie é a mesma que 
a do banco de dados.

Filtro de Controle de Acesso (Access Control Filter)
---------------------

Filtro de controle de acesso é um esquema de autorização preliminar que verifica se
o usuário atual pode realizar a ação solicitada. A
autorização é baseada no nome do usuário, endereço de IP do cliente e tipos de requisição.
É fornecido como um filtro chamado
["accessControl"|CController::filterAccessControl].

> Tip|Dica: Filtros de controle de acesso são suficientes para cenários simples. Para
controle de acesso mais complexo você pode usar o controle de acesso baseado em papéis de usuários (role-based access (RBAC)).

Para controlar o acesso a ações em um controller nós instalamos o filtro de controle
por sobrepor [CController::filters] (veja [Filter](/doc/guide/basics.controller#filter)) para mais detalhes sobre a instalação).

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
}
~~~

Acima especificamos que o [filtro de controle de acesso|CController::filterAccessControl]
deve ser aplicado a todas as
ações de `PostController`. As regras detalhadas de autorização usadas pelo
filtro são especificadas por sobrepor [CController::accessRules] na
classe controller.

~~~
[php]
class PostController extends CController
{
	......
	public function accessRules()
	{
		return array(
			array('deny',
				'actions'=>array('create', 'edit'),
				'users'=>array('?'),
			),
			array('allow',
				'actions'=>array('delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'actions'=>array('delete'),
				'users'=>array('*'),
			),
		);
	}
}
~~~

O código acima especifica três regras, cada uma representada por um array.
O primeiro elemento do array é um `'allow'` ou `'deny'` e o segundo 
é formado por pares do tipo nome-valor que especificam o padrão dos parâmetros da regra. As regras definidas acima são interpretadas como se segue: as ações `create` e `edit` não podem ser executadas por usuários anônimos (não identificados);
a ação `delete` pode ser executada por usuários com o papel `admin`;
e a ação `delete` não pode ser executada por ninguém.

As regras de acesso são avaliadas uma a uma na ordem em que foram especificadas.
A primeira regra que bater com o padrão atual (isto é, nome de usuário, papéis,
IP do cliente, endereço) determina o resultado da autorização. Se esta regra for um `allow`
a ação pode ser executada. Se for um `deny` a ação não pode ser executada;
E se nenhuma das regras bater, a ação também poderá ser executada.

> Tip|Dica: Para garantir que uma ação não seja executada sob certos contextos,
> é benéfico sempre especificar uma regra pega-tudo do tipo `deny` no final
> do conjunto de regras, como no exemplo:
> ~~~
> [php]
> return array(
>     // ... outras regras...
>     // a seguinte regra nega a ação 'delete' em todos os contextos
>     array('deny',
>         'actions'=>array('delete'),
>     ),
> );
> ~~~
> Sem esta regra, se nenhuma das regras batesse em algum contexto, a ação `delete` ainda seria executada.


Uma regra de acesso pode bater nos seguintes contextos:

   - [actions|CAccessRule::actions]: especifica com quais ações esta regra bate.
Deve ser um array de IDs de ações. A comparação não diferencia maiúsculas/minúsculas (case-insensitive).

   - [controllers|CAccessRule::controllers]: especifica com quais controllers esta regra bate.
Deve ser um array de IDs de ações. A comparação não diferencia maiúsculas/minúsculas (case-insensitive).

   - [users|CAccessRule::users]: especifica com quais usuários esta regra bate.
O [nome de usuário|CWebUser::name] é usado para a comparação. A comparação não diferencia maiúsculas/minúsculas (case-insensitive).
Três caracteres especiais podem ser usados aqui:

     - `*`: qualquer usuário, inclusive anônimos e autenticados.
	   - `?`: usuários anônimos.
     - `@`: usuários autenticados.

   - [roles|CAccessRule::roles]: especifica com quais papéis esta regra bate.
Isto faz uso do [controle de acesso baseado em papéis](/doc/guide/topics.auth#role-based-access-control),
característica que será descrita na próxima sub-seção. Em resumo, a regra é aplicada
se [CWebUser::checkAccess] retornar true para um dos papéis.
Note que você deve usar principalmente papéis em uma regra `allow` por que, por definição,
um papel representa uma permissão de fazer algo. Note também que, embora usemos o termo 'papéis'
aqui, seu valor pode realmente ser qualquer item de autenticação, incluindo papéis,
tarefas e operações.

   - [ips|CAccessRule::ips]: especifica com quais endereços de IP de clientes esta regra bate.
   - [verbs|CAccessRule::verbs]: especifica com quais tipos de requisição (exemplo: `GET`,
`POST`) esta regra bate. A comparação não diferencia maiúsculas/minúsculas (case-insensitive).
   - [expression|CAccessRule::expression]: especifica uma expressão PHP cujo valor indica se
esta regra bate. Na expressão você pode a variável `$user` que se refere a `Yii::app()->user`.


Manipulando o Resultado da Autorização
-----------------------------

Quando a autorização falha, ou seja, o usuário não tem permissão de realizar a ação
especificada, um dos dois cenários a seguir pode ocorrer:

   - Se o usuário não estiver logado e se a propriedade [loginUrl|CWebUser::loginUrl]
do componente User estiver configurada para ser a URL da página de login, o navegador
vai ser redirecionado para essa página. Note que, por padrão, 
[loginUrl|CWebUser::loginUrl] aponta para a página `site/login`.

   - Caso contrário, uma exceção HTTP com código de erro 403 vai ser exibida.

Ao configurar a propriedade [loginUrl|CWebUser::loginUrl], pode-se fornecer uma
URL relativa ou absoluta. Pode-se também fornecer um array que vai ser usado
para gerar uma URL por chamar [CWebApplication::createUrl]. O primeiro elemento array
deve especificar a [rota](/doc/guide/basics.controller#route) para a ação login 
do controller, e o restante, pares nome-valor de parâmetros GET.
Por exemplo:

~~~
[php]
array(
	......
	'components'=>array(
		'user'=>array(
			// este é, de fato, o valor padrão
			'loginUrl'=>array('site/login'),
		),
	),
)
~~~

Se o navegador for redirecionado para a página de login e o login for bem-sucedido,
podemos redirecioná-lo novamente para a página que gerou a falha de autorização.
Como podemos saber a URL dessa página? Podemos conseguir essa informação da propriedade
[returnUrl|CWebUser::returnUrl] do componente Usuário. Podemos assim fazer o seguinte para
executar o redirecionamento:

~~~
[php]
Yii::app()->request->redirect(Yii::app()->user->returnUrl);
~~~

Role-Based Access Control
Controle de Acesso Baseado em Papéis
-------------------------

Controle de Acesso Baseado em Papéis (Role-Based Access Control - RBAC) provê um simples porém poderoso
controle de acesso centralizado. Por favor, consulte o [Artigo Wiki]
(http://en.wikipedia.org/wiki/Role-based_access_control) para mais detalhes
sobre a comparação do RBAC com outras formas de controle de acesso mais tradicionais.

O Yii implementa o esquema de hierarquia RBAC através de seu
componente de aplicação [authManager|CWebApplication::authManager].
A seguir, nós primeiro introduzimos os conceitos principais usados neste esquema;
Após, descrevemos como definir dados de autorização. Por fim, mostramos como
fazer uso dos dados de autorização para realizar a verificação de acesso.

### Visão Geral

Um conceito fundamental do RBAC no Yii é o *item de autorização*. Um
item de autorização é uma permissão de fazer algo (ex: criar novas postagens
num blog, gerenciar usuários, etc). De acordo com sua granulidade e audiência,
itens de autorização podem ser classificados como *operações*, *tarefas* e 
*papéis*. Um papel consiste de tarefas, uma tarefa consiste de operações e
uma operação é uma permissão que é atômica.
Por exemplo, podemos ter um sistema com um papel `administrador` que consista
das tarefas `gerenciar postagens` e `gerenciar usuários`. A tarefa `gerenciar usuários`
pode consistir das operações `criar usuário`, `atualizar usuário` e `excluir usuário`.
Para maior flexibilidade o Yii também permite que um papel seja constituído de
outros papéis e/ou operações, uma tarefa seja constituída de outras tarefas e uma
operação seja constituída de outras operações.

An authorization item is uniquely identified by its name.

An authorization item may be associated with a *business rule*. A
business rule is a piece of PHP code that will be executed when performing
access checking with respect to the item. Only when the execution returns
true, will the user be considered to have the permission represented by the
item. For example, when defining an operation `updatePost`, we would like
to add a business rule that checks if the user ID is the same as the post's
author ID so that only the author himself can have the permission to update
a post.

Using authorization items, we can build up an *authorization
hierarchy*. An item `A` is a parent of another item `B` in the
hierarchy if `A` consists of `B` (or say `A` inherits the permission(s)
represented by `B`). An item can have multiple child items, and it can also
have multiple parent items. Therefore, an authorization hierarchy is a
partial-order graph rather than a tree. In this hierarchy, role items sit
on top levels, operation items on bottom levels, while task items in
between.

Once we have an authorization hierarchy, we can assign roles in this
hierarchy to application users. A user, once assigned with a role, will
have the permissions represented by the role. For example, if we assign the
`administrator` role to a user, he will have the administrator permissions
which include `post management` and `user management` (and the
corresponding operations such as `create user`).

Now the fun part starts. In a controller action, we want to check if the
current user can delete the specified post. Using the RBAC hierarchy and
assignment, this can be done easily as follows:

~~~
[php]
if(Yii::app()->user->checkAccess('deletePost'))
{
	// delete the post
}
~~~

Configuring Authorization Manager
---------------------------------

Before we set off to define an authorization hierarchy and perform access
checking, we need to configure the
[authManager|CWebApplication::authManager] application component. Yii
provides two types of authorization managers: [CPhpAuthManager] and
[CDbAuthManager]. The former uses a PHP script file to store authorization
data, while the latter stores authorization data in database. When we
configure the [authManager|CWebApplication::authManager] application
component, we need to specify which component class to use and what are the
initial property values for the component. For example,

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'sqlite:path/to/file.db',
		),
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'connectionID'=>'db',
		),
	),
);
~~~

We can then access the [authManager|CWebApplication::authManager]
application component using `Yii::app()->authManager`.

Defining Authorization Hierarchy
--------------------------------

Defining authorization hierarchy involves three steps: defining
authorization items, establishing relationships between authorization
items, and assigning roles to application users. The
[authManager|CWebApplication::authManager] application component provides a
whole set of APIs to accomplish these tasks.

To define an authorization item, call one of the following methods,
depending on the type of the item:

   - [CAuthManager::createRole]
   - [CAuthManager::createTask]
   - [CAuthManager::createOperation]

Once we have a set of authorization items, we can call the following
methods to establish relationships between authorization items:

   - [CAuthManager::addItemChild]
   - [CAuthManager::removeItemChild]
   - [CAuthItem::addChild]
   - [CAuthItem::removeChild]

And finally, we call the following methods to assign role items to
individual users:

   - [CAuthManager::assign]
   - [CAuthManager::revoke]

Below we show an example about building an authorization hierarchy with
the provided APIs:

~~~
[php]
$auth=Yii::app()->authManager;

$auth->createOperation('createPost','create a post');
$auth->createOperation('readPost','read a post');
$auth->createOperation('updatePost','update a post');
$auth->createOperation('deletePost','delete a post');

$bizRule='return Yii::app()->user->id==$params["post"]->authID;';
$task=$auth->createTask('updateOwnPost','update a post by author himself',$bizRule);
$task->addChild('updatePost');

$role=$auth->createRole('reader');
$role->addChild('readPost');

$role=$auth->createRole('author');
$role->addChild('reader');
$role->addChild('createPost');
$role->addChild('updateOwnPost');

$role=$auth->createRole('editor');
$role->addChild('reader');
$role->addChild('updatePost');

$role=$auth->createRole('admin');
$role->addChild('editor');
$role->addChild('author');
$role->addChild('deletePost');

$auth->assign('reader','readerA');
$auth->assign('author','authorB');
$auth->assign('editor','editorC');
$auth->assign('admin','adminD');
~~~

Once we have established this hierarchy, the [authManager|CWebApplication::authManager] component (e.g.
[CPhpAuthManager], [CDbAuthManager]) will load the authorization
items automatically. Therefore, we only need to run the above code one time, and NOT for every request.

> Info: While the above example looks long and tedious, it is mainly for
> demonstrative purpose. Developers will usually need to develop some administrative user
> interfaces so that end users can use to establish an authorization
> hierarchy more intuitively.


Using Business Rules
--------------------

When we are defining the authorization hierarchy, we can associate a role, a task or an operation with a so-called *business rule*. We may also associate a business rule when we assign a role to a user. A business rule is a piece of PHP code that is executed when we perform access checking. The returning value of the code is used to determine if the role or assignment applies to the current user. In the example above, we associated a business rule with the `updateOwnPost` task. In the business rule we simply check if the current user ID is the same as the specified post's author ID. The post information in the `$params` array is supplied by developers when performing access checking.


### Access Checking

To perform access checking, we first need to know the name of the
authorization item. For example, to check if the current user can create a
post, we would check if he has the permission represented by the
`createPost` operation. We then call [CWebUser::checkAccess] to perform the
access checking:

~~~
[php]
if(Yii::app()->user->checkAccess('createPost'))
{
	// create post
}
~~~

If the authorization rule is associated with a business rule which
requires additional parameters, we can pass them as well. For example, to
check if a user can update a post, we would pass in the post data in the `$params`:

~~~
[php]
$params=array('post'=>$post);
if(Yii::app()->user->checkAccess('updateOwnPost',$params))
{
	// update post
}
~~~


### Using Default Roles

Many Web applications need some very special roles that would be assigned to
every or most of the system users. For example, we may want to assign some
privileges to all authenticated users. It poses a lot of maintenance trouble
if we explicitly specify and store these role assignments. We can exploit
*default roles* to solve this problem.

A default role is a role that is implicitly assigned to every user, including
both authenticated and guest. We do not need to explicitly assign it to a user.
When [CWebUser::checkAccess] is invoked, default roles will be checked first as if they are
assigned to the user.

Default roles must be declared in the [CAuthManager::defaultRoles] property.
For example, the following configuration declares two roles to be default roles: `authenticated` and `guest`.

~~~
[php]
return array(
	'components'=>array(
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'defaultRoles'=>array('authenticated', 'guest'),
		),
	),
);
~~~

Because a default role is assigned to every user, it usually needs to be
associated with a business rule that determines whether the role
really applies to the user. For example, the following code defines two
roles, `authenticated` and `guest`, which effectively apply to authenticated
users and guest users, respectively.

~~~
[php]
$bizRule='return !Yii::app()->user->isGuest;';
$auth->createRole('authenticated', 'authenticated user', $bizRule);

$bizRule='return Yii::app()->user->isGuest;';
$auth->createRole('guest', 'guest user', $bizRule);
~~~

<div class="revision">$Id: topics.auth.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
