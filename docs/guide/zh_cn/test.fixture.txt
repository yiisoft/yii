定义特定状态(Fixtures)
=================

自动测试需要被执行很多次.为了确保测试过程是可以重复的, 我们很想要在一些可知的状态下进行测试, 这个状态我们称之为 *特定状态*. 举个例子,在一个博客应用中测试文章创建特性, 每次当我们进行测试时, 与文章相关的表(例如. `Post` 表 , `Comment` 表)应该被恢复到一个特定的状态下. [PHPUnit 文档](http://www.phpunit.de/manual/current/en/fixtures.html) 已经很好的描述了一般的特定状态的构建. 而本节主要介绍怎样像刚才描述的例子那样构建数据库特定状态.

设置构建数据库的特定状态,这恐怕是测试以数据库为后端支持的应用最耗时的部分之一.Yii 引进的 [CBbFixtureManager] 应用组件可以有效的减轻这一问题.当进行一组测试的时候,它基本上会做以下这些事情:

 * 在所有测试运行之前,它重置测试相关数据为可知的状态.
 * 在单个测试运行之前, 它将特定的表重置为可知状态.
 * 在一个测试方法执行过程中, 它提供了供给特定状态的行数据的访问接口.

请按如下使用我们在 [应用配置](/doc/guide/basics.application#application-configuration) 中配置的 [CDbFixtureManager].

~~~
[php]
return array(
	'components'=>array(
		'fixture'=>array(
			'class'=>'system.test.CDbFixtureManager',
		),
	),
);
~~~

然后我们在目录 `protected/tests/fixtures`下提供一个特定状态数据. 这个目录可以通过配置应用配置文件中的 [CDbFixtureManager::basePath] 属性指定为其他目录.特定状态数据是由多个称之为特定状态文件的PHP文件组合而成.每个特定状态文件返回一个数组, 代表数据的一个特定表的初始行.文件名和表名相同.以下则是将 `Post` 表的特定状态数据存储于名为 `Post.php` 文件里的例子.

~~~
[php]
<?php
return array(
	'sample1'=>array(
		'title'=>'test post 1',
		'content'=>'test post content 1',
		'createTime'=>1230952187,
		'authorId'=>1,
	),
	'sample2'=>array(
		'title'=>'test post 2',
		'content'=>'test post content 2',
		'createTime'=>1230952287,
		'authorId'=>1,
	),
);
~~~

正如我们所见, 上面返回了两行数据. 每一行都表示一个数组,其键是表的字段名,其值则是对应的字段值.每行的索引都是称之为*行别名*的字符串(例如: `simple1`, `simple2`). 稍后当我们编写测试脚本的时候, 我们可以方便地通过它的别名调用这行数据.我们将在下节中详细的介绍这个.

你也许注意到了我们并未在上述特定状态中指定 `id` 字段的值. 这是因为 `id` 字段已经被定义为自增主键了,它的值也会在我们插入新数据的时候自动生成.

当 [CDbFixtureManager] 第一次被引用时, 它会仔细检查所有的特定状态文件然后使用他们重置对应的表.它通过清空表,重置表主键的自增序列值,然后插入来自特定状态文件的数据行到表中来重置表.

有时候,我们可能不想在一套测试前重置特定状态文件里描述的每一个表, 因为重置太多的特定状态文件可能需要很多时间.这种情况下,我们可以写一个PHP脚本来定制这个初始化过程.这个脚本应该被保存在存放特定状态文件的目录下,并命名为 `init.php`.当 [CDbFixtureManager] 检测到了这个脚本的存在, 它将执行这个脚本而不是重置每一个表.

不喜欢使用默认方式来重置表也是可以的,例如: 清空表然后插入特定状态数据. 如果是这种情况, 我们可以为指定的特定状态文件编写一个初始化脚本.这个脚本必须名称为表名+`.init.php`. 例如: `Post` 表的初始化脚本文件就是 `Post.init.php`. 当 [CDbFixtureManager] 发现了这个脚本,它将执行这个脚本而不是采用默认的方式去重置该表.

> Tip: 太多的特定状态文件大大延长了测试时间.因此, 你应该只为那些在测试中数据会发生变化的表提供特定状态文件. 那些做为查找服务的表不会改变,因此不需要特定状态文件.

接下来两节, 我们将谈到如何在单元测试和功能测试中使用被 [CDbFixtureManager] 管理的特定状态.

<div class="revision">$Id: test.fixture.txt 3039 2011-03-09 19:48:15Z qiang.xue, translated by xwsoul $</div>
