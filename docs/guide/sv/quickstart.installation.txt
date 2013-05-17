Installation
============

Installation av Yii omfattar vanligtvis följande två steg:

   1. Ladda ned Yii-ramverket från [yiiframework.com](http://www.yiiframework.com/).
   2. Extrahera Yii:s distributionsfil till en katalog som är tillgänglig för webbanvändare.

> Tip|Tips: Yii behöver inte installeras i en katalog med webbåtkomst. En Yii-
applikation har ett startskript som vanligtvis är den enda filen som behöver 
exponeras för webbanvändare. Andra PHP-skript, inklusive de från Yii, bör 
skyddas från webbanvändare, då de annars riskerar att utnyttjas av hackers.

Systemkrav
----------

När Yii har installerats kan det vara önskvärt att verifiera att servern 
uppfyller kraven för användning av Yii. Detta kan göras med hjälp av skriptet 
för kontroll av systemkrav, via följande URL i en webbläsare:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

Yii kräver PHP 5.1, så  servern måste ha PHP 5.1 eller senare installerad 
och åtkomlig för webbservern. Yii har testats med [Apache HTTP-server](http://httpd.apache.org/) 
på Windows och Linux. Det bör även gå att köra på andra webbservrar och plattformar, 
förutsatt att PHP 5.1 stöds.

<div class="revision">$Id: quickstart.installation.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>