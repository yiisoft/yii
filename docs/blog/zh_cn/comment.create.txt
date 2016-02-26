评论的创建与显示
================================

此节中，我们实现评论的创建与显示功能。

为增强用户交互体验，我们打算在用户输入完每个表单域时就提示用户可能的出错信息。也就是客户端输入验证。我们将看到，在Yii中实现这个是多么简单多么爽。注意，这需要 Yii 1.1.1 版或更高版本的支持。


评论的显示
-------------------

我们使用日志详情页(由 `PostController` 的  `view`  动作生成)来显示和创建评论，而不是使用单独的页面。在日志内容下面，我们首先显示此日志的评论列表，然后显示一个用于创建评论的表单。

为了在日志详情页中显示评论，我们把视图脚本 `/wwwroot/blog/protected/views/post/view.php` 修改如下：

~~~
[php]
...这儿是日志的视图...

<div id="comments">
	<?php if($model->commentCount>=1): ?>
		<h3>
			<?php echo $model->commentCount . 'comment(s)'; ?>
		</h3>

		<?php $this->renderPartial('_comments',array(
			'post'=>$model,
			'comments'=>$model->comments,
		)); ?>
	<?php endif; ?>
</div>
~~~

如上所示，我们调用了 `renderPartial()` 方法渲染一个名为 `_comments` 的局部视图以显示属于当前日志的评论列表。注意，在这个视图中我们使用了表达式 `$model->comments` 获取日志的评论。这是有效的，因为我们在 `Post` 类中声明了一个 `comments` 关系。执行此表达式会触发一个隐式的 JOIN 数据库查询以获取相应的评论数据。此特性被称为 [懒惰的关系查询（lazy relational query）](http://www.yiiframework.com/doc/guide/database.arr)。

局部视图 `_comments` 没有太多有意思的。它主要用于遍历每条评论并显示其详情。感兴趣的读者可以参考 `/wwwroot/yii/demos/blog/protected/post/_comments.php`.


评论的创建
-----------------

要处理评论创建，我们首先修改 `PostController` 中的 `actionView()` 方法如下：

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
				Yii::app()->user->setFlash('commentSubmitted','Thank you...');
			$this->refresh();
		}
	}
	return $comment;
}
~~~

如上所示，我们在渲染 `view` 前调用了 `newComment()` 方法。在 `newComment()` 方法中，我们创建了一个 `Comment` 实例并检查评论表单是否已提交。如果已提交，我们尝试通过调用 `$post->addComment($comment)` 添加日志评论。如果一切顺利，我们刷新详情页面。由于评论需要审核，我们将显示一条闪过信息(flash message)以作出提示。闪过信息通常是一条显示给最终用户的确认信息。如果用户点击了浏览器的刷新按钮，此信息将会消失。

此外，我们还需要修改 `/wwwroot/blog/protected/views/post/view.php` ，

~~~
[php]
......
<div id="comments">
	......
	<h3>Leave a Comment</h3>

	<?php if(Yii::app()->user->hasFlash('commentSubmitted')): ?>
		<div class="flash-success">
			<?php echo Yii::app()->user->getFlash('commentSubmitted'); ?>
		</div>
	<?php else: ?>
		<?php $this->renderPartial('/comment/_form',array(
			'model'=>$comment,
		)); ?>
	<?php endif; ?>

</div><!-- comments -->
~~~

以上代码中，如果有可用的闪过信息，我们就会显示它。如果没有，我们就通过渲染局部视图  `/wwwroot/blog/protected/views/comment/_form.php` 显示评论输入表单。


客户端验证
----------------------

为支持评论表单的客户端验证，我们需要对评论表单视图 `/wwwroot/blog/protected/views/comment/_form.php` 和 `newComment()` 方法做一些小的修改。

在 `_form.php` 文件中，我们主要需要在创建 [CActiveForm] 小物件时设置 [CActiveForm::enableAjaxValidation] 为 true：

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

在 `newComment()` 方法中，我们插入了一段代码以响应 AJAX 验证请求。这段代码检查是否存在一个名为 `ajax` 的 `POST` 变量，如果存在，它将通过调用 [CActiveForm::validate] 显示验证结果。

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
				Yii::app()->user->setFlash('commentSubmitted','Thank you...');
			$this->refresh();
		}
	}
	return $comment;
}
~~~

<div class="revision">$Id: comment.create.txt 1753 2010-01-25 18:25:03Z qiang.xue $</div>