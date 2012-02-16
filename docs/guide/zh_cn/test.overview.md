测试
=======

测试是软件开发中必不可少的环节.无论我们是否意识到,在开发Web应用的时候,我们始终都是在测试的.例如, 当我们用PHP写了一个类时, 我们可能会用到一些注入 `echo` 或者 `die` 语句来显示我们是否正确地实现了某个方法;当我们实现了包含一套复杂的HTML表单的web页面时, 我们可能会试着输入一些测试数据来确认页面是否是按照我们的预期来交互的.更高级的开发者则会写一些代码来自动完成这个测试过程, 这样一来每当我们需要测试一些东西的时候, 我们只需要调用代码, 剩下来的就交给计算机了. 这就是所谓的 *自动测试*, 也是本章的主要话题.

Yii 提供的测试支持包括 *单元测试* 和 *功能测试*.

单元测试检验了代码的一个独立单元是否按照预期工作. 在面向对象编程中, 最基本的代码单元就是类. 因此, 单元测试的主要职责就是校验这个类所实现的每个方法工作都是正常的. 单元测试通常是由开发了这个类的人来编写.

功能测试检验了特性是否按照预期工作(如:在一个博客系统里的提交操作).与单元测试相比, 功能测试通常要高级一些, 因为待测试的特性常常牵涉到多个类. 功能测试通常是由非常了解系统需求的人编写.(这个人既可以是开发者也可以是质量工程师).


测试驱动开发
-----------------------

以下展示的便是所谓的 [测试驱动开发 (TDD)](http://zh.wikipedia.org/wiki/测试驱动开发) 的开发周期:

 1. 创建一个涵盖要实现的特性的新的测试. 测试预计将在第一次执行的时候失败, 因为特性尚未实现.
 2. 执行所有测试,确保这个新的测试是失败的.
 3. 编写代码来使得测试通过.
 4. 执行所有测试,确保所有测试通过.
 5. 重构新编写的代码并确保这些测试仍然能够通过.

重复步骤1至5推进整体功能的实现.


构建测试环境
----------------------

Yii 提供的测试支持需要 [PHPUnit](http://www.phpunit.de/) 3.5+ 和 [Selenium Remote Control](http://seleniumhq.org/projects/remote-control/) 1.0+.请参照他们提供的文档来安装 PHPUnit 和 Selenium Remote Control.

当我们使用 `yiic webapp` 控制台命令来创建一个新的 Yii 应用时, 它将会生成以下文件和目录供我们来编写和完成测试.

~~~
testdrive/
   protected/                包含了受保护的应用文件
      tests/                 包含了应用测试
         fixtures/           包含了数据 fixtures
         functional/         包含了功能测试
         unit/               包含了单元测试
         report/             包含了 coverage 报告
         bootstrap.php       这个脚本在一开始执行
         phpunit.xml         PHPUnit 配置文件
         WebTestCase.php     基于 Web 的功能测试基类
~~~

如上所示的, 我们的测试代码主要放在 `fixtures`, `functional` 和 `unit` 这三个目录下, `report` 目录则用于存储生成的代码 coverage 报告.

我们可以在控制台窗口执行以下命令来执行测试(无论是单元测试还是功能测试):

~~~
% cd testdrive/protected/tests
% phpunit functional/PostTest.php    // 执行单个测试
% phpunit --verbose functional       // 执行 'functional' 下的所有测试
% phpunit --coverage-html ./report unit
~~~

上面的最后一条命令将执行 `unit` 目录下的所有测试然后在 `report` 目录下生成出一份 code-coverage 报告. 注意要生成 code-coverage 报告必须安装并开启PHP的 [xdebug 扩展](http://www.xdebug.org/) .


测试的引导脚本
--------------------

让我们来看看 `bootstrap.php` 文件里会有些什么. 首先这个文件有点特殊,因为它看起来很像是 [入口脚本](/doc/guide/basics.entry), 而它也正是我们执行一系列测试的入口. 

~~~
[php]
$yiit='path/to/yii/framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';
require_once($yiit);
require_once(dirname(__FILE__).'/WebTestCase.php');
Yii::createWebApplication($config);
~~~

如上所示, 首先我们包含了来自 Yii 框架的 `yiit.php` 文件, 它初始化了一些全局常量以及必要的测试基类.然后我们使用 `test.php` 这个配置文件来创建一个应用实例.如果你查看 `test.php` 文件, 你会发现它是继承自 `main.php` 这个配置文件的, 只不过它多加了一个类名为 [CDbFixtureManager] 的 `fixture` 应用组件.我们将在下一节中详细的介绍 fixtures.

~~~
[php]
return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'components'=>array(
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			/* 去除以下注释可为测试提供一个数据库连接.
			'db'=>array(
				'connectionString'=>'DSN for test database',
			),
			*/
		),
	)
);
~~~

当我执行那些涉及到数据库操作的测试时, 我们应该提供一个测试专用的数据库以便测试执行不会干扰到正常的开发或者生产活动. 这样一来, 我们纸需要去除上面 `db` 配置的注释, 然后填写 `connectionString` 属性的用以连接到数据库的DSN(数据源名称)即可.

通过这样一个启动脚本, 当我们执行单元测试时, 我们便可以获得一个与服务需求类似的应用实例, 而主要的不同就是测试拥有一个 fixture 管理器以及它专属的测试数据库.


<div class="revision">$Id: test.overview.txt 2997 2011-02-23 13:51:40Z alexander.makarow, Translated By xwsoul$</div>
