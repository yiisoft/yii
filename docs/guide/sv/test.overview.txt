Testning
========

Testning är en nödvändig process vid mjukvaruutveckling. Vare sig vi är medvetna 
om det eller inte, genomför vi testning hela tiden medan vi utvecklar en 
webbapplikation. Till exempel när vi skriver en klass i PHP, kan det hända att 
vi använder några `echo`- eller `die`-satser för att visa att vi implementerar 
en metod korrekt; när vi implementerar en webbsida innehållande ett komplext 
HTML-formulär, kan vi prova att mata in testdata för att garantera att sidan 
interagerar med oss som tänkt. Mer avancerade utvecklare kanske skriver kod för 
att automatisera denna testningsprocess så att vi varje gång vi behöver testa 
något, endast behöver hämta fram koden och låta datorn göra testningen åt oss. 
Detta är känt som *automatiserad testning*, vilket är huvudämnet för detta kapitel.

Testningsstödet som erbjuds av Yii omfattar *enhetstestning* och *funktionell testning*.

Ett enhetstest verifierar att en enskild kodenhet fungerar som tänkt. Inom 
objektorienterad programmering är den grundläggande kodenheten en klass. Ett 
enhetstest behöver därför huvudsakligen verifiera att var och en av metoderna i 
klassens gränssnitt (interface) fungerar korrekt. Dvs, med givna inparametrar, 
verifierar testet att metoden returnerar förväntat resultat. Enhetstester 
utvecklas vanligen av samma personer som skriver de klasser som testas.

Ett funktionellt test verifierar att en finess (t.ex. hantering av postningar i 
ett bloggsystem) fungerar som väntat. I jämförelse med ett enhetstest hör ett 
funktionellt test till en högre nivå  eftersom en finess som testas ofta 
omfattar flera klasser. Funktionella tester är vanligen utvecklade av personer 
som är mycket väl insatta i systemkraven (de kan vara antingen utvecklare eller 
kvalitetssäkringsingenjörer).


Testdriven utveckling
---------------------

Nedan visas utvecklingscykeln inom så kallad [testdriven utveckling (TDD)](http://en.wikipedia.org/wiki/Test-driven_development):

 1. Skapa ett nytt test som täcker en finess som skall implementeras. Testet förväntas gå fel vid dess första körning eftersom finessen ännu inte implementerats.
 2. Kör alla test och kontrollera att det nytillkomna testet går fel.
 3. Skriv kod som skall få det nya testet att passera felfritt.
 4. Kör alla test och kontrollera att alla passerar.
 5. Refaktorisera den nyskrivna koden och kontrollera att testerna fortfarande passerar.

Upprepa steg 1 till 5 för att driva implementeringen av funktionalitet framåt.


Sätta upp testomgivning
-----------------------

Stödet för testning som Yii erbjuder kräver [PHPUnit](http://www.phpunit.de/) 
3.5+ samt [Selenium Remote Control](http://seleniumhq.org/projects/remote-control/) 1.0+.
Se deras respektive dokumentation angående installation av PHPUnit och Selenium Remote Control.

När vi använder konsolkommandot `yiic webapp` till att skapa en ny Yii-
applikation, kommer det att generera följande filer och kataloger åt oss med 
ändamålet att skriva och köra nya test:

~~~
testdrive/
   protected/                containing protected application files
      tests/                 containing tests for the application
         fixtures/           containing database fixtures
         functional/         containing functional tests
         unit/               containing unit tests
         report/             containing coverage reports
         bootstrap.php       the script executed at the very beginning
         phpunit.xml         the PHPUnit configuration file
         WebTestCase.php     the base class for Web-based functional tests
~~~

Som framgår ovan, kommer våra test att huvudsakligen placeras i tre kataloger: 
`fixtures`, `functional` och `unit`och katalogen `report` kommer att användas 
för lagring av genererade kodtäckningsrapporter.

För att köra test (enhetstest eller funktionella test), kan vi köra följande 
kommandon i ett konsolfönster:

~~~
% cd testdrive/protected/tests
% phpunit functional/PostTest.php    // kör ett enstaka test
% phpunit --verbose functional       // kör alla test under 'functional'
% phpunit --coverage-html ./report unit
~~~

I ovanstående exempel kör det sista kommandot alla tester under katalogen `unit` 
och skapar en kodtäckningsrapport i katalogen `report`. Märk att [xdebug-tillägget](http://www.xdebug.org/) 
måste vara installerat och aktiverat för att kodtäckningsrapport skall kunna genereras.


Bootstrap-skript för test
-------------------------

Låt oss titta på vad som kan finnas i filen `bootstrap.php`. Denna fil är 
speciell eftersom den motsvarar [startskriptet](/doc/guide/basics.entry) och utgör 
startpunkt vid exekvering av en uppsättning tester.

~~~
[php]
$yiit='path/to/yii/framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';
require_once($yiit);
require_once(dirname(__FILE__).'/WebTestCase.php');
Yii::createWebApplication($config);
~~~

Ovan inkluderas först filen `yiit.php` från Yii-ramverket, genom vilken några 
globala konstanter initialiseras samt erforderliga basklasser för test 
inkluderas. Därefter skapas en instans av webbapplikation med hjälp av 
konfigurationsfilen `test.php`. Om vi tittar närmare på `test.php`, kommer vi 
att finna att den ärver från konfigurationen i `main.php` och lägger till 
applikationskomponenten `fixture` som är av klassen [CDbFixtureManager]. 
I nästa avsnitt kommer fixturer att beskrivas detaljerat.

~~~
[php]
return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'components'=>array(
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			/* uncomment the following to provide test database connection
			'db'=>array(
				'connectionString'=>'DSN for test database',
			),
			*/
		),
	)
);
~~~

När vi kör tester som involverar en databas bör vi använda en testdatabas så att 
exekvering av tester inte påverkar normala utvecklings- eller 
produktionsaktiviteter. För att åstadkomma detta behöver vi bara ta bort 
kommentaren kring `db`-konfigurationen ovan och fylla i propertyn 
`connectionString` med DSN:et (namnet på datakällan) för testdatabasen.

Med ett sådant här bootstrap-skript förfogar vi vid körning av enhetstester över 
en applikationsinstans som är nästan likadan som den som servar request från 
webbanvändare. Den huvudsakliga skillnaden är att den innehåller fixturhanteraren samt 
använder testdatabasen.


<div class="revision">$Id: test.overview.txt 2997 2011-02-23 13:51:40Z alexander.makarow $</div>