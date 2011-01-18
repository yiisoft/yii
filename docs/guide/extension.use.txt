Using Extensions
================

Using an extension usually involves the following three steps:

  1. Download the extension from Yii's
     [extension repository](http://www.yiiframework.com/extensions/).
  2. Unpack the extension under the `extensions/xyz` subdirectory of
     [application base directory](/doc/guide/basics.application#application-base-directory),
     where `xyz` is the name of the extension.
  3. Import, configure and use the extension.

Each extension has a name that uniquely identifies it among all extensions.
Given an extension named as `xyz`, we can always use the path alias
`ext.xyz` to locate its base directory which contains all files of `xyz`.

Different extensions have different requirements about importing,
configuration and usage. In the following, we summarize common usage scenarios
about extensions, according to their categorization described in the
[overview](/doc/guide/extension.overview).


Zii Extensions
--------------

Before we start describing the usage of third-party extensions, we would like to introduce
the Zii extension library, which is a set of extensions developed by the Yii developer team
and included in every release.

When using a Zii extension, one must refer to the corresponding class using a path alias
in the form of `zii.path.to.ClassName`. Here the root alias `zii` is predefined by Yii. It refers
to the root directory of the Zii library. For example, to use [CGridView], we would use the
following code in a view script when referring to the extension:

~~~
[php]
$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
));
~~~


Application Component
---------------------

To use an [application component](/doc/guide/basics.application#application-component),
we first need to change the [application configuration](/doc/guide/basics.application#application-configuration)
by adding a new entry to its `components` property, like the following:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'ext.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // other component configurations
    ),
);
~~~

Then, we can access the component at any place using `Yii::app()->xyz`. The component
will be lazily created (that is, created when it is accessed for the first time)
unless we list it the `preload` property.


Behavior
--------

[Behavior](/doc/guide/basics.component#component-behavior) can be used in all sorts of components.
Its usage involves two steps. In the first step, a behavior is attached to a target component.
In the second step, a behavior method is called via the target component. For example:

~~~
[php]
// $name uniquely identifies the behavior in the component
$component->attachBehavior($name,$behavior);
// test() is a method of $behavior
$component->test();
~~~

More often, a behavior is attached to a component using a configurative way instead of
calling the `attachBehavior` method. For example, to attach a behavior to an
[application component](/doc/guide/basics.application#application-component), we could
use the following
[application configuration](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'behaviors'=>array(
				'xyz'=>array(
					'class'=>'ext.xyz.XyzBehavior',
					'property1'=>'value1',
					'property2'=>'value2',
				),
			),
		),
		//....
	),
);
~~~

The above code attaches the `xyz` behavior to the `db` application component. We can do so
because [CApplicationComponent] defines a property named `behaviors`. By setting this property
with a list of behavior configurations, the component will attach the corresponding behaviors
when it is being initialized.

For [CController], [CFormModel] and [CActiveRecord] classes which usually need to be extended,
attaching behaviors can be done by overriding their `behaviors()` method. The classes will
automatically attach any behaviors declared in this method during initialization. For example,

~~~
[php]
public function behaviors()
{
	return array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzBehavior',
			'property1'=>'value1',
			'property2'=>'value2',
		),
	);
}
~~~


Widget
------

[Widgets](/doc/guide/basics.view#widget) are mainly used in [views](/doc/guide/basics.view).
Given a widget class `XyzClass` belonging to the `xyz` extension, we can use it in
a view as follows,

~~~
[php]
// widget that does not need body content
<?php $this->widget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// widget that can contain body content
<?php $this->beginWidget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...body content of the widget...

<?php $this->endWidget(); ?>
~~~

Action
------

[Actions](/doc/guide/basics.controller#action) are used by a [controller](/doc/guide/basics.controller)
to respond specific user requests. Given an action class `XyzClass` belonging to
the `xyz` extension, we can use it by overriding the [CController::actions] method
in our controller class:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// other actions
		);
	}
}
~~~

Then, the action can be accessed via [route](/doc/guide/basics.controller#route)
`test/xyz`.

Filter
------
[Filters](/doc/guide/basics.controller#filter) are also used by a [controller](/doc/guide/basics.controller).
Their mainly pre- and post-process the user request when it is handled by an
[action](/doc/guide/basics.controller#action).
Given a filter class `XyzClass` belonging to
the `xyz` extension, we can use it by overriding the [CController::filters] method
in our controller class:

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// other filters
		);
	}
}
~~~

In the above, we can use plus and minus operators in the first array element
to apply the filter to limited actions only. For more details, please refer
to the documentation of [CController].

Controller
----------
A [controller](/doc/guide/basics.controller) provides a set of actions that can
be requested by users. In order to use a controller extension, we need to
configure the [CWebApplication::controllerMap] property in the
[application configuration](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// other controllers
	),
);
~~~

Then, an action `a` in the controller can be accessed via
[route](/doc/guide/basics.controller#route) `xyz/a`.

Validator
---------
A validator is mainly used in a [model](/doc/guide/basics.model) class
(one that extends from either [CFormModel] or [CActiveRecord]).
Given a validator class `XyzClass` belonging to
the `xyz` extension, we can use it by overriding the [CModel::rules] method
in our model class:

~~~
[php]
class MyModel extends CActiveRecord // or CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// other validation rules
		);
	}
}
~~~

Console Command
---------------
A [console command](/doc/guide/topics.console) extension usually enhances
the `yiic` tool with an additional command. Given a console command
`XyzClass` belonging to the `xyz` extension, we can use it by configuring
the configuration for the console application:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// other commands
	),
);
~~~

Then, we can use the `yiic` tool is equipped with an additional
command `xyz`.

> Note: A console application usually uses a configuration file
that is different from the one used by a Web application. If an application
is created using `yiic webapp` command, then the configuration file
for the console application `protected/yiic` is `protected/config/console.php`,
while the configuration file for the Web application is `protected/config/main.php`.


Module
------
Please refer to the section about [modules](/doc/guide/basics.module#using-module) on how to use a module.


Generic Component
-----------------
To use a generic [component](/doc/guide/basics.component), we first
need to include its class file by using

~~~
Yii::import('ext.xyz.XyzClass');
~~~

Then, we can either create an instance of the class, configure its properties,
and call its methods. We may also extend it to create new child classes.


<div class="revision">$Id$</div>