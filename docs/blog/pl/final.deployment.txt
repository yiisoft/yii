Końcowe dopieszczanie oraz wdrażanie
============================

Zbliżamy się do zakończenia prac nad naszym blogiem. Zanim go wdrożymy chcielibyśmy dokonać kilku ulepszeń.


Zmiana strony domowej
------------------

Ustawimy stronę wyświetlająca listę wiadomości jako stronę domową. W tym celu zmienimy [konfigurację aplikacji](http://www.yiiframework.com/doc/guide/basics.application#application-configuration) w następujący sposób,

~~~
[php]
return array(
	......
	'defaultController'=>'post',
	......
);
~~~

> Tip|Wskazówka: Ponieważ kontroler `PostController` zawiera już akcję `index` jako domyślną akcje, podczas wchodzenia na stronę domową aplikacji zobaczymy rezultat wygenerowany przez akcję `index` kontrolera wiadomości.


Włączenie buforowania schematu bazy
-----------------------

Ponieważ rekord aktywny wykorzystuje metadane pochodzące z tabel w celu określenia informacji o kolumnach, zajmuje mu trochę czasu ich odczytanie oraz przeanalizowanie. Może to nie być problemem podczas fazy tworzenia aplikacji ale dla aplikacji działającej w trybie produkcyjnym, jest to totalne marnotrawstwo czasu, jeśli schemat bazy danych nie zmienia się. Dlatego też, włączymy buforowanie schematu poprzez zmodyfikowanie konfiguracji aplikacji w następujący sposób,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CDbCache',
		),
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:/wwwroot/blog/protected/data/blog.db',
			'schemaCachingDuration'=>3600,
		),
	),
);
~~~

W powyższym kodzie, najpierw dodaliśmy komponent buforowania `cache`, który używa domyślnie bazy danych SQLite jako miejsce składowania bufora. Jeśli twój serwer jest wyposażony w inne rozszerzenie buforujące, takie jak APC, moglibyśmy przełączyć się równie dobrze na jego używanie. Zmodyfikujemy również komponent `db` poprzez ustawienie jego właściwości [schemaCachingDuration|CDbConnection::schemaCachingDuration] na wartość 3600, co oznacza, że przeanalizowane dane schematu bazy danych pozostaną ważne w buforze na okres 3600 sekund.


Wyłączanie trybu debugowania
------------------------

Zmodyfikujemy plik wejściowy `/wwwroot/blog/index.php` poprzez usunięcie linii definiującej stałą `YII_DEBUG`. Stała ta jest użyteczna podczas fazy tworzenia, gdyż pozwala ona Yii wyświetlać więcej informacji użytecznych dla debugowania w przypadku wystąpienia błędu. Jednakże, jeśli aplikacja działa w trybie produkcyjnym, wyświetlanie takich informacji nie jest najlepszym pomysłem ponieważ mogą one zawierać wrażliwe informacje takie jak miejsce, gdzie skrypt się znajduje, oraz zawartość jego pliku, itp.


Wdrażanie aplikacji
-------------------------

Końcowy proces wdrażana polega głównie na kopiowaniu katalogu `/wwwroot/blog` do docelowego katalogu. Następujący wykaz czynności kontrolnych pokazuje wszystkie potrzebne kroki:

 1. Zainstaluj Yii w docelowym miejscu jeśli nie jest ono jeszcze tam dostępne; 
 2. Skopiuj całą zawartość katalogu `/wwwroot/blog` do miejsca docelowego;
 3. Zmień plik skryptu wejściowego `index.php` poprzez wskazanie zmiennej `$yii` na nowy plik rozruchowy (ang. bootstrap file);
 4. Wyedytuj plik `protected/yiic.php` poprzez ustawienie zmiennej `$yiic` wskazujące  
 na nowy plik `yiic.php`;
 5. Zmień uprawnienia do katalogów `assets` oraz `protected/runtime` tak aby były one 
 zapisywalne przez proces Web serwera.


<div class="revision">$Id: final.deployment.txt 2017 2010-04-05 17:12:13Z alexander.makarow $</div>