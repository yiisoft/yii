性能调整
==================

网络应用程序的性能受很多因素的影响。数据库存取，文件系统操作，网络带宽等都是潜在的影响因素。 Yii 已在各个方面减少框架带来的性能影响。但是在用户的应用中仍有很多地方可以被改善来提高性能。

开启 APC 扩展
----------------------

启用 [PHP APC
扩展](http://docs.php.net/manual/zh/book.apc.php) 可能是改善一个应用整体性能的最简单方式。此扩展缓存和优化 PHP 中间代码并避免时间花费再为每个新来的请求解析PHP脚本。

禁用调试模式
--------------------

禁用调试模式是另一个改善性能的容易方式。若常量 `YII_DEBUG` 被定以为 true,这个 Yii 应用将以调试模式运行。 调试模
式在开发阶段是有用的，但是它影响性能因为一些组件引起额外的系统开销。例如，信息记录器(the message logger)将为
被条被记录的信息记录额外的调试信息。

使用 `yiilite.php`
-------------------

当启用 [PHP APC 扩展](http://docs.php.net/manual/zh/book.apc.php) 时， 我们可以将 `yii.php` 替换为另一个名为 `yiilite.php` 的引导文件来进一步提高 Yii-powered 应用的性能。

文件 `yiilite.php` 包含在每个 Yii 发布中。它是一些常用到的 Yii 类文件的合并文件。在文件中，注释和跟踪语句都被去除。因此，使用 `yiilite.php` 将减少被引用的文件数量并避免执行跟踪语句。

注意，使用 `yiilite.php` 而不开启 APC 实际上将降低性能，因为 `yiilite.php` 包含了一些不是每个请求都必须的类，这将花费额外的解析时间。 同时也要注意，在一些服务器配置下使用 `yiilite.php` 将更慢，即使 APC 被打开。 
最好使用演示中的 `hello world` 运行一个基准程序来决定是否使用 `yiilite.php`。

使用缓存技术
------------------------

如在 [缓存](/doc/guide/caching.overview) 章节所述，Yii 提供了几个可以有效提高性能的缓存方案。
若一些数据的生成需要长时间，我们可以使用[数据缓存](/doc/guide/caching.data) 方法来减少数据产生的频率；若页面的一部分保持相对的固定，我们可以使用 [碎片缓存](/doc/guide/caching.fragment) 方法减少它的渲染频率；若一整个页面保持相对的固定，我们可以使用 [页面缓存](/doc/guide/caching.page) 方法来节省页面渲染所需的花销。

若应用在使用 [Active Record](/doc/guide/database.ar)，我们应当打开 数据结构缓存 以节省解析数据表结构的时间。可以
通过设置 [CDbConnection::schemaCachingDuration] 属性为一个大于 0 的值来完成。

除了这些应用级别的缓存技术，我们也可使用服务级别的缓存方案来提高应用的性能。
事实上，我们之前描述的 [PHP APC 缓存](/doc/guide/topics.performance#enabling-apc-extension)  就属于此项。 也有其他的服务器技术，例如 [Zend Optimizer](http://www.zend.com/en/products/guard/zend-optimizer), [eAccelerator](http://eaccelerator.net/), [Squid](http://www.squid-cache.org/)，其他不一一列出。

数据库优化
---------------------

从数据库取出数据经常是一个网络应用的主要瓶颈。虽然使用缓存可以减少性能损失，它不能解决根本问题。 当数据库包
含大量数据而被缓存的数据是无效时，如果没有良好的数据库和查询优化设计，获取最新的数据将会非常耗费资源。

在一个数据库中聪明的设计索引。索引可以让 `SELECT` 查询更快， 但它会让 `INSERT`, `UPDATE` 或 `DELETE` 查询更慢。

对于复杂的查询，推荐为它创建一个数据库视图，而不是通过PHP代码生成查询语句让DBMS来重复解析他们。

不要滥用 [Active Record](/doc/guide/database.ar)。虽然 [Active Record](/doc/guide/database.ar) 擅长以一个 OOP样式模型化数据， 它实际上为了它需要创建一个或几个对
象来代表每条查询结果降低了性能。 对于数据密集的应用，在底层使用 [DAO](/doc/guide/database.dao) 或 数据库接口 将是一个更好的选择。

最后但并不是最不重要的一点，在你的 `SELECT` 查询中使用 `LIMIT` 。这将避免从数据库中取出过多的数据 并耗尽为 PHP 分配的内存。

最小化脚本文件
-----------------------

复杂的页面经常需要引入很多外部的 JavaScript 和 CSS 文件。 因为每个文件将引起一次额外的往返一次，我们应当通过联合文件来最小化脚本文件的数量。 我们也应当考虑减少每个脚本文件的大小来减少 网络传输时间。有很多工具来帮助改善这两方面。

对于一个 Yii 产生的页面，例外是一些由组件渲染的脚本文件我们不想要更改 (例如 Yii core 组件，第三方组件)。 为了最小化这些脚本文件，我们需要两个步骤。

> Note: 下面描述的 `scriptMap` 特征已自版本 1.0.3 起被支持。

首先，通过配置应用组件 [clientScript|CWebApplication::clientScript] 的 [scriptMap|CClientScript::scriptMap] 属性来声明脚本被最小化。 可以在应用配置中完成，也可以在代码中配置。例如，

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>'/js/all.js',
	'jquery.ajaxqueue.js'=>'/js/all.js',
	'jquery.metadata.js'=>'/js/all.js',
	......
);
~~~

上面的代码所做是映射这些 JavaScript 文件到 URL `/js/all.js`。 若这些 JavaScript 文件任何之一需要被一些组件引入， Yii 将引入这个 URL (一次) 而不是各个独立的脚本文件。

其次，我们需要使用一些工具来联合 (和压缩) JavaScript 文件为一个单独的文件，并保存为 `js/all.js`。

相同的技巧也适用于 CSS 文件。

在 [Google AJAX Libraries API](http://code.google.com/apis/ajaxlibs/) 帮助下我们可以改善页面载入速度。例如，我们可以从 Google 的服务器引入 `jquery.js`
而不是从我们自己的服务器。要这样做， 我们首先配置 `scriptMap` 如下,

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>false,
	'jquery.ajaxqueue.js'=>false,
	'jquery.metadata.js'=>false,
	......
);
~~~

通过映射(map)这些脚本文件为 false，我们阻止 Yii 产生引入这些文件的代码。作为替代，我们在页面中编写如下代码直接从 Google 引入文件,

~~~
[php]
<head>
<?php echo CGoogleApi::init(); ?>

<?php echo CHtml::script(
	CGoogleApi::load('jquery','1.3.2') . "\n" .
	CGoogleApi::load('jquery.ajaxqueue.js') . "\n" .
	CGoogleApi::load('jquery.metadata.js')
); ?>
......
</head>
~~~

<div class="revision">$Id: topics.performance.txt 1769 2010-11-13 13:33:49Z HonestQiao $</div>