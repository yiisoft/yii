Installation
============

Installation of Yii mainly involves the following two steps:

   1. Download Yii Framework from [yiiframework.com](http://www.yiiframework.com/).
   2. Unpack the Yii release file to a Web-accessible directory.

> Tip: Yii does not need to be installed under a Web-accessible directory.
A Yii application has one entry script which is usually the only file that
needs to be exposed to Web users. Other PHP scripts, including those from
Yii, should be protected from Web access; otherwise they might be exploited 
by hackers.

Requirements
------------

After installing Yii, you may want to verify that your server satisfies
Yii's requirements. You can do so by accessing the requirement checker 
script via the following URL in a Web browser:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

Yii requires PHP 5.1, so the server must have PHP 5.1 or above installed and 
available to the web server.  Yii has been tested with [Apache HTTP server](http://httpd.apache.org/) 
on Windows and Linux.  It may also run on other Web servers and platforms, 
provided PHP 5.1 is supported.

<div class="revision">$Id$</div>
