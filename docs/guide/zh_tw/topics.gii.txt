Automatic Code Generation
=========================

Starting from version 1.1.2, Yii is equipped with a Web-based code generation tool called *Gii*. It supercedes the previous `yiic shell` generation tool which runs on command line. In this section, we will describe how to use Gii and how to extend Gii to increase our development productivity.

Using Gii
---------

Gii is implemented in terms of a module and must be used within an existing Yii application. To use Gii, we first modify the application configuration as follows:

~~~
[php]
return array(
	......
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'pick up a password here',
			// 'ipFilters'=>array(...a list of IPs...),
			// 'newFileMode'=>0666,
			// 'newDirMode'=>0777,
		),
	),
);
~~~

In the above, we declare a module named `gii` whose class is [GiiModule]. We also specify a password for the module which we will be prompted for when accessing Gii.

By default for security reasons, Gii is configured to be accessible only on localhost. If we want to make it accessible on other trustable computers, we can configure the [GiiModule::ipFilters] property as shown in the above code.

Because Gii may generate and save new code files in the existing application, we need to make sure that the Web server process has the proper permission to do so. The above [GiiModule::newFileMode] and [GiiModule::newDirMode] properties control how the new files and directories should be generated.

> Note: Gii is mainly provided as a development tool. Therefore, it should only be installed on a development machine. Because it can generate new PHP script files in the application, we should pay sufficient attention to its security measures (e.g. password, IP filters).

We can now access Gii via the URL `http://hostname/path/to/index.php?r=gii`. Here we assume `http://hostname/path/to/index.php` is the URL for accessing the existing Yii application.

If the existing Yii application uses `path`-format URLs (see [URL management](/doc/guide/topics.url)), we can access Gii via the URL `http://hostname/path/to/index.php/gii`. We may need to add the following URL rules to the front of the existing URL rules:

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
			...existing rules...
		),
	),
)
~~~

Gii comes with a few default code generators. Each code generator is responsible for generating a specific type of code. For example, the controller generator generates a controller class together with a few action view scripts; the model generator generates an ActiveRecord class for the specified database table.

The basic workflow of using a generator is as follows:

1. Enter the generator page;
2. Fill in the fields that specify the code generation parameters. For example, to use the module generator to create a new module, you need to specify the module ID;
3. Press the `Preview` button to preview the code to be generated. You will see a table showing a list of code files to be generated. You can click on any of them to preview the code;
4. Press the `Generate` button to generate the code files;
5. Review the code generation log.


Extending Gii
-------------

While the default code generators coming with Gii can generate very powerful code, we often want to customize them or create new ones to fit for our taste and needs. For example, we may want the generated code to be in our own favorite coding styles, or we may want to make the code to support multiple languages. All these can be done easily with Gii.

Gii can be extended in two ways: customizing the code templates of the existing code generators, and writing new code generators.

###Structure of a Code Generator

A code generator is stored under a directory whose name is treated as the generator name. The directory usually consists of the following content:

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

###Generator Search Path

Gii looks for available generators in a set of directories specified by the [GiiModule::generatorPaths] property. When customization is needed, we can configure this property in the application configuration as follows,

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

The above configuration instructs Gii to look for generators under the directory aliased as `application.gii`, in addition to the default location `system.gii.generators`.

It is possible to have two generators with the same name but under different search paths. In this case, the generator under the path specified earlier in [GiiModule::generatorPaths] will take precedence.


###Customizing Code Templates

This is the easiest and the most common way of extending Gii. We use an example to explain how to customize code templates. Assume we want to customize the code generated by the model generator.

We first create a directory named `protected/gii/model/templates/compact`. Here `model` means that we are going to *override* the default model generator. And `templates/compact` means we will add a new code template set named `compact`.

We then modify our application configuration to add `application.gii` to [GiiModule::generatorPaths], as shown in the previous sub-section.

Now open the model code generator page. Click on the `Code Template` field. We should see a dropdown list which contains our newly created template directory `compact`. However, if we choose this template to generate the code, we will see an error. This is because we have yet to put any actual code template file in this new `compact` template set.

Copy the file `framework/gii/generators/model/templates/default/model.php` to `protected/gii/model/templates/compact`. If we try generating again with the `compact` template, we should succeed. However, the code generated is no different from the one generated by the `default` template set.

It is time for us to do the real customization work. Open the file `protected/gii/model/templates/compact/model.php` to edit it. Remember that this file will be used like a view script, which means it can contain PHP expressions and statements. Let's modify the template so that the `attributeLabels()` method in the generated code uses `Yii::t()` to translate the attribute labels:

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

In each code template, we can access some predefined variables, such as `$labels` in the above example. These variables are provided by the corresponding code generator. Different code generators may provide different set of variables in their code templates. Please read the description in the default code templates carefully.


###Creating New Generators

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

<div class="revision">$Id$</div>