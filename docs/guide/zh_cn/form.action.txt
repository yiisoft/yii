创建动作
===============

有了模型，我们就可以开始编写用于操作此模型的逻辑了。
我们将此逻辑放在一个控制器的动作中。对登录表单的例子来讲，相应的代码就是：

~~~
[php]
public function actionLogin()
{
	$model=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// 收集用户输入的数据
		$model->attributes=$_POST['LoginForm'];
		// 验证用户输入，并在判断输入正确后重定向到前一页
		if($model->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// 显示登录表单
	$this->render('login',array('model'=>$model));
}
~~~

如上所示，我们首先创建了一个 `LoginForm` 模型示例；
如果请求是一个  POST 请求（意味着这个登录表单被提交了），我们则使用提交的数据 `$_POST['LoginForm']` 
填充 `$model` ；然后我们验证此输入，如果验证成功，重定向用户浏览器到之前需要身份验证的页面。
如果验证失败，或者此动作被初次访问，我们则渲染 `login` 视图，此视图的内容我们在下一节中讲解。

> Tip|提示: 在 `login` 动作中，我们使用 `Yii::app()->user->returnUrl` 获取之前需要身份验证的页面URL。
组件 `Yii::app()->user` 是一种 [CWebUser] (或其子类) ，它表示用户会话信息（例如 用户名，状态）。更多详情，
请参考 [验证与授权](/doc/guide/topics.auth).

让我们特别留意一下 `login` 动作中出现的下面的 PHP 语句：

~~~
[php]
$model->attributes=$_POST['LoginForm'];
~~~

正如我们在 [安全的特性赋值](/doc/guide/form.model#securing-attribute-assignments) 中所讲的，
这行代码使用用户提交的数据填充模型。
`attributes` 属性由 [CModel] 定义，它接受一个名值对数组并将其中的每个值赋给相应的模型特性。
因此如果 `$_POST['LoginForm']` 给了我们这样的一个数组，上面的那段代码也就等同于下面冗长的这段
(假设数组中存在所有所需的特性):

~~~
[php]
$model->username=$_POST['LoginForm']['username'];
$model->password=$_POST['LoginForm']['password'];
$model->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note|注意: 为了使 `$_POST['LoginForm']` 传递给我们的是一个数组而不是字符串，
我们需要在命名表单域时遵守一个规范。具体的，对应于模型类 `C` 中的特性 `a` 的表单域，我们将其命名为 
 `C[a]` 。例如，我们可使用 `LoginForm[username]` 命名
`username` 特性相应的表单域。

现在剩下的工作就是创建 `login` 视图了，它应该包含一个带有所需输入项的 HTML 表单。

<div class="revision">$Id: form.action.txt 1837 2010-02-24 22:49:51Z qiang.xue $</div>