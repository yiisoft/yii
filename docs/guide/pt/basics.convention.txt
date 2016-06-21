Convenções
===========

O Yii favorece convenções sobre configurações. Siga as convenções e você 
poderá criar aplicações sofisticadas sem ter que escrever ou gerenciar 
configurações complexas. Evidentemente, o Yii ainda podem ser personalizados em quase
todos os aspectos, com configurações, quando necessário.

Abaixo descrevemos convenções que são recomendadas para programar com o Yii. 
Por conveniência, assumimos que `WebRoot` é o diretório onde está instalada uma aplicação desenvolvida com o Yii framework.


URL
---

Por padrão, o Yii reconhece URLs com o seguinte formato:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

A variável `r`, passada via GET, refere-se a 
[rota](/doc/guide/basics.controller#route), que pode ser interpretada pelo Yii 
como um controle e uma ação. Se o id da ação (`ActionID`) for omitido, o controle irá 
utilizar a ação padrão (definida através da propriedade [CController::defaultAction]); e se 
o id do controle (`ControllerID`) também for omitido (ou a variável `r` estiver ausente), a
aplicação irá utilizar o controle padrão (definido através da propriedade 
[CWebApplication::defaultController]).

Com a ajuda da classe [CUrlManager], é possível criar e reconhecer 
URLs mais amigáveis, ao estilo SEO, tais como 
`http://hostname/ControllerID/ActionID.html`. Esta funcionalidade é abordada em 
detalhes em [Gerenciamento de URL](/doc/guide/topics.url).

Código
----

O Yii recomenda que nomes de variáveis, funções e nomes de classe sejam escritos no formato Camel Case, 
onde inicia-se cada palavra com letra maiúscula e junta-se todas, sem espaços entre elas.
Variáveis e nomes de funções devem ter a sua primeira palavra totalmente em letras minúsculas, 
a fim de diferencia-los dos nomes das classes (por exemplo, `$basePath`,
`runController()`, `LinkPager`). Para as variáveis privadas membros de classe, 
é recomendado prefixar seus nomes com um underscore (por exemplo,
`$_actionList`).

Como não há suporte a namespaces antes do PHP 5.3.0, é recomendado 
que as classes sejam denominadas de uma forma única, para evitar conflitos com nomes de 
classes de terceiros. Por esta razão, todas as classes do Yii framework são
prefixadas com a letra "C".

Existe uma regra especial para as classes de controle, onde deve-se adicionar
o sufixo `Controller` ao nome da classe. O ID do controle é, então, definido como o nome
da classe, com a primeira letra minúscula, e a palavra `Controller` removida.
Por exemplo, a classe `PageController` terá o ID `page`. Esta regra
torna a aplicação mais segura. Também deixa mais limpas as URLs relacionados aos 
controles (por exemplo, `/index.php?r=page/index` em vez de 
`/index.php?r=PageController/index`).

Configuração
-------------

A configuração é um vetor de pares chave-valor. Cada chave representa o 
nome de uma propriedade do objeto a ser configurado, e cada valor, o 
valor inicial da propriedade correspondente. Por exemplo, `array('name'=>'Minha 
aplicação', 'basePath'=>'/protected')` inicializa as propriedades `name` e 
`basePath` com os valores correspondentes no vetor.

Qualquer propriedades "alterável" de um objeto pode ser configurada. Se não forem configuradas, 
as propriedades assumirão seus valores padrão. Ao configurar uma propriedade,
vale a pena ler a documentação correspondente, para que o
valor inicial seja configurado corretamente.

Arquivo
----

As convenções para nomenclatura e utilização de arquivos dependem seus tipos.

Arquivos de classe devem ser nomeados de acordo com a classe pública que contém. Por 
exemplo, a classe [CController] está no arquivo `CController.php`. Uma 
classe pública é uma classe que pode ser utilizada por qualquer outra. Cada arquivo de 
classe deve conter, no máximo, uma classe pública. Classes privadas (aquelas 
que são utilizadas apenas por uma única classe pública) podem residir no mesmo arquivo com 
a classe que a utiliza.

Os arquivos das visões devem ser nomeados de acordo com o seus nomes. Por exemplo, a visão `index` 
está no arquivo `index.php`. O arquivo de uma visão contém um script 
com código HTML e PHP, utilizado, principalmente para apresentação de conteúdo.

Arquivos de configuração podem ser nomeadas arbitrariamente. Um arquivo de configuração é um 
script em PHP cuja única finalidade é a de retornar um vetor associativo 
representando a configuração.

Diretório
---------

O Yii assume um conjunto predefinido de diretórios utilizados para diversas finalidades. Cada um 
deles pode ser personalizado, se necessário.

- `WebRoot/protected`: este é o [diretório base 
da aplicação](/doc/guide/basics.application#application-base-directory), onde estão todos os
scripts PHP que precisão estar seguros e os arquivos de dados. O Yii tem um apelido (alias) padrão 
chamado `application`, associado a este caminho. Este diretório, e 
tudo dentro dele, deve estar protegido para não ser acessado via web. Ele
pode ser alterado através da propriedade [CWebApplication::basePath].

- `WebRoot/protected/runtime`: este diretório armazena arquivos privados temporários 
gerados durante a execução da aplicação. Este diretório deve ter 
permissão de escrita para o processo do servidor Web. Ele pode ser alterado através da 
propriedade [CApplication::runtimePath].

- `WebRoot/protected/extensions`: este diretório armazena todas as extensões 
de terceiros. Ele pode ser alterado através da propriedade [CApplication::extensionPath].

- `WebRoot/protected/modules`: este diretório contém todos os 
[módulos](/doc/guide/basics.module) da aplicação, cada um representado como um subdiretório. 

- `WebRoot/protected/controllers`: neste diretório estão os arquivos de classe 
de todos os controles. Ele pode ser alterado através da propriedade [CWebApplication::controllerPath].

- `WebRoot/protected/views`: este diretório possui todos os arquivos das visões,
incluindo as visões dos controles, visões do layout e visões do sistema. Ele pode ser alterado 
através da propriedade [CWebApplication::viewPath].

- `WebRoot/protected/views/ControllerID`: neste diretório estão os arquivos das visões 
para um controle específico. Aqui, `ControllerID` é o ID 
do controle. Ele pode ser alterado através da propriedade [CController::viewPath].

- `WebRoot/protected/views/layouts`: este diretório possui todos os arquivos de visão 
do layout. Ele pode ser alterado através da propriedade [CWebApplication::layoutPath].

- `WebRoot/protected/views/system`: este diretório mantém todos os arquivos 
de visões do sistema. Visões do sistema são templates utilizados para exibir exceções e 
erros. Ele pode ser alterado através da propriedade [CWebApplication::systemViewPath].

- `WebRoot/assets`: este diretório mantém os assets publicados. Um 
asset é um arquivo privado que pode ser publicado para se tornar acessível aos 
usuários, via web. Este diretório deve ter permissão de escrita para o processo do servidor Web. Ele pode ser 
alterado através da propriedade [CAssetManager::basePath].

- `WebRoot/themes`: este diretório armazena vários temas que podem ser 
aplicados à aplicação. Cada subdiretório representa um único tema 
cujo nome é o nome do tema. Ele pode ser alterado através da propriedade 
[CThemeManager::basePath].

Banco de Dados
--------------

A maioria das aplicações web utilizam algum tipo de banco de dados. Como boa prática,
propomos as seguintes convenções para a criação de nomes de tabelas e colunas. Note
que nenhuma delas é obrigatória para a utilização do Yii.

 - Nomes de tabelas e colunas devem utilizar apenas letras minúsculas.

 - As palavras de um nome devem ser separadas por underscores (ex.: `product_order`).

 - Para as tabelas, você pode utilizar nomes no singular ou no plural, mas nunca
ambos ao mesmo tempo. Para simplificar, recomendamos a utilização de nomes no singular.

 - Os nomes das tabelas devem ser prefixados com um token como, por exemplo,
`tbl_`. Isso é especialmente útil em casos onde as tabelas de uma aplicação estão
no mesmo banco de dados utilizado por tabelas de outra aplicação. Assim, os dois
conjuntos de tabelas podem ser lidos separadamente utilizando os prefixos dos nomes
das tabelas.

<div class="revision">$Id: basics.convention.txt 2345 2010-08-28 12:51:08Z mdomba $</div>

<div class="revision">$Id: basics.convention.txt 749 2009-02-26 02:11:31Z qiang.xue $</div>
