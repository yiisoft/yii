开发规范
===========

Yii 偏爱规范胜于配置。遵循规范可使你能够创建成熟的Yii应用而不需要编写、维护复杂的配置。
当然了，在必要时，Yii 仍然可以在几乎所有的方面通过配置实现自定义。

下面我们讲解 Yii 编程中推荐的开发规范。
为简单起见，我们假设 `WebRoot` 是 Yii 应用安装的目录。

URL
---

默认情况下，Yii 识别如下格式的 URL：

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

`r` GET 变量意为
[路由（route）](/doc/guide/basics.controller#route) ，它可以被Yii解析为 控制器和动作。
如果 `ActionID` 被省略，控制器将使用默认的动作（在[CController::defaultAction]中定义）；
如果 `ControllerID` 也被省略（或者 `r` 变量不存在），应用将使用默认的控制器
（在[CWebApplication::defaultController]中定义）。

通过 [CUrlManager] 的帮助，可以创建更加可识别，更加 SEO 友好的 URL，例如
`http://hostname/ControllerID/ActionID.html`。此功能在 [URL Management](/doc/guide/topics.url) 中有详细讲解。


代码
----

Yii 推荐命名变量、函数和类时使用 驼峰风格，即每个单词的首字母大写并连在一起，中间无空格。
变量名和函数名应该使它们的第一个单词全部小写，以使其区别于类名（例如：`$basePath`,
`runController()`, `LinkPager`）。对私有类成员变量来说，我们推荐以下划线作为其名字前缀（例如： `$_actionList`）。

由于在 PHP 5.3.0 之前不支持名字空间，我们推荐类要通过某种独立的方式命名，以避免和第三方类发生冲突。鉴于此，
所有的 Yii 框架类名以 "C" 作前缀。

一个针对控制器名字的特殊规则是它们必须以单词 `Controller` 结尾。那么控制器 ID 即类名的首字母小写并去掉单词 `Controller`。
例如，`PageController` 类的 ID 就是  `page` 。这个规则使应用更加安全。它还使控制器相关的URL更加简单(例如 `/index.php?r=page/index` 而不是 
`/index.php?r=PageController/index`)。

配置
-------------

配置是一个键值对数组。每个键代表了所配置的对象中的属性名，每个值则为相应属性的初始值。
例如， `array('name'=>'My
application', 'basePath'=>'./protected')` 初始化了 `name` 和
`basePath` 属性为它们相应的数组值。

类中任何可写的属性都可以被配置。如果没有配置，属性将使用它们的默认值。
当配置一个属性时，最好阅读相应文档以保证初始值正确。

文件
----

命名和使用文件的规范取决于它们的类型。

类文件应以它们包含的公有类命名。例如， [CController] 类位于 `CController.php` 文件中。
公有类是可以被任何其他类使用的类。每个类文件应包含最多一个公有类。
私有类（只能被一个公有类使用的类）可以放在使用此类的公有类所在的文件中。

视图文件应以视图的名字命名。例如， `index` 视图位于 `index.php` 文件中。
视图文件是一个PHP脚本文件，它包含了用于呈现内容的 HTML和PHP代码。

配置文件可以任意命名。
配置文件是一个PHP脚本，它的主要目的是返回一个体现配置的关联数组。

目录
---------

Yii 假定了一系列默认的目录用于不同的场合。如果需要，每个目录都可以自定义。

   - `WebRoot/protected`: 这是 [应用基础目录](/doc/guide/basics.application#application-base-directory)，
 是放置所有安全敏感的PHP脚本和数据文件的地方。Yii 有一个默认的 `application` 别名指向此目录。
此目录及目录中的文件应该保护起来防止Web用户访问。它可以通过 [CWebApplication::basePath] 自定义。

   - `WebRoot/protected/runtime`: 此目录放置应用在运行时产生的私有临时文件。
此目录必须对 Web 服务器进程可写。它可以通过 [CApplication::runtimePath]自定义。

   - `WebRoot/protected/extensions`: 此目录放置所有第三方扩展。
它可以通过 [CApplication::extensionPath] 自定义。

   - `WebRoot/protected/modules`: 此目录放置所有的应用
[模块](/doc/guide/basics.module)，每个模块使用一个子目录。

   - `WebRoot/protected/controllers`: 此目录放置所有控制器类文件。
它可以通过   [CWebApplication::controllerPath] 自定义。

   - `WebRoot/protected/views`: 此目录放置所有试图文件，
包含控制器视图，布局视图和系统视图。
它可以通过 [CWebApplication::viewPath] 自定义。

   - `WebRoot/protected/views/ControllerID`: 此目录放置单个控制器类中使用的视图文件。
此处的 `ControllerID` 是指控制器的 ID 。它可以通过 [CController::viewPath] 自定义。

   - `WebRoot/protected/views/layouts`: 此目录放置所有布局视图文件。它可以通过
[CWebApplication::layoutPath] 自定义。

   - `WebRoot/protected/views/system`: 此目录放置所有系统视图文件。
系统视图文件是用于显示异常和错误的模板。它可以通过 [CWebApplication::systemViewPath]
自定义。

   - `WebRoot/assets`: 此目录放置公共资源文件。
资源文件是可以被发布的，可由Web用户访问的私有文件。此目录必须对 Web 服务器进程可写。
它可以通过 [CAssetManager::basePath] 自定义

   - `WebRoot/themes`: 此目录放置应用使用的不同的主题。每个子目录即一个主题，主题的名字即目录的名字。
它可以通过 [CThemeManager::basePath] 自定义。

数据库
--------

多数Web 应用是由数据库驱动的。为了最佳时间，我们
推荐在对表和列命名时使用如下命名规范。注意，这些规范并不是 Yii 所必须的。

   - 数据库表名和列名都使用小写命名。

   - 名字中的单词应使用下划线分割 (例如 `product_order`)。

   - 对于表名，你既可以使用单数也可以使用复数。但
不要 同时使用两者。为简单起见，我们推荐使用单数名字。

   - 表名可以使用一个通用前缀，例如 `tbl_` 。这样当应用所使用的表和另一个应用说使用的表共存于同一个数据库中时就特别有用。
这两个应用的表可以通过使用不同的表前缀很容易地区别开。



<div class="revision">$Id: basics.convention.txt 2345 2010-08-28 12:51:08Z mdomba $</div>