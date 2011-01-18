Theming
=======

Theming is a systematic way of customizing the outlook of pages in a Web
application. By applying a new theme, the overall appearance of a Web
application can be changed instantly and dramatically.

In Yii, each theme is represented as a directory consisting of view files,
layout files, and relevant resource files such as images, CSS files,
JavaScript files, etc.  The name of a theme is its directory name. All
themes reside under the same directory `WebRoot/themes`. At any time, only
one theme can be active.

> Tip: The default theme root directory `WebRoot/themes` can be configured
to be a different one. Simply configure the
[basePath|CThemeManager::basePath] and the [baseUrl|CThemeManager::baseUrl]
properties of the [themeManager|CWebApplication::themeManager] application
component to be the desired ones.


Using a Theme
-------------

To activate a theme, set the [theme|CWebApplication::theme] property of
the Web application to be the name of the desired theme. This can be done
either in the [application
configuration](/doc/guide/basics.application#application-configuration) or during runtime in
controller actions.

> Note: Theme name is case-sensitive. If you attempt to activate a theme
that does not exist, `Yii::app()->theme` will return `null`.


Creating a Theme
----------------

Contents under a theme directory should be organized in the same way as
those under the [application base
path](/doc/guide/basics.application#application-base-directory). For example, all view files
must be located under `views`, layout view files under `views/layouts`, and
system view files under `views/system`. For example, if we want to replace
the `create` view of `PostController` with a view in the `classic` theme,
we should save the new view file as `WebRoot/themes/classic/views/post/create.php`.

For views belonging to controllers in a [module](/doc/guide/basics.module),
the corresponding themed view files should also be placed under the `views`
directory. For example, if the aforementioned `PostController` is in a module
named `forum`, we should save the `create` view file as `WebRoot/themes/classic/views/forum/post/create.php`. If the `forum` module
is nested in another module named `support`, then the view file should be
`WebRoot/themes/classic/views/support/forum/post/create.php`.

> Note: Because the `views` directory may contain security-sensitive data, it should be configured to prevent from being accessed by Web users.

When we call [render|CController::render] or
[renderPartial|CController::renderPartial] to display a view, the
corresponding view file as well as the layout file will be looked for in
the currently active theme. And if found, those files will be rendered.
Otherwise, it falls back to the default location as specified by
[viewPath|CController::viewPath] and
[layoutPath|CWebApplication::layoutPath].

> Tip: Inside a theme view, we often need to link other theme resource
> files. For example, we may want to show an image file under the theme's
> `images` directory. Using the [baseUrl|CTheme::baseUrl] property of the
> currently active theme, we can generate the URL for the image as follows,
>
> ~~~
> [php]
> Yii::app()->theme->baseUrl . '/images/FileName.gif'
> ~~~
>

Below is an example of directory organization for an application with two themes `basic` and `fancy`.

~~~
WebRoot/
	assets
	protected/
		.htaccess
		components/
		controllers/
		models/
		views/
			layouts/
				main.php
			site/
				index.php
	themes/
		basic/
			views/
				.htaccess
				layouts/
					main.php
				site/
					index.php
		fancy/
			views/
				.htaccess
				layouts/
					main.php
				site/
					index.php
~~~

In the application configuration, if we configure

~~~
[php]
return array(
	'theme'=>'basic',
	......
);
~~~

then the `basic` theme will be in effect, which means the application's layout will use
the one under the directory `themes/basic/views/layouts`, and the site's index view will
use the one under `themes/basic/views/site`. In case a view file is not found in the theme,
it will fall back to the one under the `protected/views` directory.


Theming Widgets
---------------

Starting from version 1.1.5, views used by a widget can also be themed. In particular, when we call [CWidget::render()] to render a widget view, Yii will attempt to search under the theme folder as well as the widget view folder for the desired view file.

To theme the view `xyz` for a widget whose class name is `Foo`, we should first create a folder named `Foo` (same as the widget class name) under the currently active theme's view folder. If the widget class is namespaced (available in PHP 5.3.0 or above), such as `\app\widgets\Foo`, we should create a folder named `app_widgets_Foo`. That is, we replace the namespace separators with the underscore characters.

We then create a view file named `xyz.php` under the newly created folder. To this end, we should have a file `themes/basic/views/Foo/xyz.php`, which will be used by the widget to replace its original view, if the currently active theme is `basic`.


Customizing Widgets Globally
----------------------------

> Note: this feature has been available since version 1.1.3.

When using a widget provided by third party or Yii, we often need to customize
it for specific needs. For example, we may want to change the value of
[CLinkPager::maxButtonCount] from 10 (default) to 5. We can accomplish this
by passing the initial property values when calling [CBaseController::widget]
to create a widget. However, it becomes troublesome to do so if we have to
repeat the same customization in every place we use [CLinkPager].

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
    'maxButtonCount'=>5,
    'cssFile'=>false,
));
~~~

Using the global widget customization feature, we only need to specify these
initial values in a single place, i.e., the application configuration. This
makes the customization of widgets more manageable.

To use the global widget customization feature, we need to configure the
[widgetFactory|CWebApplication::widgetFactory] as follows:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'widgets'=>array(
                'CLinkPager'=>array(
                    'maxButtonCount'=>5,
                    'cssFile'=>false,
                ),
                'CJuiDatePicker'=>array(
                    'language'=>'ru',
                ),
            ),
        ),
    ),
);
~~~

In the above, we specify the global widget customization for both [CLinkPager]
and [CJuiDatePicker] widgets by configuring the [CWidgetFactory::widgets]
property. Note that the global customization for each widget is represented
as a key-value pair in the array, where the key refers to the wiget class
name while the value specifies the initial property value array.

Now, whenever we create a [CLinkPager] widget in a view, the above property
values will be assigned to the widget, and we only need to write the following
code in the view to create the widget:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
));
~~~

We can still override the initial property values when necessary. For example,
if in some view we want to set `maxButtonCount` to be 2, we can do the following:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
	'maxButtonCount'=>2,
));
~~~


Skin
----

While using a theme we can quickly change the outlook of views, we can use skins to systematically customize the outlook of the [widgets](/doc/guide/basics.view#widget) used in the views.

A skin is an array of name-value pairs that can be used to initialize the properties of a widget. A skin belongs to a widget class, and a widget class can have multiple skins identified by their names. For example, we can have a skin for the [CLinkPager] widget and the skin is named as `classic`.

In order to use the skin feature, we first need to modify the application configuration by configuring the [CWidgetFactory::enableSkin] property to be true for the `widgetFactory` application component:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'enableSkin'=>true,
        ),
    ),
);
~~~

Please note that in versions prior to 1.1.3, you need to use the following configuration to enable widget skinning:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'class'=>'CWidgetFactory',
        ),
    ),
);
~~~

We then create the needed skins. Skins belonging to the same widget class are stored in a single PHP script file whose name is the widget class name. All these skin files are stored under `protected/views/skins`, by default. If you want to change this to be a different directory, you may configure the `skinPath` property of the `widgetFactory` component. As an example, we may create under `protected/views/skins` a file named `CLinkPager.php` whose content is as follows,

~~~
[php]
<?php
return array(
    'default'=>array(
        'nextPageLabel'=>'&gt;&gt;',
        'prevPageLabel'=>'&lt;&lt;',
    ),
    'classic'=>array(
        'header'=>'',
        'maxButtonCount'=>5,
    ),
);
~~~

In the above, we create two skins for the [CLinkPager] widget: `default` and `classic`. The former is the skin that will be applied to any [CLinkPager] widget that we do not explicitly specify its `skin` property, while the latter is the skin to be applied to a [CLinkPager] widget whose `skin` property is specified as `classic`. For example, in the following view code, the first pager will use the `default` skin while the second the `classic` skin:

~~~
[php]
<?php $this->widget('CLinkPager'); ?>

<?php $this->widget('CLinkPager', array('skin'=>'classic')); ?>
~~~

If we create a widget with a set of initial property values, they will take precedence and be merged with any applicable skin. For example, the following view code will create a pager whose initial values will be `array('header'=>'', 'maxButtonCount'=>6, 'cssFile'=>false)`, which is the result of merging the initial property values specified in the view and the `classic` skin.

~~~
[php]
<?php $this->widget('CLinkPager', array(
    'skin'=>'classic',
    'maxButtonCount'=>6,
    'cssFile'=>false,
)); ?>
~~~

Note that the skin feature does NOT require using themes. However, when a theme is active, Yii will also look for skins under the `skins` directory of the theme's view directory (e.g. `WebRoot/themes/classic/views/skins`). In case a skin with the same name exists in both the theme and the main application view directories, the theme skin will take precedence.

If a widget is using a skin that does not exist, Yii will still create the widget as usual without any error.

> Info: Using skin may degrade the performance because Yii needs to look for the skin file the first time a widget is being created.

Skin is very similar to the global widget customization feature. The main
differences are as follows.

   - Skin is more related with the customization of presentational property values;
   - A widget can have multiple skins;
   - Skin is themeable;
   - Using skin is more expensive than using global widget customization.

<div class="revision">$Id$</div>