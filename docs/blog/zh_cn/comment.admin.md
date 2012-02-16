评论管理
=================

评论管理包括更新，删除和审核。这些操作是以 `CommentController` 类的动作实现的。


评论的更新与删除
------------------------------

由 `yiic` 生成的更新及删除评论的代码大部分都不需要修改。


评论审核
------------------

当评论刚创建时，它们处于待审核状态，需要等审核通过后才会显示给访客。审核评论主要就是修改评论的状态（status）列。

我们创建一个 `CommentController` 中的 `actionApprove()` 方法如下：

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

如上所示，当 `approve` 动作通过一个 POST 请求被调用时，我们执行了 `Comment` 模型中定义的 `approve()` 方法改变评论状态。然后我们重定向用户浏览器到显示此评论所属日志的页面。

我们还修改了 `Comment` 的  `actionIndex()`  方法以显示所有评论。我们希望看到等待审核的评论显示在前面。

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

注意，在上面的代码中，由于 `tbl_post` 和 `tbl_comment` 表都含有 `status` 和 `create_time` 列，我们需要通过使用表的别名前缀消除列名的歧义。 正如 [指南](http://www.yiiframework.com/doc/guide/database.arr#disambiguating-column-names) 中所描述的，在一个关系查询中，主表的别名总是使用 `t`。因此，我们在上面的代码中对 `status` 和 `create_time` 使用了 `t` 前缀。

和日志的索引视图（index view）类似， `CommentController` 的 `index` 视图使用 [CListView] 显示评论列表， [CListView]  又使用了局部视图 `/wwwroot/blog/protected/views/comment/_view.php` 显示每一条评论。此处我们不打算深入讲解。读者可参考博客演示中相应的文件 `/wwwroot/yii/demos/blog/protected/views/comment/_view.php`.

<div class="revision">$Id: comment.admin.txt 1810 2010-02-18 00:24:54Z qiang.xue $</div>