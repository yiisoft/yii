视图
====

视图是一个包含了主要的用户交互元素的PHP脚本.他可以包含PHP语句,但是我们建议这些语句不要去改变数据模型,且最好能够保持其单纯性(单纯作为视图)。为了实现逻辑和界面分离,大段的逻辑应该被放置于控制器或模型中,而不是视图中。

视图有一个名字，当渲染(render)时，名字会被用于识别视图脚本文件。视图的名称与其视图脚本名称是一样的.例如:视图  `edit`  的名称出自一个名为 `edit.php` 的脚本文件.要渲染时如，需通过传递视图的名称调用  [CController::render()]。这个方法将在 `protected/views/ControllerID` 目录下寻找对应的视图文件.

在视图脚本内部,我们可以通过 `$this` 来访问控制器实例.我们可以在视图里以 `$this->propertyName` 的方式 `拉取` 控制器的任何属性.

我们也可以用以下 `推送` 的方式传递数据到视图里:

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

在以上的方式中, [render()|CController::render] 方法将提取数组的第二个参数到变量里.其产生的结果是,在视图脚本里,我们可以直接访问变量 `$var1` 和 `$var2`.

布局
------

布局是一种用来修饰视图的特殊的视图文件.它通常包含了用户界面中通用的一部分视图.例如:布局可以包含header和footer的部分,然后把内容嵌入其间.

~~~
[php]
......header here......
<?php echo $content; ?>
......footer here......
~~~

其中的 `$content` 则储存了内容视图的渲染结果.

当使用 [render()|CController::render] 时,布局被隐式应用.视图脚本 `protected/views/layouts/main.php` 是默认的布局文件.这可以通过改变 [CWebApplication::layout] 或者  [CWebApplication::layout] 进行自定义。要渲染一个不带布局的视图，则需调用 [renderPartial()|CController::renderPartial] 。

小物件
------

小物件是 [CWidget] 或其子类的实例.它是一个主要用于表现数据的组件.小物件通常内嵌于一个视图来产生一些复杂而独立的用户界面.例如,一个日历小物件可用于渲染一个复杂的日历界面.小物件使用户界面更加可复用.

我们可以按如下视图脚本来使用一个小物件:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...可能会由小物件获取的内容主体...
<?php $this->endWidget(); ?>
~~~

或者

~~~
[php]
<?php $this->widget('path.to.WidgetClass'); ?>
~~~

后者用于不需要任何 body 内容的组件.

小物件可通过配置来定制它的表现.这是通过调用 [CBaseController::beginWidget] 或 [CBaseController::widget] 设置其初始化属性值来完成的.例如,当使用 [CMaskedTextField] 小物件时,我们想指定被使用的 mask （可理解为一种输出格式，译者注）.我们通过传递一个携带这些属性初始化值的数组来实现.这里的数组的键是属性的名称,而数组的值则是小物件属性所对应的值.正如以下所示 :
~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

继承 [CWidget] 并覆盖其[init()|CWidget::init] 和 [run()|CWidget::run] 方法,可以定义一个新的小物件:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// 此方法会被 CController::beginWidget() 调用
	}

	public function run()
	{
		// 此方法会被 CController::endWidget() 调用
	}
}
~~~

小物件可以像一个控制器一样拥有它自己的视图.默认情况下,小物件的视图文件位于包含了小物件类文件目录的 `views` 子目录之下.这些视图可以通过调用 [CWidget::render()] 渲染,这一点和控制器很相似.唯一不同的是,小物件的视图没有布局文件支持。另外，小物件视图中的`$this`指向小物件实例而不是控制器实例。

系统视图
-----------

系统视图的渲染通常用于展示 Yii 的错误和日志信息.例如,当用户请求来自一个不存在的控制器或动作时,Yii 会抛出一个异常来解释这个错误. 这时,Yii 就会使用一个特殊的系统视图来显示此错误.

系统视图的命名遵从了一些规则.比如像 `errorXXX` 这样的名称就是用于渲染展示错误号 `XXX` 的 [CHttpException] 的视图.例如,如果 [CHttpException] 抛出一个 404错误,那么 `error404` 就会被显示.

在 `framework/views` 下, Yii 提供了一系列默认的系统视图. 他们可以通过在 `protected/views/system` 下创建同名视图文件进行自定义.

<div class="revision">$Id: basics.view.txt 2367 2010-08-29 17:29:22Z qiang.xue $</div>