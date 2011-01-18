Performance Tuning
==================

Performance of Web applications is affected by many factors. Database
access, file system operations, network bandwidth are all potential
affecting factors. Yii has tried in every aspect to reduce the performance
impact caused by the framework. But still, there are many places in the
user application that can be improved to boost performance.

Enabling APC Extension
----------------------

Enabling the [PHP APC
extension](http://www.php.net/manual/en/book.apc.php) is perhaps the
easiest way to improve the overall performance of an application. The
extension caches and optimizes PHP intermediate code and avoids the time
spent in parsing PHP scripts for every incoming request.

Disabling Debug Mode
--------------------

Disabling debug mode is another easy way to improve performance. A Yii
application runs in debug mode if the constant `YII_DEBUG` is defined as
true. Debug mode is useful during development stage, but it would impact
performance because some components cause extra burden in debug mode. For
example, the message logger may record additional debug information for
every message being logged.

Using `yiilite.php`
-------------------

When the [PHP APC extension](http://www.php.net/manual/en/book.apc.php) is
enabled, we can replace `yii.php` with a different Yii bootstrap file named
`yiilite.php` to further boost the performance of a Yii-powered application.

The file `yiilite.php` comes with every Yii release. It is the result of
merging some commonly used Yii class files. Both comments and trace
statements are stripped from the merged file. Therefore, using
`yiilite.php` would reduce the number of files being included and avoid
execution of trace statements.

Note, using `yiilite.php` without APC may actually reduce performance,
because `yiilite.php` contains some classes that are not necessarily used
in every request and would take extra parsing time. It is also observed that
using `yiilite.php` is slower with some server configurations, even when
APC is turned on. The best way to judge whether to use `yiilite.php` or not
is to run a benchmark using the included `hello world` demo.

Using Caching Techniques
------------------------

As described in the [Caching](/doc/guide/caching.overview) section, Yii
provides several caching solutions that may improve the performance of a
Web application significantly. If the generation of some data takes long
time, we can use the [data caching](/doc/guide/caching.data) approach to
reduce the data generation frequency; If a portion of page remains
relatively static, we can use the [fragment
caching](/doc/guide/caching.fragment) approach to reduce its rendering
frequency; If a whole page remains relative static, we can use the [page
caching](/doc/guide/caching.page) approach to save the rendering cost for
the whole page.

If the application is using [Active Record](/doc/guide/database.ar), we
should turn on the schema caching to save the time of parsing database
schema. This can be done by configuring the
[CDbConnection::schemaCachingDuration] property to be a value greater than 0.

Besides these application-level caching techniques, we can also use
server-level caching solutions to boost the application performance. As a
matter of fact, the [APC caching](/doc/guide/topics.performance#enabling-apc-extension) we
described earlier belongs to this category. There are other server
techniques, such as [Zend Optimizer](http://www.zend.com/en/products/guard/zend-optimizer),
[eAccelerator](http://eaccelerator.net/),
[Squid](http://www.squid-cache.org/), to name a few.

Database Optimization
---------------------

Fetching data from database is often the main performance bottleneck in a
Web application. Although using caching may alleviate the performance hit,
it does not fully solve the problem. When the database contains enormous
data and the cached data is invalid, fetching the latest data could be
prohibitively expensive without proper database and query design.

Design index wisely in a database. Indexing can make `SELECT` queries much
faster, but it may slow down `INSERT`, `UPDATE` or `DELETE` queries.

For complex queries, it is recommended to create a database view for it
instead of issuing the queries inside the PHP code and asking DBMS to parse
them repetitively.

Do not overuse [Active Record](/doc/guide/database.ar). Although [Active
Record](/doc/guide/database.ar) is good at modelling data in an OOP
fashion, it actually degrades performance due to the fact that it needs to
create one or several objects to represent each row of query result. For
data intensive applications, using [DAO](/doc/guide/database.dao) or
database APIs at lower level could be a better choice.

Last but not least, use `LIMIT` in your `SELECT` queries. This avoids
fetching overwhelming data from database and exhausting the memory
allocated to PHP.

Minimizing Script Files
-----------------------

Complex pages often need to include many external JavaScript and CSS files. Because each file would cause one extra round trip to the server and back, we should minimize the number of script files by merging them into fewer ones. We should also consider reducing the size of each script file to reduce the network transmission time. There are many tools around to help on these two aspects.

For a page generated by Yii, chances are that some script files are rendered by components that we do not want to modify (e.g. Yii core components, third-party components). In order to minimizing these script files, we need two steps.

First, we declare the scripts to be minimized by configuring the [scriptMap|CClientScript::scriptMap] property of the [clientScript|CWebApplication::clientScript] application component. This can be done either in the application configuration or in code. For example,

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>'/js/all.js',
	'jquery.ajaxqueue.js'=>'/js/all.js',
	'jquery.metadata.js'=>'/js/all.js',
	......
);
~~~

What the above code does is that it maps those JavaScript files to the URL `/js/all.js`. If any of these JavaScript files need to be included by some components, Yii will include the URL (once) instead of the individual script files.

Second, we need to use some tools to merge (and perhaps compress) the JavaScript files into a single one and save it as `js/all.js`.

The same trick also applies to CSS files.

We can also improve page loading speed with the help of [Google AJAX Libraries API](http://code.google.com/apis/ajaxlibs/). For example, we can include `jquery.js` from Google servers instead of our own server. To do so, we first configure the `scriptMap` as follows,

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>false,
	'jquery.ajaxqueue.js'=>false,
	'jquery.metadata.js'=>false,
	......
);
~~~

By mapping these script files to false, we prevent Yii from generating the code to include these files. Instead, we write the following code in our pages to explicitly include the script files from Google,

~~~
[php]
<head>
<?php echo CGoogleApi::init(); ?>

<?php echo CHtml::script(
	CGoogleApi::load('jquery','1.3.2') . "\n" .
	CGoogleApi::load('jquery.ajaxqueue.js') . "\n" .
	CGoogleApi::load('jquery.metadata.js')
); ?>
......
</head>
~~~

<div class="revision">$Id$</div>