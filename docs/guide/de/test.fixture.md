Definieren von Fixtures
=======================

Automatisierte Tests müssen sehr oft ausgeführt werden. Damit der Testprozess
wiederholbar bleibt, führen wir ihn in einem wohldefinierten Zustand genannt
*Fixture* (sinngem: festes Inventar) aus. Testen wir zum Beispiel das Anlegen
eines Beitrags in einer Bloganwendung, sollten alle betroffenen Tabellen (z.B.
`Post` und `Comment`) auf einen festen Zustand zurückgesetzt werden. In der
[Dokumentation von PHPUnit](http://www.phpunit.de/manual/current/en/fixtures.html) ist das
allgemeine Einrichten von Fixtures genau beschrieben. In diesem Abschnitt
beschreiben wir, wie wir die eben beschriebenen Datenbankfixtures einrichten.

Das Anlegen von Datenbankfixtures ist wohl eine der zeitintensivesten
Tätigkeiten beim Testen datenbankgestützter Webanwendungen. Zur Erleichterung 
führt Yii daher die [CDbFixtureManager]-Anwendungskomponente ein. Sie kümmert
sich hauptsächlich um diese Punkte:

 * Vor dem Ausführen aller Tests, werden alle Tabellen in einen fest definierten Zustand zurückversetzt.
 * Vor dem Ausführen einer einzelnen Testmethode, werden alle angegebenen Tabellen in einen fest definierten Zustand zurückversetzt
 * Während der Ausführung einer Testmethode bietet sie Zugriff auf einzelne Fixturedaten

Um [CDbFixtureManager] verwenden zu können, aktivieren wir ihn in der
[Anwendungskonfiguration](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'components'=>array(
		'fixture'=>array(
			'class'=>'system.test.CDbFixtureManager',
		),
	),
);
~~~

Danach stellen wir die Fixturedaten im Verzeichnis `protected/tests/fixtures`
bereit. Über [CDbFixtureManager::basePath] können wir auch ein anderes
Verzeichnis konfigurieren. Die Fixturedaten sind in einer Sammlung von PHP-Dateien
als Fixturedateien organisiert. Jede Fixturedatei gibt ein Array mit den
anfänglichen Datenzeilen einer bestimmten Tabelle zurück. Der Dateiname
entspricht dem Tabellennamen. Hier ein Beispiel der Fixturedaten für die
Tabelle `Post` die in der Datei `Post.php` gespeichert werden:

~~~
[php]
<?php
return array(
	'sample1'=>array(
		'title'=>'test beitrag 1',
		'content'=>'test beitrag inhalt 1',
		'createTime'=>1230952187,
		'authorId'=>1,
	),
	'sample2'=>array(
		'title'=>'test beitrag 2',
		'content'=>'test beitrag inhalt 2',
		'createTime'=>1230952287,
		'authorId'=>1,
	),
);
~~~

Wie wir sehen, werden zwei Datenzeilen zurückgeliefert. Jede Zeile wird durch
ein assoziatives Array dargestellt, wobei die Schlüssel den Spaltennamen und
die Werte den Spaltenwerten entsprechen. Außerdem ist jede Zeile mit einem
String (z.B. `sample1`) genannt *Zeilenalias* indiziert. Wenn wir später
Testscripts schreiben, können wir bequem über den Alias auf eine Zeile
zugreifen. Wir werden dies im nächsten Abschnitt näher erläutern.

Vielleicht haben Sie bemerkt, dass wir keine `id`-Spaltenwerte in obigen
Fixtures angeben. Das liegt daran, dass wir die Spalte `id` als
autoinkrementellen Primärschlüssel definiert haben, dessen Wert automatisch
vergeben wird, wenn eine neue Zeile eingefügt wird.

Beim ersten Zugriff auf [CDbFixtureManager] geht dieser alle Fixturedateien
durch und setzt die entsprechenden Tabellen zurück. Dazu leert er die Tabelle,
setzt den Autoinkrement-Wert für den Primärschlüssel zurück und fügt dann die
Zeilen aus der Fixturedatei in die Tabelle ein.

Manchmal wollen wir vor dem Ausführen aller Tests evtl. nicht alle Tabellen mit einer Fixturedatei
zurücksetzen, da dies sehr lang dauern könnte. In diesem Fall können wir 
das Einfügen der Fixtures über ein eigenes PHP-Script anpassen. Dieses Script
sollte in einer Datei namens `init.php` im Fixtureverzeichnis abgelegt werden.
Falls [CDbFixtureManager] eine solche Datei findet, wird dieses Script
ausgeführt, statt alle Tabellen zurückzusetzen.

Genauso kann es sein, dass wir das standardmäßige Vorgehen beim Zurücksetzen
einer bestimmten Tabelle ändern möchten. In diesem Fall können wir ein
eigenes Initialisierungsscript für die entsprechende Fixturedatei schreiben.
Das Scriptname muss dem Namen der Tabelle entsprechen aber mit `.init.php`
enden. Zum Initalisieren der Tabelle `Post` müsste das Script also
`Post.init.php` heissen. Wenn [CDbFixtureManager] ein solches Script
vorfindet, wird dieses statt der Standardmethode zum Zurücksetzen der Tabelle
ausgeführt.

> Tip|Tipp: Zu viele Fixturedateien können die Testlaufzeit wesentlich verlängern. 
Daher sollten Sie nur Fixtures für die Tabellen verwenden, deren Inhalt sich
während des Tests verändern kann. Tabellen die zum Nachschlagen dienen, ändern
sich nicht und benötigen daher keine Fixturedateien.

In den nächsten beiden Abschnitten beschreiben wir, wie die vom
[CDbFixtureManager] verwalteten Fixtures in Unit- und Funktionstests verwendet
werden.

<div class="revision">$Id: test.fixture.txt 3039 2011-03-09 19:48:15Z qiang.xue $</div>
