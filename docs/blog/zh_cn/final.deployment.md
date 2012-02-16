最终调整与部署
============================

我们的博客应用快要完成了。在部署之前，我们还想做一些调整。


修改主页
------------------

我们要把日志列表页修改为主页。我们将 [应用配置](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) 修改如下,

~~~
[php]
return array(
	......
	'defaultController'=>'post',
	......
);
~~~

> Tip|提示: 由于 `PostController` 已经声明了 `index` 作为它的默认动作，当我们访问此应用的首页时，我们将看到由 post 控制器的 `index` 动作生成的结果页面。


启用表结构缓存
-----------------------

由于  ActiveRecord 按数据表的元数据（metadata）测定列的信息。读取元数据并对其分析需要消耗时间。这在开发环境中应该问题不大，但对于一个在生产环境中运行的应用来说，数据表结构如果不发生变化那这就是在浪费时间。因此，我们应通过修改应用配置启用数据表结构缓存,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CDbCache',
		),
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:/wwwroot/blog/protected/data/blog.db',
			'schemaCachingDuration'=>3600,
		),
	),
);
~~~

如上所示，我们首先添加了一个 `cache` 组件，它使用一个默认的 SQLite 数据库作为缓存平台。如果我们的服务器配备了其他的缓存扩展，例如 APC， 我们同样可以使用它们。我们还修改了 `db` 组件，设置它的 [schemaCachingDuration|CDbConnection::schemaCachingDuration] 属性为 3600，这样解析的数据表结构将可以在 3600 秒的缓存期内有效。


禁用除错（Debug）模式
------------------------

我们修改入口文件 `/wwwroot/blog/index.php` ，移除定义了 `YII_DEBUG` 常量的那一行。此常量在开发环境中非常有用，它使 Yii 在错误发生时显示更多的除错信息。然而，当应用运行于生产环境时，显示除错信息并不是一个好主意。因为它可能含有一些敏感信息，例如文件所在的位置，文件的内容等。


部署应用
-------------------------

最终的部署主要是将 `/wwwroot/blog` 目录复制到目标目录。下面的检查列表列出了每一个所需的步骤：

 1. 如果目标位置没有可用的 Yii，先将其安装好。
 2. 复制整个 `/wwwroot/blog` 目录到目标位置；
 3. 修改入口文件 `index.php` ，把 `$yii` 变量指向新的Yii引导文件。
 4. 修改文件 `protected/yiic.php` ，设置 `$yiic` 变量的值为新的 `yiic.php` 文件位置；
 5. 修改目录 `assets` 和 `protected/runtime` 的权限，确保Web服务器进程对它们有可写权。


<div class="revision">$Id: final.deployment.txt 2017 2010-04-05 17:12:13Z alexander.makarow $</div>