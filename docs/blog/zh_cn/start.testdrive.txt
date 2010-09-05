Yii 之初体验
====================

在这一部分里，我们将讲解怎样建立一个程序的骨架作为着手点。为简单起见，我们假设Web服务器根目录是 `/wwwroot` ，相应的URL是 `http://www.example.com/`。


安装Yii
--------------

首先，我们来安装Yii框架。 从 [www.yiiframework.com](http://www.yiiframework.com/download) 获取一份Yii的拷贝，解压缩到 `/wwwroot/yii`。再次检查以确保 `/wwwroot/yii/framework` 目录存在。

> Tip|提示: Yii框架可以安装在文件系统的任何地方，而不是必须在Web目录中。它的 `framework` 目录包含了框架的代码，这也是部署Yii应用时唯一一个必要的目录。一个单独的Yii安装可以被用于多个Yii应用。

Yii安装完毕之后，打开浏览器访问URL `http://www.example.com/yii/requirements/index.php`。它将显示Yii提供的需求检查程序。对我们的Blog应用来说，除了Yii所需的最小需求之外，我们还需要启用 `pdo` 和 `pdo_sqlite` 这两个PHP 扩展。这样我们才能访问SQLite数据库。


创建应用骨架
-----------------------------

然后，我们使用 `yiic` 工具在 `/wwwroot/blog` 目录下创建一个应用骨架。`yiic` 工具是在Yii发布包中提供的命令行工具。它可以用于创建代码以减少某些重复的编码工作。

打开一个命令行窗口，执行以下命令：

~~~
% /wwwroot/yii/framework/yiic webapp /wwwroot/blog
Create a Web application under '/wwwroot/blog'? [Yes|No]y
......
~~~

> Tip|提示: 为了使用上面提到的 `yiic` 工具，CLI PHP 程序必须在命令搜索路径内（译者注：即 php.exe 所在的目录必须在PATH环境变量中），否则，可能要使用下面的命令:
>
>~~~
> path/to/php /wwwroot/yii/framework/yiic.php webapp /wwwroot/blog
>~~~

要查看我们刚创建的应用，打开浏览器访问 URL `http://www.example.com/blog/index.php`。可以看到我们的程序骨架已经有了四个具备完整功能的页面：首页（Home），“关于”页（About），联系页（Contact）和登录页（Login）。

接下来，我们简单介绍一下在这个程序骨架中的内容。

###入口脚本

我们有一个[入口脚本](http://www.yiiframework.com/doc/guide/basics.entry) 文件 `/wwwroot/blog/index.php` ，内容如下：

~~~
[php]
<?php
$yii='/wwwroot/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
Yii::createWebApplication($config)->run();
~~~

这是唯一一个网站用户可以直接访问的脚本。此脚本首先包含了Yii的引导文件 `yii.php`。然后它按照指定的配置创建了一个[应用](http://www.yiiframework.com/doc/guide/basics.application) 实例并执行此应用。


###基础应用目录

我们还有一个 [应用基础目录](http://www.yiiframework.com/doc/guide/basics.application#application-base-directory) `/wwwroot/blog/protected`。我们主要的代码和数据将放在此目录下，它应该被保护起来，防止网站访客的直接访问。针对 [Apache httpd 网站服务器](http://httpd.apache.org/) ，我们在此目录下放了一个 `.htaccess` 文件，其内容如下：

~~~
deny from all
~~~

对于其他的网站服务器，请参考相应的关于保护目录以防止被访客直接访问的相关文档。


应用的工作流程
--------------------

为了帮你理解Yii是怎样工作的，对于我们的程序骨架，当有人访问它的联系页（Contact）时，我们对它的工作流程描述如下：

 0. 用户请求此 URL `http://www.example.com/blog/index.php?r=site/contact`；
 1. [入口脚本](http://www.yiiframework.com/doc/guide/basics.entry) 被网站服务器执行以处理此请求；
 2. 一个 [应用](http://www.yiiframework.com/doc/guide/basics.application) 的实例被创建，其配置参数为`/wwwroot/blog/protected/config/main.php` 应用配置文件中指定的初始值；
 3. 应用分派此请求到一个 [控制器（Controller）](http://www.yiiframework.com/doc/guide/basics.controller) 和一个 [控制器动作（Controller action）](http://www.yiiframework.com/doc/guide/basics.controller#action)。对于联系页（Contact）的请求，它分派到了 `site` 控制器和 `contact` 动作 (即 `/wwwroot/blog/protected/controllers/SiteController.php` 中的  `actionContact` 方法);
 4. 应用按 `SiteController` 实例创建了 `site` 控制器并执行；
 5. `SiteController` 实例通过调用它的 `actionContact()` 方法执行 `contact` 动作；
 6. `actionContact` 方法为用户渲染一个名为 `contact` 的 [视图（View）](http://www.yiiframework.com/doc/guide/basics.view) 。在程序内部，这是通过包含一个视图文件 `/wwwroot/blog/protected/views/site/contact.php` 并将结果插入 [布局](http://www.yiiframework.com/doc/guide/basics.view#layout) 文件 `/wwwroot/blog/protected/views/layouts/column1.php` 实现的。


<div class="revision">$Id: start.testdrive.txt 1734 2010-01-21 18:41:17Z qiang.xue $</div>