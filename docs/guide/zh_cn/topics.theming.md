Theming(主题)
=======

Theming是一个在Web应用程序里定制网页外观的系统方式。通过采用一个新的主题，网页应用程序的整体外观可以立即和戏剧性的改变。

在Yii，每个主题由一个目录代表，包含view文件，layout文件和相关的资源文件，如图片， CSS文件， JavaScript文件等。主题的名字就是他的目录名字。全部主题都放在在同一目录`WebRoot/themes`下 。在任何时候，只有一个主题可以被激活。

> 提示：默认的主题根目录`WebRoot/themes`可被配置成其他的。只需要配置[themeManager|CWebApplication::themeManager]应用部件的属性[basePath|CThemeManager::basePath]和[baseUrl|CThemeManager::baseUrl]为你所要的值。

要激活一个主题，设置Web应用程序的属性[theme|CWebApplication::theme]为你所要的名字。可以在[application configuration](/doc/guide/basics.application#application-configuration)中配置或者在执行过程中在控制器的动作里面修改。

> 注：主题名称是区分大小写的。如果您尝试启动一个不存在的主题， `Yii::app()->theme`将返回`null` 。

主题目录里面内容的组织方式和[application base path](/doc/guide/basics.application#application-base-directory)目录下的组织方式一样。例如，所有的view文件必须位于`views`下 ，布局view文件在`views/layouts`下 ，和系统view文件在`views/system`下。例如，如果我们要替换`PostController`的`create` view文件为`classic`主题下，我们将保存新的view文件为`WebRoot/themes/classic/views/post/create.php`。

对于在[module](/doc/guide/basics.module)里面的控制器view文件，相应主题view文件将被放在`views`目录下。例如，如果上述的`PostController`是在一个命名为`forum`的模块里 ，我们应该保存`create` view 文件为`WebRoot/themes/classic/views/forum/post/create.php` 。如果 `forum`模块嵌套在另一个名为`support`模块里 ，那么view文件应为`WebRoot/themes/classic/views/support/forum/post/create.php` 。

> 注：由于`views`目录可能包含安全敏感数据，应当配置以防止被网络用户访问。

当我们调用[render|CController::render]或[renderPartial|CController::renderPartial]显示视图，相应的view文件以及布局文件将在当前激活的主题里寻找。如果发现，这些文件将被render渲染。否则，就后退到[viewPath|CController::viewPath]和[layoutPath|CWebApplication::layoutPath] 所指定的预设位置寻找。

> 提示：在一个主题的视图，我们经常需要链接其他主题资源文件。例如，我们可能要显示一个在主题下`images`目录里的图像文件。使用当前激活主题的[baseUrl|CTheme::baseUrl]属性，我们就可以为此图像文件生成如下URL，

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

> Note: The skin feature has been available since version 1.1.0.

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

<div class="revision">$Id: topics.theming.txt 1774 2010-11-13 15:34:33Z HonestQiao $</div>