Skapa en första Yii-applikation
===============================

För att ge en inledande erfarenhet av Yii beskrivs i detta avsnitt hur man kan skapa 
en första Yii-applikation. Vi kommer att använda `yiic` (kommandoradsverktyg)
till att skapa en ny Yii-applikation samt `Gii` (kraftfull webbaserad kodgenerator) 
till att automatisera kodgenerering för bestämda ändamål. Vi antar att `YiiRoot` 
är katalogen där Yii är installerat, samt att `WebRoot` är webbserverns rotkatalog 
för dokument. 

Kör `yiic` från en kommandorad enligt följande:

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Märk: Om `yiic` körs under Mac OS, Linux eller Unix, kan rättigheterna för 
> filen `yiic` behöva kompletteras så att den blir körbar (executable).
> Alternativt kan verktyget köras på följande sätt,
>
> ~~~
> % cd WebRoot
> % php YiiRoot/framework/yiic.php webapp testdrive
> ~~~

Detta kommer att skapa en mall till Yii-applikation under katalogen 
`WebRoot/testdrive`. Applikationen har en katalogstruktur som kommer till 
användning i de flesta Yii-applikationer. 

Utan att skriva en enda rad kod kan den första Yii-applikationen nu testköras 
genom att mata in följande URL i webbläsarens adressfält:

~~~
http://hostname/testdrive/index.php
~~~

Som nu framgår består applikationen av fyra sidor: startsidan, "om"-sidan, 
kontaktsidan och inloggningssidan. Kontaktsidan presenterar ett kontaktformulär 
som användare kan fylla i för att skicka sina förfrågningar till webbadministratören 
och inloggningssidan ger användare möjlighet att bli autentiserade för åtkomst till 
priviligierat innehåll. Se nedanstående skärmdumpar för närmare detaljer.

![Startsida](first-app1.png)

![Kontaktsida](first-app2.png)

![Kontaktsida med inmatningsfel](first-app3.png)

![Kontaktsida vid korrekt inmatning](first-app4.png)

![Inloggningssida](first-app5.png)


Följande diagram visar applikationens katalogstruktur. Se 
[Konventioner](/doc/guide/basics.convention#directory) för en detaljerad förklaring.

~~~
testdrive/
   index.php                 webbapplikationens startskript
   index-test.php            startskript för funktionell testning
   assets/                   innehåller publicerade resursfiler
   css/                      innehåller CSS-filer
   images/                   innehåller bildfiler
   themes/                   innehåller applikationsteman
   protected/                innehåller åtkomstskyddade applikationsfiler
      yiic                   yiic kommandoradsskript för Unix/Linux
      yiic.bat               yiic kommandoradsskript för Windows
      yiic.php               yiic PHP-kommandoradsskript
      commands/              innehåller egna/anpassade 'yiic'-kommandon
         shell/              innehåller egna/anpassade 'yiic shell'-kommandon
      components/            innehåller (egna) återanvändningsbara komponenter
         Controller.php      basklass för alla kontrollerklasser
         UserIdentity.php    klassen 'UserIdentity' som används för autenticering
      config/                innehåller konfigurationsfiler
         console.php         konfiguration för konsolapplikationer
         main.php            konfiguration för webbapplikationer
         test.php            konfiguration för funktionell testning
      controllers/           innehåller filer med kontrollerklasser
         SiteController.php  standardkontrollerklassen
      data/                  innehåller SQLite-databas för exempel
         schema.mysql.sql    DB-schema för MySQL-exempeldatabas
         schema.sqlite.sql   DB-schema för SQLite-exempeldatabas
         testdrive.db        SQLite-databasfil, exempeldatabas
      extensions/            innehåller tredjepartstillägg
      messages/              innehåller översatta systemmeddelanden
      models/                innehåller modellklassfiler
         LoginForm.php       modellen (av formtyp) för 'login'-åtgärden
         ContactForm.php     modellen (av formtyp) för 'contact'-åtgärden
      runtime/               innehåller tillfälliga genererade filer
      tests/                 innehåller testskript
      views/                 innehåller kontrollervy- och layoutfiler
         layouts/            innehåller layoutfiler
            main.php         standardlayout för alla vyer
            column1.php      layout för sidor som använder en kolumn
            column2.php      layout for sidor som använder två kolumner
         site/               innehåller vyfiler för 'site'-kontrollern
         	pages/           innehåller "statiska" sidor
        	   about.php     vyn för "about"-sidan
            contact.php      vyn för 'contact'-åtgärden
            error.php        vyn för 'error'-åtgärden (presenterar externa felmeddelanden)
            index.php        vyn för 'index'-åtgärden
            login.php        vyn för 'login'-åtgärden
~~~

Anslutning till databas
-----------------------

De flesta webbapplikationer backas upp av databaser. Vår testkörningsapplikation 
utgör inget undantag. För att använda en databas måste vi visa applikationen hur 
den skall göra för att ansluta. Detta gör man i applikationens konfigurationsfil 
`WebRoot/testdrive/protected/config/main.php`, så som visas nedan:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/testdrive.db',
		),
	),
	......
);
~~~

Ovanstående kod instruerar Yii om att applikationen skall ansluta till SQLite-databasen
`WebRoot/testdrive/protected/data/testdrive.db` när så erfordras. Notera att SQLite-databasen
redan är inkluderad i skelettet till applikation vi just genererat. Databasen innehåller endast
en enda tabell med namnet `tbl_user`:

~~~
[sql]
CREATE TABLE tbl_user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

Den som vill prova en MySQL-databas istället kan använda den medföljande MySQL-schemafilen 
`WebRoot/testdrive/protected/data/schema.mysql.sql` för att skapa databasen.

> Note|Märk: För att Yii:s databasfiness skall gå att använda måste PHP:s PDO-tillägg
samt det drivrutinspecifika PDO-tillägget aktiveras. För prova på-applikationen innebär 
detta att tilläggen `php_pdo` och `php_pdo_sqlite` skall vara igång.

Implementering av CRUD-operationer
----------------------------------

Nu till den roliga biten. Vi vill implementera CRUD-operationerna (create, read, 
update och delete) för tabellen `tbl_user` vi just skapat. Detta är även ett vanligt 
förekommande krav i skarpa webbapplikationer. I stället för omaket att skriva kod 
manuellt, använder vi `Gii` -- en kraftfull webbaserad kodgenerator.

> Info: Gii har varit tillgänglig sedan version 1.1.2. Innan dess kunde det tidigare 
> nämnda verktyget `yiic` användas för samma ändamål. 
> För ytterligare detaljer, hänvisas till [Implementering av CRUD-operationer med yiic shell](/doc/guide/quickstart.first-app-yiic).


### Konfigurera Gii

För att Gii skall kunna användas, behöver vi först redigera filen `WebRoot/testdrive/protected/config/main.php`, 
även känd som applikationens [konfigurationsfil](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'pick up a password here',
		),
	),
);
~~~

Gå därefter till URL:en `http://hostname/testdrive/index.php?r=gii`. 
Vid frågan om lösenord skall det i applikationens konfigurationsfil angivna lösenordet matas in.

### Generera modellen User

Efter inloggning, klicka på länken `Model Generator`. Detta förflyttar oss till följande modellgenereringssida,

![Model Generator](gii-model.png)

Mata in `tbl_user` i fältet `Table Name`. Mata in `User` i fältet `Model Class`. Klicka på `Preview`-knappen. 
Detta presenterar kodfilen som kommer att genereras. Klicka på `Generate`-knappen. En ny fil med namnet `User.php` 
kommer att genereras i katalogen `protected/models`. Som vi kommer att beskriva senare i denna guide, 
tillåter denna `User`-modell oss att, i en objektorienterad stil, kommunicera med den underliggande 
databasens tabell `tbl_user`.

### Generera CRUD-kod

När modellens klassfil har genererats skall vi generera kod som implementerar CRUD-operationer för user-data. 
Vi väljer `Crud Generator` i Gii, så som följer,

![CRUD Generator](gii-crud.png)

Mata in `User` i fältet `Model Class`. Mata in `user` (med gemena) i fältet `Controller ID`. 
Klicka på `Preview`-knappen följt av `Generate`-knappen. Genereringen av CRUD-kod är nu klar.


### Åtkomst till CRUD-sidor

Resultatet kan nu beskådas genom inmatning av URL:en:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Detta kommer att presentera en lista med poster från tabellen `tbl_user`.

Klicka på knappen `Create User` på sidan. Inloggningssidan kommer att visas (såvida 
vi inte loggat in tidigare). Efter inloggningen presenteras ett 
inmatningsformulär där en ny user-post kan läggas till. Fyll i formuläret och 
klicka på knappen `Create`. Om det förekommer något inmatningsfel kommer en 
trevlig felmeddelanderuta visas, vilken förhindrar att felaktig inmatning 
sparas. Tillbaka i listsidan skall den nyligen tillagda user-posten dyka upp i listan.

Upprepa ovanstående för att lägga till fler användare. Lägg märke till att 
listsidan automatiskt kommer att paginera user-posterna om de är för många för 
att visas på en sida.

Genom inloggning som administratör med `admin/admin`, kan user:s administrationssida visas via följande URL:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Detta presenterar user-posterna i ett trevligt tabulärt format. Sorteringskolumn 
kan väljas genom klick på respektive kolumnrubrik. Genom klick på knapparna i varje 
rad kan vi visa i formulär, uppdatera eller ta bort den motsvarande raden med data.
Vi kan översiktligt se olika sidor samt filtrera och söka efter data av intresse.

Allt detta uppnåddes utan att skriva en enda rad kod!

![User-administreringssida](first-app6.png)

![Skapa ny user-sida](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>