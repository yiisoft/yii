错误处理
==============

Yii提供了一个完整的，基于PHP5异常处理的错误处理机制。当一个应用开始运行，进行用户请求的处理的时候，会注册[handleError|CApplication::handleError]方法来处理PHP warnings和notices信息；同时也注册加[handleException|CApplication::handleException]方法来处理未被捕获的PHP异常。因此，如果在应用运行期间出现一个PHP warning/notice 或者一个未捕获的PHP异常，错误处理器就会接过控制权来运行必要的处理机制。

> Tip|提示: 错误处理器的注册是在应用中的constructor方法中进行的，使用了PHP函数[set_exception_handler](http://www.php.net/manual/en/function.set-exception-handler.php)
和[set_error_handler](http://www.php.net/manual/en/function.set-error-handler.php)。
如果你不想让Yii来处理错误和异常，你可以在[入口文件](/doc/guide/basics.entry)中定义`YII_ENABLE_ERROR_HANDLER`和`YII_ENABLE_EXCEPTION_HANDLER`为false.

默认情况下，在触发[onError|CApplication::onError]事件（或[onException|CApplication::onException]事件）的时候，[errorHandler|CApplication::errorHandler]（或[exceptionHandler|CApplication::exceptionHandler]）将被触发。如果错误或者异常未被任何事件所处理，那么就需要运行[errorHandler|CErrorHandler]组件来处理了。

引发异常
------------------

在Yii中引发异常和在普通PHP文件中没什么两样。你可以使用下面的代码来抛出异常：

~~~
[php]
throw new ExceptionClass('错误信息');
~~~

Yii定义了两个异常类：[CException]和[CHttpException]。前者是一个通用的异常类，而后者用于对最终用户显示异常信息。同时，后者有一个[statusCode|CHttpException::statusCode]属性来代表HTTP状态码。异常的类型决定了显示效果，下面会细说。

> Tip|提示: 想要告诉用户某个操作是错误的，那么引发一个[CHttpException]异常是最简单的方法了。比如说，如果用户在URL中提供了一个无效的ID值，我们可以显示一个404错误:
~~~
[php]
// 如果提交的ID是无效的
throw new CHttpException(404,'此页面不存在');
~~~

显示错误
-----------------

当一个错误被转发给组件[CErrorHandler]的时候，它会选择合适的视图来显示错误。如果这个错误要显示给最终用户的（比如说一个[CHttpException]）那么会使用名为`errorXXX`的视图来显示错误。这个`XXX`代表着HTTP错误码（比如说400，404，500等）。如果这是个内部错误，应该只能被开发者看到，那么将使用的视图名是`exception`。在后一种中，将会显示完整的调用栈信息和错误行信息。

> Info|信息: 当应用运行在[生产模式](/doc/guide/basics.entry#debug-mode)时，所有的错误，包括内部错误都会使用视图`errorXXX`。这是因为调用的栈信息和错误行信息可能包含一些敏感信息。这种情况下，开发者应该依靠错误日志来确定错误原因。

[CErrorHandler]会搜索合适的视图来显示错误信息，搜索的顺序如下：

   1. `WebRoot/themes/ThemeName/views/system`: 在当前主题视图下的`system`目录中。

   2. `WebRoot/protected/views/system`: 在应用的默认视图的`system`目录中。

   3. `yii/framework/views`: 在Yii提供的标准视图目录中。

因此，如果你想要自定义错误显示，可以直接在`system`视图目录中或者主题的`system`视图目录中创建一个视图文件。每个视图文件都是一个包含许多HTML代码的普通PHP文件。参考框架的`view`目录下的文件，可以获得更多信息。

使用一个动作来处理错误
-------------------------------

Yii也可以使用[控制器 动作](/doc/guide/basics.controller#action)来处理错误显示。实现的方法是在应用的配置文件中配置一个错误处理器。

~~~
[php]
return array(
	......
	'components'=>array(
		'errorHandler'=>array(
			'errorAction'=>'site/error',
		),
	),
);
~~~

上面的代码中，我们配置了[CErrorHandler::errorAction]属性，属性值是一个路由`site/error`。这个路由指向`SiteController`中的`error`。当然，你也可以使用其他的路由。

我们可以这样来编写`error`动作：

~~~
[php]
public function actionError()
{
	if($error=Yii::app()->errorHandler->error)
		$this->render('error', $error);
}
~~~

在这个动作中，首先从[CErrorHandler::error]中取得详细的错误信息。如果取得的信息非空，就使用[CErrorHandler::error]返回的信息来渲染`error`视图。[CErrorHandler::error]返回的信息是一个数组，结构如下：

 * `code`: HTTP 状态码（比如 403, 500）；
 * `type`: 错误类型（比如 [CHttpException], `PHP Error`）；
 * `message`: 错误信息；
 * `file`: 发生错误的PHP文件名；
 * `line`: 错误所在的行；
 * `trace`: 错误的调用栈信息；
 * `source`: 发生错误的代码的上下文。

> Tip|提示: 我们检查[CErrorHandler::error]是否为空的原因是`error`动作可以被用户访问到，这时候也许并没有什么错误。当我们传递`$error`数组给视图，它将会被自动释放为独立的变量。所以，在视图中我们可以使用`$code`，`$type`来访问这些信息。


消息记录
---------------

一个`error`级别的错误信息会在错误发生时候被记录。如果这个错误是由PHP warning 或 notice引发的，那么这个消息将会被记录在`php`这个分类中；如果错误信息是由未捕获的异常所引起的，那么分类将是`exception.ExceptionClassName`（对于[CHttpException]来说，它的[statusCode|CHttpException::statusCode]也将被追加到分类名中）。开发者可以使用这些[记录](/doc/guide/topics.logging)来监测应用执行时候的错误信息

<div class="revision">$Id$</div>
