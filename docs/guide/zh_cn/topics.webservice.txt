Web Service
===========

[Web service](http://en.wikipedia.org/wiki/Web_service) 是一个软件系统，设计来支持计算机之间跨网络相互访问。在Web应用程序，它通常用一套API，可以被互联网访问和执行在远端系统主机上的被请求服务。系统主机所要求的服务。例如，以[Flex](http://www.adobe.com/products/flex/)为基础的客户端可能会援引函数实现在服务器端运行PHP的Web应用程序。 Web service依赖[SOAP](http://en.wikipedia.org/wiki/SOAP)作为通信协议栈的基础层。

Yii提供[CWebService]和[CWebServiceAction]简化了在Web应用程序实现Web service。这些API以类形式实现，被称为*service providers*. Yii将为每个类产生一个[WSDL](http://www.w3.org/TR/wsdl)，描述什么API有效和客户端怎么援引。当客户端援引API，Yii将实例化相应的service provider和调用被请求的API来完成请求。

>注:[CWebService] 依靠[PHP SOAP extension](http://www.php.net/manual/en/ref.soap.php) 。请确定您是否在试用本节中的例子前允许此扩展。

Defining Service Provider（定义Service Provider）
-------------------------

正如我们上文所述，service provider是一个类定义能被远程援引的方法。Yii依靠[doc
comment](http://java.sun.com/j2se/javadoc/writingdoccomments/) and [class
reflection](http://www.php.net/manual/en/language.oop5.reflection.php)识别
哪些方法可以被远程调用和他们的参数还有返回值。

让我们以一个简单的股票报价服务开始。这项服务允许客户端请求指定股票的报价。我们确定service provider如下。请注意，我们定义扩展[CController]的提供类`StockController`。这是不是必需的。马上我们将解释为什么这样做。

~~~
[php]
class StockController extends CController
{
	/**
	 * @param string the symbol of the stock
	 * @return float the stock price
	 * @soap
	 */
	public function getPrice($symbol)
	{
		$prices=array('IBM'=>100, 'GOOGLE'=>350);
		return isset($prices[$symbol])?$prices[$symbol]:0;
	    //...return stock price for $symbol
	}
}
~~~

在上面的，我们通过在文档注释中的`@soap`标签声明`getPrice`方法为一个Web service API。依靠文档注释指定输入的参数数据类型和返回值。其他的API可使用类似方式声明。

Declaring Web Service Action（定义Web Service动作）
----------------------------

已经定义了service provider，我们使他能够通过客户端访问。特别是，我们要创建一个控制器动作暴露这个服务。可以做到这一点很容易，在控制器类中定义一个[CWebServiceAction]动作。对于我们的例子中，我们把它放在`StockController`中。

~~~
[php]
class StockController extends CController
{
	public function actions()
	{
		return array(
			'quote'=>array(
				'class'=>'CWebServiceAction',
			),
		);
	}

	/**
	 * @param string the symbol of the stock
	 * @return float the stock price
	 * @soap
	 */
	public function getPrice($symbol)
	{
	    //...return stock price for $symbol
	}
}
~~~

这就是我们需要建立的Web service！如果我们尝试访问
动作网址`http://hostname/path/to/index.php?r=stock/quote` ，我们将
看到很多XML内容，这实际上是我们定义的Web service的WSDL描述。

> 提示：在默认情况下， [CWebServiceAction] 假设当前的控制器
是service provider。这就是因为我们定义 `getPrice`方法在`StockController`中。

Consuming Web Service（消费Web Service）
---------------------

要完成这个例子，让我们创建一个客户端来消费我们刚刚创建的Web service。例子中的客户端用php编写的，但可以用别的语言编写，例如`Java`, `C#`, `Flex`等等。

~~~
[php]
$client=new SoapClient('http://hostname/path/to/index.php?r=stock/quote');
echo $client->getPrice('GOOGLE');
~~~

在网页中或控制台模式运行以上脚本，我们将看到`GOOGLE`的价格`350` 。

Data Types（数据类型）
----------

当定义的方法和属性被远程访问，我们需要指定输入和输出参数的数据类型。以下的原始数据类型可以使用：

   - str/string: 对应 `xsd:string`;
   - int/integer: 对应 `xsd:int`;
   - float/double: 对应 `xsd:float`;
   - bool/boolean: 对应 `xsd:boolean`;
   - date: 对应 `xsd:date`;
   - time: 对应 `xsd:time`;
   - datetime: 对应 `xsd:dateTime`;
   - array: 对应 `xsd:string`;
   - object: 对应 `xsd:struct`;
   - mixed: 对应 `xsd:anyType`.

如果类型不属于上述任何原始类型，它被看作是复合型组成的属性。复合型类型被看做类，他的属性当做类的公有成员变量，在文档注释中被用`@soap`标记。

我们还可以使用数组类型通过附加`[]`在原始或
复合型类型的后面。这将定义指定类型的数组。

下面就是一个例子定义`getPosts`网页API，返回一个`Post`对象的数组。

~~~
[php]
class PostController extends CController
{
	/**
	 * @return Post[] a list of posts
	 * @soap
	 */
	public function getPosts()
	{
		return Post::model()->findAll();
	}
}

class Post extends CActiveRecord
{
	/**
	 * @var integer post ID
	 * @soap
	 */
	public $id;
	/**
	 * @var string post title
	 * @soap
	 */
	public $title;
}
~~~

Class Mapping（类映射）
-------------

为了从客户端得到复合型参数，应用程序需要定义从WSDL类型到相应PHP类的映射。这是通过配置[CWebServiceAction]的属性[classMap|CWebServiceAction::classMap]。

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'service'=>array(
				'class'=>'CWebServiceAction',
				'classMap'=>array(
					'Post'=>'Post',  // or simply 'Post'
				),
			),
		);
	}
	......
}
~~~

Intercepting Remote Method Invocation（拦截远程方法调用）
-------------------------------------
通过实现[IWebServiceProvider]接口，sevice provider可以拦截远程方法调用。在
[IWebServiceProvider::beforeWebMethod] ，sevice provider可以获得当前[CWebService]实例和通过[CWebService::methodName]请求的方法的名字 。它可以返回假如果远程方法出于某种原因不应被援引（例如：未经授权的访问） 。

<div class="revision">$Id: topics.webservice.txt 265 2008-11-23 12:30:16Z weizhuo  译：sharehua $</div>