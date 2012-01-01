Entry Script
============

L'entry script è lo script PHP di avvio che gestisce inizialmente le richieste 
utente. È l'unico script PHP che un utente può chiedere di eseguire direttamente.

Nella maggior parte dei casi, l'entry script di un'applicazione Yii contiene del 
codice semplice come questo:


~~~
[php]
// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// include Yii bootstrap file
require_once('path/to/yii/framework/yii.php');
// create application instance and run
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

Lo script prima include il file `yii.php` di avvio del framework Yii. Quindi 
crea una istanza della web application con una specifica configurazione e la 
esegue.

Modalità di Debug 
----------

Un'applicazione Yii può funzionare sia in modalità debug che in modalità 
produzione, in base al valore della costante `YII_DEBUG`. Per default, il valore 
di questa costante è `false`, cioè è in modalità produzione. Per funzionare in 
modalità debug, impostare la costante a `true` prima di includere il file 
`yii.php`. L'applicazione eseguita in modalità debug è meno efficiente perché 
mantiene parecchi log interni. D'altro canto, la modalità debug è molto più 
utile durante la fase di sviluppo perché fornisce molte più informazioni di 
debug quando si verifica un errore.

<div class="revision">$Id: basics.entry.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>