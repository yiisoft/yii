Novos recursos
============

Esta página resume as principais novidades introduzidas em cada versão do Yii.

Versão 1.1.8
-------------
 * [Adicionando suporte para o uso customizado de regras URLs em classes](/doc/guide/topics.url#using-custom-url-rule-classes)

Versão 1.1.7
-------------
 * [Adicionado suporte para URL RESTful](/doc/guide/topics.url#user-friendly-urls)
 * [Adicionado suporte de cache para consultas](/doc/guide/caching.data#query-caching)
 * [Agora é possível passar parâmetros para uma requisição relacional](/doc/guide/database.arr#relational-query-with-named-scopes)
 * [Adicionado capacidade para realizar consultas relacionais sem ter modelos relacionados](/doc/guide/database.arr#performing-relational-query-without-getting-related-models)
 * [Adicionado suporte para HAS_MANY através de HAS_ONE com relacionamento AR](/doc/guide/database.arr#relational-query-with-through)
 * [Adicionado suporte para transações no recurso de migração de banco de dados](/doc/guide/database.migration#transactional-migrations)
 * [Adicionado suporte para a utilização de parâmetros de ligação com class-based actions](/doc/guide/basics.controller#action-parameter-binding)
 * Adicionado suporte para validação de dados no cliente com [CActiveForm]

 Versão 1.1.6
-------------
 * [Adicionado query builder](/doc/guide/database.query-builder)
 * [Adicionado database migration](/doc/guide/database.migration)
 * [Melhores práticas MVC](/doc/guide/basics.best-practices)
 * [Adicionado suporte para o uso de parâmetros anônimos e opções globais em comandos do console](/doc/guide/topics.console)

Versão 1.1.5
-------------

 * [Adicionado suporte para as ações e parâmetros de ação nos comandos do console](/doc/guide/topics.console)
 * [Adicionado suporte para autoloading de classes com namespace](/doc/guide/basics.namespace)
 * [Adicionado suporte para temas em widget com views](/doc/guide/topics.theming#theming-widget-views)

Versão 1.1.4
-------------

 * [Adicionado suporte para automática ligação de parâmetros de ações](/doc/guide/basics.controller#action-parameter-binding)

Versão 1.1.3
-------------

 * [Adicionado suporte para configurar valores padrões em widgets na configuração da aplicação](/doc/guide/topics.theming#customizing-widgets-globally)

Versão 1.1.2
-------------

 * [Adicionado ferramenta Web para geração de código chamada Gii](/doc/guide/topics.gii)

Versão 1.1.1
-------------

 * Adicionado CActiveForm que simplifica a escrita de códigos de formulários com suporte a validação de 
 dados em ambos aos lados, cliente e servidor.
 
 * Refatorado o código gerado pela ferramenta yiic. Em particular, o esqueleto
 da aplicação é agora gerado com vários layouts, o menu de operação foi
 reorganizado para páginas CRUD; adicionado recurso de pesquisa e filtragem para a página admin
 gerada pelo comando CRUD; usado CActiveForm para renderizar o formulário
 
 * [Adicionado suporte para permitir definição global de comandos yiic](/doc/guide/topics.console)

Versão 1.1.0
-------------

 * [Adicionado suporte para escrever testes unitários](/doc/guide/test.overview).

 * [Adicionado suporte para usar skins em widget](/doc/guide/topics.theming#skin).

 * [Adicionado um construtor de formulários extensível](/doc/guide/form.builder).

 * Melhorado a forma de declarar atributos seguros no modelo. Veja
 [Securing Attribute Assignments](/doc/guide/form.model#securing-attribute-assignments).

 * Modificado o padrão do algoritmo de carregamento relacional em consultas Active Record,
 de modo que todas as tabelas são unidas em uma única instrução SQL. 

 * Modificado o alias padrão de tabelas para ser o nome do relacionamento active record.

 * [Adicionado suporte para uso de prefix em tabelas](/doc/guide/database.dao#using-table-prefix).

 * Adicionado um conjunto de novas extensões conhecido como o [Zii library](http://code.google.com/p/zii/).

 * O nome do alias para a tabela principal em uma consulta AR é fixado para ser 't'.

<div class="revision">$Id: changes.txt 3235 2011-05-24 18:54:01Z qiang.xue $</div>
