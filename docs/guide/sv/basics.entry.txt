Startskript
============

Startskriptet är boot-skriptet som initialt tar hand om en inkommen 
request från användare. Det är det enda skript som slutanvändare har direkt 
åtkomst till för exekvering.

I de flesta fall innehåller en Yii-applikations startskript lika okomplicerad kod som denna:

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

Skriptet inkluderar först Yii-ramverkets boot-fil `yii.php`. Sedan skapar det en 
webbapplikationsinstans enligt specificerad konfiguration och kör denna.

Debugläge
---------

En Yii-applikation kan köras i antingen debug- eller produktionsläge givet av 
värdet hos konstanten `YII_DEBUG`. Som standard är denna konstant definierad 
till `false`, vilket innebär produktionsläge. För att köra i debugläge, 
definiera konstanten till `true` före inkludering av filen `yii.php`. Körning av 
applikationen i debugläge är mindre effektivt eftersom många interna 
loggar administreras. Å andra sidan är debugläge också till stor hjälp i utvecklingsfasen 
eftersom utförligare information blir tillgänglig när fel uppträder.

<div class="revision">$Id: basics.entry.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>