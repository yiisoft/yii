Active Record
=============

Även om Yii DAO kan hantera så gott som varje databasrelaterad uppgift, är det 
stor risk att vi använder 90% av vår tid till att skriva vissa SQL-satser som 
genomför de återkommande CRUD-operationerna (create, read, update och delete). 
Det är också svårt att underhålla koden när den är uppblandad med SQL-satser. En 
lösning på detta problem är att använda Active Record.

Active Record (AR) är en populär teknik för objekt-relationsmappning (ORM). 
Varje AR-klass representerar en databastabell (eller -vy) vars attribut är 
representerade som AR-klassens propertyn, en AR-instans representerar en rad i 
nämnda tabell. Vanliga CRUD-operationer är implementerade som AR-metoder. 
Resultatet är tillgång till data på ett mer objektorienterat sätt. Till exempel, 
kan följande kod användas för att sätta in en ny rad i tabellen `tbl_post`:

~~~
[php]
$post=new Post;
$post->title='sample post';
$post->content='post body content';
$post->save();
~~~

I det följande beskrivs hur man sätter upp AR och använder denna till att 
genomföra CRUD-operationer. I nästa avsnitt visas hur man kan använda AR för att 
hantera databassamband (relationship). För enkelhets skull kommer nedanstående 
databastabell att användas i exemplen i detta avsnitt.  Märk att om MySQL-databas 
används skall `AUTOINCREMENT` bytas mot `AUTO_INCREMENT` i följande SQL.

~~~
[sql]
CREATE TABLE tbl_post (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	create_time INTEGER NOT NULL
);
~~~

> Note|Märk: AR är inte tänkt att lösa alla databasrelaterade uppgifter. Tekniken kommer 
bäst till användning för att modellera databastabeller i form av PHP-konstruktioner 
samt genomföra sådana frågor som inte inbegriper komplexa SQL-satser. Yii:s DAO 
bör användas för mer komplexa scenarier.


Upprätta en databasanslutning
-----------------------------

AR förlitar sig på en databasanslutning för att genomföra databasrelaterade 
operationer. Som standard antar den att applikationskomponenten `db` ger den 
[CDbConnection]-instans som behövs till att tjäna som databasanslutning. 
Följande applikationskonfiguration visar ett exempel:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:path/to/dbfile',
			// turn on schema caching to improve performance
			// 'schemaCachingDuration'=>3600,
		),
	),
);
~~~

> Tip|Tips: Eftersom Active Record förlitar sig på metadata om tabeller för att 
avgöra information om kolumner, åtgår tid till att läsa metadata och till att 
analysera den. Om det är mindre troligt att databasschemat kommer att ändras, 
bör schemacachning slås på genom att konfigurera 
[CDbConnection::schemaCachingDuration]-propertyn till ett värde större än 0.

Stödet för AR beror av använd databashanterare. För närvarande 
stöds endast följande databashanterare:

   - [MySQL 4.1 eller senare](http://www.mysql.com)
   - [PostgreSQL 7.3 eller senare](http://www.postgres.com)
   - [SQLite 2 och 3](http://www.sqlite.org)
   - [Microsoft SQL Server 2000 eller senare](http://www.microsoft.com/sqlserver/)
   - [Oracle](http://www.oracle.com)

Vid önskemål om att använda en annan applikationskomponent än `db`, eller om att 
använda AR till att arbeta mot flera databaser, åsidosätt 
[CActiveRecord::getDbConnection()]. Klassen [CActiveRecord] utgör basklass för alla 
AR-klasser.

> Tip|Tips: Det finns två sätt att arbeta mot multipla databaser i AR. Om 
databasernas scheman är olika, kan man skapa olika AR-basklasser med skild 
implementering av [getDbConnection()|CActiveRecord::getDbConnection]. I annat 
fall är det en bättre idé att dynamiskt ändra den statiska variabeln 
[CActiveRecord::db].

Definiera AR-klass
------------------

För tillgång till en databastabell måste först en AR-klass definieras genom arv 
och utvidgning av [CActiveRecord]. Varje AR-klass representerar en enda 
databastabell och en AR-instans representerar en rad i den tabellen. Följande 
exempel visar den minimala kod som erfordras för AR-klassen korresponderande 
mot tabellen `tbl_post`.

~~~
[php]
class Post extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'tbl_post';
	}
}
~~~

> Tip|Tips: Eftersom AR-klasser ofta refereras till på många ställen, är det möjligt att 
> importera hela katalogen som innehåller AR-klasserna, i stället för att inkludera 
> dem en och en. Till exempel, om alla AR-klasser finns i katalogen
> `protected/models`, kan applikationen konfigureras som följer:
> ~~~
> [php]
> return array(
> 	'import'=>array(
> 		'application.models.*',
> 	),
> );
> ~~~

Som standard är namnet på AR-klassen samma som namnet på databastabellen. 
Åsidosätt metoden [tableName()|CActiveRecord::tableName] om de skall vara olika. 
Metoden [model()|CActiveRecord::model] finns deklarerad per se i varje AR-klass 
(förklaring följer längre ned).

> Info: För att använda [tabellprefix-finessen](/doc/guide/database.dao#using-table-prefix),
> kan [tableName()|CActiveRecord::tableName]-metoden i en AR-klass behöva åsidosättas på följande sätt,
> ~~~
> [php]
> public function tableName()
> {
>     return '{{post}}';
> }
> ~~~
> Det innebär att, istället för att returnera det fullständiga tabellnamnet, returnerar vi
> tabellnamnet utan prefix, omgivet av dubbla krumparenteser.

Kolumnvärden för en rad i en tabell kan åtkommas som propertyn i den motsvarande 
AR-instansen. Till exempel, följande kod sätter kolumnen (attributet) `title`:

~~~
[php]
$post=new Post;
$post->title='a sample post';
~~~

Ävensom vi aldrig uttryckligen deklarerar `title`-propertyn i klassen `Post`, 
kan vi fortfarande få tillgång till den i ovanstående kod. Detta beror på att 
`title` är en kolumn i tabellen `tbl_post` och CActiveRecord gör den tillgänglig som 
en property med hjälp av PHP:s "magiska" metod `__get()`. En exception 
signaleras vid försök att tillgå en icke-existerande kolumn på detta sätt.

> Info: I denna guide användes gemener för tabell- och kolumnnamn. Detta beror på 
att olika databashanterare hanterar skiftläge olika. Till exempel PostgreSQL 
betraktar underförstått kolumnnamn som oberoende av skiftläge, och om ett kolumnnamn 
i en fråga innehåller både gemena och versaler måste det omges av citationstecken. 
Användning av enbart gemener eliminerar ev. skiftlägesrelaterade problem.

AR förlitar sig på väldefinierade primärnycklar för tabeller. Om en tabell saknar 
primärnyckel, krävs det att motsvarande AR-klass specificerar vilken/vilka kolumn(er) 
som skall utgöra primärnyckel, genom att åsidosätta metoden `primaryKey()` enligt 
nedanstående exempel,

~~~
[php]
public function primaryKey()
{
	return 'id';
	// For composite primary key, return an array like the following
	// return array('pk1', 'pk2');
}
~~~


Skapa DB-post
-------------

För att sätta in en ny rad i en databastabell, skapa en ny instans av den 
motsvarande AR-klassen, sätt dess propertyn associerade med tabellens kolumner 
och anropa metoden [save()|CActiveRecord::save] för att genomföra insättningen.

~~~
[php]
$post=new Post;
$post->title='sample post';
$post->content='content for the sample post';
$post->create_time=time();
$post->save();
~~~

Om tabellens primärnyckel är självuppräknande, kommer AR-instansen att efter 
insättningen innehålla en uppdaterad primärnyckel. I ovanstående exempel 
återspeglar `id`-propertyn primärnyckelns värde i den nyligen insatta 
postningen, trots att vi aldrig uttryckligen ändrar den.

Om en kolumn är definierad med något statiskt standardvärde (t.ex. en sträng, 
ett tal) i tabellschemat, kommer motsvarande property i AR-instansen att 
automatiskt innehålla ett sådant värde när instansen skapats. Ett sätt att ändra 
ett sådant standardvärde är genom att uttryckligen deklarera propertyn i AR-klassen:

~~~
[php]
class Post extends CActiveRecord
{
	public $title='please enter a title';
	......
}

$post=new Post;
echo $post->title;  // this would display: please enter a title
~~~

Ett attribut kan tilldelas ett värde av typen [CDbExpression] innan posten sparas 
till databasen (antingen insättning eller uppdatering). Exempelvis, för att spara 
en tidstämpel, returnerad av MySQL:s funktion `NOW()`, kan följande kod användas:

~~~
[php]
$post=new Post;
$post->create_time=new CDbExpression('NOW()');
// $post->create_time='NOW()'; will not work because
// 'NOW()' will be treated as a string
$post->save();
~~~

> Tip|Tips: Även om AR tillåter oss att utföra databasoperationer utan att skriva
arbetskrävande SQL-satser, vill vi ofta veta vilka SQL-satser som exekveras av AR.
Detta kan uppnås genom att slå på Yii:s [loggningsfiness](/doc/guide/topics.logging).
Till exempel kan vi aktivera [CWebLogRoute] i applikationskonfigurationen, vilket 
leder till att exekverade SQL-satser kan avläsas i slutet av varje webbsida.
Vi kan sätta [CDbConnection::enableParamLogging] till true i applikationskonfigurationen 
så att även parametervärden knutna till SQL-satserna loggas.


Läsa DB-post
------------

För att läsa data i en databastabell, anropa någon av följande `find`-metoder:

~~~
[php]
// leta upp den första raden som satisfierar angivet villkor
$post=Post::model()->find($condition,$params);
// leta upp raden med angiven primärnyckel
$post=Post::model()->findByPk($postID,$condition,$params);
// leta upp en rad som har angivna attributvärden
$post=Post::model()->findByAttributes($attributes,$condition,$params);
// leta upp den första raden genom användning av specifierad SQL-sats
$post=Post::model()->findBySql($sql,$params);
~~~

I ovanstående, anropas metoden `find` medelst `Post::model()`. Som tidigare 
nämnts är den statiska metoden `model()` obligatorisk i varje AR-klass. Metoden 
returnerar en AR-instans som används för att få tillgång till metoder på 
klassnivå (något liknande statiska klassmetoder) i en objektkontext.

Om `find`-metoden hittar en rad som satisfierar frågevillkoren, kommer den att 
returnera en instans av `Post` vars propertyn innehåller korresponderande 
kolumnvärde från tabellraden. De laddade värdena kan sedan läsas på samma sätt 
som vanliga objektpropertyn, till exempel, `echo $post->title;`.

Metoden `find` returnerar null om inget kan hittas i databasen med det givna 
frågevillkoret.

I anropet till `find` används `$condition` och `$params` för att specificera 
frågevillkor. Här kan `$condition` vara en sträng som representerar `WHERE`-
ledet i en SQL-sats, `$params` en array av parametrar vars värden kommer att 
kopplas till platshållaren i `$condition`. Till exempel,

~~~
[php]
// find the row with postID=10
$post=Post::model()->find('postID=:postID', array(':postID'=>10));
~~~

> Note|Märk: I ovanstående exempel kan, för vissa databashanterare, referensen 
till kolumen `postID` behöva omges av escapetecken. Till exempel, om PostgreSQL 
används, behöver vi skriva villkoret som `"postID"=:postID` eftersom PostgreSQL 
som standard betraktar kolumnnamn som skiftlägesokänsliga.

`$condition` kan också användas för att specificera mer komplexa frågevillkor. I 
stället för en sträng kan `$condition` vara en instans av [CDbCriteria], vilken 
tillåter oss att specificera andra villkor än `enbart WHERE`-ledet. Till exempel,

~~~
[php]
$criteria=new CDbCriteria;
$criteria->select='title';  // only select the 'title' column
$criteria->condition='postID=:postID';
$criteria->params=array(':postID'=>10);
$post=Post::model()->find($criteria); // $params is not needed
~~~

Märk väl att när [CDbCriteria] används som frågevillkor, behövs inte `$params`-
parametern eftersom den kan specificeras i [CDbCriteria], vilket exemplifieras 
ovan.

Ett alternativt sätt att använda [CDbCriteria] är genom att lämna med en array 
till `find`-metoden. Arrayens nycklar och värden motsvarar kriterieobjektets 
respektive propertynamn och -värden. Ovanstående exempel kan skrivas om som 
följer,

~~~
[php]
$post=Post::model()->find(array(
	'select'=>'title',
	'condition'=>'postID=:postID',
	'params'=>array(':postID'=>10),
));
~~~

> Info: När ett frågevillkor handlar om att matcha några kolumner mot 
specificerade värden, kan [findByAttributes()|CActiveRecord::findByAttributes] 
användas. Vi låter då `$attributes`-parametern vara en array med värden 
indexerade av kolumnnamnen. I vissa ramverk kan denna uppgift fullgöras genom 
anrop till metoder i stil med `findByNameAndTitle`. Även om detta 
tillvägagångssätt förefaller attraktivt, leder det ofta till förvirring, 
konflikter samt problem som känslighet för kolumnnamns skiftläge (case).

Om mer än en rad med data matchar det specificerade frågevillkoret, kan samtliga 
hämtas in tillsammans med hjälp av följande `findAll`-metoder, vilka var och en 
har en motsvarande `find`-metod, så som beskrivits ovan.

~~~
[php]
// leta upp alla rader som satisfierar angivet villkor
$posts=Post::model()->findAll($condition,$params);
// leta upp alla rader med den specificerade primärnyckeln
$posts=Post::model()->findAllByPk($postIDs,$condition,$params);
// leta upp alla rader som har angivna attributvärden
$posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
// leta upp alla rader genom användning av specifierad SQL-sats
$posts=Post::model()->findAllBySql($sql,$params);
~~~

Om inget matchar frågevillkoret, returnerar `findAll` en tom array. Detta 
skiljer sig från `find` som skulle returnera null om inget hittades.

Förutom `find`- och `findAll`-metoderna som beskrivs ovan, finns även följande 
metoder tillgängliga:

~~~
[php]
// beräkna antalet rader som satisfierar angivet villkor
$n=Post::model()->count($condition,$params);
// beräkna antalet rader genom användning av specifierad SQL-sats
$n=Post::model()->countBySql($sql,$params);
// undersök om det finns åtminstone en rad som satisfierar angivet villkor
$exists=Post::model()->exists($condition,$params);
~~~

Uppdatera DB-post
-----------------

Efter det att en AR-instans initialiserats med kolumnvärden, kan dessa ändras och 
sparas tillbaka till databastabellen.

~~~
[php]
$post=Post::model()->findByPk(10);
$post->title='new post title';
$post->save(); // save the change to database
~~~

Som synes, används samma [save()|CActiveRecord::save]-metod för både 
insättnings- och uppdateringoperationer. Om en AR-instans skapas med hjälp av 
`new`-operatorn, leder anrop av [save()|CActiveRecord::save] till insättning av 
en ny post i databastabellen; om en AR-instans är resultatet från någon anrop av 
metoderna `find` eller `findAll`, leder anrop av [save()|CActiveRecord::save] 
till uppdatering av den existerande raden i tabellen. Faktum är att 
[CActiveRecord::isNewRecord] kan användas för att upplysa om huruvida en AR-
instans är ny eller inte.

Det är också möjligt att uppdatera en eller flera rader i en databastabell utan 
att först ladda dem. AR tillhandahåller följande ändamålsenliga metoder på 
klassnivå för ändamålet:

~~~
[php]
// uppdatera raderna som matchar det specificerade villkoret
Post::model()->updateAll($attributes,$condition,$params);
// uppdatera raderna som matchar det specificerade villkoret och primärnyckel/-nycklar
Post::model()->updateByPk($pk,$attributes,$condition,$params);
// uppdatera räknarkolumnerna i raderna som satisfierar det specificerade villkoret
Post::model()->updateCounters($counters,$condition,$params);
~~~

I ovanstående, är `$attributes` en array med kolumnvärden indexerade av 
kolumnnamn; `$counters` är en array med inkrementella värden indexerade av 
kolumnnamn samt `$condition` och `$params` är som beskrivits i föregående 
delavsnitt.

Ta bort DB-post
---------------

Det går också att ta bort en rad med data om en AR-instans har initialiserats med denna rad.

~~~
[php]
$post=Post::model()->findByPk(10); // assuming there is a post whose ID is 10
$post->delete(); // delete the row from the database table
~~~

Lägg märke till att efter borttagningen förblir AR-instansen oförändrad när 
den motsvarande raden i databastabellen redan är borttagen.

Följande metoder på klassnivå finns tillgängliga för att ta bort rader utan att 
först behöva ladda dem:

~~~
[php]
// tag bort raderna som matchar det angivna villkoret
Post::model()->deleteAll($condition,$params);
// tag bort raderna som matchar det angivna villkoret och primärnyckel/-nycklar
Post::model()->deleteByPk($pk,$condition,$params);
~~~

Datavalidering
--------------

Vid insättning eller uppdatering av en rad behöver vi  ofta kontrollera ifall 
kolumnvärden är i överensstämmelse med vissa regler. Detta är av speciell vikt 
om kolumnvärdena tillhandahålls från slutanvändare. Generellt sett skall vi inte 
lita blint på något som kommer från klientsidan.

AR utför datavalidering automatiskt när [save()|CActiveRecord::save] anropas. 
Valideringen baseras på de regler som fins specificerade i metoden 
[rules()|CModel::rules] i AR-klassen. Fler detaljer angående specificering av 
valideringsregler återfinns i sektionen 
[Deklarera valideringsregler](/doc/guide/form.model#declaring-validation-rules). 
Nedan ses det typiska arbetsflödet som behövs för att spara en databaspost:

~~~
[php]
if($post->save())
{
	// data is valid and is successfully inserted/updated
}
else
{
	// data is invalid. call getErrors() to retrieve error messages
}
~~~

När data för insättning eller uppdatering skickas av en slutanvändare i ett 
html-formulär, behöver vi tilldela dem till motsvarande AR-propertyn. Detta kan 
göras enligt följande :

~~~
[php]
$post->title=$_POST['title'];
$post->content=$_POST['content'];
$post->save();
~~~

Om det handlar om många kolumner, kommer listan med tilldelningar att bli lång. 
Detta kan mildras genom användning av propertyn 
[attributes|CActiveRecord::attributes] så som visas nedan. För fler detaljer se avsnittet 
[Säkra upp attributilldelningar](/doc/guide/form.model#securing-attribute-assignments) 
samt avsnittet [Skapa Action](/doc/guide/form.action).

~~~
[php]
// assume $_POST['Post'] is an array of column values indexed by column names
$post->attributes=$_POST['Post'];
$post->save();
~~~


Jämföra DB-poster
-----------------

Liksom tabellrader identifieras AR-instanser unikt genom sina primärnycklars 
värden. Att jämföra två AR-instanser handlar därför bara om att jämföra värdena 
för deras primärnycklar, givet att de tillhör samma AR-klass. Ett enklare sätt 
är dock att anropa [CActiveRecord::equals()].

> Info: Till skillnad mot AR-implementeringar i andra ramverk, stöder Yii 
sammansatta primärnycklar i sitt AR. En sammansatt primärnyckel består av två 
eller flera kolumner. Följaktligen är primärnyckelvärden i Yii representerade av 
en array. Propertyn [primaryKey|CActiveRecord::primaryKey] ger 
primärnyckelvärdet för en AR-instans.

Anpassning
----------

[CActiveRecord] erbjuder några platshållarmetoder vilka kan åsidosättas i ärvda 
klasser för att anpassa arbetsflödet i dessa.

   - [beforeValidate|CModel::beforeValidate] och 
   [afterValidate|CModel::afterValidate]: körs innan resp. efter att validering 
   utförs.

   - [beforeSave|CActiveRecord::beforeSave] och 
   [afterSave|CActiveRecord::afterSave]: körs innan resp. efter en AR-instans 
   sparas.

   - [beforeDelete|CActiveRecord::beforeDelete] and 
   [afterDelete|CActiveRecord::afterDelete]: körs innan resp. efter en AR-
   instans tas bort.

   - [afterConstruct|CActiveRecord::afterConstruct]: körs varje gång en AR-
   instans skapas med hjälp av operatorn `new`.

   - [beforeFind|CActiveRecord::beforeFind]: körs innan någon av AR:s 
   'find'-metoder används för att exekvera en fråga (t.ex. `find()`, `findAll()`). 

   - [afterFind|CActiveRecord::afterFind]: körs varje gång en AR-instans 
   har skapats som resultat av en fråga.


Använda Transaction med AR
--------------------------

Varje AR-instans innehåller en property benämnd 
[dbConnection|CActiveRecord::dbConnection], vilken är en instans av 
[CDbConnection]. Sålunda kan 
[transaction](/doc/guide/database.dao#using-transactions)-finessen 
som tillhandahålls av Yii DAO användas med AR om så önskas:

~~~
[php]
$model=Post::model();
$transaction=$model->dbConnection->beginTransaction();
try
{
	// find and save are two steps which may be intervened by another request
	// we therefore use a transaction to ensure consistency and integrity
	$post=$model->findByPk(10);
	$post->title='new post title';
	$post->save();
	$transaction->commit();
}
catch(Exception $e)
{
	$transaction->rollBack();
}
~~~


Namngivna omfång
----------------

> Note|Märk: Ursprungsidén till namngivna omfång (named scopes) är hämtad från Ruby on Rails.

Ett *namngivet omfång* representerar ett *namngivet* frågekriterium som kan kombineras 
med andra namngivna omfång och appliceras på en Active Record-fråga.

Namngivna omfång deklareras huvudsakligen i metoden [CActiveRecord::scopes()], 
i form av par av namn-kriterium. Följande kod deklarerar två namngivna omfång, 
`published` och `recently`, i modellklassen `Post`:

~~~
[php]
class Post extends CActiveRecord
{
	......
	public function scopes()
	{
		return array(
			'published'=>array(
				'condition'=>'status=1',
			),
			'recently'=>array(
				'order'=>'create_time DESC',
				'limit'=>5,
			),
		);
	}
}
~~~

Varje namngivet omfång deklareras som en array vilken kan användas för att 
initialisera en instans av [CDbCriteria]. Till exempel, det namngivna omfånget 
`recently` specificerar att propertyn `order` till att vara `create_time DESC` 
och propertyn `limit` till 5, vilket leder till ett frågekriterium som skulle 
returnera de fem senast publicerade postningarna.

Namngivna omfång används huvudsakligen som modifierare till metodanrop i `find`-familjen. 
Multipla namngivna omfång kan länkas till varandra och resultera i en mer restriktiv 
resultatmängd från en fråga. Till exempel, för att hitta de senast publicerade postningarna 
kan följande kod användas:

~~~
[php]
$posts=Post::model()->published()->recently()->findAll();
~~~

Generellt måste namngivna omfång placeras till vänster om ett anrop till en `find`-metod. 
Vart och ett av dem tillhandahåller ett frågekriterium som kombineras med andra kriterier, 
inklusive det som lämnas med i anropet `find`-metoden. Nettoeffekten blir som att lägga till 
en lista av filter till en fråga.

> Note|Märk: Namngivna omfång kan endast användas för metoder på klassnivå. 
Det innebär att metoden måste anropas på formatet `Klassnamn::model()`.


### Parametriserade namngivna omfång

Namngivna omfång kan parametriseras. Ett exempel kan vara att anpassa antalet 
postningar som adresseras av det namngivna omfånget `recently`. För att åstadkomma 
det behöver vi - istället för att deklarera det namngivna omfånget i metoden 
[CActiveRecord::scopes] - definiera en ny metod med lika namn som det namngivna omfånget:

~~~
[php]
public function recently($limit=5)
{
	$this->getDbCriteria()->mergeWith(array(
		'order'=>'create_time DESC',
		'limit'=>$limit,
	));
	return $this;
}
~~~

Därefter kan följande sats användas för att hämta de tre senast publicerade 
postningarna:

~~~
[php]
$posts=Post::model()->published()->recently(3)->findAll();
~~~

Om parametern 3 ovan utelämnas kommer de fem senaste postningarna att 
hämtas, enligt förbestämt standardvärde.


### Förbestämt omfång (default scope)

En modellklass kan ha ett förbestämt omfång som kommer att åsättas 
alla frågor (inklusive relationella sådana) avseende modellen. Till exempel kan  
en webbplats som stöder flera språk vilja begränsa innehåll som visas till 
det språk den aktuella användaren valt. Eftersom det kan bli många frågor 
gällande webbplatsens innehåll, kan ett förbestämt omfång lindra 
detta problem. För att göra detta, åsidosätt metoden [CActiveRecord::defaultScope] enligt följande,

~~~
[php]
class Content extends CActiveRecord
{
	public function defaultScope()
	{
		return array(
			'condition'=>"language='".Yii::app()->language."'",
		);
	}
}
~~~

Nu kommer följande metodanrop att automatiskt använda frågekriteriet som definierades ovan:

~~~
[php]
$contents=Content::model()->findAll();
~~~

> Note|Märk: Förbestämt omfång och namngivna omfång har effekt endast vid `SELECT`-frågor. De ignoreras vid `INSERT`-, `UPDATE`- och `DELETE`-frågor.
> Dessutom, när ett omfång deklareras (förbestämt eller namngivet), kan AR-klassen inte användas för DB-frågor i metoden som deklarerar omfånget.

<div class="revision">$Id: database.ar.txt 3318 2011-06-24 21:40:34Z qiang.xue $</div>