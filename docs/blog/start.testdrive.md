Testdriving with Yii
====================

In this section, we describe how to create a skeleton application that will serve as our starting point. For simplicity, we assume that the document root of our Web server is `/wwwroot` and the corresponding URL is `http://www.example.com/`.


Installing Yii
--------------

We first install the Yii framework. Grab a copy of the Yii release file (version 1.1.1 or above) from [www.yiiframework.com](http://www.yiiframework.com/download) and unpack it to the directory `/wwwroot/yii`. Double check to make sure that there is a directory `/wwwroot/yii/framework`.

> Tip: The Yii framework can be installed anywhere in the file system, not necessarily under a Web folder. Its `framework` directory contains all framework code and is the only framework directory needed when deploying an Yii application. A single installation of Yii can be used by multiple Yii applications.

After installing Yii, open a browser window and access the URL `http://www.example.com/yii/requirements/index.php`. It shows the requirement checker provided in the Yii release. For our blog application, besides the minimal requirements needed by Yii, we also need to enable both the `pdo` and `pdo_sqlite` PHP extensions so that we can access SQLite databases.


Creating Skeleton Application
-----------------------------

We then use the `yiic` tool to create a skeleton application under the directory `/wwwroot/blog`. The `yiic` tool is a command line tool provided in the Yii release. It can be used to generate code to reduce certain repetitive coding tasks.

Open a command window and execute the following command:

~~~
% /wwwroot/yii/framework/yiic webapp /wwwroot/blog
Create a Web application under '/wwwroot/blog'? [Yes|No]y
......
~~~

> Tip: In order to use the `yiic` tool as shown above, the CLI PHP program must be on the command search path. If not, the following command may be used instead:
>
>~~~
> path/to/php /wwwroot/yii/framework/yiic.php webapp /wwwroot/blog
>~~~

To try out the application we just created, open a Web browser and navigate to the URL `http://www.example.com/blog/index.php`. We should see that our skeleton application already has four fully functional pages: the homepage, the about page, the contact page and the login page.

In the following, we briefly describe what we have in this skeleton application.

###Entry Script

We have an [entry script](http://www.yiiframework.com/doc/guide/basics.entry) file `/wwwroot/blog/index.php` which has the following content:

~~~
[php]
<?php
$yii='/wwwroot/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
Yii::createWebApplication($config)->run();
~~~

This is the only script that Web users can directly access. The script first includes the Yii bootstrap file `yii.php`. It then creates an [application](http://www.yiiframework.com/doc/guide/basics.application) instance with the specified configuration and executes the application.


###Base Application Directory

We also have an [application base directory](http://www.yiiframework.com/doc/guide/basics.application#application-base-directory) `/wwwroot/blog/protected`. The majority of our code and data will be placed under this directory, and it should be protected from being accessed by Web users. For [Apache httpd Web server](http://httpd.apache.org/), we place under this directory a `.htaccess` file with the following content:

~~~
deny from all
~~~

For other Web servers, please refer to the corresponding manual on how to protect a directory from being accessed by Web users.


Application Workflow
--------------------

To help understand how Yii works, we describe the main workflow in our skeleton application when a user is accessing its contact page:

 0. The user requests the URL `http://www.example.com/blog/index.php?r=site/contact`;
 1. The [entry script](http://www.yiiframework.com/doc/guide/basics.entry) is executed by the Web server to process the request;
 2. An [application](http://www.yiiframework.com/doc/guide/basics.application) instance is created and configured with initial property values specified in the application configuration file `/wwwroot/blog/protected/config/main.php`;
 3. The application resolves the request into a [controller](http://www.yiiframework.com/doc/guide/basics.controller) and a [controller action](http://www.yiiframework.com/doc/guide/basics.controller#action). For the contact page request, it is resolved as the `site` controller and the `contact` action (the `actionContact` method in `/wwwroot/blog/protected/controllers/SiteController.php`);
 4. The application creates the `site` controller in terms of a `SiteController` instance and then executes it;
 5. The `SiteController` instance executes the `contact` action by calling its `actionContact()` method;
 6. The `actionContact` method renders a [view](http://www.yiiframework.com/doc/guide/basics.view) named `contact` to the Web user. Internally, this is achieved by including the view file `/wwwroot/blog/protected/views/site/contact.php` and embedding the result into the [layout](http://www.yiiframework.com/doc/guide/basics.view#layout) file `/wwwroot/blog/protected/views/layouts/column1.php`.


<div class="revision">$Id$</div>