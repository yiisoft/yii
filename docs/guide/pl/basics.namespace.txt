Aliasy i przestrzenie nazw
========================

Yii używa w dużym stopniu aliasów ścieżek. Alias ścieżki reprezentuje katalog 
lub też ścieżkę pliku. Zapisany jest przy użyciu składni zawierającej kropki, podobnej
do powszechnie znanego formatu przestrzeni nazw:

~~~
AliasNadrzędny.ścieżka.do.celu
~~~

gdzie `AliasNadrzędny` jest aliasem do pewnego istniejącego katalogu. 

Używając [YiiBase::setPathOfAlias()], możemy przetłumaczyć alias na odpowiadającą mu ścieżkę.
Na przykład, `system.web.CController` zostanie przetłumaczony na ścieżkę `yii/framework/web/CController`.

Za pomocą [YiiBase::setPathOfAlias()] możemy również zdefiniować nowy alias do ścieżki katalogu głównego. 

Aliasy nadrzędne
----------

Dla wygody, Yii predefiniuje następujące aliasy nadrzędne: 

 - `system`: wskazuje katalog frameworku Yii; 
 - `zii`: wskazuje na katalog z [biblioteką Zii](/doc/guide/extension.use#zii-extensions); 
 - `application`: wskazuje do [katalogu głównego aplikacji](/doc/guide/basics.application#application-base-directory);
 - `webroot`: wskazuje na katalog zawierający plik [skryptu wejściowego](/doc/guide/basics.entry). 
 - `ext`: wskazuje na katalog zawierający wszystkie [rozszerzenia](/doc/guide/extension.overview) stron trzecich. 
 
Dodatkowo, jeśli aplikacja używa [modułów](/doc/guide/basics.module), każdy z nich będzie posiadał 
predefiniowany alias główny o tej samej nazwie jak ID modułu i będzie on wskazywał na katalog
główny danego modułu. Na przykład, jeśli aplikacja używa modułu, którego ID to `users`, to 
zostanie predefiniowany dla niego alias główny `users`.


Importowanie klas
-----------------

Używanie aliasów jest bardzo wygodne w celu dołączenia definicji klasy. 
Na przykład, jeśli chcemy dołączyć definicję klasy [CController], możemy to zrobić następująco:

~~~
[php]
Yii::import('system.web.CController');
~~~

Metoda [import|YiiBase::import] różni się od `include` oraz `require` tym, że jest 
bardziej wydajna. Definicja klasy, która została importowana, nie jest ładowana 
dopóki nie zostanie użyta po raz pierwszy (zaimplementowano przy użyciu mechanizmu 
autoładowania PHP). Importowanie wielokrotnie tej samej 
przestrzeni nazw jest także dużo szybsze niż `include_once` czy też `require_once`.

> Tip|Wskazówka: Kiedy odnosimy się do klasy zdefiniowanej we frameworku Yii, nie musimy
> jej importować lub dołączać. Wszystkie klasy Yii są preimportowane.

###Używanie map klas

Poczynając od wersji 1.1.5, Yii umożliwia preimportowanie klas poprzez mechanizm
mapowania klas, który jest również używany przez klasy bazowe Yii. Preimportowane
klasy mogą być używane w dowolnym miejsu w aplikacji Yii bez potrzeby jawnego
importu czy też dołączania. Funkcjonalność ta jest najczęściej używana przez frameworki
lub też biblioteki, które zostały zbudowane w oparciu o Yii.

Aby preimportować zestaw klasy, następujący kod musi zostać wykonany, zanim 
metoda [CWebApplication::run()] zostanie wywołana:

~~~
[php]
Yii::$classMap=array(
	'NazwaKlasy1' => 'ścieżka/do/Klasy1.php',
	'NazwaKlasy2' => 'ścieżka/do/Klasy2.php',
	......
);
~~~


Importowanie katalogów
---------------------

Możemy również użyć następującej składni do importowania całego katalogu, tak, że 
wszystkie pliki klas w katalogu będą automatycznie dołączone gdy zajdzie taka potrzeba.

~~~
[php]
Yii::import('system.web.*');
~~~

Poza metodą [import|YiiBase::import] aliasy są używane w wielu innych miejscach odnoszących się do klasy.
Na przykład, alias może zostać przekazany do metody [Yii::createComponent()] w celu
utworzenia instancji klasy komponentu, nawet jeśli plik klasy nie był dołączony wcześniej.

Przestrzeń nazw (ang. namespace)
---------

Przestrzeń nazw wskazuje na logiczne grupowanie pewnych nazw klas, w celu 
rozróżnienia ich od innych klas, nawet gdy te mają te same nazwy. Nie należy mylić 
aliasów do ścieżek z przestrzeniami nazw. Alias jest jedynie wygodnym sposobem
nazwania pliku lub katalogu. Nie ma to nic wspólnego z przestrzenią nazw.

> Tip|Wskazówka: Ponieważ wersje PHP wcześniejsze niż 5.3.0 nie wspierały przestrzeni 
nazw, nie możesz stworzyć instancji dwóch klas, które posiadają tą samą nazwę 
ale różnią się definicjami. Z tego powodu, Wszystkie klasy frameworku poprzedzone 
są prefiksem zawierającym literę 'C' (od ang. 'class' - klasa), tak, by móc je rozróżnić 
od klas zdefiniowanych przez użytkownika. Zaleca się, aby prefix 'C' był zarezerwowany
wyłącznie dla frameworku Yii a klasy użytkownika były poprzedzone prefiksem składającym 
się z innej litery.

Klasy w przestrzeni nazw
------------------------

Klasy w przestrzeni nazw są klasami zadeklarowanymi w nieglobalnej przestrzeni nazw.
Na przykład, klasa `application\components\GoogleMap` zadeklarowana jest w przestrzeni nazw
`application\components`. Używanie klas w przestrzeni nazw wymaga PHP w wersji 5.3.0 lub wyższej.

Poczynając od wersji 1.1.5, istnieje możliwość używania klas w przestrzeni nazw bez konieczności 
ich dołączania w sposób jawny. Na przykład, możemy utworzyć nową instancję 
`application\components\GoogleMap` bez dołączania odpowiadającego jej pliku w sposób jawny.
Jest to możliwe, dzięki rozszerzeniu zaimplementowanego w Yii mechanizmu autoładowania klas.

W celu umożliwienia autoładowania klas w przestrzeni nazw, przestrzeń nazw musi być nazwana
w podobny sposób do nazw aliasów ścieżek. Na przykład, klasa `application\components\GoogleMap`
musi być zapisana w pliku, którego aliasem jest `application.components.GoogleMap`.


<div class="revision">$Id: basics.namespace.txt 3086 2011-03-15 00:04:53Z qiang.xue $</div>