自动代码生成
=========================

自版本 1.1.2 起， Yii 装备了基于 Web 界面的代码生成工具 *Gii*。 它取代了之前的命令行端的代码生成工具 `yiic shell` 。 在这部分，我们将讲解如何使用 Gii 以及如何扩展 Gii 以增加我们的开发成果。

使用 Gii
---------

Gii 是以模块的方式实现的，它必须在一个已存在的 Yii 应用程序中使用。要使用 Gii，我们首先更改应用程序的配置如下：

~~~
[php]
return array(
	......
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'在这里填写密码',
			// 'ipFilters'=>array(...IP 列表...),
			// 'newFileMode'=>0666,
			// 'newDirMode'=>0777,
		),
	),
);
~~~

在上面，我们声明了一个名为 `gii` 的模块，它的类是 [GiiModule]。我们也为这个模块设置了一个密码，我们访问 Gii 时会有一个输入框要求填写这个密码。

出于安全考虑，默认情况下只允许本机访问 Gii。若允许其他可信赖的机器访问它，我们需要如上所示配置 [GiiModule::ipFilters] 属性。

因为 Gii 会生成并保存新文件到应用程序中，我们需要确保 Web 服务器进程有权限这样做。上面的 [GiiModule::newFileMode] 和 [GiiModule::newDirMode] 属性控制如何生成新文件和新目录。

> Note|注意: Gii 主要用作一个开发工具。因此，应当只在开发机器上安装它。因为它可以在应用程序中生成新的 PHP 文件，我们应当对安全问题足够重视(例如设置密码，IP 过滤)。

现在可以通过 URL `http://hostname/path/to/index.php?r=gii` 访问 Gii 了。这里我们假设 `http://hostname/path/to/index.php` 是访问 Yii 应用程序的 URL。

若 Yii 应用程序使用 `path` 格式的 URL (查看 [URL management](/doc/guide/topics.url))，我们可以通过 URL `http://hostname/path/to/index.php/gii` 访问 Gii。 我们可能需要增加如下 URL 规则到已有的 URL 规则的前面:

~~~
[php]
'components'=>array(
	......
	'urlManager'=>array(
		'urlFormat'=>'path',
		'rules'=>array(
			'gii'=>'gii',
			'gii/<controller:\w+>'=>'gii/<controller>',
			'gii/<controller:\w+>/<action:\w+>'=>'gii/<controller>/<action>',
			...已有的规则...
		),
	),
)
~~~

Gii 有一些默认的代码生成器。每个代码生成器负责生成特定类型的代码。例如 controller 生成器生成一个 controller 类以及一些 action view 脚本； model 生成器为指定的数据表生成一个 ActiveRecord 类。

使用一个生成器的基本流程如下：

1. 进入生成器页面；
2. 填写指定代码生成参数的输入框。例如，使用 Module Generator 创建一个新模块，你需要指定 module ID；
3. 点击 `Preview` 按钮预览即将生成的代码。你将看到一个表格中列出了将要生成的文件列表。你可以点击其中任何一个文件来预览代码；
4. 点击 `Generate` 按钮生成这些代码文件；
5. 查看代码生成日志。


扩展 Gii
-------------

虽然默认的 Gii 代码生成器可以生成非常强大的代码,然而我们经常想定制它们或者创建一个新的来适应我们的口味和需求。例如，我们想让生成的代码是我们喜欢的风格，或者想让代码支持多种语言。所有这些在 Gii 中都可非常容易地实现。

可以 2 种方式扩展 Gii：定制已存在的代码生成器的代码模板，以及编写新的代码生成器。

###代码生成器的架构

一个代码生成器存储在一个目录中，这个目录的名字被认为是生成器的名字。目录通常由如下内容组成：

~~~
model/                       the model generator root folder
   ModelCode.php             the code model used to generate code
   ModelGenerator.php        the code generation controller
   views/                    containing view scripts for the generator
      index.php              the default view script
   templates/                containing code template sets
      default/               the 'default' code template set
         model.php           the code template for generating model class code
~~~

###生成器搜索路径

Gii 在[GiiModule::generatorPaths] 属性指定的目录中查找可用的生成器。 当需要定制时，我们可以在应用程序的配置文件中做如下配置，

~~~
[php]
return array(
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'generatorPaths'=>array(
				'application.gii',   // a path alias
			),
		),
	),
);
~~~

上面的配置告诉 Gii 在别名是 `application.gii` 的目录中寻找生成器，以及默认的位置 `system.gii.generators`。

在不同的搜索路径有同名的生成器也是可以的。这种情况下，在 [GiiModule::generatorPaths] 指定目录中先出现的生成器有优先权。


###定制代码模板

这是扩展 Gii 最容易最常用的方式。我们使用一个例子来介绍如何定制代码模板。假设我们想要定制由 model 生成器生成的代码。

我们首先创建一个名为 `protected/gii/model/templates/compact` 的目录。这里的 `model` 意味着我们将要 *override* 默认的 model 生成器。 `templates/compact` 意味着我们将增加一个新的代码模板集名为 `compact`。

然后我们在应用程序配置里把 `application.gii` 增加到 [GiiModule::generatorPaths] 。如上所示。

现在打开 model 代码生成器页面。点击 `Code Template` 输入框。我们应当看到一个下拉列表，这个列表包含了我们新建的模板目录 `compact`。可是，若我们选择此模板生成代码，我们将看到错误。这是因为我们还没有在新的 `compact` 模板集中放入任何实际的代码模板文件。

复制文件 `framework/gii/generators/model/templates/default/model.php` 到 `protected/gii/model/templates/compact`。若我们再次尝试以 `compact` 模板生成，我们会成功。但是，生成的代码和以 `default` 模板集生成的代码没什么不同。

现在是时候做点真正的工作了。打开文件 `protected/gii/model/templates/compact/model.php` 以编辑它。记得这个文件将作为类似一个视图文件被使用，意味着它可以包含 PHP 表达式和语句。让我们更改模板以便生成的代码里  `attributeLabels()` 方法使用 `Yii::t()` 来翻译属性标签：

~~~
[php]
public function attributeLabels()
{
	return array(
<?php foreach($labels as $name=>$label): ?>
			<?php echo "'$name' => Yii::t('application', '$label'),\n"; ?>
<?php endforeach; ?>
	);
}
~~~

在每个代码模板中，我们可以访问一些预定义的变量，例如上面例子中的 `$labels`。这些变量由对应的代码生成器提供。不同的代码生成器可能在他们的代码模板中提供不同的变量。请认真阅读默认代码模板中的描述。


###创建新的生成器

In this sub-section, we show how to create a new generator that can generate a new widget class.

We first create a directory named `protected/gii/widget`. Under this directory, we will create the following files:

* `WidgetGenerator.php`: contains the `WidgetGenerator` controller class. This is the entry point of the widget generator.
* `WidgetCode.php`: contains the `WidgetCode` model class. This class has the main logic for code generation.
* `views/index.php`: the view script showing the code generator input form.
* `templates/default/widget.php`: the default code template for generating a widget class file.


#### Creating `WidgetGenerator.php`

The `WidgetGenerator.php` file is extremely simple. It only contains the following code:

~~~
[php]
class WidgetGenerator extends CCodeGenerator
{
	public $codeModel='application.gii.widget.WidgetCode';
}
~~~

In the above code, we specify that the generator will use the model class whose path alias is `application.gii.widget.WidgetCode`. The `WidgetGenerator` class extends from [CCodeGenerator] which implements a lot of functionalities, including the controller actions needed to coordinate the code generation process.

#### Creating `WidgetCode.php`

The `WidgetCode.php` file contains the `WidgetCode` model class that has the main logic for generating a widget class based on the user input. In this example, we assume that the only input we want from the user is the widget class name. Our `WidgetCode` looks like the following:

~~~
[php]
class WidgetCode extends CCodeModel
{
	public $className;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('className', 'required'),
			array('className', 'match', 'pattern'=>'/^\w+$/'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'className'=>'Widget Class Name',
		));
	}

	public function prepare()
	{
		$path=Yii::getPathOfAlias('application.components.' . $this->className) . '.php';
		$code=$this->render($this->templatepath.'/widget.php');

		$this->files[]=new CCodeFile($path, $code);
	}
}
~~~

The `WidgetCode` class extends from [CCodeModel]. Like a normal model class, in this class we can declare `rules()` and `attributeLabels()` to validate user inputs and provide attribute labels, respectively. Note that because the base class [CCodeModel] already defines some rules and attribute labels, we should merge them with our new rules and labels here.

The `prepare()` method prepares the code to be generated. Its main task is to prepare a list of [CCodeFile] objects, each of which represent a code file being generated. In our example, we only need to create one [CCodeFile] object that represents the widget class file being generated. The new widget class will be generated under the `protected/components` directory. We call [CCodeFile::render] method to generate the actual code. This method includes the code template as a PHP script and returns the echoed content as the generated code.


#### Creating `views/index.php`

Having the controller (`WidgetGenerator`) and the model (`WidgetCode`), it is time for us to create the view `views/index.php`.

~~~
[php]
<h1>Widget Generator</h1>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'className'); ?>
		<?php echo $form->textField($model,'className',array('size'=>65)); ?>
		<div class="tooltip">
			Widget class name must only contain word characters.
		</div>
		<?php echo $form->error($model,'className'); ?>
	</div>

<?php $this->endWidget(); ?>
~~~

In the above, we mainly display a form using the [CCodeForm] widget. In this form, we display the field to collect the input for the `className` attribute in `WidgetCode`.

When creating the form, we can exploit two nice features provided by the [CCodeForm] widget. One is about input tooltips. The other is about sticky inputs.

If you have tried any default code generator, you will notice that when setting focus in one input field, a nice tooltip will show up next to the field. This can easily achieved here by writing next to the input field a `div` whose CSS class is `tooltip`.

For some input fields, we may want to remember their last valid values so that the user can save the trouble of re-entering them each time they use the generator to generate code. An example is the input field collecting the controller base class name default controller generator. These sticky fields are initially displayed as highlighted static text. If we click on them, they will turn into input fields to take user inputs.

In order to declare an input field to be sticky, we need to do two things.

First, we need to declare a `sticky` validation rule for the corresponding model attribute. For example, the default controller generator has the following rule to declare that `baseClass` and `actions` attributes are sticky:

~~~
[php]
public function rules()
{
	return array_merge(parent::rules(), array(
		......
		array('baseClass, actions', 'sticky'),
	));
}
~~~

Second, we need to add a CSS class named `sticky` to the container `div` of the input field in the view, like the following:

~~~
[php]
<div class="row sticky">
	...input field here...
</div>
~~~

#### Creating `templates/default/widget.php`

Finally, we create the code template `templates/default/widget.php`. As we described earlier, this is used like a view script that can contain PHP expressions and statements. In a code template, we can always access the `$this` variable which refers to the code model object. In our example, `$this` refers to the `WidgetModel` object. We can thus get the user-entered widget class name via `$this->className`.

~~~
[php]
<?php echo '<?php'; ?>

class <?php echo $this->className; ?> extends CWidget
{
	public function run()
	{

	}
}
~~~

This concludes the creation of a new code generator. We can access this code generator immediately via the URL `http://hostname/path/to/index.php?r=gii/widget`.

<div class="revision">$Id: topics.gii.txt 3223 2011-05-17 23:02:50Z alexander.makarow $</div>