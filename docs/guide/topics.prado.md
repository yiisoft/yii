Using Alternative Template Syntax
=================================

Yii allows developers to use their own favorite template syntax (e.g.
Prado, Smarty) to write controller or widget views. This is achieved by
writing and installing a [viewRenderer|CWebApplication::viewRenderer]
application component. The view renderer intercepts the invocations of
[CBaseController::renderFile], compiles the view file with customized
template syntax, and renders the compiling results.

> Info: It is recommended to use customized template syntax only when
writing views that are less likely to be reused. Otherwise, people who are
reusing the views would be forced to use the same customized template
syntax in their applications.

In the following, we introduce how to use [CPradoViewRenderer], a view
renderer that allows developers to use the template syntax similar to that
in [Prado framework](http://www.pradosoft.com/). For people who want to
develop their own view renderers, [CPradoViewRenderer] is a good reference.

Using `CPradoViewRenderer`
--------------------------

To use [CPradoViewRenderer], we just need to configure the application as
follows:

~~~
[php]
return array(
	'components'=>array(
		......,
		'viewRenderer'=>array(
			'class'=>'CPradoViewRenderer',
		),
	),
);
~~~

By default, [CPradoViewRenderer] will compile source view files and save
the resulting PHP files under the
[runtime](/doc/guide/basics.convention#directory) directory. Only when the
source view files are changed, will the PHP files be re-generated.
Therefore, using [CPradoViewRenderer] incurs very little performance
degradation.

> Tip: While [CPradoViewRenderer] mainly introduces some new template tags
to make writing views easier and faster, you can still write PHP code as
usual in the source views.

In the following, we introduce the template tags that are supported by
[CPradoViewRenderer].

### Short PHP Tags

Short PHP tags are shortcuts to writing PHP expressions and statements in
a view. The expression tag `<%= expression %>` is translated into
`<?php echo expression ?>`; while the statement tag `<% statement
%>` to `<?php statement ?>`. For example,

~~~
[php]
<%= CHtml::textField($name,'value'); %>
<% foreach($models as $model): %>
~~~

is translated into

~~~
[php]
<?php echo CHtml::textField($name,'value'); ?>
<?php foreach($models as $model): ?>
~~~

### Component Tags

Component tags are used to insert a
[widget](/doc/guide/basics.view#widget) in a view. It uses the following
syntax:

~~~
[php]
<com:WidgetClass property1=value1 property2=value2 ...>
	// body content for the widget
</com:WidgetClass>

// a widget without body content
<com:WidgetClass property1=value1 property2=value2 .../>
~~~

where `WidgetClass` specifies the widget class name or class [path
alias](/doc/guide/basics.namespace), and property initial values can be
either quoted strings or PHP expressions enclosed within a pair of curly
brackets. For example,

~~~
[php]
<com:CCaptcha captchaAction="captcha" showRefreshButton={false} />
~~~

would be translated as

~~~
[php]
<?php $this->widget('CCaptcha', array(
	'captchaAction'=>'captcha',
	'showRefreshButton'=>false)); ?>
~~~

> Note: The value for `showRefreshButton` is specified as `{false}`
instead of `"false"` because the latter means a string instead of a
boolean.

### Cache Tags

Cache tags are shortcuts to using [fragment
caching](/doc/guide/caching.fragment). Its syntax is as follows,

~~~
[php]
<cache:fragmentID property1=value1 property2=value2 ...>
	// content being cached
</cache:fragmentID >
~~~

where `fragmentID` should be an identifier that uniquely identifies the
content being cached, and the property-value pairs are used to configure
the fragment cache. For example,

~~~
[php]
<cache:profile duration={3600}>
	// user profile information here
</cache:profile >
~~~

would be translated as

~~~
[php]
<?php if($this->beginCache('profile', array('duration'=>3600))): ?>
	// user profile information here
<?php $this->endCache(); endif; ?>
~~~

### Clip Tags

Like cache tags, clip tags are shortcuts to calling
[CBaseController::beginClip] and [CBaseController::endClip] in a view. The
syntax is as follows,

~~~
[php]
<clip:clipID>
	// content for this clip
</clip:clipID >
~~~

where `clipID` is an identifier that uniquely identifies the clip content.
The clip tags will be translated as

~~~
[php]
<?php $this->beginClip('clipID'); ?>
	// content for this clip
<?php $this->endClip(); ?>
~~~

### Comment Tags

Comment tags are used to write view comments that should only be visible
to developers. Comment tags will be stripped off when the view is displayed
to end users. The syntax for comment tags is as follows,

~~~
[php]
<!---
view comments that will be stripped off
--->
~~~

Mixing Template Formats
-----------------------

Starting from version 1.1.2, it is possible to mix the usage of some alternative
template syntax with the normal PHP syntax. To do so, the [CViewRenderer::fileExtension]
property of the installed view renderer must be configured with a value other than
`.php`. For example, if the property is set as `.tpl`, then any view file ending with `.tpl`
will be rendered using the installed view renderer, while all other view files ending
with `.php` will be treated as normal PHP view script.


<div class="revision">$Id$</div>