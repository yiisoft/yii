数据缓存
============

数据缓存即存储一些 PHP 变量到缓存中，以后再从缓存中取出来。出于此目的，缓存组件的基类 [CCache]
提供了两个最常用的方法： [set()|CCache::set] 和 [get()|CCache::get]。

要在缓存中存储一个变量 `$value` ，我们选择一个唯一 ID 并调用 [set()|CCache::set] 存储它：

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

缓存的数据将一直留在缓存中，除非它由于某些缓存策略（例如缓存空间已满，旧的数据被删除）而被清除。
要改变这种行为，我们可以在调用  [set()|CCache::set]  的同时提供一个过期参数，这样在设定的时间段之后，缓存数据将被清除：

~~~
[php]
// 值$value 在缓存中最多保留30秒
Yii::app()->cache->set($id, $value, 30);
~~~

稍后当我们需要访问此变量时（在同一个或不同的 Web 请求中），就可以通过 ID 调用 [get()|CCache::get] 从缓存中将其取回。
如果返回的是 false，表示此值在缓存中不可用，我们应该重新生成它。

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
	// 因为在缓存中没找到 $value ，重新生成它 ，
	// 并将它存入缓存以备以后使用：
	// Yii::app()->cache->set($id,$value);
}
~~~

为要存入缓存的变量选择 ID 时，要确保此 ID 对应用中所有其他存入缓存的变量是唯一的。
而在不同的应用之间，这个 ID 不需要是唯一的。缓存组件具有足够的智慧区分不同应用中的 ID。

一些缓存存储器，例如 MemCache, APC, 支持以批量模式获取多个缓存值。这可以减少获取缓存数据时带来的开销。
从版本  1.0.8 起，Yii 提供了一个新的名为
[mget()|CCache::mget] 的方法。它可以利用此功能。如果底层缓存存储器不支持此功能，[mget()|CCache::mget] 依然可以模拟实现它。

要从缓存中清除一个缓存值，调用 [delete()|CCache::delete]; 要清楚缓存中的所有数据，调用 [flush()|CCache::flush]。
当调用 [flush()|CCache::flush] 时一定要小心，因为它会同时清除其他应用中的缓存。

> Tip|提示: 由于 [CCache] 实现了 `ArrayAccess`，缓存组件也可以像一个数组一样使用。下面是几个例子：
> ~~~
> [php]
> $cache=Yii::app()->cache;
> $cache['var1']=$value1;  // 相当于: $cache->set('var1',$value1);
> $value2=$cache['var2'];  // 相当于: $value2=$cache->get('var2');
> ~~~

缓存依赖
----------------

除了过期设置，缓存数据也可能会因为依赖条件发生变化而失效。例如，如果我们缓存了某些文件的内容，而这些文件发生了改变，我们就应该让缓存的数据失效，
并从文件中读取最新内容而不是从缓存中读取。

我们将一个依赖关系表现为一个 [CCacheDependency] 或其子类的实例。
当调用   [set()|CCache::set] 时，我们连同要缓存的数据将其一同传入。

~~~
[php]
// 此值将在30秒后失效
// 也可能因依赖的文件发生了变化而更快失效
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('FileName'));
~~~

现在如果我们通过调用[get()|CCache::get] 从缓存中获取 `$value` ，依赖关系将被检查，如果发生改变，我们将会得到一个 false 值，表示数据需要被重新生成。

如下是可用的缓存依赖的简要说明：

   - [CFileCacheDependency]: 如果文件的最后修改时间发生改变，则依赖改变。

   - [CDirectoryCacheDependency]: 如果目录和其子目录中的文件发生改变，则依赖改变。

   - [CDbCacheDependency]: 如果指定 SQL 语句的查询结果发生改变，则依赖改变。

   - [CGlobalStateCacheDependency]: 如果指定的全局状态发生改变，则依赖改变。全局状态是应用中的一个跨请求，跨会话的变量。它是通过 [CApplication::setGlobalState()] 定义的。

   - [CChainedCacheDependency]: 如果链中的任何依赖发生改变，则此依赖改变。

   - [CExpressionDependency]: 如果指定的 PHP 表达式的结果发生改变，则依赖改变。此类从版本 1.0.4 起可用。

<div class="revision">$Id: caching.data.txt 1855 2010-03-04 22:42:32Z qiang.xue, translated by riverlet $</div>