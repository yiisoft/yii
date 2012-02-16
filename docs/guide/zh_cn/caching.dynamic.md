动态内容(Dynamic Content)
===============

当使用[fragment caching](/doc/guide/caching.fragment)或[page
caching](/doc/guide/caching.page)，我们常常遇到的这样的情况
整个部分的输出除了个别地方都是静态的。例如，帮助页可能会显示静态的帮助
信息，而用户名称显示的是当前用户的。

解决这个问题，我们可以根据用户名匹配缓存内容，但是这将是我们宝贵空间一个巨大的浪费，因为缓存除了用户名其他大部分内容是相同的。我们还可以把网页切成几个片段并分别缓存，但这种情况会使页面和代码变得非常复杂。更好的方法是使用由[ CController ]提供的*动态内容dynamic content*功能 。

动态内容是指片段输出即使是在片段缓存包括的内容中也不会被缓存。即使是包括的内容是从缓存中取出，为了使动态内容在所有时间是动态的，每次都得重新生成。出于这个原因，我们要求
动态内容通过一些方法或函数生成。

调用[CController::renderDynamic()]在你想的地方插入动态内容。

~~~
[php]
...别的HTML内容...
<?php if($this->beginCache($id)) { ?>
...被缓存的片段内容...
	<?php $this->renderDynamic($callback); ?>
...被缓存的片段内容...
<?php $this->endCache(); } ?>
...别的HTML内容...
~~~

在上面的， `$callback`指的是有效的PHP回调。它可以是指向当前控制器类的方法或者全局函数的字符串名。它也可以是一个数组名指向一个类的方法。其他任何的参数，将传递到[renderDynamic()|CController::renderDynamic()]方法中。回调将返回动态内容而不是仅仅显示它。

<div class="revision">$Id: caching.dynamic.txt 163 2008-11-05 12:51:48Z weizhuo 译:sharehua$</div>