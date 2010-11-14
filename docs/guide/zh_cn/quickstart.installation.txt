安装
====

Yii 的安装由如下两步组成：

   1. 从 [yiiframework.com](http://www.yiiframework.com/) 下载 Yii 框架。
   2. 将 Yii 压缩包解压至一个 Web 可访问的目录。

> Tip|提示: 安装在 Web 目录不是必须的，每个 Yii 应用都有一个入口脚本，只有它才必须暴露给 Web 用户。其它 PHP 脚本（包括 Yii）应该保护起来不被 Web 访问，因为它们可能会被黑客利用。

需求
----

安装完 Yii 以后你也许想验证一下你的服务器是否满足使用 Yii 的要求，只需浏览器中输入如下网址来访问需求检测脚本：

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

Yii 的最低需求是你的 Web 服务器支持 PHP 5.1.0 或更高版本。Yii 在 Windows 和 Linux 系统上的 [Apache HTTP 服务器](http://httpd.apache.org/) 中测试通过，应该在其它支持 PHP 5 的 Web 服务器和平台上也工作正常。

<div class="revision">$Id: quickstart.installation.txt 1622 2009-12-26 20:56:05Z qiang.xue $</div>