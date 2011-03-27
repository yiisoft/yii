Übersicht
=========

Testen ist ein unverzichtbarer Bestandteil der Softwareentwicklung. Ob wir uns
dessen bewusst sind oder nicht: wir führen während der Entwicklung andauernd Tests durch. 
Wenn wir zum Beispiel eine PHP-Klasse schreiben und dort `echo`- oder `die`-Anweisungen
verwenden, um zu prüfen, ob wir eine Methode richtig umgesetzt haben. Oder
wenn wir eine Webseite mit einem komplexen Formular erstellen und dort
Testdaten eingeben, um zu sehen, ob sich die Seite wie gewünscht verhält.
Fortgeschrittene Entwickler schreiben hierzu eigenen Code, der diese
Testprozesse automatisch durchführt. Jedesmal, wenn wir also etwas testen
wollen, müssen wir nur diesen Code ausführen und den Test vom Computer
durchführen lassen. Man spricht dann von *automatisierten Tests*, welche wir
uns in diesem Kapitel näher ansehen wollen.

Yii unterstützt *Unittests* und *Funktionstests*.

Ein Unittest prüft, ob eine einzelne Einheit (engl.: Unit) eines Codes wie
erwartet arbeitet. Beim objektorientierten Programmieren entspricht die
einfachste Codeeinheit einer Klasse. Ein Unittest muss daher im Wesentlichen
prüfen, dass jede Interfacemethode der Klasse ordentlich arbeitet. Das
bedeutet, der Test überprüft ob die Klasse für verschiedene Eingabeparameter 
die erwarteten Ergebnisse zurückliefert. Unittests werden in der Regel vom
gleichen Programmierer erstellt, der auch die zu testende Klasse geschrieben
hat.

Ein Funktionstest prüft, ob ein Feature (z.B. das Verwalten von Beiträgen in
einem Blogsystem) wie erwartet funktioniert. Ein Funktionstest sitzt - im
Vergleich zu einem Unittest - auf einer höheren Ebene, da ein zu testendes
Feature oft mehrere Klassen verwendet. Funktionstest werden meist von jenen
geschrieben, die die Anforderungen an das System sehr genau kennen (also
entweder dem Entwickler selbst oder Qualitätssicherungsingenieuren).


Testgetriebene Entwicklung
--------------------------

Unten zeigen wir den Entwicklungszyklus bei der sogenannten [testgetriebenen
Entwicklung](http://de.wikipedia.org/wiki/Testgetriebene_Entwicklung) (engl.:
test driven development, TDD).

 1. Erstellen Sie einen Test für ein zu implementierendes Feature. Der Test wird
zu Beginn erwartungsgemäß fehlschlagen, da das Feature erst noch umgesetzt
werden muss.
 2. Lassen Sie alle Tests laufen und stellen Sie sicher, dass der neue Test
fehlschlägt.
 3. Schreiben Sie den Code, der zum Bestehen des neuen Tests nötig ist.
 4. Lassen Sie alle Tests laufen und stellen Sie sicher, dass alle erfolgreich
waren.
 5. Überarbeiten Sie den neuen Code und stellen Sie sicher, dass die Tests
immer noch erfolgreich verlaufen.

Wiederholen Sie die Schritte 1 bis 5 um weitere Funktionalitäten zu
implementieren.


Einrichten der Testumgebung
---------------------------

Die von Yii unterstützten Tests benötigen [PHPUnit](http://www.phpunit.de/)
3.5+ und [Selenium Remote
Control](http://seleniumhq.org/projects/remote-control/) 1.0+. Bitte ziehen
Sie bei Fragen zur Installation deren Dokumation zu Rate.

Wenn wir eine neue Yii-Anwendung mit `yiic webapp` anlegen, werden diese
Dateien und Verzeichnisse für automatisierte Tests erzeugt:

~~~
testdrive/
   protected/                enthält geschützte Anwendungsdateien
      tests/                 enthält Tests für die Anwendung
         fixtures/           enthält Datenbank-Fixtures (s.u.)
         functional/         enthält Funktionstests
         unit/               enthält Unittests
         report/             enthält Reports zur Codeabdeckung
         bootstrap.php       das ganz zu Beginn ausgeführte Script
         phpunit.xml         die Konfigurationsdatei für PHPUnit
         WebTestCase.php     die Basisklasse für webbasierte Funktionstests
~~~

Wie wir sehen, wird unser Testcode hauptsächlich in den drei
Verzeichnissen `fixtures`, `functional` und `unit` abgelegt. Im Ordner
`report` werden die erstellten Reports zur Codeabdeckung (engl.: code
coverage) gespeichert.

Um die Tests zu starten (egal ob Unittest oder Funktionstest), können wir 
an der Textkonsole folgende Befehle ausführen:

~~~
% cd testdrive/protected/tests
% phpunit functional/PostTest.php    // führt einen bestimmten Test aus
% phpunit --verbose functional       // führt alle Tests unter 'functional' aus
% phpunit --coverage-html ./report unit
~~~

Der letzte Befehl führt alle Tests im Verzeichnis `unit` aus und erzeugt einen
Report zur Codeabdeckung im `report`-Verzeichnis. Beachten Sie, dass die 
[xdebug-Erweiterung](http://www.xdebug.org/) installiert und aktiviert sein
muss, damit Reports zur Codeabdeckung erstellt werden können.


Test Bootstrap-Script
---------------------

Werfen wir einen Blick auf die Datei `bootstrap.php`. Diese Datei ist ebenso
besonders wie das [Startscript](/doc/guide/basics.entry) unserer Anwendung. 
Sie stellt den Startpunkt bei der Ausführung aller Tests dar.

~~~
[php]
$yiit='path/to/yii/framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';
require_once($yiit);
require_once(dirname(__FILE__).'/WebTestCase.php');
Yii::createWebApplication($config);
~~~

Hier binden wir zunächst die Datei `yiit.php` aus dem Yii-Framework ein, die
einige globale Konstanten initialisiert und benötigte Basisklassen für Tests
enthält. Danach erzeugen wir eine Instanz der Webanwendung mit der
Konfiguration in `test.php`. Wenn wir uns `test.php` ansehen, sehen wir, dass
es die Konfiguration aus der `main.php`-Datei erbt und eine
Anwendungskomponente `fixture` mit der Klasse [CDbFixtureManager] hinzufügt.
Fixtures werden wir im nächsten Abschnitt behandeln.

~~~
[php]
return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'components'=>array(
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			/* für eine Test-DB-Verbindung Kommentar entfernen:
			'db'=>array(
				'connectionString'=>'DSN zur Testdatenbank',
			),
			*/
		),
	)
);
~~~

Wenn wir Tests im Zusammenhang mit Datenbanken ausführen, sollten wir eine
Testdatenbank bereitstellen, damit beim Ausführen der Tests keine Konflikte 
mit dem Entwicklungs- oder Produktivsystem auftreten. Dazu müssen wir nur die
`db`-Konfiguration im obigen Beispiel aktivieren und die DSN zur Testdatenbank
unter `connectionString` eintragen.

Mit diesem Bootstrap-Script (sinngem.: Ladescript) arbeiten die Tests mit
einer Anwendungsinstanz, die fast genauso aussieht, wie jene die unsere
Web-Requests beantwortet. Der wesentliche Unterschied liegt darin, dass ein
Fixturemanager enthalten ist und sie die Testdatenbank verwendet.

<div class="revision">$Id: test.overview.txt 2997 2011-02-23 13:51:40Z alexander.makarow $</div>
