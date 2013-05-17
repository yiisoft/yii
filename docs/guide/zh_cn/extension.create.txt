Creating Extensions（创建扩展）
===================

由于扩展意味着是第三方开发者使用，需要一些额外的努力去创建它。以下是一些一般性的指导原则：

*扩展最好是自己自足。也就是说，其外部的依赖应是最少的。如果用户的扩展需要安装额外的软件包，类或资源档案，这将是一个头疼的问题。
*文件属于同一个扩展的，应组织在同一目录下，目录名用扩展名称。
*扩展里面的类应使用一些单词字母前缀，以避免与其他扩展命名冲突。
*扩展应该提供详细的安装和API文档。这将减少其他开发员使用扩展时花费的时间和精力。
*扩展应该用适当的许可。如果您想您的扩展能在开源和闭源项目中使用，你可以考虑使用许可证，如BSD的，麻省理工学院等，但不是GPL的，因为它要求其衍生的代码是开源的。

在下面，我们根据 [overview](/doc/guide/extension.overview)中所描述的分类，描述如何创建一个新的扩展。当您要创建一个主要用于在您自己项目的component部件，这些描述也适用。

Application Component（应用部件）
---------------------

一个[application component](/doc/guide/basics.application#application-component)
应实现接口[IApplicationComponent]或继承[CApplicationComponent]。主要需要实现的方法是
[IApplicationComponent::init]，部件在此执行一些初始化工作。此方法在部件创建和属性值（在[application configuration](/doc/guide/basics.application#application-configuration)里指定的 ）被赋值后调用。

默认情况下，一个应用程序部件创建和初始化，只有当它首次访问期间要求处理。如果一个应用程序部件需要在应用程序实例被创建后创建，它应要求用户在[CApplication::preload] 的属性中列出他的编号。


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


Widget（小工具）
------

[widget](/doc/guide/basics.view#widget)应继承[CWidget]或其子类。
A [widget](/doc/guide/basics.view#widget) should extend from [CWidget] or its
child classes.

最简单的方式建立一个新的小工具是继承一个现成的小工具和重载它的方法或改变其默认的属性值。例如，如果您想为[CTabView]使用更好的CSS样式，您可以配置其[CTabView::cssFile]属性，当使用的小工具时。您还可以继承[CTabView]如下，让您在使用小工具时，不再需要配置属性。

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
在上面的，我们重载[CWidget::init]方法和指定[CTabView::cssFile]的URL到我们的新的默认CSS样式如果此属性未设置时。我们把新的CSS样式文件和`MyTabView`类文件放在相同的目录下，以便他们能够封装成扩展。由于CSS样式文件不是通过Web访问，我们需要发布作为一项asset资源。

要从零开始创建一个新的小工具，我们主要是需要实现两个方法：[CWidget::init] 和[CWidget::run]。第一种方法是当我们在视图中使用 `$this->beginWidget` 插入一个小工具时被调用，第二种方法在`$this->endWidget`被调用时调用。如果我们想在这两个方法调用之间捕捉和处理显示的内容，我们可以开始[output buffering](http://us3.php.net/manual/en/book.outcontrol.php)在[CWidget::init] 和在[CWidget::run]中回收缓冲输出作进一步处理。
If we want to capture and process the content displayed between these two
method invocations, we can start [output buffering](http://us3.php.net/manual/en/book.outcontrol.php)
in [CWidget::init] and retrieve the buffered output in [CWidget::run]
for further processing.

在网页中使用的小工具，小工具往往包括CSS，Javascript或其他资源文件。我们叫这些文件*assets*，因为他们和小工具类在一起，而且通常Web用户无法访问。为了使这些档案通过Web访问，我们需要用[CWebApplication::assetManager]发布他们，例如上述代码段所示。此外，如果我们想包括CSS或JavaScript文件在当前的网页，我们需要使用[CClientScript]注册 ：

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

小工具也可能有自己的视图文件。如果是这样，创建一个目录命名`views`在包括小工具类文件的目录下，并把所有的视图文件放里面。在小工具类中使用`$this->render('ViewName')` 来render渲染小工具视图，类似于我们在控制器里做。

Action（动作）
------

[action](/doc/guide/basics.controller#action)应继承[CAction]或者其子类。action要实现的主要方法是[IAction::run] 。

Filter（过滤器）
------
[filter](/doc/guide/basics.controller#filter)应继承[CFilter] 或者其子类。filter要实现的主要方法是[CFilter::preFilter]和[CFilter::postFilter]。前者是在action之前被执行，而后者是在之后。

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

参数`$filterChain`的类型是[CFilterChain]，其包含当前被filter的action的相关信息。


Controller（控制器）
----------
[controller](/doc/guide/basics.controller)要作为扩展需继承[CExtController]，而不是 [CController]。主要的原因是因为[CController] 认定控制器视图文件位于`application.views.ControllerID` 下，而[CExtController]认定视图文件在`views`目录下，也是包含控制器类目录的一个子目录。因此，很容易重新分配控制器，因为它的视图文件和控制类是在一起的。

Validator（验证）
---------
Validator需继承[CValidator]和实现[CValidator::validateAttribute]方法。

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

Console Command（控制台命令）
---------------
[console command](/doc/guide/topics.console) 应继承[CConsoleCommand]和实现[CConsoleCommand::run]方法。 或者，我们可以重载[CConsoleCommand::getHelp]来提供
一些更好的有关帮助命令。

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

Module（模块）
------
请参阅[modules](/doc/guide/basics.module#creating-module)一节中关于就如何创建一个模块。 

一般准则制订一个模块，它应该是独立的。模块所使用的资源文件（如CSS ， JavaScript ，图片），应该和模块一起分发。还有模块应发布它们，以便可以Web访问它们 。


Generic Component（通用组件）
-----------------
开发一个通用组件扩展类似写一个类。还有，该组件还应该自足，以便它可以很容易地被其他开发者使用。


<div class="revision">$Id: extension.create.txt 1774 2010-11-13 15:34:33Z HonestQiao $</div>