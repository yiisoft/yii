Trabalhando com formulários
===========================

Coletar dados do usuário através de formulários HTML é uma das principais tarefas 
no desenvolvimento de aplicativos Web. Além de conceder formulários, os desenvolvedores 
precisam preencher o formulário com dados existentes ou valores, validar entrada do 
usuário, exibir mensagens de erros apropriadas para uma entrada inválida, e salvar o 
entrada para o armazenamento persistente. Yii simplifica muito este trabalho com a 
sua arquitetura MVC.

Os seguintes passos são tipicamente necessários ao tratar os formulários em Yii:

   1. Crie uma classe modelo que representa os campos de dados a serem coletados;
   1. Crie um controlador de ação com o código que responde à submissão do formulário.
   1. Crie um arquivo de visualização do formulário associado com o controlador de ação.

Nas próximas subseções, descreveremos cada uma dessas etapas com mais detalhes.

<div class="revision">$Id: form.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>
