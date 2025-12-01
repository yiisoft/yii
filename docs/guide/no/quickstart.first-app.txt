Lag din første Yii applikasjon
==============================


For å få et førsteinntrykk av Yii skal vi i denne delen beskrive hvordan vi lager
vår første Yii applikasjon. Vi skal bruke det kraftige verktøyet `yiic` som kan
brukes til å automatisere generering av kode til forskjellige oppgaver. For
enkelhets skyld antar vi at katalogen `YiiRoot` er samme katalogen der Yii
er installert, og `WebRoot` er rot-katalogen til webtjeneren.

Kjør `yiic` fra ledetekst som følger:
~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Merk: Når `yiic` kjøres på Mac OS, Linux eller Unix, må du kanskje
> endre rettigheter for `yiic` filen så den blir eksekverbar.
> Alternativt kan du kjøre verktøyet på følgende måte,
>
> ~~~
> % cd WebRoot/testdrive
> % php YiiRoot/framework/yiic.php webapp WebRoot/testdrive
> ~~~

Dette vil opprette et skall av en Yii applikasjon under katalogen
`WebRoot/testdrive`. Applikasjonen vil ha en katalogstruktur som trengs
av de fleste Yii applikasjoner.

Uten å skrive en eneste linje kode kan vi nå teste vår første Yii
applikasjon ved å gå til følgende link i en nettleser:

~~~
http://hostname/testdrive/index.php
~~~

Som vi kan se har applikasjonen fire sider: hjem, om, kontakt og login. Kontaktsiden viser
et kontaktskjema som brukere kan fylle ut for å sende forespørsler til webmaster, 
og login siden lar brukere autentisere seg før de får tilgang til priviligert
innhold. Se følgende skjermbilder for flere detaljer.

![Home page](first-app1.png)

![Contact page](first-app2.png)

![Contact page with input errors](first-app3.png)

![Contact page with success](first-app4.png)

![Login page](first-app5.png)


Det følgende diagrammet viser katalogstrukturen for applikasjonen vår. 
Se [Konvensjoner](/doc/guide/basics.convention#directory) for en detaljert
forklaring av denne strukturen.

~~~
testdrive/
   index.php                 Webapplikasjonens inngangspunkt
   index-test.php            inngangspunkt for funksjonelle tester
   assets/                   inneholder published resource files
   css/                      inneholder CSS filer
   images/                   inneholder bilder
   themes/                   inneholder tema for applikasjonen
   protected/                inneholder beskyttede applikasjonsfiler
      yiic                   yiic kommandolinje skript for Unix/Linux
      yiic.bat               yiic kommandolinje skript for Windows
      yiic.php               yiic kommandolinje skript PHP skript
      commands/              inneholder egendefinerte 'yiic' kommandoer
         shell/              inneholder egendefinerte 'yiic shell' kommandoer
      components/            inneholder gjennbrukbare brukerkonponenter
         Controller.php      baseklassen for alle kontrollerklasser
         Identity.php        'Identity' klasse brukt for autentikasjon
      config/                inneholder konfigurasjonsfiler
         console.php         konfigurasjon for terminal-applikasjonen
         main.php            konfigurasjon for web applikasjonen
         test.php            konfigurasjon for de funksjonelle testene
      controllers/           inneholder kontroller klassefiler
         SiteController.php  standard kontroller klasse
      data/                  inneholder eksempel database
         schema.mysql.sql    DB skjema for MySQL database eksempel
         schema.sqlite.sql   DB skjema for SQLite database eksempel
         testdrive.db        SQLite database eksempelsfil
      extensions/            inneholder tredjeparts utvidelser
      messages/              inneholder oversatte meldinger og beskjeder
      models/                inneholder modell-klassefiler
         LoginForm.php       skjema-modellen for 'login' handlingen
         ContactForm.php     skjema-modellen for 'contact' handlingen
      runtime/               inneholder middlertidig genererte filer
      tests/                 inneholder test skript
      views/                 inneholder kontrollersider ("view") og layout filer
         layouts/            inneholder layout sider ("view") filer
            main.php         grunnleggende layout delt for alle sider
            column1.php      layout for sider med en enkelt kolonne
            column2.php      layout for sider med to kolonner
         site/               inneholder sider ("view") for kontrolleren 'site'
       	 pages/              inneholder "statiske" sider
            about.php        inneholder side ("view") for "about" siden
            contact.php      inneholder side ("view") for 'contact' handlingen
            error.php        inneholder side ("view") for 'error' handlingen (viser eksterne feil)
            index.php        inneholder side ("view") for 'index' handlingen
            login.php        inneholder side ("view") for 'login' handlingen
~~~

Koble til en database
----------------------

De fleste webapplikasjoner er drevet av en database. Vår test-drive applikasjon er 
ikke et unntak. for å bruke en database må vi fortelle applikasjonen hvordan den skal koble seg til databasen.
Dette er gjort i applikasjonens konfigurasjonsfil `WebRoot/testdrive/protected/config/main.php`,
uthevet som følger,

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

Koden over forteller Yii at applikasjonen skal koble til SQLite databasen
`WebRoot/testdrive/protected/data/testdrive.db` når det trengs. Merk at SQLite databasen
er allerede inkludert i applikasjonsskallet som vi netopp genererte. Databasen inneholder kun en enkelt
tabell som heter `tbl_user`:

~~~
[sql]
CREATE TABLE tbl_user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

Om du ønsker å prøve en MySQL database i steden så kan du bruke den inkluderte MySQL skjema filen
`WebRoot/testdrive/protected/data/schema.mysql.sql` for å opprette databasen.

> Note|Merk: For å bruke Yii's databasefunksjonalitet så må vi aktivere PHP PDO tillegget og det
driver-spesifikke PDO-tillegget. For test-drive applikasjonen må vi skru på både  `php_pdo` og `php_pdo_sqlite`
tilleggene.



Implementere typiske dataoperasjoner
----------------------------
Nå begynner moroa. Vi vil nå implementere typiske dataoperasjoner
(opprett, les, oppdater og slett) for  `User` tabellen som vi netopp laget.

Dette er også ofte nødvendig i praktiske applikasjoner. I stedet for å ta seg bryet med
å skrive selve koden, vil vi bruke Gii - en kraftig web-basert kode generator.

> Info: Gii har vært tilgjengelig siden versjon 1.1.2. For tidligere versjoner kan vi bruke `yiic` verktøyet for å oppnå samme mål. For mer informasjon, se [Implementere dataoperasjoner med yiic skjell](/doc/guide/quickstart.first-app-yiic).

### Konfigurere Gii

For å bruke Gii vi først må redigere filen `WebRoot/testdrive/protected/config/main.php`, som er kjent som [applikasjonsens konfigurasjonsfil](/doc/guide/basics.application#application-configuration):


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

Deretter kan du gå til nettadressen `http://hostname/testdrive/index.php?r=gii`. Du vil bli spurt om et passord, som skal være det vi skrev inn i ovennevnte programkonfigurasjonsfil.

### Generere User Modell


Etter innlogging, klikk på linken `Model Generator`. Dette vil bringe oss til den følgende modell-generasjon siden,

![Model Generator](gii-model.png)

I `Table Name` feltet, skriv `tbl_user`. I `Model Class` feltet skriver `User`. Trykk deretter på `Preview`-knappen. Dette vil vise oss den nye kodefilen som skal genereres. Trykk på `Generate`-knappen. En ny fil med navnet `User.php` vil bli generert under `protected/models`. Som vi vil forklare senere i denne veiledningen kan denne `User` modell-klassen hjelpe oss å snakke med den underliggende databasen og tabellen `tbl_user` på en objektorientert måte.

### Generere kode for dataoperasjoner

Når du har opprettet klassefilen for modellen vil vi generere kode som implementerer dataoperasjonene for brukerdataen. Vi velger `Crud Generator` i Gii, som følger,


![CRUD Generator](gii-crud.png)

I `Model Class` feltet, skriv `User`. I `Controller ID` feltet, skriv `user` (med små bokstaver). Trykk `Preview`-knappen etterfulgt av `Generer`-knappen. Vi er nå ferdig med kodegenerering for dataoperasjoner.


### Aksessering av CRUD Pages

La oss nyte vårt arbeid ved å surfe på følgende URL:
~~~
http://hostname/testdrive/index.php?r=user
~~~

Dette viser en liste over brukere i `tbl_user` tabellen.

Klikk på `Create User`-knappen på siden. Vi vil bli brakt til login- siden
dersom vi ikke har logget inn før. Etter login ser vi
en inndata skejma som lar oss å legge til en ny bruker. Fyll ut skjemaet og
klikk på `Create`-knappen. Hvis det er noen inndatafeil vil en fin en feilmelding dukke
opp som hindrer oss fra å lagre oppføringen. Tilbake i siden med brukerlisten bør vi se den nye brukeren i listen

Gjenta trinnene over for å legge til flere brukere. Legg merke til at siden med brukerlisten
vil automatisk lage navigerbare sider om det er for mange brukere i listen til å vises på en side. 

Om vi logger på som en administrator-bruker med `admin / admin`, kan vi vise brukeren
adminsidene med følgende URL:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Dette vil vise oss brukerene i en fin tabell. Vi kan klikke på tabellens første celle for å sortere de tilsvarende kolonnene.
Vi kan klikke på knappene på hver rad med data for å vise, oppdatere eller slette den tilsvarende raden med data.
Vi kan bla til forskjellige sider, og vi kan også filtrere og søke å se etter dataene vi er interessert i.

Alle disse fine funksjonene kommer uten at vi skal skrev en eneste linje kode!

![User admin page](first-app6.png)

![Create new user page](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 2125 2010-05-11 23:36:02Z alexander.makarow $</div>
