Alkalmazás
===========

Az alkalmazás képviseli a kérés feldolgozásának végrehajtási környezetét.
Elsődleges feladata feloldani a felhasználó kérését és továbbítani a megfelelő
kontrollernek további feldolgozásra. Ezen kívül az alkalmazás szintű beállítások
tárolásának központi helyeként is szolgál. Ezért az alkalmazást `front-controller`-nek
is szokták nevezni.

A [belépési szkript] (/doc/guide/basics.entry) az alkalmazást 'singleton'-ként hozza létre.
Az alkalmazás 'singleton' bárhol elérhető a kovetkezőn keresztül: [Yii::app()|YiiBase::app].


Alkalmazás beállítás
--------------------

Alapértelmezésként az alkalmazás a [CWebApplication] egy példánya. Testreszabásához
alap esteben egy konfigurációs fájlt (vagy tömböt) biztosítunk, hogy inicializáljuk
tulajdonságait példányosítása során. Egy másik módszer az alkalmazás testreszabásához
a [CWebApplication] kiterjesztése.

A beállítás egy kulcs-érték párokat tartalmazó tömbön keresztül történik. Minden
kulcs az alkalamazás egy tulajdonságának nevét képviseli, míg minden érték a
hozzá tartozó tulajdonság alapértelmezését állítja be. Például a következő beállítás
meghatározza az alkalmazás [name|CApplication::name] és [defaultController|CWebApplication::defaultController]
tulajdonságait.

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

Általában a beállítást egy külön PHP szkriptben tároljuk (pl.: `protected/config/main.php`).
A szkripten belül a következők szerint visszaadjuk a konfigurációs tömböt:

~~~
[php]
return array(...);
~~~

A beállítás alkalmazásához a konfigurációs fájl nevét paraméterként kell átadjuk
az alkalamazás konstruktorának, vagy [Yii::createWebApplication()]-nek a következők
szerint (ez általában a [belépési szkript](/doc/guide/basics.entry)-ben történik):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|Tipp: Ha az alkalmás bonyolult konfigurációt igényel, szétoszthatjuk
több fájlba, melyek mindegyike visszadja a konfigurációs tömb egy részét.
Végül a fő konfigurációs fájlban a PHP `include()`-ot meghívva beolvassuk az
egyes beállításokat és utána egy teljes konfigurációs tömbbé egyesítjük.


Az alkalmazás alap könyvtára
----------------------------

Az alkalmazás alap könyvtára nem más, mint a könyvtár, melyben az összes
biztonságra érzékeny PHP szkript és adat található. Alapértelmezésként ez a
belépési szkriptet tartalmazó könyvtár `protected` nevű alkönyvtára. Helye
testreszabható az alkalmazás [basePath|CWebApplication::basePath] tulajdonságának
meghatározásával az [alkalmazás beállításban](#application-configuration).

Az alkalmazás alap könyvtára alatt található tartalmaknak nem szabad
web felhasználók által elérhetőnek lenniük. [Apache HTTP
szerverrel](http://httpd.apache.org/) ez egyszerűen elérhető egy `.htaccess` fájl
elhelyezésével az alap könyvtárban. A `.htaccess` fájl tartalma a következő:

~~~
deny from all
~~~

Alkalmazás komponens
--------------------

Az alkalmazás képességei könnyen testraszabhatóak és gazdagíthatóak rugalmas
komponens szerkezetének köszönhetően. Az alkalmazás komponensek egy halmazát
kezeli, melyek mindegyike egy speciális képességet valósít meg. Például,
az alkalmazás egy felhasználói kérést a [CUrlManager] és [CHttpRequest]
segítségével old fel.

Az alkalmazás [components|CApplication::components] tulajdonságának beállításával
testreszabhatjuk az osztályát és tulajdonságait bármelyik felhasznált komponensnek.
Például, beállíthatjuk úgy a [CMemCache] komponenst, hogy több memcache szervert
használjon gyorsítótárazásra:

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

A fentiekben hozzáadjuk a `cache` elemet a `components` tömbhöz. A `cache` elem
meghatározza, hogy a komponens `CMemCache` osztályú és `servers` tulajdonsága a
megadottak szerint legyen beállítva.

Egy komponenst a `Yii::app()->ComponentID` használatával érhetünk el, ahol is
`ComponentID` a komponens azonosítójára hivatkozik (pl.: `Yii::app()->cache`).

Egy alkalmazás komponenst letilthatunk, ha beállításakor az `enabled` tulajdonságát
`false`-ra állítjuk. Ha egy letiltott komponenst próbálunk elérni `null`-t kapunk
visszatérési értékként.

> Tip|Tipp: Alapértelmezésként a komponensek igény szerint kerülnek létrehozásra.
Ez azt jelenti, hogy egy komponens nem is jön létre egyáltalán, ha a felhasználói
kérés során nincs rá szükség. Következményképpen az átlagos teljesítmény még akkor
sem csökken, ha az alkalmazást sok komponenssel konfiguráljuk. Ugyanakkor néhány
komponenst minden esetben létre kell hozni (pl.: [CLogRouter]), függetlenül attól,
hogy hivatkozunk-e rá, vagy sem. Ennek eléréséhez felsoroljuk az azonosítóikat
az alkalmazás [preload|CApplication::preload] tulajdonságában.

Alap alkalmazás komponensek
---------------------------

Yii meghatároz néhány alap alkalmazás komponenst, biztosítandó a web alkalmazások
alap képességeit. Például a [request|CWebApplication::request] komponens a
felhasználói kérések feloldására, valamint URL és süti információk
biztosítására használatos. E komponensek tulajdonságainak beállításával
szinte minden tekintetben testreszabhatjuk Yii alap viselkedését.

Következzék a [CWebApplication] által előre meghatározott komponensek listája:

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
manages the publishing of private asset files.

   - [authManager|CWebApplication::authManager]: [CAuthManager] - manages role-based access control (RBAC).

   - [cache|CApplication::cache]: [CCache] - adat gyorstárazási lehetőséget
biztosít. Megjegyzendő: meg kell határozni a tényleges osztályát (pl.: [CMemCache],
[CDbCache]), különben `null` kerül visszaadásra a komponens elérésekor.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
kliens szkripteket kezel (JavaScript és CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
a Yii keretrendszer alap üzeneteinek lefordított változatát biztosítja.

   - [db|CApplication::db]: [CDbConnection] - adatbázis kapcsolatot biztosít.
Megjegyzendő: be kell állítani a [connectionString|CDbConnection::connectionString]
tulajdonságát, hogy használható legyen.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] -
nem kezelt PHP hibákat és kivételeket kezel.

   - [messages|CApplication::messages]: [CPhpMessageSource] -
a Yii alklamazás által használt üzenetek lefordított változatát biztosítja.

   - [request|CWebApplication::request]: [CHttpRequest] -
felhasználói kérésekkel kapcsolatos információkat biztosít.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
biztonsággal kapcsolatos szolgáltatásokat biztosít, mint pl. hash-elés, titkosítás.

   - [session|CWebApplication::session]: [CHttpSession] - munkamenet-kezeléssel
kapcsolatos képességeket biztosít.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
provides global state persistence method.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] -
URL elemzéssel és létrehozással kapcsolatos képességeket biztosít.

   - [user|CWebApplication::user]: [CWebUser] - felhasználó azonosítási információkat
képvisel.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - témákat kezel.


Alkalmazás életciklus
---------------------

Egy felhasználói kérés kezelése során az alkalmazás a következő életciklust járja be:

   0. Alkalmazás előkészítése [CApplication::preinit()] segítségével;

   1. Osztály autoloader és hibakezelés beállítása;

   2. Alap alkalmazás komponensek regsiztrálása;

   3. Alkalmazás beállítás betöltése;

   4. Alkalmazás inicializálása [CApplication::init()] segítségével
       - Alkalmazás viselkedések regisztrálása;
	   - Statikus alkalmazás komponensek betöltése;

   5. [onBeginRequest|CApplication::onBeginRequest] esemény létrehozása;

   6. Felhasználói kérés feldolgozása:
	   - a kérés feloldása;
	   - kontrolelr létrehozása;
	   - kontroller futtatása;

   7. [onEndRequest|CApplication::onEndRequest] esemény létrehozása;

<div class="revision">$Id: basics.application.txt 857 2009-03-20 17:31:09Z qiang.xue $</div>