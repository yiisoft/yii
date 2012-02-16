入口脚本
============

入口脚本是处理用户的初始引导PHP脚本。它是唯一一个最终用户可直接请求执行的PHP脚本。

多数情况下，一个 Yii 应用的入口脚本包含像下面这样简单的脚本：

~~~
[php]
// 在生产环境中请删除此行
defined('YII_DEBUG') or define('YII_DEBUG',true);
// 包含Yii引导文件
require_once('path/to/yii/framework/yii.php');
// 创建一个应用实例并执行
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

脚本首先包含了 Yii 框架的引导文件 `yii.php`。然后他按指定的配置创建了一个Web 应用实例并执行。

调试模式
----------

Yii 应用可以按常量 `YII_DEBUG` 的值运行在调试或生产模式。默认情况下，此常量值定义为 `false`，
意为生产模式。要运行在调试模式中则需要在包含 `yii.php` 文件之前定义此常量为 `true`。
在调试模式中运行应用效率较低，因为它要维护许多内部日志。另一角度讲，调试模式在开发环境中非常有用，
因为它在错误产生时提供了丰富的调试信息。

<div class="revision">$Id: basics.entry.txt 1622 2009-12-26 20:56:05Z qiang.xue $</div>