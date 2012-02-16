Användning av tredjepartsbibliotek
==================================

Yii är omsorgsfullt konstruerat på så sätt att tredjepartsbibliotek utan 
svårighet kan integreras för att ytterligare utöka Yii:s funktionalitet. När 
tredjepartsbibliotek används i ett projekt, stöter utvecklare ofta på problem 
rörande namngivning av klasser och inkludering av filer. Eftersom alla Yii:s 
klasser har namn som föregås av bokstaven `C`, är det mindre risk för att 
problem kring namngivning av klasser uppstår; och eftersom Yii förlitar sig på 
[SPL autoload](http://us3.php.net/manual/en/function.spl-autoload.php) för 
inkludering av klassfiler, kan det friktionsfritt samexistera med andra 
bibliotek om dessa använder samma autoladdningsfiness, alternativt 
PHP-inkluderingssökväg för att inkludera klassfiler.


Nedan används ett exempel för att illustrera hur man i en Yii-applikation kan använda komponenten 
[Zend_Search_Lucene](http://www.zendframework.com/manual/en/zend.search.lucene.html) från 
[Zend-ramverket](http://www.zendframework.com).

Extrahera först distributionsfilen innehållande Zend-ramverket till en katalog 
under `protected/vendors`, förutsatt att `protected` är [applikationens 
rotkatalog](/doc/guide/basics.application#application-base-directory). 
Kontrollera att filen `protected/vendors/Zend/Search/Lucene.php` existerar.

Sätt därefter in följande rader i början av kontrollerns klassfil:

~~~
[php]
Yii::import('application.vendors.*');
require_once('Zend/Search/Lucene.php');
~~~

Ovanstående kod inkluderar klassfilen `Lucene.php`. Eftersom en relativ sökväg 
används, behöver PHP:s inkluderingssökväg ändras så att filen kan lokaliseras 
korrekt. Detta gör man genom att anropa `Yii::import` innan `require_once`.

När väl ovanstående grundinställning är på plats, kan `Lucene`-klassen användas 
i en kontrolleråtgärd (action), på följande sätt:

~~~
[php]
$lucene=new Zend_Search_Lucene($pathOfIndex);
$hits=$lucene->find(strtolower($keyword));
~~~

Använda tredjepartsbibliotek med namespace
------------------------------------------

För användning av bibliotek med namespace som följer 
[PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
(som Zend Framework 2 eller Symfony2), behöver dess rotkatalog registreras som sökvägsalias.

Som exempel använder vi [Imagine](https://github.com/avalanche123/Imagine).
Om vi placerar katalogen `Imagine` under `protected/vendors` kommer vi att kunna 
använda det på följande sätt:

~~~
[php]
Yii::setPathOfAlias('Imagine',Yii::getPathOfAlias('application.vendors.Imagine'));

// Därefter standardkod från Imagine-guiden:
// $imagine = new Imagine\Gd\Imagine();
// etc.
~~~

I ovanstående exempel skall det alias vi definierar matcha bibliotekets första 
namespace-avsnitt.

Använda Yii med tredjepartsystem
--------------------------------

Yii kan även användas som ett självständigt bibliotek för att stödja utveckling 
och förbättring av existerande tredjepartsystem, så som WordPress, Joomla, etc. 
För att åstadkomma detta, inkludera följande kod i tredjepartsystemets startskript:

~~~
[php]
require_once('path/to/yii.php');
Yii::createWebApplication('path/to/config.php');
~~~

Ovanstående kod är mycket lik kod som används i startskriptet för en typisk Yii-applikation, 
med undantag för en sak: metoden `run()` anropas inte när webapplikationens instans skapats.

Nu kan de flesta finesser som Yii erbjuder användas för vidareutveckling av tredjepartskod. 
Till exempel kan `Yii::app()` användas för att ge tillgång till applikationsinstansen; 
databasfinesser som DAO och ActiveRecord kan användas liksom modell och valideringsfinesser 
för att nämna några.


<div class="revision">$Id: extension.integration.txt 3431 2011-11-03 00:53:44Z alexander.makarow@gmail.com $</div>