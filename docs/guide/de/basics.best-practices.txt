Bewährte MVC-Verfahren
======================

Obwohl nahezu jeder Webentwickler das Model-View-Controller-Konzept kennt,
entzieht sich der richtige Einsatz von MVC in realen Anwendungen oft deren
Kenntnis. Die zentrale Idee hinter MVC besteht in der **Wiederverwendbarkeit 
von Code und einer Trennung von Zuständigkeiten**. In diesem Abschnitt
geben wir einige allgemeine Richtlinien, wie man dem MVC-Konzept beim Entwickeln 
mit Yii dem besser gerecht wird.

Zum leichteren Verständnis nehmen wir eine Webanwendung an, die aus mehreren
Teilapplikationen besteht, wie zum Beispiel

* Frontend: eine öffentliche Website für normale Endbenutzer;
* Backend: eine Website die administrative Funktionen zum Verwalten der
Anwendung bereitstellt. In der Regel ist der Zugriff auf administrative
Mitarbeiter beschränkt;
* Konsole: eine Anwendung aus Konsolenbefehlen zur Wartung der Webanwendung
besteht, welche man entweder an einer Eingabeaufforderung aufrufen kann oder 
die zeitgesteuerte ablaufen.
* Web-API: stellt Schnittstellen zur Integration der Webanwendung für Dritte zur Verfügung.

Die Teilapplikationen können etwa als [Module](/doc/guide/basics.module) oder
als Yii-Anwendung, die Code mit anderen Teilapplikationen teilt, implementiert
werden.


Model
-----

[Models](/doc/guide/basics.model) stellen die zugrundeliegende Datenstruktur
einer Webanwendung dar. Ein `LoginForm`-Model könnte sowohl im Frontend als
auch im Backend einer Anwendung eingesetzt werden; ein `News`-Model könnte von
Konsolenbefehlen, Web-APIs, Front- und Backend einer Anwedung benutzt werden.
Models ...

* sollten Eigenschaften für die entsprechenden Daten beinhalten,

* sollten Geschäftslogik (z.B. Validierungsregeln) enthalten, um sicherzustellen, dass
die dargestellten Daten den Designanforderungen genügen,

* und können Code zum Verändern der Daten beinhalten. So könnte `SearchForm`-Model
nicht nur die eingegebenen Suchdaten darstellen, sonder auch deine Methode
`search` zur eigentlichen Suche implementieren.

Manchmal führt diese letzte Regel dazu, dass ein Model sehr "fett" wird, also
zu viel Code in einer einzelnen Klasse enthält. Das kann ein Model auch schwer
wartbar machen, falls der Code unterschiedlichen Zwecken dient. Zum Beispiel
könnte es in einem `News`-Model eine Methode `getLatestNews` enthalten, die
nur im Frontend verwendet wird. Sie könnte außerdem eine Methode `getDeletedNews` 
implementieren, die nur im Backend benötigt wird. Das mag für kleine bis
mittelgroße Anwendungen noch praktikabel sein. Bei großen Applikationen kann
stattdessen mit folgender Strategie die Models besser wartbar halten:

* Definieren Sie eine `NewsBase`-Klasse, die nur den von allen Teilapplikationen benötigten Code enthält

* Für jede Teilapplikation definieren Sie dann ein eigenes `News`-Model, dass
diese Basisklasse erweitert. In diesem Model kann dann Code untergebracht
werden, der nur für die Teilapplikation (Frontend, Backend, ...) benötigt
wird.

In unserem obigen Beispiel würde diese Strategie zu einem `News`-Model für das
Frontend mit der Methode `getLatestNews`, sowie einem für das Backend mit
`getDeletedNews` führen.

Allgemein sollten Models keine Logik enthalten, die direkt mit dem Endbenutzer
interagiert. Genauer gesagt sollten Models ...

* nicht auf `$_GET`, `$_POST` oder ähnliche Variablen zugreifen, die direkt
mit dem Request eines Endbenutzers verbunden sind. Bedenken Sie, dass ein
Model auch von einer ganz anderen Teilapplikation verwendet werden könnte (z.B.
Unittests, Web-API), die keinen Zugriff auf diese Requestdaten hat. Solche
Variablen sollten nur vom Controller verarbeitet werden.

* kein  HTML oder anderen darstellungsbezogenen Code enthalten. Da dieser Code
sich je nach Anwendungsfall unterscheiden kann (eine News-Detailansicht kann
z.B. im Verwaltungsbereich ganz anders aussehen als im Frontend), ist er in
Views viel besser aufgehoben.

View
----

[Views](/doc/guide/basics.view) sind dafür verantwortlich, Models nach den
Wünschen des Endbenutzers darzustellen. Allgemein gilt, dass Views ...

* hauptsächlich darstellungsbezogenen Code enthalten sollten, also HTML und
einfaches PHP um Daten zu formatieren, anzuzeigen oder in Schleifen zu
durchlaufen.

* keinen Code mit Datenbank-Abfragen enthalten sollten. Dieser Code ist in
Models besser aufgehoben.

* den direkten Zugriff auf `$_GET`, `$_POST` und ähnliche Variablen aus dem
Endbenutzerrequest vermeiden sollten. Ein View sollte sich auf die Darstellung
und das Layout der Daten konzentrieren, die er von Controller und Model
erhalten hat, aber nicht versuchen, direkt auf Request-Daten oder die
Datenbank zuzugreifen.

* direkt auf Eigenschaften und Methoden von Controller und Model zugreifen
können. Dies sollte allerdings nur zum Zwecke der Darstellung geschehen.


Views können auf verschiedene Weise wiederverwendet werden:

* Layout: Gemeinsame Darstellungsbereiche (z.B. Seitenheader und -footer, also 
Kopf- und Fußbereich) können im Layout-View untergebracht werden.

* Partielle Views: Verwenden Sie partielle Views (Views, die nicht mit einem
Layout dekoriert werden) um Darstellungsfragmente wiederzuverwenden. Der
partielle View `_form.php` wird zum Beispiel sowohl auf der Seite für neue
Einträge als auch derjenigen zum Ändern verwendet.

* Widgets: Falls ein partieller View viel an Logik enthält, kann man ihn auch
zu einem Widget verwandeln, wo diese Logik in dessen Klassendatei ausgelagert
wird. Widgets, die viel HTML produzieren verwenden außerdem am besten ihre
eigene Viewdatei für diesen code.

* Hilfsklassen: In Views benötigt man für so kleine Aufgaben wie das
Formatieren von Daten oder das Rendern von HTML-Tags oft häufig wiederkehrende
Codeschnipsel. Statt diesen Code überall zu wiederholen, lagert man ihn besser
in eine Hilfsklasse aus und verwendet diese stattdessen im View. Yii enthält
bereits ein Beispiel für diesen Ansatz: Die leistungsfähige
[CHtml]-Hilfsklasse kann häufig verwendeten HTML-Code erzeugen. Hilfsklassen
können in einem [importierten Verzeichnis](/doc/guide/basics.namespace) abgelegt werden, 
so dass man sie ohne explizites Einbinden verwenden kann.


Controller
----------

[Controllers](/doc/guide/basics.controller) bilden den Leim, der Models, Views
und andere Komponenten zu einer lauffähigen Anwendung verbindet. Controller
sind für die Verarbeitung einer Benutzeranfrage verantwortlich. Daher gilt,
Controller ...
	
* können auf `$_GET`, `$_POST` und andere PHP-Variablen des Benutzerrequests zugreifen.

* können Instanzen eines Models erstellen und dessen Lebenszyklus verwalten.
In einer typischen Update-Action zum Beispiel, könnte der Controller erst die
Model-Instanz erzeugen, sie dann mit den Benutzereingaben in `$_POST` befüllen
und - nachdem das Model erfolgreich gespeichert wurde - auf die Detailseite
des Models umleiten. Beachten Sie aber, dass der eigentliche Code zum
Speichern im Model und nicht im Controller untergebracht ist.

* sollte keine SQL-Anweiseungen enthalten. Sie werden im Model gehalten.

* sollte kein HTML oder anderes Markup zur Darstellung enthalten. Dazu eignetn
sich Views besser.


In einer gut geplanten MVC-Anwendung sind Controller oft sehr "dünn",
enthalten also vielleicht nur ein paar dutzend Zeilen an Code. Models hingegen
sind sehr "fett" und enthalten den Großteil des Codes zum Manipulieren der
Daten. Das liegt daran, dass die Datenstrukturen und Geschäftslogiken im Model
normalerweise stark auf eine bestimmte Anwendung zugeschnitten sind,
wohingegen Controllerlogik oft ähnlichen Mustern folgt. Letzerer kann also gut
vereinfacht und in zugrundeliegende Basis- oder Frameworkklassen ausgelagert
werden.

<div class="revision">$Id: basics.best-practices.txt 2795 2010-12-31 00:22:33Z alexander.makarow $</div>
