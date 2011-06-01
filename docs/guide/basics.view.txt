View
====

A view is a PHP script consisting mainly of user interface elements. It
can contain PHP statements, but it is recommended that these statements
should not alter data models and should remain relatively simple. In the
spirit of separating of logic and presentation, large chunks of logic should
be placed in controllers or models rather than in views.

A view has a name which is used to identify the view script file when
rendering. The name of a view is the same as the name of its view script.
For example, the view name `edit` refers to a view script named `edit.php`. 
To render a view, call [CController::render()] with the name of
the view. The method will look for the corresponding view file under the
directory `protected/views/ControllerID`.

Inside the view script, we can access the controller instance using
`$this`. We can thus `pull` in any property of the controller by
evaluating `$this->propertyName` in the view.

We can also use the following `push` approach to pass data to the view:

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

In the above, the [render()|CController::render] method will extract the second array
parameter into variables. As a result, in the view script we can access the
local variables `$var1` and `$var2`.

Layout
------

Layout is a special view that is used to decorate views. It usually
contains parts of a user interface that are common among several views.
For example, a layout may contain a header and a footer, and embed
the view in between, like this:

~~~
[php]
......header here......
<?php echo $content; ?>
......footer here......
~~~

where `$content` stores the rendering result of the view.

Layout is implicitly applied when calling [render()|CController::render].
By default, the view script `protected/views/layouts/main.php` is used as
the layout. This can be customized by changing either [CWebApplication::layout]
or [CController::layout]. To render a view without applying any layout,
call [renderPartial()|CController::renderPartial] instead.

Widget
------

A widget is an instance of [CWidget] or a child class of [CWidget]. It is a 
component that is mainly for presentational purposes. A widget is usually 
embedded in a view script to generate a complex, yet self-contained user 
interface. For example, a calendar widget can be used to render 
a complex calendar user interface. Widgets facilitate better reusability in 
user interface code.

To use a widget, do as follows in a view script:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...body content that may be captured by the widget...
<?php $this->endWidget(); ?>
~~~

or

~~~
[php]
<?php $this->widget('path.to.WidgetClass'); ?>
~~~

The latter is used when the widget does not need any body content.

Widgets can be configured to customize their behavior. This is done by
setting their initial property values when calling
[CBaseController::beginWidget] or [CBaseController::widget]. For example,
when using a [CMaskedTextField] widget, we might like to specify the mask
being used. We can do so by passing an array of initial property 
values as follows, where the array keys are property names and array values
are the initial values of the corresponding widget properties:

~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

To define a new widget, extend [CWidget] and override its
[init()|CWidget::init] and [run()|CWidget::run] methods:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// this method is called by CController::beginWidget()
	}

	public function run()
	{
		// this method is called by CController::endWidget()
	}
}
~~~

Like a controller, a widget can also have its own view. By default, widget
view files are located under the `views` subdirectory of the directory
containing the widget class file. These views can be rendered by calling
[CWidget::render()], similar to that in controller. The only difference is
that no layout will be applied to a widget view. Also, `$this` in the view refers
to the widget instance instead of the controller instance.

System View
-----------

System views refer to the views used by Yii to display error and logging
information. For example, when a user requests for a non-existing controller
or action, Yii will throw an exception explaining the error. Yii displays the
exception using a specific system view.

The naming of system views follows some rules. Names like `errorXXX` refer
to views for displaying [CHttpException] with error code `XXX`. For
example, if [CHttpException] is raised with error code 404, the `error404`
view will be displayed.

Yii provides a set of default system views located under
`framework/views`. They can be customized by creating the same-named view
files under `protected/views/system`.

<div class="revision">$Id$</div>
