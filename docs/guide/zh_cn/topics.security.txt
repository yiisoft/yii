安全措施 (Security)
========

跨站脚本攻击的防范
-------------------------------
跨站脚本攻击(简称 XSS)，即web应用从用户收集用户数据。
攻击者常常向易受攻击的web应用注入JavaScript，VBScript，ActiveX，HTML或 Flash来迷惑访问者以收集访问者的信息。
举个例子，一个未经良好设计的论坛系统可能不经检查就显示用户所输入的内容。
攻击者可以在帖子内容中注入一段恶意的JavaScript代码。
这样，当其他访客在阅读这个帖子的时候，这些JavaScript代码就可以在访客的电脑上运行了。

一个防范XSS攻击的最重要的措施之一就是：在显示用户输入的内容之前进行内容检查。
比如，你可以对内容中的HTML进行转义处理。但是在某些情况下这种方法就不可取了，因为这种方法禁用了所有的HTML标签。

Yii集成了[HTMLPurifier](http://htmlpurifier.org/)并且为开发者提供了一个很有用的组件[CHtmlPurifier]，
这个组件封装了[HTMLPurifier](http://htmlpurifier.org/)类。它可以将通过有效的审查、安全和白名单功能来把所审核的内容中的所有的恶意代码清除掉，并且确保过滤之后的内容过滤符合标准。

[CHtmlPurifier]组件可以作为一个[widget](/doc/guide/basics.view#widget)或者[filter](/doc/guide/basics.controller#filter)来使用。
当作为一个widget来使用的时候，[CHtmlPurifier]可以对在视图中显示的内容进行安全过滤。
以下是代码示例：

~~~
[php]
<?php $this->beginWidget('CHtmlPurifier'); ?>
//...这里显示用户输入的内容...
<?php $this->endWidget(); ?>
~~~


跨站请求伪造攻击的防范
-------------------------------------

跨站请求伪造(简称CSRF)攻击，即攻击者在用户浏览器在访问恶意网站的时候，让用户的浏览器向一个受信任的网站发起攻击者指定的请求。
举个例子，一个恶意网站有一个图片，这个图片的`src`地址指向一个银行网站：
 `http://bank.example/withdraw?transfer=10000&to=someone`。
如果用户在登陆银行的网站之后访问了这个恶意网页，那么用户的浏览器会向银行网站发送一个指令，这个指令的内容可能是“向攻击者的帐号转账10000元”。
跨站攻击方式利用用户信任的某个特定网站，而CSRF攻击正相反，它利用用户在某个网站中的特定用户身份。

要防范CSRF攻击，必须谨记一条：`GET`请求只允许检索数据而不能修改服务器上的任何数据。
而`POST`请求应当含有一些可以被服务器识别的随机数值，用来保证表单数据的来源和运行结果发送的去向是相同的。

Yii实现了一个CSRF防范机制，用来帮助防范基于`POST`的攻击。
这个机制的核心就是在cookie中设定一个随机数据，然后把它同表单提交的`POST`数据中的相应值进行比较。

默认情况下，CSRF防范是禁用的。如果你要启用它，可以编辑[应用配置](/doc/guide/basics.application#application-configuration)
中的组件中的[CHttpRequest]部分。

代码示例：

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCsrfValidation'=>true,
		),
	),
);
~~~

要显示一个表单，请使用[CHtml::form]而不要自己写HTML代码。因为[CHtml::form]可以自动地在表单中嵌入一个隐藏项，这个隐藏项储存着验证所需的随机数据，这些数据可在表单提交的时候发送到服务器进行验证。


Cookie攻击的防范
------------------------
保护cookie免受攻击是非常重要的。因为session ID通常存储在Cookie中。
如果攻击者窃取到了一个有效的session ID，他就可以使用这个session ID对应的session信息。

这里有几条防范对策：

* 您可以使用SSL来产生一个安全通道，并且只通过HTTPS连接来传送验证cookie。这样攻击者是无法解密所传送的cookie的。
* 设置cookie的过期时间，对所有的cookie和seesion令牌也这样做。这样可以减少被攻击的机会。
* 防范跨站代码攻击，因为它可以在用户的浏览器触发任意代码，这些代码可能会泄露用户的cookie。
* 在cookie有变动的时候验证cookie的内容。

Yii实现了一个cookie验证机制，可以防止cookie被修改。启用之后可以对cookie的值进行HMAC检查。

Cookie验证在默认情况下是禁用的。如果你要启用它，可以编辑[应用配置](/doc/guide/basics.application#application-configuration)
中的组件中的[CHttpRequest]部分。

代码示例：

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCookieValidation'=>true,
		),
	),
);
~~~

一定要使用经过Yii验证过的cookie数据。使用Yii内置的[cookies|CHttpRequest::cookies]组件来进行cookie操作，不要使用`$_COOKIES`。

~~~
[php]
// 检索一个名为$name的cookie值
$cookie=Yii::app()->request->cookies[$name];
$value=$cookie->value;
......
// 设置一个cookie
$cookie=new CHttpCookie($name,$value);
Yii::app()->request->cookies[$name]=$cookie;
~~~


<div class="revision">$Id: topics.security.txt 1173 2009-09-03 16:46:23Z i@imdong.net $</div>