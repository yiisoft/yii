用户验证
===================

我们的博客应用需要区分系统所有者和来宾用户。因此，我们需要实现 [用户验证](http://www.yiiframework.com/doc/guide/topics.auth) 功能。

或许你已经发现了，我们的程序骨架已经提供了用户验证功能，它会判断用户名和密码是不是都为 `demo` 或 `admin`。在这一节里，我们将修改这些代码，以使身份验证通过 `User` 数据表实现。

用户验证在一个实现了 [IUserIdentity] 接口的类中进行。此程序骨架通过 `UserIdentity` 类实现此目的。此类存储在 `/wwwroot/blog/protected/components/UserIdentity.php` 文件中。

> Tip|提示: 按照约定，类文件的名字必须是相应的类名加上 `.php` 后缀。遵循此约定，就可以通过一个[路径别名（path alias）](http://www.yiiframework.com/doc/guide/basics.namespace) 指向此类。例如，我们可以通过别名 `application.components.UserIdentity` 指向 `UserIdentity` 类。Yii 的许多API都可以识别路径别名 (例如 [Yii::createComponent()|YiiBase::createComponent])，使用路径别名可以避免在代码中插入文件的绝对路径。绝对路径的存在往往会导致在部署应用时遇到麻烦。

我们将 `UserIdentity` 类做如下修改,

~~~
[php]
<?php
class UserIdentity extends CUserIdentity
{
	private $_id;

	public function authenticate()
	{
		$username=strtolower($this->username);
		$user=User::model()->find('LOWER(username)=?',array($username));
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if(!$user->validatePassword($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$user->id;
			$this->username=$user->username;
			$this->errorCode=self::ERROR_NONE;
		}
		return $this->errorCode==self::ERROR_NONE;
	}

	public function getId()
	{
		return $this->_id;
	}
}
~~~

在 `authenticate()` 方法中，我们使用 `User` 类来查询 `tbl_user` 表中 `username` 列值（不区分大小写）和提供的用户名一致的一行，请记住 `User` 类是在前面的章节中通过 `gii` 工具创建的。由于 `User` 类继承自 [CActiveRecord] ，我们可以利用 [ActiveRecord 功能](http://www.yiiframework.com/doc/guide/database.ar) 以 OOP 的风格访问 `tbl_user` 表。

为了检查用户是否输入了一个有效的密码，我们调用了 `User` 类的 `validatePassword` 方法。我们需要按下面的代码修改 `/wwwroot/blog/protected/models/User.php` 文件。注意，我们在数据库中存储了密码的加密串和随机生成的SALT密钥，而不是存储明文密码。 所以当要验证用户输入的密码时，我们应该和加密结果做对比。

~~~
[php]
class User extends CActiveRecord
{
	......
	public function validatePassword($password)
	{
		return $this->hashPassword($password,$this->salt)===$this->password;
	}

	public function hashPassword($password,$salt)
	{
		return md5($salt.$password);
	}
}
~~~

在 `UserIdentity` 类中，我们还覆盖（Override，又称为重写）了 `getId()` 方法，它会返回在 `User` 表中找到的用户的 `id`。父类 ([CUserIdentity]) 则会返回用户名。`username` 和 `id` 属性都将存储在用户 SESSION 中，可在代码的任何部分通过 `Yii::app()->user` 访问。

> Tip|提示: 在 `UserIdentity` 类中，我们没有显式包含(include)相应的类文件就访问了 [CUserIdentity] 类，这是因为 [CUserIdentity] 是一个由Yii框架提供的核心类。Yii 将会在任何核心类被首次使用时自动包含类文件。
>
> 我们也对 `User` 类做了同样的事情。这是因为 `User` 类文件被放在了 `/wwwroot/blog/protected/models` 目录，此目录已经通过应用配置中的如下几行代码被添加到了 PHP 的 `include_path` 中：
>
> ~~~
> [php]
> return array(
>     ......
>     'import'=>array(
>         'application.models.*',
>         'application.components.*',
>     ),
>     ......
> );
> ~~~
>
> 上面的配置说明，位于 `/wwwroot/blog/protected/models` 或 `/wwwroot/blog/protected/components` 目录中的任何类将在第一次使用时被自动包含。

`UserIdentity` 类主要用于 `LoginForm` 类中，它基于用户名和从登录页中收到的密码来实现用户验证。下面的代码展示了 `UserIdentity` 的使用：

~~~
[php]
$identity=new UserIdentity($username,$password);
$identity->authenticate();
switch($identity->errorCode)
{
	case UserIdentity::ERROR_NONE:
		Yii::app()->user->login($identity);
		break;
	......
}
~~~

> Info|信息: 人们经常对 identity 和 `user` 应用组件感到困惑，前者代表的是一种验证方法，后者代表当前用户相关的信息。一个应用只能有一个 `user` 组件，但它可以有一个或多个 identity 类，这取决于它支持什么样的验证方法。一旦验证通过，identity 实例会把它自己的状态信息传递给 `user` 组件，这样它们就可以通过 `user` 实现全局可访问。

要测试修改过的 `UserIdentity` 类，我们可以浏览 URL `http://www.example.com/blog/index.php` ，然后尝试使用存储在 `User` 表中的用户名和密码登录。如果我们使用了 [博客演示](http://www.yiiframework.com/demos/blog/) 中的数据，我们应该可以通过用户名 `demo` 和密码 `demo` 登录。注意，此博客系统没有提供用户管理功能。因此，用户无法修改自己的信息或通过Web界面创建一个新的帐号。用户管理功能可以考虑作为以后对此博客应用的增强。

<div class="revision">$Id: prototype.auth.txt 2333 2010-08-24 21:11:55Z mdomba $</div>