Usando Extensões
================

A utilização de uma extensão normalmente envolve os seguintes passos:

  1. Faça o download da extensão no [repositório](http://www.yiiframework.com/extensions/) 
  do Yii.
  2. Descompacte a extensão no diretório `extensions/xyz`, dentro do 
  [diretório base da aplicação](/doc/guide/basics.application#application-base-directory), 
  onde `xyz` é o nome da extensão.
  3. Importe, configure e utilize a extensão.
  
Cada extensão tem um nome que a identifica unicamente. Dada uma extensão chamada 
`xyz`, podemos sempre utilizar o path alias `ext.xyz` para localizar seu diretório 
base, que contém todos os arquivos de `xyz`.

> Note|Nota: O path alias `ext` está disponível a partir da versão 1.0.8. Nas versões 
anteriores, precisávamos utilizar `application.extensions` para nos referir ao diretório 
base das extensões. Nos exemplos a seguir, vamos assumir que `ext` está definido, 
Caso você utilize a versão 1.0.7, ou anterior, substitua o path alias por 
`aaplication.extensions`.

Extensões diferentes tem requisitos diferentes para importação, configuração e 
utilização. Abaixo, resumimos os tipos mais comuns de utilização de extensões, 
de acordo com as categorias descritas na [visão geral](/doc/guide/extensions.overview).

Extensões Zii
-------------

Antes de descrever a utilização de extensões de terceiros, gostariamos de
apresentar a biblioteca de extensões Zii. Trata-se de um conjunto de extensões
criadas pelo time de desenvolvedores do Yii e é incluída em todos os lançamentos
do framework, a partir da versão 1.1.0. Essa biblioteca está hospedada no Google
Code, no projeto chamado [zii](http://code.google.com/p/zii).

Ao utilizar uma das extensões da Zii, você deve utilizar um path alias para fazer referências às classes
correspondentes, no formato `zii.caminho.para.NomeDaClasse`.
O alias `zii` é definido pelo framework e aponta para o diretório raiz da biblioteca
Zii. Por exemplo, para utilizar a extensão [CGridView], devemos utilizar o seguinte
código em uma view:

~~~
[php]
$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
));
~~~

Componente de Aplicação
-----------------------

Para utilizar um [componente de aplicação](/doc/guide/basics.application#application-component), 
primeiro precisamos alterar a [configuração da aplicação](/doc/guide/basics.application#application-configuration) 
adicionando uma nova entrada na propriedade `components`, como no código abaixo:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'ext.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // configurações de outros componentes
    ),
);
~~~

Dessa forma, podemos acessar o componente em qualquer lugar utilizando 
`Yii::app()->xyz`. O componente será criado somente quando for acessado pela primeira vez, 
a não ser que ele tenha sido adicionado na propriedade `preload`.

Comportamento
-------------

[Comportamentos](/doc/guide/basics.component#component-behavior) podem ser utilizados em todos 
os tipos de componentes. O processo é realizado em dois passos. No primeiro, um comportamento é 
atribuído a um componente. No segundo, um método do comportamento é executado através do componente. 
Por exemplo:

~~~
[php]
// $nome identifica o comportamento dentro do componente
$componente->attachBehavior($nome, $comportamento);
// test() é um método de $comportamento
$componente->test();
~~~

Na maioria das vezes, um comportamento é atribuído a um componente através de configurações, em vez 
de utilizar o método `attachBehavior`. Por exemplo, para atribuir um comportamento a um 
[componente da aplicação](/doc/guide/basics.application#application-component), podemos utilizar 
a seguinte [configuração](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'behaviors'=>array(
				'xyz'=>array(
					'class'=>'ext.xyz.XyzComportamento',
					'propriedade1'=>'valor1',
					'propriedade2'=>'valor2',
				),
			),
		),
		//....
	),
);
~~~

No exemplo acima, o comportamento `xyz` é atribuído ao componente `db`. Essa forma de atribuição é 
possível porque a classe [CApplicationComponent] define uma propriedade chamada `behaviors`. Ao 
atribuir a ela uma lista de configurações de comportamentos, o componente irá anexa-los quando for inicializado.

Para as classes [CController], [CFormModel] e [CActiveModel], que, normalmente, necessitam ser estendidas, 
a atribuição de comportamentos é feita sobrescrevendo-se o método `behaviors()`.
Qualquer comportamento desclarado nesse método será automaticamente anexo à classe.
Por exemplo:

~~~
[php]
public function behaviors()
{
	return array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzComportamentos',
			'propriedade1'=>'valor1',
			'propriedade2'=>'valor2',
		),
	);
}
~~~

Widget
------

[Widgets](/doc/guide/basics.view#widget) são utilizados principalmente nas 
[visões](/doc/guide/basics.view). Dada uma classe widget, chamada `XyzClass`, 
pertencente a extensão `xyz`, podemos utiliza-la da seguinte maneira:

~~~
[php]
// um widget que não precisa de conteúdo para seu corpo
<?php $this->widget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// um widget que precisa de conteúdo para o seu corpo
<?php $this->beginWidget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...conteúdo do corpo do widget...

<?php $this->endWidget(); ?>
~~~

Ação
----

[Ações](/doc/guide/basics.controller#action) são utilizadas por um 
[controle](/doc/guide/basics.controller) para responder à uma requisição específica 
do usuário. Dada a classe da ação `XyzClass`, pertencente a extensão `xyz`, 
podemos utiliza-la sobrescrevendo o método [CController::actions] na classe de 
nosso controle:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// outras ações
		);
	}
}
~~~

Dessa forma, a ação pode ser acessada através da [rota](/doc/guide/basics.controller#route) 
`test/xyz`.

Filtro
------

[Filtros](/doc/guide/basics.controller#filter) também são utilizados por um 
[controle](/doc/guide/basics.controller). Basicamente eles pré e pós processam a requisição 
do usuário manuseada por uma [ação](/doc/guide/basics.controller#action).
Dada a classe do filtro `XyzClass`, pertencente a extensão `xyz`, podemos utiliza-la 
sobrescrevendo o método [CController::filters], na classe de nosso controle.

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// outros filtros
		);
	}
}
~~~

No exemplo acima, podemos utilizar no primeiro elemento do 
vetor os operadores `+` e `-`,  para limitar as ações onde o filtro será aplicado. 
Para mais detalhes, veja a documentação da classe [CController].

Controle
--------

Um [controle](/doc/guide/basics.controller), fornece um conjunto de ações que podem 
ser requisitadas pelos usuários. Para utilizar uma extensão de um controle, precisamos 
configurar a propriedade [CWebApplication::controllerMap] na 
[configuração da aplicação](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// outros controles
	),
);
~~~

Dessa forma, uma ação `a` no controle pode ser acessada pela 
[rota](/doc/guide/basics.controller#route) `xyz/a`.


Validador
---------

Um validador é utilizado principalmente na classe de um [modelo](/doc/guide/basics.model) 
(que estenda de [CFormModel] ou [CActiveRecord]). Dada a classe de um validador 
chamada `XyzClass`, pertencente a extensão `xyz`, podemos utiliza-la sobrescrevendo 
o método [CModel::rules] na classe de nosso modelo:

~~~
[php]
class MyModel extends CActiveRecord // ou CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// outras regras de validação
		);
	}
}
~~~

Comando de Console
------------------

Uma extensão do tipo [comando de console](/doc/guide/topics.console), normalmente 
é utilizada para adicionar comandos à ferramenta `yiic`. Dado um comando de console 
`XyzClass`, pertencente à extensão `xyz`, podemos utiliza-lo o adicionando 
nas configurações da aplicação de console:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// outros comandos
	),
);
~~~

Dessa forma, podemos utilizar o comando `xyz` na ferramenta `yiic`.

> Note|Nota: Uma aplicação de console normalmente utiliza um arquivo de configuração 
diferente do utilizado pela aplicação web. Se uma aplicação foi criada utilizando o 
comando `yiic webapp`, o arquivo de configurações para o console estará em `protected/config/console.php`, 
enquanto o arquivo de configuração para a aplicação web estará em `protected/config/main.php`.

Módulo
------

Para utilizar módulos, por favor, veja a seção sobre [módulos](/doc/guide/basics.module#using-module).

Componente Genérico
-------------------

Para utilizar um [componente](/doc/guide/basics.component), primeiro precisamos 
incluir seu arquivo de classe, utilizando:

~~~
Yii::import('ext.xyz.XyzClass');
~~~

Feito isso, podemos criar uma instância dessa classe, configurar suas propriedades e 
chamar seus métodos. Podemos também estendê-lo para criar novas classes.

<div class="revision">$Id: extension.use.txt 1780 2010-02-01 20:32:50Z qiang.xue $</div>
