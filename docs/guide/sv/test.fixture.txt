Definiera fixturer
==================

Automatiserade tester behöver exekveras många gånger. För att garantera att 
testningsprocessen är repeterbar vill vi kunna köra testerna i ett känt 
tillstånd, benämnt *fixtur*. Till exempel, för att testa att skapa poster i en 
bloggapplikation bör, varje gång vi kör testerna, tabellerna som lagrar 
relevanta data om postningar (t.ex. tabellen `Post`, tabellen `Comment`) 
återställas till något bestämt tillstånd. [PHPUnit-dokumentationen](http://www.phpunit.de/manual/current/en/fixtures.html) 
har en bra beskrivning av fixturpreparering i allmänhet. I detta avsnitt 
kommer vi att beskriva hur man sätter upp databasfixturer.

Att preparera databasfixturer är kanske den mest tidsödande delarna inom 
testning av databasassisterade webbapplikationer. Yii introducerar 
applikationskomponenten [CDbFixtureManager] för att mildra problemet. Den utför 
i grunden följande åtgärder när man kör en uppsättning  tester:

 * Innan testerna körs återställer den alla för testerna relevanta tabeller till ett känt tillstånd.
 * Innan en enskild testmetod körs, återställer den de specificerade tabellerna till ett känt tillstånd.
 * Under exekveringen av en testmetod erbjuder den tillgång till dataposterna som ingår i fixturen.

För att använda [CDbFixtureManager] behöver den konfigureras i [applikationskonfigurationen](/doc/guide/basics.application#application-configuration) 
på följande sätt,

~~~
[php]
return array(
	'components'=>array(
		'fixture'=>array(
			'class'=>'system.test.CDbFixtureManager',
		),
	),
);
~~~

Därefter gör vi fixturdata tillgängligt under katalogen `protected/tests/fixtures`. 
Denna katalog kan anpassas till någon annan katalog genom konfigurering av 
propertyn  [CDbFixtureManager::basePath] i applikationskonfigurationen. 
Fixturdata är organiserat som en samling av PHP-filer benämnda fixturfiler. 
Varje fixturfil returnerar en array som representerar initialt existerande dataposter 
för en specifik tabell. Filnamnet är samma som tabellnamnet. Följande är ett exempel på 
fixturdata för tabellen `Post`, lagrat i en fil med namnet `Post.php`:

~~~
[php]
<?php
return array(
	'sample1'=>array(
		'title'=>'test post 1',
		'content'=>'test post content 1',
		'createTime'=>1230952187,
		'authorId'=>1,
	),
	'sample2'=>array(
		'title'=>'test post 2',
		'content'=>'test post content 2',
		'createTime'=>1230952287,
		'authorId'=>1,
	),
);
~~~

I exemplet ovan returneras två rader av data . Varje rad reperesenteras av en 
associativ array vars nycklar är kolumnnamn och vars värden är motsvarande 
kolumnvärden. Vidare är varje rad indexerad av en sträng (t.ex. `sample1`, `sample2`), 
kallad *rad-alias*. När vi senare skriver testskript kan vi bekvämt referera till en rad 
genom dess alias. Detta kommer att beskrivas i detalj i nästa avsnitt.

Värt att notera är att vi inte specificerar värden för `id`-kolumnen i 
ovanstående fixtur. Detta beror på att `id`-kolumnen är definierad som en 
automatiskt uppräknande primärnyckel vars värden kommer att fyllas i när vi 
sätter in nya rader.

När [CDbFixtureManager] refereras till för första gången, går den igenom varje 
fixturfil och återställer med ledning av denna motsvarande tabell. En tabell 
återställs genom trunkering, vilket återställer värdesekvensen för den 
autoinkrementella primärnyckeln. Därefter sätts raderna med fixturdata in i 
tabellen.

Ibland vill vi inte återställa varje tabell som har en fixturfil innan vi kör en 
uppsättning tester, eftersom det kan ta lång tid att återställa alltför många 
fixturfiler. I et sådant fall kan vi skriva ett PHP-skript som utför 
initialiseringsarbetet på ett anpassat sätt. Detta skript skall sparas i en fil 
med namnet `init.php` i samma katalog som innehåller övriga fixturfiler. När 
[CDbFixtureManager] upptäcker att detta skript existerar kommer den att köra 
skriptet i stället för att återsälla varje tabell.

Det är också möjligt att vi inte är nöjda med det underförstådda sättet att 
återställa en tabell, dvs att trunkera den samt sätta in fixturdata. I ett 
sådant fall kan vi skriva ett initialiseringsskript för en specifik fixturfil. 
Skriptet måste namnges som tabellnamnet följt av `.init.php`. Till exempel 
skulle initialiseringsskriptet för tabellen `Post` få namnet `Post.init.php`. 
När [CDbFixtureManager] ser detta skript kommer den att köra skriptet i stället 
för att använda det vanliga sättet att återställa tabellen.

> Tip|Tips: Med för många fixturfiler kan testningstiden förlängas dramatiskt. 
Av detta skäl bör fixturfiler endast göras tillgängliga för de tabeller vars 
innehåll kan komma att ändras under testet. Tabeller som endast används för 
att söka fram data ändras inte och behöver därför inte fixturfiler.

I följande två avsnitt kommer att beskrivas hur man använder fixturer hanterade 
av [CDbFixtureManager] i enhetstester och funktionella tester.

<div class="revision">$Id: test.fixture.txt 3039 2011-03-09 19:48:15Z qiang.xue $</div>