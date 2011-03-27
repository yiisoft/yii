Logowanie
=======

Yii dostarcza elastycznej oraz rozszerzalnej funkcjonalności logowania. 
Logowane komunikaty mogą być klasyfikowane w zależności od poziomu logu 
oraz kategorii komunikatu. Używając filtry kategorii oraz poziomów, wybrane komunikaty 
mogą zostać później przekierowane do różnych miejsc przeznaczenia, takich jak pliki, 
maile, okno przeglądarki, itp.


Logowanie komunikatów
---------------------

Komunikaty mogą być logowane poprzez wywołanie zarówno metody [Yii::log] jak i [Yii::trace].
Metody te różnią się tym, że druga loguje komunikat tylko wtedy, gdy aplikacja 
znajduje się w [trybie debugowania](/doc/guide/basics.entry#debug-mode).

~~~
[php]
Yii::log($message, $level, $category);
Yii::trace($message, $category);
~~~

Gdy logujemy komunikaty, musimy określić ich kategorię oraz poziom. 
Kategoria jest ciągiem w formacie `xxx.yyy.zzz` który przypomina 
[alias ścieżki](/doc/guide/basics.namespace). Na przykład, jeśli komunikat jest 
logowany w kontrolerze [CController], możemy użyć kategorii `system.web.CController`.
Poziom komunikatu powinien przyjmować jedną z poniższych wartości:

   - `trace`: jest to poziom używany przez metodę [Yii::trace]. Służy on do śledzenia 
   przebiegu przepływu wywołań w aplikacji podczas jej tworzenia.

   - `info`: służy do logowania ogólnych informacji.

   - `profile`: jest to profil wydajnościowy, który zostanie krótko opisany później.

   - `warning`: dla komunikatów z ostrzeżeniami.

   - `error`: dla komunikatów o błędach krytycznych.

Przekierowywanie komunikatów
----------------------------

Komunikaty rejestrowane przy użyciu metod [Yii::log] oraz [Yii::trace] trzymane są w pamięci.
Zazwyczaj potrzebujemy wyświetlić je w oknie przeglądarki lub też zapisać je w pewnej 
trwałej pamięci danych takiej pliki czy też maile. Nazywamy to *przekierowywaniem komunikatów* 
(ang. message routing), np. wysyłanie komunikatów do różnych miejsc przeznaczenia.
W Yii przekierowywanie komunikatów zarządzane jest przez komponent aplikacji [CLogRouter].
Zarządza on zestawem tak zwanych *dzienników tras*. Każdy dziennik trasy reprezentuje
pojedyncze miejsce przeznaczenia dziennika. Komunikaty wysłane przez dziennik trasy 
mogą zostać przefiltrowane w zależności od swoich poziomów oraz kategorii.

Aby używać przekierowania komunikatów musimy zainstalować i inicjalnie załadować 
komponent aplikacji [CLogRouter]. Musimy również skonfigurować jego właściwość [routes|CLogRouter::routes]
zawierająca pożądane przez nas dzienniki tras. Następujący kod pokazuje przykład 
pożądanej [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration):

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'trace, info',
					'categories'=>'system.*',
				),
				array(
					'class'=>'CEmailLogRoute',
					'levels'=>'error, warning',
					'emails'=>'admin@example.com',
				),
			),
		),
	),
)
~~~

W powyższym przykładzie mamy dwa dzienniki tras. Pierwsza trasa to klasa [CFileLogRoute]
która zapisuje komunikaty w pliku w katalogu uruchomieniowym aplikacji. Tylko komunikaty,
których poziom to `trace` lub `info` oraz te, których kategoria rozpoczyna się od ciągu `system.`
są zapisywane. Druga to klasa [CEmailLogRoute], która wysyła komunikaty pod określony adres mailowy.
Wyłącznie komunikaty, których poziom to `error` lub `warning` są wysyłane.

Następujące dzienniki tras dostępne są w Yii:

   - [CDbLogRoute]: zapisuje komunikaty w tabeli bazy danych.
   - [CEmailLogRoute]: wysyła komunikaty pod podany adres mailowy.
   - [CFileLogRoute]: zapisuje komunikaty w pliku w katalogu uruchomieniowym aplikacji. 
   - [CWebLogRoute]: wyświetla komunikaty na końcu aktualnej strony internetowej.
   - [CProfileLogRoute]: wyświetla profilowane komunikaty na końcu aktualnej strony internetowej.

> Info|Info: Przekierowanie komunikatów odbywa się na końcu aktualnego cyklu żądania
kiedy przywoływane jest zdarzenie [onEndRequest|CApplication::onEndRequest]. 
Aby wyraźnie zakończyć przetwarzanie aktualnego żądania wywołaj metodę [CApplication::end()]
zamiast używać funkcji `die()` lub też `exit()`, dlatego że [CApplication::end()]
wywoła zdarzenie [onEndRequest|CApplication::onEndRequest] dzięki czemu komunikaty będą mogły
zostać poprawnie zarejestrowane.

Filtrowanie komunikatów
-----------------------

Jak już wspominaliśmy komunikaty mogą być filtrowane w zależności od ich poziomów oraz
kategorii zanim zostaną wysłane go dziennika trasy. Dzieje się to poprzez ustawienie
właściwości [levels|CLogRoute::levels] oraz [categories|CLogRoute::categories]
odpowiedniego dziennika trasy. Większa ilość poziomów oraz kategorii powinna zostać
rozdzielona przecinkami. 

Ponieważ kategorie komunikatów posiadają format `xxx.yyy.zzz`, możemy je traktować jak
hierarchię kategorii. W szczególności mówimy, że `xxx` jest rodzicem `xxx.yyy`, który jest
rodzicem `xxx.yyy.zzz`. Możemy zatem użyć `xxx.*` w celu reprezentowania kategorii `xxx`
oraz jej wszystkich kategorii potomnych i ich kategorii potomnych. 


Logowanie informacji kontekstowych
----------------------------------

Możemy zadeklarować logowanie dodatkowych informacji zależnych  
od kontekstu, takich jak predefiniowane zmienne PHP (np. `$_GET`, `$_SERVER`), ID sesji,  
nazwa użytkownika, itp. Osiągamy to poprzez ustawienie odpowiedniego filtru logowania
we właściwości [CLogRoute::filter] trasy logowania.

Framework dostarcza wygodnego filtru [CLogFilter], który może zostać użyty, w większości przypadków,
jako wymagany filtr logu. Domyślnie [CLogFilter] będzie logował komunikaty zawierające zmienne  
takie jak `$_GET`, `$_SERVER`, które zazwyczaj posiadają cenne systemowe informacje kontekstowe.
[CLogFilter] może zostać skonfigurowany do dodawania prefiksu do każdego logowanego komunikatu 
w postaci ID sesji, użytkownika, itp. co może bardzo uprościć globalne przeszukiwanie
podczas przeszukiwania ogromnej ilości zarejestrowanych komunikatów. 

Nastepująca konfiguracja pokazuje jak udostępnić logowanie informacji kontekstowych. 
Zauważ, że każda trasa logowania może posiadać swój własny filtr logu. Domyślnie
trasa logowania nie posiada zdefiniowanych żadnych filtrów.

~~~
[php]
array(
  ......
  'preload'=>array('log'),
  'components'=>array(
    ......
    'log'=>array(
      'class'=>'CLogRouter',
      'routes'=>array(
        array(
          'class'=>'CFileLogRoute',
          'levels'=>'error',
          'filter'=>'CLogFilter',
        ),
        ...pozostałe trasy logowania...
      ),
    ),
  ),
)
~~~

Yii wspiera logowanie informacji ze stosu wywołani w komunikatach logowanych poprzez
wywołanie `Yii::trace`. Domyślnie funkcjonalnośc ta jest wyłączona ze względu na zmniejszanie wydajności. Aby móc  
używac tej funkcjonalności, po prostu zdefiniuj stałą nazwaną `YII_TRACE_LEVEL` na początku skryptu wejściowego 
(zanim załączysz `yii.php`) jako wartość całkowita większa niż 0. Yii dołaczy wtedy do każdego śledzonego komunikatu 
informację o nazwie pliku oraz numerze linii dla stosu wywołań należącego do danego fragmentu kodu aplikacji.
Liczba przypisana do `YII_TRACE_LEVEL` określa ile wartstw każdego stosu wywołań powinno zostać zarejestrowanych. 
Opcja ta jest szczególnie przydatna podczas fazy dewelopmentu, ponieważ może pomóc nam zidentyfikować miejsce, które 
wywołuje śledzenie wiadomości. 


Profilowanie wydajności
-----------------------

Profilowanie wydajności jest specjalnym typem logowania rejestrowania komunikatów. 
Profilowanie wydajności może być używane do mierzenia czasu potrzebnego do wykonania  
określonego bloku kodu oraz znalezienia wąskiego gardła wydajności.

Aby używać profilowania wydajności potrzebujemy określić, które bloki kodu powinny 
być profilowane. Zaznaczamy początek oraz koniec każdego bloku kodu poprzez wstawienie następujących metod:

~~~
[php]
Yii::beginProfile('blockID');
...blok kodu, który będzie profilowany...
Yii::endProfile('blockID');
~~~

gdzie `blockID` jest identyfikatorem, który jednoznacznie określa blok kodu.

Zauważ, że bloki kodów muszą być prawidłowo zagnieżdżone. Oznacza to, że 
blok kodu nie może się krzyżować z innym blokiem. Musi on sie znajdować na równoległym
poziomie lub całkowicie zawierać się w innym bloku kodu. 

Aby zobaczyć wynik profilowania potrzebujemy zainstalować komponent aplikacji [CLogRouter]
wraz z dziennikiem trasy [CProfileLogRoute]. Dokładnie tak samo jak to robimy podczas
zwyczajnego przekierowywania komunikatów. Trasa [CProfileLogRoute] wyświetli wyniki mówiące o wydajności 
na końcu aktualnej strony. 


Profilowanie zapytań SQL
------------------------

Profilowanie jest szczególnie użyteczne podczas pracy z bazami danych, ponieważ wywołania SQL
są często główną przyczyną wąskich gardeł w wydajności aplikacji. Chociaż możemy wstawiać 
wyrażenia `beginProfile` oraz `endProfile` w odpowiednich miejscach aby mierzyć czas spędzony na 
wykonywaniu danego zapytania SQL, Yii dostarcza porządniejszego podejścia do rozwiązania tego problemu.

Poprzez ustawienie właściwości [CDbConnection::enableProfiling] na true w konfiguracji aplikacji,
każde wyrażenie SQL, które zostanie wywołane będzie profilowane. Rezultat może zostać łatwo
wyświetlony przy użyciu wyżej wspomnianej klasy [CProfileLogRoute], która potrafi wyświetlić nam jak wiele 
czasu zostało spędzone na wykonywaniu danego zapytania SQL. Możemy również zawołać metodę
[CDbConnection::getStats()] aby otrzymać całkowitą ilość wywołań zapytań SQL oraz ich całkowity czas wykonania.


<div class="revision">$Id: topics.logging.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>