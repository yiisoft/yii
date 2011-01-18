Module
======

A module is a self-contained software unit that consists of [models](/doc/guide/basics.model), [views](/doc/guide/basics.view), [controllers](/doc/guide/basics.controller) and other supporting components. In many aspects, a module resembles to an [application](/doc/guide/basics.application). The main difference is that a module cannot be deployed alone and it must reside inside of an application. Users can access the controllers in a module like they do with normal application controllers.

Modules are useful in several scenarios. For a large-scale application, we may divide it into several modules, each being developed and maintained separately. Some commonly used features, such as user management, comment management, may be developed in terms of modules so that they can be reused easily in future projects.


Creating Module
---------------

A module is organized as a directory whose name serves as its unique [ID|CWebModule::id]. The structure of the module directory is similar to that of the [application base directory](/doc/guide/basics.application#application-base-directory). The following shows the typical directory structure of a module named `forum`:

~~~
forum/
   ForumModule.php            the module class file
   components/                containing reusable user components
      views/                  containing view files for widgets
   controllers/               containing controller class files
      DefaultController.php   the default controller class file
   extensions/                containing third-party extensions
   models/                    containing model class files
   views/                     containing controller view and layout files
      layouts/                containing layout view files
      default/                containing view files for DefaultController
         index.php            the index view file
~~~

A module must have a module class that extends from [CWebModule]. The class name is determined using the expression `ucfirst($id).'Module'`, where `$id` refers to the module ID (or the module directory name). The module class serves as the central place for storing information shared among the module code. For example, we can use [CWebModule::params] to store module parameters, and use [CWebModule::components] to share [application components](/doc/guide/basics.application#application-component) at the module level.

> Tip: We can use the module generator in Gii to create the basic skeleton of a new module.


Using Module
------------

To use a module, first place the module directory under `modules` of the [application base directory](/doc/guide/basics.application#application-base-directory). Then declare the module ID in the [modules|CWebApplication::modules] property of the application. For example, in order to use the above `forum` module, we can use the following [application configuration](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

A module can also be configured with initial property values. The usage is very similar to configuring [application components](/doc/guide/basics.application#application-component). For example, the `forum` module may have a property named `postPerPage` in its module class which can be configured in the [application configuration](/doc/guide/basics.application#application-configuration) as follows:

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

The module instance may be accessed via the [module|CController::module] property of the currently active controller. Through the module instance, we can then access information that are shared at the module level. For example, in order to access the above `postPerPage` information, we can use the following expression:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// or the following if $this refers to the controller instance
// $postPerPage=$this->module->postPerPage;
~~~

A controller action in a module can be accessed using the [route](/doc/guide/basics.controller#route) `moduleID/controllerID/actionID`. For example, assuming the above `forum` module has a controller named `PostController`, we can use the [route](/doc/guide/basics.controller#route) `forum/post/create` to refer to the `create` action in this controller. The corresponding URL for this route would be `http://www.example.com/index.php?r=forum/post/create`.

> Tip: If a controller is in a sub-directory of `controllers`, we can still use the above [route](/doc/guide/basics.controller#route) format. For example, assuming `PostController` is under `forum/controllers/admin`, we can refer to the `create` action using `forum/admin/post/create`.


Nested Module
-------------

Modules can be nested in unlimited levels. That is, a module can contain another module which can contain yet another module. We call the former *parent module* while the latter *child module*. Child modules must be declared in the [modules|CWebModule::modules] property of their parent module, like we declare modules in the application configuration shown as above.

To access a controller action in a child module, we should use the route `parentModuleID/childModuleID/controllerID/actionID`.


<div class="revision">$Id$</div>