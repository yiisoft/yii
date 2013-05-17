Modelo-Visão-Controle (MVC)
===========================

O Yii implementa o padrão de desenvolvimento modelo-visão-controle (MVC) que é
amplamente adotado na programação Web. O MVC visa separar a lógica de negócio
da interface com o usuário, assim os programadores podem mudar facilmente cada
parte, sem afetar as outras. No padrão MVC, o modelo representa as informações
(os dados) e as regras de negócio, a visão contém elemento de interface com o 
usuário, como textos, formulários, e o controle gerencia a comunicação entre
o modelo e a visão.

Além MVC, o Yii também introduz um controle de frente, chamado aplicação (application),
que representa o contexto de execução dos processos requisitados. A aplicação recebe
a solicitação do usuário e a envia para um controlador adequado para ser processada.

O diagrama seguinte mostra a estrutura estática de uma aplicação Yii:

![Estrutura estática de uma aplicação Yii](structure.png)


Um típico fluxo de execução
---------------------------
O diagrama a seguir mostra um típico fluxo de execução de uma aplicação Yii
quando esta está recebendo uma solicitação de um usuário

![Um típico fluxo de execução de uma aplicação Yii](flow.png)

   1. O usuário faz uma solicitação com a URL `http://www.exemplo.com/index.php?r=post/show&id=1`
e o servidor Web processa o pedido executando o script de bootstrap `index.php`.
   2. O script de bootstrap cria uma instancia de [aplicação](/doc/guide/basics.application) (application)
e a executa.
   3. A aplicação obtém o as informações detalhadas da solicitação de um
[componente da aplicação](/doc/guide/basics.application#application-component)
chamado `request`.
   4. A aplicação determina o [controle](/doc/guide/basics.controller)
e a [ação](/doc/guide/basics.controller#action) requerida com a ajuda do componente
chamado `urlManager`. Para este exemplo, o controle é `post` que se refere à classe `PostController` e
a ação é `show` cujo significado real é determinado no controle.
   5. A aplicação cria uma instancia do controle solicitado para poder lidar com a solicitação do
usuário. O controle determina que a ação `show` refere-se a um método chamado `actionShow` no
controle da classe. Em seguida, cria e executa filtros (por exemplo, o controle de acesso, benchmarking)
associados a esta ação. A ação só é executada se permitida pelos filtros.
   6. A ação le um [modelo](/doc/guide/basics.model) `Post` cujo ID é `1` no Banco de Dados.
   7. A ação processa a [visão](/doc/guide/basics.view) chamada `show`, com o `Post`.
   8. A visão apresenta os atributos do modelo `Post`.
   9. A visão executa alguns [widgets](/doc/guide/basics.view#widget).
   10. O resultado do processamento da visão é embutido em um [layout](/doc/guide/basics.view#layout).
   11. A ação conclui o processamento da visão e exibe o resultado ao usuário.


<div class="revision">$Id: basics.mvc.txt 1622 2009-12-26 20:56:05Z qiang.xue $</div>