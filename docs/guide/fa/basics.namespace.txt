Path Alias and Namespace
========================

Yii uses path aliases extensively. A path alias is associated with a
directory or file path. It is specified in dot syntax, similar to that of
widely adopted namespace format:

~~~
RootAlias.path.to.target
~~~

where `RootAlias` is the alias of some existing directory.

By using [YiiBase::getPathOfAlias()], an alias can be translated to its
corresponding path. For example, `system.web.CController` would be
translated as `yii/framework/web/CController`.

We can also use [YiiBase::setPathOfAlias()] to define new root path aliases.


Root Alias
----------

For convenience, Yii predefines the following root aliases:

 - `system`: refers to the Yii framework directory;
 - `zii`: refers to the [Zii library](/doc/guide/extension.use#zii-extensions) directory;
 - `application`: refers to the application's [base directory](/doc/guide/basics.application#application-base-directory);
 - `webroot`: refers to the directory containing the [entry script](/doc/guide/basics.entry) file.
 - `ext`: refers to the directory containing all third-party [extensions](/doc/guide/extension.overview).

Additionally, if an application uses [modules](/doc/guide/basics.module), each module will have a predefined
root alias that has the same name as the module ID and refers to the module's base path. For example,
if an application uses a module whose ID is `users`, a root alias named `users` will be predefined.


Importing Classes
-----------------

Using aliases, it is very convenient to include the definition of a class.
For example, if we want to include the [CController] class, we can call the following:

~~~
[php]
Yii::import('system.web.CController');
~~~

The [import|YiiBase::import] method differs from `include` and `require`
in that it is more efficient. The class definition being imported is
actually not included until it is referenced for the first time (implemented
via PHP autoloading mechanism). Importing the same namespace multiple times
is also much faster than `include_once` and `require_once`.

> Tip: When referring to a class defined by the Yii framework, we do not
> need to import or include it. All core Yii classes are pre-imported.


###Using Class Map

Starting from version 1.1.5, Yii allows user classes to be pre-imported via
a class mapping mechanism that is also used by core Yii classes. Pre-imported
classes can be used anywhere in a Yii application without being explicitly
imported or included. This feature is most useful for a framework or library
that is built on top of Yii.

To pre-import a set of classes, the following code must be executed before
[CWebApplication::run()] is invoked:

~~~
[php]
Yii::$classMap=array(
	'ClassName1' => 'path/to/ClassName1.php',
	'ClassName2' => 'path/to/ClassName2.php',
	......
);
~~~


Importing Directories
---------------------

We can also use the following syntax to import a whole directory so that
the class files under the directory can be automatically included when
needed.

~~~
[php]
Yii::import('system.web.*');
~~~

Besides [import|YiiBase::import], aliases are also used in many other
places to refer to classes. For example, an alias can be passed to
[Yii::createComponent()] to create an instance of the corresponding class,
even if the class file was not included previously.


Namespace
---------

A namespace refers to a logical grouping of some class names so that
they can be differentiated from other class names even if their names
are the same. Do not confuse path alias with namespace. A path alias
is merely a convenient way of naming a file or directory. It has nothing
to do with a namespace.

> Tip: Because PHP prior to 5.3.0 does not support namespace
intrinsically, you cannot create instances of two classes who have the same
name but with different definitions. For this reason, all Yii framework
classes are prefixed with a letter 'C' (meaning 'class') so that they can
be differentiated from user-defined classes. It is recommended that the
prefix 'C' be reserved for Yii framework use only, and user-defined classes
be prefixed with other letters.


Namespaced Classes
------------------

A namespaced class refers to a class declared within a non-global namespace.
For example, the `application\components\GoogleMap` class is declared within
the namespace `application\components`. Using namespaced classes requires PHP 5.3.0 or above.

Starting from version 1.1.5, it is possible to use a namespaced class without
including it explicitly. For example, we can create a new instance of
`application\components\GoogleMap` without including the corresponding class file
explicitly. This is made possible with the enhanced Yii class autoloading mechanism.

In order to be able to autoload a namespaced class, the namespace must be named in
a way similar to naming a path alias. For example, the class `application\components\GoogleMap`
must be stored in a file that can be aliased as `application.components.GoogleMap`.


<div class="revision">$Id$</div>