Konsolapplikationer
===================

Konsolapplikationer används huvudsakligen till att utföra offline-arbete 
åt uppkopplade (online) webbapplikationer, så som kodgenerering, 
kompilering av sökindex, skicka mail, etc. Yii erbjuder ett ramverk för 
framställning av konsolapplikationer på ett objektorienterat och systematiskt sätt.
Det ger konsolapplikationer möjlighet att använda sig av resurser (t.ex. DB-anslutningar) 
som används av en uppkopplad webbapplikation.

Översikt
--------

Yii representerar varje konsoluppgift i termer av ett [kommando|CConsoleCommand]. 
Ett konsolkommando skrivs i form av en klass som ärver från och utvidgar [CConsoleCommand].

När vi använder verktyget `yiic webapp` till att initialt skapa skelettet till 
en Yii-applikation, kan vi finna två filer i katalogen `protected`:

* `yiic`: det exekverbara skriptet som används för Linux/Unix;
* `yiic.bat`: den exekverbara batchfilen som används för Windows.

I ett kommandoradsfönster kan vi mata in följande kommandon:

~~~
cd protected
yiic help
~~~

Detta kommer att presentera en lista med tillgängliga konsolkommandon. Som standard, 
omfattar de tillgängliga kommandona de som Yii-ramverket tillhandahåller 
(benämnda **system commands**) samt de som utvecklats av användare för enskilda 
applikationer (benämnda **user commands**).

För att se hur ett kommando används kan vi exekvera

~~~
yiic help <command-name>
~~~

Och för att köra ett kommando kan följande kommandoformat användas:

~~~
yiic <command-name> [parameters...]
~~~


Skapa kommandon
---------------

Konsolkommandon lagras som klassfiler under katalogen som specificeras av 
[CConsoleApplication::commandPath]. Som standard innebär detta katalogen 
`protected/commands`.

En konsolkommandoklass måste ärva från och utvidga [CConsoleCommand]. 
Klassnamnet måste ha formatet `XyzCommand`, där `Xyz` refererar till kommandots 
namn, med första bokstaven som versal. Till exempel, ett `sitemap`-kommando måste
använda klassnamnet `SitemapCommand`. Namn på konsolkommandon är skiftlägesberoende.

> Tip|Tips: Genom konfigurering av [CConsoleApplication::commandMap] kan man 
> även ha kommandoklasser med andra namngivningskonventioner och placerade i andra 
> kataloger.

När ett nytt kommando skapas behöver man ofta åsidosätta metoden [CConsoleCommand::run()] 
alternativt utveckla en eller flera kommandoåtgärder (actions) 
(förklaras i nästa avsnitt).

Nör ett konsolkommando exekveras, kommer metoden [CConsoleCommand::run()] att anropas 
av konsolapplikationen. Eventuella parametrar till konsolkommandot vidarebefordras 
även de till metoden, som har följande signatur:

~~~
[php]
public function run($args) { ... }
~~~

där `$args` refererar till de extra parametrarna givna från kommandoraden.

Inuti ett konsolkommando kan vi använda `Yii::app()` för tillgång till konsolapplikationens 
instans och därigenom även få tillgång till resurser så som databasanslutningar
(t.ex. `Yii::app()->db`). Som framgått är användningen mycket snarlik det vi kan göra 
i en webbapplikation.

> Info: Med start från version 1.1.1, är det även möjligt att skapa globala kommandon 
som delas av **alla** Yii-applikationer på samma maskin. För att åstadkomma detta, 
definiera en miljödatavariabel benämnd `YII_CONSOLE_COMMANDS` som pekar ut en 
befintlig filkatalog. Därefter kan våra globala kommandons klassfiler lagras i 
denna katalog.


Konsolkommandoåtgärd
--------------------

> Note|Märk: Finessen konsolkommandoåtgärd har varit tillgänglig sedan version 1.1.5.

Ett konsolkommando behöver ofta hantera olika kommandoradsparametrar, vissa 
obligatoriska, andra frivilliga. Ett konsolkommando kan också behöva tillhandahålla 
ett flertal underkommandon för hantering olika underordnade uppgifter. Sådant arbete 
kan förenklas genom användning av konsolkommandoåtgärder.

En konsolkommandoåtgärd (action) är en metod i en konsolkommandoklass.
Metodens namn måste ha formatet `actionXyz`, där `Xyz` refererar till åtgärdsnamnet 
med första bokstaven som versal. Till exempel metoden `actionIndex` definierar en 
åtgärd med namnet `index`.

För att exekvera en specifik åtgärd används följande konsolkommandoformat:

~~~
yiic <command-name> <action-name> --option1=value --option2=value2 ...
~~~

De tillkommande alternativ-värdeparen kommer lämnas med som namngivna parametrar 
till åtgärdsmetoden. Värdet för ett alternativ `xyz` propageras som `$xyz`-parametern 
i åtgärdsmetoden. Till exempel, om vi definierar följande kommandoklass:

~~~
[php]
class SitemapCommand extends CConsoleCommand
{
    public function actionIndex($type, $limit=5) { ... }
    public function actionInit() { ... }
}
~~~

Följande konsolkommandon kommer alla att leda till metodanropet `actionIndex('News', 5)`:

~~~
php entryScript.php sitemap index --type=News --limit=5

// $limit erhåller standardvärde
yiic sitemap index --type=News

// $limit erhåller standardvärde
// eftersom 'index' är standardåtgärd kan åtgärdsnamnet utelämnas
yiic sitemap --type=News

// alternativens ordning saknar betydelse
yiic sitemap index --limit=5 --type=News
~~~

Om ett alternativ ges utan värde (t.ex. `--type` i stället för `--type=News`),  
förmodas att motsvarande åtgärdsparameter skall ges värdet boolean `true`.

> Note|Märk: Variationer i alternativens format så som 
> `--type News`, `-t News` stöds inte.

En parameters värde kan vara av typen array om den deklareras med typledtråd:

~~~
[php]
public function actionIndex(array $types) { ... }
~~~

Faktiskt arrayinnehåll levereras genom att samma alternativ upprepas på kommandoraden:

~~~
yiic sitemap index --types=News --types=Article
~~~

Ovanstående kommando leder till metodanropet `actionIndex(array('News', 'Article'))`.


Med start från version 1.1.6, stöder Yii även användning av anonyma åtgärdsparametrar och 
globala alternativ.

Anonyma parametrar refererar till de kommandoradsparametrar som inte har formen av alternativ.
Till exempel, i ett kommando `yiic sitemap index --limit=5 News`, har vi en anonym parameter 
vars värde är `News` medan den namngivna parametern `limit` erhåller värdet 5.

För att kunna använda anonyma parametrar måste en kommmandoåtgärd deklarera en parameter med 
namnet `$args`. Till exempel,

~~~
[php]
public function actionIndex($limit=10, $args=array()) {...}
~~~

Vektorn `$args` kommer att innehålla alla tillgängliga värden av anonyma parametrar.

Globala alternativ (options) refererar till de kommandoradsalternativ som delas av alla åtgärder 
i ett kommando. Till exempel, kan vi önska att samtliga åtgärder i ett kommando som innehåller 
flera åtgärder får del av alternativet `verbose`. Även om vi skulle kunna deklarera parametern 
`$verbose` i varje åtgärdsmetod, är det bättre att deklarera den som en **'public' medlemsvariabel** 
i kommandoklassen, vilket förvandlar `verbose` till ett globalt alternativ:

~~~
[php]
class SitemapCommand extends CConsoleCommand
{
	public $verbose=false;
	public function actionIndex($type) {...}
}
~~~

Ovanstående kod tillåter oss att exekvera ett kommando med ett `verbose`-alternativ:

~~~
yiic sitemap index --verbose=1 --type=News
~~~


Anpassa konsolapplikation
-------------------------

I en applikation som skapats med hjälp av verktyget `yiic webapp` tool, kommer 
konfigurationsfilen för konsolapplikation att vara `protected/config/console.php`. 
Precis som konfigurationsfilen för en webbapplikation, är denna fil ett PHP-skript 
vilken returnerar en vektor som utgör initialvärden för propertyn i en konsolapplikations 
instans. Resultatet är att varje publik propertymedlem i [CConsoleApplication] kan konfigureras 
i denna fil.

Eftersom konsolkommandon ofta skapas för att betjäna en webbaplikation, behöver de ha tillgång 
till resurser (så som DB-anslutningar) vilka används av den senare. Detta kan vi åstadkomma 
i konsolapplikationens konfigurationsfil på följande sätt:

~~~
[php]
return array(
	......
	'components'=>array(
		'db'=>array(
			......
		),
	),
);
~~~

Som synes är konfigurationsformatet mycket likt det vi använder i en webbapplikations 
konfiguration. Detta beror på att både [CConsoleApplication] och [CWebApplication] delar 
samma basklass.


<div class="revision">$Id: topics.console.txt 2867 2011-01-15 10:22:03Z haertl.mike $</div>