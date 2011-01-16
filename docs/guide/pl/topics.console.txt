Aplikacje konsolowe
====================

Aplikacje konsolowe używane są głównie przez aplikacje webowe do wykonywania zadań 
w trybie online, takich jak generowanie kodu, kompilacja indeksu wyszukiwania,  
wysyłanie maili, itp. Yii dostarcza frameworku do pisania aplikacji konsolowych  
w sposób systematyczny i obiektowo zorientowany. Yii udostępnia aplikacji konsolowej
zasoby (np. połączenia z bazą danych), które używane są przez aplikację sieciową 
w trybie online.

Przegląd
====================

Yii reprezentuje każde zadanie konsolowe pod pojęciem [polecenia|CConsoleCommand].
Polecenia konsolowe zostały napisane klasy dziedziczące z [CConsoleCommand].

Jeśli używamy narzędzia `yiic webapp` w celu utworzenia początkowego szkieletu aplikacji Yii,
znajdujemy dwa następujące pliki w katalogu `protected`:

* `yiic`: jest skrypt wykonywalny na systemie Linux/Unix;
* `yiic.bat`: jest to wykonywalny plik wsadowy używany w systemie Windows.

W oknie konsoli, możemy wprowadzić następujące polecenia:

~~~
cd protected
yiic help
~~~

Spowoduje to wyświetlenie listy dostępnych poleceń konsolowych. Domyślnie, dostepne polecenia
zawierają te dostarczone przez framework Yii (nazywane **poleceniami systemowymi**)
oraz te utworzone przez użytkownika dla poszczególnych aplikacji (nazywanych **poleceniami użytkownika**).

Aby zobaczyć jak używać poleceń, możemy wywołać

~~~
yiic help <nazwa-polecenia>
~~~

Aby wywołać polecenie, używamy następującego formatu poleceń:

~~~
yiic <nazwa-polecenia> [parametry...]
~~~

Tworzenie poleceń
---------------

Polecenia konsolowe zapisane są w plikach, z klasami, w katalogu określonym 
poprzez parametr [CConsoleApplication::commandPath]. Domyślnie wskazuje on 
na `protected/commands`.

Klasa polecenia konsolowego musi dziedziczyć po [CConsoleCommand]. Nazwa klasy
powinna posiadać format `XyzCommand`, gdzie `Xyz` oznacza nazwę polecenia,
którego nazwa rozpoczyna się dużą literą. Np, polecenie `sitemap` musi używać klasy
o nazwie `SitemapCommand`. Wielkość liter poleceń konsolowych ma znaczenie.


> Tip|Wskazówka: Poprzez skonfigurowanie [CConsoleApplication::commandMap] można posiadać również 
> klasy poleceń spełniające inne konwencje nazewnictwa oraz znajdujące się w innych katalogach.

Aby utworzyć nowe polecenie, nierzadko trzeba nadpisać metodę [CConsoleCommand::run()]
lub też stworzyć jedną bądź też kilka akcji poleceń (zostanie to wyjaśnione w dalszej części).

Podczas wykonywania polecenia konsolowego, metoda [CConsoleCommand::run()] zostanie wywołana
przez aplikację konsolową. Każdy parametr konsoli poleceń zostanie przekazany do metody,
zgodnie z następującą sygnaturą metody:

~~~
[php]
public function run($args) { ... }
~~~

gdzie `$args` wskazuje na dodatkowe parametry podane w linii poleceń.

W konsoli poleceń możemy używać `Yii::app()` w celu dostępu do instancji
aplikacji konsolowej za pomocą której możemy również uzyskać dostęp do połączeń 
bazodanowych (np. `Yii::app()->db`). Można powiedzieć, że użyteczność jest bardzo
podobna do tego, co możemy zrobić w aplikacji sieciowej.

> Info|Info: Poczynając od wersji 1.1.1, możemy również tworzyć globalne polecenia, 
które są współdzielone przez **wszystkie** aplikacje Yii znajdujące się na tej samej 
maszynie. W tym celu zdefiniuj gobalną zmienną o nazwie `YII_CONSOLE_COMMANDS`, 
wskazującą na istniejący katalog. Możemy umieścić nasze globalne polecenia w tym katalogu.


Akcje poleceń konsolowych
----------------------

> Note|Uwaga: Funkcjonalność poleceń konsolowych został udostępniona od wersji 1.1.5.


Polecenie konsolowe często potrzebuje operować różnymi parametrami linii poleceń, zarówno
wymaganymi jak i opcjonalnymi. Polecenie konsolowe może również dostarczać kilku pod-poleceń
do obsługi różnych podzadań. Czynność może zostać uproszczona poprzez używanie akcji konsoli poleceń.

Akcja poleceń konsolowych jest metodą w klasie polecenia konsolowego.
Nazwa metody musi posiadać format `actionXyz`, gdzie `Xyz` określa nazwę akcji, 
której pierwsza litera nazwy zapisana jest za pomocą dużej litery. Na przykład,
metoda `actionIndex` określa akcję o nazwie `index`.

W celu wywołania określonej akcji, używamy następującego formatu dla polecenia konsolowego

~~~
yiic <nazwa-polecenia> <nazwa-akcji> --opcja1=wartość --opcja2=wartość2 ...
~~~

Dodatkowe pary opcja-wartość zostaną przekazane jako nazwane parametry do metody akcji.
Wasrtośc opcji `xyz` zostanie prezkazana do parametru `$xyz` metody akcji. 
Na przykład, jeśli zdefiniujemy następująco klasę poleceń:

~~~
[php]
class SitemapCommand extends CConsoleCommand
{
    public function actionIndex($type, $limit=5) { ... }
    public function actionInit() { ... }
}
~~~

W następstwie, wynikiem wszystkich kolejnych poleceń konsolowych będzie	wywołanie `actionIndex('News', 5)`:

~~~
yiic sitemap index --type=News --limit=5

// $limit przyjmuje wartość domyślną
yiic sitemap index --type=News

// $limit  przyjmuje wartość domyślną
// ponieważ 'index' jest akcją domyślną, możemy pominąć nazwę akcji 
yiic sitemap --type=News

// kolejność opcji nie ma znaczenia 
yiic sitemap index --limit=5 --type=News
~~~

Jeśli opcja podana została bez wartości (np. `--type` zamiast `--type=News`), 
to odpowiadająca mu wartość parametru akcji zostanie przyjęta jako `true`.

> Note|Uwaga: Nie wspieramy alternatywnego formatpu opcji, takich jak:
> `--type News`, `-t News`.

Parametr może przyjmować wartości tablicowe jeśli zadeklarujemy go poprzez podpowiadanie typów:

~~~
[php]
public function actionIndex(array $types) { ... }
~~~

Aby wypełnić tablicę wartościami, po prostu powtarzamy tę samą opcje kilka razy w linii poleceń:

~~~
yiic sitemap index --types=News --types=Article
~~~

Powyższe polecenie wywoła ostatecznie `actionIndex(array('News', 'Article'))`.


Poczynając od wersji 1.1.6, Yii wspiera używanie anonimowych parametrów akcji oraz opcji globalnych.

Anonimowe parametry odnoszą się do tych parametrów linii poleceń, które nie są podawane w formie opcji.
Na przykład, w poleceniu `yiic sitemap index --limit=5 News`, mamy anonimowy parametr, którego wartością
jest `News`, gdy zaś nazwany parametr `limit` przyjmuje wartość 5.

Aby móc używać anonimowych parametrów, akcja polecenia musi deklarować parametr o nazwie `$args`. Na przykład:

~~~
[php]
public function actionIndex($limit=10, $args=array()) {...}
~~~

Tablica `$args` będzie przechowywać wszystkie wartości dostępnych anonimowych parametrów.

Globalne opcje odnoszą się do tych opcji linii polecań, które są dzielone przez wszystkie akcje w poleceniu.
Na przykład, w poceneiu, które dostarcza kilka akcji, możemy chcieć aby każda akcja rozpoznawała
opcje nazwaną `verbose` (z ang. gadatliwy, wielomówny). Chociaż możemy zadeklarować parametr `$verbose` 
w każdej metodzie akcji, lepszym sposobem jest zadelarować ją jako **publiczną zmienną** klasy polecenia, 
która zamieni `verbose` w globalną opcję:

~~~
[php]
class SitemapCommand extends CConsoleCommand
{
	public $verbose=false;
	public function actionIndex($type) {...}
}
~~~

Powyższy kod pozwoli wykonać nam polecenie z opcją `verbose`:

~~~
yiic sitemap index --verbose=1 --type=News
~~~


Dostosowywanie aplikacji konsolowej
--------------------------------

Jeśli aplikacja została utworzona za pomocą narzędzia `yiic webapp`, domyślnie, 
konfiguracja aplikacji konsolowej będzie się znajdować w `protected/config/console.php`. 
Tak jak i plik konfiguracyjny aplikacji sieciowej, plik ten jest skryptem PHP, który zwraca
tablicę reprezentującą inicjalne wartości właściwości instancji aplikacji konsolowej.
W rezultacie, każda publiczna właściwość [CConsoleApplication] może zostać skonfigurowana w tym pliku.

Ponieważ polecenia konsoli często służą aplikacji sieciowej, potrzebuję dostępu do używanych 
przez nią zasobów (takich jak połączenie z bazą danych). Możemy go zagwarantować w następujący sposób
poprzez plik konfiguracji aplikacji:

~~~
[php]
return array(
	......
	'components'=>array(
		'db'=>array(
			......
		),
	),
);
~~~

Jak widzimy, format konfiguracji jest bardzo podobny do tego z aplikacji sieciowej. Dzieje się tak
gdyż zarówno [CConsoleApplication] jak i [CWebApplication] dziedziczą po tej samej klasie bazowej.


<div class="revision">$Id: topics.console.txt 2867 2011-01-15 10:22:03Z haertl.mike $</div>