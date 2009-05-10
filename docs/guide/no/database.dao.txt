Data Access Objects (DAO)
=========================

Data Access Objects (DAO) tilbyr et API for å aksessere data lagret
i ulike databasesystemer (DBMS). Som et resultat av dette kan underliggende
databasesystem byttes ut uten å endre koden som benytter DAO for å aksessere
dataene.

DAO i Yii benytter [PHP Data Objects
(PDO)](http://php.net/manual/en/book.pdo.php) som er et tillegg til PHP som
tilbyr et generelt grensesnitt for mange populære databasesystemer slik som
MySQL og PostgreSQL. Derfor må PDO-tillegget og nødvendige PDO-drivere (for
eksempel: `PDO_MYSQL`) være installert.

DAO i Yii består i hovedsak av følgende fire klasser:

   - [CDbConnection]: representerer en tilkobling til en database.
   - [CDbCommand]: representerer et SQL-uttrykk som kjøres mot en database.
   - [CDbDataReader]: representerer en strøm av rader fra resultat av en spørring.
   - [CDbTransaction]: representerer en transaksjon mot databasen.

Videre ser vi litt på bruken av DAO i Yii.

Etablere en databasetilkobling
------------------------------

For å etablere en databasetilkobling må du opprette en instans av
[CDbConnection] og aktivere den. Et navn på datakilden (DSN) er påkrevet
for å koble til databasen. Et brukernavn og passord kan også være nødvendig
for å etablere tilkoblingen. Dersom tilkoblingen feiler får du en feilmelding
(for eksempel: feil DSN eller ugyldig brukernavn/passord).

~~~
[php]
$connection = new CDbConnection($dsn,$username,$password);
// etablerer tilkoblingen. Her kan du bruke try...catch for å håndtere feil
$connection->active = true;
......
$connection->active = false;  // lukk tilkoblingen
~~~

Formatet på DSN avhenger av PDO-driveren som benyttes. Generelt inneholder
DSN navnet på PDO-driveren etterfulgt av kolon etterfulgt av 
driver-spesifikk syntaks for tilkoblingen. Se [PDO
dokumentasjonen](http://www.php.net/manual/en/pdo.construct.php) for 
komplett informasjon. Her er noen eksempler:

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`
   - SQL Server: `mssql:host=localhost;dbname=testdb`

Siden [CDbConnection] arver [CApplicationComponent] kan du også bruke den som 
en [appkilasjonskomponent](/doc/guide/basics.application#application-component)
For å få til dette må `db`-komponenten i konfigurasjonsfilen for applikasjonen
defineres slik:

~~~
[php]
array(
	......
	'components' => array(
		......
		'db' => array(
			'class' => 'CDbConnection',
			'connectionString' => 'mysql:host=localhost;dbname=testdb',
			'username' => 'root',
			'password' => 'password',
		),
	),
)
~~~

Da kan du aksessere databasetilkoblingen via `Yii::app()->db` som allerede
er aktivert (med mindre du eksplisitt setter [CDbConnection::autoConnect] til 
`false`). Ved å bruke denne teknikken kan samme databasetilkobling deles
mange steder i koden.

Utføre spørringer
-----------------

Når du har etablert tilkoblingen til databasen kan du kjøre spørringer
ved hjelp av [CDbCommand]. Du oppretter en instans av [CDbCommand] ved å
kjøre [CDbConnection::createCommand()] med et SQL-uttrykk:

~~~
[php]
$command = $connection->createCommand($sql);
// ved behov kan du også oppdatere SQL-uttrykket ditt slik:
// $command->text = $newSQL;
~~~

Du utfører et SQL-uttrykk via [CDbCommand] på en av følgende to måter:

   - [execute()|CDbCommand::execute]: utfører et SQL-uttrykk som ikke er 
en spørring, slik som `INSERT`, `UPDATE` og `DELETE`. Hvis ok returnerer den
antall rader som blei påvirket av evalueringen.

   - [query()|CDbCommand::query]: utfører et SQL-uttrykk som returnerer
rader med data, slik som `SELECT`. Hvis ok returnerer den en 
[CDbDataReader]-instans som du kan iterere over for å hente ut dataene.
For enkelhets skyld finnes det også et set med `queryXXX()` metoder som
returnerer resultatet direkte. 

Dersom en feil opptrer under kjøring av et SQL-uttrykk kastes en exception.

~~~
[php]
$rowCount = $command->execute();   // utfører et SQL-uttrykk som ikke er en spørring
$dataReader = $command->query();   // utfører en SQL-spørring
$rows = $command->queryAll();      // utfører en spørring og returnerer alle rader
$row = $command->queryRow();       // utfører en spørring og returnerer første rad
$column = $command->queryColumn(); // utfører en spørring og returnerer første kolonne
$value = $command->queryScalar();  // utfører en spørring og returnerer første felt i første rad
~~~

Hente ut resultatet av en spørring
----------------------------------

Når [CDbCommand::query()] har returnert [CDbDataReader]-instansen kan du
hente ut rader fra resultatsettet ved å kalle [CDbDataReader::read()]
gjenntatte ganger. Du kan også bruke [CDbDataReader] i en `foreach`-løkke
for å hente ut rad for rad.

~~~
[php]
$dataReader = $command->query();

// kjør read() gjenntatte ganger helt til den returnerer "false"
while(($row = $dataReader->read()) !== false) { ... }

// eller bruk en foreach-løkke for å iterere igjennom hver rad i datasettet
foreach($dataReader as $row) { ... }

// eller hent ut alle rader direkte og lagre de i et array
$rows = $dataReader->readAll();
~~~

> Note|Merk: I motsetning til [query()|CDbCommand::query] returnerer alle 
`queryXXX()` metoder data direkte. For eksempel returnerer [queryRow()|CDbCommand::queryRow]
første rad fra resultatet.

Transaksjoner
-------------

Når en applikasjon utfører et par spørringer som hver seg henter og/eller
skriver informasjon til databasen er det viktig å passe på at alle
spørringene blir kjørt riktig. En transaksjon (representert ved en 
[CDbTransaction]-instans i Yii) kan brukes slik:

 - Start en transaksjon.
 - Utfør spørringer en etter en. Alle oppdateringer til databasen er ikke synlig for andre.
 - Avslutt transaksjonen. Alle oppdateringer blir synlige for andre hvis transaksjonen går igjennom.
 - Dersom en av spørringene feiler vil hele transaksjonen rulles tilbake. Ingen skade skjedd.

Dette kan implementeres med følgende kode:

~~~
[php]
$transaction = $connection->beginTransaction();

try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... flere spørringer
	$transaction->commit();
}
catch(Exception $e) // en "exception" kastes dersom en spørring feiler
{
	$transaction->rollBack();
}
~~~

Kobling av variable
-------------------

For å unngå såkalte [SQL-injeksjons angrep](http://en.wikipedia.org/wiki/SQL_injection)
og for å øke ytelsen på SQL-uttrykk som gjenntas flere ganger kan du "forberede" 
et SQL-uttrykk med markører som byttes ut med faktiske variable.

Markørene for variablene kan enten defineres med unike navn eller med 
et spørsmålstegn. Kall [CDbCommand::bindParam()] eller [CDbCommand::bindValue()]
for å bytte ut disse markørene med faktiske variable. Du trenger ikke tenke
på å bytte ut reserverte tegn (slik som anførselstegn) da den underliggende
databasedriveren håndterer dette for deg. Bindingen mellom markører og 
variable må defineres før SQL'en kjøres.

~~~
[php]
// en uttrykk med to markører ":username" og ":email"
$sql = "INSERT INTO users(username, email) VALUES(:username,:email)";
$command = $connection->createCommand($sql);

// bytt ut markøren ":username" med det faktiske brukernavnet
$command->bindParam(":username", $username, PDO::PARAM_STR);

// bytt ut markøren ":email" med den faktiske e-postadressen
$command->bindParam(":email", $email, PDO::PARAM_STR);
$command->execute();

// sett inn en rad til med et nytt sett med variable
$command->bindParam(":username", $username2, PDO::PARAM_STR);
$command->bindParam(":email", $email2, PDO::PARAM_STR);
$command->execute();
~~~

Metodene [bindParam()|CDbCommand::bindParam] og
[bindValue()|CDbCommand::bindValue] er veldig like. Den eneste forskjellen
er at den første kobler variable med en referanse til en PHP variabel, mens
den siste med verdien av en PHP variabel. For variable som inneholder 
store mengder data anbefales det å bruke den første.

For flere detaljer om kobling av variable se [PHP-manualen](http://www.php.net/manual/en/pdostatement.bindparam.php).

Kobling av kolonner
-------------------

Du kan også koble kolonner i et resultatsett med variabler i PHP slik at 
de automatisk oppdateres hver gang du henter ut en rad.

~~~
[php]
$sql = "SELECT username, email FROM users";
$dataReader = $connection->createCommand($sql)->query();

// koble første kolonne (username) med variabelen $username
$dataReader->bindColumn(1, $username);

// koble andre kolonne (email) med variabelen $email
$dataReader->bindColumn(2, $email);

while($dataReader->read() !== false)
{
    // $username og $email inneholder brukernavnet og e-postadressen for den gjeldene raden
}
~~~

<div class="revision">$Id: database.dao.txt 857 2009-03-20 17:31:09Z qiang.xue $</div>
