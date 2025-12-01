Path Alias e Namespace
======================

O Yii utiliza path aliases (apelidos para caminhos) extensivamente. Um path alias, é um apelido associado 
ao caminho de um diretório ou arquivo.Um path alias utiliza a sintaxe de ponto para separar seus itens, similar a forma largamente adotada em namespaces:


~~~
RootAlias.path.to.target
~~~

Onde `RootAlias` é o nome de um diretório existente. Ao executar o método [YiiBase::setPathOfAlias()], 
podemos definir novos apelidos para caminhos. Por conveniência, o Yii já possui predefinidos os seguintes apelidos: 

- `system`: refere-se ao diretório do Yii framework;
- `application`: refere-se ao [diretório base](/doc/guide/basics.application#application-base-directory) da aplicação;
- `webroot`: refere-se ao diretório que contém o arquivo do [script de entrada](/doc/guide/basics.entry). Esse apelido está disponível desde a versão 1.0.3.
- `ext`: refere-se ao diretório que contém todas as [extensões](/doc/guide/extension.overview) de terceiros. Esse apelido está 
disponível desde a versão 1.0.8.

Além disso, se a aplicação utiliza [módulos](/doc/guide/basics.module), um apelido de diretório raiz (root alias) é predefinido para cada módulo, apontando para o diretório base do módulo correspondente. Esta funcionalidade está disponível desde a versão 1.0.3.

Ao usar o método [YiiBase::getPathOfAlias()], um apelido pode ser traduzido para o seu 
caminho correspondente. Por exemplo, `system.web.CController` seria 
traduzido para `yii/framework/web/CController`.

A utilização de apelidos é muito conveniente para importar a definição de uma classe.
Por exemplo, se quisermos incluir a definição da classe [CController]
podemos fazer o seguinte:

~~~
[php]
Yii::import('system.web.CController');
~~~

O método [import|YiiBase::import] é mais eficiente que o `include` e o `require` do PHP.
Com ele, a definição da classe que está sendo importada 
não é incluída até que seja referenciada pela primeira vez. Importar
o mesmo namespace várias vezes, também é muito mais rápido do que utilizar o `include_once`
e o `require_once`.

> Tip|Dica: Quando referenciamos uma das classes do Yii Framework, não precisamos 
importa-la ou inclui-la. Todas as classes Yii são pré-importadas.

Podemos também utilizar a seguinte sintaxe para importar todo um diretório de uma só vez, de forma que 
os arquivos de classe dentro dele sejam automaticamente incluídos, quando necessário.


~~~
[php]
Yii::import('system.web.*');
~~~

Além do método [import|YiiBase::import], apelidos são utilizados em vários outros 
locais para se referir a classes. Por exemplo, um apelido pode ser passado para o método 
[Yii::createComponent()] para criar uma instância da classe informada,
mesmo que o arquivo da classe ainda não tenha sido incluído.

Não confunda um path alias com um namespace. Um namespace refere-se a um agrupamento lógico
de nomes de classes para que eles possam ser diferenciadas de outros 
nomes das classes, mesmo que eles sejam iguais. Já um path alias é utilizado para 
referenciar um arquivo de classe ou um diretório. Um path alias não conflita com um 
namespace.

> Tip|Dica: Como o PHP, antes da versão 5.3.0, não dá suporte a namespaces, 
você não pode criar instâncias de duas classes que tenham o mesmo 
nome, mas definições diferentes. Por isso, todas as classes do Yii framework 
são prefixadas com uma letra "C" (que significa 'class'), de modo que elas possam 
ser diferenciadas das classes definidas pelo usuário. Recomenda-se que o 
prefixo "C" seja reservado somente para utilização do Yii framework, e que classes criadas pelos usuário 
sejam prefixadas com outras letras.

<div class="revision">$Id: basics.namespace.txt 1400 2009-09-07 12:45:17Z qiang.xue $</div>
