片段缓存(Fragment Caching)
================

片段缓存指缓存网页某片段。例如，如果一个页面在表中显示每年的销售摘要，我们可以存储此表在缓存中，减少每次请求需要重新产生的时间。

要使用片段缓存，在控制器视图脚本中调用[CController::beginCache()|CBaseController::beginCache()] 和[CController::endCache()|CBaseController::endCache()] 。这两种方法开始和结束包括的页面内容将被缓存。类似[data caching](/doc/guide/caching.data) ，我们需要一个编号，识别被缓存的片段。

~~~
[php]
...别的HTML内容...
<?php if($this->beginCache($id)) { ?>
...被缓存的内容...
<?php $this->endCache(); } ?>
...别的HTML内容...
~~~

在上面的，如果[beginCache()|CBaseController::beginCache()] 返回false，缓存的内容将此地方自动插入; 否则，在`if`语句内的内容将被执行并在[endCache()|CBaseController::endCache()]触发时缓存。

缓存选项(Caching Options)
---------------

当调用[beginCache()|CBaseController::beginCache()]，可以提供一个数组由缓存选项组成的作为第二个参数，以自定义片段缓存。事实上为了方便，[beginCache()|CBaseController::beginCache()] 和[endCache()|CBaseController::endCache()]方法是[ COutputCache ]widget的包装。因此[COutputCache]的所有属性都可以在缓存选项中初始化。

### 有效期（Duration）

也许是最常见的选项是[duration|COutputCache::duration]，指定了内容在缓存中多久有效。和[CCache::set()]过期参数有点类似。下面的代码缓存内容片段最多一小时：

~~~
[php]
...其他HTML内容...
<?php if($this->beginCache($id, array('duration'=>3600))) { ?>
...被缓存的内容...
<?php $this->endCache(); } ?>
...其他HTML内容...
~~~

如果我们不设定期限，它将默认为60 ，这意味着60秒后缓存内容将无效。

### 依赖(Dependency)

像[data caching](/doc/guide/caching.data) ，内容片段被缓存也可以有依赖。例如，文章的内容被显示取决于文章是否被修改。

要指定一个依赖，我们建立了[dependency|COutputCache::dependency]选项，可以是一个实现[ICacheDependency]的对象或可用于生成依赖对象的配置数组。下面的代码指定片段内容取决于`lastModified` 列的值是否变化：

~~~
[php]
...其他HTML内容...
<?php if($this->beginCache($id, array('dependency'=>array(
		'class'=>'system.caching.dependencies.CDbCacheDependency',
		'sql'=>'SELECT MAX(lastModified) FROM Post')))) { ?>
...被缓存的内容...
<?php $this->endCache(); } ?>
...其他HTML内容...
~~~

### 变化(Variation)

缓存的内容可根据一些参数变化。例如，每个人的档案都不一样。缓存的档案内容将根据每个人ID变化。这意味着，当调用[beginCache()|CBaseController::beginCache()]时将用不同的ID。

[COutputCache]内置了这一特征，程序员不需要编写根据ID变动内容的模式。以下是摘要。

   - [varyByRoute|COutputCache::varyByRoute]: 设置此选项为true ，缓存的内容将根据[route](/doc/guide/basics.controller#route)变化。因此，每个控制器和行动的组合将有一个单独的缓存内容。

   - [varyBySession|COutputCache::varyBySession]: 设置此选项为true ，缓存的内容将根据session ID变化。因此，每个用户会话可能会看到由缓存提供的不同内容。

   - [varyByParam|COutputCache::varyByParam]: 设置此选项的数组里的名字，缓存的内容将根据GET参数的值变动。例如，如果一个页面显示文章的内容根据`id`的GET参数，我们可以指定[varyByParam|COutputCache::varyByParam]为`array('id')`，以使我们能够缓存每篇文章内容。如果没有这样的变化，我们只能能够缓存某一文章。

   - [varyByExpression|COutputCache::varyByExpression]: by setting this option
to a PHP expression, we can make the cached content to be variated according
to the result of this PHP expression. This option has been available since
version 1.0.4.

### Request Types

有时候，我们希望片段缓存只对某些类型的请求启用。例如，对于某张网页上显示表单，我们只想要缓存initially requested表单(通过GET请求)。任何随后显示（通过POST请求）的表单将不被缓存，因为表单可能包含用户输入。要做到这一点，我们可以指定[requestTypes|COutputCache::requestTypes] 选项：

~~~
[php]
...其他HTML内容...
<?php if($this->beginCache($id, array('requestTypes'=>array('GET')))) { ?>
...被缓存的内容...
<?php $this->endCache(); } ?>
...其他HTML内容...
~~~

嵌套缓存(Nested Caching)
--------------

片段缓存可以嵌套。就是说一个缓存片段附在一个更大的片段缓存里。例如，意见缓存在内部片段缓存，而且它们一起在外部缓存中在文章内容里缓存。

~~~
[php]
...其他HTML内容...
<?php if($this->beginCache($id1)) { ?>
...外部被缓存内容...
	<?php if($this->beginCache($id2)) { ?>
	...内部被缓存内容...
	<?php $this->endCache(); } ?>
...外部被缓存内容...
<?php $this->endCache(); } ?>
...其他HTML内容...
~~~

嵌套缓存可以设定不同的缓存选项。例如， 在上面的例子中内部缓存和外部缓存可以设置时间长短不同的持续值。当数据存储在外部缓存无效，内部缓存仍然可以提供有效的内部片段。 然而，反之就不行了。如果外部缓存包含有效的数据， 它会永远保持缓存副本，即使内容中的内部缓存已经过期。

<div class="revision">$Id: caching.fragment.txt 323 2008-12-04 01:40:16Z qiang.xue 译:sharehua$</div>