MVC - bästa tillvägagångssätt
=============================

Även om nästan alla webbutvecklare är bekanta med termen Model-View-Controller (MVC), 
undgår det fortfarande många hur MVC korrekt skall användas i faktisk applikationsutveckling. 
Den centrala idén bakom MVC är **återanvändning av kod samt separation av angelägenheter**. 
I detta avsnitt kommer att ges några generella riktlinjer om hur man bättre kan följa MVC vid 
utveckling av Yii-applikationer.

För att bättre förklara dessa riktlinjer, antar vi att en webbapplikation består av flera 
underapplikationer, så som 

* förgrund (front end): en publikt tillgänglig webbplats för vanliga användare;
* bakgrund (back end): en webbplats som tillgängliggör funktionalitet for administrering av applikationen. 
Denna är vanligen endast åtkomlig för administratörspersonal;
* console: en applikation bestående av konsolkommandon som körs i ett terminalfänster eller 
som schemalagda jobb, till stöd för hela applikationen;
* Web API: erbjuder programmeringsgränssnitt åt tredje part för integrering med applikationen.

Underapplikationerna kan implementeras i form av [moduler](/doc/guide/basics.module), eller som 
en Yii-applikation som delar viss kod med andra underapplikationer.


Modell (model)
--------------

[Modeller](/doc/guide/basics.model) representerar den underliggande datastrukturen för en webbapplikation. 
Modeller delas ofta mellan skilda underapplikationer till en webbapplikation. Till exempel kan en 
`LoginForm`-modell användas både av förgrunds- och bakgrundsdelen av en applikation; en `News`-modell 
skulle kunna användas av konsolkommandon såväl som webb-API:er som förgrunds-/bakgrundsdelarna av en 
applikation. Detta medför att modeller:

* bör innehålla propertyn för representation av specifik data;

* bör innehålla affärslogik (t.ex. valideringsregler) som säkerställer att representerad data uppfyller 
konstruktionskraven;

* kan innehålla kod för bearbetning av data. Till exempel kan en `SearchForm`-modell, 
förutom att representera inmatning av sökriterier, även innehålla en `search`-metod 
för implementering av själva sökningen.

Ibland kan tillämpning av föregående regel göra en modell mycket omfångsrik (fat), med alltför mycket 
kod i en enda klass. Det kan även medföra att en modell blir svår att underhålla om koden den innehåller 
är avsedd för olika ändamål. Till exempel kan en `News`-modell innehålla en metod `getLatestNews`, 
som endast används av applikationens förgrundsdel; den kan även innehålla en metod `getDeletedNews`, 
som endast används av bakgrundsdelen. Detta kan vara helt i sin ordning för små till medelstora 
applikationer. För stora applikationer kan följande strategi användas för att göra modeller lättare 
att underhålla:

* Definiera en modellklass `NewsBase`, som bara innehåller kod som delas av olika underapplikationer 
(t.ex. förgrund, bakgrund);

* Definiera i varje underapplikationon en modell `News`, vilken ärver från `NewsBase`. Placera all kod 
som är specifik för underapplikationen i denna `News`-modell.

Om vi tillämpar denna strategi på vårt exempel ovan, skulle vi i förgrundsapplikationen lägga till en 
`News`-modell, endast innehållande `getLatestNews`-metoden, samt i bakgrundsapplikationen lägga till 
en annan `News`-modell, endast innehållande `getDeletedNews`-metoden.

Rent allmänt bör modeller inte innehålla logik som direkt interagerar med användare. Mer specifikt gäller att:

* modeller inte bör använda `$_GET`, `$_POST`, eller andra liknande variabler som har direkt koppling till 
användarens request. Kom ihåg att en modell kan komma att användas av en helt annorlunda underapplikation 
(t.ex. enhetstest, webb-API) som kanske inte använder dessa variabler för att representera användarens request. 
Dessa variabler som hänför sig till användarens request bör hanteras av kontrollern.

* modeller bör undvika innehåll som HTML eller annan presentationsrelaterad kod. Eftersom presentationsrelaterad 
kod varierar beroende på användarens krav (t.ex. kan förgrunds- respektive bakgrundsdelen 
presentera nyhetsdetaljer i helt olika format), tas detta bättre om hand av vyer.


Vy (view)
---------

[Vyer](/doc/guide/basics.view) har till uppgift att presentera modeller i det format användarna önskar. 
Allmänt gäller för vyer att de:

* i huvudsak bör innehålla presentationsrelaterad kod, så som HTML samt okomplicerad PHP-kod för traversering, 
formatering och återgivning (render) av data;

* bör undvika innehåll som utför uttryckliga databasfrågor. Sådan kod är mer lämpad för placering i modeller.

* bör undvika att direkt referera till `$_GET`, `$_POST`, eller andra liknande variabler som representerar 
användarens request. Detta är kontrollerns uppgift. Vyn bör fokusera på presentation och utformning av data som 
kontrollern och/eller modellen förser den med, men inte söka direkt tillgång till request-variabler eller databasen.

* kan referera direkt till propertyn och metoder i kontroller och modeller. Dock bör detta ske endast för presentationsändamål.


Vyer kan återanvändas på olika sätt:

* Layout: gemensamma presentationsrelaterade områden (t.ex. sidrubrik, sidfot) kan placeras i en layoutvy.

* Partiella vyer: använd partiella vyer (vyer som inte utsmyckas med layout) för att återanvända fragment av 
presentationsrelaterad kod. Till exempel används den partiella vyn `_form.php` för rendering av 
inmatningsfomuläret som både används för sidor som skapar såväl som uppdaterar modell.

* Widgetar: om en hel del logik krävs för att presentera en partiell vy kan denna göras om till en widget, 
vars klassfil är ett lämpligt ställe att placera sådan logik. I fråga om widgetar som genererar mängder av 
HTML-kod (markup), är det bäst att använda widget-specifika vyfiler för sådant innehåll.

* Hjälpklasser: i vyer behöver vi ofta kodfragment för mindre uppfifter som att formatera data eller 
generera HTML-taggar. En bättre lösning än att placera denna kod direkt i vyfiler, är att placera alla dessa 
kodfragment i hjälpklasser ämnade för vyer. Därefter är det bara att använda hjälpklassen direkt i vyfilerna. 
Yii innehåller exempel på detta tillvägagångssätt i form av en kraftfull hjälpklass, [CHtml], som kan 
producera vanligtvis använd HTML-kod. Hjälpklasser kan placeras i en [automatiskt laddad katalog](/doc/guide/basics.namespace) 
så att de kan användas utan uttrycklig inkludering av klassen.


Kontroller (controller)
-----------------------

[Kontroller](/doc/guide/basics.controller) är limmet som sammanfogar modeller, vyer och andra komponenter till en körbar 
applikation. Kontroller har till uppgift att direkt handskas med användarens request. Därför gäller för kontroller att de: 

* kan använda sig av `$_GET`, `$_POST` och andra PHP-variabler som representerar användarens request;

* kan instantiera modeller och förvalta deras livscykel. Till exempel, i en typisk uppdateringsåtgärd (action) 
för en modell, kan kontrollern först instantiera modellen; därefter förse den med innehåll från användarens 
inmatning, via `$_POST`; för att sedan, efter att framgångsrikt ha sparat modellen omdirigera webbläsaren 
till sidan som presenterar modellens detaljer. Märk att den faktiska implementeringen av kod för att spara 
en modell bör placeras i modellen i stället för i kontrollern.

* bör undvika inbäddade SQL-satser, vilka det är bättre att hålla inom modeller.

* bör undvika innehåll som HTML eller någon annan presentationsrelaterad markup. Sådant är det lämpligare att hålla i vyer.


I en väl utformad MVC-applikation är kontroller ofta mycket tunna, innehållande endast några tiotal rader kod; 
modeller är däremot betydligt tjockare, innehållande det mesta av koden som har till uppgift att representera 
och manipulera data. Detta beror på att datastrukturen och affärslogiken som modeller representerar vanligen är 
mycket specifika för den aktuella applikationen och behöver långtgående anpassning för att motsvara de specifika 
kraven på applikationen, medan kontrollerlogik ofta följer ungefär samma mönster i olika applikationer och därför 
mycket väl kan förenklas av det underliggande ramverket eller basklasser.


<div class="revision">$Id: basics.best-practices.txt 2795 2010-12-31 00:22:33Z alexander.makarow $</div>