日志记录
=======

Yii 提供了一个灵活可扩展的日志功能。记录的日志
可以通过日志级别和信息分类进行归类。通过使用
级别和分类过滤器，所选的信息还可以进一步路由到
不同的目的地，例如一个文件，Email，浏览器窗口等。

信息记录
---------------

信息可以通过 [Yii::log] 或 [Yii::trace] 记录。其
区别是后者只在当应用程序运行在 [调试模式(debug mode)](/doc/guide/basics.entry#debug-mode)
中时才会记录信息。

~~~
[php]
Yii::log($message, $level, $category);
Yii::trace($message, $category);
~~~

当记录信息时，我们需要指定它的分类和级别
分类是一段格式类似于 [路径别名](/doc/guide/basics.namespace) 的字符串。
例如，如果一条信息是在 [CController] 中记录的，我们可以使用 `system.web.CController`
作为分类。信息级别应该是下列值中的一种：

   - `trace`: 这是在  [Yii::trace] 中使用的级别。它用于在开发中
跟踪程序的执行流程。

   - `info`: 这个用于记录普通的信息。

   - `profile`: 这个是性能概述（profile）。下面马上会有更详细的说明。

   - `warning`: 这个用于警告（warning）信息。

   - `error`: 这个用于致命错误（fatal error）信息。

信息路由
---------------

通过 [Yii::log] 或 [Yii::trace] 记录的信息是保存在内存中的。
我们通常需要将它们显示到浏览器窗口中，或者将他们保存到一些
持久存储例如文件、Email中。这个就叫作 *信息路由*，例如，
发送信息到不同的目的地。

在 Yii 中，信息路由是由一个叫做 [CLogRouter] 的应用组件管理的。
它负责管理一系列称作 *日志路由* 的东西。每个日志路由
代表一个单独的日志目的地。通过一个日志路由发送的信息会被他们的级别和分类过滤。

要使用信息路由，我们需要安装并预加载一个 [CLogRouter]
应用组件。我们也还需要配置它的
[routes|CLogRouter::routes] 属性为我们想要的那些日志路由。
下面的代码演示了一个所需的  [应用配置](/doc/guide/basics.application#application-configuration)
示例: 

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'trace, info',
					'categories'=>'system.*',
				),
				array(
					'class'=>'CEmailLogRoute',
					'levels'=>'error, warning',
					'emails'=>'admin@example.com',
				),
			),
		),
	),
)
~~~

在上面的例子中，我们定义了两个日志路由。第一个是
[CFileLogRoute] ，它会把信息保存在位于应用程序 runtime 目录中的一个文件中。
而且只有级别为 `trace` 或 `info` 、分类以 `system.` 开头的信息才会被保存。 
第二个路由是
[CEmailLogRoute] ，它会将信息发送到指定的 email 地址，且只有级别为  `error` 
或 `warning` 的才会发送。

在 Yii 中，有下列几种日志路由可用：

   - [CDbLogRoute]: 将信息保存到数据库的表中。
   - [CEmailLogRoute]: 发送信息到指定的 Email 地址。
   - [CFileLogRoute]: 保存信息到应用程序 runtime 目录中的一个文件中。
   - [CWebLogRoute]: 将 信息 显示在当前页面的底部。
   - [CProfileLogRoute]: 在页面的底部显示概述（profiling）信息。

> Info|信息: 信息路由发生在当前请求周期最后的 [onEndRequest|CApplication::onEndRequest] 事件触发时。
要显式终止当前请求过程，请调用 [CApplication::end()] 而不是使用  `die()` 或 `exit()`，因为
[CApplication::end()] 将会触发 [onEndRequest|CApplication::onEndRequest] 事件，
这样信息才会被顺利地记录。

信息过滤
-----------------

正如我们所提到的，信息可以在他们被发送到一个日志路由之前通过它们的级别和分类过滤。
这是通过设置对应日志路由的 [levels|CLogRoute::levels] 和 [categories|CLogRoute::categories] 属性完成的。
多个级别或分类应使用逗号连接。

由于信息分类是类似 `xxx.yyy.zzz` 格式的，我们可以将其视为一个分类层级。
具体地，我们说 `xxx` 是 `xxx.yyy` 的父级，而`xxx.yyy` 又是 `xxx.yyy.zzz` 的父级。
这样我们就可以使用 `xxx.*` 表示分类 `xxx` 及其所有的子级和孙级分类

记录上下文信息
---------------------------

从版本 1.0.6 起，我们可以设置记录附加的上下文信息，
比如 PHP 的预定义变量（例如 `$_GET`, `$_SERVER`），session ID，用户名等。
这是通过指定一个日志路由的 [CLogRoute::filter]属性为一个合适的日志过滤规则实现的。

The framework comes with the convenient [CLogFilter] that may be used as the needed log
filter in most cases. By default, [CLogFilter] will log a message with variables like
`$_GET`, `$_SERVER` which often contains valuable system context information.
[CLogFilter] can also be configured to prefix each logged message with session ID, username, etc.,
which may greatly simplifying the global search when we are checking the numerous logged messages.

The following configuration shows how to enable logging context information. Note that each
log route may have its own log filter. And by default, a log route does not have a log filter.

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error',
					'filter'=>'CLogFilter',
				),
				...other log routes...
			),
		),
	),
)
~~~


Starting from version 1.0.7, Yii supports logging call stack information in the messages that are
logged by calling `Yii::trace`. This feature is disabled by default because it lowers performance.
To use this feature, simply define a constant named `YII_TRACE_LEVEL` at the beginning of the entry
script (before including `yii.php`) to be an integer greater than 0. Yii will then append to
every trace message with the file name and line number of the call stacks belonging to application
code. The number `YII_TRACE_LEVEL` determines how many layers of each call stack should be recorded.
This information is particularly useful during development stage as it can help us identify the
places that trigger the trace messages.


Performance Profiling
---------------------

Performance profiling is a special type of message logging. Performance
profiling can be used to measure the time needed for the specified code
blocks and find out what the performance bottleneck is.

To use performance profiling, we need to identify which code blocks need
to be profiled. We mark the beginning and the end of each code block by
inserting the following methods:

~~~
[php]
Yii::beginProfile('blockID');
...code block being profiled...
Yii::endProfile('blockID');
~~~

where `blockID` is an ID that uniquely identifies the code block.

Note, code blocks need to be nested properly. That is, a code block cannot
intersect with another. It must be either at a parallel level or be
completely enclosed by the other code block.

To show profiling result, we need to install a [CLogRouter] application
component with a [CProfileLogRoute] log route. This is the same as we do
with normal message routing. The [CProfileLogRoute] route will display the
performance results at the end of the current page.


Profiling SQL Executions
------------------------

Profiling is especially useful when working with database since SQL executions
are often the main performance bottleneck of an application. While we can manually
insert `beginProfile` and `endProfile` statements at appropriate places to measure
the time spent in each SQL execution, starting from version 1.0.6, Yii provides
a more systematic approach to solve this problem.

By setting [CDbConnection::enableProfiling] to be true in the application configuration,
every SQL statement being executed will be profiled. The results can be readily displayed
using the aforementioned [CProfileLogRoute], which can show us how much time is spent
in executing what SQL statement. We can also call [CDbConnection::getStats()] to retrieve
the total number SQL statements executed and their total execution time.


<div class="revision">$Id: topics.logging.txt 2739 2010-12-14 01:50:04Z weizhuo $</div>
