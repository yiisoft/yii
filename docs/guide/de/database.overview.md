Arbeiten mit Datenbanken
========================

Yii bietet leistungsstarke Features für die Arbeit mit Datenbanken. 

Mit den Data Access Objects (Datenzugriffsobjekte) oder kurz DAO kann man über eine
vereinheitlichte Schnittstelle auf unterschiedliche Datenbanksysteme (DBMS)
zugreifen. Sie basieren auf der PDO-Erweiterung von PHP. Verwendet man DAO in
einer Anwendung, kann diese sehr leicht auf ein anderes DBMS umgestellt werden,
ohne den entsprechenden Code ändern zu müssen.

Der Querybuilder von Yii ermöglicht zudem, SQL-Abfragen auf objektorientierte
Weise zu erstellen, was nützlich ist, um das Risiko von
SQL-Injection-Angriffen zu minimieren.

Und mit Yii’s ActiveRecord-Implementierung (kurz AR) kann man die Datenbankprogrammierung
noch weiter vereinfachen. Sie nutzt das weit verbreitete
[ORM](http://de.wikipedia.org/wiki/ORM)-Konzept (engl.: Object-Relational Mapping), 
die sogenannte objektrelationale Abbildung. Eine Tabelle
einer Datenbank wird dabei durch eine Klasse repräsentiert. Eine Zeile in
dieser Tabelle entspricht einem Objekt dieser Klasse. Man vermeidet damit,
immer wieder die selben SQL-Ausdrücke für die oft gleichen CRUD-Operationen 
(für "*C*reate, *R*ead, *U*pdate, *D*elete", "Erstellen, Lesen, Aktualisieren, Löschen")
zu erstellen.

Obwohl man mit diesen Features praktisch alle datenbankbezogenen Aufgaben erledigen kann,
können Sie auch Ihre eigene Datenbankbibliothek mit Yii verwenden.
Bei der Konzeption von Yii wurde allgemein sehr viel Sorgfalt darauf
verwendet, Drittbibliotheken problemlos einbinden zu können.

<div class="revision">$Id: database.overview.txt 2666 2010-11-17 19:56:48Z qiang.xue $</div>
