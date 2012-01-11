Databasmigrering
================

> Note|Märk: Finessen databasmigrering har varit tillgänglig sedan version 1.1.6.

Likt källkod vidareutvecklas strukturen för en databas över tid under utveckling och förvaltning 
av en databasdriven applikation. Till exempel under pågående utveckling, kan vi behöva lägga till en ny tabell; 
när applikationen gått i produktion kan vi upptäcka behov av att lägga till ett index för en kolumn. 
Dat är viktigt att hålla reda på sådana strukturella databasändringar (benämnda **migration**), 
så som vi gör med vår källkod. Om källkod och databas kommer ur synk, är det mycket sannolikt att 
hela systemet kan fallera. Av denna anledning tillhandahåller Yii ett verktyg för databasmigrering 
som kan hålla reda på historik för datamigrering, applicera nya migreringar, eller återställa 
sådana som gjorts tidigare.

Följande steg belyser hur databasmigrering kan användas under utvecklingens gång:

1. Tim skapar en ny migrering (t.ex. skapa en ny tabell)
2. Tim fastlägger (commits) den nya migreringen i versionshanteringssystemet (t.ex. SVN, GIT)
3. Doug uppdaterar från versionshanteringssystemet och tar emot den nya migreringen
4. Doug applicerar migreringen på sin lokala utvecklingsdatabas


Yii stöder databasmigrering via kommandoradsverktyget `yiic migrate`. Detta verktyg har stöd för att
skapa nya migreringar, applicera/återställa/upprepa migreringar samt för att visa migreringshistorik 
och nya migreringar.

I det följande kommer vi att visa hur detta verktyg används.

> Note|Märk: Det är bättre att använda applikationens instans av yiic (t.ex. `cd path/to/protected`)
> vid arbete med `migrate`-kommandot, i stället för den i `framework`-katalogen.
> Kontrollera att katalogen `protected\migrations` existerar och är möjlig att skriva till, 
> samt att en databasanslutning är konfigurerad i `protected/config/console.php`.

Skapa migreringar
-----------------

För att skapa en ny migrering (t.ex. skapa tabellen news), kör vi följande kommando:

~~~
yiic migrate create <name>
~~~

Den obligatoriska `name`-parametern specificerar en mycket kortfattad beskrivning 
av migreringen (t.ex. `create_news_table`). Nedan framgår det att `name`-parametern 
kommer att användas som en del i ett PHP-klassnamn. Därför skall den bara 
innehålla bokstäver, siffror och/eller understrykningstecken.

~~~
yiic migrate create create_news_table
~~~

Ovanstående kommando kommer att, i katalogen `protected/migrations`, skapa en ny fil 
med namnet `m101129_185401_create_news_table.php` vilken innehåller följande initiala kod:

~~~
[php]
class m101129_185401_create_news_table extends CDbMigration
{
	public function up()
	{
	}

    public function down()
    {
		echo "m101129_185401_create_news_table does not support migration down.\n";
		return false;
    }

	/*
	// implementera safeUp/safeDown i stället, om transaktioner skall användas
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
~~~

Lägg märke till att klassnamnet är samma som filnamnet enligt mönstret `m<timestamp>_<name>`, 
där `<timestamp>` refererar till UTC-tidstämpeln för när migreringen skapats (på formatet `yymmdd_hhmmss`), 
och `<name>` hämtas från kommandots `name`-parameter.

Metoden `up()` skall innehålla koden som implementerar den aktuella databasmigreringen, 
medan metoden `down()` kan innehålla kod för att återställa det som sker i `up()`.

Ibland är det inte möjligt att implementera `down()`. Om vi exempelvis tar bort tabellrader i `up()`, 
kommer vi inte att kunna återställa dem i `down()`. I sådant fall kallas migreringen irreversibel, 
med innebörden att vi inte kan backa till ett tidigare tillstånd för databasen. I den generarade 
koden ovan, returnerar metoden `down()` värdet `false` som indikation att migreringen inte är reversibel.

> Info: Med start från version 1.1.7 gäller att returvärdet `false` fråm metoderna `up()` och `down()` 
> medför att efterföljande migreringar annulleras. Tidigare, i version 1.1.6, krävdes att man 
> genererade en exception för att övriga migreringar skulle annulleras.

Tag som exempel migreringen att skapa en news-tabell.

~~~
[php]
class m101129_185401_create_news_table extends CDbMigration
{
	public function up()
	{
		$this->createTable('tbl_news', array(
			'id' => 'pk',
			'title' => 'string NOT NULL',
			'content' => 'text',
		));
	}

	public function down()
	{
		$this->dropTable('tbl_news');
	}
}
~~~

Basklassen [CDbMigration] erbjuder en uppsättning metoder för manipulering av data och databasens schema. 
Till exempel, [CDbMigration::createTable] skapar en databastabell, medan [CDbMigration::insert] lägger till 
en rad med data. Dessa metoder använder alla databasanslutningen som returneras av [CDbMigration::getDbConnection()], 
som standard `Yii::app()->db`.

> Info: Noterbart är att databasmetoderna som [CDbMigration] erbjuder är mycket lika de i [CDbCommand]. 
Faktiskt så är de likadana med undantaget att [CDbMigration]-metoderna mäter tidåtgång och skriver ut 
några meddelanden om metodparametrarna.


Transaktionella migreringar
---------------------------

> Info: Finessen transaktionella migreringar stöds sedan version 1.1.7.

Vid genomförande av komplexa DB-migreringar vill vi vanligen försäkra oss om att samtliga migreringssteg 
utförs korrekt eller fallerar som en enhet, så att databasen upprätthåller korrekthet och integritet. 
För att uppnå detta mål kan vi dra nytta av DB-transaktioner.

Vi kan uttryckligen starta en DB-transaktion och låta transaktionen omge resterande DB-relaterad kod, 
på följande sätt:

~~~
[php]
class m101129_185401_create_news_table extends CDbMigration
{
	public function up()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
		try
		{
			$this->createTable('tbl_news', array(
				'id' => 'pk',
				'title' => 'string NOT NULL',
				'content' => 'text',
			));
			$transaction->commit();
		}
		catch(Exception $e)
		{
			echo "Exception: ".$e->getMessage()."\n";
			$transaction->rollBack();
			return false;
		}
	}

	// ...liknande kod för down()
}
~~~

Ett enklare sätt att erhålla transaktionsstöd är att implementera metoden `safeUp()` i stället för `up()`, 
och metoden `safeDown()` i stället för `down()`. Till exempel,

~~~
[php]
class m101129_185401_create_news_table extends CDbMigration
{
	public function safeUp()
	{
		$this->createTable('tbl_news', array(
			'id' => 'pk',
			'title' => 'string NOT NULL',
			'content' => 'text',
		));
	}

	public function safeDown()
	{
		$this->dropTable('tbl_news');
	}
}
~~~

När migreringen utförs kommer Yii att starta en DB-transaktion och därefter anropa `safeUp()` eller `safeDown()`. 
Om något DB-fel inträffar i `safeUp()` eller `safeDown()`, kommer transaktionen att reverseras och därmed  
säkerställa att databasen förblir i ett gott skick.

> Note|Märk: Transaktioner stöds inte av alla databashanterare. Vissa DB-frågor kan inte ingå i en 
> transaktion. I sådant fall måste i stället `up()` och `down()` implementeras. 
> I fråga om MySQL, kan vissa SQL-satser leda till en [underförstådd commit-operation](http://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html).


Applicera migreringar
---------------------

För att applicera alla tillgängliga nya migreringar (dvs göra den lokala databasen helt up-to-date), 
kör följande kommando:

~~~
yiic migrate
~~~

Kommandot kommer att presentera en lista över alla nya migreringar. Efter bekräftelse att migreringarna 
skall appliceras, kommer metoden `up()` i varje ny migreringsklass att exekveras, en efter en, i den ordning 
tidstämpelvärden i klassnamnen anger.

Efter applicering av en migrering kommer migreringsverktyget att spara en post i databastabellen `tbl_migration`. 
Detta möjliggör för vektyget att hålla reda på vilka migreringar som applicerats och vilka som inte har applicerats. 
Om tabellen `tbl_migration` inte existerar, kommer verktyget att automatiskt skapa den i databasen som specificeras 
av applikationskomponenten `db`.

Ibland vill vi bara applicera en eller ett fåtal nya migreringar. Följande kommando kan då användas:

~~~
yiic migrate up 3
~~~

Detta kommando applicerar tre nya migreringar. Genom att ändra värdet (3) kan vi 
ändra antalet migreringar som skall appliceras.

Vi kan även migrera databasen till en specifik version, med följande kommando:

~~~
yiic migrate to 101129_185401
~~~

Det vill säga, vi använder tidstämpeldelen av migreringens namn för att specificera den 
version vi vill migrera databasen till. Om det finns flera migreringar mellan den senast applicerade 
och den specificerade migreringen kommer samtliga dessa migreringar att appliceras. Om den specificerade 
migreringen redan har applicerats tidigare, kommer alla migreringar som applicerats därefter att återställas 
(kommer att beskrivas i nästa avsnitt).


Återställa migreringar
----------------------

För att backa tillbaka den senaste eller flera applicerade migreringar, kan följande kommando användas:

~~~
yiic migrate down [step]
~~~

där den frivilliga `step`-parametern specificerar antalet migreringar som skall backas tillbaka. 
Som standard är den 1, vilket innebär att den senaste migreringen backas.

Som tidigare beskrivits kan inte alla migreringar backas. Försök att backa irreversibla migreringar kommer att 
generera ett undantag och innebär att hela återställningsprocessen stoppas.


Göra om migreringar
-------------------

Att göra om migreringar innebär att först backa och sedan på nytt applicera de specificerade migreringarna. 
Detta kan göras med följande kommando:

~~~
yiic migrate redo [step]
~~~

där den frivilliga `step`-parametern specificerar antalet migreringar som skall göras om. Standardvärde 1, 
med innebörden att göra om den senaste migreringen.


Visa information om migreringar
-------------------------------

Utöver att applicera och backa tillbaka migreringar, kan migreringsverktyget även presentera migrationshistorik 
och de nya migreringar som väntar på att appliceras.

~~~
yiic migrate history [limit]
yiic migrate new [limit]
~~~

där den frivilliga parametern `limit` specificerar antalet migreringar som skall visas. Om `limit` utelämnas, 
kommer samtliga tillgängliga migreringar att visas.

Det första kommandot visar de migeringar som har applicerats medan det andra kommandot visar de migreringar 
som ännu ej applicerats.


Modifiera migreringshistorik
----------------------------

Ibland kan vi ha för avsikt att ändra migreringshistoriken till en specifik version, utan att faktiskt applicera 
eller backa tillbaka de relevanta migreringarna. Detta inträffar ofta under utveckling av en ny migrering. 
Följande kommando kan användas för detta ändamål.

~~~
yiic migrate mark 101129_185401
~~~

Detta kommando är snarlikt `yiic migrate to`-kommandot, med undantag för att det enbart ändrar tabellen med 
migreringshistorik till den specificerade versionen utan att applicera eller backa tillbaka migreringarna.


Anpassa migreringskommandot
---------------------------

Migreringskommandot kan anpassas på många sätt.

### Med alternativ på kommandorad

Migreringskommandot har fyra inbyggda alternativ som kan specificeras på kommandoraden:

* `interactive`: boolean, specificerar huruvida migrering skall utföras i interaktivt läge. 
Som standard true, vilket innebär att användaren tillfrågas när en specifik migrering skall utföras. 
Sätt till false i händelse migreringarna skall utföras i en bakgrundsprocess.

* `migrationPath`: string, specificerar katalogen som lagrar alla migreringars klassfiler. 
Detta måste specificeras som ett sökvägsalias och den motsvarande katalogen måste existera. 
Om alternativet utelämnas kommer underkatalogen `migrations` till applikationens basePath att användas.

* `migrationTable`: string, specificerar namnet på den databastabell som lagrar migreringshistoriken. 
Som standard är det `tbl_migration`. Tabellstrukturen är `version varchar(255) primary key, apply_time integer`.

* `connectionID`: string, specificerar ID för databasens applikationskomponent. Är som standard 'db'.

* `templateFile`: string, specificerar sökvägen för den fil som skall tjänsgöra som kodmall för generering 
av migreringsklasserna. Denna måste specificeras som ett sökvägsalias (t.ex. `application.migrations.template`). 
Om alternativet utelämnas kommer en intern mall att användas. Inuti kodmallen kommer symbolen `{ClassName}` 
att ersättas av det faktiska namnet på migreringsklassen.

För att specificera dessa alternativ, exekvera migreringskommandot på följande format

~~~
yiic migrate up --option1=value1 --option2=value2 ...
~~~

Till exempel, om vi vill migrera för en modul `forum`, vars migreringsfiler är placerade i modulens 
underkatalog `migrations`, kan vi använda följande kommando:

~~~
yiic migrate up --migrationPath=ext.forum.migrations
~~~


### Global konfigurering av kommando

Medan kommandoradsalternativ tillåter oss att konfigurera migreringskommandot i farten, kan vi ibland 
vilja konfigurera kommandot en gång för alla. Till exempel kan vi vilja använda en annan tabell för 
lagring av migreringshistoriken, eller vi kanske vill använda en anpassad migreringsmall. Detta kan vi 
göra genom att modifiera konsolapplikationens konfigurationsfil så som följer,

~~~
[php]
return array(
	......
	'commandMap'=>array(
		'migrate'=>array(
			'class'=>'system.cli.commands.MigrateCommand',
			'migrationPath'=>'application.migrations',
			'migrationTable'=>'tbl_migration',
			'connectionID'=>'db',
			'templateFile'=>'application.migrations.template',
		),
		......
	),
	......
);
~~~

Om vi nu kör `migrate`-kommandot, kommer ovanstående konfigureringar att gälla, utan att vi behöver 
mata in kommandoradsalternativ varje gång.


<div class="revision">$Id: database.migration.txt 3450 2011-11-20 22:52:07Z alexander.makarow $</div>