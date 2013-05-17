缓存
=======

缓存是提升Web应用性能的简便有效的方式。通过将相对静态的数据存储到缓存并在收到请求时取回缓存，我们便节省了生成这些数据所需的时间。

在 Yii 中使用缓存主要包括配置并访问一个应用组件。
下面的应用配置设定了一个使用两个 memcache 缓存服务器的缓存组件。

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'system.caching.CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
);
~~~

当应用运行时，缓存组件可通过 `Yii::app()->cache` 访问。

Yii 提供了不同的缓存组件，可以将缓存数据存储到不同的媒介中。例如， [CMemCache] 组件封装了 PHP 的 memcache 扩展并使用内存作为缓存存储媒介。
[CApcCache] 组件封装了 PHP APC 扩展; 而 [CDbCache] 组件会将缓存的数据存入数据库。下面是一个可用缓存组件的列表：

   - [CMemCache]: 使用 PHP [memcache 扩展](http://www.php.net/manual/en/book.memcache.php).

   - [CApcCache]: 使用 PHP [APC 扩展](http://www.php.net/manual/en/book.apc.php).

   - [CXCache]: 使用 PHP [XCache 扩展](http://xcache.lighttpd.net/)。注意，这个是从 1.0.1 版本开始支持的。

   - [CEAcceleratorCache]: 使用 PHP [EAccelerator 扩展](http://eaccelerator.net/).

   - [CDbCache]: 使用一个数据表存储缓存数据。默认情况下，它将创建并使用在 runtime 目录下的一个 SQLite3 数据库。
你也可以通过设置其  [connectionID|CDbCache::connectionID] 属性指定一个给它使用的数据库。

   - [CZendDataCache]: 使用 Zend Data Cache 作为后台缓存媒介。注意，这个是从 1.0.4 版本开始支持的。

   - [CFileCache]: 使用文件存储缓存数据。这个特别适合用于存储大块数据（例如页面）。注意，这个是从  1.0.6 版本开始支持的。

   - [CDummyCache]: 目前 dummy 缓存并不实现缓存功能。此组件的目的是用于简化那些需要检查缓存可用性的代码。
例如，在开发阶段或者服务器尚未支持实际的缓存功能，我们可以使用此缓存组件。当启用了实际的缓存支持后，我们可以切换到使用相应的缓存组件。
在这两种情况中，我们可以使用同样的代码 
`Yii::app()->cache->get($key)` 获取数据片段而不需要担心
`Yii::app()->cache` 可能会是  `null`。此组件从 1.0.5 版开始支持。

> Tip|提示: 由于所有的这些缓存组件均继承自同样的基类
[CCache]，因此无需改变使用缓存的那些代码就可以切换到使用另一种缓存方式。

缓存可以用于不同的级别。最低级别中，我们使用缓存存储单个数据片段，例如变量，我们将此称为 *数据缓存（data caching）*。下一个级别中，我们在缓存中存储一个由视图脚本的一部分生成的页面片段。
而在最高级别中，我们将整个页面存储在缓存中并在需要时取回。

在接下来的几个小节中，我们会详细讲解如何在这些级别中使用缓存。

> Note|注意: 按照定义，缓存是一个不稳定的存储媒介。即使没有超时，它也并不确保缓存数据一定存在。
因此，不要将缓存作为持久存储器使用。（例如，不要使用缓存存储 Session 数据）。

<div class="revision">$Id: caching.overview.txt 2005 2010-04-03 16:24:46Z alexander.makarow, translated by riverlet $</div>