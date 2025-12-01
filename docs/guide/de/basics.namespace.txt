Pfadaliase und Namespace
=========================

Yii macht intensiven Gebrauch von Pfadaliasen. Ein Pfadalias steht für
ein Verzeichnis oder einen Dateipfad. Er wird - ähnlich zum weit verbreiteten
Namespaceformat - in Punkt-Syntax angegeben:

~~~
RootAlias.pfad.zu.ziel
~~~

wobei `RootAlias` für den Alias eines existierenden Verzeichnisses steht. 

Mit [YiiBase::getPathOfAlias()] erhält man den aufgelösten Pfad zu einem
Alias. `system.web.CController` würde zum Beispiel nach
`yii/framework/web/CController` übersetzt werden.

Über [YiiBase::setPathOfAlias()] kann man auch neue Rootaliase definieren.

Rootaliase
----------

Die folgenden praktischen Rootaliase werden von Yii schon vorbelegt:

 - `system`: Verweist auf das Frameworkverzeichnis von Yii
 - `zii`: Verweist auf das Verzeichnis der [Zii-Bibliothek](/doc/guide/extension.use#zii-extensions).
 - `application`: Verweist auf das [Anwendungsverzeichnis](/doc/guide/basics.application#application-base-directory) 
 - `webroot`: Steht für das Verzeichnis, das das [Startscript](/doc/guide/basics.entry) enthält.
 - `ext`: Verweist auf das Verzeichnis, das alle [Erweiterungen](/doc/guide/extension.overview) enthält.

Falls die Anwendung [Module](/doc/guide/basics.module) verwendet, wird für
jedes Modulstammverzeichnis ein zusätzlicher RootAlias entsprechend der ModulID
angelegt. Bei einem Modul mit der ID `users` ergibt so zum Beispiel den
RootAlias `users`.

Importieren von Klassen
-----------------------

Über Aliase kann man auch sehr bequem eine Klassendatei einbinden.
Möchte man zum Beispiel den Quelltext für [CController] importieren, 
genügt dieser Aufruf:

~~~
[php]
Yii::import('system.web.CController');
~~~

Die [import|YiiBase::import]-Methode ist wesentlich effizienter als `include` und 
`require`. Eine importierte Klassendatei wird erst eingebunden, wenn die
entsprechende Klasse zum ersten mal verwendet wird (das geschieht über den
PHP-Autoloader-Mechanismus). Auch wenn ein Namespace
mehrfach importiert wird, ist das wesentlich schneller als `include_once` oder
`require_once` zu verwenden.

> Tip|Tipp: Klassen aus dem Yii-Framework braucht man nicht zu importieren 
> oder mit include einzubinden. Alle Kernklassen von Yii sind bereits importiert.

###Verwenden einer Classmap

Seit Version 1.1.5 kann Yii Anwenderklassen auch
vorimportieren. Diesen Mechanismus verwendet Yii bereits intern 
für seine Kernklassen. Vorimportierte Klassen können überall in einer
Anwendung ohne expliziten Import oder Include verwendet werden. Das ist
insbesondere für Frameworks oder Libraries nützlich, die auf Yii aufbauen.

Um eine Reihe von Klassen vorzuimportieren muss folgender Code vor 
[CWebApplication::run()] ausgeführt werden:

~~~
[php]
Yii::$classMap=array(
	'KlassenName1' => 'pfad/zu/KlassenName1.php',
	'KlassenName2' => 'pfad/zu/KlassenName2.php',
	......
);
~~~

Importieren von Verzeichnissen
------------------------------

Um ein komplettes Verzeichnis zu importieren, kann man folgende Syntax
verwenden. Die darin enthaltenen Klassendateien werden dann bei Bedarf
automatisch eingebunden.

~~~
[php]
Yii::import('system.web.*');
~~~

Aliase werden neben [import|YiiBase::import] noch an vielen anderen Stellen
verwendet, wo es um Klassen geht. An [Yii::createComponent()] kann zum
Beispiel auch ein Alias übergeben werden, um eine Instanz der entsprechenden
Klasse zu erzeugen, auch wenn die Klassendatei vorher noch nicht eingebunden
war.

Namespace
---------

Ein Namespace bezieht sich auf eine logisch zusammengehörige Gruppe von
Klassen, um sie von anderen Klassen zu unterscheiden, selbst wenn diese den
selben Namen haben. Verwechseln Sie Namespaces bitte nicht mit Pfadaliasen.
Ein Pfadalias ist eher eine praktische Abkürzung für eine Datei oder ein
Verzeichnis. Er hat mit einem Namespace nichts zu tun.

> Tip|Tipp: Da PHP vor Version 5.3.0 von Haus aus noch keine Namespaces
> unterstützt, können Sie keine Instanzen von unterschiedlichen Klassen mit
> dem selben Namen erzeugen. Daher wird allen Klassen des
> Yii-Frameworks der Buchstabe 'C' (von engl.: Class, Klasse) vorangestellt,
> um Sie von eigenen Klassen zu unterscheiden. Wir empfehlen, das Präfix
> 'C' für das Yii-Framework zu reservieren und eigene Klassen mit
> anderen Präfixen zu versehen.


Klassen mit Namespace
---------------------

Eine Klasse mit Namespace bezeichnet eine Klasse, die innerhalb eines
nichtglobalen Namespaces definiert wurde. Die Klasse `application\components\GoogleMap` 
wurde z.B. im Namespace `application\components` definiert. Namespaces setzen
PHP 5.3.0 oder höher voraus.

Ab Version 1.1.5 können Klassen aus Namespaces ohne expliziten Import
verwendet werden. Man kann z.B. `application\components\GoogleMap` verwenden,
ohne die zugehörige Klassendatei vorher explizit einzubinden. Dies wird durch
durch einen verbesserten Autoloading-Mechanismus von Yii ermöglicht.

Um Klassen mit Namespace automatisch einzubinden, muss der Namespace ähnlich
wie ein Pfadalias benannt sein. Die Klasse `application\components\GoogleMap`
muss zum Beispiel an dem Ort gespeichert werden, der dem Pfadalias
`application.components.GoogleMap` entspricht.

<div class="revision">$Id: basics.namespace.txt 3086 2011-03-15 00:04:53Z qiang.xue $</div>
