使用表单生成器
==================

当创建 HTML 表单时，经常我们发现我们在写很多重复而且在不同项目中很难重用的视图代码。
例如，对于每个输入框， 我们需要以一个文本标签和显示可能的验证错误来关联它。
为了改善这些代码的重用性，我们可以使用自版本 1.1.0 可用的表单生成器特征。


基本概念
--------------

Yii 表单生成器使用 [CForm] 对象来代表描述一个HTML表单所需的内容，包括哪些数据模型关联到此表单，
表单中有哪些输入框，以及如何渲染整个表单。开发者主要需要创建和配置这个 [CForm] 对象，然后调用它的渲染方法来显示表单。

表单的输入框参数被组织为根据表单元素的分层结构。
在结构的顶层，是 [CForm] 对象。此对象的成员分为两大类： [CForm::buttons] 和 [CForm::elements]。前者包含
按钮元素（例如提交按钮，重设按钮），后者包含输入元素，静态文本和子表单。子表单也是 [CForm] 对象，只是它存在于
另一个表单的 [CForm::elements] 中。子表单可以有它自己的数据模型，
[CForm::buttons] 和 [CForm::elements] 集合。

当用户提交一个表单时，整个表单结构中填写的数据被提交，
也包含子表单中填写的数据。 [CForm] 提供了便利方法，可以自动赋值输入的数据到对应的数据属性并执行数据验证。


创建一个简单的表单
----------------------

下面，我们展示如何使用表单生成器来创建一个登录表单。

首先，我们编写登录 action 代码：

~~~
[php]
public function actionLogin()
{
	$model = new LoginForm;
	$form = new CForm('application.views.site.loginForm', $model);
	if($form->submitted('login') && $form->validate())
		$this->redirect(array('site/index'));
	else
		$this->render('login', array('form'=>$form));
}
~~~

在上面的代码中，我们使用由路径别名 `application.views.site.loginForm` （将会简要解释） 指定的参数创建了 [CForm] 对象。
[CForm] 对象和 `LoginForm` 模型（在[Creating Model](/doc/guide/form.model)中已介绍）关联。

如代码所示，若表单被提交并且所有的输入经过了验证而没有错误，我们将转向用户的浏览器到 `site/index` 页面。否则，
我们以此表单渲染 `login` 视图。

路径别名 `application.views.site.loginForm` 实际指的是 PHP 文件
`protected/views/site/loginForm.php`。此文件应当返回一个 PHP 数组，这个数组代表了 [CForm] 所需的配置，
如下所示：

~~~
[php]
return array(
	'title'=>'Please provide your login credential',

    'elements'=>array(
        'username'=>array(
            'type'=>'text',
            'maxlength'=>32,
        ),
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),
        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),

    'buttons'=>array(
        'login'=>array(
            'type'=>'submit',
            'label'=>'Login',
        ),
    ),
);
~~~

配置是一个由键值对组成的关联数组，被用来初始化 [CForm] 的对应属性。要配置的最重要的属性，如之前所述，是 [CForm::elements] 和 [CForm::buttons]。
它们的每一个是一个指定了表单元素列表的数组。在下一小节我们将给出更多细节关于如何配置表单元素。

最后，我们编写 `login` 视图，可以简洁地如下所示，

~~~
[php]
<h1>Login</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~

> Tip | 提示: 上面的代码 `echo $form;` 相当于 `echo $form->render();`。
> 这是因为 [CForm] 执行了 `__toString` 魔术方法，它调用 `render()` 并返回它的结果为代表此表单对象的字符串。


指定表单元素
------------------------

使用表单生成器，我们大部分的工作由编写视图脚本代码转为指定表单元素。在这一小节中，我们讲述如何指定 [CForm::elements] 属性。
我们不准备讲述 [CForm::buttons] 因为它的配置和 [CForm::elements] 的配置几乎相同。

[CForm::elements] 属性接受一个数组作为它的值。每个数组元素指定了一个单独的表单元素，这个表单元素可以是一个输入框，一个静态文本字符串或一个子表单。

### 指定输入元素

一个输入元素主要由标签，输入框，提示文字和错误显示组成。
它必须和一个模型属性关联。一个输入元素的规格被代表为一个 [CFormInputElement]  实例。 [CForm::elements] 数组中的如下代码指定了一个单独的输入元素：

~~~
[php]
'username'=>array(
    'type'=>'text',
    'maxlength'=>32,
),
~~~

它说明模型属性被命名为 `username`，输入框的类型为 `text`，它的 `maxlength` 属性为 32。

任何 [CFormInputElement] 可写的属性都可以如上配置。例如，我们可以指定
 [hint|CFormInputElement::hint] 选项来显示提示信息，或者我们可以指定
[items|CFormInputElement::items] 选项若输入框是一个 list box，一个下拉列表，一个多选列表或一个单选按钮列表。
若选项的名字不是一个 [CFormInputElement] 属性，它将被认为是对应 HTML 输入元素的属性，
例如，因为上面的 `maxlength` 不是一个 [CFormInputElement] 属性，它被渲染作为 HTML 文本输入框的 `maxlength` 属性。

[type|CFormInputElement::type] 选项需要特别注意。它指定了输入框的类型。
例如，`text` 类型意味着将渲染一个普通的文本输入框；`password` 类型意味着将渲染一个密码输入框。 [CFormInputElement] 识别如下内置的类型：

 - text
 - hidden
 - password
 - textarea
 - file
 - radio
 - checkbox
 - listbox
 - dropdownlist
 - checkboxlist
 - radiolist

在上面的内置类型中，我们想要对这些 "list" 类型的用法多说一些，
包括 `dropdownlist`， `checkboxlist` 和 `radiolist`。这些类型需要设置对应输入元素的 [items|CFormInputElement::items]
属性。可以这样做：

~~~
[php]
'gender'=>array(
    'type'=>'dropdownlist',
    'items'=>User::model()->getGenderOptions(),
    'prompt'=>'Please select:',
),

...

class User extends CActiveRecord
{
	public function getGenderOptions()
	{
		return array(
			0 => 'Male',
			1 => 'Female',
		);
	}
}
~~~

上面的代码将生成一个下拉列表选择器，提示文字是 “please select:”。选项包括
“Male” 和 “Female”，它们是由 `User` 模型类中的 `getGenderOptions` 方法返回的。

除了这些内置的类型， [type|CFormInputElement::type] 选项也可以是一个 widget 类名字或 widget 类的路径别名。
widget 类必须扩展自 [CInputWidget] 或 [CJuiInputWidget]。当渲染输入元素时，
一个指定 widget 类的实例将被创建并渲染。The widget will be configured using
the specification as given for the input element.


### 指定静态文本

很多情况下，一个表单包含一些装饰性的 HTML 代码。 例如，一个水平线被用来分隔表单中不同的部分；一个图像出现在特定的位置来增强表单的视觉外观。
我们可以在 [CForm::elements] 集合中指定这些 HTML 代码作为静态文本。要这样做，我们只要指定一个静态文本字符串作为一个数组元素，在 [CForm::elements] 恰当的位置。例如,

~~~
[php]
return array(
    'elements'=>array(
		......
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),

        '<hr />',

        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),
	......
);
~~~

在上面，我们在 `password` 输入框和 `rememberMe` 之间插入一个水平线。

静态文本最好用于文本内容和它们的位置不规则时。 若表单中的每个输入元素需要被相似的装饰，我们应当定制表单渲染方法，此章节将简短介绍。


### 指定子表单

子表单被用来分离一个长的表单为几个逻辑部分。 例如，我们可以分离用户注册表单为两部分：登录信息和档案信息。
每个子表单和一个数据模型有无关联均可。例如在用户注册表单，若我们存储用户登录信息和档案信息到两个分离的数据表中(表示为两个数据模型)，
 然后每个子表单需要和一个对应的数据模型关联。若我们存储所有信息到一个数据表中，任意一个子表单都没有数据模型,因为它们和根表单分享相同的模型。

一个子表单也表示为一个[CForm] 对象。要指定一个子表单，我们应当配置 [CForm::elements] 属性为一个类型是 `form` 的元素：

~~~
[php]
return array(
    'elements'=>array(
		......
        'user'=>array(
            'type'=>'form',
            'title'=>'Login Credential',
            'elements'=>array(
            	'username'=>array(
            		'type'=>'text',
            	),
            	'password'=>array(
            		'type'=>'password',
            	),
            	'email'=>array(
            		'type'=>'text',
            	),
            ),
        ),

        'profile'=>array(
        	'type'=>'form',
        	......
        ),
        ......
    ),
	......
);
~~~

类似于配置一个根表单，我们主要需要为一个子表单指定 [CForm::elements] 属性。若一个子表单需要关联一个数据模型，我们也可以配置它的 [CForm::model]  属性。

有时，我们想要使用一个类代表表单，而不使用默认的 [CForm] 类。例如，
此小节将简短展示，我们可以扩展 [CForm] 以定制表单渲染逻辑。
通过指定输入元素的类型为 `form`，一个子表单将自动被表示为一个对象，它的类和它的父表单相同。若我们指定输入元素的类型类似于 `XyzForm` (一个以 `Form` 结尾的字符串)，
然后子表单将被表示为一个 `XyzForm` 对象。


访问表单元素
-----------------------

访问表单元素和访问数组元素一样简单。[CForm::elements] 属性返回一个 [CFormElementCollection] 对象，
它扩展自 [CMap]  并允许以类似于一个普通数组的方式来访问它的元素。例如，要访问登录表单中的元素 `username`，我们可以使用下面的代码：

~~~
[php]
$username = $form->elements['username'];
~~~

要访问用户注册表单中的 `email` 元素，使用

~~~
[php]
$email = $form->elements['user']->elements['email'];
~~~

因为 [CForm] 为它的 [CForm::elements] 属性执行数组访问，上面的代码可以简化为：

~~~
[php]
$username = $form['username'];
$email = $form['user']['email'];
~~~


创建一个嵌套表单
----------------------

我们已经描述了子表单。我们称一个有子表单的表单为一个嵌套表单。在这一章节，
我们使用用户注册表单作为例子来展示如何创建一个关联多个数据模型的嵌套表单。我们假设用户的认证信息存储为一个 `User` 模型，而用户的档案信息被存储为一个 `Profile` 模型。

我们首先创建 `register` action 如下：

~~~
[php]
public function actionRegister()
{
	$form = new CForm('application.views.user.registerForm');
	$form['user']->model = new User;
	$form['profile']->model = new Profile;
	if($form->submitted('register') && $form->validate())
	{
		$user = $form['user']->model;
		$profile = $form['profile']->model;
		if($user->save(false))
		{
			$profile->userID = $user->id;
			$profile->save(false);
			$this->redirect(array('site/index'));
		}
	}

	$this->render('register', array('form'=>$form));
}
~~~

在上面，我们使用由 `application.views.user.registerForm` 指定的配置创建了表单。
在表单被提交且成功验证之后，我们尝试保存 user 和 proﬁle 模型。
我们通过访问相应子表单对象的 `model` 属性来检索 user 和 proﬁle 模型。
因为输入验证已经完成，我们调用 `$user->save(false)` 来跳过验证。为 proﬁle 模型也这样做。

接下来，我们编写表单配置文件 `protected/views/user/registerForm.php`：

~~~
[php]
return array(
	'elements'=>array(
		'user'=>array(
			'type'=>'form',
			'title'=>'Login information',
			'elements'=>array(
		        'username'=>array(
		            'type'=>'text',
		        ),
		        'password'=>array(
		            'type'=>'password',
		        ),
		        'email'=>array(
		            'type'=>'text',
		        )
			),
		),

		'profile'=>array(
			'type'=>'form',
			'title'=>'Profile information',
			'elements'=>array(
		        'firstName'=>array(
		            'type'=>'text',
		        ),
		        'lastName'=>array(
		            'type'=>'text',
		        ),
			),
		),
	),

    'buttons'=>array(
        'register'=>array(
            'type'=>'submit',
            'label'=>'Register',
        ),
    ),
);
~~~

在上面，当指定每个子表单时，我们也指定它的 [CForm::title] 属性。
默认的表单渲染逻辑将封装每个子表单到一个 ﬁeld-set 中，使用此属性作为它的标题。

最后，我们编写 `register` 视图脚本：

~~~
[php]
<h1>Register</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~


定制表单显示
------------------------

使用表单生成器最主要的好处是逻辑 (表单配置被存储在一个单独的文件中) 和表现 ([CForm::render]方法) 的分离。
这样，我们可以实现定制表单显示，通过重写 [CForm::render] 或提供一个局部视图来渲染表单。两种方法都可以保持表单配置的完整性，并且可以容易地重用。

当重写 [CForm::render] 时, 你主要需要遍历 [CForm::elements]  和 [CForm::buttons] 并调用每个表单元素的 [CFormElement::render] 方法。例如,

~~~
[php]
class MyForm extends CForm
{
	public function render()
	{
		$output = $this->renderBegin();

		foreach($this->getElements() as $element)
			$output .= $element->render();

		$output .= $this->renderEnd();

		return $output;
	}
}
~~~

可能我们也需要写一个视图脚本 `_form` 以渲染一个视图：

~~~
[php]
<?php
echo $form->renderBegin();

foreach($form->getElements() as $element)
	echo $element->render();

echo $form->renderEnd();
~~~

要使用此视图脚本，我们需要调用：

~~~
[php]
<div class="form">
$this->renderPartial('_form', array('form'=>$form));
</div>
~~~

若一个通用的表单渲染不适用于一个特殊的表单(例如，表单为特定的元素需要不规则的装饰)，在视图脚本中我们可以这样做:

~~~
[php]
some complex UI elements here

<?php echo $form['username']; ?>

some complex UI elements here

<?php echo $form['password']; ?>

some complex UI elements here
~~~

在最后的方法中，表单生成器看起来并没有带来好处，因为我们仍然需要写很多表单代码。然而，它仍然是有好处的，表单被使用一个分离的配置文件指定，这样可以帮助开发者更专注于逻辑部分。


<div class="revision">$Id: form.builder.txt 2353 2010-08-28 20:45:26Z qiang.xue $</div>