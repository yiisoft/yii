Fisierul de intrare
===================

Fisierul de intrare este fisierul PHP care trateaza initial cererile utilizatorilor.
Este singurul fisier PHP accesibil pe care utilizatorii il pot executa direct.

In cele mai multe cazuri, fisierul de intrare al unei aplicatii Yii
contine codul urmator: 

~~~
[php]
// comentam urmatoarea linie de cod atunci cand site-ul este facut public
defined('YII_DEBUG') or define('YII_DEBUG',true);
// includem fisierul bootstrap Yii
require_once('path/to/yii/framework/yii.php');
// cream instanta application si o rulam
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

Acest fisier include intai fisierul bootstrap al platformei Yii `yii.php`. Apoi
creaza o instanta de aplicatie Web cu configuratia specificata, si apoi o ruleaza.

Modul Debug
-----------

O aplicatie Yii poate rula ori in modul debug, ori in modul production. Valoarea constantei
`YII_DEBUG` determina acest mod. Implicit, valoarea constantei este`false`,
aceasta insemnand ca modul implicit este production. Pentru a rula aplicatia in modul debug,
definim constanta ca fiind `true` inainte de a include fisierul `yii.php`. Rularea
aplicatiei in modul debug este mult mai putin eficienta din cauza log-urilor interne necesare
in timpul stadiului de dezvoltare al aplicatiei cand avem nevoie de mai multe informatii
atunci cand apar erori de programare. 

<div class="revision">$Id: basics.entry.txt 162 2008-11-05 12:44:08Z weizhuo $</div>