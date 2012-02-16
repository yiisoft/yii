Query Builder
=============

Mit dem Yii Query Builder (sinngem. "Abfrageersteller") kann man auf
objektorientierte Weise SQL-Anweisungen erstellen. Ein Entwickler kann damit die
einzelnen Teile eines SQL-Ausdrucks mittels Klassenmethoden und -eigenschaften 
festlegen. Der Query Builder baut daraus dann einen gültigen SQL-Ausdruck, der
mit den üblichen DAO-Methoden verwendet werden kann, wie es im Kapitel 
[Data Access Objects](/doc/guide/database.dao) beschrieben wurde. Eine
typische Anwendung des Query Builders zum erstellen eines SELECT-Ausdrucks
sieht so aus:

~~~
[php]
$user = Yii::app()->db->createCommand()
	->select('id, username, profile')
	->from('tbl_user u')
	->join('tbl_profile p', 'u.id=p.user_id')
	->where('id=:id', array(':id'=>$id))
	->queryRow();
~~~

Der Query Builder eignet sich am besten, wenn ein SQL-Ausdruck prozedural oder
abhängig von bestimmten Konditionen zusammengebaut werden muss. Die
wesentlichen Vorteile des Query Builders sind:

* Er erlaubt das programmatische Erzeugen komplexer SQL-Ausdrücke.

* Tabellen- und Spaltennamen werden automatisch in die richtigen Anführungszeichen
gesetzt um Konflikte mit reservierten SQL-Wörtern und Sonderzeichen zu verhindern.

* Auch Paramterwerte werden automatisch von korrekten Anführungszeichen
eingefasst und soweit möglich "gebunden" um das Risiko von SQL-Injection-Angriffen zu
minimieren.

* Er bietet ein gewisses Maß an DB-Abstraktion, was die Migration auf eine
andere DB-Platform erleichtert.


Der Query Builder muss nicht zwingend verwendet werden. Für einfache Abfragen
ist es sogar einfacher und schneller die SQL-Abfragen direkt in den Code zu schreiben.

>Note|Hinweis: Der Query Builder kann nicht verwendet werden, um eine
>vorhandene Abfrage zu verändern. Hier ein Beispiel, was nicht funktioniert:
>
> ~~~
> [php]
> $command = Yii::app()->db->createCommand('SELECT * FROM tbl_user');
> // die folgende Zeile wird den WHERE-Ausdruck NICHT anhängen:
> $command->where('id=:id', array(':id'=>$id));
> ~~~
>
> In anderen Worten: Mischen sie keine reinen SQL-Ausdrücke mit dem Query Builder.

Vorbereiten des Query Builders
------------------------------

Der Yii Query Builder steht in Form eines [CDbCommand]s zur Verfügung, also
jener Hauptklasse für DB-Abfragen, wie sie bereits in [Data Access Objects](/doc/guide/database.dao)
beschrieben wurde.

Um den Query Builder zu verwenden, erstellt man zunächst eine neu
[CDbCommand]-Instanz:

~~~
[php]
$command = Yii::app()->db->createCommand();
~~~

Das bedeutet, man holt sich zunächst über `Yii::app()->db` die DB-Verbindung
und ruft dann [CDbConnection::createCommand()] auf, um die benötigte
Objektinstanz zu erzeugen.

Beachten Sie, dass hier im Gegensatz zum [Data Access Objects](/doc/guide/database.dao)-Kapitel, 
kein SQL-Ausdruck an `createCommand()` übergeben wurde. Stattdessen werden
die einzelnen Bestandteile des SQL-Ausdrucks mit den Query Builder Methoden
wie folgt zusammengebaut.


Zusammenbau von Abfrageausdrücken
---------------------------------

Abfrageausdrücke beziehen sich auf SELECT-SQL-Ausdrücke. Der Query Builder
bietet eine Reihe von Methoden um die einzelnen Bestandteile eines
SELECT-Ausdrucks zusammenzufügen. Da alle diese Methdoen die
[CDbCommand]-Instanz zurückgeben, kann man die Aufrufe wie im Eingangsbeispiel
gezeigt verketten.

* [select()|CDbCommand::select()]: beschreibt den SELECT-Teil der Abfrage
* [selectDistinct()|CDbCommand::selectDistinct()]: beschreibt den SELECT-Teil der Abfrage und schaltet das DISTINCT-Flag ein
* [from()|CDbCommand::from()]: beschreibt den FROM-Teil der Abfrage
* [where()|CDbCommand::where()]: beschreibt den WHERE-Teil der Abfrage
* [join()|CDbCommand::join()]: hängt ein INNER JOIN-Fragment an
* [leftJoin()|CDbCommand::leftJoin()]: hängt ein LEFT OUTER JOIN-Fragment an
* [rightJoin()|CDbCommand::rightJoin()]: hängt ein RIGHT OUTER JOIN-Fragment an
* [crossJoin()|CDbCommand::crossJoin()]: hängt ein CROSS JOIN-Fragment an
* [naturalJoin()|CDbCommand::naturalJoin()]: hängt ein NATURAL JOIN-Fragment an
* [group()|CDbCommand::group()]: beschreibt den GROUP BY-Teil der Abfrage
* [having()|CDbCommand::having()]: beschreibt den HAVING-Teil der Abfrage
* [order()|CDbCommand::order()]: beschreibt den ORDER BY-Teil der Abfrage
* [limit()|CDbCommand::limit()]: beschreibt den LIMIT-Teil der Abfrage
* [offset()|CDbCommand::offset()]: beschreibt den OFFSET-Teil der Abfrage
* [union()|CDbCommand::union()]: hängt ein UNION-Fragment an


Wir beschreiben im folgenden, wie man diese Query Builder Methoden verwendet.
Der Einfachheit halber gehen wir dabei von einer MySQL-Datenbank aus. Beachten
Sie, dass sich je nach verwendetem DBMS das verwendete Zeichen für die
Anführungszeichen bei Tabellen- und Spaltennamen sowie der Werte unterscheiden kann.


### select()

~~~
[php]
function select($columns='*')
~~~

Die [select()|CDbCommand::select()]-Methode beschreibt den `SELECT`-Teil der
Abfrage. Der `$columns` Parameter gibt die SELECT-Spalten entweder als String
mit den Spaltennamen durch Komma getrennt oder als Array an. Spaltennamen
können Tabellenpräfixe und/oder Spaltenaliase enthalten. Die Methode wird die
Spaltennamen automatisch in korrekte Anführungszeichen setzen, es sei denn, eine Spalte
enthält einige Klammern (was bedeutet, dass die Spalte als DB-Ausdruck
angegeben wurde).

Hier einige Beispiele:

~~~
[php]
// SELECT *
select()
// SELECT `id`, `username`
select('id, username')
// SELECT `tbl_user`.`id`, `username` AS `name`
select('tbl_user.id, username as name')
// SELECT `id`, `username`
select(array('id', 'username'))
// SELECT `id`, count(*) as num
select(array('id', 'count(*) as num'))
~~~


### selectDistinct()

~~~
[php]
function selectDistinct($columns)
~~~

Die [selectDistinct()|CDbCommand::selectDistinct()]-Methode ähnelt
[select|CDbCommand::select], mit dem Unterschied dass es das `DISTINCT`-Flag
aktiviert. For example, `selectDistinct('id, username')` will generate the following SQL:

~~~
SELECT DISTINCT `id`, `username`
~~~


### from()

~~~
[php]
function from($tables)
~~~

Die [from()|CDbCommand::from()]-Methode beschreibt den `FROM`-Teil einer Abfrage.
Der `$tables`-Parameter gibt die Tabellennamen entweder als String mit Tabellennamen 
durch Komma getrennt oder als Array an. Tabellennamen können Schemapräfixe
(z.B. `public.tbl_user`) und/oder Tabellenaliase (z.B. `tbl_user u`)
enthalten. Die Methode setzt Tabellennamen automatisch in korrekte Anführungszeichen,
es sei denn sie enthalten Klammern (was bedeutet, die Tabelle ist in Form
einer Sub-Abfrage oder eines DB-Ausdrucks angegeben).

Hier einige Beispiele:

~~~
[php]
// FROM `tbl_user`
from('tbl_user')
// FROM `tbl_user` `u`, `public`.`tbl_profile` `p`
from('tbl_user u, public.tbl_profile p')
// FROM `tbl_user`, `tbl_profile`
from(array('tbl_user', 'tbl_profile'))
// FROM `tbl_user`, (select * from tbl_profile) p
from(array('tbl_user', '(select * from tbl_profile) p'))
~~~


### where()

~~~
[php]
function where($conditions, $params=array())
~~~

Die [where()|CDbCommand::where()]-Methode beschreibt den `WHERE`-Teil einer
Abfrage. Der `$conditions`-Parameter gibt die Abfragebedingung an und 
`$params` die Parameter die an die ganze Abfrage gebunden werden sollen.
Der `$conditions`-Parameter kann entweder ein String (z.B. `id=1`) oder ein
Array folgender Form sein:

~~~
[php]
array(operator, operand1, operand2, ...)
~~~

wobei `operator` einer der folgenden sein kann:

* `and`: Die Operanden sollen mit `AND` verbunden werden. Zum Beispiel wird
`array('and', 'id=1', 'id=2')` die Bedingung `id=1 AND id=2` erzeugen. Falls
ein Operand als Array vorliegt, wird dieser wiederum nach diesen Regeln in einen
String umgewandelt. Zum Beispiel wird `array('and', 'type=1', array('or',
'id=1', 'id=2'))` den Ausdruck `type=1 AND (id=1 OR id=2)` erzeugen. Hierbei
werden KEINE Anführungszeichen hinzugefügt bzw. kein Escaping vorgenommen.

* `or`: Analog zum `and`-Operator werden die Operanden mit OR verbunden.

* `in`: Operand 1 sollte eine Spalte oder ein DB-Ausdruck sein und Operand 2
ein Array mit der Reihe der Werte in der Operand 1 enthalten sein soll. Aus
`array('in', 'id', array(1,2,3))` wird somit `id IN (1,2,3)`. 
Diese Methode fügt Anführungszeichen hinzu wo nötig und esacped die
Werte im angegebenen Bereich.

* `not in`: Analog zum `in`-Operator wird ein `NOT IN` erzeugt.

* `like`: Operand 1 sollte eine Spalte oder ein DB-Ausdruck sein, Operand 2
ein String oder ein Array mit den Werten denen Operand 1 mit LIKE entsprechen
soll. `array('like', 'name', 'tester')` erzeugt somit `name LIKE '%tester%'`. 
Ist Operand 2 ein Array werden mehrere mit `AND` verknüpfte LIKEs erzeugt.
`array('like', 'name', array('test', 'sample'))` ergibt damit `name LIKE '%test%' AND name LIKE '%sample%'`. 
Diese Methode fügt Anführungszeichen hinzu, wo nötig und esacped die
Werte im angegebenen Bereich.

* `not like`: Analgo zum `like`-Operator wird ein `NOT LIKE` erzeugt.

* `or like`: Analog zum `like`-Operator, außer dass `OR` verwendet wird, um
mehrere LIKEs zu verbinden.

* `or not like`: Analog zum `not like`-Operator, außer dass `OR` verwendet
wird um mehrere LIKEs zu verbinden.


Hier einige Beispiele:

~~~
[php]
// WHERE id=1 or id=2
where('id=1 or id=2')
// WHERE id=:id1 or id=:id2
where('id=:id1 or id=:id2', array(':id1'=>1, ':id2'=>2))
// WHERE id=1 OR id=2
where(array('or', 'id=1', 'id=2'))
// WHERE id=1 AND (type=2 OR type=3)
where(array('and', 'id=1', array('or', 'type=2', 'type=3')))
// WHERE `id` IN (1, 2)
where(array('in', 'id', array(1, 2))
// WHERE `id` NOT IN (1, 2)
where(array('not in', 'id', array(1,2)))
// WHERE `name` LIKE '%Qiang%'
where(array('like', 'name', '%Qiang%'))
// WHERE `name` LIKE '%Qiang' AND `name` LIKE '%Xue'
where(array('like', 'name', array('%Qiang', '%Xue')))
// WHERE `name` LIKE '%Qiang' OR `name` LIKE '%Xue'
where(array('or like', 'name', array('%Qiang', '%Xue')))
// WHERE `name` NOT LIKE '%Qiang%'
where(array('not like', 'name', '%Qiang%'))
// WHERE `name` NOT LIKE '%Qiang%' OR `name` NOT LIKE '%Xue%'
where(array('or not like', 'name', array('%Qiang%', '%Xue%')))
~~~

Beachten Sie bitte, dass bei sämtlichen `like`-Operatoren die
"Wildcard"-Zeichen (also `%` und `_`) manuell hinzugefügt werden müssen.
Stammen die Daten aus einer Benutzereingabe, sollte man die enthaltenen
Wildcard-Zeichen mit diesem Code "unschädlich" machen:

~~~
[php]
$keyword=$_GET['q'];
// escape % and _ characters
$keyword=strtr($keyword, array('%'=>'\%', '_'=>'\_'));
$command->where(array('like', 'title', '%'.$keyword.'%'));
~~~


### order()

~~~
[php]
function order($columns)
~~~

Die [order()|CDbCommand::order()]-Methode bestimmt den `ORDER BY`-Teil einer
Abfrage. Der Parameter `$columns` gibt die Spalten an, nach denen sortiert
werden soll und kann entweder ein String aus Spaltennamen und
Sortierrichtungen (`ASC` oder `DESC`) sein, die durch Komma getrennt werden oder eine Array aus
selbigen. Spaltennamen können Tabellenpräfixe enthalten und werden auch
automatisch in die richtigen Anführungszeichen gesetzt, es sei denn es tauchen Klammern auf
(was bedeutet, dass es sich um einen DB-Ausdruck handelt).

Einige Beispiele:

~~~
[php]
// ORDER BY `name`, `id` DESC
order('name, id desc')
// ORDER BY `tbl_profile`.`name`, `id` DESC
order(array('tbl_profile.name', 'id desc'))
~~~


### limit() und offset()

~~~
[php]
function limit($limit, $offset=null)
function offset($offset)
~~~

Die [limit()|CDbCommand::limit()]- und [offset()|CDbCommand::offset()]-Methoden
definieren den `LIMIT`- und `OFFSET`-Teil einer Abfrage. Beachten Sie, dass
diese Syntax evtl. nicht von allen DBMS unterstützt wird. In diesem Fall wird
der gesamte Ausdruck vom Query Builder so umgeschrieben, dass er diese
Funktion simuliert.

Hier wieder einige Beispiele:

~~~
[php]
// LIMIT 10
limit(10)
// LIMIT 10 OFFSET 20
limit(10, 20)
// OFFSET 20
offset(20)
~~~


### join() und Varianten

~~~
[php]
function join($table, $conditions, $params=array())
function leftJoin($table, $conditions, $params=array())
function rightJoin($table, $conditions, $params=array())
function crossJoin($table)
function naturalJoin($table)
~~~

Die [join()|CDbCommand::join()]-Methode und deren Varianten legen fest, wie andere
Tabellen mit `INNER JOIN`, `LEFT OUTER JOIN`, `RIGHT OUTER JOIN`, `CROSS JOIN`
oder `NATURAL JOIN` angebunden werden sollen. Der `$table`-Parameter gibt den
Namen der anzubindenden Tabelle an und kann ein Schemapräfix und/oder einen Alias
enthalten. Die Methode setzt den Namen automatisch in korrekte Anführungszeichen, es
sei denn, er enthält Klammern: Dann handelt es sich offenbar um einen DB-Ausdruck oder
eine Sub-Abfrage. Mit dem `$conditions`-Parameter kann man die JOIN-Bedingung
mit der gleichen Syntax wie bei [where|CDbCommand::where] angeben. `$params`
enthält die Parameter, die an die Abfrage gebunden werden sollen.

Beachten Sie, dass - im Gegensatz zu anderen Methoden des Query Builders -
jeder Aufruf einer join-Methode an die vorhergehende anstückelt.

Einige Beispiele:

~~~
[php]
// JOIN `tbl_profile` ON user_id=id
join('tbl_profile', 'user_id=id')
// LEFT JOIN `pub`.`tbl_profile` `p` ON p.user_id=id AND type=1
leftJoin('pub.tbl_profile p', 'p.user_id=id AND type=:type', array(':type'=>1))
~~~


### group()

~~~
[php]
function group($columns)
~~~

Mit der [group()|CDbCommand::group()]-Methode bestimmt man den `GROUP BY`-Teil
einer Abfrage. Der Parameter `$columns` gibt die Spalten an, nach denen
gruppiert werden soll und kann entweder ein String aus Spaltennamen sein, 
die durch Komma getrennt werden oder eine Array aus
selbigen. Spaltennamen können Tabellenpräfixe enthalten und werden auch
automatisch in passende Anführungszeichen gesetzt, es sei denn es tauchen Klammern auf
(was bedeutet, dass es sich um einen DB-Ausdruck handelt).

Auch hierzu einige Beispiele:

~~~
[php]
// GROUP BY `name`, `id`
group('name, id')
// GROUP BY `tbl_profile`.`name`, `id`
group(array('tbl_profile.name', 'id')
~~~


### having()

~~~
[php]
function having($conditions, $params=array())
~~~

Die [having()|CDbCommand::having()]-Methode gibt den `HAVING`-Teil einer Abfrage
an. Sie wird analog zur [where()|CDbCommand::where()]-Methode verwendet.

Einige Beispiele:

~~~
[php]
// HAVING id=1 or id=2
having('id=1 or id=2')
// HAVING id=1 OR id=2
having(array('or', 'id=1', 'id=2'))
~~~


### union()

~~~
[php]
function union($sql)
~~~

Die [union()|CDbCommand::union()]-Methode gibt den `UNION`-Teil einer Abfrage an.
Sie fügt `$sql` per `UNION` an das bestehende SQL an. Ruft man die Methode
mehrmals auf, werden auch mehrere SQLs angehängt.

Hier einige Beispiele:

~~~
[php]
// UNION (select * from tbl_profile)
union('select * from tbl_profile')
~~~


### Abfragen ausführen

Hat man eine Abfrage mit obigen Methoden erstellt, kann man sie mit den 
üblichen DAO-Methoden ausführen, wie sie in [Data Access Objects](/doc/guide/database.dao) 
beschrieben wurden. Mit [CDbCommand::queryRow()] erhält man z.B. eine einzelne Zeile
als Ergebnis, mit [CDbCommand::queryAll()] alle Zeilen auf einmal.
Example:

~~~
[php]
$users = Yii::app()->db->createCommand()
	->select('*')
	->from('tbl_user')
	->queryAll();
~~~


### SQL generieren

Man kann die Abfragen des Query Builders nicht nur ausführen, sondern sich
auch das erzeugte SQL liefern lassen. Dazu dient [CDbCommand::getText()].

~~~
[php]
$sql = Yii::app()->db->createCommand()
	->select('*')
	->from('tbl_user')
	->text;
~~~

Wenn Parameter an den Ausdruck gebunden werden, sind diese in
[CDbCommand::params] verfügbar.


### Alternative Syntax zum Erstellen von Abfragen

In bestimmten Fällen können verkettete Methodenaufrufe eher ungünstig sein.
Man kann daher stattdessen auch einfache Zuweisungen verwenden. Für jede
Methode gibt es dazu eine Eigenschaft mit dem gleichen Namen. Weist man einer
solchen Eigenschaft einen Wert zu, ist das das selbe, als hätte man die
entsprechende Methode aufgerufen. Die folgenden beiden Befehle haben daher
die selbe Auswirkung (vorausgesetzt `$comman` enthält ein [CDbCommand]-Objekt:

~~~
[php]
$command->select(array('id', 'username'));
$command->select = array('id', 'username');
~~~

Außerdem kann man auch an [CDbConnection::createCommand()] ein Array
übergeben, dass diese Eigenschaftswerte als Schlüssel-Wert-Paare für das zu
erzeugende [CDbCommand] enthält. Eine Abfrage kann man also auch so erstellen:

~~~
[php]
$row = Yii::app()->db->createCommand(array(
	'select' => array('id', 'username'),
	'from' => 'tbl_user',
	'where' => 'id=:id',
	'params' => array(':id'=>1),
))->queryRow();
~~~


### Erstellen mehrfacher Abfragen 

Eine [CDbCommand]-Instanz kann mehrfach wiederverwendet werden. Bevor man
jedoch eine neue Abfrage damit erstellen kann, muss die vorhergehende Abfrage
durch einen Aufruf von [CDbCommand::reset()] gelöscht werden:

~~~
[php]
$command = Yii::app()->db->createCommand();
$users = $command->select('*')->from('tbl_users')->queryAll();
$command->reset();  // clean up the previous query
$posts = $command->select('*')->from('tbl_posts')->queryAll();
~~~



Ausdrücke zum Verändern von Daten
----------------------------------

Darunter versteht man Ausdrücke zum Einfügen, Aktualisieren und Löschen von
Daten in ein DB-Tabelle. Der Query Builder bietet dazu die entsprechenden
Methoden `inser`, `update` und `delete` an. Anders als bei den oben
beschriebenen Methoden für SELECT-Abfragen, erzeugt jede dieser Methoden einen
vollständigen SQL-Ausdruck und führt ihn sofort aus.

* [insert()|CDbCommand::insert()]: Fügt eine Zeile in ein Tabelle ein
* [update()|CDbCommand::update()]: Aktualisiert eine Zeile in einer Tabelle
* [delete()|CDbCommand::delete()]: Löscht eine Zeile aus einer Tabelle


### insert()

~~~
[php]
function insert($table, $columns)
~~~

Die [insert()|CDbCommand::insert()]-Methode erzeugt einen `INSERT`-Ausdruck und
führt ihn aus. Der Parameter `$table` gibt den Namen der Tabelle an, in die
eine Zeile eingefügt werden soll. Die Werte werden in `$columns` als Array mit
den jeweiligen Spaltennamen als Schlüssel übergeben. Der Tabellenname wird
automatisch in korrekte Anführungszeichen gesetzt. Die Daten werden als Parameter
gebunden.

Ein Beispiel:

~~~
[php]
// Führt dieses SQL aus:
// INSERT INTO `tbl_user` (`name`, `email`) VALUES (:name, :email)
$command->insert('tbl_user', array(
	'name'=>'Tester',
	'email'=>'tester@example.com',
));
~~~


### update()

~~~
[php]
function update($table, $columns, $conditions='', $params=array())
~~~

Die [update()|CDbCommand::update()]-Methode erzeugt einen `UPDATE`-Ausdruck und
führt ihn aus. Die Tabelle wird in `$table` angegeben, die Parameter als
Name-Wert-Paare im Array `$columns`. `$conditions` und `$params` werden wie
bei [where|CDbCommand::where] verwendet und spezifizieren den  `WHERE`-Teil
im `UPDATE` Ausdruck. Der Tabellenname wird wieder automatisch in korrekte Anführungszeichen 
gesetzt und die Daten als Parameter an den Ausdruck gebunden.

Hier ein Beispiel:

~~~
[php]
// Führt dieses SQL aus:
// UPDATE `tbl_user` SET `name`=:name WHERE id=:id
$command->update('tbl_user', array(
	'name'=>'Tester',
), 'id=:id', array(':id'=>1));
~~~


### delete()

~~~
[php]
function delete($table, $conditions='', $params=array())
~~~

Die [delete()|CDbCommand::delete()]-Methode erzeugt einen `DELETE`-Ausdruck und
führt ihn aus. Die Tabelle wird in `$table` angegeben. `$conditions` und `$params` werden wie
bei [where()|CDbCommand::where()] verwendet und spezifizieren den  `WHERE`-Teil
im `DELETE` Ausdruck. Der Tabellenname wird wieder automatisch in korrekte Anführungszeichen 
gesetzt und die Daten als Parameter an den Ausdruck gebunden.


Hier ein Beispiel:

~~~
[php]
// Führt dieses SQL aus:
// DELETE FROM `tbl_user` WHERE id=:id
$command->delete('tbl_user', 'id=:id', array(':id'=>1));
~~~

Ausdrücke zum Verändern von Schemata
------------------------------------

Neben den Funktionen zum Abfragen und Ändern von Daten, unterstützt der Query
Builder auch Änderungen an der Datenbank selbst. Im einzelnen stehen zur
Verfügung:

* [createTable()|CDbCommand::createTable()]: Erstellt eine Tabelle
* [renameTable()|CDbCommand::renameTable()]: Benennt eine Tabelle um
* [dropTable()|CDbCommand::dropTable()]: Entfernt eine Tabelle
* [truncateTable()|CDbCommand::truncateTable()]: Leert eine Tabelle
* [addColumn()|CDbCommand::addColumn()]: Fügt eine Tabellenspalte hinzu
* [renameColumn()|CDbCommand::renameColumn()]: Benennt eine Tabellenspalte um
* [alterColumn()|CDbCommand::alterColumn()]: Verändert eine Tabellenspalte
* [dropColumn()|CDbCommand::dropColumn()]: Entfernt eine Tabellenspalte
* [createIndex()|CDbCommand::createIndex()]: Erstellt einen Index
* [dropIndex()|CDbCommand::dropIndex()]: Entfernt einen Index

> Info|Info: Obwohl Datenbanksysteme ganz unterschiedliche Ausdrücke für
> diesee Änderungen erwarten, zielt der Query Builder auf eine
> vereinheitlichte Schnittstelle ab. Dadurch wird die Migration auf ein
> anderes Datenbanksystem deutlich erleichtert.


###Abstrakte Datentypen

Der Query Builder führt eine Reihe abstrakter Datentypen ein. Im Gegensatz zu
den tatsächlichen Datentypen der jeweiligen Systeme sind diese Typen
unabhängig vom verwendeten DBMS. Der Query Builder wandelt sie je nach
verwendetem DBMS in die passenden Datentyp um.

Folgende abstrakten Datentypen werden unterstützt:

* `pk`: Universaltyp für Primärschlüssel, wird bei MySQL zu `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`
* `string`: Zeichenkette, wird bei MySQL zu `varchar(255)`
* `text`: Text (langer String), wird bei MySQL zu `text`
* `integer`: Ganze Zahl, wird bei MySQL zu `int(11)`
* `float`: Fließkommazahl, wird bei MySQL zu `float`
* `decimal`: Dezimalzahl, wird bei MySQL zu `decimal`
* `datetime`: Datum/Uhrzeit, wird bei MySQL zu `datetime`
* `timestamp`: Zeitstempel, wird bei MySQL zu `timestamp`
* `time`: Zeit, wird bei MySQL zu `time`
* `date`: Datum, wird bei MySQL zu `date`
* `binary`: Binärdaten, wird bei MySQL zu `blob`
* `boolean`: Bool'scher Wert, wird bei MySQL zu `tinyint(1)`
* `money`: Monetärer Wert/Währung, wird bei MySQL zu `decimal(19,4)`. Verfügbar seit Version 1.1.8


###createTable()

~~~
[php]
function createTable($table, $columns, $options=null)
~~~

Der Befehl [createTable()|CDbCommand::createTable()] führt den SQL-Ausdruck zum
Erstellen einer Tabelle aus. Der Parameter `$table` gibt den Namen der neuen
Tabelle an, `$columns` enthält die anzulegenden Spalten in Array-Form (z.B.
`'username'=>'string'`). Der Parameter `$options` kann ein weiteres SQL-Fragment
enthalten, das an den SQL-Ausdruck angehängt werden soll. Sowohl Tabellen- als
auch Spaltennamen werden in die korrekten Anführungszeichen gesetzt.

Bei der Angabe der Spalten kann man die oben erwähnten abstrakten Datentypen
verwenden. Der Query Builder wird diese dann je nach gerade verwendetem DBMS
in einen passenden Datentyp umwandeln. Für MySQL wird somit aus `string` ein
`varchar(255)`.

Stattdessen kann man aber auch andere Typen angeben. Sie werden eins zu eins
im erzeugten SQL verwendet. `point` ist zum Beispiel kein abstrakter Datentyp
und wird direkt ins SQL übernommen. Bei `string NOT NULL` wird lediglich der
abstrakte Typ `string` umgewandelt, man erhält also `VARCHAR(255) NOT NULL`.

Hier ein Beispiel, wie man eine Tabelle erstellt:

~~~
[php]
// CREATE TABLE `tbl_user` (
//     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
//     `username` varchar(255) NOT NULL,
//     `location` point
// ) ENGINE=InnoDB
createTable('tbl_user', array(
	'id' => 'pk',
	'username' => 'string NOT NULL',
	'location' => 'point',
), 'ENGINE=InnoDB')
~~~


###renameTable()

~~~
[php]
function renameTable($table, $newName)
~~~

Mit [renameTable()|CDbCommand::renameTable()] wird die Tabelle mit dem Namen
`$table` nach `$newName` umbenannt. Die Namen werden in die korrekten
Anführungszeichen eingefasst.

Ein Beispiel zum Umbenennen einer Tabelle:

~~~
[php]
// RENAME TABLE `tbl_users` TO `tbl_user`
renameTable('tbl_users', 'tbl_user')
~~~


###dropTable()

~~~
[php]
function dropTable($table)
~~~

Der Befehl [dropTable()|CDbCommand::dropTable()] führt den SQL-Ausdruck zum
Löschen einer Tabelle aus. `$table` enthält den Namen der Tabelle, der vom
Query Builder in die passenden Anführungszeichen gesetzt wird.

Ein Beispiel zum Löschen einer Tabelle:

~~~
[php]
// DROP TABLE `tbl_user`
dropTable('tbl_user')
~~~

###truncateTable()

~~~
[php]
function truncateTable($table)
~~~

Mit der [truncateTable()|CDbCommand::truncateTable()]-Methode wird das SQL zum
Leeren der Einträge in Tabelle `$table` ausgeführt. Der Tabellenname wird in
die passenden Anführungszeichen gesetzt.

Ein Beispiel zum Leeren einer Tabelle:

~~~
[php]
// TRUNCATE TABLE `tbl_user`
truncateTable('tbl_user')
~~~


###addColumn()

~~~
[php]
function addColumn($table, $column, $type)
~~~

Mit [addColumn()|CDbCommand::addColumn()] wird ein SQL-Ausdruck ausgeführt,
der der Tabelle `$table` eine neue Spalte namens `$column`
hinzufügt. Als Typ `$type` kann ein abstrakter Datentyp angegeben werden.
Tabellen- und Spaltennamen werden vom Query Builder in die richtigen
Anführungszeichen gesetzt.

Das folgende Beispiel zeigt, wie man eine neue Spalte hinzufügt:

~~~
[php]
// ALTER TABLE `tbl_user` ADD `email` varchar(255) NOT NULL
addColumn('tbl_user', 'email', 'string NOT NULL')
~~~


###dropColumn()

~~~
[php]
function dropColumn($table, $column)
~~~

[dropColumn()|CDbCommand::dropColumn()] führt das SQL zum Entfernen einer
Tabellenspalte aus. `$table` gibt den Namen der Tabelle an, aus der die Spalte
`$column` entfernt werden soll. Beide Namen werden in die korrekten
Anführungszeichen eingefasst.

Hier ein Beispiel zum Entfernen einer Spalte:

~~~
[php]
// ALTER TABLE `tbl_user` DROP COLUMN `location`
dropColumn('tbl_user', 'location')
~~~


###renameColumn()

~~~
[php]
function renameColumn($table, $name, $newName)
~~~

Mit [renameColumn()|CDbCommand::renameColumn()] wird die Spalte `$name` in der
Tabelle `$table` nach `$newName` umbenannt. Alle Namen werden in die richtigen
Anführungszeichen gesetzt.

Ein Beispiel hierzu:

~~~
[php]
// ALTER TABLE `tbl_users` CHANGE `name` `username` varchar(255) NOT NULL
renameColumn('tbl_user', 'name', 'username')
~~~


###alterColumn()

~~~
[php]
function alterColumn($table, $column, $type)
~~~

Mit der Methode [alterColumn()|CDbCommand::alterColumn()] wird eine Tabellenspalte
verändert. `$table` gibt den Namen der Tabelle, `$column` den Namen der darin
enthaltenen zu ändernden Spalte an. `$type` gibt den neuen Datentypen der
Spalte an und kann auch wieder einen abstrakten Datentypen enthalten.
Tabellen- und Spaltennamen werden in richtige Anführungszeichen gesezt.

Hier ein Beispiel zum Verändern einer Tabellenspalte:

~~~
[php]
// ALTER TABLE `tbl_user` CHANGE `username` `username` varchar(255) NOT NULL
alterColumn('tbl_user', 'username', 'string NOT NULL')
~~~




###addForeignKey()

~~~
[php]
function addForeignKey($name, $table, $columns,
	$refTable, $refColumns, $delete=null, $update=null)
~~~

Die Methode [addForeignKey()|CDbCommand::addForeignKey()] führt einen SQL-Ausdruck
aus, der eine Fremdschlüsselbeziehung zu einer Tabelle hinzufügt. Der
`$name`-Parameter legt den Namen der Beziehung fest. `$table` und
`$columns` definieren Tabellen- und Spaltenname(n) des Fremschlüssels. Mehrere
Spaltennamen müssen hierbei durch Kommas getrennt werden. `$refTable`
und `$refColumns` geben das Ziel der Fremdschlüsselbeziehung an. Mit den
Parametern `$delete` und `$update` können die `ON DELETE`- und `ON
UPDATE`-Optionen für den SQL-Ausdruck übergeben werden. Die meisten DBMS
unterstützen diese Optionen: `RESTRICT`, `CASCADE`, `NO ACTION`, `SET DEFAULT`, 
`SET NULL`. Tabellen-, Spalten- und Indexnamen werden wieder in korrekte
Anführungszeichen eingefasst.

Hier ein Beispiel, wie man eine solche Beziehung hinzufügt:

~~~
[php]
// ALTER TABLE `tbl_profile` ADD CONSTRAINT `fk_profile_user_id`
// FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`)
// ON DELETE CASCADE ON UPDATE CASCADE
addForeignKey('fk_profile_user_id', 'tbl_profile', 'user_id',
	'tbl_user', 'id', 'CASCADE', 'CASCADE')
~~~


###dropForeignKey()

~~~
[php]
function dropForeignKey($name, $table)
~~~

Mit [dropForeignKey()|CDbCommand::dropForeignKey()] wird eine
Fremdschlüsselbeziehung gelöscht. `$name` muss dabei den Namen der zu
löschenden Beziehung, `$table` die Tabelle für die er definiert wurde
enthalten. Beide Namen werden in die richtigen Anführungszeichen eingefasst. 

Hier ein Beispiel:

~~~
[php]
// ALTER TABLE `tbl_profile` DROP FOREIGN KEY `fk_profile_user_id`
dropForeignKey('fk_profile_user_id', 'tbl_profile')
~~~


###createIndex()

~~~
[php]
function createIndex($name, $table, $column, $unique=false)
~~~

[createIndex()|CDbCommand::createIndex()] erzeugt in der Tabelle `$table` einen
Index mit dem Namen `$name` über die Spalte `$column`. Der Parameter `$unique`
gibt an, ob ein eindeutiger Index erzeugt werden soll. Bei Indizes über
mehrere Spalten müssen die einzelnen Namen mit Kommas getrennt werden. Alle
Namen werden vom Query Builder korrekt in Anführungszeichen gesetzt.

Hier ein Beispiel:

~~~
[php]
// CREATE INDEX `idx_username` ON `tbl_user` (`username`)
createIndex('idx_username', 'tbl_user')
~~~


###dropIndex()

~~~
[php]
function dropIndex($name, $table)
~~~

[dropIndex()|CDbCommand::dropIndex()] löscht den Index mit dem Namen `$name` aus
der Tabelle `$table`. Beide Namen werden in die korrekten Anführungszeichen
gesetzt. 

Hier ein Beispiel:

~~~
[php]
// DROP INDEX `idx_username` ON `tbl_user`
dropIndex('idx_username', 'tbl_user')
~~~

<div class="revision">$Id: database.query-builder.txt 3408 2011-09-28 20:50:28Z alexander.makarow $</div>
