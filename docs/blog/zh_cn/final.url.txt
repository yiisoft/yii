美化 URL
================

链接着我们博客应用不同页面的 URL 看起来很丑。例如展示日志内容的页面，其 URL 如下：

~~~
/index.php?r=post/show&id=1&title=A+Test+Post
~~~

此节中，我们将讲解如何美化这些 URL 并使它们对 SEO 友好。我们的目标是在应用中可以使用如下样式的 URL：

 1. `/index.php/posts/yii`： 指向属于标签 `yii` 的日志列表页；
 2. `/index.php/post/2/A+Test+Post`： 指向 ID 为 2，标题为 `A Test Post` 的日志的日志详情页；
 3. `/index.php/post/update?id=1`： 指向 ID 为 1 的日志更新页。

注意在第二个URL格式中，我们在URL中还包含了日志标题。这主要是为了使其对 SEO 友好。据说搜索引擎会在索引URL时重视其中的单词。

要实现我们的目标，我们修改 [应用配置](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) 如下,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
        		'post/<id:\d+>/<title:.*?>'=>'post/view',
        		'posts/<tag:.*?>'=>'post/index',
        		'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
	),
);
~~~

如上所示，我们配置了 [urlManager](http://www.yiiframework.com/doc/guide/topics.url) 组件，设置其 `urlFormat` 属性为 `path` 并添加了一系列 `rules` （规则）。

`urlManager` 通过这些规则解析并创建目标格式的URL。例如，第二条规则指明：如果一个 URL  `/index.php/posts/yii` 被请求， `urlManager` 组件就应负责调度此请求到 [路由（route）](http://www.yiiframework.com/doc/guide/basics.controller#route) `post/index` 并创建一个值为 `yii` 的 GET 参数 `tag` 。从另一个角度来说，当使用路由 `post/index` 和 `tag` 参数生成URL时，`urlManager` 组件将同样使用此规则生成目标 URL  `/index.php/posts/yii`。鉴于此，我们说 `urlManager` 是一个双向的 URL 管理器。

`urlManager` 组件还可以继续美化我们的URL，例如从URL中隐藏  `index.php` ，在URL的结尾添加 `.html` 等。我们可以通过在应用配置中设置 `urlManager` 的各种属性实现这些功能。更多详情，请参考 [指南](http://www.yiiframework.com/doc/guide/topics.url).


<div class="revision">$Id: final.url.txt 2240 2010-07-03 18:06:11Z alexander.makarow $</div>