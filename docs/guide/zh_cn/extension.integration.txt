Using 3rd-Party Libraries(使用第三方库)
=========================

Yii是精心设计，使第三方库可易于集成，进一步扩大Yii的功能。 当在一个项目中使用第三方库，程序员往往遇到关于类命名和文件包含的问题。 因为所有Yii类以`C`字母开头，这就减少可能会出现的类命名问题;而且因为Yii依赖[SPL autoload](http://us3.php.net/manual/en/function.spl-autoload.php)执行类文件包含，如果他们使用相同的自动加载功能或PHP包含路径包含类文件，它可以很好地结合。

下面我们用一个例子来说明如何在一个Yii application从[Zend framework](http://www.zendframework.com)使用[Zend_Search_Lucene](http://www.zendframework.com/manual/en/zend.search.lucene.html)部件。

首先，假设`protected`是[application base directory](/doc/guide/basics.application#application-base-directory)，我们提取Zend Framework的发布文件到`protected/vendors`目录 。 确认`protected/vendors/Zend/Search/Lucene.php`文件存在。

第二，在一个controller类文件的开始，加入以下行：

~~~
[php]
Yii::import('application.vendors.*');
require_once('Zend/Search/Lucene.php');
~~~

上述代码包含类文件`Lucene.php`。因为我们使用的是相对路径，我们需要改变PHP的包含路径，以使文件可以正确定位。这是通过在`require_once`之前调用`Yii::import`做到。 

一旦上述设立准备就绪后，我们可以在controller action里使用`Lucene`类，类似如下：

~~~
[php]
$lucene=new Zend_Search_Lucene($pathOfIndex);
$hits=$lucene->find(strtolower($keyword));
~~~


<div class="revision">$Id: extension.integration.txt 251 2008-11-19 22:28:46Z qiang.xue 译：sharehua $</div>