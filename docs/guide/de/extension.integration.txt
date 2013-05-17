Verwenden von Fremdbibliotheken 
===============================

Bei der Planung von Yii wurde sorgfältig darauf geachtet, dass andere
Bibliotheken leicht mit eingebunden werden können, um den Funktionsumfang zu
erweitern. Oft passiert es nämlich, dass es zu Namenskonflikten oder Problemen 
mit Includes kommt, wenn man Fremdbibliotheken in einem Projekt verwenden
möchte. Yii's Klassen beginnnen daher alle mit dem Buchstaben `C` um solche
Namenskonflikte zu vermeiden. Außerdem verwendet Yii das [SPL
autoload](http://us3.php.net/manual/de/function.spl-autoload.php) Feature von
PHP. Damit klappt das Zusammenspiel mit Bibliotheken reibungslos, auch wenn diese 
selbe Methode zum automatischen Laden ihrer Klassen verwenden oder den PHP 
Include-Pfad verwenden.

Unten zeigen wir an einem Beispiel, wie man die 
[Zend_Search_Lucene](http://www.zendframework.com/manual/en/zend.search.lucene.html)-Komponente
aus dem [Zend-Framework](http://www.zendframework.com) in einer Yii-Anwendung
verwendet.

Zunächst werden die Dateien des Zend-Frameworks in ein Verzeichnis
unterhalb von `protected/vendors` entpackt, wobei `protected` für das
[Anwendungsverzeichnis](/doc/guide/basics.application#application-base-directory)
steht. Überprüfen Sie, dass die Datei `protected/vendors/Zend/Search/Lucene.php`
vorhanden ist.

Fügen Sie dann die folgenden beiden Zeilen am Anfang einer Controllerklasse
ein:

~~~
[php]
Yii::import('application.vendors.*');
require_once('Zend/Search/Lucene.php');
~~~

Der obige Code bindet die Klassendatei `Lucene.php` ein. Da wir einen
relativen Pfad verwenden, müssen wir den Include-Pfad von PHP verändern, damit
die Datei gefunden werden kann. Dies erfolgt über den Aufruf von `Yii::import`
vor `require_once`.

Nachdem obige Änderungen vorgenommen wurden, kann man die Klasse `Lucene` in
einer Controlleraction wie folgt verwenden:

~~~
[php]
$lucene=new Zend_Search_Lucene($pathOfIndex);
$hits=$lucene->find(strtolower($keyword));
~~~

Verwenden von Fremdbibliotheken mit Namespaces
----------------------------------------------

Damit man Bibliotheken mit Namespaces gemäß
[PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
(etwa Zend Framework 2 oder Symfony2) verwenden kann, muss man einen Pfad-Alias
für deren Stammverzeichnis definieren.

Wir zeigen das hier am Beispiel von [Imagine](https://github.com/avalanche123/Imagine).
Wenn das `Imagine`-Verzeichnis unter `protected/vendors` liegt, können wir die
Bibliothek wie folgt verwenden:

~~~
[php]
Yii::setPathOfAlias('Imagine',Yii::getPathOfAlias('application.vendors.Imagine'));

// Hier dann Code wie aus dem Imagine-Guide:
// $imagine = new Imagine\Gd\Imagine();
// etc.
~~~

Der definierte Alias sollte also dem ersten Teil des Namespaces der Bibliothek
entsprechen.

Verwenden von Yii in anderen Systemen
-------------------------------------

Man kann Yii kann auch als eigenständige Bibliothek verwenden, die einen bei
der Entwicklung und Verbesserung von Drittsystemen wie WordPress oder Joomla
unterstützt. Dazu wird folgender Ladecode in das Drittsystem eingebunden:

~~~
[php]
require_once('path/to/yii.php');
Yii::createWebApplication('path/to/config.php');
~~~

Das ist fast der selbe Code, wie ihn auch eine typische Yii-Anwendung
verwendet. Allerdings mit dem Unterschied, dass hier nicht die Methode
`run()` auf dem erstellten Anwendungsobjekt aufgerufen wird.

Ab nun kann man die meisten Yii-Features auch im Fremdsystem verwenden. Über
`Yii::app()` lässt sich zum Beispiel auf die Anwendungsinstanz zugreifen. Auch
Datenbankfeatures wie DAO und ActiveRecord stehen zur Verfügung. Ebenso Models
und deren Validierungsfeature und vieles mehr.

<div class="revision">$Id: extension.integration.txt 3431 2011-11-03 00:53:44Z alexander.makarow@gmail.com $</div>
