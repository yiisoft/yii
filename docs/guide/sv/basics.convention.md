Konventioner
============

Yii föredrar konventioner före konfigurationer. Genom att följa konventioner kan 
man med Yii skapa sofistikerade applikationer utan att skriva och administrera 
komplicerade konfigurationer. Självklart kan Yii vid behov, med hjälp av 
konfigurationer, fortfarande anpassas på nästan varje tänkbart sätt.

Nedan beskrivs de konventioner som rekommenderas för programmering med Yii. För 
enkelhets skull antar vi att `WebRoot` är den katalog i vilken Yii-applikationen 
är installerad.

URL
---

Som standard, känner Yii igen URL:er på följande format:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

GET-variabeln `r` refererar till [route](/doc/guide/basics.controller#route) som 
kan fördelas av Yii till controller och action. Om `ActionID` utelämnas kommer 
kontrollern utföra sin standard-action (definierad via 
[CController::defaultAction]); om även `ControllerID` utelämnas (eller `r`-
variabeln saknas), kommer applikationen att använda standardkontrollern 
(definierad via [CWebApplication::defaultController]).

Med hjälp av [CUrlManager] går det att skapa och känna igen mer SEO-vänliga 
URL:er, som t ex `http://hostname/ControllerID/ActionID.html`. Denna finess 
beskrivs detaljerat i [URL-hantering](/doc/guide/topics.url).

Kod
---

Yii rekommenderar att namn på variabler, funktioner och klasstyper ges i 
kamelnotation dvs varje ord i namnet har inledande versal och orden skrivs ihop 
utan blanksteg. Variabel- och funktionsnamn skall ha sitt första ord helt i 
gemener för att skilja dem från klassnamn (t.ex. `$basePath`, `runController()`, 
`LinkPager`). För privata medlemsvariabler i klasser rekommenderas ett inledande 
understreck i namnet (t.ex. `$_actionList`).

Eftersom namespace inte stöds i PHP före version 5.3.0 rekommenderas att klasser 
namnges på något unikt sätt för att undvika namnkonflikt med tredjepartsklasser 
Av denna anledning namnges alla Yii-klasser med ett inledande "C"-tecken.

En speciell regel för namn på kontrollerklasser är att de måste ges suffixet 
`Controller`. En kontrollers ID definieras sedan som klassnamnet med första 
bokstaven gemen (lower case) och ordet `Controller` bortklippt. Till exempel, 
klassen `PageController` får ID:t `page`. Den här regeln gör applikationen 
säkrare. Den gör också att URL:er relaterade till kontroller får ett renare 
format (t.ex. `/index.php?r=page/index` i stället för 
`/index.php?r=PageController/index`).

Konfiguration
-------------

En konfiguration är en array bestående av nyckel-värdepar. Varje nyckel 
representerar namnet på en egenskap (property) hos objektet som skall 
konfigureras och varje värde motsvarar egenskapens initialvärde. Till exempel 
`array('name'=>'My application', 'basePath'=>'./protected')` initialiserar 
egenskaperna `name` och `basePath` med deras respektive värden av arraytyp.

Varje skrivbar egenskap hos ett objekt kan konfigureras. Utan konfiguration 
antar egenskaperna sina standardvärden. Vid konfigurering av en egenskap är det 
värt att läsa motsvarande dokumentation så att korrekt initialvärde kan 
sättas.

File
----

Konventioner för namnsättning och användning av filer beror på filtyp.

Klassfiler bör ges namn efter den publika klass de innehåller. Till exempel, 
klassen [CController] återfinns i filen `CController.php` file. En publik klass 
är en klass som kan användas av varje annan klass. Varje klassfil bör innehålla 
högst en publik klass. Privata klasser (klasser som bara används av en enda 
publik klass) kan placeras i samma fil som den publika klassen.

Filer med vyer (view files) namnges efter vyns namn. Till exempel, vyn `index` 
finns i filen `index.php`. En vyfil är ett PHP-skript innehållande HTML- och 
PHP-kod huvudsakligen ämnad för presentation.

Konfigurationsfiler kan ges valfria namn. En konfigurationsfil är ett PHP-skript 
vars enda uppgift är att returnera en associativ array representerande 
konfigurationen.

Filkatalog
----------

Yii förväntar sig en standarduppsättning kataloger för olika ändamål. Var och en 
av dem kan anpassas om så önskas.

   - `WebRoot/protected`: detta är [applikationens baskatalog](/doc/guide/basics.application#application-base-directory) 
   innehållande alla ur säkerhetssynpunkt känsliga PHP-skript och datafiler. Yii har ett standardalias `application` förknippat med denna sökväg. Denna 
   katalog och allting därunder bör skyddas från tillgång för vanliga webbanvändare. Anpassning kan ske via [CWebApplication::basePath].

   - `WebRoot/protected/runtime`: denna katalog innehåller privata tillfälliga filer, genererade vid körning av applikationen. Webbserverprocessen måste ha skrivrättighet till denna katalog. Anpassning kan ske via [CApplication::runtimePath].

   - `WebRoot/protected/extensions`: denna katalog innehåller alla tredjepartstillägg. Anpassning kan ske via [CApplication::extensionPath]. Yii har ett standardalias `ext` förknippat med denna sökväg.

   - `WebRoot/protected/modules`: denna katalog innehåller alla applikationens [moduler](/doc/guide/basics.module), var och en representerad som en underkatalog.

   - `WebRoot/protected/controllers`: denna katalog innehåller alla kontrollerklassfiler. Anpassning kan ske via [CWebApplication::controllerPath].

   - `WebRoot/protected/views`: denna katalog innehåller alla vyfiler, inklusive kontrollervyer, layoutvyer samt systemvyer. Anpassning kan ske via [CWebApplication::viewPath].

   - `WebRoot/protected/views/ControllerID`: denna katalog innehåller vyer tillhörande en specifik kontrollerklass. `ControllerID` står för kontrollerns ID. Anpassning kan ske via [CController::viewPath].

   - `WebRoot/protected/views/layouts`: denna katalog innehåller alla filer med layoutvyer. Anpassning kan ske via [CWebApplication::layoutPath].

   - `WebRoot/protected/views/system`: denna katalog innehåller alla filer med systemvyer. Systemvyer är mallar som används till att presentera exception och felmeddelanden. Anpassning kan ske via [CWebApplication::systemViewPath].

   - `WebRoot/assets`: denna katalog innehåller publicerade resursfiler (asset files), som är privata filer vilka behöver göras tillgängliga för webbanvändare. Webbserverprocessen måste ha skrivrättighet till denna katalog. Anpassning kan ske via [CAssetManager::basePath].

   - `WebRoot/themes`: denna katalog innehåller olika teman att applicera på applikationen. Varje underkatalog representerar ett specifikt tema med samma namn som underkatalogen. Anpassning kan ske via [CThemeManager::basePath].

Databas
-------

De flesta webbapplikationer använder sig av någon databas. För bästa resultat, rekommenderas 
följande namngivingskonventioner för databastabeller och -kolumner. Märk att de inte utgör 
krav från Yii:s sida.

   - Både databastabeller och -kolumner namnges med gemena (lower case).

   - Ord i ett namn separeras med understreck (underscore) t.ex. `product_order`.

   - För tabellnamn, kan antingen singularis eller pluralis användas, men inte både och. 
   För enkelhets skull rekommenderas namn i singularisform.

   - Tabellnamn kan föregås av ett gemensamt prefix så som `tbl_`. Detta är speciellt användbart 
   när tabellerna som hör till en applikation samexisterar, i samma databas, med tabeller 
   som hör till en annan applikation. De två uppsättningarna tabeller kan särskiljas genom 
   användning av olika tabellnamnprefix.



<div class="revision">$Id: basics.convention.txt 3225 2011-05-17 23:23:05Z alexander.makarow $</div>