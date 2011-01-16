Criando um Formulário
=====================

Escrever a visão `login` é algo bem simples. Devemos começar com uma tag 
`form`, cujo atributo action deve ser a URL da ação `login`, descrita 
anteriormente. Em seguida inserimos os rótulos e os campos para os atributos 
declarados na classe `LoginForm`. Por fim, inserimos um botão de envio (submit) 
que pode ser utilizado pelos usuários para enviar o formulário. Tudo isso pode 
ser feito puramente com HTML.

O Yii fornece algumas classes auxiliares para facilitar a composição da visão. 
Por exemplo, para criar um caixa de texto, podemos utilizar o método 
[CHtml::textField()]; para criar uma lista do tipo drop-down, utilizamos 
[CHtml::dropDownList()].

> Info|Informação: Você deve estar se perguntando qual a vantagem de se utilizar 
> uma classe auxiliar, se elas utilizam a mesma quantidade de código do que o 
> equivalente em HTML. A resposta é que as classes auxiliares geram mais 
> do que somente código HTML. Por exemplo, o código a seguir gera uma caixa de texto 
> que dispara o envio do formulário caso seu valor seja alterado pelo usuário:
>
> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
>
> Se não fosse assim, seria necessário um monte de código em JavaScript espalhado.

No exemplo a seguir, utilizamos a classe [CHtml] para criar o formulário de login. 
Assumimos que a variável `$model` representa uma instância de `LoginForm`.

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>

	<?php echo CHtml::errorSummary($model); ?>

	<div class="row">
		<?php echo CHtml::activeLabel($model,'username'); ?>
		<?php echo CHtml::activeTextField($model,'username') ?>
	</div>

	<div class="row">
		<?php echo CHtml::activeLabel($model,'password'); ?>
		<?php echo CHtml::activePasswordField($model,'password') ?>
	</div>

	<div class="row rememberMe">
		<?php echo CHtml::activeCheckBox($model,'rememberMe'); ?>
		<?php echo CHtml::activeLabel($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

Esse código gera um formulário mais dinâmico. Por exemplo, o método [CHtml::activeLabel()] 
gera um rótulo associado ao atributo do modelo especificado. Se o ocorrer um erro 
com a validação desse atributo, a classe CSS do rótulo será alterada para `error`, 
o que mudará a aparência do rótulo. Da mesma forma, o método [CHtml::activeTextField()] 
gera uma caixa de texto para o atributo especificado e, também, altera sua 
classe CSS na ocorrência de erros.

Se utilizarmos o arquivo css `form.css`, fornecido pelo script do `yiic`, o 
formulário gerado terá a seguinte aparência:

![A Página de Login](login1.png)

![A Página de Login com Erros](login2.png)

A partir da versão 1.1.1, existe um novo widget chamado [CActiveForm], que pode
ser utilizado para facilitar a criação de formulários. Esse widget é capaz de
realizar validações de forma consistente e transparente, tanto do lado do cliente,
quanto do lado do servidor. Utilizando o [CActiveForm], o código do último
exemplo pode ser reescrito da seguinte maneira:

~~~
[php]
<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->label($model,'username'); ?>
		<?php echo $form->textField($model,'username') ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'password'); ?>
		<?php echo $form->passwordField($model,'password') ?>
	</div>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
~~~

<div class="revision">$Id: form.view.txt 1751 2010-01-25 17:21:31Z qiang.xue $</div>
