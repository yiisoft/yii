页面缓存
============

页面缓存指的是缓存整个页面的内容。页面缓存可以发生在不同的地方。
例如，通过选择适当的页面头，客户端的浏览器可能会缓存网页浏览有限时间。 
Web应用程序本身也可以在缓存中存储网页内容。 在本节中，我们侧重于后一种办法。

页面缓存可以被看作是 [片段缓存](/doc/guide/caching.fragment)一个特殊情况 。
由于网页内容是往往通过应用布局来生成，如果我们只是简单的在布局中调用[beginCache()|CBaseController::beginCache]
和[endCache()|CBaseController::endCache]，将无法正常工作。
这是因为布局在[CController::render()]方法里的加载是在页面内容产生之后。

如果想要缓存整个页面，我们应该跳过产生网页内容的动作执行。我们可以使用[COutputCache]作为动作
[过滤器](/doc/guide/basics.controller#filter)来完成这一任务。下面的代码演示如何配置缓存过滤器：

~~~
[php]
public function filters()
{
	return array(
		array(
			'COutputCache',
			'duration'=>100,
			'varyByParam'=>array('id'),
		),
	);
}
~~~

上述过滤器配置会使过滤器适用于控制器中的所有行动。
我们可能会限制它在一个或几个行动通过使用插件操作器。
更多的细节中可以看[过滤器](/doc/guide/basics.controller#filter)。

> Tip: 我们可以使用[COutputCache]作为一个过滤器，因为它从[CFilterWidget]继承过来，
这意味着它是一个工具(widget)和一个过滤器。事实上，widget的工作方式和过滤器非常相似：
工具widget (过滤器filter)是在action动作里的内容执行前执行，在执行后结束。

<div class="revision">$Id: caching.page.txt 162 2008-11-05 12:44:08Z weizhuo 译：sharehua$</div>