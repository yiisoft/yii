Creating Extensions
===================

Because an extension is meant to be used by third-party developers, it takes
some additional efforts to create it. The followings are some general guidelines:

* An extension should be self-contained. That is, its external dependency should
  be minimal. It would be a headache for its users if an extension requires
  installation of additional packages, classes or resource files.
* Files belonging to an extension should be organized under the same
  directory whose name is the extension name
* Classes in an extension should be prefixed with some letter(s) to avoid
  naming conflict with classes in other extensions.
* An extension should come with detailed installation and API documentation.
  This would reduce the time and effort needed by other developers when they
  use the extension.
* An extension should be using an appropriate license. If you want to make
  your extension to be used by both open-source and closed-source projects,
  you may consider using licenses such as BSD, MIT, etc., but not GPL as it
  requires its derived code to be open-source as well.

In the following, we describe how to create a new extension, according to
its categorization as described in [overview](/doc/guide/extension.overview).
These descriptions also apply when you are creating a component mainly used
in your own projects.

Application Component
---------------------

An [application component](/doc/guide/basics.application#application-component)
should implement the interface [IApplicationComponent] or extend
from [CApplicationComponent]. The main method needed to be implemented is
[IApplicationComponent::init] in which the component performs some initialization
work. This method is invoked after the component is created and the initial property
values (specified in [application configuration](/doc/guide/basics.application#application-configuration))
are applied.

By default, an application component is created and initialized only when it
is accessed for the first time during request handling. If an application component
needs to be created right after the application instance is created, it should
require the user to list its ID in the [CApplication::preload] property.


Behavior
--------

To create a behavior, one must implement the [IBehavior] interface. For convenience,
Yii provides a base class [CBehavior] that already implements this interface and
provides some additional convenient methods. Child classes mainly need to implement
the extra methods that they intend to make available to the components being attached to.

When developing behaviors for [CModel] and [CActiveRecord], one can also extend
[CModelBehavior] and [CActiveRecordBehavior], respectively. These base classes offer
additional features that are specifically made for [CModel] and [CActiveRecord].
For example, the [CActiveRecordBehavior] class implements a set of methods to respond
to the life cycle events raised in an ActiveRecord object. A child class can thus
override these methods to put in customized code which will participate in the AR life cycles.

The following code shows an example of an ActiveRecord behavior. When this behavior is
attached to an AR object and when the AR object is being saved by calling `save()`, it will
automatically sets the `create_time` and `update_time` attributes with the current timestamp.

~~~
[php]
class TimestampBehavior extends CActiveRecordBehavior
{
	public function beforeSave($event)
	{
		if($this->owner->isNewRecord)
			$this->owner->create_time=time();
		else
			$this->owner->update_time=time();
	}
}
~~~


Widget
------

A [widget](/doc/guide/basics.view#widget) should extend from [CWidget] or its
child classes.

The easiest way of creating a new widget is extending an existing widget and
overriding its methods or changing its default property values. For example, if
you want to use a nicer CSS style for [CTabView], you could configure its
[CTabView::cssFile] property when using the widget. You can also extend [CTabView]
as follows so that you no longer need to configure the property when using the widget.

~~~
[php]
class MyTabView extends CTabView
{
	public function init()
	{
		if($this->cssFile===null)
		{
			$file=dirname(__FILE__).DIRECTORY_SEPARATOR.'tabview.css';
			$this->cssFile=Yii::app()->getAssetManager()->publish($file);
		}
		parent::init();
	}
}
~~~

In the above, we override the [CWidget::init] method and assign to
[CTabView::cssFile] the URL to our new default CSS style if the property
is not set. We put the new CSS style file under the same directory
containing the `MyTabView` class file so that they can be packaged as
an extension. Because the CSS style file is not Web accessible, we need
to publish as an asset.

To create a new widget from scratch, we mainly need to implement two methods:
[CWidget::init] and [CWidget::run]. The first method is called when we
use `$this->beginWidget` to insert a widget in a view, and the
second method is called when we call `$this->endWidget`.
If we want to capture and process the content displayed between these two
method invocations, we can start [output buffering](http://us3.php.net/manual/en/book.outcontrol.php)
in [CWidget::init] and retrieve the buffered output in [CWidget::run]
for further processing.

A widget often involves including CSS, JavaScript or other resource files
in the page that uses the widget. We call these files *assets* because
they stay together with the widget class file and are usually not accessible by
Web users. In order to make these files Web accessible, we need to publish
them using [CWebApplication::assetManager], as shown in the above code snippet.
Besides, if we want to include a CSS or JavaScript file in the current page,
we need to register it using [CClientScript]:

~~~
[php]
class MyWidget extends CWidget
{
	protected function registerClientScript()
	{
		// ...publish CSS or JavaScript file here...
		$cs=Yii::app()->clientScript;
		$cs->registerCssFile($cssFile);
		$cs->registerScriptFile($jsFile);
	}
}
~~~

A widget may also have its own view files. If so, create a directory named
`views` under the directory containing the widget class file, and put all the
view files there. In the widget class, in order to render a widget view, use
`$this->render('ViewName')`, which is similar to what we do in a controller.

Action
------

An [action](/doc/guide/basics.controller#action) should extend from [CAction]
or its child classes. The main method that needs to be implemented for an action
is [IAction::run].

Filter
------
A [filter](/doc/guide/basics.controller#filter) should extend from [CFilter]
or its child classes. The main methods that need to be implemented for a filter
are [CFilter::preFilter] and [CFilter::postFilter]. The former is invoked before
the action is executed while the latter after.

~~~
[php]
class MyFilter extends CFilter
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

The parameter `$filterChain` is of type [CFilterChain] which contains information
about the action that is currently filtered.


Controller
----------
A [controller](/doc/guide/basics.controller) distributed as an extension
should extend from [CExtController], instead of [CController]. The main reason
is because [CController] assumes the controller view files are located under
`application.views.ControllerID`, while [CExtController] assumes the view
files are located under the `views` directory which is a subdirectory of
the directory containing the controller class file. Therefore, it is easier
to redistribute the controller since its view files are staying together
with the controller class file.


Validator
---------
A validator should extend from [CValidator] and implement its
[CValidator::validateAttribute] method.

~~~
[php]
class MyValidator extends CValidator
{
	protected function validateAttribute($model,$attribute)
	{
		$value=$model->$attribute;
		if($value has error)
			$model->addError($attribute,$errorMessage);
	}
}
~~~

Console Command
---------------
A [console command](/doc/guide/topics.console) should extend from
[CConsoleCommand] and implement its [CConsoleCommand::run] method.
Optionally, we can override [CConsoleCommand::getHelp] to provide
some nice help information about the command.

~~~
[php]
class MyCommand extends CConsoleCommand
{
	public function run($args)
	{
		// $args gives an array of the command-line arguments for this command
	}

	public function getHelp()
	{
		return 'Usage: how to use this command';
	}
}
~~~

Module
------
Please refer to the section about [modules](/doc/guide/basics.module#creating-module) on how to create a module.

A general guideline for developing a module is that it should be self-contained. Resource files (such as CSS, JavaScript, images) that are used by a module should be distributed together with the module. And the module should publish them so that they can be Web-accessible.


Generic Component
-----------------
Developing a generic component extension is like writing a class. Again, the component
should also be self-contained so that it can be easily used by other developers.


<div class="revision">$Id$</div>