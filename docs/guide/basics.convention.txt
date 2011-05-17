Conventions
===========

Yii favors conventions over configurations. Follow the conventions and one
can create sophisticated Yii applications without writing and managing
complex configurations. Of course, Yii can still be customized in nearly
every aspect with configurations when needed.

Below we describe conventions that are recommended for Yii programming.
For convenience, we assume that `WebRoot` is the directory that a Yii
application is installed at.

URL
---

By default, Yii recognizes URLs with the following format:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

The `r` GET variable refers to the
[route](/doc/guide/basics.controller#route) that can be resolved by Yii
into controller and action. If `ActionID` is omitted, the controller will
take the default action (defined via [CController::defaultAction]); and if
`ControllerID` is also omitted (or the `r` variable is absent), the
application will use the default controller (defined via
[CWebApplication::defaultController]).

With the help of [CUrlManager], it is possible to create and recognize
more SEO-friendly URLs, such as
`http://hostname/ControllerID/ActionID.html`. This feature is covered in
detail in [URL Management](/doc/guide/topics.url).

Code
----

Yii recommends naming variables, functions and class types in camel case which
capitalizes the first letter of each word in the name and joins them without spaces.
Variable and function names should have their first word all in lower-case,
in order to differentiate from class names (e.g. `$basePath`,
`runController()`, `LinkPager`). For private class member variables, it is
recommended to prefix their names with an underscore character (e.g.
`$_actionList`).

Because namespace is not supported prior to PHP 5.3.0, it is recommended
that classes be named in some unique way to avoid name conflict with
third-party classes. For this reason, all Yii framework classes are
prefixed with letter "C".

A special rule for controller class names is that they must be appended
with the word `Controller`. The controller ID is then defined as the class
name with first letter in lower case and the word `Controller` truncated.
For example, the `PageController` class will have the ID `page`. This rule
makes the application more secure. It also makes the URLs related with
controllers a bit cleaner (e.g. `/index.php?r=page/index` instead of
`/index.php?r=PageController/index`).

Configuration
-------------

A configuration is an array of key-value pairs. Each key represents the
name of a property of the object to be configured, and each value the
corresponding property's initial value. For example, `array('name'=>'My
application', 'basePath'=>'./protected')` initializes the `name` and
`basePath` properties to their corresponding array values.

Any writable properties of an object can be configured. If not configured,
the properties will take their default values. When configuring a property,
it is worthwhile to read the corresponding documentation so that the
initial value can be given properly.

File
----

Conventions for naming and using files depend on their types.

Class files should be named after the public class they contain. For
example, the [CController] class is in the `CController.php` file.  A
public class is a class that may be used by any other classes. Each class
file should contain at most one public class. Private classes (classes that
are only used by a single public class) may reside in the same file with
the public class.

View files should be named after the view name. For example, the `index`
view is in the `index.php` file. A view file is a PHP script file that
contains HTML and PHP code mainly for presentational purpose.

Configuration files can be named arbitrarily. A configuration file is a
PHP script whose sole purpose is to return an associative array
representing the configuration.

Directory
---------

Yii assumes a default set of directories used for various purposes. Each
of them can be customized if needed.

   - `WebRoot/protected`: this is the [application base
directory](/doc/guide/basics.application#application-base-directory) holding all
security-sensitive PHP scripts and data files. Yii has a default alias
named `application` associated with this path. This directory and
everything under should be protected from being accessed by Web users. It
can be customized via [CWebApplication::basePath].

   - `WebRoot/protected/runtime`: this directory holds private temporary
files generated during runtime of the application. This directory must be
writable by Web server process. It can be customized via
[CApplication::runtimePath].

   - `WebRoot/protected/extensions`: this directory holds all third-party
extensions. It can be customized via [CApplication::extensionPath]. Yii has
a default alias named `ext` associated with this path.

   - `WebRoot/protected/modules`: this directory holds all application
[modules](/doc/guide/basics.module), each represented as a subdirectory.

   - `WebRoot/protected/controllers`: this directory holds all controller
class files. It can be customized via [CWebApplication::controllerPath].

   - `WebRoot/protected/views`: this directory holds all view files,
including controller views, layout views and system views. It can be
customized via [CWebApplication::viewPath].

   - `WebRoot/protected/views/ControllerID`: this directory holds view
files for a single controller class. Here `ControllerID` stands for the ID
of the controller. It can be customized via [CController::viewPath].

   - `WebRoot/protected/views/layouts`: this directory holds all layout
view files. It can be customized via [CWebApplication::layoutPath].

   - `WebRoot/protected/views/system`: this directory holds all system
view files. System views are templates used in displaying exceptions and
errors. It can be customized via [CWebApplication::systemViewPath].

   - `WebRoot/assets`: this directory holds published asset files. An
asset file is a private file that may be published to become accessible to
Web users. This directory must be writable by Web server process. It can be
customized via [CAssetManager::basePath].

   - `WebRoot/themes`: this directory holds various themes that can be
applied to the application. Each subdirectory represents a single theme
whose name is the subdirectory name. It can be customized via
[CThemeManager::basePath].

Database
--------

Most Web applications are backed by some database. For best practice, we propose
the following naming conventions for database tables and columns. Note that they
are not required by Yii.

   - Both database tables and columns are named in lower case.

   - Words in a name should be separated using underscores (e.g. `product_order`).

   - For table names, you may use either singular or plural names, but not both.
For simplicity, we recommend using singular names.

   - Table names may be prefixed with a common token such as `tbl_`. This is
especially useful when the tables of an application coexist in the same database
with the tables of another application. The two sets of tables can be readily
separate by using different table name prefixes.



<div class="revision">$Id$</div>