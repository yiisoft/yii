Web Service
===========

[Web service](http://en.wikipedia.org/wiki/Web_service) jest opartym o sieć
systemem informatycznym zaprojektowanym do wspierania wzajemnej interakcji
maszyna-maszyna. W kontekście aplikacji webowej termin odnosi się do zbioru
funkcji API, które są dostępne poprzez Internet i wykonywane są w zdalnym
systemie utrzymującym udostępnianą usługę. Przykładowo: klient oparty
o [Flex](http://www.adobe.com/products/flex/) może wywoływać funkcje zaimplementowane
na serwerze utrzymującym aplikację opartą o PHP. Web service opiera się
o [SOAP](http://en.wikipedia.org/wiki/SOAP), jako podstawową warstwę stosu
protokołu komunikacyjnego.

Yii dostarcza klas [CWebService] i [CWebServiceAction] dla uproszczenia pracy
związanej z implementacją Web service'u w aplikacji webowej. Poszczególne
API pogrupowane są w klasy, zwane *dostawcami usług*. Dla każej z tych klas
Yii generuje specyfikację [WSDL](http://www.w3.org/TR/wsdl) określającą,
jakie funkcje API są dostępne i jak powinny być wywoływane przez klienta.
W momencie gdy funkcja API jest wywoływana przez klienta, Yii tworzy instancję
odpowiedniego dostawcy usługi i wywołuje potrzebne API, aby zrealizować żądanie.

> Uwaga: [CWebService] opiera się o [rozszerzenie PHP SOAP]
(http://www.php.net/manual/en/ref.soap.php). Upewnij się,
że je włączyłeś zanim wypróbujesz przykłady zawarte w tej części.

Definiowanie dostawcy usługi
----------------------------

Jak wcześniej mówiliśmy, dostawca usługi jest klasą udostępniającą metody,
które mogą być wywoływane zdalnie. Yii opiera się na [komentarzach dla potrzeb
dokumentacji - tzw. doc comment](http://java.sun.com/j2se/javadoc/writingdoccomments/)
i na [introspekcjach klas](http://php.net/manual/en/book.reflection.php)
w celu identyfikacji, która z metod może być zdalnie wywoływana oraz jakie są
jej parametry i zwracana wartość.

Rozpocznijmy od prostej usługi informującej o notowaniach giełdowych. Pozwala ona
klientowi uzyskać informację o notowaniu określonych akcji. Definiujemy dostawcę
usług jak pokazano niżej. Zwróć uwagę, że klasę dostawcy `StockController`
definiujemy jako rozszerzenie klasy [CController]. Nie jest to jednak wymagane.
Wkrótce krótko wyjaśnimy dlaczego.

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

Powyżej deklarujemy metodę `getPrice`, która stanie się funkcją API Web
service'u za sprawą umieszczenia znacznika `@soap` w jej sekcji doc comment.
Opieramy się na doc comment aby określić typ argumentów i zwracaną wartość.
Kolejne funkcje API mogą być deklarowane w analogiczny sposób.

Deklarowanie akcji web service'u
--------------------------------

Po zdefiniowaniu dostawcy usług musimy sprawić, by te usługi były dostępne
dla klientów. W tym celu utworzymy akcję kontrolera, aby udostępnić usługę.
Możemy to łatwo zrobić deklarując w klasie kontrolera akcję [CWebServiceAction].
W naszym przykładzie wstawiamy ją do klasy `StockController`.

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

To wszystko co musimy zrobić by stworzyć Web service! Jeżeli spróbujemy teraz
wywołać akcję poprzez URL `http://hostname/path/to/index.php?r=stock/quote`,
otrzymamy w odpowiedzi mnóstwo danych w formacie XML, które w rzeczywistości
stanowią kod WSDL dla web service'u, który właśnie stworzyliśmy.

> Wskazówka: [CWebServiceAction] domyślnie zakłada, że bieżący kontroler
jest dostawcą usługi. To dlatego definiujemy metodę `getPrice` wewnątrz
klasy `StockController`.

Korzystanie z Web Service'u
---------------------------

Aby dokończyć przykład stwórzmy klienta, który użyje usługi naszego web
service'u. Przykładowy klient napisany jest w PHP, ale mógłby być także
napisany także w innych językach, np. `Java`, `C#`, `Flex`, itp.

~~~
[php]
$client=new SoapClient('http://hostname/path/to/index.php?r=stock/quote');
echo $client->getPrice('GOOGLE');
~~~

Uruchom powyższy skrypt w trybie webowym lub w konsoli, a powinieneś ujrzeć
wartość `350`, co jest ceną `GOOGLE`.

Typy danych
-----------

Gdy deklarujemy metody i atrybuty klas, które mają być zdalnie dostępne,
musimy określić typy danych dla parametrów wejściowych i wyjściowych.
Mogą tu zostać użyte następujące proste typy danych:

   - str/string: mapowany jest na `xsd:string`;
   - int/integer: mapowany jest na `xsd:int`;
   - float/double: mapowany jest na `xsd:float`;
   - bool/boolean: mapowany jest na `xsd:boolean`;
   - date: mapowany jest na `xsd:date`;
   - time: mapowany jest na `xsd:time`;
   - datetime: mapowany jest na `xsd:dateTime`;
   - array: mapowany jest na `xsd:string`;
   - object: mapowany jest na `xsd:struct`;
   - mixed: mapowany jest na `xsd:anyType`.

Jeżeli jakiś typ nie występuje wśród powyższych typów prostych, traktowany
jest jako typ złożony, składający się z parametrów. Typ złożony reprezentowany
jest przez klasę, jego parametry są publicznymi atrybutami tej klasy
zawierającymi znacznik `@soap` w ich sekcjach doc comments.

Możemy również użyć typu tablicowego poprzez dodanie `[]` na końcu typu
prostego lub złożonego. Tym sposobem definiujemy tablicę określonego typu.

W przykładzie poniżej zdefiniowano funkcję Web API `getPosts`, która zwraca
tablicę obiektów `Post`.

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

Mapowanie klas
--------------

W celu otrzymania od klienta (usługi) parametrów typu złożonego,
aplikacja musi deklarować mapowanie z typów WSDL na odpowiednie
klasy PHP. Jest to realizowane przez konfigurację atrybutu
[classMap|CWebServiceAction::classMap] klasy [CWebServiceAction].

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

Przechwytywanie zdalnych wywołań metod
--------------------------------------

Przez zaimplementowanie interfejsu [IWebServiceProvider] dostawca usług
może przechwytywać zdalne wywołania metod. W (kodzie obsługi zdarzenia)
[IWebServiceProvider::beforeWebMethod] dostawca może uzyskać dostęp do
bieżącej instancji [CWebService] i odczytać nazwę metody aktualnie
wywoływanej poprzez [CWebService::methodName]. Może wtedy zwrócić wartość
false, jeżeli z pewnych powodów zdalna metoda nie powinna być wywoływana
(np. nieautoryzowany dostęp).

<div class="revision">$Id: topics.webservice.txt 1808 2010-02-17 21:49:42Z qiang.xue $</div>