创建模型
==============

在编写表单所需的 HTML 代码之前，我们应该先确定来自最终用户输入的数据的类型，以及这些数据应符合什么样的规则。
模型类可用于记录这些信息。
正如[模型](/doc/guide/basics.model) 章节所定义的，
模型是保存用户输入和验证这些输入的中心位置。

取决于使用用户所输入数据的方式，我们可以创建两种类型的模型。
如果用户输入被收集、使用然后丢弃，我们应该创建一个  [表单模型](/doc/guide/basics.model);
如果用户的输入被收集后要保存到数据库，我们应使用一个 
[Active Record](/doc/guide/database.ar) 。
两种类型的模型共享同样的基类 [CModel] ，它定义了表单所需的通用接口。

> Note|注意: 我们在这一节的示例中主要使用了表单模型 。然而，同样的操作也可应用于
 [active record](/doc/guide/database.ar) 模型。

定义模型类
--------------------

下面我们创建了一个 `LoginForm` 模型类用于在一个登录页中收集用户的输入。
由于登录信息只被用于验证用户，并不需要保存，因此我们将 `LoginForm` 创建为一个 表单模型。

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;
}
~~~

`LoginForm` 中定义了三个属性: `$username`, `$password` 和
`$rememberMe`。他们用于保存用户输入的用户名和密码，还有用户是否想记住他的登录的选项。
由于 `$rememberMe` 有一个默认的值 `false`，相应的选项在初始化显示在登录表单中时将是未勾选状态。

> Info|信息: 我们将这些成员变量称为 *特性（attributes）* 而不是 属性（properties），以区别于普通的属性（properties）。
特性（attribute）是一个主要用于存储来自用户输入或数据库数据的属性（propertiy）。

声明验证规则
--------------------------

一旦用户提交了他的输入，模型被填充，我们就需要在使用前确保用户的输入是有效的。
这是通过将用户的输入和一系列规则执行验证实现的。我们在 `rules()` 方法中指定这些验证规则，
此方法应返回一个规则配置数组。

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;

	private $_identity;

	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate'),
		);
	}

	public function authenticate($attribute,$params)
	{
		$this->_identity=new UserIdentity($this->username,$this->password);
		if(!$this->_identity->authenticate())
			$this->addError('password','错误的用户名或密码。');
	}
}
~~~

上述代码指定：`username` 和 `password` 为必填项，
`password` 应被验证（authenticated），`rememberMe` 应该是一个布尔值。

`rules()` 返回的每个规则必须是以下格式：

~~~
[php]
array('AttributeList', 'Validator', 'on'=>'ScenarioList', ...附加选项)
~~~

其中 `AttributeList（特性列表）` 是需要通过此规则验证的特性列表字符串，每个特性名字由逗号分隔;
`Validator（验证器）` 指定要执行验证的种类；`on` 参数是可选的，它指定此规则应被应用到的场景列表；
附加选项是一个名值对数组，用于初始化相应验证器的属性值。

有三种方式可在验证规则中指定  `Validator` 。第一，
`Validator` 可以是模型类中一个方法的名字，就像上面示例中的
`authenticate` 。验证方法必须是下面的结构：

~~~
[php]
/**
 * @param string 所要验证的特性的名字
 * @param array 验证规则中指定的选项
 */
public function ValidatorName($attribute,$params) { ... }
~~~

第二，`Validator` 可以是一个验证器类的名字，当此规则被应用时，
一个验证器类的实例将被创建以执行实际验证。规则中的附加选项用于初始化实例的属性值。
验证器类必须继承自 [CValidator]。

第三，`Validator` 可以是一个预定义的验证器类的别名。在上面的例子中，
`required` 名字是 [CRequiredValidator] 的别名，它用于确保所验证的特性值不为空。
下面是预定义的验证器别名的完整列表：

   - `boolean`: [CBooleanValidator] 的别名， 确保特性有一个 [CBooleanValidator::trueValue] 或
[CBooleanValidator::falseValue] 值。

   - `captcha`: [CCaptchaValidator] 的别名，确保特性值等于 [CAPTCHA](http://en.wikipedia.org/wiki/Captcha) 中显示的验证码。

   - `compare`: [CCompareValidator] 的别名，确保特性等于另一个特性或常量。
   
   - `email`: [CEmailValidator] 的别名，确保特性是一个有效的Email地址。

   - `default`: [CDefaultValueValidator] 的别名，指定特性的默认值。

   - `exist`: [CExistValidator] 的别名，确保特性值可以在指定表的列中可以找到。

   - `file`: [CFileValidator] 的别名，确保特性含有一个上传文件的名字。

   - `filter`: [CFilterValidator] 的别名，通过一个过滤器改变此特性。

   - `in`: [CRangeValidator] 的别名，确保数据在一个预先指定的值的范围之内。

   - `length`: [CStringValidator] 的别名，确保数据的长度在一个指定的范围之内。

   - `match`: [CRegularExpressionValidator] 的别名，确保数据可以匹配一个正则表达式。

   - `numerical`: [CNumberValidator] 的别名，确保数据是一个有效的数字。

   - `required`: [CRequiredValidator] 的别名，确保特性不为空。

   - `type`: [CTypeValidator] 的别名，确保特性是指定的数据类型。

   - `unique`: [CUniqueValidator] 的别名，确保数据在数据表的列中是唯一的。

   - `url`: [CUrlValidator] 的别名，确保数据是一个有效的 URL。

下面我们列出了几个只用这些预定义验证器的示例：

~~~
[php]
// 用户名为必填项
array('username', 'required'),
// 用户名必须在 3 到 12 个字符之间
array('username', 'length', 'min'=>3, 'max'=>12),
// 在注册场景中，密码password必须和password2一致。
array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
// 在登录场景中，密码必须接受验证。
array('password', 'authenticate', 'on'=>'login'),
~~~


安全的特性赋值
------------------------------

在一个类的实例被创建后，我们通常需要用最终用户提交的数据填充它的特性。
这可以通过如下块赋值（massive assignment）方式轻松实现：

~~~
[php]
$model=new LoginForm;
if(isset($_POST['LoginForm']))
	$model->attributes=$_POST['LoginForm'];
~~~

最后的表达式被称作 *块赋值（massive assignment）* ，它将  `$_POST['LoginForm']`
中的每一项复制到相应的模型特性中。这相当于如下赋值方法：

~~~
[php]
foreach($_POST['LoginForm'] as $name=>$value)
{
	if($name 是一个安全的特性)
		$model->$name=$value;
}
~~~

检测特性的安全非常重要，例如，如果我们以为一个表的主键是安全的而暴露了它，那么攻击者可能就获得了一个修改记录的主键的机会，
从而篡改未授权给他的内容。

检测特性安全的策略在版本 1.0 和 1.1 中是不同的，下面我们将分别讲解：

###1.1 中的安全特性

在版本 1.1 中，特性如果出现在相应场景的一个验证规则中，即被认为是安全的。
例如：

~~~
[php]
array('username, password', 'required', 'on'=>'login, register'),
array('email', 'required', 'on'=>'register'),
~~~

如上所示， `username` 和 `password` 特性在 `login` 场景中是必填项。而
 `username`, `password` 和 `email` 特性在 `register` 场景中是必填项。
于是，如果我们在 `login` 场景中执行块赋值，就只有 `username` 和 `password` 会被块赋值。
因为只有它们出现在 `login` 的验证规则中。
另一方面，如果场景是 `register` ，这三个特性就都可以被块赋值。

~~~
[php]
// 在登录场景中
$model=new User('login');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];

// 在注册场景中
$model=new User('register');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];
~~~

那么为什么我们使用这样一种策略来检测特性是否安全呢？
背后的基本原理就是：如果一个特性已经有了一个或多个可检测有效性的验证规则，那我们还担心什么呢？

请记住，验证规则是用于检查用户输入的数据，而不是检查我们在代码中生成的数据（例如时间戳，自动产生的主键）。
因此，**不要** 为那些不接受最终用户输入的特性添加验证规则。

有时候，我们想声明一个特性是安全的，即使我们没有为它指定任何规则。
例如，一篇文章的内容可以接受用户的任何输入。我们可以使用特殊的 `safe` 规则实现此目的：

~~~
[php]
array('content', 'safe')
~~~

为了完成起见，还有一个用于声明一个属性为不安全的  `unsafe` 规则：

~~~
[php]
array('permission', 'unsafe')
~~~

`unsafe` 规则并不常用，它是我们之前定义的安全特性的一个例外。


###1.0 中的安全特性

在版本1.0中,决定一个数据项是否是安全的,基于一个名为 `safeAttributes` 方法的返回值和数据项被指定的场景. 默认的,这个方法返回所有公共成员变量作为 [CFormModel]
的安全特性,而它也返回了除了主键外, 表中所有字段名作为 [CActiveRecord]的安全特性.我们可以根据场景重写这个方法来限制安全特性 .例如, 一个用户模型可以包含很多特性,但是在 `login`
场景.里,我们只能使用  `username` 和 `password` 特性.我们可以按照如下来指定这一限制 :

~~~
[php]
public function safeAttributes()
{
	return array(
		parent::safeAttributes(),
		'login' => 'username, password',
	);
}
~~~

`safeAttributes` 方法更准确的返回值应该是如下结构的 :

~~~
[php]
array(
   // these attributes can be massively assigned in any scenario
   // that is not explicitly specified below
   'attr1, attr2, ...',
	 *
   // these attributes can be massively assigned only in scenario 1
   'scenario1' => 'attr2, attr3, ...',
	 *
   // these attributes can be massively assigned only in scenario 2
   'scenario2' => 'attr1, attr3, ...',
)
~~~

如果模型不是场景敏感的(比如,它只在一个场景中使用,或者所有场景共享了一套同样的安全特性),返 回值可以是如下那样简单的字符串.

~~~
[php]
'attr1, attr2, ...'
~~~

而那些不安全的数据项,我们需要使用独立的赋值语句来分配它们到相应的特性.如下所示:

~~~
[php]
$model->permission='admin';
$model->id=1;
~~~


触发验证
---------------------

一旦模型被用户提交的数据填充，我们就可以调用 [CModel::validate()]
出发数据验证进程。此方法返回一个指示验证是否成功的值。
对 [CActiveRecord] 模型来说，验证也可以在我们调用其  [CActiveRecord::save()]
方法时自动触发。

我们可以使用 [scenario|CModel::scenario] 设置场景属性，这样，相应场景的验证规则就会被应用。

验证是基于场景执行的。 [scenario|CModel::scenario] 属性指定了模型当前用于的场景和当前使用的验证规则集。
例如，在 `login` 场景中，我们只想验证用户模型中的 `username` 和 `password` 输入；
而在 `register` 场景中，我们需要验证更多的输入，例如  `email`, `address`, 等。
下面的例子演示了如何在 `register` 场景中执行验证：

~~~
[php]
// 在注册场景中创建一个  User 模型。等价于：
// $model=new User;
// $model->scenario='register';
$model=new User('register');

// 将输入的值填充到模型
$model->attributes=$_POST['User'];

// 执行验证
if($model->validate())   // if the inputs are valid
    ...
else
    ...
~~~

规则关联的场景可以通过规则中的 `on` 选项指定。如果 `on` 选项未设置，则此规则会应用于所有场景。例如：

~~~
[php]
public function rules()
{
	return array(
		array('username, password', 'required'),
		array('password_repeat', 'required', 'on'=>'register'),
		array('password', 'compare', 'on'=>'register'),
	);
}
~~~

第一个规则将应用于所有场景，而第二个将只会应用于 `register` 场景。


提取验证错误
----------------------------

验证完成后，任何可能产生的错误将被存储在模型对象中。
我们可以通过调用 [CModel::getErrors()] 和[CModel::getError()] 提取这些错误信息。
这两个方法的不同点在于第一个方法将返回 *所有* 模型特性的错误信息，而第二个将只返回 *第一个* 错误信息。

特性标签
----------------

当设计表单时，我们通常需要为每个表单域显示一个标签。
标签告诉用户他应该在此表单域中填写什么样的信息。虽然我们可以在视图中硬编码一个标签，
但如果我们在相应的模型中指定（标签），则会更加灵活方便。

默认情况下 [CModel] 将简单的返回特性的名字作为其标签。这可以通过覆盖
[attributeLabels()|CModel::attributeLabels] 方法自定义。
正如在接下来的小节中我们将看到的，在模型中指定标签会使我们能够更快的创建出更强大的表单。

<div class="revision">$Id: form.model.txt 2285 2010-07-28 20:40:00Z qiang.xue $</div>