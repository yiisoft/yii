Moduł
======

Moduł jest samowystarczalną jednostką aplikacji, która zawiera [modele](/doc/guide/basics.model), 
[widoki](/doc/guide/basics.view), [kontrolery](/doc/guide/basics.controller) oraz inne 
wspierające komponenty. W wielu aspektach, moduły przypominają [aplikację](/doc/guide/basics.application). 
Główna różnica jest taka, że moduł nie może istnieć sam i musi rezydować 
w aplikacji. Użytkownicy posiadają dostęp do kontrolerów w modułach w ten sam sposób
jak w kontrolerze aplikacji. 

Moduły są użyteczne w kilku scenariuszach. Dla dużych aplikacji, możemy ją podzielić
na kilka modułów, każdy z nich może być pisany oraz zarządzany osobno. Część 
wspólnie używanych funkcjonalności, takich jak zarządzanie użytkownikami, zarządzanie
komentarzami, mogą być opracowywane w postaci modułów, które można łatwo ponownie 
wykorzystać w przyszłych projektach. 

Tworzenie modułu
---------------

Moduł jest zorganizowany w katalogu, którego nazwa służy jako jego unikalne [ID|CWebModule::id].
Struktura katalogów modułu jest podobna do tej z 
[katalogu głównego aplikacji](/doc/guide/basics.application#application-base-directory).
Poniżej pokażemy typową strukturę modułu nazwanego `forum`:

~~~
forum/
   ForumModule.php            klasa modułu
   components/                posiada komponenty użytkownika do ponownego użycia
      views/                  zawiera pliki widoków dla widżetów
   controllers/               zawiera pliki klas kontrolerów
      DefaultController.php   plik domyślnego kontrolera klasy
   extensions/                zawiera zewnętrzne rozszerzenia
   models/                    zawiera pliki modeli klas
   views/                     zawiera pliki widoku kontrolera oraz układów
      layouts/                zawiera pliki układów widoku
      default/                zawiera plik widoku dla DefaultController (domyślnego kontrolera)
         index.php            plik widoku index
~~~

Moduł musi posiadać klasę modułu, która dziedziczy z [CWebModule]. Nazwa klasy zależy
od wyniku wyrażenia `ucfirst($id).'Module'`, gdzie `$id` odpowiada ID modułu 
(lub katalogowi modułu). Klasa modułu służy jako główne miejsce dla przechowywania 
informacji współdzielonych wewnątrz kodu modułu. Na przykład, możemy użyć [CWebModule::params] 
do przechowywania parametrów modułu oraz [CWebModule::components] do dzielenia 
[komponentów aplikacji](/doc/guide/basics.application#application-component) na poziomie 
modułu. 

> Tip|Wskazówka: Aby utworzyć podstawowy szkielet nowego modułu możemy używać generatora modułów zawartego w Gii.


Używanie modułów
------------

Aby używać modułu, najpierw umieść moduł w katalogu `modules` w 
[katalogu głównym aplikacji](/doc/guide/basics.application#application-base-directory). 
Następnie zadeklaruj ID modułu we właściwości [modules|CWebApplication::modules] aplikacji. 
Na przykład, w celu używania powyższego modułu `forum`, możemy używać następującej
[konfiguracji aplikacji](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

Moduł może również zostać skonfigurowany poprzez wartości inicjalne właściwości. 
Sposób użycia jest bardzo podobny do tego z konfiguracji 
[komponentów aplikacji](/doc/guide/basics.application#application-component). 
Na przykład, moduł `forum` może mieć właściwość nazwaną `postPerPage` (ilość postów na stronę) 
w swojej klasie, która może zostać skonfigurowana w [konfiguracji aplikacji(/doc/guide/basics.application#application-configuration) 
następująco:

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

Dostęp do instancji modułu można uzyskać poprzez właściwość [module|CController::module] 
aktualnie aktywnego kontrolera. Poprzez instancję modułu, możemy uzyskać dostęp do
informacji dzielonych na poziomie modułu. Na przykład, w celu uzyskania dostępu 
do powyższej informacji o ilości postów na stronę `postPerPage`, możemy użyć 
następującego wyrażenia:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// lub następująco jeśli $this oznacza instancję kontrolera
// $postPerPage=$this->module->postPerPage;
~~~

Dostęp do akcji kontrolera w module można uzyskać poprzez [trasę](/doc/guide/basics.controller#route) 
`IDmodułu/IDkontrolera/IDakcji`. Na przykład zakładając, że powyższy moduł `forum` 
posiada kontroler nazwany `PostController`, używamy [trasy](/doc/guide/basics.controller#route) 
`forum/post/create` aby odnieść się do akcji `create` w tym kontrolerze. 
Odpowiadający tej trasie adres URL będzie następujący `http://www.example.com/index.php?r=forum/post/create`.

> Tip|Wskazówka: Jeśli kontroler jest podkatalogiem katalogu `controllers` 
możemy wciąż używać powyższego formatu [trasy](/doc/guide/basics.controller#route). 
Na przykład zakładając, że kontroler `PostController` znajduje się wewnątrz 
`forum/controllers/admin`, możemy odnieść się do akcji `create` używając `forum/admin/post/create`.


Zagnieżdżone moduły
-------------

Liczba zagnieżdzeń modułów jest nieograniczona. Oznacza to, że jeden moduł może posiadać inne moduły,
które mogą posiadać kolejne moduły. Pierwszego nazywamy *modułem rodzica* a drugiego *modułem dziecka*. Moduły dzieci muszą zostać zadeklarowane we właściwościach [modules|CWebModule::modules] swego rodzica,
w taki sam sposób jak moduły w konfiguracji aplikacji z wcześniejszych przykładów.
Aby uzyskać dostęp do akcji modułu dziecka, powinniśmy skorzystać z trasy
`IDModułuRodzica/IDModułuDziecka/IDKontrolera/IDAkcji`.


<div class="revision">$Id: basics.module.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>