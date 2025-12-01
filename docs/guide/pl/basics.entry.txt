Skrypt wejściowy
================

Skrypt wejściowy to skrypt rozruchowy PHP, który zarządza żądaniami użytkownika.
Jest on jedynym skryptem, który użytkownik końcowy może wykonywać.

W większości przypadków skrypt wejściowy aplikacji Yii zawiera kod, który jest bardzo prosty,
np. taki:

~~~
[php]
// usuń tą linię na serwerze produkcyjnym
defined('YII_DEBUG') or define('YII_DEBUG',true);
// załaduj plik rozruchowy Yii
require_once('path/to/yii/framework/yii.php');
// utwórz instancję aplikacji oraz uruchom ją 
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

Skrypt na początku dołącza plik rozruchowy Yii `yii.php`. Następnie tworzy instancję 
aplikacji sieciowej wraz z odpowiednią konfiguracją i uruchamia ją.

Tryb debugowania
----------

W zależności od wartości stałej `YII_DEBUG` aplikacja Yii może być uruchomiona w trybie 
debugowania lub produkcyjnym. Domyślnie, zmienna ta posiada wartość `false`, 
co oznacza tryb produkcyjny. Aby uruchomić aplikację w trybie debugowania, 
należy zdefiniować tą zmienną jako `true` przed załadowaniem pliku `yii.php`.
Uruchamianie aplikacji w trybie debugowania jest mniej wydajne ponieważ przechowuje ona 
wiele wewnętrznych logów. Z drugiej strony, tryb debugowania jest bardziej pomocny 
podczas fazy dewelopmentu, ponieważ dostarcza bogatszych informacji w momencie wystąpienia
błędu.

<div class="revision">$Id: basics.entry.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>