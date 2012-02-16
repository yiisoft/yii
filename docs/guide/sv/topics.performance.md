Prestandaoptimering
===================

Prestanda i webbapplikationer kan påverkas av många faktorer. Databas- och 
filsystemoperationer samt nätverksbandbredd är alla potentiellt påverkande 
faktorer. Yii har på alla sätt försökt minimera prestandaförluster orsakade av 
själva ramverket. Men det finns ändå flera ställen i en användarpplikation som 
kan trimmas för att öka prestanda.

Slå på APC-tillägget
--------------------

Att slå på [PHP:s APC-tillägg](http://www.php.net/manual/en/book.apc.php) är 
kanske det lättaste sättet att förbättra totalprestanda för en applikation. 
Tillägget cachelagrar och optimerar PHP:s partiellt kompilerade kod (bytecode), 
vilket undviker tidåtgång för parsning av PHP-skript för varje inkommande 
request.

Stäng av debugläget
-------------------

Att stänga av debugläget är ett annat enkelt sätt att öka prestanda. En Yii-
applikation körs i debugläge om konstanten `YII_DEBUG` är definierad till true. 
Debugläge är användbart under utvecklingsfasen, men det försämrar prestanda 
eftersom vissa komponenter orsakar extra arbete i debugläge. Till exempel 
meddelandeloggaren kan behöva registrera tillkommande debuginformation för varje 
meddelande som loggas.

Använd `yiilite.php`
--------------------

När [PHP:s APC-tillägg](http://www.php.net/manual/en/book.apc.php) är påslaget, 
kan vi ersätta `yii.php` med en annan bootstrapfil för Yii, `yiilite.php`, för 
att ytterligare förbättra prestanda i en Yii-driven applikation.

Filen `yiilite.php` kommer med i varje Yii-release. Den är resultatet av en 
sammanslagning av några vanligen använda Yii-klassfiler. Både kommentarer och 
trace-satser tas bort ur den sammanslagna filen. Därför reducerar man genom att 
använda `yiilite.php` antalet filer som behöver inkluderas, samt undviker 
exekvering av trace-satser.

Lägg märke till att användning av `yiilite.php` utan APC faktiskt kan försämra 
prestanda, eftersom `yiilite.php` innehåller klasser som inte nödvändigtvis 
används i varje request men som tar upp extra parsningstid. Det har också observerats 
att användning av `yiilite.php` är långsammare i vissa serverkonfigurationer, 
även när APC är påslagen. Bästa sättet att avgöra huruvida `yiilite.php` skall 
användas eller inte, är att köra en benchmark med den medföljande `hello world`-
demoapplikationen.

Använda cachningsmetoder
------------------------

Som beskrivs i avsnittet [Cachning](/doc/guide/caching.overview), erbjuder Yii 
ett antal cachningslösningar som signifikant kan förbättra prestandan för en 
webbapplikation. Om det tar lång tid att generera en viss sorts data, kan 
tillvägagångssättet [datacachning](/doc/guide/caching.data) reducera frekvensen 
data behöver genereras; Om en del av en sida förblir relativt statisk, kan 
tillvägagångssättet [fragmentcachning](/doc/guide/caching.fragment) reducera 
renderingsfrekvensen; Om en hel sida förblir relativt statisk kan 
tillvägagångssättet [sidcachning](/doc/guide/caching.page) användas för att 
spara hela sidan.

Om applikationen använder [Active Record](/doc/guide/database.ar), bör vi slå på 
schemacachning för att spara in tiden som annars går åt för parsning av 
databasschema. Detta kan göras genom att konfigurera propertyn 
[CDbConnection::schemaCachingDuration] till ett värde större än 0.

Förutom dessa cachningsmetoder på applikationsnivå, kan även cachningslösningar 
på servernivå användas för att förbättra applikationsprestanda. Faktum är att, 
[APC-cachning](/doc/guide/topics.performance#enabling-apc-extension) som 
beskrevs tidigare, hör till denna kategori. Det finns andra serverbaserade 
metoder, som [Zend Optimizer](http://www.zend.com/en/products/guard/zend-optimizer), 
[eAccelerator](http://eaccelerator.net/), [Squid](http://www.squid-cache.org/), 
för att nämna några.

Databasoptimering
-----------------

Hämtning av data från en databas är ofta den huvudsakliga prestandasänkan i en 
webbapplikation. Även om cachning kan lindra prestandaförlusten, löser den inte 
helt problemet. Om databasen innehåller enorma mängder data och cachelagrad data 
är ogiltig, kan det, utan rätt databas- och frågedesign, bli oacceptabelt dyrt 
att hämta in senaste data.

Utforma index genomtänkt i en databas. Indexering kan göra `SELECT`-frågor 
mycket snabbare, men de kan göra `INSERT`-, `UPDATE`- och `DELETE`-frågor 
långsammare.

För komplexa frågor rekommenderas att en databasvy (view) skapas för dessa 
istället för att repetitivt ge ut frågan i PHP-koden och få databashanteraren 
att parsa den.

Överanvänd inte [Active Record](/doc/guide/database.ar). Även om [Active 
Record](/doc/guide/database.ar) är bra för att modellera data på ett 
objektorienterat sätt, reducerar den faktiskt prestanda på grund av att den 
behöver skapa ett eller flera objekt för att representera varje rad i ett 
frågeresultat. För dataintensiva applikationer kan det vara ett bättre val att 
använda [DAO](/doc/guide/database.dao) eller databas-API:er på lägre nivå.

Sist men inte minst, använd om möjligt `LIMIT` i `SELECT`-frågor. Detta undviker 
inhämtning av överväldigande mängder data från databasen med risk att förbruka 
allt minne som allokerats för PHP.

Minimering av skriptfiler
-------------------------

Komplexa sidor behöver ofta inkludera många externa JavaScript- och CSS-filer. 
Eftersom varje skriptfil kräver en extra fråga till och svar från servern bör vi 
minimera antalet skriptfiler genom att kombinera dem till ett mindre antal. 
Vi bör också överväga att reducera storleken på varje skriptfil så att överföringstiden 
i nätverket reduceras. Det finns ett flertal verktyg att tillgå för hjälp ur dessa aspekter.

För en sida som genererats av Yii, är det stor risk att en del skriptfiler har renderats av 
komponenter som vi inte vill ändra (t.ex. Yii:s kärnkomponenter, tredjepartskomponenter). 
För att minimera dessa skriptfiler behöver vi göra det i två steg.

Först deklarerar vi att skripten skall minimeras genom att konfigurera propertyn 
[scriptMap|CClientScript::scriptMap] i applikationskomponenten [clientScript|CWebApplication::clientScript]. 
Detta kan ske antingen i applikationskonfigurationen eller i kod. Till exempel,

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>'/js/all.js',
	'jquery.ajaxqueue.js'=>'/js/all.js',
	'jquery.metadata.js'=>'/js/all.js',
	......
);
~~~

Det ovanstående kod gör, är att mappa dessa JavaScript-filer till URL:en `/js/all.js`. 
Om någon av dessa JavaScript-filer behöver inkluderas av någon komponent, kommer Yii 
att inkludera URL:en (en gång), i stället för de individuella skriptfilerna.

I det andra steget behöver vi använda några verktyg för att slå samman (och kanske komprimera) 
JavaScript-filerna till en enda fil samt spara den som `js/all.js`.

Samma förfarande används för CSS-filer.

Vi kan också öka sidladdningshastigheten med hjäp av [Google AJAX Libraries API](http://code.google.com/apis/ajaxlibs/). 
Till exempel kan `jquery.js` inkluderas från Google:s servrar i stället för från egen server. För att åstadkomma det 
konfigurerar vi först `scriptMap` på följande sätt,

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>false,
	'jquery.ajaxqueue.js'=>false,
	'jquery.metadata.js'=>false,
	......
);
~~~

Genom mappningen av dessa skriptfiler till false, förhindrar vi Yii att generera kod för att inkludera dessa filer. 
I stället lägger vi in följande kod i våra sidor för att uttryckligen inkludera skriptfilerna från Google,

~~~
[php]
<head>
<?php echo CGoogleApi::init(); ?>

<?php echo CHtml::script(
	CGoogleApi::load('jquery','1.3.2') . "\n" .
	CGoogleApi::load('jquery.ajaxqueue.js') . "\n" .
	CGoogleApi::load('jquery.metadata.js')
); ?>
......
</head>
~~~

<div class="revision">$Id: topics.performance.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>