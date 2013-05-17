Aplikacja
===========

Obiekt aplikacji obudowuje kontekst wywołania, w którym przetwarzane jest żądanie.
Jego głównym zadaniem jest zbieranie pewnych podstawowych informacji o żądaniu
i wysyłania ich do odpowiedniego kontrolera w celu późniejszego ich przetworzenia.

Służy on również jako główne miejsce przechowujące ustawienia konfiguracji na poziomie aplikacji. 
Z tego powodu, obiekt aplikacji jest również nazywany `front-controller'em`.

Obiekt aplikacji jest tworzony jako singleton przez [skrypt wejściowy](/doc/guide/basics.entry).
Jest on dostępny w każdym miejscu poprzez wywołanie [Yii::app()|YiiBase::app].


Konfiguracja aplikacji
-------------------------

Domyślnie, obiekt aplikacji jest instancją klasy [CWebApplication]. Aby dostosować go
do własnych potrzeb, zazwyczaj dostarczamy mu plik ustawień konfiguracyjnych (lub tablicę) 
podczas tworzenia jego instancji w celu zainicjalizowania wartości jego właściwości.
Alternatywnym sposobem dostosowywania go do własnych potrzeb jest rozszerzenie
klasy [CWebApplication].

Konfigurację stanowi tablica par klucz-wartość. Każdy klucz reprezentuje nazwę właściwości
instancji aplikacji a każda odpowiadająca jej wartość reprezentuje odpowiadającą tej właściwości wartość początkową. 
Na przykład, następująca tablica konfiguracyjna, ustawia właściwości [name|CApplication::name] oraz
[defaultController|CWebApplication::defaultController] aplikacji.

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

Zazwyczaj przechowujemy konfigurację w osobnym skrypcie PHP (np. `protected/config/main.php`). 
Wewnątrz skryptu zwracamy tablicę konfiguracyjną w następujący sposób:

~~~
[php]
return array(...);
~~~

Aby zastosować konfigurację, przekazujemy nazwę pliku konfiguracyjnego jako parametr
do konstruktora aplikacji lub do metody [Yii::createWebApplication()] w następujący
sposób, zazwyczaj w [skrypcie wejściowym](/doc/guide/basics.entry):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|Wskazówka: Jeśli konfiguracja aplikacji jest bardzo złożona, możemy podzielić ją 
na kilka plików, każdy będzie zwracał wtedy część tablicy konfiguracyjnej.
Następnie w głównym pliku konfiguracyjnym, możemy wywołać funkcję PHP `include()` w celu 
załadowania pozostałych plików konfiguracyjnych oraz złączenia ich w jedną, kompletną
tablicę konfiguracyjną.


Katalog główny aplikacji (ang. Application Base Directory)
--------------------------

Katalog główny aplikacji to katalog główny, w którym zawierają się wszystkie 
wrażliwe na bezpieczeństwo skrypty PHP oraz dane. Domyślnie, jest to podkatalog 
nazwany `protected`, który znajduje się w katalogu zawierającym skrypt wejściowy.
Można go dostosować do własnych potrzeb poprzez ustawienie właściwości  
[basePath|CWebApplication::basePath] w [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration).

Dostęp do zawartości w katalogu głównym aplikacji powinien być chroniony, tak by 
żaden użytkownik webowy nie miał do niego dostępu. Dla [serwera HTTP Apache](http://httpd.apache.org/)
można to uczynić prosto poprzez umieszczenie pliku `.htaccess` w katalogu głównym. 
Zawartość pliku `.htaccess` powinna być następująca:

~~~
deny from all
~~~

Komponenty aplikacji
---------------------

Funkcjonalności oferowane przez obiekt aplikacji mogą zostać łatwo dostosowane 
oraz wzbogacone poprzez użycie jego elastycznej komponentowej architektury. Obiekt 
zarządza zestawem komponentów aplikacji, każdy z nich implementuje określoną funkcjonalność.
Na przykład, obiekt przetwarza wstępnie żądanie użytkownika przy pomocy komponentów
[CUrlManager] oraz [CHttpRequest].

Poprzez skonfigurowanie właściwości [komponentów|CApplication::components] aplikacji,
możemy dostosować do swoich potrzeb klasy oraz wartości właściwości każdego komponentu
aplikacji używanego w aplikacji. Na przykład, możemy skonfigurować komponent [CMemCache]
w taki sposób, że będzie używał wielu serwerów memcache do buforowania (ang. caching).

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

W powyższym przykładzie, dodaliśmy element `cache` do tablicy komponentów `components`.
Element `cache` mówi, iż klasą komponentu jest `CMemCache` a jej właściwość `servers` 
powinna być w taki i taki sposób zainicjalizowana.

Aby dostać się do komponentu aplikacji używamy `Yii::app()->ComponentID`, gdzie
`ComponentID` odnosi się do ID komponentu (np. `Yii::app()->cache`).

Komponent aplikacji może zostać dezaktywowany poprzez ustawienie `enabled` na false 
w jego konfiguracji. Wartość null jest zwracana, jeśli chcemy uzyskać dostęp do 
dezaktywowanego komponentu.

> Tip|Wskazówka: Domyślnie, komponenty aplikacji tworzone są na żądanie. Oznacza to, że 
komponent aplikacji nie zostanie tak długo utworzony, dopóki nie będzie żądania 
dostępu do niego ze strony użytkownika. W rezultacie, ogólna wydajność nie będzie 
zmniejszona nawet jeśli aplikacja posiada skonfigurowanych wiele komponentów. Część
komponentów aplikacji (np. [CLogRouter]) musi być utworzona niezależnie od tego, czy
żądanie dostępu do nich wystąpiło, czy też nie. Aby to umożliwić, należy wypisać 
ich numery ID we właściwości [preload|CApplication::preload] aplikacji.

Rdzenne komponenty aplikacji (ang. Core Application Components)
---------------------------------------------------------------

Yii predefiniuje zestaw podstawowych komponentów aplikacji aby dostarczyć funkcjonalność 
wspólną dla prawie wszystkich aplikacji webowych. Na przykład, komponent [request|CWebApplication::request] 
jest używany do zbierania informacji o żądaniu użytkownika i dostarczania informacji takich jak 
żądany URL, ciasteczka. Poprzez skonfigurowanie właściwości rdzennych komponentów, 
możemy zmienić domyślne zachowanie Yii prawie w każdym jego aspekcie.

Poniżej znajduje się lista rdzennych komponentów, które są predeklarowane w [CWebApplication].

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] - zarządza publikowaniem 
   prywatnych plików zasobów.

   - [authManager|CWebApplication::authManager]: [CAuthManager] - zarządza bazującą 
   na rolach kontrolą dostępu (RBAC).

   - [cache|CApplication::cache]: [CCache] - dostarcza funkcjonalność umożliwiającą
   buforowanie danych. Zauważ, że musisz określić aktualną klasę (np. [CMemCache], 
   [CDbCache]). W przeciwnym przypadku, wartość null zostanie zwrócona kiedy będziesz 
   próbował uzyskać dostęp do tego komponentu.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] - zarządza 
   skryptami klienta (javascript oraz CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] - dostarcza 
   tłumaczenia rdzennych komunikatów używanych przez framework Yii.

   - [db|CApplication::db]: [CDbConnection] - dostarcza połączenia z bazą danych. 
   Zauważ, że aby móc używać tego komponentu, musisz skonfigurować jego właściwość 
   [connectionString|CDbConnection::connectionString].

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - zarządza niezłapanymi
   błędami oraz wyjątkami PHP.
   
   - [format|CApplication::format]: [CFormatter] - formatuje wartości danych w celu wyświetlenia.

   - [messages|CApplication::messages]: [CPhpMessageSource] - dostarcza przetłumaczone
   komunikaty używane przez aplikacje Yii.

   - [request|CWebApplication::request]: [CHttpRequest] - dostarcza informacji 
   związanych z żądaniem użytkownika.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] - dostarcza
   usługi związane z bezpieczeństwem, takie jak haszowanie i szyfrowanie.

   - [session|CWebApplication::session]: [CHttpSession] - dostarcza funkcjonalność 
   powiązaną z sesją.

   - [statePersister|CApplication::statePersister]: [CStatePersister] - dostarcza mechanizmu
   przechowywania stanów globalnych.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - dostarcza funkcjonalności
   tworzenia i parsowania adresów URL.

   - [user|CWebApplication::user]: [CWebUser] - przechowuje informację związane z tożsamością
   aktualnego użytkownika.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - zarządza tematami.


Cykl życia aplikacji
--------------------

Podczas przetwarzania żądania użytkownika aplikacja podlega następującemu cyklowi 
życia:

   0. Inicjalizacja początkowa aplikacji za pomocą [CApplication::preinit()];

   1. Utworzenie klasy autoloader oraz klasy zarządzania błędami;

   2. Zarejestrowanie rdzennych komponentów aplikacji;

   3. Wczytanie konfiguracji aplikacji;

   4. Zainicjalizowanie aplikacji przy użyciu metody [CApplication::init()]
     - rejestruje zachowania aplikacji;   
	   - załadowanie statycznych komponentów aplikacji;

   5. Zgłoszenie zdarzenia [onBeginRequest|CApplication::onBeginRequest];

   6. Przetwarzanie żądania użytkownika:
	   - zbieranie informacji o żądaniu;
	   - utworzenie kontrolera;
	   - uruchomienie kontrolera;

   7. Zgłoszenie zdarzenia [onEndRequest|CApplication::onEndRequest];

<div class="revision">$Id: basics.application.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>