Criando um Modelo
=================

Antes de escrever o código HTML necessário para um formulário, devemos 
decidir quais tipos de dados esperamos dos usuários e a quais regras eles devem 
estar de acordo. Uma classe de modelo pode ser utilizada para registrar essas 
informações. Um modelo, como descrito em [Modelo](/doc/guide/basics.model), 
é o lugar central para manter as entradas fornecidas pelo usuário e validá-las.

Dependendo da forma como utilizamos as entradas dos usuários, podemos criar 
dois tipos de modelo. Se os dados são coletados, utilizados e, então, descartados, 
devemos criar um [modelo de formulário](/doc/guide/basics.model) (form model); 
porém, se a entrada do usuário deve ser coletada e armazenada em uma base de dados, devemos 
utilizar um [active record](/doc/guide/database.ar). Ambos os tipos de modelo 
compartilham [CModel] como classe base, onde está definida uma interface 
comum necessária a formulários.

> Note|Nota: Nós utilizaremos modelos de formulários nos exemplos desta seção.
Entretanto, o mesmo pode ser aplicado para modelos utilizando [active record](/doc/guide/database.ar).

Definindo uma Classe de Modelo
------------------------------

No trecho de código abaixo, criamos uma classe de modelo chamada `LoginForm` 
que será utilizada para coletar os dados informados pelo usuário em uma página 
de login. Como essa informação é utilizada somente para autenticar o usuário e 
não necessita ser armazenada, criaremos a classe `LoginForm` como um modelo de 
formulário.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;
}
~~~

Foram declarados três atributos na classe `LoginForm`: `$username`, `$password`, 
e `$rememberMe`. Eles são utilizados para manter o nome de usuário e senha 
informados no formulário, bem como a opção se ele deseja que o sistema se lembre 
de seu login. Como a valor padrão de `$rememberMe` é `false`, a opção 
correspondente no formulário de login estará, inicialmente, desmarcada.

> Info|Informação: Em vez de chamarmos essas variáveis membro de propriedades, 
utilizamos o termo *atributos* para diferenciá-las de propriedades normais. 
Um atributo é uma propriedade utilizada, basicamente, para armazenar dados 
originados de entradas de usuários ou do banco de dados.

Declarando Regras de Validação
------------------------------

Uma vez que o usuário envia seus dados e o modelo é preenchido com eles, 
devemos garantir que essas informações sejam validadas antes de serem utilizadas. 
Para isso, utilizamos um conjunto de regras que são testadas contra os dados 
informados. Para especificar essas regras de validação, utilizamos o método `rules()`, 
que deve retornar um vetor contendo as configurações de regras.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;

	private $_identity;

	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate'),
		);
	}

	public function authenticate($attribute,$params)
	{
		$this->_identity=new UserIdentity($this->username,$this->password);
		if(!$this->_identity->authenticate())
			$this->addError('password','Incorrect username or password.');
	}
}
~~~

No código acima, especificamos que `username` e `password` são obrigatórios (required). 
Além disso, definimos que `password` deve ser autenticado (authenticate) e
`rememberMe` deve aceitar apenas valores booleanos (boolean).

Cada regra retornada pelo método `rules()` deve estar no seguinte formato:

~~~
[php]
array('ListaDeAtributos', 'Validador', 'on'=>'ListaDeCenarios', ...opções adicionais)
~~~

Onde, `ListaDeAtributos` é uma string contendo todos os atributos, separados por vírgula, 
que devem ser validados de acordo com a regra; `Validador` determina que tipo de validação 
deverá ser efetuada; o parâmetro `on` é opcional e é utilizado para especificar uma 
lista de cenários onde a regra deve ser aplicada; opções adicionais são pares chave-valor, 
utilizados para iniciar as propriedades do validador.

Existem três maneiras de especificar o `Validador` em uma regra. Primeira, `Validador` 
pode ser o nome de um método na classe do modelo, como o `authenticate` no exemplo acima. 
Nesse caso, o método validador deve ter a seguinte assinatura:

~~~
[php]
/**
 * @param string o nome do atributo a ser validado
 * @param array opções especificadas na regra de validação
 */
public function nomeDoValidador($atributo,$parametros) { ... }
~~~

Segunda, `Validador` pode ser o nome de uma classe validadora. Dessa maneira, quando 
a regra é aplicada, uma instância dessa classe será criada para efetuar a validação. 
As opções adicionais na regra serão utilizadas para iniciar os valores dos atributos 
da instância. Uma classe validadora deve estender a classe [CValidator].

Terceira, `Validador` pode ser um alias (apelido) predefinido para uma classe validadora. 
No exemplo acima, o nome `required` é um alias para a classe [CRequiredValidator], a qual 
valida se o valor do atributo não está vazio. Abaixo, temos uma lista completa dos 
aliases (apelidos) predefinidos:

   - `boolean`: alias para [CBooleanValidator], garante que o valor de um atributo seja somente  
[CBooleanValidator::trueValue] ou [CBooleanValidator::falseValue].

   - `captcha`: alias para [CCapthcaValidator], garante que o atributo é igual ao código 
de verificação exibido em um [CAPTCHA](http://en.wikipedia.org/wiki/Captcha).

   - `compare`: alias para [CCompareValidator], garante que o atributo é igual a outro
atributo ou a uma constante.

   - `email`: alias para [CEmailValidator], garante que o atributo é um endereço de email
válido.

   - `default`: alias para [CDefaultValueValidator], utilizado para atribuir um valor padrão 
(default) aos atributos especificados.

   - `exist`: alias para [CExistValidator], garante que o valor do atributo existe na coluna 
da tabela informada.

   - `file`: alias para [CFileValidator], garante que o atributo contém o nome de um arquivo 
enviado via upload.

   - `filter`: alias para [CFilterValidator], modifica o atributo com um filtro.
   
   - `in`: alias para [CRangeValidator], garante que o dado informado está entre uma lista 
específica de valores.

   - `length`: alias para [CStringValidator], garante que o tamanho do dado está dentro de 
um tamanho específico.

   - `match`: alias para [CRegularExpressionValidator], garante que o dado informado 
casa com um expressão regular.

   - `numerical`: alias para [CNumberValidator], garante que o dado informado é um número 
válido.

   - `required`: alias para [CRequiredValidator], garante que o valor do atributo não está vazio.
   
   - `type`: alias para [CTypeValidator], garante que o atributo é de um tipo específico.
   
   - `unique`: alias para [CUniqueValidator], garante que o dado informado é único na coluna da 
tabela do banco de dados informada.

   - `url`: alias para [CUrlValidator], garante que o dado informado é uma URL válida.

Abaixo listamos alguns exemplos da utilização de validadores predefinidos:

~~~
[php]
// username é obrigatório
array('username', 'required'),
// username deve ter entre 3 e 12 caracteres
array('username', 'length', 'min'=>3, 'max'=>12),
// quando estiver no cenário register, password deve ser igual password2
array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
// quando estiver no cenário login, password deve ser autenticado
array('password', 'authenticate', 'on'=>'login'),
~~~

Atribuição Segura de Atributos
------------------------------

Normalmente, depois que uma instância de um modelo é criada, precisamos popular seus 
atributos com as informações enviadas pelo usuário. Isso pode ser feito de uma 
maneira conveniente, utilizando a atribuição em massa, como pode ser visto no código abaixo:

~~~
[php]
$model=new LoginForm;
if(isset($_POST['LoginForm']))
	$model->attributes=$_POST['LoginForm'];
~~~

A forma de atribuição utilizada no exemplo acima é chamada *atribuição em massa*.
Nela cada entrada em `$_POST['LoginForm']` será atribuída ao atributo correspondente
no modelo ($model). Ela é equivalente a:

~~~
[php]
foreach($_POST['LoginForm'] as $name=>$value)
{
	if($name é um atributo seguro)
		$model->$name=$value;
}
~~~

É crucial determinar quais atributos são seguros. Por exemplo, se expormos a
chave primária de uma tabela como um atributo seguro, um atacante terá a chance
de modificar o valor da chave para um determinado registro e adulterar dados que
ele não tem autorizaçao de modificação.

O política utilizada para decidir quais atributos são seguros ou não, foi alterada
entre as versões 1.0 e 1.1. A seguir elas serão descritas separadamente:

###Atributos Seguros na versão 1.1

Na versão 1.1, um atributo é considerado seguro se ele aparece em alguma das
regras de validação aplicáveis para o dado cenário. Por exemplo:

~~~
[php]
array('username, password', 'required', 'on'=>'login, register'),
array('email', 'required', 'on'=>'register'),
~~~

No trecho de código acima, os atributos `username` e `password` são obrigatórios
no cenário `login`, enquanto `username`, `password` e `email` são obrigatórios no
cenário `register`. Como resultado disso, se efetuarmos uma atribuição em massa
no cenário `login`, somente os campos `username` e `password` serão atribuídos, uma
vez que somente eles possuem regras de validação nesse cenário. Por outro lado,
caso o cenário seja `register`, todos os três campos serão atribuídos.

~~~
[php]
// no cenário login
$model=new User('login');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];

// no cenário register
$model=new User('register');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];
~~~

Então por que utilizamos essa política para determinar se um atributo é seguro ou
não?
A idéia por tras disso é, se um atributo já tem uma ou mais regras de validação
para garantir seus valores, por que devemos nos preocupar com ele?

É importante lembrar que regras de validação são utilizadas para verificar
dados inseridos pelo usuário e não dados gerados no código (ex.: timestamps,
chaves primárias criadas automaticamente). Portanto, NÃO adicione regras para
os atributos que não irão receber dados dos usuários.

As vezes, precisamos declarar um atributo como seguro sem que seja necessária
qualquer regra de validação para ele. Um exemplo para isso, seria um atributo para
armazenar o conteúdo de um artigo, que pode receber qualquer tipo de entrada de
um usuário. Nesse caso, podemos utilizar a regra especial chamada `safe`:

~~~
[php]
array('conteudo', 'safe')
~~~

Além disso, também existe uma regra `unsafe`, utilizada para declarar explicitamente
que um atributo não é seguro:

~~~
[php]
array('permission', 'unsafe')
~~~

A regra `unsafe` raramente é utilizada e trata-se de uma excessão a regra para
atributos seguros.

###Atributos Seguros na versão 1.0


Na versão 1.0, a tarefa de decidir se um dado é seguro ou não é baseada no valor de retorno do
método `safeAttributes` e o cenário especificado. Por padrão, esse método 
retorna todas as variáveis membro públicas como atributos seguros para a classe 
[CFormModel], ou todas as colunas de uma tabela, menos a chave primária, como atributos 
para a classe [CActiveRecord]. Nós podemos sobrescrever este método para limitar esses 
atributos seguros de acordo com os cenários. Por exemplo, um modelo usuário deve 
conter vários atributos, mas no cenário `login`, precisamos apenas do `username` e do 
`password`. Podemos especificar esses limites da seguinte maneira:

~~~
[php]
public function safeAttributes()
{
	return array(
		parent::safeAttributes(),
		'login' => 'username, password',
	);
}
~~~

Mais precisamente, o valor de retorno do método `safeAttributes` deve ter a seguinte 
estrutura:

~~~
[php]
array(
   // esses atributos podem ser atribuídos em massa em qualquer cenário
   // isso não ser explicitamente especificado, como vemos abaixo
   'attr1, attr2, ...',
	 *
   // esses atributos só podem ser atribuídos em massa no cenário 1
   'cenario1' => 'attr2, attr3, ...',
	 *
   // esses atributos só podem ser atribuídos em massa no cenário 2
   'cenario2' => 'attr1, attr3, ...',
)
~~~

Se os cenários não são importantes para o modelo, ou se todos os cenários tem o mesmo 
conjunto de atributos, o valor de retorno pode ser simplificado para um simples string:

~~~
[php]
'attr1, attr2, ...'
~~~

Para dados não seguros, devemos atribui-los individualmente aos atributos, como 
no exemplo a seguir:

~~~
[php]
$model->permission='admin';
$model->id=1;
~~~

Disparando a Validação
----------------------

Uma vez que o modelo foi preenchido com os dados enviados pelo usuário,
podemos executar o método [CModel::validate()] para disparar o processo de 
validação. Esse método retorna uma valor indicando se a validação ocorreu com 
sucesso ou não. Para modelos utilizando [CActiveRecord], a validação pode ser 
disparada automaticamente quando o método [CActiveRecord::save()] é executado.

Podemos configurar utilizando o cenário utilizando a propriedade
[scenario|CModel::scenario]. O valor informado será utilizado para decidir quais
regras de validação serão aplicadas.

Por exemplo, no cenário `login`, queremos validar apenas as entradas para os
campos `username` e `password`; enquanto que, no cenário `register`, precisamos
validar mais entradas, tais como `email`, `address`, etc. O exemplo a seguir
mostra como executar a validação no cenário `register`:

~~~
[php]
// cria uma instância do modelo User no cenário register. É equivalente a:
// $model=new User;
// $model->scenario='register';
$model=new User('register');

// popula o modelo com as entradas do usuário
$model->attributes=$_POST['User'];

// executa a validação
if($model->validate())   // se as entradas são validas
    ...
else
    ...
~~~

Os cenários aplicáveis a uma regra pode ser especificados através da opção `on`.
Caso ela não seja especificada, a regra será aplicada em todos os cenários. Por
exemplo:

~~~
[php]
public function rules()
{
	return array(
		array('username, password', 'required'),
		array('password_repeat', 'required', 'on'=>'register'),
		array('password', 'compare', 'on'=>'register'),
	);
}
~~~

A primeira regra será aplicada para todos os cenários, enquanto
as outras duas serão aplicadas apenas no cenário `register`

Recuperando Erros de Validação
------------------------------

Uma vez que a validação foi executada, qualquer erro encontrado será armazenado
na instância do modelo. Podemos recuperar as mensagens de erro através dos métodos
[CModel::getErrors()] e [CModel::getError()]. A diferença entre esses dois métodos
é que o primeiro retorna *todos* os erros para o atributo especificado, enquando o
segundo irá retornar apenas o *primeiro* erro.

Rótulos de Atributos
--------------------

Quando desenvolvemos um formulário, normalmente precisamos exibir um rótulo 
para cada campo. Esse rótulo indica ao usuário que tipo de informação espera-se 
que ele informe naquele campo. Embora podemos escrever esses rótulos diretamente 
na visão, seria mais flexível e conveniente poder especifica-los diretamente no 
modelo correspondente.

Por padrão, a classe [CModel] irá retornar o nome do atributo como seu rótulo. 
Essa característica pode ser alterada sobrescrevendo o método 
[attributeLabels()|CModel::attributeLabels]. Como veremos nas subseções a seguir, 
especificando rótulos nos modelos nos permite criar formulários poderosos de uma 
maneira mais rápida.

<div class="revision">$Id: form.model.txt 2285 2010-07-28 20:40:00Z qiang.xue $</div>
