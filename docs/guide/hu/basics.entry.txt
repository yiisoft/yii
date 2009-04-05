Belépési Szkript
================

A belépési szkript nem más, mint a rendszertöltő PHP szkript, ami a felhasználói
kéréseket kezeli. Ez az egyetlen PHP szkript, aminek a végrehajtását a felhasználók
közvetlenül kezdeményezhetik.

A legtöbb esetben egy Yii alkalmazás belépési szkriptje a következőhöz hasonlóan
egyszerű:

~~~
[php]
// éles alkalmazásban a következő sor eltávolítandó
defined('YII_DEBUG') or define('YII_DEBUG',true);
// Yii rendszertöltő fájl behívása
require_once('path/to/yii/framework/yii.php');
// alkalmazás példány létrehozása és futtatása
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

A fenti szkript elsőként beolvassa a `yii.php`-t a Yii keretrendszer
rendszertöltő fájlját. Ezután létrehoz egy Webalkalmazás példányt a
megadott konfigurációval, majd futtatja.

Debug Üzemmód
-------------

Egy Yii alkalmazás futhat debug üzemmódban, vagy éles verzióként is, a
`YII_DEBUG` állandó értékétől függően. Alapbeállításként `false`-ként van
meghatározva az értéke, ami élesített üzemmódot jelent. Debug üzemmódban
való futtatáshoz elég definiálnunk ezt az állandót `true` értékkel, mielőtt
behívnánk a `yii.php` fájlt. A debug üzemmód kevésbé hatékony futtatási mód,
lévén számos belső naplózást futtat. Másrészről fejlesztési fázisban ez az
üzemmód rendkívül hasznos, hiszen gazdag hibakeresési információval szolgál
egy-egy hiba esetén.

<div class="revision">$Id: basics.entry.txt 162 2008-11-05 12:44:08Z weizhuo $</div>