验证和授权(Authentication and Authorization)
================================

对于需要限制某些用户访问的网页，我们需要使用验证（Authentication）和授权（Authorization）。
验证是指核查一个人是否真的是他自己所声称的那个人。这通常需要一个用户名和密码，
但也包括任何其他可以表明身份的方式，例如一个智能卡，指纹等等。
授权则是找出已通过验证的用户是否允许操作特定的资源。
这一般是通过查询此用户是否属于一个有权访问该资源的角色来判断的。

Yii 有一个内置的验证/授权（auth）框架，用起来很方便，还能对其进行自定义，使其符合特殊的需求。

Yii auth 框架的核心是一个预定义的 *用户（user）应用组件* 它是一个实现了 [IWebUser] 接口的对象。
此用户组件代表当前用户的持久性认证信息。我们可以通过`Yii::app()->user`在任何地方访问它。

使用此用户组件，我们可以通过 [CWebUser::isGuest] 检查检查一个用户是否登陆; 可以 [登录（login）|CWebUser::login] 或
[注销（logout）|CWebUser::logout] 一个用户；我们可以通过[CWebUser::checkAccess]检查此用户是否可以执行特定的操作；还可以获取此用户的[唯一标识（unique identifier）|CWebUser::name]及其他持久性身份信息。


定义身份类 （Defining Identity Class）
-----------------------

为了验证一个用户，我们定义一个有验证逻辑的身份类。这个身份类实现[IUserIdentity] 接口。

不同的类可能实现不同的验证方式（例如：OpenID，LDAP）。最好是继承 [CUserIdentity]，此类是居于用户名和密码的验证方式。

定义身份类的主要工作是实现[IUserIdentity::authenticate]方法。在用户会话中根据需要，身份类可能需要定义别的身份信息

#### 应用实例

下面的例子，我们使用[Active Record](/doc/guide/database.ar)来验证提供的用户名、密码和数据库的用户表是否吻合。我们通过重写`getId`函数来返回验证过程中获得的`_id`变量（缺省的实现则是返回用户名）。在验证过程中，我们还借助[CBaseUserIdentity::setState]函数把获得的`title`信息存成一个状态。

1. The implementation of the `authenticate()` to use the database to validate credentials.
2. Overriding the `CUserIdentity::getId()` method to return the `_id` property because the default implementation returns the username as the ID.
3. Using the `setState()` ([CBaseUserIdentity::setState]) method to demonstrate storing other information that can easily be retrieved upon subsequent requests.

~~~
[php]
class UserIdentity extends CUserIdentity
{
	private $_id;
	public function authenticate()
	{
		$record=User::model()->findByAttributes(array('username'=>$this->username));
		if($record===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($record->password!==md5($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$record->id;
			$this->setState('title', $record->title);
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
    }

	public function getId()
	{
		return $this->_id;
	}
}
~~~

作为状态存储的信息（通过调用[CBaseUserIdentity::setState]）将被传递给[CWebUser]。而后者则把这些信息存放在一个永久存储媒介上（如session）。我们可以把这些信息当作[CWebUser]的属性来使用。例如，为了获得当前用户的`title`信息，我们可以使用`Yii::app()->user->title`（这项功能是在1.0.3版本引入的。在之前的版本里，我们需要使用`Yii::app()->user->getState('title')`）。

> info|提示: 缺省情况下，[CWebUser]用session来存储用户身份信息。如果允许基于cookie方式登录(通过设置
[CWebUser::allowAutoLogin]为 true)，用户身份信息将被存放在cookie中。确记敏感信息不要存放(例如 password) 。

登录和注销（Login and Logout）
----------------

使用身份类和用户部件，我们方便的实现登录和注销。

~~~
[php]
// 使用提供的用户名和密码登录用户
$identity=new UserIdentity($username,$password);
if($identity->authenticate())
	Yii::app()->user->login($identity);
else
	echo $identity->errorMessage;
......
// 注销当前用户
Yii::app()->user->logout();
~~~

Here we are creating a new UserIdentity object and passing in the authentication credentials (i.e. the `$username` and `$password` values submitted by the user) to its constructor. We then simply call the `authenticate()` method. If successful, we pass the identity information into the [CWebUser::login] method, which will store the identity information into persistent storage (PHP session by default) for retrieval upon subsequent requests. If the authentication fails, we can interrogate the `errorMessage` property for more information as to why it failed.

Whether or not a user has been authenticated can easily be checked throughout the application by using `Yii::app()->user->isGuest`. If using persistent storage like session (the default) and/or a cookie (discussed below) to store the identity information, the user can remain logged in upon subsequent requests. In this case, we don't need to use the UserIdentity class and the entire login process upon each request. Rather CWebUser will automatically take care of loading the identity information from this persistent storage and will use it to determine whether `Yii::app()->user->isGuest` returns true or false.

### 基于Cookie 的登录

缺省情况下，用户将根据[session configuration](http://www.php.net/manual/en/session.configuration.php)完成一序列inactivity动作后注销。设置用户部件的[allowAutoLogin|CWebUser::allowAutoLogin]属性为true和在[CWebUser::login]方法中设置一个持续时间参数来改变这个行为。即使用户关闭浏览器，此用户将保留用户登陆状态时间为被设置的持续时间之久。前提是用户的浏览器接受cookies。

~~~
[php]
// 保留用户登陆状态时间7天
// 确保用户部件的allowAutoLogin被设置为true。
Yii::app()->user->login($identity,3600*24*7);
~~~

As we mentioned above, when cookie-based login is enabled, the states
stored via [CBaseUserIdentity::setState] will be saved in the cookie as well.
The next time when the user is logged in, these states will be read from
the cookie and made accessible via `Yii::app()->user`.

Although Yii has measures to prevent the state cookie from being tampered
on the client side, we strongly suggest that security sensitive information be not
stored as states. Instead, these information should be restored on the server
side by reading from some persistent storage on the server side (e.g. database).

In addition, for any serious Web applications, we recommend using the following
strategy to enhance the security of cookie-based login.

* When a user successfully logs in by filling out a login form, we generate and
store a random key in both the cookie state and in persistent storage on server side
(e.g. database).

* Upon a subsequent request, when the user authentication is being done via the cookie information, we compare the two copies
of this random key and ensure a match before logging in the user.

* If the user logs in via the login form again, the key needs to be re-generated.

By using the above strategy, we eliminate the possibility that a user may re-use
an old state cookie which may contain outdated state information.

To implement the above strategy, we need to override the following two methods:

* [CUserIdentity::authenticate()]: this is where the real authentication is performed.
If the user is authenticated, we should re-generate a new random key, and store it
in the database as well as in the identity states via [CBaseUserIdentity::setState].

* [CWebUser::beforeLogin()]: this is called when a user is being logged in.
We should check if the key obtained from the state cookie is the same as the one
from the database.




访问控制过滤器（Access Control Filter）
---------------------

访问控制过滤器是检查当前用户是否能执行访问的controller action的初步授权模式。这种授权模式基于用户名，客户IP地址和访问类型。
It is provided as a filter named as
["accessControl"|CController::filterAccessControl].

> tip|小贴士: 访问控制过滤器适用于简单的验证。需要复杂的访问控制，需要使用将要讲解到的基于角色访问控制（role-based access (RBAC)）.

在控制器（controller）里重载[CController::filters]方法设置访问过滤器来控制访问动作(看
[Filter](/doc/guide/basics.controller#filter) 了解更多过滤器设置信息)。

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
}
~~~

在上面，设置的[access
control|CController::filterAccessControl]过滤器将应用于`PostController`里每个动作。过滤器具体的授权规则通过重载控制器的[CController::accessRules]方法来指定。

~~~
[php]
class PostController extends CController
{
	......
	public function accessRules()
	{
		return array(
			array('deny',
				'actions'=>array('create', 'edit'),
				'users'=>array('?'),
			),
			array('allow',
				'actions'=>array('delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'actions'=>array('delete'),
				'users'=>array('*'),
			),
		);
	}
}
~~~

上面设定了三个规则，每个用个数组表示。数组的第一个元素不是`'allow'`就是`'deny'`，其他的是名-值成对形式设置规则参数的。上面的规则这样理解：`create`和`edit`动作不能被匿名执行；`delete`动作可以被`admin`角色的用户执行；`delete`动作不能被任何人执行。

访问规则是一个一个按照设定的顺序一个一个来执行判断的。和当前判断模式（例如：用户名、角色、客户端IP、地址）相匹配的第一条规则决定授权的结果。如果这个规则是`allow`，则动作可执行；如果是`deny`，不能执行；如果没有规则匹配，动作可以执行。

> info|提示：为了确保某类动作在没允许情况下不被执行，设置一个匹配所有人的`deny`规则在最后，类似如下：

> ~~~
> [php]
> return array(
>     // ... 别的规则...
>     // 以下匹配所有人规则拒绝'delete'动作
>     array('deny',
>         'action'=>'delete',
>     ),
> );
> ~~~
> 因为如果没有设置规则匹配动作，动作缺省会被执行。

访问规则通过如下的上下文参数设置：

   - [actions|CAccessRule::actions]: 设置哪个动作匹配此规则。

   - [users|CAccessRule::users]: 设置哪个用户匹配此规则。
此当前用户的[name|CWebUser::name] 被用来匹配. 三种设定字符在这里可以用：

	   - `*`: 任何用户，包括匿名和验证通过的用户。
	   - `?`: 匿名用户。
	   - `@`: 验证通过的用户。

   - [roles|CAccessRule::roles]: 设定哪个角色匹配此规则。
这里用到了将在后面描述的[role-based access control](#role-based-access-control)技术。In particular, the rule is applied if [CWebUser::checkAccess] returns true for one of the roles.提示，用户角色应该被设置成`allow`规则，因为角色代表能做某些事情。

   - [ips|CAccessRule::ips]: 设定哪个客户端IP匹配此规则。

   - [verbs|CAccessRule::verbs]: 设定哪种请求类型(例如：`GET`, `POST`)匹配此规则。

   - [expression|CAccessRule::expression]: 设定一个PHP表达式。它的值用来表明这条规则是否适用。在表达式，你可以使用一个叫`$user`的变量，它代表的是`Yii::app()->user`。这个选项是在1.0.3版本里引入的。


### 授权处理结果（Handling Authorization Result）

当授权失败，即，用户不允许执行此动作，以下的两种可能将会产生：

   - 如果用户没有登录和在用户部件中配置了[loginUrl|CWebUser::loginUrl]，浏览器将重定位网页到此配置URL。

   - 否则一个错误代码401的HTTP例外将显示。

当配置[loginUrl|CWebUser::loginUrl] 属性，可以用相对和绝对URL。还可以使用数组通过[CWebApplication::createUrl]来生成URL。第一个元素将设置[route](/doc/guide/basics.controller#route) 为登录控制器动作，其他为名-值成对形式的GET参数。如下，

~~~
[php]
array(
	......
	'components'=>array(
		'user'=>array(
			// 这实际上是默认值
			'loginUrl'=>array('site/login'),
		),
	),
)
~~~

如果浏览器重定位到登录页面，而且登录成功，我们将重定位浏览器到引起验证失败的页面。我们怎么知道这个值呢？我们可以通过用户部件的[returnUrl|CWebUser::returnUrl] 属性获得。我们因此可以用如下执行重定向：

~~~
[php]
Yii::app()->request->redirect(Yii::app()->user->returnUrl);
~~~

基于角色的访问控制（Role-Based Access Control）
-------------------------

基于角色的访问控制提供了一种简单而又强大的集中访问控制。
请参阅[维基文章](http://en.wikipedia.org/wiki/Role-based_access_control)了解更多详细的RBAC与其他较传统的访问控制模式的比较。

Yii 通过其 [authManager|CWebApplication::authManager] 组件实现了分等级的 RBAC 结构。
在下文中，我们将首先介绍在此结构中用到的主要概念。然后讲解怎样定义用于授权的数据。在最后，我们看看如何利用这些授权数据执行访问检查。


### 概览（Overview）

在 Yii 的 RBAC 中，一个基本的概念是 *授权项目（authorization item）*。
一个授权项目就是一个做某件事的许可（例如新帖发布，用户管理）。根据其粒度和目标受众，
授权项目可分为 *操作（operations）*，*任务（tasks）* 和 *角色（roles）*。
一个角色由若干任务组成，一个任务由若干操作组成， 而一个操作就是一个许可，不可再分。
例如，我们有一个系统，它有一个 `管理员` 角色，它由 `帖子管理` 和 `用户管理` 任务组成。
`用户管理` 任务可以包含 `创建用户`，`修改用户` 和 `删除用户` 操作组成。
为保持灵活性，Yii 还允许一个角色包含其他角色或操作，一个任务可以包含其他操作，一个操作可以包括其他操作。


授权项目是通过它的名字唯一识别的。

一个授权项目可能与一个 *业务规则* 关联。
业务规则是一段 PHP 代码，在进行涉及授权项目的访问检查时将会被执行。
仅在执行返回 true 时，用户才会被视为拥有此授权项目所代表的权限许可。
例如，当定义一个 `updatePost（更新帖子）` 操作时，我们可以添加一个检查当前用户 ID 是否与此帖子的作者 ID 相同的业务规则，
这样，只有作者自己才有更新帖子的权限。

通过授权项目，我们可以构建一个 *授权等级体系* 。在等级体系中，如果项目 `A` 由另外的项目 `B` 组成（或者说 `A` 继承了 `B` 所代表的权限），则 `A` 就是 `B` 的父项目。
一个授权项目可以有多个子项目，也可以有多个父项目。因此，授权等级体系是一个偏序图（partial-order graph）结构而不是一种树状结构。
在这种等级体系中，角色项目位于最顶层，操作项目位于最底层，而任务项目位于两者之间。

一旦有了授权等级体系，我们就可以将此体系中的角色分配给用户。
而一个用户一旦被赋予一个角色，他就会拥有此角色所代表的权限。
例如，如果我们赋予一个用户 `管理员` 的角色，他就会拥有管理员的权限，包括
 `帖子管理` 和 `用户管理` （以及相应的操作，例如 `创建用户`）。

现在有趣的部分开始了，在一个控制器动作中，我们想检查当前用户是否可以删除指定的帖子。
利用 RBAC 等级体系和分配，可以很容易做到这一点。如下：

~~~
[php]
if(Yii::app()->user->checkAccess('deletePost'))
{
	// 删除此帖
}
~~~


配置授权管理器（Authorization Manager）
---------------------------------

在我们准备定义一个授权等级体系并执行访问权限检查之前，
我们需要配置一下 [authManager|CWebApplication::authManager] 应用组件。
Yii 提供了两种授权管理器：  [CPhpAuthManager] 和
[CDbAuthManager]。前者将授权数据存储在一个 PHP 脚本文件中而后者存储在数据库中。
配置 [authManager|CWebApplication::authManager] 应用组件时，我们需要指定使用哪个授权管理器组件类，
以及所选授权管理器组件的初始化属性值。例如：

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'sqlite:path/to/file.db',
		),
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'connectionID'=>'db',
		),
	),
);
~~~

然后，我们便可以使用 `Yii::app()->authManager` 访问 [authManager|CWebApplication::authManager] 应用组件。


定义授权等级体系
--------------------------------

定义授权等级体总共分三步：定义授权项目，建立授权项目之间的关系，还要分配角色给用户。
[authManager|CWebApplication::authManager] 应用组件提供了用于完成这三项任务的一系列 API 。

要定义一个授权项目，可调用下列方法之一，具体取决于项目的类型：

   - [CAuthManager::createRole]
   - [CAuthManager::createTask]
   - [CAuthManager::createOperation]

建立授权项目之后，我们就可以调用下列方法建立授权项目之间的关系：

   - [CAuthManager::addItemChild]
   - [CAuthManager::removeItemChild]
   - [CAuthItem::addChild]
   - [CAuthItem::removeChild]

最后，我们调用下列方法将角色分配给用户。

   - [CAuthManager::assign]
   - [CAuthManager::revoke]

下面的代码演示了使用 Yii 提供的 API 构建一个授权体系的例子：

~~~
[php]
$auth=Yii::app()->authManager;

$auth->createOperation('createPost','create a post');
$auth->createOperation('readPost','read a post');
$auth->createOperation('updatePost','update a post');
$auth->createOperation('deletePost','delete a post');

$bizRule='return Yii::app()->user->id==$params["post"]->authID;';
$task=$auth->createTask('updateOwnPost','update a post by author himself',$bizRule);
$task->addChild('updatePost');

$role=$auth->createRole('reader');
$role->addChild('readPost');

$role=$auth->createRole('author');
$role->addChild('reader');
$role->addChild('createPost');
$role->addChild('updateOwnPost');

$role=$auth->createRole('editor');
$role->addChild('reader');
$role->addChild('updatePost');

$role=$auth->createRole('admin');
$role->addChild('editor');
$role->addChild('author');
$role->addChild('deletePost');

$auth->assign('reader','readerA');
$auth->assign('author','authorB');
$auth->assign('editor','editorC');
$auth->assign('admin','adminD');
~~~

建立此授权等级体系后，[authManager|CWebApplication::authManager] 组件（例如 [CPhpAuthManager], [CDbAuthManager]）
就会自动加载授权项目。因此，我们只需要运行上述代码一次，并不需要在每个请求中都要运行。

> Info|信息: 上面的示例看起来比较冗长拖沓，它主要用于演示的目的。
> 开发者通常需要开发一些用于管理的用户界面，这样最终用户可以通过界面更直观地建立一个授权等级体系。


使用业务规则
--------------------

在定义授权等级体系时，我们可以将 *业务规则* 关联到一个角色，一个任务，或者一个操作。
我们也可以在为一个用户分配角色时关联一个业务规则。
一个业务规则就是一段 PHP 代码，在我们执行权限检查时被执行。
代码返回的值用来决定是否将角色或分配应用到当前用户。
在上面的例子中，我们把一条业务规则关联到了 `updateOwnPost` 任务。
在业务规则中，我们简单的检查了当前用户的 ID 是否与指定帖子的作者 ID 相同。
`$params` 数组中的帖子（post）信息由开发者在执行权限检查时提供。


### 权限检查

要执行权限检查，我们首先需要知道授权项目的名字。
例如，要检查当前用户是否可以创建帖子，我们需要检查他是否拥有 `createPost` 所表示的权限。
然后我们调用 [CWebUser::checkAccess] 执行权限检查：

~~~
[php]
if(Yii::app()->user->checkAccess('createPost'))
{
	// 创建帖子
}
~~~

如果授权规则关联了一条需要额外参数的业务规则，我们也可以传递给它。例如，要检查一个用户是否可以更新帖子，
我们可以通过 `$params` 传递帖子的数据：

~~~
[php]
$params=array('post'=>$post);
if(Yii::app()->user->checkAccess('updateOwnPost',$params))
{
	// 更新帖子
}
~~~


### 使用默认角色

> Note|注意: 默认角色功能从 1.0.3 版本起可用。

许多 Web 程序需要一些可以分配给系统中所有或大多数用户的比较特殊的角色。
例如，我们可能想要分配一些权限给所有已通过身份验证的用户。如果我们特意指定并存储这些角色分配，就会引起很多维护上的麻烦。
我们可以利用 *默认角色* 解决这个问题。

默认角色就是一个隐式分配给每个用户的角色，这些用户包括通过身份验证的用户和游客。
我们不需要显式地将其分配给一个用户。
当 [CWebUser::checkAccess] 被调用时，将会首先检查默认的角色，就像它已经被分配给这个用户一样。

默认角色必须定义在 [CAuthManager::defaultRoles] 属性中。
例如，下面的配置声明了两个角色为默认角色：`authenticated` 和 `guest`。

~~~
[php]
return array(
	'components'=>array(
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'defaultRoles'=>array('authenticated', 'guest'),
		),
	),
);
~~~

由于默认角色会被分配给每个用户，它通常需要关联一个业务规则以确定角色是否真的要应用到用户。
例如，下面的代码定义了两个角色， `authenticated` 和 `guest`，很高效地分别应用到了已通过身份验证的用户和游客用户。

~~~
[php]
$bizRule='return !Yii::app()->user->isGuest;';
$auth->createRole('authenticated', 'authenticated user', $bizRule);

$bizRule='return Yii::app()->user->isGuest;';
$auth->createRole('guest', 'guest user', $bizRule);
~~~

<div class="revision">$Id: topics.auth.txt 2739 2010-12-14 01:50:04Z weizhuo $</div>