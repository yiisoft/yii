控制器
==========

`控制器` 是 [CController] 或其子类的实例。它在当用户请求时由应用创建。
当一个控制器运行时，它执行所请求的动作，动作通常会引入所必要的模型并渲染相应的视图。
`动作` 的最简形式，就是一个名字以 `action` 开头的控制器类方法。

控制器通常有一个默认的动作。当用户的请求未指定要执行的动作时，默认动作将被执行。
默认情况下，默认的动作名为 `index`。它可以通过设置 [CController::defaultAction] 修改。

如下是一个控制器类所需的最简代码。由于此控制器未定义任何动作，对它的请求将抛出一个异常。

~~~
[php]
class SiteController extends CController
{
}
~~~


路由
-----

控制器和动作以 ID 识别。控制器 ID 是一种 'path/to/xyz' 的格式，对应相应的控制器类文件
`protected/controllers/path/to/XyzController.php`, 其中的标志 `xyz`
应被替换为实际的名字 (例如 `post` 对应 `protected/controllers/PostController.php`).
动作 ID 是除去 `action` 前缀的动作方法名。例如，如果一个控制器类含有一个名为 `actionEdit`
的方法，则相应的动作 ID 为 `edit`。

> Note|注意: 在 1.0.3 版本之前，控制器 ID 的格式为 `path.to.xyz` ，而不是 `path/to/xyz`。

用户以路由的形式请求特定的控制器和动作。路由是由控制器 ID 和动作 ID 连接起来的，两者以斜线分割。
例如，路由 `post/edit` 代表 `PostController` 及其 `edit` 动作。默认情况下，URL
`http://hostname/index.php?r=post/edit` 即请求此控制器和动作。

>Note|注意: 默认情况下，路由是大小写敏感的，从版本 1.0.1 开始，可以通过设置应用配置中的
> [CUrlManager::caseSensitive] 为 false 使路由对大小写不敏感。当在大小写不敏感模式中时，
>要确保你遵循了相应的规则约定，即：包含控制器类文件的目录名小写，且 [控制器映射|CWebApplication::controllerMap]
>和 [动作映射|CController::actions] 中使用的键为小写。

从 1.0.3 版本开始，应用可以含有 [模块（Module）](/doc/guide/basics.module). 模块中，控制器动作的路由格式为 `moduleID/controllerID/actionID` 。
更多详情，请阅读 [模块相关章节](/doc/guide/basics.module).


控制器实例化
------------------------

控制器实例在 [CWebApplication] 处理到来的请求时创建。指定了控制器 ID ，
应用将使用如下规则确定控制器的类以及类文件的位置。

   - 如果指定了 [CWebApplication::catchAllRequest] , 控制器将基于此属性创建，
而用户指定的控制器 ID 将被忽略。这通常用于将应用设置为维护状态并显示一个静态提示页面。

   - 如果在 [CWebApplication::controllerMap] 中找到了 ID, 相应的控制器配置将被用于创建控制器实例。

   - 如果 ID 为 `'path/to/xyz'`的格式，控制器类的名字将判断为 `XyzController`，
相应的类文件则为 `protected/controllers/path/to/XyzController.php`。例如，
控制器 ID `admin/user` 将被解析为控制器类 `UserController`，类文件是 `protected/controllers/admin/UserController.php`。
如果类文件不存在，将触发一个 404 [CHttpException] 异常。

在使用了 [模块](/doc/guide/basics.module) (1.0.3 版后可用) 后，上述过程则稍有不同。
具体来说，应用将检查此 ID 是否代表一个模块中的控制器。如果是的话，模块实例将被首先创建，然后创建模块中的控制器实例。


动作
------

如前文所述，动作可以被定义为一个以 `action` 单词作为前缀命名的方法。而更高级的方式是定义一个动作类并让控制器在收到请求时将其实例化。
这使得动作可以被复用，提高了可复用度。

要定义一个新动作类，可用如下代码：

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// place the action logic here
	}
}
~~~

为了让控制器注意到这个动作，我们要用如下方式覆盖控制器类的[actions()|CController::actions] 方法：

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

如上所示，我们使用了路径别名 `application.controllers.post.UpdateAction` 指定动作类文件为
 `protected/controllers/post/UpdateAction.php`.

通过编写基于类的动作，我们可以将应用组织为模块的风格。例如，
如下目录结构可用于组织控制器相关代码：

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~

### 动作参数绑定

从版本  1.1.4 开始，Yii 提供了对自动动作参数绑定的支持。
就是说，控制器动作可以定义命名的参数，参数的值将由 Yii 自动从 `$_GET` 填充。

为了详细说明此功能，假设我们需要为 `PostController` 写一个 `create` 动作。此动作需要两个参数：

* `category`: 一个整数，代表帖子（post）要发表在的那个分类的ID。
* `language`: 一个字符串，代表帖子所使用的语言代码。

从 `$_GET` 中提取参数时，我们可以不再下面这种无聊的代码了：

~~~
[php]
class PostController extends CController
{
	public function actionCreate()
	{
		if(isset($_GET['category']))
			$category=(int)$_GET['category'];
		else
			throw new CHttpException(404,'invalid request');

		if(isset($_GET['language']))
			$language=$_GET['language'];
		else
			$language='en';

		// ... fun code starts here ...
	}
}
~~~

现在使用动作参数功能，我们可以更轻松的完成任务：

~~~
[php]
class PostController extends CController
{
	public function actionCreate($category, $language='en')
	{
		$category=(int)$category;

		// ... fun code starts here ...
	}
}
~~~

注意我们在动作方法 `actionCreate` 中添加了两个参数。
这些参数的名字必须和我们想要从 `$_GET` 中提取的名字一致。
当用户没有在请求中指定 `$language` 参数时，这个参数会使用默认值 `en` 。
由于 `$category` 没有默认值，如果用户没有在 `$_GET` 中提供 `category` 参数，
将会自动抛出一个 [CHttpException] (错误代码 400) 异常。
Starting from version 1.1.5, Yii also supports array type detection for action parameters.
This is done by PHP type hinting using the syntax like the following:

~~~
[php]
class PostController extends CController
{
	public function actionCreate(array $categories)
	{
		// Yii will make sure $categories be an array
	}
}
~~~

That is, we add the keyword `array` in front of `$categories` in the method parameter
declaration. By doing so, if `$_GET['categories']` is a simple string, it will be
converted into an array consisting of that string.

> Note: If a parameter is declared without the `array` type hint, it means the parameter
> must be a scalar (i.e., not an array). In this case, passing in an array parameter via
> `$_GET` would cause an HTTP exception.



过滤器
------

过滤器是一段代码，可被配置在控制器动作执行之前或之后执行。例如，
访问控制过滤器将被执行以确保在执行请求的动作之前用户已通过身份验证；性能过滤器可用于测量控制器执行所用的时间。

一个动作可以有多个过滤器。过滤器执行顺序为它们出现在过滤器列表中的顺序。过滤器可以阻止动作及后面其他过滤器的执行

过滤器可以定义为一个控制器类的方法。方法名必须以 `filter` 开头。例如，现有的 `filterAccessControl` 方法定义了一个名为 `accessControl` 的过滤器。
过滤器方法必须为如下结构：

~~~
[php]
public function filterAccessControl($filterChain)
{
	// 调用 $filterChain->run() 以继续后续过滤器与动作的执行。
}
~~~

其中的 `$filterChain` (过滤器链)是一个 [CFilterChain] 的实例，代表与所请求动作相关的过滤器列表。在过滤器方法中，
我们可以调用 `$filterChain->run()` 以继续执行后续过滤器和动作。

过滤器也可以是一个 [CFilter] 或其子类的实例。如下代码定义了一个新的过滤器类：

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// 动作被执行之前应用的逻辑
		return true; // 如果动作不应被执行，此处返回 false
	}

	protected function postFilter($filterChain)
	{
		// 动作执行之后应用的逻辑
	}
}
~~~

要对动作应用过滤器，我们需要覆盖
`CController::filters()` 方法。此方法应返回一个过滤器配置数组。例如：

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

上述代码指定了两个过滤器： `postOnly` 和 `PerformanceFilter`。
`postOnly` 过滤器是基于方法的（相应的过滤器方法已在 [CController] 中定义）；
而 `performanceFilter` 过滤器是基于对象的。路径别名 `application.filters.PerformanceFilter`
指定过滤器类文件是 `protected/filters/PerformanceFilter`。我们使用一个数组配置
`PerformanceFilter` ，这样它就可被用于初始化过滤器对象的属性值。此处 `PerformanceFilter` 的 `unit` 属性值将被初始为 `second`。

使用加减号，我们可指定哪些动作应该或不应该应用过滤器。上述代码中， `postOnly`
应只被应用于 `edit` 和 `create` 动作，而 `PerformanceFilter` 应被应用于 除了 `edit` 和 `create` 之外的动作。
如果过滤器配置中没有使用加减号，则此过滤器将被应用于所有动作。

<div class="revision">$Id: basics.controller.txt 2418 2010-09-02 16:01:46Z qiang.xue, translated by riverlet$</div>