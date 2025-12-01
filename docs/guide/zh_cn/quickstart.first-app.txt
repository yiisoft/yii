建立第一个 Yii 应用
===================

为了对 Yii 有个初步认识，我们在本节讲述如何建立第一个 Yii 应用。我们将使用 `yiic` （命令行工具）创建一个新的 Yii 应用。`Gii`（强大的基于web的代码生成器）为特定的任务完成自动代码生成。假定 `YiiRoot`  为 Yii 的安装目录，`WebRoot` 是服务器的文档根目录。

在命令行运行 `yiic`，如下所示：

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|注意: 在 MacOS、Linux 或 Unix 系统中运行 `yiic` 时，你可能需要修改 `yiic` 文件的权限使它能够运行。此外，也可以这样运行此工具：

~~~
% cd WebRoot
% php YiiRoot/framework/yiic.php webapp testdrive
~~~

这将在 `WebRoot/testdrive` 目录下建立一个最基本的 Yii 应用。这个应用拥有了大多数 Yii 应用所需要的目录结构。

不用写一行代码，我们可以在浏览器中访问如下 URL 来看看我们第一个 Yii 应用：

~~~
http://hostname/testdrive/index.php
~~~

正如我们看到的，这个应用包含三个页面：首页、联系页、登录页。首页展示一些关于应用和用户登录状态的信息，联系页显示一个联系表单以便用户填写并提交他们的咨询，登录页允许用户先通过认证然后访问已授权的内容。
查看下列截图了解更多：

![首页](first-app1.png)

![联系页](first-app2.png)

![输入错误的联系页](first-app3.png)

![提交成功的联系页](first-app4.png)

![登录页](first-app5.png)


下面的树图描述了我们这个应用的目录结构。请查看[约定](/doc/guide/basics.convention#directory)以获取该结构的详细解释。

~~~
testdrive/
   index.php                 Web 应用入口脚本文件
   index-test.php            功能测试使用的入口脚本文件
   assets/                   包含公开的资源文件
   css/                      包含 CSS 文件
   images/                   包含图片文件
   themes/                   包含应用主题
   protected/                包含受保护的应用文件
      yiic                   yiic 命令行脚本
      yiic.bat               Windows 下的 yiic 命令行脚本
      yiic.php               yiic 命令行 PHP 脚本
      commands/              包含自定义的 'yiic' 命令
         shell/              包含自定义的 'yiic shell' 命令
      components/            包含可重用的用户组件
         Controller.php      所有控制器类的基础类
         Identity.php        用来认证的 'Identity' 类
      config/                包含配置文件
         console.php         控制台应用配置
         main.php            Web 应用配置
         test.php            功能测试使用的配置
      controllers/           包含控制器的类文件
         SiteController.php  默认控制器的类文件
      data/                  包含示例数据库
         schema.mysql.sql    示例 MySQL 数据库
         schema.sqlite.sql   示例 SQLite 数据库
         testdrive.db        示例 SQLite 数据库文件
      extensions/            包含第三方扩展
      messages/              包含翻译过的消息
      models/                包含模型的类文件
         LoginForm.php       'login' 动作的表单模型
         ContactForm.php     'contact' 动作的表单模型
      runtime/               包含临时生成的文件
      tests/                 包含测试脚本
      views/                 包含控制器的视图和布局文件
         layouts/            包含布局视图文件
            main.php         所有视图的默认布局
            column1.php      使用单列页面使用的布局
            column2.php      使用双列的页面使用的布局
         site/               包含 'site' 控制器的视图文件
            pages/           包含 "静态" 页面
               about.php     "about" 页面的视图
            contact.php      'contact' 动作的视图
            error.php        'error' 动作的视图(显示外部错误)
            index.php        'index' 动作的视图
            login.php        'login' 动作的视图
         system/             包含系统视图文件
~~~

连接到数据库
------------

大多数 Web 应用由数据库驱动，我们的测试应用也不例外。要使用数据库，我们首先需要告诉应用如何连接它。修改应用的配置文件 `WebRoot/testdrive/protected/config/main.php` 即可，如下所示：

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/source.db',
		),
	),
	......
);
~~~

上面的代码告诉 Yii 应用在需要时将连接到 SQLite 数据库 `WebRoot/testdrive/protected/data/testdrive.db` 。注意这个SQLite 数据库已经包含在我们创建的应用框架中。数据库只包含一个名为 `tbl_user` 的表：

~~~
[sql]
CREATE TABLE tbl_user (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(128) NOT NULL,
    password VARCHAR(128) NOT NULL,
    email VARCHAR(128) NOT NULL
);
~~~

若你想要换成一个 MySQL 数据库，你需要导入文件 `WebRoot/testdrive/protected/data/schema.mysql.sql` 来建立数据库。

> Note|注意: 要使用 Yii 的数据库功能，我们需要启用 PHP 的 PDO 扩展和相应的驱动扩展。对于测试应用来说，我们需要启用 `php_pdo` 和 `php_pdo_sqlite` 扩展。

实现 CRUD 操作
--------------

激动人心的时刻来了。我们想要为刚才建立的 `tbl_user` 表实现 CRUD (create, read, update 和 delete) 操作，这也是实际应用中最常见的操作。我们无需麻烦地编写实际代码，这里我们将使用 `Gii` —— 一个强大的基于Web 的代码生成器。

> Info|信息:Gii 自版本 1.1.2 可用。在此之前，可以使用 `yiic` 来实现相同的功能。更多细节，请参考 [用 yiic shell实现 CRUD 操作](/doc/guide/quickstart.first-app-yiic)。

### 配置Gii

为了使用 Gii，首先需要编辑文件 `WebRoot/testdrive/protected/main.php`，这是已知的 [应用配置](/doc/guide/basics.application#application-configuration) 文件：

~~~
[php]
return array(
	......
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'pick up a password here',
		),
	),
);
~~~

然后，访问 URL `http://hostname/testdrive/index.php?r=gii`。这里我们需要输入密码，它是在我们在上面的配置中指定的。


### 生成 User 模型

登陆后，点击链接 `Model Generator`。它将显示下面的模型生成页面，

![Model Generator](gii-model.png)

在 `Table Name` 输入框中，输入 `tbl_user`。在 `Model Class` 输入框中，输入 `User`。然后点击 `Preview` 按钮。这里将展示将要生成的新文件。现在点击 `Generate` 按钮。一个名为 `User.php`  将生成到 `protected/models` 目录中。如我们稍后描述的， `User` 模型类允许我们以面向对象的方式来访问数据表 `tbl_user` 。

### 生成 CRUD 代码

在创建模型类之后，我们将生成执行 CRUD 操作的代码。我们选择 Gii 中的 `Crud Generator`，如下所示，

![CRUD Generator](gii-crud.png)

在 `Model Class` 输入框中，输入 `User`。在 `Controller ID` 输入框中，输入 `user` (小写格式)。现在点击 `Generate` 按钮后的 `Preview` 按钮。CRUD 代码生成完成了。


### 访问 CRUD 页面

让我们看看成果，访问如下 URL：

~~~
http://hostname/testdrive/index.php?r=user
~~~

这会显示一个 `tbl_user`  表中记录的列表。

点击页面上的 `Create User` 链接，如果没有登录的话我们将被带到登录页。登录后，我们看到一个可供我们添加新用户的表单。完成表单并点击 `Create` 按钮，如果有任何输入错误的话，一个友好的错误提示将会显示并阻止我们保存。回到用户列表页，我们应该能看到刚才添加的用户显示在列表中。

重复上述步骤以添加更多用户。注意，如果一页显示的用户条目太多，列表页会自动分页。

如果我们使用 `admin/admin` 作为管理员登录，我们可以在如下 URL 查看用户管理页：

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

这会显示一个包含用户条目的漂亮表格。我们可以点击表头的单元格来对相应的列进行排序，而且它和列表页一样会自动分页。

实现所有这些功能不要我们编写一行代码！

![用户管理页](first-app6.png)

![新增用户页](first-app7.png)

<div class="revision">$Id: quickstart.first-app.txt 2375 2010-08-30 12:19:23Z mdomba $</div>