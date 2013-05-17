Aplicação
=========

A aplicação representa o contexto de execução do processamento de uma solicitação.
Sua principal tarefa é a de receber uma solicitação do usuário e eviá-la para um
controle adequado para o posterior processamento. Serve também como o lugar central
para o processamento de configurações a nível da aplicação. Por esta razão, a aplicação
é também chamada de `controle de frente`.

A aplicação é criada como um singleton pelo [script de entrada](/doc/guide/basics.entry).
O singleton da aplicação significa que esta pode ser acessada em qualquer lugar pelo [Yii::app()|YiiBase::app].

Configuração da Aplicação
-------------------------

Por padrão, a aplicação é uma instancia de [CWebApplication]. Para personalizá-la
normalmente é utilizado um arquivo de configuração (ou um array) para inicializar
os valores de suas propriedades quando a instância da aplicação é criada. Uma 
alternativa para a costomização é estender o [CWebApplication].

A configuração é um conjunto de pares do tipo chave-valor. Cada chave representa
o nome de uma propriedade da instancia da aplicação, e cada valor a propriedade
inicial correspondente. Por exemplo, a seguinte configuração altera as propriedades
[name|CApplication::name] e [defaultController|CWebApplication::defaultController]
da aplicação.

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

Costumamos salvar a configuração em um script PHP separado (ex.:
`protected/config/main.php`). Dentro do script, retornamos o array
de configuração do seguinte modo:

~~~
[php]
return array(...);
~~~

Para aplicar a configuração, passamos o nome do arquivo de configuração
como um parâmetro ao construtor da aplicação, ou para o método [Yii::createWebApplication()],
como o seguinte exemplo que é feito normalmente no [script de entrada](/doc/guide/basics.entry):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|Dica: Se a configuração da aplicação é muito complexa, podemos dividí-la
em vários arquivos, cada um retornando uma porção do array de configuração.
Em seguida, no arquivo principal de configuração, chamamos a função `include()`
do PHP para incluir o resto dos arquivos de configuração e combinamos em uma
única array de configuração completa.

Diretório Base da Aplicação
---------------------------

O diretório base da aplicação é a pasta principal que contém todos os
dados e scripts de PHP sensíveis à segurança. Por padrão, é um subdiretório
chamado `protected` que está localizado sob o diretório que contém o script
de entrada. Ele pode ser personalizado através da definição 
[basePath|CWebApplication::basePath], uma propriedade da 
[configuração da aplicação](#application-configuration).

O Conteúdo dentro do diretório base da aplicação deve ter o acesso protegido
de usuários da Web. Com o servidor [Apache HTTP Server](http://httpd.apache.org/),
isto pode ser feito facilmente criando um arquivo `.htaccess` dentro do diretório
base. O conteúdo do arquivo `.htaccess` deverá ser o seguinte:

~~~
deny from all
~~~

Componentes da Aplicação
------------------------

As funcionalidades da aplicação podem ser facilmente customizadas
e enriquecidas graças à arquitetura flexível de componentes.
A aplicação gerencia um conjunto de componentes, que implementam
recursos específicos. Por exemplo, a aplicação realiza um pedido de
um usuário com a ajuda dos componentes [CUrlManager] e [CHttpRequest].

Ao configurar as propriedades dos [componentes|CApplication::components] da
aplicação, podemos personalizar a classe e os valores das propriedades de qualquer
componente usado na aplicação. Por exemplo, podemos configurar o componente [CMemCache] 
para que ele possa utilizar múltiplos servidores de memchache para fazer o caching:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

Acima, adicionamos o elemento `cache` ao array `components`. O elemento
`cache` indica a classe do componente, que é `CMemCache` e sua propriedade
`servers` deve ser inicializada como no exemplo.

Para acessar um componente da aplicação, use `Yii::app()->ComponentID`, onde
`ComponentID` refere=se ao ID do componente (ex.: `Yii::app()->cache`).

Um componente da aplicação pode ser desativado setando a propriedade `enabled`
para `false` na configuração. O valor Null é retornado quando um componente
desativado é acessado.

> Tip|Dica: Por padrão, os componentes da aplicação são criados sob demanda.
Isto significa que um componente pode não ser completamente criado se não
for acessado durante uma requisição de um usuário. Conseqüentemente, o desempenho
global não será prejudicado, mesmo em uma aplicação com muitos componentes.
Alguns componentes da aplicação (ex.: [CLogRouter]) necessitam serem criados,
não importando se estão sendo acessados ou não. Para fazer isso, liste os IDs
na propriedade [preload|CApplication::preload] da aplicação.

Principais Componentes da Aplicação
-----------------------------------

O Yii predefine um conjunto de componentes principais da aplicação para fornecer
funcionalidades comuns em aplicações Web. Por exemplo, o componente [request|CWebApplication::request] 
é usado para processar pedidos do usuário e prover informações como URL, cookies. Ao configurar
as propriedades desses componentes principais, podemos mudar o padrão de comportamento do Yii
em quase todos os aspectos.

Abaixo, listamos os principais componentes que são pré-declarados pelo [CWebApplication].

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
gerencia a criação dos ativos privados (assets).

   - [authManager|CWebApplication::authManager]: [CAuthManager] - 
gerencia o controle de acesso baseado em regras (RBAC).

   - [cache|CApplication::cache]: [CCache] - fornece as funcionalidades
do caching de dados. Note que você deve especificar a classe atual (ex.:
[CMemCache], [CDbCache]). Caso contrário, será retornado Null ao acessar
o componente.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
gerencia os scripts (javascripts e CSS) do cliente.

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
fornece as principais mensagens traduzidas usadas pelo framework Yii.

   - [db|CApplication::db]: [CDbConnection] - fornece uma conexão ao 
banco de dados. Note que você deverá configurar a propriedade
[connectionString|CDbConnection::connectionString] corretamente para
utilizar esse componente.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - processa 
erros e exceções do PHP.

   - [messages|CApplication::messages]: [CPhpMessageSource] - fornece mensagens
traduzidas utilizadas pela aplicação Yii.

   - [request|CWebApplication::request]: [CHttpRequest] - fornece informações 
relacionadas à solicitação do usuário.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
fornece serviços relacionados à segurança, como hashing e encriptação.

   - [session|CWebApplication::session]: [CHttpSession] - fornece funcionalidades 
relacionadas à sessão.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
fornece o estado global do método de persistência.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - fornece
funcionalidades de análise e criação de URLs.

   - [user|CWebApplication::user]: [CWebUser] - representa a identidade e
informações do usuário atual.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - gerencia temas.


Ciclo de Vida de uma Aplicação
------------------------------

Quando processa uma solicitação de um usuário, a aplicação segue o ciclo 
de vida descrito a seguir:

   0. Pré-inicia a aplicação com o método  [CApplication::preinit()];

   1. Configura as o auto-carregamento de classes (autoloader) e o tratamento de erros;

   2. Registra os principais componentes da aplicação;

   3. Carrega as configurações da aplicação;

   4. Inicia a aplicação com o [CApplication::init()]:
       - Registra os comportamentos (behaviors) da aplicação;
	   - Carrega os componentes estáticos da aplicação;

   5. Dispara o evento [onBeginRequest|CApplication::onBeginRequest] (no início da requisição);

   6. Processa a requisição do usuário:
	   - Resolve a requisição do usuário;
	   - Cria um controle;
	   - Executa o controle;

   7. Dispara o evento [onEndRequest|CApplication::onEndRequest] (ao fim da requisição);

<div class="revision">$Id: basics.application.txt 857 2009-03-20 17:31:09Z qiang.xue $</div>
