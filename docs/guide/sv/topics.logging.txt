Loggning
========

Yii erbjuder en flexibel och utbyggbar loggningsmöjlighet. Meddelanden som 
loggas kan klassificeras efter loggningsnivå och meddelandekategori. Med hjälp 
av nivå- och kategorifilter kan utvalda meddelanden styras vidare till 
varierande destinationer, så som filer, mail, webbläsarfönster, etc.

Meddelandeloggning
------------------

Meddelanden kan loggas genom anrop till antingen [Yii::log] eller [Yii::trace]. 
Skillnaden mellan dess två metoder är att den senare loggar ett meddelande 
endast när applikationen är i [debugläge](/doc/guide/basics.entry#debug-mode).

~~~
[php]
Yii::log($message, $level, $category);
Yii::trace($message, $category);
~~~

När ett meddelande loggas, behöver dess kategori och nivå level anges. Kategori 
är en sträng på formatet `xxx.yyy.zzz` har likheter med 
[sökvägsalias](/doc/guide/basics.namespace). Till exempel, om ett meddelande 
loggas i [CController], kan vi använda kategorin `system.web.CController`. 
Meddelandenivå skall vara något av följande värden:

   - `trace`: detta är nivån som [Yii::trace] använder. Den är till för att 
   följa exekveringsflödet i applikationen när den utvecklas.

   - `info`: denna nivå är för loggning av generell information.

   - `profile`: denna nivå är för prestandaprofilering som beskrivs längre ned.

   - `warning`: denna nivå är för varningsmeddelanden.

   - `error`: denna nivå är för meddelanden om oåterkallerliga fel.

Meddelandestyrning
------------------

Meddelanden som loggas med hjälp av [Yii::log] eller [Yii::trace] förvaras i 
primärminne. Vanligtvis vill vi presentera dem i webbläsarens fönster, eller 
spara dem till något icke-flyktigt minne såsom filer, email. Detta kallas för 
*meddelandestyrning* (message routing), dvs att skicka meddelanden till 
varierande destinationer.

I Yii hanteras meddelandestyrning av en applikationskomponent, [CLogRouter]. Den 
hanterar en uppsättning av så kallade *loggningsvägar* (log routes). Varje 
loggningsväg representerar en distinkt loggdestination. Meddelanden som skickas 
längs en loggningsväg kan filtreras med avseende på deras nivåer och kategorier.

För att använda meddelandestyrning, behöver en applikationskomponent, 
[CLogRouter], installeras och förhandsladdas. Dessutom behöver dess property 
[routes|CLogRouter::routes] konfigureras med de loggvägar vi vill ha. Följande 
visar ett exempel på den erforderliga 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration):

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'trace, info',
					'categories'=>'system.*',
				),
				array(
					'class'=>'CEmailLogRoute',
					'levels'=>'error, warning',
					'emails'=>'admin@example.com',
				),
			),
		),
	),
)
~~~

I ovanstående exempel har vi två loggningsvägar. Den första är [CFileLogRoute], 
vilken sparar loggmedelanden till en fil i applikationens runtime-katalog. 
Enbart meddelanden vars nivå är `trace` eller `info` och vars kategori börjar 
med `system.` sparas. Den andra loggningsvägen är [CEmailLogRoute] som skickar 
meddelanden till den angivna mailadressen. Enbart meddelanden vars nivå är 
`error` eller `warning` skickas.

Följande loggningsvägar finns tillgängliga i Yii:

   - [CDbLogRoute]: sparar meddelanden i en databastabell.
   - [CEmailLogRoute]: Skickar meddelanden till en angiven mailadress.
   - [CFileLogRoute]: sparar meddelanden till en fil i applikationens runtime-katalog.
   - [CWebLogRoute]: presenterar meddelanden i slutet av den aktuella webbsidan.
   - [CProfileLogRoute]: visar profileringsmeddelanden i slutet av den aktuella webbsidan.

> Info: Meddelandestyrning sker i slutet av den pågående request-cykeln, när 
[onEndRequest|CApplication::onEndRequest]-händelsen signaleras. För att 
uttryckligen stoppa bearbetningen av pågående request, anropa 
[CApplication::end()] i stället för `die()` eller `exit()`, eftersom 
[CApplication::end()] signalerar [onEndRequest|CApplication::onEndRequest]-
händelsen så att meddelanden kan loggas korrekt.

Meddelandefiltrering
--------------------

Som tidigare nämnts kan meddelanden filtreras enligt deras nivåer och kategorier 
innan de skickas längs en loggningsväg. Detta utförs genom att sätta propertyna 
[levels|CLogRoute::levels] och [categories|CLogRoute::categories] för 
motsvarande loggningsväg. Multipla nivåer eller kategorier läggs till varandra 
separerade av kommatecken.

Eftersom meddelandekategorierna är på formatet `xxx.yyy.zzz`, kan de betraktas 
som en kategorihierkarki. Närmare bestämt, säger vi att `xxx` är förälder till 
`xxx.yyy`, som i sin tur är förälder till `xxx.yyy.zzz`. Därför kan `xxx.*` 
användas för att representera kategori `xxx` samt alla dess barn- och 
barnbarnskategorier.

Loggning av kontextinformation
------------------------------

Det är möjligt att specificera loggning av ytterligare kontextinformation, såsom 
fördefinierade PHP-variabler (t.ex. `$_GET`, `$_SERVER`), sessions-ID, användarnamn, etc. 
Detta åstadkommmes genom att propertyn [CLogRoute::filter] för en loggningsväg specificeras 
till ett passande loggningsfilter.

Yii-ramverket innehåller det praktiska [CLogFilter] som i de flesta fall kan användas 
när ett loggningsfilter erfordras. Som standard loggar [CLogFilter] ett meddelande 
tillsammans med variabler som `$_GET`, `$_SERVER`, vilka ofta innehåller värdefull 
information om systemkontext.
[CLogFilter] kan även konfigureras att föregå varje loggat meddelande med sessions-ID, 
användarnamn, etc., vilket i stor utsträckning kan förenkla global sökning i en större 
mängd loggade meddelanden.

Följande konfiguration visar hur man aktiverar loggning av kontextinformation. 
Lägg märke till att varje loggningsväg kan ha sitt eget loggningsfilter. Som standard 
har en loggningsväg inget filter.

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error',
					'filter'=>'CLogFilter',
				),
				...other log routes...
			),
		),
	),
)
~~~


Yii stöder loggning av anropsstacken i meddelanden som loggas genom anrop till `Yii::trace`. 
Denna finess är som standard inte aktiverad, eftersom den sätter ned prestanda. 
För att använda denna finess, definiera helt enkelt konstanten `YII_TRACE_LEVEL` 
placerad i början av startskriptet (före inkludering av `yii.php`) till ett heltal 
större än 0. Yii kommer då att komplettera varje trace-meddelande med filnamn och 
radnummer för anrop som tillhör applikationskod. Värdet av `YII_TRACE_LEVEL` avgör 
hur många nivåer av anropsstacken som skall registreras. Denna information är 
i synnerhet användbar i utvecklingsfasen, eftersom den kan hjälpa till att 
identifiera ställen som triggar trace-meddelanden.


Prestandaprofilering
--------------------

Prestandaprofilering är en speciell typ av meddelandeloggning. 
Prestandaprofilering kan användas till att mäta tidåtgången för att exekvera 
specificerade kodblock och lokalisera vilka prestandasänkorna är.

För att använda prestandaprofilering, behöver vi identifiera vilka kodblock som 
behöver profileras. Början och slutet av dessa kodblock markeras genom 
insättning av följande metoder:

~~~
[php]
Yii::beginProfile('blockID');
...code block being profiled...
Yii::endProfile('blockID');
~~~

där `blockID` är ett ID som oförväxelbart identifierar kodblocket.

Märk att kodblock måste vara korrrekt nästlade. Det vill säga, kodblock kan inte 
korsa varandras blockgränser. De måste antingen befinna sig parallellt på samma 
nivå eller inneslutas helt i ett omgivande kodblock.

För att presentera resultatet av profileringen behöver en applikationskomponent, 
[CLogRouter] installeras, med en [CProfileLogRoute] loggningsväg. Detta är 
detsamma som vi gör vid vanlig meddelandestyrning. [CProfileLogRoute]-loggningsvägen 
kommer att presentera prestandaresultaten vid slutet av den aktuella sidan.


Profilering av SQL-exekvering
-----------------------------

Profilering är speciellt användbar vid arbete med databaser eftersom SQL-exekvering 
ofta står för den huvudsakliga prestandaförlusten i en applikation. Utöver att vi 
manuellt kan sätta in `beginProfile`- och `endProfile`-satser på lämpliga ställen, 
för att mäta tidåtgången i varje SQL-exekvering, tillhandahåller Yii ett mer systematiskt 
tillvägagångssätt för att lösa detta problem.

Genom att sätta [CDbConnection::enableProfiling] till true i applikationskonfigurationen,
kommer exekveringen av varje SQL-sats att profileras. Resultatet kan med lätthet 
presenteras genom användning av nämnda [CProfileLogRoute], som kan visa oss hur lång tid 
som spenderas för exekvering av respektive SQL-sats. Vi kan även anropa 
[CDbConnection::getStats()] för att erhålla det totala antalet exekverade SQL-satser 
samt total exekveringstid.


<div class="revision">$Id: topics.logging.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>