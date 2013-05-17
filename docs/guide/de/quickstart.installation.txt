Installation
============

Die Installation von Yii erfolgt im Wesentlichen in zwei Schritten:

   1. Herunterladen des Yii-Frameworks von [yiiframework.com](http://www.yiiframework.com/).
   2. Entpacken des Yii-Pakets in ein Verzeichnis mit Webzugriff.

> Tip|Tipp: Yii muss nicht zwingend in ein Webverzeichnis installiert werden.
Bei einer Yii-Anwendung muss in der Regel nur das Startscript (meist
index.php) vom Web aus erreichbar sein. Alle anderen PHP-Dateien (inkl. denen
von Yii) sollten nicht von außen erreichbar sein, da sie sonst für
Hack-Versuche missbraucht werden könnten.

Voraussetzungen
---------------

Nachdem Sie Yii installiert haben, sollten Sie überprüfen, ob Ihr Webserver auch
alle Voraussetzungen erfüllt. Dazu können Sie über diese URL einen Test per Webbrowser ausführen:

~~~
http://hostname/pfad/zu/yii/requirements/index.php
~~~

Da Yii PHP 5.1.0 benötigt, muss auf dem Server mindestens PHP 5.1.0
installiert sein und im Webserver zur Verfügung stehen. Yii wurde
bisher mit dem [Apache HTTP Server](http://httpd.apache.org/) und Windows-
und Linux-Betriebssystemen getestet. Es läuft u.U. aber auch auf anderen Webservern und
Plattformen, sofern PHP 5.1 dort unterstützt wird.

<div class="revision">$Id: quickstart.installation.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>
