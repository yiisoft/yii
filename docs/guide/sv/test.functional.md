Funktionell testning
====================

Före läsning av detta avsnitt rekommenderas läsning av [Selenium-dokumentationen](http://seleniumhq.org/docs/) 
och [PHPUnit-dokumentationen](http://www.phpunit.de/wiki/Documentation). Här följer en sammanfattning 
av de grundläggande principerna för hur man skriver ett funktionellt test i Yii:

 * Liksom för ett enhetstest är ett funktionellt test skrivet i form av en klass `XyzTest` 
 som ärver från [CWebTestCase], där `Xyz` står för klassen som skall testas. Eftersom 
 `PHPUnit_Extensions_SeleniumTestCase` är förälder till [CWebTestCase], kan vi använda 
 alla metoder som ärvs från denna.

 * Klassen med det funktionella testet spara i en PHP-fil med namnet `XyzTest.php`. Enligt 
 konvention sparas filen med det funktionella testet under katalogen `protected/tests/functional`.

 * Testklassen består i huvudsak av en uppsättning testmetoder med namn som `testAbc`, 
 där `Abc` ofta är namnet på den finess som är föremål för testning. För att till exempel testa 
 funktionen logga in användare, kan vi ha en testmetod med namnet `testLogin`.

 * En testmetod innehåller vanligen en sekvens av satser som ger ut kommandon till Selenium RC 
 för interaktion med webbapplikationen som testas. Testmetoden innehåller också assertion-satser 
 för att verifiera att webbapplikationen svarar som förväntat.

Låt oss, innan vi beskriver hur man skriver ett funktionellt test, titta närmare 
på filen `WebTestCase.php` skapad av `yiic webapp`-kommandot. Denna fil 
definierar `WebTestCase` vilken kan tjänstgöra som basklass för alla klasser med 
funktionella test.

~~~
[php]
define('TEST_BASE_URL','http://localhost/yii/demos/blog/index-test.php/');

class WebTestCase extends CWebTestCase
{
	/**
	 * Sets up before each test method runs.
	 * This mainly sets the base URL for the test application.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
	}

	......
}
~~~

Klassen `WebTestCase` sätter i huvudsak upp en bas-URL till sidorna som skall testas. 
Senare, i testmetoderna, kan relativa URL:er användas för att specificera vilka sidor 
som skall testas.

Det är värt att notera är att i bas-URL:en för testning används `index-test.php` 
som startskript, i stället för `index.php`. Den enda skillnaden mellan `index-test.php` 
och `index.php` är att den förra använder filen `test.php` som applikationskonfiguration 
medan den senare använder `main.php`.

Nu kan vi beskriva hur man kan testa förfarandet för att visa en postning i applikationen 
[blog demo](http://www.yiiframework.com/demos/blog). Först skriver vi testklassen som följer, 
med beaktande att testklassen ärver från basklassen vi just beskrivit:

~~~
[php]
class PostTest extends WebTestCase
{
	public $fixtures=array(
		'posts'=>'Post',
	);

	public function testShow()
	{
		$this->open('post/1');
	    // verify the sample post title exists
	    $this->assertTextPresent($this->posts['sample1']['title']);
	    // verify comment form exists
	    $this->assertTextPresent('Leave a Comment');
	}

	......
}
~~~

I likhet med hur vi skriver en klass för enhetstestning, deklarerar vi fixturerna 
som skall användas i aktuellt test. I detta fall indikerar vi att fixturen `Post` 
skall användas. I testmetoden `testShow`, instruerar vi först Selenium RC att 
öppna URL:en `post/1`. Lägg märke till att detta är en relativ URL och att den 
kompletta URL:en bildas genom att lägga till den till bas-URL:en som sattes upp 
i basklassen (dvs `http://localhost/yii/demos/blog/index- test.php/post/1`). 
Därefter verifierar vi att det går att hitta titeln för postningen `sample1` i den  
för tillfället visade webbsidan. Vi kontrollerar även att sidan innehåller texten 
`Leave a Comment`.

> Tip|Tips: Innan funktionella test kan köras måste Selenium-RC-servern startas. 
Detta kan göras genom exekvering av kommandot `java -jar selenium-server.jar` från 
Selenium-serverns installationskatalog.

<div class="revision">$Id: test.functional.txt 1662 2010-01-04 19:15:10Z qiang.xue $</div>