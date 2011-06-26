Fluxo de trabalho do desenvolvimento
====================================

Após descrever os conceitos fundamentais do Yii, mostraremos o fluxo de trabalho 
do desenvolvimento de uma aplicação web utilizando o Yii. Esse fluxo assume que 
já realizamos a análise de requisitos, bem como a análise de design para a 
aplicação.

   1. Crie o esqueleto da estrutura de diretórios. A ferramenta `yiic`, descrita 
em [criando a primeira aplicação Yii](/doc/guide/quickstart.first-app) pode ser 
utilizada para agilizar este passo.

   2. Configure a [aplicação](/doc/guide/basics.application). Faça isso alterando 
o arquivo de configuração da aplicação. Neste passo, também pode ser necessário 
escrever alguns componentes da aplicação (por exemplo, o componente de usuário).

   3. Crie um [modelo](/doc/guide/basics.model) para cada tipo de dado a ser 
gerenciado. Novamente, podemos utilizar a `yiic` para gerar automaticamente as 
classes [active record](/doc/guide/database.ar) para cada tabela importante do 
banco de dados.

   4. Crie uma classe de [controle](/doc/guide/basics.controller) para cada tipo
de requisição do usuário. A classificação dessas requisições depende dos requisitos 
da aplicação. No geral, se um modelo precisa ser acessado pelos usuário, ele deve 
ter um controle correspondente. A ferramenta `yiic` pode automatizar este passo 
para você.

   5. Implemente [ações](/doc/guide/basics.controller#action) e as 
[visões](/doc/guide/basics.view). Aqui é onde o trabalho de verdade precisa ser feito.

   6. Configure os [filtros](/doc/guide/basics.controller#filter) de ações necessários nas classes dos controles.

   7. Crie [temas](/doc/guide/topics.theming), se esta funcionalidade for necessária.
   
   8. Crie mensagens traduzidas se [internacionalização](/doc/guide/topics.i18n) for necessária.
   
   9. Identifique dados e visões que possam ser cacheadas e aplique as técnicas 
apropriadas de [caching](/doc/guide/caching.overview).

   10. Finalmente, faça [ajustes de desempenho](/doc/guide/topics.performance) e a implantação.
   
Para cada um dos passos acima, testes devem ser criados e executados.

<div class="revision">$Id: basics.workflow.txt 1034 2009-05-19 21:33:55Z qiang.xue $</div>
