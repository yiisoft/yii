Web Service
===========

[Web service](http://en.wikipedia.org/wiki/Web_service) is a software
system designed to support interoperable machine-to-machine interaction
over a network. In the context of Web applications, it usually refers to a
set of APIs that can be accessed over the Internet and executed on a remote
system hosting the requested service. For example, a
[Flex](http://www.adobe.com/products/flex/)-based client may invoke a
function implemented on the server side running a PHP-based Web
application. Web service relies on
[SOAP](http://en.wikipedia.org/wiki/SOAP) as its foundation layer of the
communication protocol stack.

Yii provides [CWebService] and [CWebServiceAction] to simplify the work of
implementing Web service in a Web application. The APIs are grouped into
classes, called *service providers*. Yii will generate for each
class a [WSDL](http://www.w3.org/TR/wsdl) specification which describes
what APIs are available and how they should be invoked by client. When an
API is invoked by a client, Yii will instantiate the corresponding service
provider and call the requested API to fulfill the request.

> Note: [CWebService] relies on the [PHP SOAP
extension](http://www.php.net/manual/en/ref.soap.php). Make sure you have
enabled it before trying the examples displayed in this section.

Defining Service Provider
-------------------------

As we mentioned above, a service provider is a class defining the methods
that can be remotely invoked. Yii relies on [doc
comment](http://java.sun.com/j2se/javadoc/writingdoccomments/) and [class
reflection](http://php.net/manual/en/book.reflection.php) to
identify which methods can be remotely invoked and what are their
parameters and return value.

Let's start with a simple stock quoting service. This service allows a
client to request for the quote of the specified stock. We define the
service provider as follows. Note that we define the provider class
`StockController` by extending [CController]. This is not required. We will
explain why we do so shortly.

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

In the above, we declare the method `getPrice` to be a Web service API by
marking it with the tag `@soap` in its doc comment. We rely on doc comment
to specify the data type of the input parameters and return value.
Additional APIs can be declared in the similar way.

Declaring Web Service Action
----------------------------

Having defined the service provider, we need to make it available to
clients. In particular, we want to create a controller action to expose the
service. This can be done easily by declaring a [CWebServiceAction] action
in a controller class. For our example, we will just put it in
`StockController`.

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

That is all we need to create a Web service! If we try to access the
action by URL `http://hostname/path/to/index.php?r=stock/quote`, we will
see a lot of XML content which is actually the WSDL for the Web service we
defined.

> Tip: By default, [CWebServiceAction] assumes the current controller is
the service provider. That is why we define the `getPrice` method inside
the `StockController` class.

Consuming Web Service
---------------------

To complete the example, let's create a client to consume the Web service
we just created. The example client is written in PHP, but it could be in
other languages, such as `Java`, `C#`, `Flex`, etc.

~~~
[php]
$client=new SoapClient('http://hostname/path/to/index.php?r=stock/quote');
echo $client->getPrice('GOOGLE');
~~~

Run the above script in either Web or console mode, and we shall see `350`
which is the price for `GOOGLE`.

Data Types
----------

When declaring class methods and properties to be remotely accessible, we
need to specify the data types of the input and output parameters. The
following primitive data types can be used:

   - str/string: maps to `xsd:string`;
   - int/integer: maps to `xsd:int`;
   - float/double: maps to `xsd:float`;
   - bool/boolean: maps to `xsd:boolean`;
   - date: maps to `xsd:date`;
   - time: maps to `xsd:time`;
   - datetime: maps to `xsd:dateTime`;
   - array: maps to `xsd:string`;
   - object: maps to `xsd:struct`;
   - mixed: maps to `xsd:anyType`.

If a type is not any of the above primitive types, it is considered as a
composite type consisting of properties. A composite type is represented in
terms of a class, and its properties are the class' public member variables
marked with `@soap` in their doc comments.

We can also use array type by appending `[]` to the end of a primitive or
composite type. This would specify an array of the specified type.

Below is an example defining the `getPosts` Web API which returns an array
of `Post` objects.

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

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
~~~

Class Mapping
-------------

In order to receive parameters of composite type from client, an
application needs to declare the mapping from WSDL types to the
corresponding PHP classes. This is done by configuring the
[classMap|CWebServiceAction::classMap] property of [CWebServiceAction].

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

Intercepting Remote Method Invocation
-------------------------------------

By implementing the [IWebServiceProvider] interface, a sevice provider can
intercept remote method invocations. In
[IWebServiceProvider::beforeWebMethod], the provider may retrieve the
current [CWebService] instance and obtain the the name of the method
currently being requested via [CWebService::methodName]. It can return
false if the remote method should not be invoked for some reason (e.g.
unauthorized access).

<div class="revision">$Id$</div>