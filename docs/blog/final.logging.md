Logging Errors
==============

A production Web application often needs sophisticated logging for various events. In our blog application, we would like to log the errors occurring when it is being used. Such errors could be programming mistakes or users' misuse of the system. Logging these errors will help us to improve the blog application.

We enable the error logging by modifying the [application configuration](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) as follows,

~~~
[php]
return array(
	'preload'=>array('log'),

	......

	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
		......
	),
);
~~~

With the above configuration, if an error or warning occurs, detailed information will be logged and saved in a file located under the directory `/wwwroot/blog/protected/runtime`.

The `log` component offers more advanced features, such as sending log messages to a list of email addresses, displaying log messages in JavaScript console window, etc. For more details, please refer to [the Guide](http://www.yiiframework.com/doc/guide/topics.logging).


<div class="revision">$Id$</div>