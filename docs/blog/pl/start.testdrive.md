Jazda próbna z Yii
====================

W części tej napiszemy jak utworzyć szkielet aplikacji, który będzie stanowić nasz punkt startowy. Dla uproszczenia, zakładamy, że główny katalog naszego serwera to `/wwwroot` a odpowiadający mu adres URL to `http://www.example.com/`.


Instalacja Yii
--------------

Na samym początku zainstalujemy framework Yii. W tym celu pobierz plik z wydaniem Yii (wersja 1.1.1 lub nowsze) spod adresu [www.yiiframework.com](http://www.yiiframework.com/download) i rozpakuj go do katalogu `/wwwroot/yii`. Upewnij się jeszcze raz, że istnieje folder `/wwwroot/yii/framework`.

> Tip|Wskazówka: framework Yii może zostać zainstalowany w dowolnym miejscu systemu plików, niekoniecznie w folderze sieci Web. Jego katalog `framework` zawiera cały kod frameworku i jest jedynym wymaganym folderem frameworku podczas wdrażania aplikacji napisanej w Yii. Pojedyncza instalacja Yii może być używana przez wiele aplikacji.

Po zainstalowaniu Yii, otwórz okno przeglądarki i wpisz adres URL `http://www.example.com/yii/requirements/index.php`. Pokaże on dostarczoną wraz z Yii stronę pozwalającą sprawdzić wymagania. Dla naszego blogu, poza minimalnymi wymaganiami stawianymi Yii, potrzebujemy włączyć rozszerzenia PHP `pdo` oraz `pdo_sqlite` by móc używać bazy danych SQLite.


Tworzenie szkieletu aplikacji
-----------------------------

Następnie przy użyciu narzędzia `yiic` utworzymy szkielet aplikacji w katalogu `/wwwroot/blog`. Narzędzie `yiic` jest narzędziem wiersza poleceń, dostarczonym wraz z wydaniem Yii. Może zostać użyte do wygenerowania kodu aby ograniczyć wykonywanie pewnych powtarzających się zadań.

Otwórz okno wiersza poleceń oraz wywołaj następującą komendę:

~~~
% /wwwroot/yii/framework/yiic webapp /wwwroot/blog
Create a Web application under '/wwwroot/blog'? [Yes|No]y
......
~~~

> Tip|Wskazówka: W celu wykorzystania narzędzia `yiic` w sposób pokazany powyżej program CLI PHP musi się znajdować w ścieżce poleceń. Jeśli tak nie jest, następująca komenda może zostać użyta w miejsce powyższej:
>
>~~~
> ścieżka/do/php /wwwroot/yii/framework/yiic.php webapp /wwwroot/blog
>~~~

Aby wypróbować aplikację, którą właśnie stworzyliśmy, otwórz przeglądarkę i przejdź do adresu `http://www.example.com/blog/index.php`. Powinniśmy zobaczyć, że nasza szkieletowa aplikacja posiada już cztery w pełni funkcjonalne strony: stronę domową, stronę o nas, stronę kontaktową oraz stronę logowania. 

W dalszej części, pokrótce opiszemy co znajduje się w właśnie stworzonym szkielecie aplikacji.

###Skrypt wejściowy

Mamy plik [skryptu wejściowego](http://www.yiiframework.com/doc/guide/basics.entry) `/wwwroot/blog/index.php`, który posiada następującą zawartość:

~~~
[php]
<?php
$yii='/wwwroot/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// usuń następującą linię jeśli pracujesz w trybie produkcyjnym
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
Yii::createWebApplication($config)->run();
~~~

Jest to jedyny skrypt, do którego internauci mają dostęp. Skrypt najpierw załącza plik inicjalizacyjny `yii.php`. Następnie tworzy instancję [aplikacji](http://www.yiiframework.com/doc/guide/basics.application) z określoną konfiguracją i uruchamia aplikację.


###Główny katalog aplikacji

Mamy również [główny katalog aplikacji](http://www.yiiframework.com/doc/guide/basics.application#application-base-directory) `/wwwroot/blog/protected`. Większość naszego kodu i danych znajdzie się w tym katalogu, dlatego więc nie powinien on być udostępniony dla internautów. Dla [serwera httpd Apache](http://httpd.apache.org/), umieszczamy w tym miejscu plik `.htaccess` z następującą zawartością:

~~~
deny from all
~~~

Dla pozostałych serwerów informacje o tym jak ochronić katalog przed dostępem z zewnątrz przez internautów można znaleźć w odpowiadających im podręcznikach.


Działanie aplikacji (ang. Application Workflow)
--------------------

Aby pomóc zrozumieć w jaki sposób Yii działa, opiszemy ogólnie przebieg działania aplikacji w naszym szkielecie aplikacji podczas gdy użytkownik żąda dostępu do strony kontaktowej:

 0. Użytkownik zażądał adresu URL `http://www.example.com/blog/index.php?r=site/contact`;
 1. [Skrypt wejściowy](http://www.yiiframework.com/doc/guide/basics.entry) jest wykonywany przez serwer sieciowy aby przetworzyć żądanie.
 2. Instancja [aplikacji](http://www.yiiframework.com/doc/guide/basics.application) jest tworzona i konfigurowana początkowymi wartościami określonymi w pliku konfiguracyjnym aplikacji `/wwwroot/blog/protected/config/main.php`;
 3. Aplikacja dzieli żądanie na [kontroler](http://www.yiiframework.com/doc/guide/basics.controller) oraz [akcję kontrolera](http://www.yiiframework.com/doc/guide/basics.controller#action). Dla żądania strony kontaktowej, aplikacja dzieli żądanie na kontroler `site` oraz akcję `contact` (metoda `actionContact` w `/wwwroot/blog/protected/controllers/SiteController.php`);
 4. Aplikacja tworzy kontroler `site` pod postacią instancji `SiteController` a następnie uruchamia go;
 5. Instancja kontrolera `SiteController` wykonuje akcję `contact` poprzez wywołanie metody kontrolera `actionContact()`;
 6. Metoda `actionContact` generuje internaucie [widok](http://www.yiiframework.com/doc/guide/basics.view) o nazwie `contact`. Wewnętrznie dzieje się to poprzez załączenie pliku widoku `/wwwroot/blog/protected/views/site/contact.php` i osadzeniu wyniku w pliku [układu](http://www.yiiframework.com/doc/guide/basics.view#layout) `/wwwroot/blog/protected/views/layouts/column1.php`.


<div class="revision">$Id: start.testdrive.txt 1734 2010-01-21 18:41:17Z qiang.xue $</div>