应用
===========

应用是指请求处理中的执行上下文。它的主要任务是分析用户请求并将其分派到合适的控制器中以作进一步处理。
它同时作为服务中心，维护应用级别的配置。鉴于此，应用也叫做`前端控制器`。

应用由 [入口脚本](/doc/guide/basics.entry) 创建为一个单例对象。这个应用单例对象可以在任何地方通过
 [Yii::app()|YiiBase::app] 访问。


应用配置
-------------------------

默认情况下，应用是一个  [CWebApplication] 的实例。要自定义它，我们通常需要提供一个配置文件
（或数组） 以创建应用实例时初始化其属性值。自定义应用的另一种方式是继承 [CWebApplication]。

配置是一个键值对数组。每个键代表应用实例中某属性的名字，每个值即相应属性的初始值。
例如，如下的配置设定了应用的 [name|CApplication::name] 和
[defaultController|CWebApplication::defaultController] 属性。

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

我们通常在一个单独的PHP 脚本（e.g.`protected/config/main.php`）中保存这些配置。在脚本中，
我们通过以下方式返回此配置数组：

~~~
[php]
return array(...);
~~~

要应用此配置，我们将配置文件的名字作为参数传递给应用的构造器，或像下面这样传递到 [Yii::createWebApplication()]
。这通常在 [入口脚本](/doc/guide/basics.entry) 中完成：

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|提示: 如果应用配置非常复杂，我们可以把它分割为若干文件，每个文件返回配置数组中的一部分。
然后，在主配置文件中，我们调用PHP的 `include()` 包含其余的配置文件并把它们合并为一个完整的配置数组。


应用基础目录
--------------------------

应用基础目录指包含了所有安全敏感的PHP脚本和数据的根目录。默认状态下，它是一个位于含有入口脚本目录的名为
`protected` 的子目录。它可以通过设置 [application configuration](/doc/guide/basics.application#application-configuration)
中的 [basePath|CWebApplication::basePath] 属性自定义。

在应用基础目录下的内容应该保护起来防止网站访客直接访问。对于 [Apache HTTP
服务器](http://httpd.apache.org/), 这可以通过在基础目录中放置一个 `.htaccess`
文件很简单的实现。 `.htaccess` 内容如下：

~~~
deny from all
~~~

应用组件
---------------------

应用的功能可以通过其灵活的组件结构轻易地自定义或增强。应用管理了一系列应用组件，每个组件实现一特定功能。
例如，应用通过 [CUrlManager] 和 [CHttpRequest] 的帮助解析来自用户的请求。

通过配置应用的 [components|CApplication::components] 属性，
我们可以自定义应用中用到的任何组件类及其属性值。例如，我们可以配置应用的 [CMemCache] 组件，
这样它就可以使用多个 memcache 服务器实现缓存：

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

如上所示，我们在 `components` 数组中添加了 `cache` 元素。`cache` 元素表明此组件的类是 `CMemCache`,
他的 `servers` 属性应依此初始化。

要访问一个应用组件，使用 `Yii::app()->ComponentID` ，其中的 `ComponentID` 是指组件的ID（例如 `Yii::app()->cache`）。

应用的组件可以通过在其配置中设置 `enabled` 为 false 禁用。当我们访问被禁用的组件时将返回 Null。

> Tip|提示: 默认情况下，应用组件会按需创建。这意味着一个应用的组件如果没有在一个用户请求中被访问，它可能根本不被创建。
因此，如果一个应用配置了很多组件，其总体性能可能并不会下降。有的应用组件 (例如 [CLogRouter]) 可能需要在无论它们是否被访问的情况下都要被创建。
要实现这个，需将其ID列在应用的 [preload|CApplication::preload] 属性里。

核心应用组件
---------------------------

Yii 预定义了一系列核心应用组件，提供常见 Web 应用中所用的功能。例如，
[request|CWebApplication::request] 组件用于解析用户请求并提供例如 URL，cookie 等信息。
通过配置这些核心组件的属性，我们可以在几乎所有的方面修改Yii 的默认行为。

下面我们列出了由 [CWebApplication] 预定义的核心组件。

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
管理私有资源文件的发布。

   - [authManager|CWebApplication::authManager]: [CAuthManager] - 管理基于角色的访问控制 (RBAC).

   - [cache|CApplication::cache]: [CCache] - 提供数据缓存功能。注意，你必须指定实际的类（例如[CMemCache], [CDbCache]）。
否则，当你访问此组件时将返回 NULL。

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
管理客户端脚本 (javascripts 和 CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
提供 Yii 框架用到的核心信息的翻译。

   - [db|CApplication::db]: [CDbConnection] - 提供数据库连接。注意，使用此组件你必须配置其
[connectionString|CDbConnection::connectionString] 属性。

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - 处理未捕获的 PHP 错误和异常。

   - [format|CApplication::format]: [CFormatter] - 格式化数值显示。此功能从版本 1.1.0 起开始提供。

   - [messages|CApplication::messages]: [CPhpMessageSource] - 提供Yii应用中使用的信息翻译。

   - [request|CWebApplication::request]: [CHttpRequest] - 提供关于用户请求的信息。

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
提供安全相关的服务，例如散列，加密。

   - [session|CWebApplication::session]: [CHttpSession] - 提供session相关的功能。

   - [statePersister|CApplication::statePersister]: [CStatePersister] - 提供全局状态持久方法。

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - 提供 URL 解析和创建相关功能

   - [user|CWebApplication::user]: [CWebUser] - 提供当前用户的识别信息。

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - 管理主题。


应用的生命周期
----------------------

当处理用户请求时，应用将经历如下声明周期：

   0. 通过 [CApplication::preinit()] 预初始化应用；

   1. 设置类的自动装载器和错误处理；

   2. 注册核心类组件；

   3. 加载应用配置；

   4. 通过 [CApplication::init()] 初始化应用:
       - 注册应用行为；
	   - 载入静态应用组件；

   5. 触发 [onBeginRequest|CApplication::onBeginRequest] 事件；

   6. 处理用户请求:
	   - 解析用户请求；
	   - 创建控制器；
	   - 运行控制器；

   7. 触发 [onEndRequest|CApplication::onEndRequest] 事件。

<div class="revision">$Id: basics.application.txt 2360 2010-08-28 23:55:47Z qiang.xue $</div>