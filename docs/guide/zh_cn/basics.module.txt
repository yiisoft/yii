模块
======

> Note|注意: 版本 1.0.3 起支持模块。

模块是一个独立的软件单元，它包含 [模型](/doc/guide/basics.model), [视图](/doc/guide/basics.view), [控制器](/doc/guide/basics.controller) 和其他支持的组件。
在许多方面上，模块看起来像一个 [应用](/doc/guide/basics.application)。主要的区别就是模块不能单独部署，它必须存在于一个应用里。
用户可以像他们访问普通应用的控制器那样访问模块中的控制器。

模块在一些场景里很有用。对大型应用来说，我们可能需要把它划分为几个模块，每个模块可以单独维护和部署。一些通用的功能，例如用户管理，
评论管理，可以以模块的形式开发，这样他们就可以容易地在以后的项目中被复用。


创建模块
---------------

模块组织在一个目录中，目录的名字即模块的唯一 [ID|CWebModule::id] 。
模块目录的结构跟  [应用基础目录](/doc/guide/basics.application#application-base-directory) 很相似。下面列出了一个  `fourm` 的模块的典型的目录结构：

~~~
forum/
   ForumModule.php            模块类文件
   components/                包含可复用的用户组件
      views/                  包含小物件的视图文件
   controllers/               包含控制器类文件
      DefaultController.php   默认的控制器类文件
   extensions/                包含第三方扩展
   models/                    包含模块类文件
   views/                     包含控制器视图和布局文件
      layouts/                包含布局文件
      default/                包含 DefaultController 的视图文件
         index.php            首页视图文件
~~~

模块必须有一个继承自 [CWebModule] 的模块类。类的名字通过表达式  `ucfirst($id).'Module'` 确定, 其中的 `$id` 代表模块的 ID (或者说模块的目录名字)。
模块类是存储模块代码间可共享信息的中心位置。例如，我们可以使用 [CWebModule::params] 存储模块参数，使用 [CWebModule::components] 分享模块级的  [应用组件](/doc/guide/basics.application#application-component) .

> Tip|提示: 我们可以使用Gii中的模块创建器创建新模块的基本骨架。


使用模块
------------

要使用模块，首先将模块目录放在 [应用基础目录](/doc/guide/basics.application#application-base-directory) 的 `modules` 中。
然后在应用的  [modules|CWebApplication::modules] 属性中声明模块 ID 。例如，为了使用上面的 `forum` 模块，
我们可以使用如下 [应用配置](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

模块也可以在配置时带有初始属性值。做法和配置 [应用组件](/doc/guide/basics.application#application-component) 很类似。
例如， `forum` 模块可以在其模块类中有一个名为 `postPerPage` 的属性，它可以在 [应用配置](/doc/guide/basics.application#application-configuration) 中配置如下:

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

模块的实例可通过当前活动控制器的 [module|CController::module] 属性访问。在模块实例中，我们可以访问在模块级中共享的信息。
例如，为访问上面的 `postPerPage` 信息，我们可使用如下表达式：

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// or the following if $this refers to the controller instance
// $postPerPage=$this->module->postPerPage;
~~~

模块中的控制器动作可以通过 [路由](/doc/guide/basics.controller#route) `moduleID/controllerID/actionID` 访问。
例如，假设上面的 `forum` 模块有一个名为 `PostController` 的控制器，我们就可以通过 [路由](/doc/guide/basics.controller#route) `forum/post/create` 访问此控制器中的 `create` 动作。
此路由对应的 URL  即 `http://www.example.com/index.php?r=forum/post/create`.

> Tip|提示: 如果一个控制器位于 `controllers` 目录的子目录中，我们仍然可以使用上述 [路由](/doc/guide/basics.controller#route) 格式。
例如，假设 `PostController` 位于 `forum/controllers/admin` 中，我们可以通过  `forum/admin/post/create` 访问 `create` 动作。


嵌套的模块
-------------

模块可以无限级嵌套。这就是说，一个模块可以包含另一个模块，而这另一个模块又可以包含其他模块。我们称前者为 *父模块* ，后者为 *子模块*. 
子模块必须定义在其父模块的  [modules|CWebModule::modules] 属性中，就像我们前面在应用配置中定义模块一样。

要访问子模块中的控制器动作，我们应使用路由  `parentModuleID/childModuleID/controllerID/actionID`.


<div class="revision">$Id: basics.module.txt 2363 2010-08-29 02:35:15Z qiang.xue $</div>