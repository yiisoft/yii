Using 3rd-Party Libraries
=========================

Yii is carefully designed so that third-party libraries can be
easily integrated to further extend Yii's functionalities.
When using third-party libraries in a project, developers often
encounter issues about class naming and file inclusion.
Because all Yii classes are prefixed with letter `C`, it is less
likely class naming issue would occur; and because Yii relies on
[SPL autoload](http://us3.php.net/manual/en/function.spl-autoload.php)
to perform class file inclusion, it can play nicely with other libraries
if they use the same autoloading feature or PHP include path to include
class files.


Below we use an example to illustrate how to use the
[Zend_Search_Lucene](http://www.zendframework.com/manual/en/zend.search.lucene.html)
component from the [Zend framework](http://www.zendframework.com) in a Yii application.

First, we extract the Zend framework release file to a directory
under `protected/vendors`, assuming `protected` is the
[application base directory](/doc/guide/basics.application#application-base-directory).
Verify that the file `protected/vendors/Zend/Search/Lucene.php` exists.

Second, at the beginning of a controller class file, insert the following lines:

~~~
[php]
Yii::import('application.vendors.*');
require_once('Zend/Search/Lucene.php');
~~~

The above code includes the class file `Lucene.php`. Because we are using
a relative path, we need to change the PHP include path so that the file
can be located correctly. This is done by calling `Yii::import` before `require_once`.

Once the above set up is ready, we can use the `Lucene` class in a controller action,
like the following:

~~~
[php]
$lucene=new Zend_Search_Lucene($pathOfIndex);
$hits=$lucene->find(strtolower($keyword));
~~~

Using namespaced 3rd-Party Libraries
------------------------------------

In order to use namespaced library that follows
[PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
(such as Zend Framework 2 or Symfony2) you need to register its root as path alias.

As an example we'll use [Imagine](https://github.com/avalanche123/Imagine).
If we put the `Imagine` directory under `protected/vendors` we'll be able to use
it like the following:

~~~
[php]
Yii::setPathOfAlias('Imagine',Yii::getPathOfAlias('application.vendors.Imagine'));

// Then standard code from Imagine guide:
// $imagine = new Imagine\Gd\Imagine();
// etc.
~~~

In the code above the name of the alias we've defined should match the first namespace
part used in the library.

Using Yii in 3rd-Party Systems
------------------------------

Yii can also be used as a self-contained library to support developing and enhancing
existing 3rd-party systems, such as WordPress, Joomla, etc. To do so, include
the following code in the bootstrap code of the 3rd-party system:

~~~
[php]
require_once('path/to/yii.php');
Yii::createWebApplication('path/to/config.php');
~~~

The above code is very similar to the bootstrap code used by a typical Yii application
except one thing: it does not call the `run()` method after creating the Web application
instance.

Now we can use most features offered by Yii when developing 3rd-party enhancements. For example,
we can use `Yii::app()` to access the application instance; we can use the database features
such as DAO and ActiveRecord; we can use the model and validation feature; and so on.


<div class="revision">$Id$</div>