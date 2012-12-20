Controller
==========

A `controller` is an instance of [CController] or of a class that extends [CController]. 
It is created by the application object when the user requests it. When a controller
runs, it performs the requested action, which usually brings in the needed
models and renders an appropriate view. An `action`, in its simplest form, is
just a controller class method whose name starts with `action`.

A controller has a default action. When the user request does not specify
which action to execute, the default action will be executed. By default,
the default action is named as `index`. It can be changed by setting the 
public instance variable, [CController::defaultAction].

The following code defines a `site` controller, an `index` action (the default
action), and a `contact` action:

~~~
[php]
class SiteController extends CController
{
	public function actionIndex()
	{
		// ...
	}

	public function actionContact()
	{
		// ...
	}
}
~~~


Route
-----

Controllers and actions are identified by IDs. A Controller ID is in the
format `path/to/xyz`, which corresponds to the controller class file
`protected/controllers/path/to/XyzController.php`, where the token `xyz`
should be replaced by actual names; e.g. `post` corresponds to
`protected/controllers/PostController.php`. Action ID is the action method
name without the `action` prefix. For example, if a controller class
contains a method named `actionEdit`, the ID of the corresponding action
would be `edit`.

Users request a particular controller and action in terms of route. A
route is formed by concatenating a controller ID and an action ID, separated
by a slash. For example, the route `post/edit` refers to `PostController`
and its `edit` action. By default, the URL `http://hostname/index.php?r=post/edit` 
would request the post controller and the edit action.

>Note: By default, routes are case-sensitive. It is
>possible to make routes case-insensitive by setting [CUrlManager::caseSensitive]
>to false in the application configuration. When in case-insensitive mode,
>make sure you follow the convention that directories containing controller
>class files are in lowercase, and both [controller map|CWebApplication::controllerMap]
>and [action map|CController::actions] have lowercase keys.

An application can contain [modules](/doc/guide/basics.module). The route for
a controller action inside a module is in the format `moduleID/controllerID/actionID`.
For more details, see the [section about modules](/doc/guide/basics.module).


Controller Instantiation
------------------------

A controller instance is created when [CWebApplication] handles an
incoming request. Given the ID of the controller, the application will use
the following rules to determine what the controller class is and where the
class file is located.

   - If [CWebApplication::catchAllRequest] is specified, a controller
will be created based on this property, and the user-specified controller ID
will be ignored. This is mainly used to put the application in
maintenance mode and display a static notice page.

   - If the ID is found in [CWebApplication::controllerMap], the
corresponding controller configuration will be used to create the
controller instance.

   - If the ID is in the format `'path/to/xyz'`, the controller class
name is assumed to be `XyzController` and the corresponding class file is
`protected/controllers/path/to/XyzController.php`. For example, a controller
ID `admin/user` would be mapped to the controller class `UserController`
and the class file `protected/controllers/admin/UserController.php`.
If the class file does not exist, a 404 [CHttpException] will be raised.

When [modules](/doc/guide/basics.module) are used, the above process is
slightly different. In particular, the application will check whether or not 
the ID refers to a controller inside a module, and if so, the module instance 
will be created first, followed by the controller instance.


Action
------

As previously noted, an action can be defined as a method whose name starts
with the word `action`. A more advanced technique is to define an action class
and ask the controller to instantiate it when requested. This allows
actions to be reused and thus introduces more reusability.

To define a new action class, do the following:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// place the action logic here
	}
}
~~~

To make the controller aware of this action, we override the
[actions()|CController::actions] method of our controller class:

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

In the above, we use the path alias
`application.controllers.post.UpdateAction` to specify that the action
class file is `protected/controllers/post/UpdateAction.php`.

By writing class-based actions, we can organize an application in a modular
fashion. For example, the following directory structure may be used to
organize the code for controllers:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~

### Action Parameter Binding

Since version 1.1.4, Yii has added support for automatic action parameter binding.
That is, a controller action method can define named parameters whose value will
be automatically populated from `$_GET` by Yii.

To illustrate how this works, let's assume we need to write a `create` action for 
`PostController`.  The action requires two parameters:

* `category`: an integer indicating the category ID under which the new post will
   be created;
* `language`: a string indicating the language code that the new post will be in.

We may end up with the following boring code for the purpose of retrieving the needed
parameter values from `$_GET`:

~~~
[php]
class PostController extends CController
{
	public function actionCreate()
	{
		if(isset($_GET['category']))
			$category=(int)$_GET['category'];
		else
			throw new CHttpException(404,'invalid request');

		if(isset($_GET['language']))
			$language=$_GET['language'];
		else
			$language='en';

		// ... fun code starts here ...
	}
}
~~~

Now using the action parameter feature, we can achieve our task more pleasantly:

~~~
[php]
class PostController extends CController
{
	public function actionCreate($category, $language='en')
	{
		$category=(int)$category;

		// ... fun code starts here ...
	}
}
~~~

Notice that we add two parameters to the action method `actionCreate`.
The name of these parameters must be exactly the same as the ones we expect
from `$_GET`. The `$language` parameter takes a default value `en` in case
the request does not include such a parameter.  Because `$category`
does not have a default value, if the request does not include a `category` 
parameter, a [CHttpException] (error code 400) will be thrown automatically.

Starting from version 1.1.5, Yii also supports array type detection for action parameters.
This is done by PHP type hinting using syntax like the following:

~~~
[php]
class PostController extends CController
{
	public function actionCreate(array $categories)
	{
		// Yii will make sure that $categories is an array
	}
}
~~~

That is, we add the keyword `array` in front of `$categories` in the method parameter
declaration. By doing so, if `$_GET['categories']` is a simple string, it will be
converted into an array consisting of that string.

> Note: If a parameter is declared without the `array` type hint, it means the parameter
> must be a scalar (i.e., not an array). In this case, passing in an array parameter via
> `$_GET` would cause an HTTP exception.

Starting from version 1.1.7, automatic parameter binding also works for
class-based actions. When the `run()` method of an action class is defined
with some parameters, they will be populated with the corresponding named
request parameter values. For example,

~~~
[php]
class UpdateAction extends CAction
{
	public function run($id)
	{
		// $id will be populated with $_GET['id']
	}
}
~~~


Filter
------

Filter is a piece of code that is configured to be executed before and/or
after a controller action executes. For example, an access control filter
may be executed to ensure that the user is authenticated before executing
the requested action; a performance filter may be used to measure the time
spent executing the action.

An action can have multiple filters. The filters are executed in the order
that they appear in the filter list. A filter can prevent the execution of
the action and the rest of the unexecuted filters.

A filter can be defined as a controller class method. The method name must
begin with `filter`. For example, a method named `filterAccessControl` 
defines a filter named `accessControl`. The filter method must have the 
right signature:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// call $filterChain->run() to continue filter and action execution
}
~~~

where `$filterChain` is an instance of [CFilterChain] which represents the
filter list associated with the requested action. Inside a filter method,
we can call `$filterChain->run()` to continue filter and action execution.

A filter can also be an instance of [CFilter] or its child class. The
following code defines a new filter class:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logic being applied before the action is executed
		return true; // false if the action should not be executed
	}

	protected function postFilter($filterChain)
	{
		// logic being applied after the action is executed
	}
}
~~~

To apply filters to actions, we need to override the
`CController::filters()` method. The method should return an array of
filter configurations. For example,

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

The above code specifies two filters: `postOnly` and `PerformanceFilter`.
The `postOnly` filter is method-based (the corresponding filter method is
defined in [CController] already); while the `PerformanceFilter` filter is
object-based. The path alias `application.filters.PerformanceFilter`
specifies that the filter class file is
`protected/filters/PerformanceFilter`. We use an array to configure
`PerformanceFilter` so that it may be used to initialize the property
values of the filter object. Here the `unit` property of
`PerformanceFilter` will be initialized as `'second'`.

Using the plus and the minus operators, we can specify which actions the
filter should and should not be applied to. In the above, the `postOnly`
filter will be applied to the `edit` and `create` actions, while
`PerformanceFilter` filter will be applied to all actions EXCEPT `edit` and
`create`. If neither plus nor minus appears in the filter configuration,
the filter will be applied to all actions.

<div class="revision">$Id$</div>
