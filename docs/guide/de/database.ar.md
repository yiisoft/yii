ActiveRecord
============

Man kann so gut wie alle Datenbankaufgaben mit Yii-DAO lösen. In 90% der Fälle
führt man aber immer wieder die gleichen Anweisungen  zum Lesen, Schreiben,
Aktualisieren und Löschen von Datensätzen (CRUD) aus. Außerdem erschwert
es die Codewartung, wenn zu viele SQL-Anweisungen darin vorkommen.
Als Alternative bieten sich daher oft ActiveRecords an.

ActiveRecords (oder kurz AR) sind eine verbreitete Technik zur sog. objektrelationalen
Abbildung (engl.: object-relational mapping,ORM). Jede AR-Klasse steht für
eine Tabelle (oder einen View) in der Datenbank. Die Spalten der Tabelle
werden zu AR-Klasseneigenschaften. Eine einzelne Tabellenzeile wird zu einem
AR-Objekt, das auch gleich Methoden für die üblichen CRUD-Operationen
bereitstellt. So kann man durch AR objektorientiert mit DB-Daten arbeiten.
Um zum Beispiel eine neue Zeile in die Tabelle `tbl_post` einzufügen, schreibt
man:

~~~
[php]
$post=new Post;
$post->title='Beispielbeitrag';
$post->content='Inhalt des Beitrags';
$post->save();
~~~

In diesem Kapitel geht es darum, wie man AR-Instanzen anlegt und für
CRUD-Operationen verwendet. Im nächsten Kapitel zeigen wir dann, wie man mit
Relationen arbeitet. Sämtliche Beispiele basieren auf der
folgenden Datenbanktabelle. Beachten Sie dabei bitte, dass Sie SQL AUTOINCREMENT
durch AUTO_INCREMENT ersetzen müssen, falls Sie MySQL verwenden.

~~~
[sql]
CREATE TABLE tbl_post (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	create_time INTEGER NOT NULL
);
~~~

> Note|Hinweis: AR ist nicht unbedingt für alle Datenbankaufgaben geeignet.
AR ist zweckmäßig, um Datenbanktabellen in PHP abzubilden sowie für
Abfragen, die kein komplexes SQL erfordern. In komplizierten Fällen sollte
man stattdessen direkt Yii-DAO verwenden.


Aufbau einer DB-Verbindung
--------------------------

Um auf die Datenbank zugreifen zu können, benötigt AR eine Datenbankverbindung.
Standardmäßig erwartet AR diese in der Applikationskomponente `db`. Hier ein
Beispiel, wie diese Komponente konfiguriert werden kann:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:path/to/dbfile',
            // Schema Caching einschalten, um die Performance zu verbessern
			// 'schemaCachingDuration'=>3600,
		),
	),
);
~~~

> Tip|Tipp: ActiveRecord benötigt einige Metadaten über Tabellen, um
zum Beispiel die Spalteninformationen zu ermitteln. Das Lesen und
Analysieren dieser Daten braucht Zeit. Falls sich das Schema Ihrer Datenbank
kaum mehr ändert, sollten Sie daher Schema-Caching aktivieren, indem Sie die Eigenschaft
[CDbConnection::schemaCachingDuration] auf
einen Wert größer 0 setzen.

Die AR-Unterstützung ist auf bestimmte Datenbankmanagementsysteme (DBMS) begrenzt.
Derzeit werden folgende DBMS unterstützt:

   - [MySQL 4.1 oder später](http://www.mysql.com)
   - [PostgreSQL 7.3 oder später](http://www.postgres.com)
   - [SQLite 2 und 3](http://www.sqlite.org)
   - [Microsoft SQL Server 2000 oder höher](http://www.microsoft.com/sqlserver/)
   - [Oracle](http://www.oracle.com)

Falls Sie statt `db` eine andere Verbindungskomponente verwenden möchten oder
Sie mit AR auf mehreren Datenbanken arbeiten, können Sie
[CActiveRecord::getDbConnection()] in Ihrer Klasse überschreiben.
Die [CActiveRecord]-Klasse ist die Basisklasse für alle AR-Klassen.

> Tip|Tipp: Es gibt zwei Methoden, um mit AR auf mehren Datenbanken zu arbeiten.
Falls die Datenbankschemata sich unterscheiden, können Sie mehrere
AR-Basisklassen anlegen, die jeweils [getDbConnection()|CActiveRecord::getDbConnection]
überschreiben. Ist das Schema überall gleich, ist es günstiger, die statische Variable
[CActiveRecord::db] dynamisch zu ändern.

Definieren von AR-Klassen
-------------------------

Um auf eine Datenbanktabelle zuzugreifen, muss zuerst eine neue AR-Klasse
von [CActiveRecord] abgeleitet werden. Jede Klasse repräsentiert
eine einzelne Datenbanktabelle, eine AR-Instanz eine Zeile in dieser Tabelle.
Das folgende Beispiel zeigt den Minimalcode einer AR-Klasse. In diesem Fall
für die Tabelle `tbl_post`.

~~~
[php]
class Post extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
~~~

> Tip|Tipp: Da AR-Klassen oft an vielen Stellen verwendet werden, importiert
> man am besten gleich das ganze Modelverzeichnis. Liegen alle AR-Klassen
> (wie meist üblich) im Verzeichnis `protected/models`, wird dies wie folgt
> konfiguriert:
> ~~~
> [php]
> return array(
> 	'import'=>array(
> 		'application.models.*',
> 	),
> );
> ~~~

Standardmäßig hat die AR-Klasse den gleichen Namen wie die Datenbanktabelle,
Sie können aber auch einen abweichenden Tabellennamen verwenden, indem Sie
die [tableName()|CActiveRecord::tableName]-Methode überschreiben.
Die [model()|CActiveRecord::model]-Methode muss in jeder AR-Klasse definiert
werden, worauf wir in Kürze noch näher eingehen werden.

> Info: Um ein [Tabellenpräfix](/doc/guide/database.dao#using-table-prefix) zu
> verwenden, kann man die  [tableName()|CActiveRecord::tableName]-Methode auch
> wie folgt überschreiben:
> ~~~
> [php]
> public function tableName()
> {
>     return '{{post}}';
> }
> ~~~
> Statt eines vollständigen Tabellennamens, gibt man den Namen der
> Tabelle ohne Präfix, aber dafür in doppelten geschweiften Klammern zurück.

Über die Objekteigenschaften kann man auf die Daten einer Tabellenzeile
zugreifen. Um zum Beispiel den Wert für die Spalte `title` zu setzen,
schreibt man:

~~~
[php]
$post=new Post;
$post->title='Ein Beispielbeitrag';
~~~

Erstaunlicherweise kann man auf die `title`-Eigenschaft zugreifen, obwohl
diese in der `Post`-Klasse gar nicht deklariert wurde. Das liegt daran,
das AR die magische PHP-Methode `__get()` (bzw. `__set()`) verwendet, um
Tabellenspalten wie Objekteigenschaften verfügbar zu machen. Versucht man
jedoch, ein nicht existentes Feld anzusprechen, wird eine Exception ausgelöst.

> Info|Info: In diesem Handbuch werden alle Tabellen- und Spaltennamen
> kleingeschrieben, da verschiedene DBMS mit Groß-/Kleinschreibung
> unterschiedlich umgehen. PostgreSQL zum Beispiel ignoriert die Schreibweise
> standardmäßig. Falls ein Spaltenname Groß- und Kleinbuchstaben enthält, muss
> er dort in Anführungszeichen gesetzt werden. Durch konsequente
> Kleinschreibung umgeht man dieses Problem.

AR erwartet, dass für alle Tabellen korrekte Primärschlüssel in der Datenbank
definiert wurden. Ist das für eine Tabelle nicht der Fall, muss die Methode
`primaryKey()` den Primärschlüssel wie folgt zurückliefern:

~~~
[php]
public function primaryKey()
{
	return 'id';
	// Für zusammengesetzte Primärschlüssel kann ein Array wie folgt
	// zurückgegeben werden:
	// return array('pk1', 'pk2');
}
~~~



Einfügen von Datensätzen
------------------------

Um eine neue Zeile in eine Datenbanktabelle einzufügen, erzeugt man eine
Objektinstanz der zugehörigen AR-Klasse, setzt die entsprechenden
Eigenschaften und ruft die [save()|CActiveRecord::save]-Methode auf.

~~~
[php]
$post=new Post;
$post->title='Beispielbeitrag';
$post->content='Inhalt des Beispielbeitrags';
$post->create_time=time();
$post->save();
~~~

Ist in der Datenbank AUTOINCREMENT für den Primärschlüssel aktiviert, enthält
die neue AR-Instanz nach dem Speichern automatisch den vergebenen
Schlüssel. Im Beispiel enhält also das `id`-Attribut den neuen Schlüssel, ohne
dass dieses explizit gesetzt wurde.

Wurden im Tabellenschema der DB statische Vorgabewerte für bestimmte Spalten
definiert (z.B. ein String oder eine Zahl), werden diese nach dem Speichern
ebenfalls automatisch bei den entsprechenden Eigenschaften gesetzt. Möchte
man diese Vorgabewerte ändern, kann man das in der AR-Klasse wie folgt
erreichen:

~~~
[php]
class Post extends CActiveRecord
{
	public $title='Bitte den Titel eingeben';
	......
}

$post=new Post;
echo $post->title;  // Dies führt zur Anzeige von: Bitte den Titel eingeben
~~~

Man kann einem Attribut auch einen Wert vom Typ
[CDbExpression] zuweisen, bevor der Datensatz in der Datenbank gespeichert
wird. Um zum Beispiel einen Zeitstempel zu speichern, wie er von der
MySQL-Funktion `NOW()` geliefert wird, schreibt man:

~~~
[php]
$post=new Post;
$post->create_time=new CDbExpression('NOW()');
// $post->create_time='NOW()'; funktioniert nicht,
// da 'NOW()' wie ein String behandelt werden würde.
$post->save();
~~~

> Tip|Tipp: Obwohl man durch AR keine umständlichen SQL-Ausdrücke mehr
> schreiben muss, kann es zur Fehlersuche nützlich sein, die
> im Hintergrund verwendeten SQL-Befehle zu untersuchen. Das geht mit
> Hilfe des [Log-Features](/doc/guide/topics.logging) von Yii. Konfiguriert
> man zum Beispiel eine [CWebLogRoute], werden die ausgeführten SQL-Befehle
> am Ende der Seite angezeigt.  Setzt man [CDbConnection::enableParamLogging]
> auf `true`, werden dort auch die gebundenen Parameterwerte angezeigt.



Lesen von Datensätzen
---------------------

Um Daten aus der Datenbanktabelle zu lesen verwendet man eine der folgenden
`find`-Methoden:

~~~
[php]
// Finde die erste Zeile, die die angegebene Bedingung erfüllt
$post=Post::model()->find($condition,$params);
// Finde die Zeile mit dem angegebenen Primärschlüssel
$post=Post::model()->findByPk($postID,$condition,$params);
// Finde die Zeile mit den angegeben Attributwerten
$post=Post::model()->findByAttributes($attributes,$condition,$params);
// Finde die erste Zeile unter Anwenden der angegeben SQL-Anweisung
$post=Post::model()->findBySql($sql,$params);
~~~

Oben wird die `find`-Methode auf `Post::model()` aufgerufen. Wie Sie sich
erinnern, ist die statische `model()`-Methode bei jeder AR-Klasse nötig.
Sie gibt eine AR-Instanz zurück, mit der man auf Methoden der Klassenebene
zugreifen kann. Das ist ganz ähnlich zu statischen Klassenmethoden, allerdings
hier in einem Objektkontext.

Findet die `find`-Methode eine Zeile zu den gegebenen Abfragebedingungen,
liefert sie ein `Post`-Objekt mit den entsprechenden Daten zurück. Die
geladenen Werte können dann wie normale Objekteigenschaften z.B. mit
`$post->title` ausgelesen werden.

Wurde keine entsprechende Zeile gefunden, liefert `find` den Wert null zurück.

Die Abfragebedingungen werden über die Argumente `$condition` und `$params` an
`find` übergeben. `$condition` kann ein String sein, der die
`WHERE`-Klausel in einer SQL Abfrage darstellt, `$params` ein Array mit
Parametern, falls `$condition` Platzhalter enthält. Hier ein Beispiel:

~~~
[php]
// Finde die Zeile mit postID=10
$post=Post::model()->find('postID=:postID', array(':postID'=>10));
~~~

> Note|Hinweis: Bei manchen Datenbanksystemen muss die `postID`-Spalte in
obigem Beispiel escaped werden. Bei PostgreSQL, muss `$condition` zum
Beispiel `"postID"=:postID` lauten, da PostgreSQL
standardmäßig die Groß-/Kleinschreibung von Spaltennamen nicht berücksichtigt.

`$condition` kann auch viel komplexere Abfragebedingungen enthalten, indem man
statt eines Strings eine Instanz von [CDbCriteria] übergibt. Damit können dann
weitere Kriterien angegeben werden:

~~~
[php]
$criteria=new CDbCriteria;
$criteria->select='title';  // Nur die 'title' Spalte wird ausgewählt
$criteria->condition='postID=:postID';
$criteria->params=array(':postID'=>10);
$post=Post::model()->find($criteria); // $params ist nicht nötig
~~~

Beachten Sie, dass das `$params`-Argument nicht mehr benötigt wird,
da die Parameter bereits in [CDbCriteria] angegeben werden können.

Alternativ zu einem [CDbCriteria]-Objekt, kann auch ein Array an die
`find`-Methode übergeben werden. Die Schlüssel und Werte des Array entsprechen
dann den Namen und Werten der Eigenschaften von CDbCriteria. Das obige Beispiel
kann daher wie folgt umformuliert werden:

~~~
[php]
$post=Post::model()->find(array(
	'select'=>'title',
	'condition'=>'postID=:postID',
	'params'=>array(':postID'=>10),
));
~~~

> Info|Info: Soll nach bestimmten Spaltenwerten in einer Tabelle gesucht
> werden, kann man dazu [findByAttributes()|CActiveRecord::findByAttributes]
> verwenden. `$attributes` ist ein Array mit Spaltennamen als Schlüsseln und
> den gesuchten Werten als Arraywerten.  In einigen Frameworks wird dies über
> Methoden wie z.B. `findByNameAndTitle` gelöst. Obwohl diese Herangehensweise
> ihre Reize besitzt, sorgt sie oft für Verwirrung und Konflikte bzw.
> Problemen im Zusammenhang mit der Groß-/Kleinschreibung von Spaltennamen.

Erfüllen mehrere Datenzeilen die Abfragebedingung, können diese alle auf
einmal mit den folgenden `findAll`-Methoden bezogen werden. Jede von Ihnen hat
ihr Pendant bei den eben beschriebenen `find`-Methoden:

~~~
[php]
// Finde alle Zeilen, die die angegebene Bedingung erfüllen
$posts=Post::model()->findAll($condition,$params);
// Finde alle Zeilen mit dem angegebenen Primärschlüsseln
$posts=Post::model()->findAllByPk($postIDs,$condition,$params);
// Finde alle Zeilen mit den angegeben Attributwerten
$posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
// Finde alle Zeilen unter Anwendung der angegeben SQL-Anweisung
$posts=Post::model()->findAllBySql($sql,$params);
~~~

Im Unterschied zu `find` liefern die `findAll`-Methoden ein leeres Array
zurück, falls keine entsprechende Zeile gefunden wurde.

AR bietet noch weitere Methoden, die viele gängige Aufgaben vereinfachen:

~~~
[php]
// Liefert die Anzahl der Zeilen, die die angegebene Bedingung erfüllen
$n=Post::model()->count($condition,$params);
// Liefert die Anzahl der Zeilen durch Anwenden der angegeben SQL-Anweisung
$n=Post::model()->countBySql($sql,$params);
// Prüft, ob mindestens eine Zeile die angegebene Bedingung erfüllt
$exists=Post::model()->exists($condition,$params);
~~~

Aktualisieren von Datensätzen
-----------------------------

Wurde eine AR-Instanz mit den Werten einer Tabellenzeile befüllt, können
diese verändert und in die Datenbank zurückgespeichert werden.

~~~
[php]
$post=Post::model()->findByPk(10);
$post->title='Geänderter Titel eines Beitrags';
$post->save(); // Änderung in der Datenbank speichern
~~~

Wie Sie sehen, wird die [save()|CActiveRecord::save]-Methode auch zum
Aktualisieren von Einträgen verwendet. Wurde eine Instanz also mit `new`
erzeugt, fügt [save()|CActiveRecord::save] einen neuen Datensatz ein. Stammt
das Objekt von einer Abfrage, aktualisiert [save()|CActiveRecord::save] die
entsprechende Tabellenzeile. Mit [CActiveRecord::isNewRecord] kann man prüfen,
ob es sich um ein neues Objekt handelt oder nicht.

Man kann einzelne oder mehrere Zeilen in einer Tabelle auch aktualisieren,
ohne sie vorher zu laden.AR bietet dazu die folgenden nützlichen Methoden
auf Klassenebene an:

~~~
[php]
// Aktualisiere die Zeilen, die die angegebene Bedingung erfüllen
Post::model()->updateAll($attribute,$bedingung,$params);
// Aktualisiere die Zeilen mit dem/den angegebenen Primärschlüssel(n),
// die die angegebene Bedingung erfüllen
Post::model()->updateByPk($pk,$attribute,$bedingung,$params);
// Aktualisiere Zählerspalten in den Zeilen, die die angegebene Bedingung erfüllen
Post::model()->updateCounters($zaehler,$bedingung,$params);
~~~

Hier ist `$attribute` ein Array von Werten, die durch Feldnamen indiziert sind.
`$zaehler` ist ein Array ansteigender Werte, die durch Feldnamen indiziert
sind. `$bedingung` und `$params` wurden bereits in den vorangegangenen Abschnitten
beschrieben.

Löschen von Datensätzen
-----------------------

Man kann eine Datenzeile auch löschen, wenn die AR-Instanz dieser Zeile
entspricht:

~~~
[php]
$post=Post::model()->findByPk(10); // Unter der Annahme, es gibt einen `Post` mit ID 10
$post->delete(); // Lösche diese Zeile in der Datenbanktabelle
~~~

Beachten Sie, dass die AR-Instanz nach dem Löschen unverändert bleibt, auch
wenn die entsprechende Zeile in der Datenbank bereits gelöscht wurde.

Mit diesen Methoden (wieder auf Klassenebene) kann man Zeilen auch löschen,
ohne sie vorher laden zu müssen:

~~~
[php]
// Lösche die Zeilen, die die angegebene Bedingung erfüllen
Post::model()->deleteAll($condition,$params);
// Lösche die Zeilen mit den angegebenen Primärschlüsseln,
// die die angegebene Bedingung erfüllen
Post::model()->deleteByPk($pk,$condition,$params);
~~~

Validieren der Daten
--------------------

Vor dem Einfügen oder Aktualisieren eines Datensatzes, muss oftmals geprüft
werden, ob die Werte auch bestimmten Regeln entsprechen. Diesen Vorgang nennt
man auch `Validierung`. Sie ist insbesondere wichtig, wenn die zu speichernden
Werte von Endanwendern stammen. Sämtlichen Daten von der Clientseite sollte man
grundsätzlich nie vertrauen.

AR führt die Validierung beim Aufruf von [save()|CActiveRecord::save]
automatisch durch. Die Prüfung erfolgt anhand der Regeln, die in der
[rules()|CModel::rules]-Methode der AR-Klasse angegeben wurden.
Weitere Einzelheiten zur Definition von Prüfregeln finden Sie im Abschnitt
[Angeben von Validierungsregeln](/doc/guide/form.model#declaring-validation-rules).
Beim Speichern kommt es in der Regel zu diesem typischen Ablauf:

~~~
[php]
if($post->save())
{
	// Die Daten sind gültig und wurden erfolgreich eingefügt/aktualisiert
}
else
{
	// Die Daten sind ungültig. Fehlermeldungen mit getErrors() abfragen
}
~~~

Stammen die einzufügenden oder zu aktualisierenden Daten von einem
HTML-Formular, müssen diese den entsprechenden AR-Eigenschaften zugewiesen
werden:

~~~
[php]
$post->title=$_POST['title'];
$post->content=$_POST['content'];
$post->save();
~~~

Hat eine Tabelle viele Spalten, müsste man also umständlich alle Felder
einzeln zuweisen. Über die [attributes|CActiveRecord::attributes]-Eigenschaft
kann man das vermeiden, wie im folgenden Beispiel zu sehen. Weitere Details
hierzu finden Sie im Abschnitt
[Sichere Attributzuweisungen](/doc/guide/form.model#securing-attribute-assignments)
sowie im Kapitel [Erstellen der Action](/doc/guide/form.action).

~~~
[php]
// Angenommen, $_POST['Post'] ist ein Array von Werten,
// das durch Feldnamen indiziert wurde
$post->attributes=$_POST['Post'];
$post->save();
~~~


Vergleichen von Records
-----------------------

Auch AR-Instanzen werden wie die entsprechenden Tabellenzeilen eindeutig durch
ihre Primärschlüsselwerte identifiziert. Man kann zwei Instanzen also einfach
über deren Primärschlüssel vergleichen, vorausgesetzt, sie gehören zur selben
Klasse. Einfacher geht dies jedoch mit [CActiveRecord::equals()].

> Info|Info: Im Unterschied zu anderen Frameworks unterstützen Yiis AR auch
> zusammengesetzte Primärschlüssel. Sie bestehen aus zwei oder mehr Feldern
> und werden daher als Array dargestellt. Die Eigenschaft
> [primaryKey|CActiveRecord::primaryKey] liefert den Wert des Primärschlüssels einer AR-Instanz.


Anpassung
---------

Zur Anpassung an bestimmte Abläufe, sind in [CActiveRecord] einige Methoden
als Platzhalter implementiert, die man in eigenen AR-Klassen einfach überschreiben kann.

   - [beforeValidate|CModel::beforeValidate] und [afterValidate|CModel::afterValidate]:
werden vor bzw. nach der Validierung aufgerufen

   - [beforeSave|CActiveRecord::beforeSave] und [afterSave|CActiveRecord::afterSave]:
werden vor bzw. nach dem Speichern aufgerufen

   - [beforeDelete|CActiveRecord::beforeDelete] und [afterDelete|CActiveRecord::afterDelete]:
werden vor bzw. nach dem Löschen einer AR-Instanz aufgerufen

   - [afterConstruct|CActiveRecord::afterConstruct]: wird nach dem Erzeugen
einer neuen Instanz aufgerufen

   - [beforeFind|CActiveRecord::beforeFind]: werden vor einer Suchabfrage
(z.B. `find()`, `findAll()`) ausgeführt.

   - [afterFind|CActiveRecord::afterFind]: wird aufgerufen, nachdem eine
Instanz aus einem Suchergebnis erzeugt wurde.


Transaktionen mit AR
--------------------

Über die [dbConnection|CActiveRecord::dbConnection]-Eigenschaft kann bei jeder
AR-Instanz auf die zugrundeliegende [CDbConnection] zugegriffen werden. Bei
Bedarf kann man so das Transaktions-Feature von Yii-DAO verwenden:

~~~
[php]
$model=Post::model();
$transaction=$model->dbConnection->beginTransaction();
try
{
	// Finden und Speichern sind zwei Schritte, zwischen denen eine andere
	// Anfrage vorkommen könnte. Wir verwenden daher eine Transaktion, um
	// Konsistenz und Integrität zu gewährleisten.
	$post=$model->findByPk(10);
	$post->title='Titel eines neuen Beitrags';
	$post->save();
	$transaction->commit();
}
catch(Exception $e)
{
	$transaction->rollBack();
}
~~~


Scopes
------

> Note|Hinweis: Die Idee für Scopes stammt ursprünglich von Ruby on Rails.

Ein *Scope* (engl.: named scope, sinngem.: benannter Bereich) stellt ein
vordefiniertes Abfragekriterium dar, das man bei einem AR unter einem bestimmten Namen
abrufen und sogar mit anderen Scopes kombinieren kann.

Meist werden Scopes als Array von Name-Kriterium-Paaren in der Methode
[CActiveRecord::scopes()] definiert. Der folgende Code deklariert
zum Beispiel die beiden Scopes `veroeffentlicht` und `kuerzlich` in der
Modelklasse `Beitrag`:

~~~
[php]
class Beitrag extends CActiveRecord
{
	......
	public function scopes()
	{
		return array(
			'veroeffentlicht'=>array(
				'condition'=>'status=1',
			),
			'kuerzlich'=>array(
				'order'=>'erstellZeit DESC',
				'limit'=>5,
			),
		);
	}
}
~~~

Jeder Scope besteht aus einem Array, dessen Werte zum Initialisieren
einer [CDbCriteria]-Instanz verwendet werden können. Der Scope
`kuerzlich` bestimmt zum Beispiel, dass die `order`-Eigenschaft auf
`erstell_zeit DESC` und die `limit`-Eigenschaft auf 5 gesetzt werden soll. Dies
wird also in ein Abfragekriterium übersetzt, das die letzten 5 Beiträge
zurückliefern sollte.

Scopes werden meist als Modifikatoren beim Aufruf von `find`-Methoden
verwendet. Mehrere Scopes können miteinander verkettet werden
und resultieren so in einem immer weiter eingeschränkten Abfrageergebnis.
Mit dem folgenden Code kann man so zum Beispiel die letzten 5 veröffentlichten
Beiträge abfragen:

~~~
[php]
$beitraege=Beitrag::model()->veroeffentlicht()->kuerzlich()->findAll();
~~~

Scopes müssen grundsätzlich links vom Aufruf einer
`find`-Methode stehen. Jeder einzelne von ihnen liefert ein Abfragekriterium, das
mit weiteren Kriterien kombiniert wird, inklusive demjenigen, das als
Parameter and die `find`-Methode übergeben wurde. Letztendlich fügt man
einer Abfrage also eine Liste von Filtern hinzu.

> Note|Hinweis: Scopes können nur mit Methoden auf Klassenebene
verwendet werden. Das bedeutet, dass die Methoden über `KlassenName::model()`
aufgerufen werden muss.

###Parametrisierte Scopes

Scopes können auch parametrisiert werden. Man könnte zum Beispiel
die Anzahl der Beiträge des Scopes `kuerzlich` anpassen.
Statt den Scope in der [CActiveRecord::scopes]-Methode anzugeben, wird
dazu eine eigene Methode mit dem Namen des Scopes verwendet:

~~~
[php]
public function kuerzlich($limit=5)
{
	$this->getDbCriteria()->mergeWith(array(
		'order'=>'create_time DESC',
		'limit'=>$limit,
	));
	return $this;
}
~~~

Die letzten 3 veröffentlichten Beiträge kann man dann so abfragen:

~~~
[php]
$beitraege=Beitrag::model()->veroeffentlicht()->kuerzlich(3)->findAll();
~~~

Wird der Parameter 3 weggelassen, werden standardmäßig die letzten 5 Beiträge
angezeigt.

###Standardscope

Ein Scope kann auch als Standardscope für eine Modelklasse
festgelegt werden, so dass er bei allen Abfragen (inkl. relationalen)
verwendet wird. So könnte zum Beispiel eine mehrsprachige Website ihre
Inhalte immer nur in der Sprache des aktuellen Besuchers anzeigen wollen.
Da dazu bei den allen Abfragen immer die selben Sprachkriterien verwendet werden
müssen, kann man diese Aufgabe mit einem Standardscope lösen.
Dazu wird die Methode [CActiveRecord::defaultScope] wie folgt überschrieben:

~~~
[php]
class Content extends CActiveRecord
{
	public function defaultScope()
	{
		return array(
			'condition'=>"sprache='".Yii::app()->language."'",
		);
	}
}
~~~

Dadurch verwendet der folgene Aufruf automatisch das eben festgelegte
Abfragekriterium.

~~~
[php]
$contents=Content::model()->findAll();
~~~

> Note|Hinweis: Der Standardscope wird nur für `SELECT`-Abfragen verwendet. Bei `INSERT`-, `UPDATE`- und `DELETE`-Statements wird er ignoriert.
> Innerhalb einer Scopedeklaration können außerdem keine DB-Abfragen auf die
gleiche AR-Klasse durchgeführt werden.

<div class="revision">$Id: database.ar.txt 3318 2011-06-24 21:40:34Z qiang.xue $</div>
