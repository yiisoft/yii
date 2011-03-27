Data Access Objects (DAO)
=========================

Data Access Objects (Datenzugriffsobjekte, DAO) bieten eine einheitliche
Schnittstelle für den Zugriff auf unterschiedliche Datenbanksysteme (DMBS).
Verwendet man DAO, kann auf ein anderes DBMS umgestellt werden, ohne den
entsprechenden Code ändern zu müssen.

Yii DAO basieren auf den [PHP Data Objects
(PDO)](http://php.net/manual/en/book.pdo.php).
Diese PHP-Erweiterung ermöglicht vereinheitlichten Zugriff auf viele bekannte
DMBS, wie z.B. MySQL und PostgreSQL. Möchte man Yii DAO verwenden, muss daher
die PDO-Erweiterung und die entsprechenden PDO-Datenbanktreiber 
(z.B. `PDO_MYSQL`) installiert sein.

Yii DAO besteht im Wesentlichen aus den folgenden vier Klassen:

   - [CDbConnection]: entspricht einer Datenbankverbindung
   - [CDbCommand]: entspricht einer auzuführende SQL-Anweisung
   - [CDbDataReader]: repräsentiert einen Datenstrom von Ergebniszeilen mit
"Vorwärts-Cursor" (forward-only)
   - [CDbTransaction]: entspricht eine Datenbank-Transaktion.
  
In den folgenden Abschnitten zeigen wir, wie man mit DAO arbeitet.

Aufbauen einer Datenbankverbindung
----------------------------------

Um eine Verbindung zur Datenbank aufzubauen, muss eine [CDbConnection]-Instanz
erzeugt und aktiviert werden. Die Anmeldeinformationen werden dabei in Form
eines DSN (Data Source Name) angegeben. Auch Benutzername und Passwort können 
erforderlich sein. Tritt während des Verbindungsaufbaus ein Fehler auf 
(weil z.B. der DSN ungültig oder Benutzername bzw. Passwort falsch waren), 
wird eine Exception ausgelöst.

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// Verbindung aufbauen. Sie können mögliche Exceptions 
// mit try...catch auffangen
$connection->active=true;
......
$connection->active=false;  // Verbindung beenden
~~~

Das DSN-Format hängt vom benutzten PDO-Datenbanktreiber ab. Im Allgemeinen
enthält ein DSN den PDO-Treibernamen, gefolgt von einem Doppelpunkt, gefolgt
von der treiberspezifischen Verbindungssyntax. Nähere Informationen finden Sie
in der [PDO-Dokumentation](http://www.php.net/manual/de/pdo.construct.php).
Die gebräuchlichsten DSN-Formate sind:

   - SQLite: `sqlite:/pfad/zu/dbdatei`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`
   - SQL Server: `mssql:host=localhost;dbname=testdb`
   - Oracle: `oci:dbname=//localhost:1521/testdb`

Da [CDbConnection] von [CApplicationComponent] abgeleitet ist, kann man sie
auch als [Anwendungskomponente](/doc/guide/basics.application#application-component)
verwenden. Dazu erstellt man in der [Anwendungskonfiguration](/doc/guide/basics.application#application-configuration)
eine Komponente `db` (die allerdings auch beliebig anders heißen kann):

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
			'emulatePrepare'=>true,  // wird von einigen MySQL-Installationen benötigt
		),
	),
)
~~~

Über `Yii::app()->db` kann man dann auf eine bereits aktivierte
Verbindung zugreifen, es sei denn, [CDbConnection::autoConnect]
wurde auf false gesetzt. Auf diese Weise steht in der gesamten 
Anwendung die selbe DB-Verbindung zur Verfügung. 
 
Ausführen von SQL-Anweisungen
-----------------------------

Steht die Datenbankverbindung, kann man mit Hilfe von [CDbCommand] 
SQL-Anweisungen ausführen. Eine [CDbCommand]-Instanz wird erzeugt, indem
man [CDbConnection::createCommand()] mit der entsprechenden SQL-Anweisung
aufruft:

~~~
[php]
$connection=Yii::app()->db;   // Vorausgesetzt, Sie haben eine "db"-Komponente konfiguriert
// Falls nicht, können Sie auch eine neue Verbindung erstellen:
// $connection=new CDbConnection($dsn,$username,$password);
$command=$connection->createCommand($sql);
// Falls erforderlich, kann man die SQL-Anweisung so aktualisieren:
// $command->text=$neuesSQL;
~~~

Die Anweisung wird beim Aufruf einer dieser [CDbCommand]-Methoden ausgeführt:

   - [query()|CDbCommand::query]: führt SQL-Abfragen wie `SELECT` aus, die
ein Ergebnis zurückliefern. Falls nötig, gibt sie eine [CDbDataReader]-Instanz
zurück, mit der man die Datenzeilen des Ergebnisses durchlaufen kann. Der Einfachheit
halber stehen auch einige `queryXXX()`-Methoden bereit, die das Abfrageergebnis
direkt zurückliefern.

   - [execute()|CDbCommand::execute]: führt sämtliche sonstigen SQL-Anweisungen,
wie `INSERT`, `UPDATE` und `DELETE` aus. Bei Erfolg liefert sie die Anzahl der 
betroffenen Zeilen zurück.

Tritt bei der Ausführung ein Fehler auf, wird eine Exception ausgelöst.

~~~
[php]
$dataReader=$command->query();   // Führt eine SQL-Abfrage aus (SELECT...)
$rowCount=$command->execute();   // Führt eine sonstige SQL-Anweisung aus
$rows=$command->queryAll();      // Abfragen und Zurückgeben aller Zeilen des Ergebnisses
$row=$command->queryRow();       // Abfragen und Zurückgeben der ersten Zeile des Ergebnisses
$column=$command->queryColumn(); // Abfragen und Zurückgeben der ersten Spalte des Ergebnisses
$value=$command->queryScalar();  // Abfragen und Zurückgeben des ersten Feldes in der ersten Zeile
~~~

Auslesen von Abfrageergebnissen
-------------------------------

Hat man von [CDbCommand::query()] eine Instanz von [CDbDataReader]
zurückerhalten, kann man die einzelnen Ergebniszeilen durch wiederholten 
Aufruf von [CDbDataReader::read()] abfragen. Auch der Einsatz in einer
foreach-Schleife ist möglich:

~~~
[php]
$dataReader=$command->query();
// Wiederholter Aufruf von read() bis false zurückgegeben wird
while(($row=$dataReader->read())!==false) { ... }
// Mit foreach wird jede Datenzeile durchlaufen
foreach($dataReader as $row) { ... }
// Abfragen aller Zeilen in ein einzelnes Array
$rows=$dataReader->readAll();
~~~

> Note|Hinweis: Anders als [query()|CDbCommand::query] liefern alle
`queryXXX()`-Methoden die Daten direkt zurück. Zum Beispiel liefert
[queryRow()|CDbCommand::queryRow] ein Array mit den Daten der ersten Zeile des
Abfrageergebnisses.

Verwenden von Transaktionen
---------------------------

Wenn eine Anwendung mehrere lesende und/oder schreibende Anweisungen ausführt, 
ist es wichtig, dass die Datenbank am Ende nicht in einem inkonsistenten Zustand 
verbleibt, weil nicht alle Anweisungen erfolgreich abgeschlossen werden
konnten. Zu diesem Zweck leitet man am besten eine Transaktion ein, die in 
Yii durch eine Instanz von [CDbTransaction] repräsentiert wird.

   - Transaktion beginnen.
   - Abfragen der Reihe nach ausführen. Änderungen sind von außen noch nicht sichtbar.
   - Abschließen der Transaktion mit Commit. Bei Erfolg sind die Änderungen danach auch von außen sichtbar.
   - Scheitert eine der Anfragen, wird die ganze Transaktion zurückgefahren.

Diesen Ablauf kann man in Yii so implementieren:

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... weitere SQL Abfragen
	$transaction->commit();
}
catch(Exception $e) // Eine Exception wird ausgelöst, falls eine Abfrage fehlschlägt
{
	$transaction->rollBack();
}
~~~

Binden von Parametern
---------------------

Um Angriffe wie [SQL-Injection](http://de.wikipedia.org/wiki/SQL-Injection) zu
vermeiden und um mehrfach auszuführende SQL-Anweisungen zu beschleunigen,
kann man eine Anweisung "vorbereiten" (engl. prepare), die optional auch
Platzhalter für Parameter enthalten kann. Beim sogenannten "Binden" werden 
diese Platzhalter dann durch die eigentlichen Parameterwerte ersetzt.

Platzhalter können entweder aus einer eindeutigen Zeichenkette oder einem
Fragezeichen bestehen. Mit [CDbCommand::bindParam()] oder [CDbCommand::bindValue()] 
werden die eigentlichen Parameter an die Stelle der Platzhalter gesetzt.
Sonderzeichen in diesen Parametern bedürfen dabei keiner speziellen
Beachtung. Der zugrundeliegende Datenbanktreiber kümmert sich automatisch 
um deren korrekte und sichere Verarbeitung. Dieses Binden muss vor dem
Ausführen der Anweisung erfolgen.

~~~
[php]
// SQL-Anweisung mit zwei Platzhaltern ":username" und ":email"
$sql="INSERT INTO tbl_user(username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// Ersetzt ":username" durch den tatsächlichen Benutzernamen
$command->bindParam(":username",$username,PDO::PARAM_STR);
// Ersetzt ":email" durch die tatsächliche E-Mail Adresse
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// Fügt eine weitere Zeile mit anderen Parametern ein
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

Der einzige Unterschied zwischen den erwähnten Befehlen besteht darin,
dass [bindValue()|CDbCommand::bindValue()] einen konkreten Wert an einen 
Platzhalter bindet, während [bindParam()|CDbCommand::bindParam] den Platzhalter 
an eine Referenz auf eine PHP-Variable bindet. Bei umfangreichen
Parameterdaten ist letzteres aus Performancegründen zu bevorzugen.

Weitere Einzelheiten zum Binden von Parametern finden Sie in der
[entsprechenden PHP Dokumentation](http://www.php.net/manual/de/pdostatement.bindparam.php).

Binden von Spalten
------------------

Man kann auch ganze Spalten aus einem Abfrageergebnis an PHP Variablen binden.
Bei jedem Zugriff auf eine Zeile sind diese Variablen dann automatisch mit den
aktuellen Zeilendaten befüllt.

~~~
[php]
$sql="SELECT username, email FROM tbl_user";
$dataReader=$connection->createCommand($sql)->query();
// Bindet die 1. Spalte (username) an die Variable $username
$dataReader->bindColumn(1,$username);
// Bindet die 2. Spalte (email) an die Variable $email
/$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username und $email enthalten Benutzernamen und die E-Mail-Adresse der aktuellen Zeile
}
~~~

Verwenden eines Tabellenpräfix
------------------------------

Ein Tabellenpräfix wird allen Tabellennamen der aktuellen Verbindung
vorangestellt.  Es wird meist in gemeinsam genutzten Hosting-Umgebungen verwendet, wo mehrere
Anwendungen sich eine einzelne Datenbank teilen und zur Unterscheidung eben
verschiedene Präfixe verwenden. Eine Anwendung könnte z.B. `tbl_`, eine andere
`yii_` vor alle Tabellennamen stellen.

Ein Tabellenpräfix wird über die Eigenschaft [CDbConnection::tablePrefix]
konfiguriert. In SQL-Ausdrücken kann man dann `{{TabellenName}}` statt
des Tabellennamens verwenden, wobei `TabellenName` dem Namen
ohne Präfix entspricht. Wenn die Datenbank z.B. eine Tabelle `tbl_user` 
enthält und `tbl_` als Tabellenpräfix konfiguriert wurde,
kann eine Anfrage so aussehen:

~~~
[php]
$sql='SELECT * FROM {{user}}';
$users=$connection->createCommand($sql)->queryAll();
~~~

<div class="revision">$Id: database.dao.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
