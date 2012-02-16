URL Management(网址管理)
==============

Web应用程序完整的URL管理包括两个方面。首先， 当用户请求约定的URL，应用程序需要解析
它变成可以理解的参数。第二，应用程序需求提供一种创造URL的方法，以便创建的URL应用程序可以理解的。对于Yii应用程序，这些通过[CUrlManager]辅助完成。

Creating URLs（创建网址）
-------------

虽然URL可被硬编码在控制器的视图（view）文件，但往往可以很灵活地动态创建它们：

~~~
[php]
$url=$this->createUrl($route,$params);
~~~

`$this`指的是控制器实例; `$route`指定请求的[route](/doc/guide/basics.controller#route) 的要求;`$params` 列出了附加在网址中的`GET`参数。

默认情况下，URL以`get`格式使用[createUrl|CController::createUrl] 创建。例如，提供`$route='post/read'`和`$params=array('id'=>100)` ，我们将获得以下网址：

~~~
/index.php?r=post/read&id=100
~~~

参数以一系列`Name=Value`通过符号串联起来出现在请求字符串，`r`参数指的是请求的[route](/doc/guide/basics.controller#route) 。这种URL格式用户友好性不是很好，因为它需要一些非字字符。

我们可以使上述网址看起来更简洁，更不言自明，通过采用所谓的'`path`格式，省去查询字符串和把GET参数加到路径信息，作为网址的一部分：

~~~
/index.php/post/read/id/100
~~~

要更改URL格式，我们应该配置[urlManager|CWebApplication::urlManager]应用元件，以便[createUrl|CController::createUrl]可以自动切换到新格式和应用程序可以正确理解新的网址：

~~~
[php]
array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
		),
	),
);
~~~

请注意，我们不需要指定的[urlManager|CWebApplication::urlManager]元件的类，因为它在[CWebApplication]预声明为[CUrlManager]。

> 提示：此网址通过[createUrl|CController::createUrl]方法所产生的是一个相对地址。为了得到一个绝对的URL ，我们可以用前缀`Yii::app()->hostInfo` ，或调用[createAbsoluteUrl|CController::createAbsoluteUrl] 。

User-friendly URLs（用户友好的URL）
------------------

当用`path`格式URL，我们可以指定某些URL规则使我们的网址更用户友好性。例如，我们可以产生一个短短的URL`/post/100` ，而不是冗长`/index.php/post/read/id/100`。网址创建和解析都是通过[CUrlManager]指定网址规则。

要指定的URL规则，我们必须设定[urlManager|CWebApplication::urlManager] 应用元件的属性[rules|CUrlManager::rules]：

~~~
[php]
array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'pattern1'=>'route1',
				'pattern2'=>'route2',
				'pattern3'=>'route3',
			),
		),
	),
);
~~~

这些规则以一系列的路线格式对数组指定，每对对应于一个单一的规则。路线（route）的格式必须是有效的正则表达式，没有分隔符和修饰语。它是用于匹配网址的路径信息部分。还有[route](/doc/guide/basics.controller#route)应指向一个有效的路线控制器。

规则可以绑定少量的GET参数。这些出现在规则格式的GET参数，以一种特殊令牌格式表现如下：

~~~
[php]
'pattern1'=>array('route1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

In the above, the array contains a list of customized options. As of version 1.1.0,
the following options are available:

   - [urlSuffix|CUrlRule::urlSuffix]: the URL suffix used specifically for this rule. Defaults to null,
meaning using the value of [CUrlManager::urlSuffix].

   - [caseSensitive|CUrlRule::caseSensitive]: whether this rule is case sensitive. Defaults to null,
meaning using the value of [CUrlManager::caseSensitive].

   - [defaultParams|CUrlRule::defaultParams]: the default GET parameters (name=>value) that this rule provides.
When this rule is used to parse the incoming request, the values declared in this property
will be injected into $_GET.

   - [matchValue|CUrlRule::matchValue]: whether the GET parameter values should match the corresponding
sub-patterns in the rule when creating a URL. Defaults to null,
meaning using the value of [CUrlManager::matchValue]. If this property is false, it means
a rule will be used for creating a URL if its route and parameter names match the given ones.
If this property is set true, then the given parameter values must also match the corresponding
parameter sub-patterns. Note that setting this property to true will degrade performance.


### Using Named Parameters

A rule can be associated with a few GET parameters. These GET parameters
appear in the rule's pattern as special tokens in the following format:

~~~
&lt;ParamName:ParamPattern&gt;
~~~

`ParamName`表示GET参数名字，可选项`ParamPattern`表示将用于匹配GET参数值的正则表达式。当生成一个网址（URL）时，这些参数令牌将被相应的参数值替换；当解析一个网址时，相应的GET参数将通过解析结果来生成。

我们使用一些例子来解释网址工作规则。我们假设我们的规则包括如下三个：

~~~
[php]
array(
	'posts'=>'post/list',
	'post/<id:\d+>'=>'post/read',
	'post/<year:\d{4}>/<title>'=>'post/read',
)
~~~

   - 调用`$this->createUrl('post/list')`生成`/index.php/posts`。第一个规则适用。

   - 调用`$this->createUrl('post/read',array('id'=>100))`生成`/index.php/post/100`。第二个规则适用。

   - 调用`$this->createUrl('post/read',array('year'=>2008,'title'=>'a
sample post'))`生成`/index.php/post/2008/a%20sample%20post`。第三个规则适用。

   - 调用`$this->createUrl('post/read')`产生`/index.php/post/read`。请注意，没有规则适用。

总之，当使用[createUrl|CController::createUrl]生成网址，路线和传递给该方法的GET参数被用来决定哪些网址规则适用。如果关联规则中的每个参数可以在GET参数找到的，将被传递给[createUrl|CController::createUrl] ，如果路线的规则也匹配路线参数，规则将用来生成网址。

如果GET参数传递到[createUrl|CController::createUrl]是以上所要求的一项规则，其他参数将出现在查询字符串。例如，如果我们调用`$this->createUrl('post/read',array('id'=>100,'year'=>2008))` ，我们将获得`/index.php/post/100?year=2008`。为了使这些额外参数出现在路径信息的一部分，我们应该给规则附加`/*` 。 因此，该规则`post/<id:\d+>/*` ，我们可以获取网址`/index.php/post/100/year/2008` 。

正如我们提到的，URL规则的其他用途是解析请求网址。当然，这是URL生成的一个逆过程。例如， 
当用户请求`/index.php/post/100` ，上面例子的第二个规则将适用来解析路线`post/read`和GET参数`array('id'=>100)` （可通过`$_GET`获得） 。

> 注：使用的URL规则将降低应用的性能。这是因为当解析请求的URL ，[ CUrlManager ]尝试使用每个规则来匹配它，直到某个规则可以适用。因此，高流量网站应用应尽量减少其使用的URL规则。


### Parameterizing Routes

Starting from version 1.0.5, we may reference named parameters in the route part
of a rule. This allows a rule to be applied to multiple routes based on matching
criteria. It may also help reduce the number of rules needed for an application,
and thus improve the overall performance.

We use the following example rules to illustrate how to parameterize routes
with named parameters:

~~~
[php]
array(
	'<_c:(post|comment)>/<id:\d+>/<_a:(create|update|delete)>' => '<_c>/<_a>',
	'<_c:(post|comment)>/<id:\d+>' => '<_c>/read',
	'<_c:(post|comment)>s' => '<_c>/list',
)
~~~

In the above, we use two named parameters in the route part of the rules:
`_c` and `_a`. The former matches a controller ID to be either `post` or `comment`,
while the latter matches an action ID to be `create`, `update` or `delete`.
You may name the parameters differently as long as they do not conflict with
GET parameters that may appear in URLs.

Using the aboving rules, the URL `/index.php/post/123/create`
would be parsed as the route `post/create` with GET parameter `id=123`.
And given the route `comment/list` and GET parameter `page=2`, we can create a URL
`/index.php/comments?page=2`.


### Parameterizing Hostnames

Starting from version 1.0.11, it is also possible to include hostname into the rules
for parsing and creating URLs. One may extract part of the hostname to be a GET parameter.
For example, the URL `http://admin.example.com/en/profile` may be parsed into GET parameters
`user=admin` and `lang=en`. On the other hand, rules with hostname may also be used to
create URLs with paratermized hostnames.

In order to use parameterized hostnames, simply declare URL rules with host info, e.g.:

~~~
[php]
array(
	'http://<user:\w+>.example.com/<lang:\w+>/profile' => 'user/profile',
)
~~~

The above example says that the first segment in the hostname should be treated as `user`
parameter while the first segment in the path info should be `lang` parameter. The rule
corresponds to the `user/profile` route.

Note that [CUrlManager::showScriptName] will not take effect when a URL is being created
using a rule with parameterized hostname.

Also note that the rule with parameterized hostname should NOT contain the sub-folder
if the application is under a sub-folder of the Web root. For example, if the application
is under `http://www.example.com/sandbox/blog`, then we should still use the same URL rule
as described above without the sub-folder `sandbox/blog`.

### 隐藏 `index.php`

还有一点，我们可以做进一步清理我们的网址，即在URL中藏匿`index.php`入口脚本。这就要求我们配置Web服务器，以及[urlManager|CWebApplication::urlManager]应用程序元件。

我们首先需要配置Web服务器，这样一个URL没有入口脚本仍然可以处理入口脚本。如果是[Apache HTTP server](http://httpd.apache.org/) ，可以通过打开网址重写引擎和指定一些重写规则。这两个操作可以在包含入口脚本的目录下的`.htaccess`文件里实现。下面是一个示例：

~~~
Options +FollowSymLinks
IndexIgnore */*
RewriteEngine on

# if a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# otherwise forward it to index.php
RewriteRule . index.php
~~~

然后，我们设定[urlManager|CWebApplication::urlManager]元件的[showScriptName|CUrlManager::showScriptName]属性为 `false`。

现在，如果我们调用`$this->createUrl('post/read',array('id'=>100))` ，我们将获取网址`/post/100` 。更重要的是，这个URL可以被我们的Web应用程序正确解析。

### Faking URL Suffix(伪造URL后缀)

我们还可以添加一些网址的后缀。例如，我们可以用`/post/100.html`来替代`/post/100` 。这使得它看起来更像一个静态网页URL。为了做到这一点，只需配置[urlManager|CWebApplication::urlManager]元件的[urlSuffix|CUrlManager::urlSuffix]属性为你所喜欢的后缀。

使用自定义URL规则设置类
-----------------------------

> 注意: Yii从1.1.8版本起支持自定义URL规则类

默认情况下，每个URL规则都通过[CUrlManager]来声明为一个[CUrlRule]对象，这个对象会解析当前请求并根据具体的规则来生成URL。
虽然[CUrlRule]可以处理大部分URL格式，但在某些特殊情况下仍旧有改进余地。

比如，在一个汽车销售网站上，可能会需要支持类似`/Manufacturer/Model`这样的URL格式，
其中`Manufacturer` 和 `Model` 都各自对应数据库中的一个表。此时[CUrlRule]就无能为力了。

我们可以通过继承[CUrlRule]的方式来创造一个新的URL规则类。并且使用这个类解析一个或者多个规则。
以上面提到的汽车销售网站为例，我们可以声明下面的URL规则。

~~~
[php]
array(
	// 一个标准的URL规则，将 '/' 对应到 'site/index'
	'' => 'site/index',

	// 一个标准的URL规则，将 '/login' 对应到 'site/login', 等等
	'<action:(login|logout|about)>' => 'site/<action>',

	// 一个自定义URL规则，用来处理 '/Manufacturer/Model'
	array(
	    'class' => 'application.components.CarUrlRule',
	    'connectionID' => 'db',
	),

	// 一个标准的URL规则，用来处理 'post/update' 等
	'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
),
~~~

从以上可以看到，我们自定义了一个URL规则类`CarUrlRule`来处理类似`/Manufacturer/Model`这样的URL规则。
这个类可以这么写：

~~~
[php]
class CarUrlRule extends CBaseUrlRule
{
	public $connectionID = 'db';

	public function createUrl($manager,$route,$params,$ampersand)
	{
		if ($route==='car/index')
		{
			if (isset($params['manufacturer'], $params['model']))
				return $params['manufacturer'] . '/' . $params['model'];
			else if (isset($params['manufacturer']))
				return $params['manufacturer'];
		}
		return false;  // this rule does not apply
	}

	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
	{
		if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches))
		{
			// check $matches[1] and $matches[3] to see
			// if they match a manufacturer and a model in the database
			// If so, set $_GET['manufacturer'] and/or $_GET['model']
			// and return 'car/index'
		}
		return false;  // this rule does not apply
	}
}
~~~

自定义URL规则类必须实现在[CBaseUrlRule]中定义的两个接口。

* [CBaseUrlRule::createUrl()|createUrl()]
* [CBaseUrlRule::parseUrl()|parseUrl()]

除了这种典型用法，自定义URL规则类还可以有其他的用途。比如，我们可以写一个规则类来记录有关URL解析和UEL创建的请求。
这对于正在开发中的网站来说很有用。我们还可以写一个规则类来在其他URL规则都匹配失败的时候显示一个自定义404页面。
注意，这种用法要求规则类在所有其他规则的最后声明。

<div class="revision">$Id$</div>