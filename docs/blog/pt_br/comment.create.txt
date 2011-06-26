Criando e Exibindo Comentários
==============================

Nesta seção, nós vamos implementar as funcionalidades de exibição e criação de comentários.

Para melhorar a interação com os usuários, nós vamos mostrá-los os possíveis erros cada vez que eles terminam de preencher um campo. Isto é conhecido como validação das entradas no lado do cliente. Nós vamos mostrar como isto pode ser feito no Yii de maneira perfeita e extremamente fácil. Note que isto requere a versão 1.1.1 or mais nova do Yii.


Exibindo Comentários
--------------------

Ao invés de exibir e criar comentários em páginas individuais, nós usamos a página de detalhe do post (gerada pela ação `view` de `PostController`). Abaixo da exibição do conteúdo do post, nós vamos exibir primeiro uma lista de comentários pertencentes a este post e então um formulário de criação de comentários.

Para exibir os comentários na página de detalhe do post, nós modificamos o scripi e visão `/wwwroot/blog/protected/views/post/view.php` como a seguir,

~~~
[php]
...visão do post aqui...

<div id="comments">
	<?php if($model->commentCount>=1): ?>
		<h3>
			<?php echo $model->commentCount . 'comentário(s)'; ?>
		</h3>

		<?php $this->renderPartial('_comments',array(
			'post'=>$model,
			'comments'=>$model->comments,
		)); ?>
	<?php endif; ?>
</div>
~~~

No código acima, nós chamamos `renderPartial()` para renderizar uma visão parcial de nome `_comments` para exibir a lista de comentários pertencentes ao post atual. Note que na visão nós usamos a expressão `$model->comments` para obter os comentários para o post. Isto é válido porque nós declaramos  um relacionamento chamado `comments` na classe `Post`. A avaliação desta expressão vai provocar uma consulta JOIN implícita à base de dados para retornar os comentários apropriados. Esta funcionalidade é conhecida como [lazy relational query](http://www.yiiframework.com/doc/guide/database.arr).

A visão parcial `_comments` não é muito interessante. Ela principalmente vai iterar entre os comentários e exibir os seus detalhes. Leitores interessados podem ler `/wwwroot/yii/demos/blog/protected/views/post/_comments.php`.


Criando Comentários
-------------------

Para manipular a criação de comentários, primeiro nós modificamos o método `actionView()` de `PostController` como a seguir,

~~~
[php]
public function actionView()
{
	$post=$this->loadModel();
	$comment=$this->newComment($post);

	$this->render('view',array(
		'model'=>$post,
		'comment'=>$comment,
	));
}

protected function newComment($post)
{
	$comment=new Comment;
	if(isset($_POST['Comment']))
	{
		$comment->attributes=$_POST['Comment'];
		if($post->addComment($comment))
		{
			if($comment->status==Comment::STATUS_PENDING)
				Yii::app()->user->setFlash('comentarioEnviado','Obrigado...');
			$this->refresh();
		}
	}
	return $comment;
}
~~~

No código acima, nos chamamos o método `newComment()` antes de renderizar a visão `view`. No método `newComment()`, nós criamos uma instância de `Comment` e verificamos se o formulário de comentários foi enviado. Se foi enviado, nós tentamos adicionar o comentário para o post chamando `$post->addComment($comment)`. Se isto der certo, nós atualizamos a página de detalhe do post. Caso o comentário precise ser aprovado, nós mostramos uma notificação para indicar esta decisão. Uma notificação é usualmente uma mensagem de confirmação exibida aos visitantes. Se o visitante clicar no botão "atualizar" do navegador, a mensagem desaparecerá.

Nós também precisamos modificar `/wwwroot/blog/protected/views/post/view.php`,

~~~
[php]
......
<div id="comments">
	......
	<h3>Deixe um Comentário</h3>

	<?php if(Yii::app()->user->hasFlash('comentarioEnviado')): ?>
		<div class="flash-success">
			<?php echo Yii::app()->user->getFlash('comentarioEnviado'); ?>
		</div>
	<?php else: ?>
		<?php $this->renderPartial('/comment/_form',array(
			'model'=>$comment,
		)); ?>
	<?php endif; ?>

</div><!-- comments -->
~~~

No código acima, nós exibimos a notificação caso ela exista. Se não, nós exibimos o formulário de entrada de comentários através da renderização da visão parcial `/wwwroot/blog/protected/views/comment/_form.php`.


Validação no Lado do Cliente
----------------------------

Para poder suportar a validação do formulário de comentários no lado do cliente, nós precisamos fazer algumas pequenas alterações na visão do formulário de comentários `/wwwroot/blog/protected/views/comment/_form.php` e no método `newComment()`.

No arquivo `_form.php`, nós precisamos principalmente configurar [CActiveForm::enableAjaxValidation] como "true" quando nós criamos o widget [CActiveForm]:

~~~
[php]
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'comment-form',
	'enableAjaxValidation'=>true,
)); ?>
......
<?php $this->endWidget(); ?>

</div><!-- form -->
~~~

E no método `newComment()`, nós inserimos um bloco de código para responder às requisições da validação via AJAX. O código verifica se há uma variável `POST` de nome `ajax`. Se houver, ele exibe os resultados da validação através de [CActiveForm::validate].

~~~
[php]
protected function newComment($post)
{
	$comment=new Comment;

	if(isset($_POST['ajax']) && $_POST['ajax']==='comment-form')
	{
		echo CActiveForm::validate($comment);
		Yii::app()->end();
	}

	if(isset($_POST['Comment']))
	{
		$comment->attributes=$_POST['Comment'];
		if($post->addComment($comment))
		{
			if($comment->status==Comment::STATUS_PENDING)
				Yii::app()->user->setFlash('comentarioEnviado','Obrigado...');
			$this->refresh();
		}
	}
	return $comment;
}
~~~

<div class="revision">$Id: comment.create.txt 2772 2010-12-24 16:24:12Z alexander.makarow $</div>