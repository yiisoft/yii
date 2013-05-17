Data Access Objects (DAO)
=========================

Data Access Objects (DAO) tillhandahåller ett generellt programmeringsgränssnitt 
(API) för att ge tillgång till data lagrad i olika databahshanterare (DBMS). 
Detta medför att den underliggande databashanteraren kan bytas till en annan 
utan behov av ändringar i den kod som använder DAO för dataåtkomst.

Yii:s DAO har byggts ovanpå [PHP Data Objects 
(PDO)](http://php.net/manual/en/book.pdo.php)-tillägget, vilket tillhandahåller 
enhetlig åtkomst till många populära databashanterare såsom MySQL, PostgreSQL. 
Av denna anledning behöver PDO-tillägget och den databasspecifika PDO-
drivrutinen (t.ex. `PDO_MYSQL`) installeras innan Yii:s PDO används.

Yii:s DAO består i huvudsak av följande fyra klasser:

   - [CDbConnection]: representerar en anslutning till en databas.
   - [CDbCommand]: representerar en SQL-sats som skall exekveras mot en databas.
   - [CDbDataReader]: representerar en icke-reversibel följd av rader ur en frågas resultatmängd.
   - [CDbTransaction]: representerar en databastransaktion.

I följande stycken introduceras användning av Yii:s DAO i olika scenarier.

Upprätta en databasanslutning
-----------------------------

För att upprätta en databasanslutning, skapa en instans av [CDbConnection] och 
aktivera den. Ett namn på en datakälla (DSN) erfordras för att specificera 
nödvändig information för anslutning till databasen. Ett användarnamn och ett 
lösenord kan också behövas för att upprätta anslutningen. En exception 
signaleras i händelse av att ett fel uppstår under upprättandet av anslutningen 
(t.ex. felaktig DSN eller ogiltigt användarnamn/lösenord).

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// establish connection. You may try...catch possible exceptions
$connection->active=true;
......
$connection->active=false;  // close connection
~~~

Formatet för ett DSN bestäms av vilken databasspecifik PDO-drivrutin som 
används. Generellt sett består ett DSN av namnet på PDO-drivrutinen följt av ett 
kolon, följt av drivrutinsspecifik anslutningssyntax. Se 
[PDO-dokumentationen](http://www.php.net/manual/en/pdo.construct.php) för 
fullständig information. Nedan återges en lista med vanligtvis använda DSN-format:

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`
   - SQL Server: `mssql:host=localhost;dbname=testdb`
   - Oracle: `oci:dbname=//localhost:1521/testdb`

Eftersom [CDbConnection] är en utvidgning av [CApplicationComponent], kan vi 
även använda den som just en 
[applikationskomponent](/doc/guide/basics.application#application-component). 
För att göra så, konfigurera in en `db` (eller annat namn) applikationskomponent i 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration) 
enligt följande,

~~~
[php]
array(
	......
	'components'=>array(
		......
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'mysql:host=localhost;dbname=testdb',
			'username'=>'root',
			'password'=>'password',
			'emulatePrepare'=>true,  // erfordras i vissa MySQL-installationer
		),
	),
)
~~~

Därefter kan databasanslutningen tillgås via `Yii::app()->db` som redan är 
automatiskt aktiverad, utom i det fall [CDbConnection::autoConnect] uttryckligen 
konfigurerats till false. Med användande av detta tillvägagångssätt kan en enda 
DB-anslutning delas av kod på flera ställen.

Exekvering av SQL-satser
------------------------

När en databasanslutning väl har etablerats, kan SQL-satser exekveras med hjälp 
av [CDbCommand]. Man skapar en instans av [CDbCommand] genom anrop till 
[CDbConnection::createCommand()] med SQL-satsen angiven:

~~~
[php]
$connection=Yii::app()->db;   // assuming you have configured a "db" connection
// If not, you may explicitly create a connection:
// $connection=new CDbConnection($dsn,$username,$password);
$command=$connection->createCommand($sql);
// if needed, the SQL statement may be updated as follows:
// $command->text=$newSQL;
~~~

En SQL-sats exekveras via [CDbCommand] på ett av följande två sätt:

   - [execute()|CDbCommand::execute]: genomför en SQL-sats som inte returnerar 
   rader, såsom `INSERT`, `UPDATE` and `DELETE`. Vid felfritt genomförande 
   returneras antalet rader som omfattades av operationen.

   - [query()|CDbCommand::query]: genomför en SQL-sats som returnerar rader av 
   data (eg. "påsar"), såsom `SELECT`. Vid felfritt genomförande, returneras en 
   instans av [CDbDataReader] från vilken man kan traversera den resulterande 
   mängden av rader. För bekvämlighets skull har även en uppsättning 
   `queryXXX()`-metoder implementerats, vilka direkt returnerar respektive frågeresultat.

En exception signaleras om ett fel skulle inträffa under exekveringen av SQL-satser.

~~~
[php]
$rowCount=$command->execute();   // exekvera SQL som inte är en fråga
$dataReader=$command->query();   // exekvera en SQL-fråga
$rows=$command->queryAll();      // exekvera fråga och returnera alla resultatrader
$row=$command->queryRow();       // exekvera fråga och returnera första resultatraden
$column=$command->queryColumn(); // exekvera fråga och returnera första resultatkolumnen
$value=$command->queryScalar();  // exekvera fråga och returnera första fältet i första raden
~~~

Hämta frågeresultat
-------------------

Då [CDbCommand::query()] genererat [CDbDataReader]-instansen, kan man hämta 
resulterande datarader genom att repetitivt anropa [CDbDataReader::read()]. Man 
kan även använda [CDbDataReader] i en i PHP-språket tillgänglig `foreach`-sats 
för att hämta data rad för rad.

~~~
[php]
$dataReader=$command->query();
// calling read() repeatedly until it returns false
while(($row=$dataReader->read())!==false) { ... }
// using foreach to traverse through every row of data
foreach($dataReader as $row) { ... }
// retrieving all rows at once in a single array
$rows=$dataReader->readAll();
~~~

> Note|Märk: Till skillnad från [query()|CDbCommand::query], returnerar alla 
`queryXXX()`-metoder data direkt. Till exempel returnerar 
queryRow()|CDbCommand::queryRow] en array som representerar den första raden i 
frågeresultatet.

Användning av transaktioner
---------------------------

När en applikation exekverar ett antal databasoperationer, som var och en läser 
information från och/eller skriver information till databasen, är det viktigt 
att försäkra sig om att databasen inte lämnas med några operationer 
ofullständigt utförda. En transaktion, representerad i Yii som en instans av 
[CDbTransaction], kan inledas för att säkerställa detta:

   - Starta (begin) transaktionen.
   - Exekvera frågorna/operationerna en och en. Eventuella uppdateringar av databasen kan inte ses av av världen utanför.
   - Bekräfta (commit) transaktionen. Uppdateringar blir nu synliga eftersom transaktionen lyckades.
   - Om en av operationerna misslyckades reverseras hela transaktionen (rollback).

Ovanstående arbetsflöde kan implementeras med hjälp av följande kod:

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... other SQL executions
	$transaction->commit();
}
catch(Exception $e) // an exception is raised if a query fails
{
	$transaction->rollBack();
}
~~~

Koppling av parametrar
----------------------

För att undvika [SQL-
injekteringsattacker](http://en.wikipedia.org/wiki/SQL_injection) samt för 
förbättrad prestanda vid upprepad exekvering av SQL-satser, kan man i 
förekommande fall förbereda ("prepare") en SQL-sats med platsmarkörer för 
parametrar, som senare - vid koppling av parametrar (parameter binding) - kommer 
att ersättas med aktuella parametrar.

Parameterplatsmarkörerna kan antingen var namngivna (representerade av unika 
symboler) eller icke-namngivna (representerade av frågetecken). Anropa 
[CDbCommand::bindParam()] eller [CDbCommand::bindValue()] för att ersätta dessa 
platsmarkörer med de aktuella parametrarna. Parametrarna behöver inte omges av 
citationstecken; den underliggande databasdrivrutinen utför detta. 
Parameterkoppling måste ske innan SQL-satsen exekveras.

~~~
[php]
// an SQL with two placeholders ":username" and ":email"
$sql="INSERT INTO tbl_user (username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// replace the placeholder ":username" with the actual username value
$command->bindParam(":username",$username,PDO::PARAM_STR);
// replace the placeholder ":email" with the actual email value
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// insert another row with a new set of parameters
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

Metoderna [bindParam()|CDbCommand::bindParam] och 
[bindValue()|CDbCommand::bindValue] är mycket snarlika. Den enda skillnaden är 
att den förra kopplar en parameter till en PHP-variabelreferens, den senare med 
ett värde. För parametrar som representerar stora block av dataminne är den 
förra metoden - av prestandaskäl - att föredra.

För fler detaljer om parameterkoppling, se den [tillämpliga PHP-
dokumentationen](http://www.php.net/manual/en/pdostatement.bindparam.php).

Koppling av kolumner
--------------------

Vid hämtning av frågeresultat kan man också koppla kolumner till PHP-variabler 
så att de automatiskt uppdateras med senaste data var gång en rad hämtas.

~~~
[php]
$sql="SELECT username, email FROM tbl_user";
$dataReader=$connection->createCommand($sql)->query();
// bind the 1st column (username) with the $username variable
$dataReader->bindColumn(1,$username);
// bind the 2nd column (email) with the $email variable
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username and $email contain the username and email in the current row
}
~~~

Användning av tabellprefix
--------------------------

Yii erbjuder inbyggt stöd för användning av tabellprefix. Tabellprefix innebär 
att en sträng sätts in före namnen på tabeller i den för tillfället anslutna 
databasen. Detta kommer mest till användning i en webbhotellmiljö där fler än en 
applikation delar samma databas och applikationerna hålls åtskilda genom att 
tabellprefix sätts in före tabellnamnen. Till exempel kan en applikation använda 
`tbl_` som prefix, medan en annan använder `yii_`.

För att använda tabellprefix, konfigurera propertyn [CDbConnection::tablePrefix] med
det önskade tabellprefixet. Därefter används `{{TableName}}` - där `TableName` innebär 
tabellnamnet utan prefix - i SQL-satser för att referera till tabeller. Exempelvis,
om databasen innehåller en tabell `tbl_user`, och `tbl_` är konfigurerat som
tabellprefix, kan följande kod användas i en fråga angående användare:

~~~
[php]
$sql='SELECT * FROM {{user}}';
$users=$connection->createCommand($sql)->queryAll();
~~~

<div class="revision">$Id: database.dao.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>