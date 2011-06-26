Criando uma Ação
================

Uma vez que temos um modelo pronto, podemos começar a escrever a lógica 
necessária para manipula-lo. Devemos colocar essa lógica dentro de uma ação 
no controle. Para o exemplo do formulário de login, o código a seguir é 
necessário:

~~~
[php]
public function actionLogin()
{
	$model=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// coleta a informação inserida pelo usuário
		$model->attributes=$_POST['LoginForm'];
		// valida a entrada do usuário e redireciona para a página anterior, caso valide
		if($model->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// exibe o formulário de login
	$this->render('login',array('model'=>$model));
}
~~~

No código acima, primeiro criamos uma instância de um `LoginForm`. Se a requisição 
for do tipo POST (indicando que um formulário de login foi enviado), nós 
preenchemos o `$model` com os dados enviados via `$_POST['LoginForm']`. Em
seguida, validamos os dados e, em caso de sucesso, redirecionamos o navegador 
para a página que requisitou a autenticação. Se a validação falhar, ou se for o 
primeiro acesso a essa ação, renderizamos o conteúdo da visão 'login', que será 
descrita na próxima subseção.

> Tip|Dica: Na ação `login`, utilizamos a propriedade `Yii::app()->user->returnUrl` 
para pegar a URL da página que necessitou a autenticação. O componente 
`Yii::app()->user` é do tipo [CWebUser] (ou de uma classe derivada dele) que 
representa a sessão com as informações do usuário (por exemplo, nome de usuário, 
status). Para mais detalhes, veja [Autenticação e Autorização](/doc/guide/topics.auth).

Vamos dar uma atenção especial para o seguinte trecho de código que aparece na 
ação `login`:

~~~
[php]
$model->attributes=$_POST['LoginForm'];
~~~

Como descrevemos em [Atribuição Segura de Atributos](/doc/guide/form.model#securing-attribute-assignments), 
essa linha de código preenche um modelo com as informações enviadas pelo usuário. 
A propriedade `attributes` é definida pela classe [CModel] que espera um vetor de 
pares nome-valor, e atribui cada valor ao atributo correspondente no modelo. 
Sendo assim, se `$_POST['LoginForm']`, contém um vetor desse tipo, o código 
acima seria o equivalente ao código mais longo abaixo (assumindo que todos os 
atributos necessários estão presentes no vetor):

~~~
[php]
$model->username=$_POST['LoginForm']['username'];
$model->password=$_POST['LoginForm']['password'];
$model->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note|Nota: Para fazer com que a variável `$_POST['LoginForm']` nos retorne um 
vetor em vez de uma string, utilizamos uma convenção ao nomear os campos do formulário 
na visão. Para um campo correspondente ao atributo `a` de um modelo da classe `C`, 
seu nome será `C[a]`. Por exemplo, utilizamos `LoginForm[username]` para nomear 
o campo correspondente ao atributo `username` do modelo `LoginForm`.

O trabalho restante agora é criar a visão `login` que deve conter um formulário 
HTML com os campos necessários.

<div class="revision">$Id: form.action.txt 1837 2010-02-24 22:49:51Z qiang.xue $</div>
