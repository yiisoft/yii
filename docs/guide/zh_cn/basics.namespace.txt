路径别名与名字空间
========================

Yii 中广泛的使用了路径别名。路径别名关联于一个目录或文件的路径。它以点号语法指定，类似于广泛使用的名字空间（namespace）格式：

~~~
RootAlias.path.to.target
~~~

其中的 `RootAlias` 是某个现存目录的别名。

通过使用 [YiiBase::getPathOfAlias()]， 别名可以被翻译为其相应的路径。
例如， `system.web.CController` 会被翻译为 `yii/framework/web/CController`。

通过调用 [YiiBase::setPathOfAlias()]，我们可以定义新的根路径别名。


Root Alias
----------

为方便起见，Yii 预定义了以下几个根别名：

 - `system`: 表示 Yii 框架目录；
 - `zii`: 表示 [Zii 库](/doc/guide/extension.use#zii-extensions) 目录；
 - `application`: 表示应用的 [基础目录](/doc/guide/basics.application#application-base-directory)；
 - `webroot`: 表示 [入口脚本](/doc/guide/basics.entry) 文件所在的目录。此别名从版本 1.0.3 开始有效。
 - `ext`: 表示包含了所有第三方 [扩展](/doc/guide/extension.overview) 的目录。此别名从版本 1.0.8 开始有效。

额外的，如果应用使用了 [模块](/doc/guide/basics.module), （Yii） 也为每个模块ID定义了根别名，指向相应模块的跟目录。
此功能从版本 1.0.3 起有效。

通过使用 [YiiBase::getPathOfAlias()], 别名可以被翻译为其相应的路径。
例如， `system.web.CController` 会被翻译为 `yii/framework/web/CController`。

Importing Classes
-----------------

使用别名可以很方便的导入类的定义。
例如，如果我们想包含 [CController] 类的定义，我们可以调用如下代码

~~~
[php]
Yii::import('system.web.CController');
~~~

[import|YiiBase::import] 方法跟  `include` 和 `require` 不同，它更加高效。
导入（import）的类定义并不会真正被包含进来，直到它第一次被引用。
多次导入同样的名字空间也会比  `include_once` 和 `require_once` 快得多。

> Tip|提示: 当引用 Yii 框架定义的类时，我们不需要导入或包含它。所有的核心 Yii
类都已被提前导入了。

###使用Class Map

从1.1.5版本开始，Yii允许用户定义的类通过使Class Map机制来预先导入，这也是Yii内置类使用的方法。
预先引入机制可以在Yii应用的任何地方使用，无需显式地导入或者包含文件。这个特性对于一个建立在Yii基础上的框架或者类库来说很有用。

若要使用预导入功能，要在[CWebApplication::run()]执行前执行下面的代码：

~~~
[php]
Yii::$classMap=array(
	'ClassName1' => 'path/to/ClassName1.php',
	'ClassName2' => 'path/to/ClassName2.php',
	......
);
~~~

导入目录
---------------------

我们还可以使用如下语法导入整个目录，这样此目录下的类文件就会在需要时被自动包含。

~~~
[php]
Yii::import('system.web.*');
~~~

除 [import|YiiBase::import] 外， 别名还在其他许多地方指向类。
例如，路径别名可以传递给 [Yii::createComponent()] 以创建相应类的实例。
即使类文件在之前从未被包含。

Namespace
---------

不要将路径别名和名字空间混淆了，名字空间是指对一些类名的一个逻辑组合，这样它们就可以相互区分开，即使有相同的名字。
而路径别名是用于指向一个类文件或目录。路径别名与名字空间并不冲突。

> Tip|提示: 由于 5.3.0 版本之前的 PHP 本质上不支持名字空间，你无法创建两个具有相同名字但不同定义的类的实例。
鉴于此，所有的 Yii 框架类都以字母 'C'（意为 'Class'） 作前缀，这样它们可以区分于用户定义的类。我们建议前缀 'C'
只保留给 Yii 框架使用，用户定义的类则使用其他的字母作前缀。

使用命名空间的类
------------------

使用命名空间的类是指一个在非全局命名空间下声明的类。比如说，类`application\components\GoogleMap`
在命名空间`application\components`下的类。使用命名空间需要 PHP 5.3.0 或者以上版本。

从1.1.5开始，可以无需显式引入而使用一个包含命名空间的类。比如说，我们可以创建一个`application\components\GoogleMap`
的实例而无需去处理引入的路径，这样就增强了Yii的自动导入机制。

若要自动导入使用命名空间的类，命名空间的格式必须和路径别名相似。比如说，类`application\components\GoogleMap`
所对应的路径必须和别名`application.components.GoogleMap`一致。

<div class="revision">$Id$</div>