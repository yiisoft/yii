使用扩展
================

适用扩展通常半酣了以下三步:

  1. 从 Yii 的 [扩展库](http://www.yiiframework.com/extensions/) 下载扩展.
  2. 解压到 [应用程序的基目录](/doc/guide/basics.application#application-base-directory)
的子目录 `extensions/xyz` 下,这里的 `xyz` 是扩展的名称.
  3. 导入, 配置和使用扩展.

每个扩展都有一个所有扩展中唯一的名称标识.把一个扩展命名为 `xyz` ,我们也可以使用路径别名定位到包含了 `xyz`
所有文件的基目录.

不同的扩展有着不同的导入,配置,使用要求.以下是我们通常会用到扩展的场景,按照他们在
 [概述](/doc/guide/extension.overview) 中的描述分类.


Zii Extensions
--------------

Before we start describing the usage of third-party extensions, we would like to introduce
the Zii extension library, which is a set of extensions developed by the Yii developer team
and included in every release since Yii version 1.1.0. The Zii library is hosted as a
Google project called [zii](http://code.google.com/p/zii/).

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


应用的部件
---------------------

使用 [应用的部件](/doc/guide/basics.application#application-component),
首先我们需要添加一个新条目到 [应用配置](/doc/guide/basics.application#application-configuration)
 的 `components` 属性, 如下所示:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'application.extensions.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // 其他部件配置
    ),
);
~~~

然后,我们可以在任何地方通过使用 `Yii::app()->xyz` 来访问部件.部件将会被 `惰性创建`(就是,仅当它第一次被访问时创建.) ,
除非我们把它配置到  `preload` 属性里.


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



组件
------

[组件](/doc/guide/basics.view#widget) 主要用在 [视图](/doc/guide/basics.view) 里.假设组件类 `XyzClass` 属于 `xyz` 扩展,我们可以如下在视图中使用它:

~~~
[php]
// 组件不需要主体内容
<?php $this->widget('application.extensions.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// 组件可以包含主体内容
<?php $this->beginWidget('application.extensions.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...组件的主体内容...

<?php $this->endWidget(); ?>
~~~

动作
------

[动作](/doc/guide/basics.controller#action) 被 [控制器](/doc/guide/basics.controller) 用于响应指定的用户请求.假设动作的类 `XyzClass` 属于 `xyz` 扩展,我们可以在我们的控制器类里重写 [CController::actions] 方法来使用它:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// 其他动作
		);
	}
}
~~~

然后,我们可以通过 [路由](/doc/guide/basics.controller#route) `test/xyz` 来访问.

过滤器
------
[过滤器](/doc/guide/basics.controller#filter) 也被 [控制器](/doc/guide/basics.controller) 使用.过滤器主要用于当其被 [动作](/doc/guide/basics.controller#action) 挂起时预处理,提交处理用户请求.假设过滤器的类 `XyzClass` 属于 `xyz` 扩展,我们可以在我们的控制器类里重写 [CController::filters] 方法来使用它:

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// 其他过滤器
		);
	}
}
~~~



在上述代码中,我们可以在数组的第一个元素离使用加号或者减号操作符来限定过滤器只在那些动作中生效.更多信息,请参照文档的 [CController].


控制器
----------
[控制器](/doc/guide/basics.controller) 提供了一套可以被用户请求的动作.我们需要在 [应用配置](/doc/guide/basics.application#application-configuration) 里设置 [CWebApplication::controllerMap] 属性,才能在控制器里使用扩展:

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'application.extensions.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// 其他控制器
	),
);
~~~

然后, 一个在控制里的 `a` 行为就可以通过 [路由](/doc/guide/basics.controller#route) `xyz/a` 来访问了.

校验器
---------
校验器主要用在 [模型](/doc/guide/basics.model)类(继承自 [CFormModel] 或者 [CActiveRecord])中.假设校验器类 `XyzClass` 属于 `xyz` 扩展,我们可以在我们的模型类中通过 [CModel::rules] 重写 [CModel::rules] 来使用它:

~~~
[php]
class MyModel extends CActiveRecord // or CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// 其他校验规则
		);
	}
}
~~~

控制台命令
---------------
[控制台命令](/doc/guide/topics.console)扩展通常使用一个额外的命令来增强 `yiic` 的功能.假设命令控制台 `XyzClass` 属于 `xyz` 扩展,我们可以通过设定控制台应用的配置来使用它:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'application.extensions.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// 其他命令
	),
);
~~~

然后,我们就能使用配备了额外命令 `xyz` 的 `yiic` 工具了.

> Note|注意: 控制台应用通常使用了一个不同于 Web 应用的配置文件.如果使用了 `yiic webapp` 命令创建了一个应用,这样的话,控制台应用的 `protected/yiic` 的配置文件就是 `protected/config/console.php`  了,而Web应用的配置文件
则是 `protected/config/main.php`.

模块
------
模块通常由多个类文件组成,且往往综合上述扩展类型.因此,你应该按照和以下一致的指令来使用模块.

通用部件
-----------------
使用一个通用 [部件](/doc/guide/basics.component), 我们首先需要通过使用

~~~
Yii::import('application.extensions.xyz.XyzClass');
~~~

来包含它的类文件.然后,我们既可以创建一个类的实例,配置它的属性,也可以调用它的方法.我们还可以创建一个新的子类来扩展它.ss

<div class="revision">$Id: extension.use.txt 1774 2010-11-13 15:34:33Z HonestQiao $</div>