Installasjon
============

Installasjon av Yii består hovedsakling av følgende steg:

   1. Last ned Yii Framework fra [yiiframework.com](http://www.yiiframework.com/).
   2. Pakk ut Yii-pakka i en mappe under webserveren din.

> Tip|Tips: Yii trenger ikke bli installert under en mappe på webserveren.
En Yii-applikasjon har et start-script som vanligvis er den eneste fila som
må eksponeres for nettbrukere. Andre PHP-script, inkludert de fra Yii, bør
beskyttes fra å være tilgjengelige på webserveren siden de kan utsettes
for hacking.

Systemkrav
----------

Etter installasjonen av Yii bør du sjekke at webserveren din tilfredstiller
alle krav for å bruke Yii. Du kan gjøre dette med et script som du når med
følgende URL i nettleseren din:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

Minimumskravet til Yii er at webserveren din støtter PHP 5.1.0 eller nyere.
Yii har blitt testet med [Apache HTTP server](http://httpd.apache.org/) på
Windows og Linux. Det kan også kjøres på andre webservere og plattformer
hvor PHP 5 er tilgjengelig.

<div class="revision">$Id: quickstart.installation.txt 359 2008-12-14 19:50:41Z qiang.xue $</div>
