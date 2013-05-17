Web Service
===========

[Webbtjänster](http://en.wikipedia.org/wiki/Web_service) (Web Service) är ett 
mjukvarusystem konstruerat för att ge stöd för driftkompatibel (interoperable)
maskin-till-maskin-interaktion över ett nätverk. I en webbapplikations kontext 
refererar det vanligen till en uppsättning API:er som kan kommas åt över internet 
och exekveras på ett fjärrsystem som erbjuder den begärda tjänsten. Till exempel, 
en [Flex](http://www.adobe.com/products/flex/)-baserad klient kan anropa en 
funktion implementerad på serversidan av en PHP-based webbapplikation. 
Webbtjänster förlitar sig på [SOAP](http://en.wikipedia.org/wiki/SOAP) som sitt 
grundläggande lager i kommunikationsprotokollstacken.

Yii tillhandahåller [CWebService] och [CWebServiceAction] för att förenkla 
arbetet med implementering av webbtjänster i en webbapplikation. API:erna 
grupperas klasser, kallade *service providers*. Yii genererar för varje klass en 
[WSDL](http://www.w3.org/TR/wsdl)-specifikation som beskriver vilka API:er som 
finns tillgängliga och hur de skall anropas av klienterna. När en webbtjänst-API 
invokeras av en klient, kommer Yii att instantiera motsvarande service provider 
och anropa den begärda API:n för att fullgöra önskad request.

> Note|Märk: [CWebService] är beroende av  [PHP:s SOAP-
tillägg](http://www.php.net/manual/en/ref.soap.php). Kontrollera att det är 
igång innan exemplen i detta avsnitt provkörs.

Definiera Service Provider
-------------------------

Som nämnts ovan, en service provider är en klass som definierar metoderna som 
kan fjärrinvokeras. Yii förlitar sig på  [doc 
comment](http://java.sun.com/j2se/javadoc/writingdoccomments/) och [class 
reflection](http://php.net/manual/en/book.reflection.php) för att 
identifiera vilka metoder som kan fjärrinvokeras och vilka deras parametrar och 
returvärden är.

Låt oss börja med en enkel aktiekurstjänst. Denna tjänst medger att en klient 
frågar om priset på en viss aktie. Service providern definieras som följer. Lägg 
märke till att vi definierar providerklassen `StockController` genom att ärva 
från och utvidga [CController]. Detta är inte ett krav. Varför vi gör så 
förklaras inom kort.

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

I ovanstående deklarerar vi metoden `getPrice` att vara en webbtjänst-API genom 
att markera den med taggen `@soap` i dess doc comment. Vi förlitar oss på doc 
comment för att ange datatyp för inparametrar samt returvärde. Ytterligare 
API:er kan deklareras på liknande sätt.

Deklarera Web Service-åtgärd
----------------------------

Med en service provider definierad, behöver vi göra den tillgänglig för 
klienter. Mer specifikt, vi vill skapa en kontrolleråtgärd för att exponera 
tjänsten. Detta kan enkelt låta sig göras genom att deklarera en 
[CWebServiceAction]-åtgärd i en kontrollerklass. I vårt exempel placerar vi den 
helt enkelt i `StockController`.

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

Detta är allt som behövs för att skapa en webbtjänst! Om vi försöker nå åtgärden 
via URL:en `http://hostname/path/to/index.php?r=stock/quote`, kommer vi att se 
en hel del XML-innehåll vilket faktiskt är WSDL-beskrivningen för webbtjänsten 
vi definierat.

> Tip|Tips: Som standard förmodar [CWebServiceAction] att den nuvarande 
kontrollern är service provider. Detta är anledningen till att vi deklarerar 
metoden `getPrice` i klassen `StockController`.

Konsumera Web Service
---------------------

För att slutföra exemplet, låt oss skapa en klient som skall konsumera 
webbtjänsten vi just skapat. Exempelklienten är skriven i PHP, men den kunde 
vara i andra språk, såsom `Java`, `C#`, `Flex`, etc.

~~~
[php]
$client=new SoapClient('http://hostname/path/to/index.php?r=stock/quote');
echo $client->getPrice('GOOGLE');
~~~

Kör ovanstående skript i antingen webb- eller konsolläge, och vi skall se t ex 
`350` som är priset för `GOOGLE`.

Datatyper
---------

När klassmetoder och propertyn deklareras för fjärråtkomst behöver vi 
specificera datatyper för in- och utparametrarna. Följande primitiva datatyper 
kan användas:

   - str/string: mappas till `xsd:string`;
   - int/integer: mappas till `xsd:int`;
   - float/double: mappas till `xsd:float`;
   - bool/boolean: mappas till `xsd:boolean`;
   - date: mappas till `xsd:date`;
   - time: mappas till `xsd:time`;
   - datetime: mappas till `xsd:dateTime`;
   - array: mappas till `xsd:string`;
   - object: mappas till `xsd:struct`;
   - mixed: mappas till `xsd:anyType`.

Om en typ inte motsvaras av någon av ovanstående primitiva typer, betraktas den 
som en sammansatt typ bestående av propertyn. En sammansatt typ representeras i 
termer av en klass,och dess propertyn är klassens publika medlemsvariabler 
markerade med `@soap` i sina doc comments.

Vi kan även använda arraytyper genom att lägga till `[]` i slutet av en primitiv 
eller sammansatt typ. Det specificerar en array av denna typ.

Nedan ses ett exempel som definierar webb-API:n `getPosts` vilken returnerar en 
array med `Post`-objekt.

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

Klassmappning
-------------

För att erhålla parametrar av sammansatta typer från klienten behöver en 
applikation deklarera mappningen från WSDL-typer til motsvarande PHP-klasser. 
Detta görs genom att konfigurera propertyn 
[classMap|CWebServiceAction::classMap] i [CWebServiceAction].

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

Fånga upp Remote Method Invocation
----------------------------------

Genom att implementera gränssnittet [IWebServiceProvider] kan en service 
provider fånga upp remote method invocations. I 
[IWebServiceProvider::beforeWebMethod], kan providern få tag på den nuvarande 
instansen av [CWebService] och erhålla namnet på den metod som för tillfället 
efterfrågas via [CWebService::methodName]. Den kan returnera false 
om fjärrmetoden inte invokeras av någon anledning (t.ex. icke-auktoriserad 
åtkomst).

<div class="revision">$Id: topics.webservice.txt 1808 2010-02-17 21:49:42Z qiang.xue $</div>