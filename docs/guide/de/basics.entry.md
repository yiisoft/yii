Startscript
==============

Das Startscript ist für die erste Verarbeitung eines Requests zuständig.
Es ist das einzige Script der Anwendung, das direkt über das Web aufgerufen
werden kann.

Meistens enthält dieses Startscript einfachen Code wie diesen:

~~~
[php]
// Folgende Zeile im Produktivmodus entfernen:
defined('YII_DEBUG') or define('YII_DEBUG',true);
// Yii-Startdatei einbinden
require_once('path/to/yii/framework/yii.php');
// Instanz einer Applikation erzeugen und starten
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

Zunächst wird darin die Yii-Startdatei `yii.php` eingebunden. Danach wird eine
Instanz einer Webapplikation mit der angegebenen Konfiguration erzeugt und
gestartet.

Debug-Modus
-----------

Über die Konstante `YII_DEBUG` kann eine Yii-Anwendung entweder in den Debug- 
oder in den Produktivmodus versetzt werden. In der Standardeinstellung wird `YII-DEBUG` 
auf `false` gesetzt und die Anwendung läuft im Produktivmodus.
Um in den Debugmodus umzuschalten muss die Konstante auf `true` gesetzt
werden, bevor `yii.php` eingebunden wird. Im Debugmodus läuft die Anwendung
langsamer, da intern eine wesentlich größere Menge an Logmeldungen anfällt. 
In der Entwicklungsphase ist der Debugmodus allerdings sehr hilfreich, da 
im Fehlerfall umfangreiche Debuginformationen angezeigt werden können.

<div class="revision">$Id: basics.entry.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
