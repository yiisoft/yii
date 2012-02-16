Felhantering
============

Yii tillhandahåller ett komplett felhanteringsramverk baserat på 
exception-mekanismen i PHP 5. När en applikation skapas för att hantera en 
inkommen request från användare, registrerar den sin metod 
[handleError|CApplication::handleError] för hantering av PHP:s varningar och 
påpekanden; och den registrerar sin metod 
[handleException|CApplication::handleException] för hantering av icke-uppfångade 
PHP-exception. Därför, om en varning eller ett påpekande från PHP eller en icke-
uppfångad exception uppstår när applikationen körs, kommer en av felhanterarna 
att ta över kontrollen och starta den nödvändiga felhanteringsproceduren.

> Tip|Tips: Registreringen av felhanterare sker i applikationens konstruktor 
genom anrop till PHP-funktionerna 
[set_exception_handler](http://www.php.net/manual/en/function.set-exception-handler.php) 
och [set_error_handler](http://www.php.net/manual/en/function.set-error-handler.php). 
Om det inte skulle vara önskvärt att Yii hanterar fel och 
exception, kan konstanterna `YII_ENABLE_ERROR_HANDLER` respektive 
`YII_ENABLE_EXCEPTION_HANDLER` sättas till false i 
[startskriptet](/doc/guide/basics.entry).

Som standard, signalerar [handleError|CApplication::handleError] (eller 
[handleException|CApplication::handleException]) en [onError|CApplication::onError]-händelse 
(eller [onException|CApplication::onException]-händelse). Om fel (eller 
exception respektive) inte fångas upp av någon händelsehanterare, kommer 
applikationskomponenten [errorHandler|CErrorHandler] att konsulteras.

Signalera exception
-------------------

Att signalera en exception i Yii avviker inte från att signalera en normal 
PHP-exception. Man använder följande syntax för att signalera en exception när så 
erfordras:

~~~
[php]
throw new ExceptionClass('ExceptionMessage');
~~~

Yii definierar tre exception-klasser: [CException], [CDbException] 
och [CHttpException]. [CException] är en generell exception-klass. 
[CDbException] representerar en exception som orsakas av databasrelaterade 
operationer. [CHttpException] representerar en exception som skall presenteras 
för slutanvändare. Den bär också med sig en [statusCode|CHttpException::statusCode]-property 
representerande en HTTP-statuskod. En exceptions klasstyp avgör hur den skall presenteras, 
vilket förklaras nedan.

> Tip|Tips: Att signalera en [CHttpException] är ett enkelt sätt att rapportera 
> handhavandefel av användare. Till exempel, om användaren levererar ett ogiltigt 
> post-ID i URL:en, kan vi helt enkelt göra följande för att presentera ett 
> 404-felmedelande (sidan hittades inte): 
> ~~~ 
> [php] 
> // if post ID is invalid 
> throw new CHttpException(404,'The specified post cannot be found.'); 
> ~~~ 

Presentera felmeddelanden
-------------------------

När ett fel skickas vidare till applikationskomponenten [CErrorHandler], väljer 
denna en passande vy för presentation av felmeddelandet. Om det är meningen att 
felmeddelandet skall visas för slutanvändare, som en [CHttpException], kommer 
vyn `errorXXX` att användas, där `XXX` står för HTTP-statuskoden (t.ex. 400, 
404, 500). Om felet är ett internt sådant och bara skall presenteras för 
utvecklare, kommer vyn `exception` att användas. I det senare fallet presenteras 
en komplett anropsstack såväl som information om raden där felet uppstod.

> Info: När applikationen körs i 
[produktionsläge](/doc/guide/basics.entry#debug-mode), kommer alla fel, 
inklusive de interna att presenteras med hjälp av vyn `errorXXX`. Detta beror på 
att anropsstacken vid ett fel kan innehålla känslig information. I detta fall, 
kan utvecklare ta hjälp av felloggar för att avgöra vad som är den verkliga 
anledningen till problemet.

[CErrorHandler] söker efter en vyfil till motsvarande vy i följande ordning:

   1. `WebRoot/themes/ThemeName/views/system`: detta är katalogen för `system`-vyer 
   under det för närvarande aktiva temat.

   2. `WebRoot/protected/views/system`: detta är standardkatalogen för `system`-vyer 
   i en applikation.

   3. `yii/framework/views`: detta är standardkatalogen för systemvyer som Yii-ramverket 
   tillhandahåller.

Vi kan därför. om vi vill använda anpassade felmeddelanden, helt enkelt skapa 
vyfiler med felmeddelanden i applikationens alternativt temats katalog med 
systemvyer. Varje vyfil är ett vanligt PHP-skript, huvudsakligen innehållande 
HTML-kod. För fler detaljer, studera standardvyfilerna under ramverkets `view`-
katalog.


Felhantering med hjälp av en Åtgärd
-----------------------------------

Yii tillåter att man använder en [kontrolleråtgärd](/doc/guide/basics.controller#action) 
för att hantera uppgiften felpresentation. För att åstadkomma detta skall felhanteringskomponenten i 
applikationskonfigurationen konfigureras på följande sätt:

~~~
[php]
return array(
	......
	'components'=>array(
		'errorHandler'=>array(
			'errorAction'=>'site/error',
		),
	),
);
~~~

Ovan konfigureras propertyn [CErrorHandler::errorAction] att vara vägen `site/error` 
som refererar till åtgärden `error` i `SiteController`. En annan väg kan användas allt 
efter behov.

Åtgärden `error` kan skrivas i stil med följande:

~~~
[php]
public function actionError()
{
	if($error=Yii::app()->errorHandler->error)
		$this->render('error', $error);
}
~~~

I åtgärden hämtas först detaljerad information om felet från [CErrorHandler::error].
Förutsatt att något returnerades renderas `error`-vyn tillsammans med den hämtade informationen.
Informationen som returneras från [CErrorHandler::error] utgörs av en array med följande fält:

 * `code`: HTTP statuskod (t.ex. 403, 500);
 * `type`: Typ av fel (t.ex. [CHttpException], `PHP-fel`);
 * `message`: felmeddelande;
 * `file`: namn på PHP-skriptet där felet uppträdde;
 * `line`: radnummer där felet uppträdde;
 * `trace`: anropsstacken;
 * `source`: källkodskontext där felet uppträdde.

> Tip|Tips: Anledningen till att vi testar om något returneras från [CErrorHandler::error] 
är att åtgärden `error` skulle kunna anropas direkt av en användare, i vilket fall det 
inte finns någon felinformation.
Eftersom vyn erhåller arrayen `$error`, kommer den senare att automatiskt expanderas 
till individuella variabler. Därmed kan vi i vyn direkt använda variablerna 
ex. `$code`, `$type`.


Meddelandeloggning
------------------

Ett meddelande med nivån `error` kommer alltid att loggas när ett fel uppstår. 
Om felet orsakas av en PHP-varning eller d:o påpekande, kommer meddelandet att 
loggas med katergorin `php`; om felet orsakas av en icke-uppfångad exception, 
kommer kategorin att vara `exception.ExceptionClassName` (för [CHttpException] 
kommer dess [statusCode|CHttpException::statusCode] också att läggas till dess 
kategori). Man kan därför dra fördel av finessen 
[loggning](/doc/guide/topics.logging) till att monitorera fel som inträffat när 
applikationen kördes.

<div class="revision">$Id: topics.error.txt 3374 2011-08-05 23:01:19Z alexander.makarow $</div>