Gerenciamento de Comentários
============================

Gerenciamento de comentários inclui atualização, exclusão e aprovação. Estas operação são implementadas como ações da Classe `CommentController`.


Atualização e Exclusão de Comentários
-------------------------------------

O código gerado pelo `yiic` para atualização e exclusão de comentários permanece praticamente inalterado.


Aprovação de Comentários
------------------------

Quando os comentários são criados, eles estão em um estado pendente de aprovação e precisam ser aprovados para ficarem visível aos visitantes. Aprovar um comentário é basicamente a mudança da coluna status do comentário

Nós criamos um método `actionApprove()` na classe `CommentController` veja abaixo,

~~~
[php]
public function actionApprove()
{
	if(Yii::app()->request->isPostRequest)
	{
		$comment=$this->loadModel();
		$comment->approve();
		$this->redirect(array('index'));
	}
	else
		throw new CHttpException(400,'Invalid request...');
}
~~~

No exemplo acima, quando a ação `approve` é invocada através de uma solicitação POST, que chamamos de método `approve()` definida no modelo `Comment` para alterar o status. Em seguida, redirecionamos o navegador do usuário para a página que exibe o post que pertence a este comentário.

Nós também modificamos o método `actionIndex()` de `Comment` para exibir todos os comentários. Gostariamos de exibir os comentários pendentes de aprovação primeiro.

~~~
[php]
public function actionIndex()
{
	$dataProvider=new CActiveDataProvider('Comment', array(
		'criteria'=>array(
			'with'=>'post',
			'order'=>'t.status, t.create_time DESC',
		),
	));

	$this->render('index',array(
		'dataProvider'=>$dataProvider,
	));
}
~~~

Repare no código acima, porque ambas `tbl_post` e `tbl_comment` tem as colunas `status` e `create_time`, precisamos remover esta ambiguidade das colunas correspondentes prefixando um alias (pseudônimo) da tabela. Como descrito no [Guia definitivo do Yii](http://www.yiiframework.com/doc/guide/database.arr#disambiguating-column-names),o alias para a tabela primária em uma consulta relacional é sempre `t`. Portanto, estamos prefixando `t` as colunas `status` e `create_time` no código acima.

Como o post index view, a visão `index` de `CommentController` usa [CListView] para exibir a lista de comentário que por sua vez utiliza a visão parcial `/wwwroot/blog/protected/views/comment/_view.php` para mostrar os detalhes de cada comentário. Não vou entrar em detalhes aqui. Os leitores interessados podem consultar o arquivo correspondente no demo blog `/wwwroot/yii/demos/blog/protected/views/comment/_view.php`.

<div class="revision">$Id: comment.admin.txt 1810 2010-02-18 00:24:54Z qiang.xue $</div>