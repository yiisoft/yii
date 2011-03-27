Installatie
===========

De installatie van Yii betreft voornamelijk de volgende twee stappen:

   1. Download het Yii-framework van [yiiframework.com](http://www.yiiframework.com/).
   2. Pak het Yii-releasebestand uit naar een web-toegankelijke map.

> Tip: Yii hoeft niet geinstalleerd te worden in een web-toegankelijke map.
Een Yii-applicatie heeft één startscript, welke gebruikelijk het enige bestand
is dat toegankelijk moet zijn voor webgebruikers. Andere PHP-scripts, inclusief
die van Yii, zouden beschermt moeten zijn voor toegang vanaf het web, omdat
ze mogelijk misbruikt kunnen worden door hackers.


Vereisten
---------

Na de installatie van Yii wil je mogelijk verifiëren dat de server voldoet aan
de systeemeisten van Yii. Dit kan je doen door het requirements-checker-script
aan te roepen op de volgende URL in een webbrowser:

~~~
http://hostnaam/pad/naar/yii/requirements/index.php
~~~

De minimumeis voor Yii is support voor PHP 5.1.0 of hoger van de webserver.
Yii is getest met [Apache HTTP server](http://httpd.apache.org/) op Windows en
Linux-systemen. Het draait mogelijk ook op andere webservers en platformen,
aangenomen dat PHP 5 ondersteund word.

<div class="revision">$Id: quickstart.installation.txt 1622 2009-12-26 20:56:05Z qiang.xue $</div>