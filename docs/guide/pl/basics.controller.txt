Kontroler
==========

`Kontroler` jest instancją klasy [CController] lub klasy, która rozszerza [CController]. Jest on tworzony
przez obiekt aplikacji gdy użytkownik zażąda tego. Kiedy kontroler jest uruchomiony, wykonuje
żądaną akcję, która zazwyczaj wprowadza wymagane modele oraz generuje odpowiedni widok.
`Akcja`, w jej najprostszej formie, jest metodą klasy kontrolera, której nazwa rozpoczyna 
się od słowa `action`.

Kontroler posiada domyślną akcję. Kiedy żądanie użytkownika nie określa która akcja
powinna zostać wykonana, domyślna akcja jest wykonywana. Domyślnie, domyślna akcja 
nosi nazwę `index`. Może ona zostać zmieniona poprzez ustawienie publicznej zmiennej 
instancji kontrolera [CController::defaultAction].

Następujący kod definiuje kontroler `site` a w nim akcje `index` (akcja domyślna) 
oraz `contact`:

~~~
[php]
class SiteController extends CController
{
	public function actionIndex()
	{
		// ...
	}

	public function actionContact()
	{
		// ...
	}
}
~~~

Trasa (ang. route)
-----

Kontrolery i akcje są definiowane poprzez ich ID. ID kontrolera posiada format
`ścieżka/do/xyz` która odpowiada plikowi klasy kontrolera 
`protected/controllers/path/to/XyzController.php`, gdzie ciąg `xyz` powinien zostać 
zastąpiony przez aktualną nazwę (np. `post` odpowiada 
`protected/controllers/PostController.php`). ID akcji jest nazwą metody akcji
bez prefiksu `action`. Na przykład, jeśli klasa kontrolera zawiera metodę nazwaną
`actionEdit`, ID odpowiadającej jej akcji to `edit`.

Użytkownicy żądają poszczególnych kontrolerów oraz akcji za pomocą trasy.
Trasę tworzy połączenie ID kontrolera oraz ID akcji rozdzielone za pomocą ukośnika.
Na przykład, trasa `post/edit` odpowiada kontrolerowi `PostController` oraz jego
akcji `edit`. Domyślnie, adres URL `http://hostname/index.php?r=post/edit` będzie 
żądał kontrolera post oraz akcji edit.

>Note|Uwaga: Domyślnie, trasy rozróżniają wielkość liter.  
> Możliwe jest wyłączenie rozróżnienia wielkości liter poprzez ustawienie właściwości
>[CUrlManager::caseSensitive] na false w konfiguracji aplikacji.
>Będąc w trybie rozróżniania wielkości liter, upewnij się, że używasz konwencji 
> mówiącej, że nazwy folderów zawierające pliki klas kontrolerów zapisane są małymi
> literami a zarówno [mapa kontrolerów|CWebApplication::controllerMap]
>oraz [mapa akcji|CController::actions] używają kluczy zapisanych małymi literami.

Aplikacja może posiadać [moduły](/doc/guide/basics.module). 
Trasa dla akcji kontrolera wewnątrz modułu posiada format `IDmodułu/IDkontrolera/IDakcji`.
Aby uzyskać więcej szczegółów zobacz [sekcję dotyczącą modułów](/doc/guide/basics.module).


Tworzenie instancji kontrolerów (ang. Controller Instantiation)
------------------------

Instancja kontrolera jest tworzona podczas przetwarzania przez [CWebApplication] 
przychodzących żądań. Podając ID kontrolera, aplikacja będzie używała następujących 
reguł aby zdeterminować, która to klasa kontrolera oraz gdzie plik tej klasy się znajduje.

   - Jeśli [CWebApplication::catchAllRequest] jest określona, kontroler będzie utworzony
   bazując na tej właściwości a określone przez użytkownika ID kontrolera będzie ignorowane.
   Jest to używane głównie do uruchomienia aplikacji w trybie zarządzania i wyświetlania 
   statycznej strony z wiadomością.

   - Jeśli ID zostanie znalezione w [CWebApplication::controllerMap] odpowiadająca konfiguracja
   kontrolera będzie użyta do utworzenia instancji kontrolera.

   - Jeśli ID posiada format `'ścieżka/do/xyz'`, zakłada się, że klasa kontrolera 
   będzie nazywała się `XyzController` a odpowiadający plik klasy to 
   `protected/controllers/path/to/XyzController.php`. Na przykład, ID kontrolera 
   `admin/user` będzie zmapowane do klasy kontrolera `UserController` 
   i pliku klasy `protected/controllers/admin/UserController.php`. Jeśli 
   plik klasy nie istnieje wywoła to błąd 404 [CHttpException].

Gdy używane są [moduły](/doc/guide/basics.module), powyższy proces będzie trochę inny. 
W szczególności, aplikacja sprawdzi czy ID odpowiada, bądź nie, kontrolerowi wewnątrz modułu, 
jeśli tak, instancja modułu zostanie utworzona najpierw poprzedzona przez instancję kontrolera.

Akcja
------

Jak wspomniano wyżej akcja może zostać zdefiniowana jako metoda, której nazwa rozpoczyna 
się słowem `action`. Bardziej zaawansowaną techniką jest zdefiniowanie klasy akcji 
i poproszenie kontrolera o utworzenie jej instancji na żądanie. To pozwala akcjom
być używanym ponownie a to wprowadza możliwość ponownego użycia.

Aby zdefiniować nową klasę akcji, robimy co następuje:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// umieść logikę akcji
	}
}
~~~

Aby kontroler był świadom istnienia tej akcji, nadpisujemy metodę 
[actions()|CController::actions] naszej klasy kontrolera:

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

Powyżej, użyliśmy aliasu ścieżki `application.controllers.post.UpdateAction` aby 
określić, że plik klasy akcji to `protected/controllers/post/UpdateAction.php`.

Tworząc akcje oparte na klasach, możemy zorganizować aplikację w bardziej zmodularyzowany
sposób. Na przykład, następująca struktura katalogu może zostać użyta do zorganizowania 
kodu dla kontrolerów:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~

### Wiązanie parametrów akcji

Od wersji 1.1.4, Yii dodało wsparcie dla automatycznego wiązania parametrów akcji.
Oznacza to, że metoda kontrolera akcji może zdefiniować nazwane parametry, których wartości
będą automatycznie pobrane przez Yii ze zmiennej `$_GET`.

W celu zilustrowania jak to działa, załóżmy, że potrzebujemy napisać akcję tworznia `create` 
dla kontrolera wiadomości `PostController`. Akcja ta wymaga dwóch parametrów:

* kategoria `category`: liczba całkowita określająca ID kategorii w której post zostanie utworzony;
* język `language`: łańcuch, określający kod języka, w którym wiadomość zostanie napisana.

Możemy dać sobie już spokój z pisaniem następującego, nużącego kodu w celu zwrócenia potrzebnego parametru ze zmiennej `$_GET`:

~~~
[php]
class PostController extends CController
{
	public function actionCreate()
	{
		if(isset($_GET['category']))
			$category=(int)$_GET['category'];
		else
			throw new CHttpException(404,'invalid request');

		if(isset($_GET['language']))
			$language=$_GET['language'];
		else
			$language='en';

		// ... fajniejszy kod zaczyna się tutaj ...
	}
}
~~~

Od teraz, używając funkcjonalności parametrów akcji, możemy uczynić nasze zadanie przyjemniejszym:

~~~
[php]
class PostController extends CController
{
	public function actionCreate($category, $language='en')
	{
		$category=(int)$category;

		// ... fajniejszy kod zaczyna się tutaj ...
	}
}
~~~

Zauważ, że dodaliśmy dwa parametry do metody akcji `actionCreate`.
Nazwa tych parametrów musi być taka sama jak parametry, których oczekujemy w zmiennej `$_GET`.
Parametr języka `$language` przyjmie domyślną wartość `en` w przypadku, gdy żądanie nie zawiera 
takiego parametru. Ponieważ kategoria `$category` nie posiada wartości domyślnej
jeśli żądanie nie zawiera parametru `category`, automatycznie zostanie wyrzucony 
błąd [CHttpException] (o kodzie 404).

Poczynając od wersji 1.1.5, Yii wspiera również wykrywanie typu tablicowego dla parametrów akcji.
Odbywa się to przy użyciu podpowiadania typów PHP o następującej składni:

~~~
[php]
class PostController extends CController
{
	public function actionCreate(array $categories)
	{
		// Yii upewni się, że zmienna $categories jest tablicą
	}
}
~~~

Oznacza to, że dodaliśmy słowo kluczowe `array` przed `$categories` w deklaracji parametrów metody.
W ten sposób, jeśli `$_GET['categories']` jest zwykłym ciągiem znaków, zostanie on przekonwertowany do tablicy
zawierającej ten ciąg znaków.

> Note|Uwaga: Jeśli parametr został zadeklarowany bez określenia typu tablicowego `array`, oznacza to,
> że parametr musi być skalarem (np. nie może być tablicą). W takim przypadku, przekazywanie parametrów 
> poprzez tablicę w `$_GET` spowoduje błąd HTTP.

Poczynając od wersji 1.1.7 automatyczne wiązanie parametrów działa również dla akcji opartych na klasach.
Jeśli metoda `run()` klasy akcji zdefiniowana jest wraz z parametrami, będą one wypełnione odpowiadającymi 
im parametrami z żądania. Na przykład:

~~~
[php]
class UpdateAction extends CAction
{
	public function run($id)
	{
		// $id zostanie uzupełnione wartością  $_GET['id']
	}
}
~~~


Filtry
------

Filtr jest częścią kodu, który jest skonfigurowany tak, aby być wywołanym przed i/lub 
po wywołaniu akcji kontrolera. Na przykład, filtr kontroli dostępu może być
wywołany przed wywołaniem żądanej akcji aby upewnić się, że użytkownik jest uwierzytelniony; 
filtr wydajności może zostać użyty do mierzenia czasu wykonania akcji.

Akcja może posiadać wiele filtrów. Filtry są wykonywane w kolejności pojawiania
się na liście filtrów. Filtr może zabronić wywołania akcji oraz pozostałych niewykonanych filtrów.

Filtr może zostać zdefiniowany jako metoda kontrolera klasy. Nazwa metody musi rozpoczynać 
się słowem `filter`. Na przykład, metoda `filterAccessControl` definiuje 
filtr `accessControl`. Metoda filtra musi posiadać następującą składnię:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// wywołaj $filterChain->run() aby kontynuować wykonywanie filtra i akcji
}
~~~

gdzie `$filterChain` jest instancją klasy [CFilterChain], która reprezentuje listę filtrów
powiązanych z żądaną akcją. Wewnątrz metody filtra możemy wywołać `$filterChain->run()` 
aby kontynuować wykonywanie filtra i akcji.

Filtr może być instancją klasy [CFilter] lub jej klas pochodnych. Następujący kod 
definiuje nową klasę filtra:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logika która zostanie zastosowana zanim akcja zostanie wywołana
		return true; // false jeśli akcja nie powinna zostać wywołana
	}

	protected function postFilter($filterChain)
	{
		// logika, która będzie zastosowana po wywołaniu akcji
	}
}
~~~

Aby zastosować filtry do akcji musimy nadpisać metodę `CController::filters()`. 
Metoda ta powinna zwrócić tablicę konfiguracji filtrów. Na przykład: 

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

Powyższy kod definiuje dwa filtry: `postOnly` oraz `PerformanceFilter`.
Filtr `postOnly` jest filtrem opartym na metodzie (odpowiadająca filtrowi metoda jest 
już zdefiniowana w klasie [CController]); zaś filtr `PerformanceFilter` jest filtrem opartym 
na obiekcie. Alias ścieżki `application.filters.PerformanceFilter` określa, iż plikiem 
klasy filtru jest `protected/filters/PerformanceFilter`. W celu skonfigurowania 
filtru `PerformanceFilter` używamy tablicy, tak, że może ona zostać użyta do zainicjalizowania wartości
właściwości obiektu filtru. Tutaj właściwość `unit` klasy `PerformanceFilter` 
będzie zainicjowana wartością `'second'`.

Używając operatora plusa oraz minusa, możemy określić dla których akcji filtr powinien
a dla których nie powinien mieć zastosowania. W powyższym kodzie filtr `postOnly`
zostanie zastosowany dla akcji `edit` oraz `create`, zaś filtr
`PerformanceFilter` zostanie zastosowany do wszystkich akcji ZA WYJĄTKIEM 
`edit` oraz `create`. Jeśli żaden z operatorów plus lub minus nie pojawia się 
w konfiguracji filtra, filtr będzie zastosowany do wszystkich akcji.

<div class="revision">$Id: basics.controller.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>