Applikationsobjekt
==================

Application omger exekveringsomgivningen inom vilken en inkommande request behandlas. 
Dess huvudsakliga uppgift är att samla in information ur en request från användare 
och skicka denna vidare till relevant kontroller för fortsatt bearbetning. 
Den tjänar också som central plats för konfigurationsinställningar på applikationsnivå. 
Av denna anledning kallas Application även `front-controller`.

Application instantieras av [startskriptet](/doc/guide/basics.entry) som en singleton 
vilken alltid kan nås via [Yii::app()|YiiBase::app].


Applikationens konfiguration
----------------------------

Som standard är applikationsobjektet en instans av [CWebApplication]. 
En anpassad version erhålls normalt genom att en konfigureringsfil 
(eller array) bifogas för initialisering av propertyvärden i samband med 
instantieringen. Ett alternativt sätt är att ärva från 
och utöka [CWebApplication].

Konfigurationen är en array bestående av nyckel-värdepar. Varje nyckel 
representerar namnet på en property i applikationsinstansen, det tillhörande 
värdet blir propertyns initialvärde. Exempel: följande konfiguration sätter 
applikationens [namn|CApplication::name] och propertyn 
[standardcontroller|CWebApplication::defaultController].

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

Normalt lagras konfigurationen i ett separat PHP-skript (t.ex. 
`protected/config/main.php`). I skriptet bildar konfigurationens array 
returvärde som följer:

~~~
[php]
return array(...);
~~~

Konfigurationen träder i kraft genom att dess filnamn lämnas som parameter till 
applikationens konstruktor, eller till [Yii::createWebApplication()], på 
nedanstående sätt, vilket vanligtvis sker i [startskriptet](/doc/guide/basics.entry):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|Tips: Om applikationens konfiguration är mycket omfattande kan den delas upp 
på flera filer, var och en bidragande med ett avsnitt av konfigurationsarrayen. 
De separata filerna kombineras därefter med hjälp av PHP `include()` till en komplett 
konfigurationsarray i konfigurationens huvudfil.


Applikationens rotkatalog
-------------------------

Application base directory är rotkatalogen som innehåller alla 
säkerhetskänsliga PHP-skript och data. Som standard är den en underkatalog med 
namnet `protected`, placerad under katalogen som innehåller startskriptet. 
Placeringen kan anpassas via propertyn [basePath|CWebApplication::basePath] i 
[applikationens konfiguration](/doc/guide/basics.application#application-configuration).

Innehåll i applikationens rotkatalog med underkataloger skall inte vara 
tillgängliga för webbanvändare. Med [Apache HTTP-server](http://httpd.apache.org/) 
kan detta lätt ordnas genom att en fil med namnet `.htaccess` placeras i rotkatalogen. 
Innehållet i `.htaccess` är som följer:

~~~
deny from all
~~~

Applikationskomponent
---------------------

Funktionaliteten hos applikationsobjektet kan lätt anpassas och berikas genom dess 
flexibla komponentarkitektur. Objektet handhar en uppsättning applikationskomponenter 
som var och en implementerar specifika finesser. Exempelvis tar det hand om en 
inkommen request från användare med hjälp av komponenterna [CUrlManager] och 
[CHttpRequest].

Genom att konfigurera applikationsinstansens [components|CApplication::components]-property, 
kan class- och propertyvärden sättas för varje komponent i applikationen. 
Exempel: [CMemCache]-komponenten kan konfigureras att använda multipla 
memcache-servrar för cachelagring, på följande sätt:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

I ovanstående exempel lades elementet `cache` till i arrayen `components`. 
Av elementet `cache` framgår att komponentens klass är `CMemCache` samt hur dess 
`servers`-property skall initialiseras.

För tillgång till en applikationskomponent, använd `Yii::app()->ComponentID`, 
där `ComponentID` refererar till komponentens ID (t.ex. `Yii::app()->cache`).

En applikationskomponent kan avaktiveras genom att `enabled` sätts till false i 
dess konfiguration. För en avaktiverad komponent erhålls null som returvärde.

> Tip|Tips: Som standard skapas applikationskomponenter på begäran. Detta innebär 
att en applikationskomponent inte alls behöver instansieras om den inte kommer 
till användning under en request från användare. Därav följer att totalprestanda 
inte behöver ta skada av att en applikation konfigureras med många komponenter. 
Vissa applikationskomponenter (t.ex. [CLogRouter]) kan behöva instansieras vare 
sig de används eller inte. För att åstadkomma detta, räkna upp deras ID:n i 
applikationens [preload|CApplication::preload]-property.

Applikationens kärnkomponenter
------------------------------

Yii definierar en kärna av applikationskomponenter som tillhandahåller i 
webbapplikationer allmänt förekommande finesser. Exempelvis används komponenten 
[request|CWebApplication::request] till att samla in information ur inkommande 
request från användare och leverera information såsom URL, cookies. 
Genom konfigurering av kärnkomponenternas propertyn kan de flesta av Yii:s 
standardbeteenden ändras. 

Nedan listas kärnkomponenterna som fördeklareras av [CWebApplication].

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] - handhar publicering av privata resursfiler

   - [authManager|CWebApplication::authManager]: [CAuthManager] - hanterar rollbaserad åtkomst (RBAC).

   - [cache|CApplication::cache]: [CCache] - tillhandahåller data cache-funktionalitet. Observera att aktuell klass (e.g. [CMemCache], [CDbCache]) måste specificeras. I annat fall returneras null när komponenten skall användas.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] - handhar klientskript (javascript and CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] - tillhandahåller översatta systemmeddelanden som Yii-ramverket använder.

   - [db|CApplication::db]: [CDbConnection] - tillhandahåller databasanslutningen. Observera att propertyn [connectionString|CDbConnection::connectionString] måste konfigureras om denna komponent används.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - hanterar icke uppfångade PHP-felmeddelanden och exception.

   - [format|CApplication::format]: [CFormatter] - formaterar datavärden inför presentation.

   - [messages|CApplication::messages]: [CPhpMessageSource] - tillhandahåller översatta texter ingående i Yii-applikationer.

   - [request|CWebApplication::request]: [CHttpRequest] - tillhandahåller information relaterad till inkommen request från användare.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] - tillhandahåller säkerhetsrelaterade tjänster såsom hashning, kryptering.

   - [session|CWebApplication::session]: [CHttpSession] - tillhandahåller funktionalitet relaterad till session.

   - [statePersister|CApplication::statePersister]: [CStatePersister] - tillhandahåller mekanismen för lagring av globalt tillstånd.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - tillhandahåller funktionalitet för att analysera eller skapa URL:er.

   - [user|CWebApplication::user]: [CWebUser] - representerar identitetsinformation för aktuell användare.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - handhar teman.


Livscykel för applikationsobjekt 
--------------------------------

Vid behandling av en inkommen request från användare genomgår en applikation följande livscykel:

   0. Förinitialisera applikationen med hjälp av [CApplication::preinit()];

   1. Sätt upp autoladdaren för klasser och felhantering;

   2. Registrera applikationens kärnkomponenter;

   3. Ladda applikationens konfiguration;

   4. Initialisera applikationen med [CApplication::init()]
       - Registrera applikationens `behaviors`;
	   - Ladda statiska (static) applikationskomponenter;

   5. Signalera en händelse [onBeginRequest|CApplication::onBeginRequest];

   6. Bearbeta användarens request:
	   - Samla in information ur inkommen request från användare;
	   - Skapa kontroller;
	   - Exekvera kontroller;

   7. Signalera en händelse [onEndRequest|CApplication::onEndRequest];

<div class="revision">$Id: basics.application.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>