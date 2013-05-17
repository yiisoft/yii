Iniciando com Yii
=================

Nesta seção, descrevemos como criar um esqueleto de aplicação, que servirá como ponto de partida. Para simplificar, vamos supor que o documento raiz do nosso servidor Web é  `/wwwroot` e a URL correspondente é `http://www.example.com/`.


Instalando o Yii
----------------

Primeiro precisamos instalar o framework Yii. Pegue uma cópia do arquivo de liberação do Yii (versão 1.1.1 ou superior) do [www.yiiframework.com](http://www.yiiframework.com/download) e descompacte no diretório `/wwwroot/yii`. Certifique-se de que existe um diretório `/wwwroot/yii/framework`.

> Tip|Dica: O framework Yii pode ser instalado em qualquer parte do sistema de arquivos, não necessariamente dentro da pasta Web. O diretório `framework` contém todo o código do framework, e somente este diretório é necessário para o desenvolvimento de uma aplicação Yii. Uma única instalação do Yii pode ser usada por várias aplicações Yii.

Depois de instalar o Yii, abra uma janela do navegador e acessar a URL `http://www.example.com/yii/requirements/index.php`. Ela exibe os requisitos necessários para utilizar a versão do Yii instalada. Para a nossa aplicação blog, além dos requisitos mínimos necessários para o framework Yii, precisamos também habilitar as extensões `pdo` e `pdo_sqlite` do PHP, para que possamos acessar o banco de dados SQLite.


Criando o Esqueleto da Aplicação
--------------------------------

Em seguida, usaremos a ferramenta `yiic` para criar o esqueleto da aplicação sob o diretório `/wwwroot/blog`. A ferramenta `yiic` é uma ferramenta de linha de comando fornecido com o Yii. Ela pode ser usada para gerar o código e reduzir certas tarefas de codificação repetitivas.

Abra o Prompt de Comando e execute o seguinte comando:

~~~
% /wwwroot/yii/framework/yiic webapp /wwwroot/blog
Create a Web application under '/wwwroot/blog'? [Yes|No]y
......
~~~

> Tip|Dica: A fim de utilizar a ferramenta `yiic` como mostrado acima, o programa CLI PHP deve estar no caminho de procura do comando. Se não, o seguinte comando pode ser utilizado em vez disso:
>
>~~~
> pasta/do/php /wwwroot/yii/framework/yiic.php webapp /wwwroot/blog
>~~~

Para experimentar a aplicação que acabamos de criar, abra um navegador e acesse a URL `http://www.example.com/blog/index.php`. Devemos ver que a nossa aplicação esqueleto já tem quatro páginas totalmente funcionais: página inicial, sobre, página de contato e da página de login.

A seguir, descreveremos brevimente o que tem neste esqueleto de aplicação

###Script de Entrada

Nós temos um [script de entrada](http://www.yiiframework.com/doc/guide/basics.entry) no arquivo `/wwwroot/blog/index.php` que contém o seguinte conteúdo.

~~~
[php]
<?php
$yii='/wwwroot/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
eYii::createWebApplication($config)->run();
~~~

Este é o único script que os usuários podem acessar diretamente da Web. O script, primeiro inclue o arquivo Yii bootstrap `yii.php`. Em seguida cria uma instância da [application](http://www.yiiframework.com/doc/guide/basics.application) com a configuração especificada e executa a aplicação.


###Diretório Base da Aplicação

Nós também temos uma [diretório base da aplicação](http://www.yiiframework.com/doc/guide/basics.application#application-base-directory) `/wwwroot/blog/protected`. A maioria do nosso código e os dados serão colocados neste diretório, e deve ser protegido de acesso por usuários da web. Para [Apache httpd servidor Web](http://httpd.apache.org/), colocamos neste diretório um arquivo `.htaccess` com o seguinte conteúdo:

~~~
deny from all
~~~

Para outros servidores Web, consulte o manual correspondente sobre como proteger um diretório de ser acessada por usuários da web.


Fluxo de Trabalho da Aplicação
------------------------------

Para ajudar a compreender como funciona Yii, descrevemos o fluxo principal de nossa aplicação esqueleto quando um usuário está acessando a página de contato:

 0. O usuário solicita a `http://www.example.com/blog/index.php?r=site/contact`;
 1. O [script de entrada](http://www.yiiframework.com/doc/guide/basics.entry) é executado pelo servidor Web para processar o pedido;
 2. Um instância da [aplicação](http://www.yiiframework.com/doc/guide/basics.application) é criada e configurada com as propriedades e valores iniciais especificados na configuração do aplicativo no arquivo `/wwwroot/blog/protected/config/main.php`;
 3. O aplicativo resolve a solicitação em um [controlador](http://www.yiiframework.com/doc/guide/basics.controller) e uma [ação do controlador](http://www.yiiframework.com/doc/guide/basics.controller#action). Para a solicitação da página de contato, ele é resolvido como o `site` e controlador da ação `contact` (o `actionContact` método em `/wwwroot/blog/protected/controllers/SiteController.php`);
 4. O aplicativo cria o `site` controlador em termos de uma instância `SiteController` e, em seguida, executa-o;
 5. A instância `SiteController` executa a ação `contact` chamando seu método `actionContact()`;
 6. O método `actionContact` renderiza uma [visão](http://www.yiiframework.com/doc/guide/basics.view) chamada `contact` para o usuário. Internamente, isto é feito através da inclusão da exibição do arquivo `/wwwroot/blog/protected/views/site/contact.php` e incorporação do resultado ao [layout](http://www.yiiframework.com/doc/guide/basics.view#layout) do arquivo `/wwwroot/blog/protected/views/layouts/column1.php`.


<div class="revision">$Id: start.testdrive.txt 1734 2010-01-21 18:41:17Z qiang.xue $</div>