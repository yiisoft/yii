Beautifying URLs
================

The URLs linking various pages of our blog application currently look ugly. For example, the URL for the page showing a post looks like the following:

~~~
/index.php?r=post/show&id=1&title=A+Test+Post
~~~

In this section, we describe how to beautify these URLs and make them SEO-friendly. Our goal is to be able to use the following URLs in the application:

 1. `/index.php/posts/yii`: leads to the page showing a list of posts with tag `yii`;
 2. `/index.php/post/2/A+Test+Post`: leads to the page showing the detail of the post with ID 2 whose title is `A Test Post`;
 3. `/index.php/post/update?id=1`: leads to the page that allows updating the post with ID 1.

Note that in the second URL format, we include the post title in the URL. This is mainly to make the URL SEO friendly. It is said that search engines may also respect the words found in a URL when it is being indexed.

To achieve our goal, we modify the [application configuration](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) as follows,

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

In the above, we configure the [urlManager](http://www.yiiframework.com/doc/guide/topics.url) component by setting its `urlFormat` property to be `path` and adding a set of `rules`.

The rules are used by `urlManager` to parse and create the URLs in the desired format. For example, the second rule says that if a URL `/index.php/posts/yii` is requested, the `urlManager` component should be responsible to dispatch the request to the [route](http://www.yiiframework.com/doc/guide/basics.controller#route) `post/index` and generate a `tag` GET parameter with the value `yii`. On the other hand, when creating a URL with the route `post/index` and parameter `tag`, the `urlManager` component will also use this rule to generate the desired URL `/index.php/posts/yii`. For this reason, we say that `urlManager` is a two-way URL manager.

The `urlManager` component can further beautify our URLs, such as hiding `index.php` in the URLs, appending suffix like `.html` to the URLs. We can obtain these features easily by configuring various properties of `urlManager` in the application configuration. For more details, please refer to [the Guide](http://www.yiiframework.com/doc/guide/topics.url).


<div class="revision">$Id$</div>