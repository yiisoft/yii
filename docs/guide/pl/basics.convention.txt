Konwencje
===========

Yii przedkłada konwencje nad konfiguracją. Stosując się do konwencji można stworzyć
wyszukaną aplikację w Yii bez potrzeby pisania i zarządzania skomplikowaną konfiguracją.
Oczywiście, Yii wciąż można dostosowywać prawie w każdym aspekcie za pomocą konfiguracji, 
jeśli występuje taka potrzeba.

Poniżej opiszemy konwencje, które zalecamy używać podczas programowania w Yii.
Dla wygody zakładamy, że `WebRoot` jest katalogiem, w którym zainstalowane są aplikacje Yii.

Adresy URL
---

Domyślnie Yii rozpoznaje adresy URL w następującym formacie:

~~~
http://hostname/index.php?r=IdKontrolera/IdAkcji
~~~

Zmienna GET `r` prezentuje [trasę](/doc/guide/basics.controller#route) która może 
zostać rozkodowana przez Yii na nazwę kontrolera i akcji. Jeśli `IdAkcji` jest pominięte 
kontroler weźmie akcję domyślną (zdefiniowaną poprzez [CController::defaultAction]); 
a jeśli `IdKontrolera` zostanie również pominięte (lub zmienna `r` nie będzie występować) 
aplikacja użyje domyślnego kontrolera (zdefiniowanego poprzez [CWebApplication::defaultController]).

Przy pomocy [CUrlManager], możliwym jest utworzenie oraz rozpoznawanie bardziej 
przyjaznych SEO adresów URL, takich jak `http://hostname/IdKontrolera/IdAkcji.html`. 
Funkcjonalność ta jest szczegółowo opisana w [Zarządzaniu adresami URL](/doc/guide/topics.url).

Kod
----

Yii zaleca nazywać zmienne, funkcje oraz typy klas stosując notację wielbłądzią, w której 
to pierwsza litera każdego słowa jest wielka a słowa połączone są bez spacji. 
Nazwy zmiennych oraz funkcji powinny mieć pierwsze słowo zawsze pisane małą literą,
w celu odróżnienia ich od nazw klas (np. `$basePath`, `runController()`, `LinkPager`). 
Dla zmiennych prywatnych klasy zaleca się poprzedzić ich nazwy znakiem podkreślenia 
(np. `$_actionList`).

Ponieważ przestrzenie nazw nie są wspierane dla wersji PHP niższych niż 5.3.0
zaleca się, aby klasy były nazywane w sposób unikalny, w celu uniknięcia konfliktu 
nazw z zewnętrznymi klasami. Z tego powodu, wszystkie klasy frameworku poprzedzone są 
literą "C".

Specjalna zasada dotyczy kontrolerów, do których nazw musi zostać dodane słowo `Controller`. 
ID kontrolera jest wtedy definiowane jako nazwa klasy rozpoczynająca się małą literą 
bez słowa `Controller`. Na przykład, klasa `PageController` będzie posiadała ID `page`. 
Reguła ta powoduje, że aplikacja jest bezpieczniejsza. Przyczynia się to również do tego, 
że adresy URL związane z kontrolerami są trochę bardziej czytelne (np. `/index.php?r=page/index` 
zamiast `/index.php?r=PageController/index`).

Konfiguracja
-------------

Konfiguracja składa się z tablicy par klucz-wartość. Każdy klucz reprezentuje nazwę
właściwości obiektu konfigurowanego a każda wartość jest odpowiadającą jej wartością
inicjalną własności. Na przykład, `array('name'=>'Moja aplikacja', 'basePath'=>'./protected')` 
inicjalizuje właściwości `name` oraz `basePath` odpowiadającymi im wartościom z tablicy.

Każda właściwość obiektu umożliwiająca zapis do niej może zostać skonfigurowana.
Jeśli nie zostanie to zrobione, właściwość przyjmie wartość domyślną. Podczas konfigurowania 
właściwości, opłaca się przeczytać odpowiadającą jej dokumentację, tak aby nadać 
jej poprawną wartość początkową.

Plik
----

Konwencje nazywania oraz używania plików zależą od ich typów.

Pliki klas powinny zostać nazwane tak jak klasy, które zawierają. Na przykład, 
klasa [CController] znajduje się w pliku `CController.php`. Klasa publiczna, to taka klasa, 
która może zostać użyta w innych klasach. Każdy plik klasy powinien zawierać co najmniej 
jedną klasę publiczną. Klasy prywatne (klasy, które są używane tylko przez jedną klasę publiczną) 
mogą się znajdować w tym samym pliku, gdzie znajduje się klasa w której jest używana.

Klasy widoków, powinny być nazywane tak samo jak widoki. Na przykład, widok `index`
znajduje się w pliku `index.php`. Plik widoku jest skryptem PHP, który posiada kod 
PHP oraz HTML, w celach prezentacyjnych.

Pliki konfiguracyjne można nazwać arbitralnie. Plik konfiguracji jest skryptem PHP, 
którego jedynym celem jest zwrócić tablicę asocjacyjną reprezentującą konfigurację.


Katalogi
---------

Yii zakłada, że istnieje pewien zestaw katalogów używanych do różnych celów. 
Każdy z nich może zostać zmieniony, jeśli występuje taka potrzeba:

   - `WebRoot/protected`: jest to [główny katalog aplikacji](/doc/guide/basics.application#application-base-directory) 
   zawierający wszystkie ważne ze względu na bezpieczeństwo skrypty PHP oraz
   pliki z danymi. Yii posiada domyślny alias nazwany `application` powiązany z tą ścieżką. 
   Katalog ten, oraz wszystko co się w nim znajduje powinny być zabezpieczone przed 
   dostępem z zewnątrz przez użytkowników sieciowych. Katalog ten można zmienić poprzez 
   zmianę wartości [CWebApplication::basePath].

   - `WebRoot/protected/runtime`: katalog ten trzyma prywatne pliki tymczasowe, generowane 
   w czasie działania aplikacji. Katalog ten musi posiadać prawo zapisu dla procesów 
   serwera WWW. Można ją zmienić poprzez zmianę wartości [CApplication::runtimePath].

   - `WebRoot/protected/extensions`: katalog ten zawiera wszystkie zewnętrzne rozszerzenia. 
   Można go zmienić poprzez zmianę wartości [CApplication::extensionPath]. Yii posiada domyślny
   alias `ext` skojarzony z tą ścieżką.

   - `WebRoot/protected/modules`: katalog ten zawiera wszystkie [moduły](/doc/guide/basics.module)
   aplikacji, z których każdy reprezentowany jest przez podkatalog.

   - `WebRoot/protected/controllers`: katalog ten zawiera wszystkie pliki klas kontrolerów.
   Można go zmienić poprzez zmianę wartości [CWebApplication::controllerPath].

   - `WebRoot/protected/views`: katalog ten zawiera wszystkie pliki widoków, włączając 
   w to widoki kontrolera, układy widoku oraz widoki systemowe. 
   Można go zmienić poprzez zmianę wartości [CWebApplication::viewPath].

   - `WebRoot/protected/views/ControllerID`: katalog ten zawiera pliki widoków dla 
   pojedynczej klasy kontrolera. Tutaj `ControllerID` oznacza ID kontrolera.
   Można go zmienić poprzez zmianę wartości [CController::viewPath].

   - `WebRoot/protected/views/layouts`: katalog ten zawiera pliki wszystkich układów 
   widoków. Można go zmienić poprzez zmianę wartości [CWebApplication::layoutPath].

   - `WebRoot/protected/views/system`: katalog ten zawiera pliki wszystkich widoków 
   systemowych. Widoki systemowe są szablonami używanymi do wyświetlania wyjątków 
   oraz błędów. Można go zmienić poprzez zmianę wartości [CWebApplication::systemViewPath].

   - `WebRoot/assets`: katalog ten zawiera opublikowane pliki zasobów. Plik zasobu
   jest prywatnym plikiem, który może zostać opublikowany, by stać się dostępnym dla 
   użytkowników. Katalog ten musi posiadać prawa zapisu dla procesu serwera WWW. 
   Można go zmienić poprzez zmianę wartości [CAssetManager::basePath].

   - `WebRoot/themes`: katalog ten zawiera różne tematy, które można zastosować do
   aplikacji. Każdy podkatalog reprezentuje pojedynczy temat, którego nazwa jest 
   nazwą podkatalogu. Można go zmienić poprzez zmianę wartości [CThemeManager::basePath].

Baza danych
--------

Większość aplikacji internetowych posiłkuje się bazami danych. Jako najlepszą praktykę, proponujemy następującą konwencję nazewnictwa dla baz danych oraz kolumn. Zauważ, iż nie są one wymagane przez Yii:

   - używamy małych liter do nazywania tabel bazy danych jak i kolumn.

   - słowa w nazwie powinny być rozdzielone za pomocą podkreślenia (e.g. `product_order`).

   - dla nazw tabeli, możesz używać zarówno liczby pojedynczej jak i mnogiej, ale nie obu równocześnie. Dla prostoty, zalecamy używanie liczby pojedynczej.

   - nazwy tabel mogą być poprzedzone przez wspólny token, np. `tbl_`. Jest to szczególnie użyteczne kiedy tabele aplikacji współistnieją w tej samej bazie danych z tabelami innej aplikacji. Oba zestawy tabel mogą zostać z łatwością rozdzielone poprzez użycie różnych prefiksów dla nazw tabel.


<div class="revision">$Id: basics.convention.txt 3225 2011-05-17 23:23:05Z alexander.makarow $</div>