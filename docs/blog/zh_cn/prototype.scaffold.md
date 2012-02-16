脚手架
===========

创建，读取，更新，删除 (CRUD) 是应用的数据对象中的四个基本操作。由于在Web应用的开发中实现CURD的任务非常常见，Yii 为我们提供了一些可以使这些过程自动化的代码生成工具，名为 *Gii* （也被称为 *脚手架*） 。

> Note|注意: Gii 从 Yii 1.1.2 版开始提供。在这之前，你可能需要使用 [yiic shell tool](http://www.yiiframework.com/doc/guide/quickstart.first-app-yiic) 来实现相同的任务。

下面，我们将阐述如何使用这个工具来实现博客应用中的CRUD操作。

安装 Gii
--------------

首先我们需要安装 Gii. 打开文件 `/wwwroot/blog/protected/config/main.php` ，添加如下代码:

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
			'password'=>'这儿设置一个密码',
		),
	),
);
~~~

上面的代码安装了一个名为 `gii` 的模块，这样我们就可以通过在浏览器中浏览如下URL来访问 Gii 模块:

~~~
http://www.example.com/blog/index.php?r=gii
~~~

我们将被提示要求输入一个密码。输入我们前面在 `/wwwroot/blog/protected/config/main.php` 中设置的密码，我们将看到一个页面，它列出了所有可用的代码生成工具。

> Note|注意: 上述代码在生产环境中应当移除。代码生成工具只应当用于开发环境。


创建模型
---------------

首先我们需要为每个数据表创建一个[模型（Model）](http://www.yiiframework.com/doc/guide/basics.model) 类。模型类会使我们可以通过一种直观的、面向对象的风格访问数据库。稍后我们将会看到这一点。

点击 `Model Generator` 链接开始使用模型创建工具。

在 `Model Generator` 页中，在`Table Name`一栏输入 `tbl_user` (用户表的名字)，然后按下 `Preview` 按钮。一个预览表将显示在我们面前。我们可以点击表格中的链接来预览要生成的代码。如果一切OK，我们可以按下 `Generate` 按钮来生成代码并将其保存在一个文件中。

> Info|信息: 由于代码生成器需要保存生成的代码到文件，它要求Web服务器进程必须拥有对相应文件的创建和修改权限。为简单起见，我们可以赋予Web服务器进程对整个 `/wwwroot/blog` 目录的写权限。注意这只在开发机器上使用 `Gii` 时会用到。

对剩余的其他表重复同样的步骤，包括 `tbl_post`, `tbl_comment`, `tbl_tag` 和 `tbl_lookup`。

> Tip|提示: 我们还可以在 `Table Name` 栏中输入一个星号 '\*' 。这样就可以通过一次点击就对 *所有的* 数据表生成相应的模型类。

通过这一步，我们就有了如下新创建的文件：

 * `models/User.php` 包含了继承自 [CActiveRecord] 的 `User` 类，可用于访问 `tbl_user` 数据表；
 * `models/Post.php` 包含了继承自 [CActiveRecord] 的  `Post` 类，可用于访问 `tbl_post` 数据表；
 * `models/Tag.php` 包含了继承自 [CActiveRecord] 的  `Tag` 类，可用于访问 `tbl_tag` 数据表；
 * `models/Comment.php` 包含了继承自 [CActiveRecord] 的  `Comment` 类，可用于访问 `tbl_comment` 数据表；
 * `models/Lookup.php` 包含了继承自 [CActiveRecord] 的  `Lookup` 类，可用于访问 `tbl_lookup` 数据表；


实现 CRUD 操作
----------------------------

模型类建好之后，我们就可以使用 `Crud Generator` 来创建为这些模型实现CRUD操作的代码了。我们将对 `Post` 和 `Comment` 模型执行此操作。

在 `Crud Generator` 页面中，`Model Class` 一栏输入 `Post` (就是我们刚创建的 Post 模型的名字) ，然后按下 `Preview` 按钮。我们会看到有很多文件将被创建。按下 `Generate` 按钮来创建它们。

对 `Comment` 模型重复同样的步骤。

让我们看一下通过CRUD生成器生成的这些文件。所有的文件都创建在了 `/wwwroot/blog/protected` 目录中。为方便起见，我们把它们分组为 [控制器（Controller）](http://www.yiiframework.com/doc/guide/basics.controller) 文件和 [视图（View）](http://www.yiiframework.com/doc/guide/basics.view) 文件：

 - 控制器文件：
	 * `controllers/PostController.php` 包含负责所有CRUD操作的 `PostController` 控制器类；
	 * `controllers/CommentController.php` 包含负责所有CRUD操作的 `CommentController` 控制器类；

 - 视图文件：
	 * `views/post/create.php` 一个视图文件，用于显示创建新日志的 HTML 表单；
	 * `views/post/update.php` 一个视图文件，用于显示更新日志的 HTML 表单；
	 * `views/post/view.php` 一个视图文件，用于显示一篇日志的详情；
	 * `views/post/index.php` 一个视图文件，用于显示日志列表；
	 * `views/post/admin.php` 一个视图文件，用于在一个带有管理员命令的表格中显示日志；
	 * `views/post/_form.php` 一个插入 `views/post/create.php` 和 `views/post/update.php` 的局部视图文件。它显示用于收集日志信息的HTML表单；
	 * `views/post/_view.php` 一个在 `views/post/index.php` 中使用的局部视图文件。它显示单篇日志的摘要信息；
	 * `views/post/_search.php` 一个在 `views/post/admin.php` 中使用的局部视图文件。它显示一个搜索表单；
	 * 还有为评论创建的一系列相似的文件。


测试
-------

我们可以通过访问如下网址测试我们刚生成的代码所实现的功能：

~~~
http://www.example.com/blog/index.php?r=post
http://www.example.com/blog/index.php?r=comment
~~~

注意，由代码生成器实现的日志和评论功能是完全相互独立的。并且，当创建一个新的日志或评论时，我们必须输入如 `author_id` 和 `create_time` 这些信息，而在现实应用中这些应当由程序自动设置。别担心。我们将在下一个阶段中修正这些问题。现在呢，这个模型已经包含了大多数我们需要在博客应用中实现的功能，我们应该对此感到满意了 ^_^。


为了更好地理解这些文件是如何使用的，我们在下面列出了当显示一个日志列表时发生的工作流程。

 0. 用户请求访问这个 URL `http://www.example.com/blog/index.php?r=post`;
 1. [入口脚本](http://www.yiiframework.com/doc/guide/basics.entry) 被Web服务器执行，它创建并实例化了一个 [应用](http://www.yiiframework.com/doc/guide/basics.application) 实例来处理此请求；
 2. 应用创建并执行了 `PostController` 实例；
 3. `PostController` 实例通过调用它的  `actionIndex()` 方法执行了 `index` 动作。注意，如果用户没有在URL中指定执行一个动作，则 `index` 就是默认的动作；
 4. `actionIndex()` 方法查询数据库，带回最新的日志列表；
 5. `actionIndex()` 方法使用日志数据渲染 `index` 视图。


<div class="revision">$Id: prototype.scaffold.txt 2258 2010-07-12 14:13:50Z alexander.makarow $</div>