Używanie zewnętrznych bibliotek (ang. Using 3rd-Party Libraries)
=========================

Yii jest starannie zaprojektowane, tak by można było z łatwością zintegrować z nim 
zewnętrzne biblioteki i wykorzystać je do dalszego rozszerzania funkcjonalności Yii.
Podczas używania zewnętrznych bibliotek w projekcie, deweloperzy często spotykają 
się z problemami związanymi z nazywaniem klas oraz z dołączaniem plików. 
Ponieważ wszystkie klasy Yii są poprzedzone literą `C`, zachodzi mniejsze prawdopodobieństwo, 
że wystąpi problem z nazewnictwem klas. Aby załączyć plik Yii korzysta z 
[SPL autoload](http://us3.php.net/manual/en/function.spl-autoload.php)
co powoduje iż może ono dobrze współdziałać z innymi bibliotekami, jeśli
używają one SPL autoload lub PHP include do dołączania plików klas.

Poniższy przykład zilustruje nam w jaki sposób możemy użyć komponentu 
[Zend_Search_Lucene](http://www.zendframework.com/manual/en/zend.search.lucene.html)
pochodzącego z [frameworku Zend ](http://www.zendframework.com) w aplikacji Yii.

Po pierwsze, rozpakowujemy plik z wydaniem frameworku Zend do katalogu `protected/vendors`, 
przy założeniu, że `protected` jest [głównym folderem aplikacji](/doc/guide/basics.application#application-base-directory).
Po tej czynności sprawdź czy plik `protected/vendors/Zend/Search/Lucene.php` istnieje.

Po drugie, na początku klasy kontrolera wstaw następujący kod:

~~~
[php]
Yii::import('application.vendors.*');
require_once('Zend/Search/Lucene.php');
~~~

Powyższy kod dołączył plik klasy `Lucene.php`. Ponieważ używamy ścieżki relatywnej
potrzebujemy zmienić ścieżkę include PHP, tak aby plik mógł zostać zlokalizowany poprawnie.
Zrobiliśmy to poprzez wywołanie `Yii::import` przed `require_once`.

Gdy powyższe "ustawienia" są gotowe, możemy już użyć klasy `Lucene` w akcji kontrolera 
na przykład w następujący sposób:

~~~
[php]
$lucene=new Zend_Search_Lucene($pathOfIndex);
$hits=$lucene->find(strtolower($keyword));
~~~

Używanie zewnętrzych bibliotek z przestrzenią nazw (ang. namespace)
------------------------------------

W celu używania biblioteki z przestrzenią nazw, która jest zgodna z 
[PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
(np. Zend Framework 2 czy też Symfony2) musisz zarejetrować jej główny alias jako alias ścieżki.

Za przykład posłuży nam [Imagine](https://github.com/avalanche123/Imagine).
Jeżeli umieściliśmy bibliotekę `Imagine` w katalogu `protected/vendors` będziemy ją używali
w następujący sposób:

~~~
[php]
Yii::setPathOfAlias('Imagine',Yii::getPathOfAlias('application.vendors.Imagine'));

// A następnie kod z przewodnika po Imagine:
// $imagine = new Imagine\Gd\Imagine();
// itp.
~~~

W powyższym kodzie nazwa aliasu, którą zdefiniowaliśmy powinna zgadzać się z pierwszą częścią 
przestrzeni nazw używanej w bibliotece.

Używanie Yii w zewnętrznych systemach
------------------------------

Yii może być używane jako samowystarczalna biblioteka wspierająca rozwój oraz rozszerzanie
istniejących zewnętrznych systemów, takich jak WordPress, Joomla, itp. W tym celu, należy dołączyć
następujący kod w kodzie rozruchowym zewnętrznego systemu:

~~~
[php]
require_once('path/to/yii.php');
Yii::createWebApplication('path/to/config.php');
~~~

Powyższy kod jest bardzo podobny do kodu rozruchowego używanego przez typową aplikację Yii
z jednym wyjątkiem: nie wywołuje on metody `run()` po utworzeniu instancji aplikacji.

Od teraz możemy używać większości funkcjonalności oferowanych przez Yii podczas pracy nad rozszerzaniem
zewnętrznych aplikacji. Na przykład, możemy użyć `Yii::app()` w celu uzyskania dostępu do instancji aplikacji;
możemy używać funkcjonalności związanych z bazą danych takich jak DAO czy też rekord aktywny; możemy
używać modeli oraz funckjonalności sprawdzania ich poprawności; i tak dalej.

<div class="revision">$Id: extension.integration.txt 3431 2011-11-03 00:53:44Z alexander.makarow@gmail.com $</div>