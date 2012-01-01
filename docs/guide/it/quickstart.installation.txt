Installazione
============

L'installazione di Yii principalmente si sviluppa nei seguenti due passi:

   1. Scarica il framework Yii da [yiiframework.com](http://www.yiiframework.com/).
   2. Scompatta i file di Yii in una cartella accessibile dal web.

> Suggerimento: Yii non ha bisogno di essere installato in una cartella  
accessibile dal web. Una applicazione Yii ha un solo entry file che 
solitamente è l'unico file che deve essere esposto agli utenti web. Gli altri 
script PHP, compresi quelli di Yii, dovrebbero essere protetti dall'accesso 
dal web, altrimenti potrebbero essere sfruttati dagli hackers.

Requisiti
------------

Dopo l'installazione di Yii, si consiglia di verificare che il server 
soddisfi i requisiti di Yii. Si può fare accedendo allo script di controllo dei 
requisiti raggiungibile al seguente URL tramite un browser web:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

Yii richiede PHP 5.1, per cui il server deve avere PHP 5.1 o superiore 
installato e disponibile per il web server. Yii è stato testato con 
[Apache HTTP server](http://httpd.apache.org/) su Windows e Linux. 
Yii può funzionare anche con altri web server e piattaforme dotate di PHP 5.1.

<div class="revision">$Id: quickstart.installation.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>