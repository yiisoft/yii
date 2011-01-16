国际化 (I18N)
====================

国际化 (译者注：即 Internationalization，因为这个单词 I 和 n 之间有18个字母，因此常缩写为I18N) 
是指设计一种应用软件的过程，这种软件无需做大的工程改变就能适应不同的语言和地区的需要。
对 Web 应用来说，国际化尤为重要，因为潜在的用户可能来自世界的各个角落。

Yii 在若干方面提供了对 I18N 的支持

   - 它为每种可能的语言和变量提供了本地化数据。
   - 它提了供信息和文件的翻译服务。
   - 它提供了基于本地化的日期和时间格式。
   - 它提供了基于本地话的数字格式。

在下面的小节中，我们将对以上几方面进行详细说明。

区域和语言
-------------------

区域是一系列参数，它定义了用户的语言、用户所在国家以及用户所有想要在他们的界面中看到的特殊参数。
它通常由一个包含了语言 ID 和区域 ID 的 ID 来识别。例如， ID `en_US` 表示英语区域和美国。
为保持一致性，Yii 中所有的区域 ID 被规范为小写的 `语言 ID` 或 `语言 ID_地区 ID`（例如 `en`, `en_us`）。

区域数据由一个  [CLocale] 实例表示。它提供了基于区域的信息，包括货币符号，数字符号，
日期和时间格式以及日期相关的名称。由于语言信息已经由区域 ID 实现，因此 [CLocale] 不再提供。
同理，我们通常会变换地使用词语“区域”和“语言”。

通过一个区域 ID，就可以通过 `CLocale::getInstance($localeID)` 或者 `CApplication::getLocale($localeID)` 
获取相应的  [CLocale] 实例。

> Info|信息: Yii 包含几乎所有语言和区域的区域化数据。
这些数据来自于 [Common Locale Data Repository](http://unicode.org/cldr/) (CLDR)。在每个区域中，
只提供了 CLDR 中的部分数据，因为原始的 CLDR 数据中包含了大量不太常用的信息。从版本 1.1.0 起，
用户也可以使用他们自定义的区域数据。只需要配置 [CApplication::localeDataPath] 属性为包含了自定义区域数据的目录即可。
请参考位于 `framework/i18n/data` 目录中的文件创建自定义的区域数据文件。

在一个 Yii 应用程序中，我们区分了它的 [目标语言（target
language）|CApplication::language] 和 [源语言（source
language）|CApplication::sourceLanguage]。目标语言是应用程序的目标用户的语言（区域），
而源语言是指写在应用程序源代码中的语言（区域）。国际化仅会在这两种语言不同的情况下发生。

你可以设定
[应用配置](/doc/guide/basics.application#application-configuration) 中的
[目标语言|CApplication::language] ，或者在发生国际化之前动态设定此参数。

> Tip|提示: 有时候，我们想要设置目标语言为用户所使用的语言（就是在用户的浏览器选项中指定的那个）。
只需使用  [CHttpRequest::preferredLanguage] 就可以获取到用户设定的语言。


翻译
-----------

在 I18N 中用到的最多的可能就是翻译了，包括 信息翻译 和 视图翻译。
前者将一条文本信息翻译为期望的语言，后者将整合文件翻译为期望的语言。

一个翻译请求包含要被翻译的对象，对象所用的源语言，和对象所需要翻译到的目标语言。
在 Yii 中，源语言默认为
[应用程序源语言|CApplication::sourceLanguage] 而目标语言默认为
[应用程序语言|CApplication::language]。
如果两者语言相同，翻译将不会发生。

### 信息翻译

信息翻译是通过调用 [Yii::t()|YiiBase::t] 实现的。此方法会将信息从 
[源语言|CApplication::sourceLanguage] 翻译为 [目标语言|CApplication::language]。

当翻译一条信息时，必须指定它的分类（category），因为一条信息在不同的分类或上下文中可能会有
不同的翻译。分类 `yii` 被保留为仅限 Yii 框架核心使用。

信息可以包含参数占位符，它们将会在调用 [Yii::t()|YiiBase::t] 时被实际的参数值取代。
例如，下面的信息翻译请求将会替换原始信息中的 `{alias}` 占位符为实际的别名（alias） 值。

~~~
[php]
Yii::t('app', 'Path alias "{alias}" is redefined.',
	array('{alias}'=>$alias))
~~~

> Note|注意: 要翻译的信息必须是常量字符串。它们不能包含可能会改变信息内容的变量
（例如`"Invalid {$message} content."`）。如果一条信息需要通过一些参数改变，请使用
参数占位符。

翻译过的信息会存储在一个叫做  *信息源（message source）* 的库中。 信息源是一个 
[CMessageSource] 或其子类的实例。当 [Yii::t()|YiiBase::t] 被调用时，
它将从信息源中查找相应的信息，如果找到了，就会返回翻译后的版本。

Yii 含有如下几种信息源。你也可以扩展
[CMessageSource] 创建自己的信息源类型。

   - [CPhpMessageSource]: 信息的翻译存储在一个 PHP 的 键值对 数组中。
原始信息为键，翻译后的信息为值。每个数组表示一个特定信息分类的翻译，分别存储在不同的 PHP 脚本文件中，文件名即分类名。
针对同一种语言的 PHP 翻译文件存储在同一个以区域 ID 命名的目录中。而所有的这些目录位于
[basePath|CPhpMessageSource::basePath] 指定的目录中。

   - [CGettextMessageSource]: 信息的翻译存储在 [GNU Gettext](http://www.gnu.org/software/gettext/) 文件中。

   - [CDbMessageSource]: 信息的翻译存储在数据库的表中。更多细节，请查看 [CDbMessageSource] 的 API 文档。

信息源是作为一个 [应用程序组件](/doc/guide/basics.application#application-component) 载入的。
Yii 预定义了一个名为 [messages|CApplication::messages] 的应用程序组件以存储用户程序中用到的信息。
默认情况下，此信息源的类型是  [CPhpMessageSource] ，而存储这些 PHP 翻译文件的目录是 `protected/messages`。

总体来说，要实现信息翻译，需要执行如下几步：

   1. 在合适的位置调用 [Yii::t()|YiiBase::t] ；

   2. 以 `protected/messages/LocaleID/CategoryName.php` 的格式创建 PHP 翻译文件。
每个文件简单的返回一个信息翻译数组。
注意，这是假设你使用默认的 [CPhpMessageSource] 存储翻译信息。

   3. 配置 [CApplication::sourceLanguage] 和 [CApplication::language]。

> Tip|提示: 使用 [CPhpMessageSource] 作为信息源时，Yii 中的  `yiic` 工具可用于管理信息翻译。
它的 `message` 命令可以自动从所选的源文件中提取要翻译的信息，并在需要时将其合并为现存的翻译。
关于使用 `message` 命令的更多信息，请执行 `yiic help message`。

从版本  1.0.10 起，当使用 [CPhpMessageSource] 管理信息源时，
扩展类（例如一个 widget 小物件，一个模块）中的信息可以以一种特殊的方式管理并使用。
具体来说，如果一条信息属于一个类名为 `Xyz` 的扩展，那么分类的名字可以以 `Xyz.categoryName` 的格式指定。
相应的信息文件就是 `BasePath/messages/LanguageID/categoryName.php` ，其中 `BasePath` 是指包含此扩展类文件的那个目录。
当使用 `Yii::t()` 翻译一条扩展信息时，需要使用如下格式：

~~~
[php]
Yii::t('Xyz.categoryName', '要翻译的信息');
~~~

从 1.0.2 起，Yi 添加了对 [choice format|CChoiceFormat] 的支持。Choice format 
是指选择按照一个给定数字的值选择一条翻译。例如，在英语中，视不同的数量，单词
'book' 可以有一个单数形式或者一个复数形式。而在其他语言中，
这个词可能就没有不同的形式（例如汉语）或者有更复杂的复数规则（例如俄语）。
 Choice format 以一种简单而又高效的方式解决了这个问题。
 
要使用 choice format，翻译的信息必须包含一个由 `|` 分割的 “表达式-信息” 对序列。如下所示：

~~~
[php]
'expr1#message1|expr2#message2|expr3#message3'
~~~

其中 `exprN` 表示一个有效的 PHP 表达式，它会计算出一个布尔型的值，以确定相应的信息是否应该被返回。
只有第一个返回值为 true 的表达式对应的信息会被返回。
一个表达式可以包含一个特殊的变量 `n` （注意，它不是 `$n`），它带有通过第一个信息参数传递的数字的值。
例如，假设有如下一条翻译信息：

~~~
[php]
'n==1#one book|n>1#many books'
~~~

而我们在调用 [Yii::t()|YiiBase::t] 时在参数数组中传递了数字值 2 ，
我们就会得到 `many books` 作为最终的翻译信息。

作为一种简便写法，如果一个表达式是一个数字，它将被视为等同于
`n==Number`。因此，上面的翻译信息也可以写为如下格式：

~~~
[php]
'1#one book|n>1#many books'
~~~


### 文件翻译

文件翻译是通过调用
[CApplication::findLocalizedFile()] 完成的。
给定一个所要翻译的文件的路径，此方法就会在 `区域 ID` 子目录中查找相同文件名的文件。
如果找到了，就会返回此文件的路径；否则，将返回原始文件的路径。

文件翻译主要用于渲染一个视图。
当在控制器或小物件中调用任一渲染方法时，视图文件将会被自动翻译。例如，如果
[目标语言|CApplication::language] 是 `zh_cn` 而 [源语言|CApplication::sourceLanguage] 
是 `en_us`，渲染一个名为
`edit` 的视图时，程序将会查找
`protected/views/ControllerID/zh_cn/edit.php` 视图文件。
如果此文件找到，就会通过此翻译版本渲染。否则，就会使用文件
`protected/views/ControllerID/edit.php` 渲染。

文件翻译也可以用于其他目的，例如，显示一个翻译过的图片，或者加载一个基于区域的数据文件。

日期和时间格式化
------------------------

日期和时间在不同的国家和地区通常会有不同的格式。
日期和时间格式和的任务就是生成一个符合指定区域格式的日期或时间字符串。
为实现此目的，Yii 提供了[CDateFormatter]。

每个 [CDateFormatter] 实例关联到一个目标区域。要获取关联到整个应用程序的目标区域的格式器（formatter），
只需简单的访问 应用程序的 [dateFormatter|CApplication::dateFormatter] 属性。

[CDateFormatter] 类主要提供了两个方法以格式化 UNIX 时间戳。

   - [format|CDateFormatter::format]: 此方法可通过一个自定义的模式格式化给定的 UNIX 时间戳为一个字符串
（例如 `$dateFormatter->format('yyyy-MM-dd',$timestamp)`）。

   - [formatDateTime|CDateFormatter::formatDateTime]: 
此方法通过一个在目标区域数据中预定义的模式格式化给定的 UNIX 时间戳为一个字符串
（例如日期的 `short` 格式，时间的 `long` 格式）。

数字格式化
-----------------

与日期和时间类似，数字在不同的国家或地区之间也可能有不同的格式。
数字格式化包括十进制格式化，货币格式化和百分比格式化。Yii 提供了
[CNumberFormatter] 以完成这些任务。

要获取关联到整个应用程序的目标区域的格式器（formatter），
只需简单的访问 应用程序的 [numberFormatter|CApplication::numberFormatter] 属性。

[CNumberFormatter] 提供的如下方法可以用于格式化 integer 或 double 值

   - [format|CNumberFormatter::format]: 此方法通过一个自定义的模式格式化给定的数字为一个字符串
（例如 `$numberFormatter->format('#,##0.00',$number)`）。

   - [formatDecimal|CNumberFormatter::formatDecimal]: 此方法通过在目标区域数据中预定义的十进制模式格式化给定的数字。

   - [formatCurrency|CNumberFormatter::formatCurrency]: 此方法使用目标区域数据中预定义的货币模式格式化给定的数字。

   - [formatPercentage|CNumberFormatter::formatPercentage]: 此方法使用目标区域数据中预定义的百分比模式格式化给定的数字。

<div class="revision">$Id: topics.i18n.txt 2522 2010-09-30 21:13:25Z alexander.makarow, translated by riverlet. $</div>