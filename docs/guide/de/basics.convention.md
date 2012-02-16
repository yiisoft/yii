Konventionen
============

Yii folgt dem Prinzip "Konvention statt Konfiguration". 
Das bedeutet, wenn Sie den Konventionen folgen, können Sie sehr 
anspruchsvolle Anwendungen erstellen, ohne sich mit umfangreichen oder
komplexen Konfigurationen herumschlagen zu müssen. Trotzdem kann Yii, falls nötig,
in praktisch allen Belangen angepasst werden.

Wir wollen hier die für Yii empfohlenen Konventionen kurz erläutern. 
Nehmen wir dabei an, dass im Verzeichnis `WebVerzeichnis` 
eine Yii-Anwendung installiert wurde.

URL
---

Yii verarbeitet im Normalfall URLs gemäß diesem Format:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

Die GET-Variable `r` bezieht sich auf die
[Route](/doc/guide/basics.controller#route), die von Yii in Controller und
Action aufgelöst wird. Wird `ActionID` weggelassen, verwendet der
Controller die Standardaction (festgelegt über [CController::defaultAction]). 
Falls auch die ControllerID weggelassen wird (oder die Variable `r` ganz
fehlt), verwendet die Applikation den Standardcontroller (definiert über
[CWebApplication::defaultController]).

Mit dem [CUrlManager] können auch suchmaschinenfreundliche URLs erzeugt und
verarbeitet werden, wie z.B.
`http://hostname/ControllerID/ActionID.html`. Im Kapitel [URL Management](/doc/guide/topics.url) 
werden wir dieses Feature detailliert behandeln.

Code
----

Es wird empfohlen, Variablen, Funktionen und Klassen in sog.
CamelCase-Schreibweise zu benennen. D.h. alle Worte eines Namens 
sollten ohne Leerzeichen aneinandergereiht werden und jeweils mit einem
Großbuchstaben beginnen.
Bei Variablen- und Funktionsnamen sollte das erste Wort kleingeschrieben
werden, um sie von Klassennamen zu unterscheiden (z.B. `$basePath`,
`runController()`, `LinkPager`). Wir empfehlen außerdem, privaten 
Klassenvariablen einen Unterstrich voranzustellen (z.B. `$_actionList`).

Da vor PHP 5.3.0 noch keine Namespaces unterstützt werden, empfehlen wir,
Klassennamen eindeutig zu kennzeichnen, um Konflikte mit anderen Klassen
zu vermeiden. Allen Yii-Klassen ist daher ein "C" vorangestellt.

Für Controllerklassen gilt, dass deren Name mit 'Controller' enden muss.
Eine Contoller-ID entspricht dem Klassennamen ohne 'Controller', wobei das
erste Wort kleingeschrieben wird. `PageController` hätte somit die ID `page`.
Diese Regel führt zu sichereren Anwendungen. Außerdem werden Controller-URLs
dadurch leichter lesbar  (z.B. `/index.php?r=page/index` statt `/index.php?r=PageController/index`).

Konfigurationen
---------------

Eine Konfiguration besteht aus einem Array von Schlüssel/Wert-Paaren. Jeder
Schlüssel entspricht dabei dem Namen einer Objekteigenschaft die man mit dem
entsprechenden Wert belegen möchte. Mit dem Array
`array('name'=>'Meine Anwendung', 'basePath'=>'./protected')` 
setzt man z.B. die Eigenschaften `name` und `basePath` auf die entprechenden
Werte.

Jede beschreibbare Eigenschaft eines Objekts kann konfiguriert werden. Falls
sie nicht konfiguriert wurde, behält sie ihren Vorgabewert. Es lohnt sich,
hierbei einen Blick in die Dokumentation der entsprechenden Klasse zu werfen.

Dateien
-------

Die Konventionen für Dateinamen hängt von deren Typ ab.

Klassendateien sollten genauso heißen wie die darin enthaltenen öffentlichen Klassen.
Die Klasse [CController] zum Beispiel befindet sich in der Datei
`CController.php`. Eine öffentliche Klasse kann von jeder anderen Klasse
verwendet werden. Jede Klassendatei sollte höchstens eine
öffentliche Klasse enthalten. Private Klassen (welche nur von einer
einzigen öffentlichen Klasse verwendet werden) können sich gemeinsam mit
der öffentlichen Klasse in einer Datei befinden.

Viewdateien heißen wie der enthaltene View. Der View `index`
zum Beispiel befindet sich in der Datei `index.php`. Eine Viewdatei ist ein
PHP-Script das hauptsächlich HTML und PHP für Anzeigezwecke enthält.

Konfigurationsdateien können beliebig benannt werden. Eine Konfigurationsdatei
ist ein einfaches PHP-Script, das eine Konfiguration in Form eines assoziativen 
Arrays zurückzuliefert.

Verzeichnisse
-------------

Yii geht von einigen vorhandenen Verzeichnissen aus. Bei Bedarf kann diese
Struktur jedoch angepasst werden.

   - `WebVerzeichnis/protected`: Das
[Anwendungsverzeichnis](/doc/guide/basics.application#application-base-directory), das alle
sicherheitsrelevanten PHP-Scripts und Dateien enthält. Der Standardalias
`application` verweist auf dieses Verzeichnis. Sämtliche Inhalte sollten nicht vom Web aus 
zugänglich sein. Der Pfad kann über [CWebApplication::basePath] angepasst werden.

   - `WebVerzeichnis/protected/runtime`: Enthält vertrauliche temporäre
Dateien, die während der Laufzeit der Anwendung erzeugt werden. Der
Webserver-Prozess muss in dieses Verzeichnis schreiben können. Der Pfad kann
über [CApplication::runtimePath] angepasst werden.

   - `WebVerzeichnis/protected/extensions`: Enthält Erweiterungen. Der Pfad kann über
[CApplication::extensionPath] angepasst werden. Das Verzeichnis kann in Yii
über den Pfadalias `ext` verwendet werden.

   - `WebVerzeichnis/protected/modules`: Enthält [Module](/doc/guide/basics.module), 
jeweils in einem entsprechenden Unterverzeichnis.

   - `WebVerzeichnis/protected/controllers`: Enthält alle Controllerdateien. Der Pfad kann über
[CWebApplication::controllerPath] angepasst werden.

   - `WebVerzeichnis/protected/views`: Enthält alle Viewdateien,
inklusive Controller-, Layout- und Systemviews. Der Pfad kann über 
[CWebApplication::viewPath] angepasst werden.

   - `WebVerzeichnis/protected/views/ControllerID`: Enthält die
Viewrdateien für einen Controller. `ControllerID` steht für die
ID des Controllers. Der Pfad kann über [CController::viewPath] angepasst
werden.

   - `WebVerzeichnis/protected/views/layouts`: Enthält alle 
Layout-Viewdateien. Der Pfad kann über [CWebApplication::layoutPath]
angepasst werden.

   - `WebVerzeichnis/protected/views/system`: Enthält alle
System-Viewdateien. Systemviews sind Vorlagen, die zur Anzeige von Exceptions
und Fehlern verwendet werden. Der Pfad kann über
[CWebApplication::systemViewPath] angepasst werden.

   - `WebVerzeichnis/assets`: Dieses Verzeichnis enthält veröffentlichte
Assetdateien. Eine Assetdatei ist eine vertrauliche Datei die 
veröffentlicht werden kann, um sie vom Web aus zugänglich zu machen.
Der Webserver-Prozess muss in dieses Verzeichnis schreiben können.
Der Pfad kann über [CAssetManager::basePath] angepasst werden.

   - `WebVerzeichnis/themes`: Dieses Verzeichnis enthält verschiedene Themes, 
die auf eine Applikation angewendet werden können. Der Pfad kann
über [CThemeManager::basePath] angepasst werden.


Datenbanken
-----------

Die meisten Webanwendungen sind datenbankgestützt. Wir empfehlen folgende
Namenskonventionen für Tabellen und Spalten. Für Yii sind diese allerdings
nicht zwingend nötig.

   - Sowohl Tabellen als auch Spalten werden in Kleinbuchstaben benannt.

   - Mehrere Wörter in einem Namen werden durch Untestrich getrennt (z.B. `produkt_name`).

   - Tabellenname sollten entweder im Singular oder im Plural stehen, aber
nicht gemischt. Der Einfachheit halber empfehlen wir den Singular.

   - Tabellennamen können mit eine Präfix wie `tbl_` versehen werden. Dies ist
insbesondere nützlich, wenn sich mehrere Anwendungen eine Datenbank teilen
müssen. 

<div class="revision">$Id: basics.convention.txt 3225 2011-05-17 23:23:05Z alexander.makarow $</div>
