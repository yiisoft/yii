Criando a primeira aplicação Yii
==============================

Para ter uma experiência inicial com o Yii, descrevemos nesta seção como criar nossa
primeira aplicação em Yii. Iremos utilizar a poderosa ferramenta `yiic` que pode ser
usada para automatizar a criação de código para várias finalidades. Assumiremos que
`YiiRoot` é o diretório onde o Yii está instalado e `WebRoot` é o diretório raíz
do servidor Web.

Execute o `yiic` pela linha de comando, como no exemplo a seguir:

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Nota: Quando executamos o `yiic` no Mac OS, Linux ou Unix, devemos 
> alterar a permissão do arquivo `yiic` para torna-lo executável.
>
> Como forma alternativa, você pode executa-lo da seguinte maneira:
>
> ~~~
> % cd WebRoot/testdrive
> % php YiiRoot/framework/yiic.php webapp WebRoot/testdrive
> ~~~

Esse comando irá criar o esquele de uma aplicação Yii, no diretório `WebRoot/testdrive`. 
A aplicação tem um estrutura de diretórios que é a necessária para a maioria das 
aplicações feitas no Yii.

Sem ter escrito uma única linha de código, ja podemos testar nossa primeira aplicação Yii, 
acessando a seguinte URL:

~~~
http://nomedoservidor/testdrive/index.php
~~~

Como podemos ver, a aplicação tem três páginas: a página inicial, a página de contato e a página
de login. A página principal mostra algumas informações sobre a aplicação, como o login do 
usuário ativo, a página de contato exibe um formulário de contato que os usuários podem preencher 
e enviar suas mensagens, a página de login permite que os usuários se autentiquem antes de acessar 
o conteúdo privilegiado. Veja as imagens a seguir para mais detalhes:


![Página Principal](first-app1.png)

![Página de Contato](first-app2.png)

![Página de Contato com erros de entrada](first-app3.png)

![Página de Contato com emissão bem sucedida](first-app4.png)

![Página de Login](first-app5.png)


A listagem seguinte mostra a estrutura de diretórios da nossa aplicação.
Por favor, veja as [Convenções](/doc/guide/basics.convention#directory) para
obter explicações detalhadas sobre essa estrutura.

~~~
testdrive/
   index.php                 Script de entrada da aplicação Web
   assets/                   Contém arquivos de recurso publicados
   css/                      Contém arquivos CSS
   images/                   Contém arquivos de imagem
   themes/                   Contém temas da aplicação
   protected/                Contém arquivos protegidos da aplicação
      yiic                   Script de linha de comando yiic
      yiic.bat               Script de linha de comando yiic para o Windows
      commands/              Contém comandos 'yiic' customizados
         shell/              Contém comandos 'yiic shell' customizados
      components/            Contém componentes reutilizáveis do usuário
         MainMenu.php        A classe widget 'MainMenu' (Menu Principal)
         Identity.php        A classe 'Identity' usada nas autenticações
         views/              Contém arquivos de visão dos widgets
            mainMenu.php     O arquivo de visão do widget 'MainMenu'
      config/                Contém arquivos de configurações
         console.php         Configuração da aplicação console
         main.php            Configuração da aplicação Web
      controllers/           Contém arquivos das classes de controle
         SiteController.php  Classes de controle padrão
      extensions/            Contém extensões de terceiros
      messages/              Contém mensagens traduzidas
      models/                Contém arquivos das classes de modelo
         LoginForm.php       Modelo do formulário para a ação 'login'
         ContactForm.php     Modelo do formulário para a ação 'contact'
      runtime/               Contém arquivos gerados temporariamente
      views/                 Contém arquivos de visão dos controles e layouts
         layouts/            Contém arquivos de visão do layout
            main.php         O layout padrão para todas as visões
         site/               Contém arquivos de visão para o controle 'site'
            contact.php      Visão para a ação 'contact'
            index.php        Visão para a ação 'index' 
            login.php        Visão para a ação 'login' 
         system/             Contém arquivos de visão do sistema
~~~

Conectando ao Banco de Dados
----------------------

A maioria das aplicações Web são auxiliadas com o uso de banco de dados.
Nossa aplicação de test-drive não é uma exceção. Para usar banco de dados,
primeiro precisamos dizer à aplicação como se conectar a ele. Isto é feito
alterando o arquivo de configuração `WebRoot/testdrive/protected/config/main.php`, 
como mostrado abaixo:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/source.db',
		),
	),
	......
);
~~~

Acima, nós adicionamos uma entrada para `db` ao array `components`, que instrui a
aplicação para se conectar ao banco de dados SQLite `WebRoot/testdrive/protected/data/source.db` 
quando for preciso.

> Note|Nota: Para utilizar os recursos de banco de dados do Yii, precisamos ativar
a extensão PDO do PHP e a extensão de driver PDO específico. Para a aplicação test-drive,
as extensões `php_pdo` e `php_pdo_sqlite` deverão estar habilitadas.

Para este fim, precisamos de preparar uma base de dados SQLite, para que a configuração
feita anteriormente seja eficaz. Usando alguma ferramenta de administração do SQLite,
podemos criar um banco de dados com o seguinte esquema:

~~~
[sql]
CREATE TABLE User (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

> Note|Nota: Se estiver utilizando um banco de dados MySQL, você deve substituir o 
`AUTOINCREMENT`, utilizado no código acima, por `AUTO_INCREMENT`.

Por simplicidade, criamos somente uma única tabela: `User` no nosso banco de dados.
O arquivo do banco de dados SQLite foi salvo em `WebRoot/testdrive/protected/data/source.db`.
Observe que tanto o arquivo quanto o diretório devem ter permissão de leitura do servidor Web,
como requerido pelo SQLite.


Implementando operações do tipo CRUD
----------------------------

Agora começa a parte divertida. Iremos implementar operações CRUD (create, read,
update and delete) quer realizará inserções, leituras, edições e deleções na 
tabela `User` que acabamos de criar. Este tipo de operação é frequetemente necessário
em aplicações reais.

Em vez da dificuldade na escrita de um código real, iremos utilizar a poderosa ferramenta
`yiic` para gerar automaticamente o código. Este processo é também conhecido como *scaffolding*.
Abra a linha de comando e execute os comandos listados a seguir:

~~~
% cd WebRoot/testdrive
% protected/yiic shell
Yii Interactive Tool v1.0
Please type 'help' for help. Type 'exit' to quit.
>> model User
   generate User.php

The 'User' class has been successfully created in the following file:
    D:\wwwroot\testdrive\protected\models\User.php

If you have a 'db' database connection, you can test it now with:
    $model=User::model()->find();
    print_r($model);

>> crud User
   generate UserController.php
   mkdir D:/wwwroot/testdrive/protected/views/user
   generate create.php
   generate update.php
   generate list.php
   generate show.php
   generate admin.php
   generate _form.php

Crud 'user' has been successfully created. You may access it via:
http://hostname/path/to/index.php?r=user
~~~

Acima, utilizamos o comando `yiic shell` para interagir com nossa
aplicação esqueleto. Na linha de comando, podemos digitar dois subcomandos:
`model User` e `crud User`. O primeiro gera a classe modelo para a tabela
`User`, enquanto que o segundo comando lê a classe modelo `User` e gera 
o código necessário para as operações do tipo CRUD.

> Note|Nota: Você poderá encontrar erros como "...could not find driver" ou "...driver não encontrado", 
> mesmo que o verificador de requisitos mostre que você já tem o PDO ativado e o driver PDO
> correspondente ao Banco de Dados. Caso isso ocorra, você deve tentar rodar a ferramenta
> `yiic` do seguinte modo:
>
> ~~~
> % php -c caminho/para/php.ini protected/yiic.php shell
> ~~~
>
> onde `caminho/para/php.ini` representa o arquivo PHP.ini correto.

Podemos ver nossa primeira aplicação pela seguinte URL:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Essa página irá mostrar uma lista de entradas de usuários da tabela `User`.
Se tabela estiver vazia, nada será exibido.

Clique no link `New User` da página. Caso não esteja autenticado seremos
levados à página de login. Uma vez logado, será exibido um formulário
de entrada que permite adicionar um novo usuário. Preencha o formulário e
clique sobre o botão `Create`. Se houver qualquer erro de entrada, um 
erro será mostrado, o que nos impede de salvar os dados. Voltando à lista
de usuários, iremos ver o recém adicionado usuário aparecendo na lista.

Repita as etapas acima para adicionar novos usuários. Repare que a tabela de
usuários será automaticamente paginada, caso existam muitos usuários a serem
exibidos em uma página.

Se logarmos como administrador utilizando o login/senha: `admin/admin`, veremos
a página de administração de usuários pela seguinte URL:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Será mostrada uma tabela de usuários. Podemos clicar nas células do cabeçalho 
para ordenar as colunas correspondentes. E como na página de listagem dos usuários,
a página de administração dos usuários também realiza a paginação quando existem
muitos usuários a serem exibidos.

Todas essas incríveis funcionalidades foram criadas sem escrever uma única linha de código!

![Página de administração dos usuários](first-app6.png)

![Página de criação de um novo usuário](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 1264 2009-07-21 19:34:55Z qiang.xue $</div>
