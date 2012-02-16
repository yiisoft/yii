Säkerhet
========

Förhindra webbplatsöverskridande skriptning 
------------------------------------------- 

Begreppet Cross-site Scripting (även känt som XSS) avser att en 
webbapplikation kan samla in illasinnad data från en användare. Ofta injicerar 
angripare JavaScript, VBScript, ActiveX, HTML, eller Flash i en sårbar 
applikation för att lura andra användare av applikationen och samla in data från 
dem. Till exempel kan ett bristfälligt utformat forumsystem, utan någon 
säkerhetskontroll, presentera användarinmatningar i forumpostningar. En 
angripare kan sedan injicera ett stycke skadlig JavaScriptkod i en postning med 
konsekvensen att när andra användare läser postningen kommer JavaScriptkoden att 
köras oförmodat på deras datorer.

En av de mest angelägna motåtgärderna för att förebygga XSS-attacker är att 
kontrollera användarinmatningar innan de presenteras. Man kan HTML-inkoda 
användarinmatningen för att uppnå detta mål. Emellertid, i vissa situationer är 
HTML-inkodning inte att föredra eftersom den blockerar alla sorters HTML-taggar.

Yii inkorporerar arbeten från [HTMLPurifier](http://htmlpurifier.org/) och 
erbjuder utvecklare en användbar komponent, [CHtmlPurifier], som kapslar in 
[HTMLPurifier](http://htmlpurifier.org/). Denna komponent är kapabel att 
avlägsna all skadlig kod med en omsorgsfullt granskad, säker men ändå tillåtande 
acceptanslista (whitelist) samt säkerställer att det filtrerade innehållet 
följer standard.

Komponenten [CHtmlPurifier] kan användas antingen som en 
[widget](/doc/guide/basics.view#widget) eller ett 
[filter](/doc/guide/basics.controller#filter). När den används som en widget, 
städar [CHtmlPurifier] upp innehåll som presenteras inom densamma i en vy. Till 
exempel,

~~~
[php]
<?php $this->beginWidget('CHtmlPurifier'); ?>
...display user-entered content here...
<?php $this->endWidget(); ?>
~~~


Förhindra webbplatsöverskridande request-förfalskning
-----------------------------------------------------

Begreppet Cross-Site Request Forgery (CSRF) innebär attacker där en illasinnad 
webbplats får en användares webbläsare att utföra en oönskad åtgärd på en 
betrodd webbplats. Till exempel kan en illasinnad webbplats ha en sida som 
innehåller en bild-tagg vars `src` pekar till en internetbank: 
`http://bank.example/uttag?överför=10000&till=någon`. Om en användare som har en 
inloggningscookie till nämnda internetbank råkar besöka denna illasinnade webbplats, 
kommer åtgärden att överföra 10000 kronor till "någon" att utföras. I motsats 
till tidigare nämnda XSS, som utnyttjar en användares förtroende för en viss webbplats, 
utnyttjar CSRF det förtroende en webbplats har för en viss användare.

För att förhindra CSRF-attacker, är det viktigt att hålla sig till regeln att 
`GET`-request endast skall tillåtas att hämta data, inte att modifiera data på 
servern. I fråga om `POST`-request, skall de inkludera något slumpgenererat 
värde som servern kan känna igen som bekräftelse på att formuläret skickas in 
från och resultatet sänds tillbaka till, samma källa (motpart).

Yii implementerar ett schema som hjälper till att skydda mot `POST`-baserade 
CSRF-attacker. Det är baserat på att ett slumpgenererat värde lagras i en cookie 
samt jämförelse av detta värde med det värde som skickas med i en `POST`-request.

Som standard är CSRF-skyddet avstängt. För att slå på det, konfigurera 
applikationskomponenten [CHttpRequest] i 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration) 
som följer,

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCsrfValidation'=>true,
		),
	),
);
~~~

och vid presentation av ett formulär, anropa [CHtml::form] i stället för att 
skriva en HTML-formulärtagg direkt. Metoden [CHtml::form] bäddar in det 
erforderliga randomvärdet i ett dolt fält så att det kan skickas in för CSRF-
validering.


Förhindra Cookie-attacker
-------------------------

Det är mycket viktigt att skydda cookie från att attackeras, då sessions-ID ofta 
lagras i sådana. Om någon får tag på ett sessions-ID, kan denne i princip agera 
som ägare till all relevant sessionsinformation.

Det finns åtskilliga motåtgärder för att skydda mot attacker riktade mot cookie.

* En applikation kan använda SSL till att skapa en säker kommunikationskanal och 
endast skicka autentiseringscookie över en HTTPS-anslutning. Angripare förhindras 
att dechiffrera innehållet i överförda cookie. 

* Låt sessioner upphöra efter inaktivitet av lämplig varaktighet, inklusive alla 
cookie och sessionssymboler (session tokens), för att minska sannolikheten för attacker. 

* Förhindra webbplatsöverskridande skriptning som kan leda till att godtycklig 
kod körs i användarens webbläsare och cookieinnehåll röjs. 

* Validera cookiedata och detektera om de har ändrats.

Yii implementerar ett cookievalideringsschema som förhindrar cookie från att 
modifieras. Mer specifikt utför det HMAC-kontroll (Hash Message Authentication 
Code) av cookievärden, om validering av cookie är påslagen.

Cookievalidering är avstängd som standard. För att slå på den, konfigurera 
applikationskomponenten [CHttpRequest] i 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration) 
på följande vis,

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCookieValidation'=>true,
		),
	),
);
~~~

För att dra nytta av de cookievalideringsscheman Yii erbjuder måste vi också 
hantera cookie genom [cookies|CHttpRequest::cookies]-samlingen, i stället för 
direkt genom `$_COOKIES`:

~~~
[php]
// hämta cookien med det angivna namnet
$cookie=Yii::app()->request->cookies[$name];
$value=$cookie->value;
......
// skicka en cookie
$cookie=new CHttpCookie($name,$value);
Yii::app()->request->cookies[$name]=$cookie;
~~~


<div class="revision">$Id: topics.security.txt 2535 2010-10-11 08:28:08Z mdomba $</div>