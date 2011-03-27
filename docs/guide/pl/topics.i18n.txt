Umiędzynaradawianie
===================

Umiędzynaradawianie (I18N) odnosi się do procesu projektowania oprogramowania,
tak aby mogło ono być dostosowane do różnych języków oraz regionów bez potrzeby
zmian w mechanice aplikacji. Dla aplikacji sieciowych ma to szczególne znaczenie
ponieważ użytkownicy mogą pochodzić z całego świata.

Yii dostarcza wsparcia dla I18N w kilku aspektach.  

   - Dostarcza lokalne dane dla każdego możliwego języka i wariantu preferencji.
   - Zawiera usługę tłumaczenia wiadomość oraz plików.
   - Zapewnia zależne od ustawień lokalnych formatowanie daty i czasu.
   - Zapewnia zależne od ustawień lokalnych formatowanie liczb.

W dalszych podrozdziałach zajmiemy się każdym z powyższych aspektów.

Ustawienia lokalne i języki.
---------------------------

Ustawienia lokalne są zestawem parametrów, które określają język użytkownika, 
jego kraj oraz specjalny wariant preferencji, które użytkownik chce zobaczyć 
w interfejsie użytkownika. Najczęściej identyfikacja odbywa się poprzez ID zawierające 
ID języka oraz ID regionu. Na przykład, ID `en_US` oznacza angielskie ustawienia lokalne  
dla USA. Aby zachować spójność wszystkie ID ustawień lokalnych w Yii zostały 
sprowadzone do postaci kanonicznych w formacie  `LanguageID` lub `LanguageID_RegionID`
zapisanych małymi literami (np. `en`, `en_us`).

Dane lokalne są reprezentowane jako instancja klasy [CLocale]. Dostarcza ona zależne od ustawień 
lokalnych informacje, w tym symbole i formaty liczb, symbole i formaty walut, formaty daty i czasu 
oraz nazwy dni i miesięcy. Ponieważ informacja o języku znajduje się 
już w ID ustawień lokalnych nie jest ona dostarczona przez [CLocale]. Z tego samego  
powodu często zamiennie używamy terminów ustawienia lokalne i język.

Mając ID ustawień lokalnych można otrzymać odpowiadającą mu instancję [CLocale] poprzez
`CLocale::getInstance($localeID)` lub `CApplication::getLocale($localeID)`.

> Info|Info: Yii dostarcza dane lokalne dla prawie każdego języka oraz regionu. 
Dane uzyskano ze [wspólnego repozytorium danych lokalnych ](http://unicode.org/cldr/) (CLDR). 
Dla każdego ustawienia lokalnego dostarczany jest wyłącznie zbiór danych pochodzących z CLDR,
gdyż w dane oryginalne zawierają wiele rzadko używanych informacji.
Użytkownicy mogą również dostarczać swoje własne, niestandardowe
dane lokalne. Aby to zrobić, ustaw właściwość [CApplication::localeDataPath]
by wskazywała na katalog, który zawiera niestandardowe dane lokalne. Aby stworzyć
pliki z niestandardowymi danymi lokalnymi, wzoruj się na zawartości plików lokalnych 
znajdujących się w katalogu `framework/i18n/data`.

W aplikacji Yii rozróżniamy język [docelowy|CApplication::language] od [źródłowego|CApplication::sourceLanguage]. 
Język docelowy jest językiem (ustawieniem lokalnym) użytkownika do którego skierowana jest aplikacja, 
zaś język źródłowy odnosi się do języka (ustawień lokalnych) w którym pliki źródłowe aplikacji zostały
napisane. Umiędzynaradawianie występuje tylko wtedy, gdy te dwa języki są różne.

Można skonfigurować [język docelowy|CApplication::language] w [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration)
lub zmienić go dynamicznie zanim nastąpi jakiekolwiek umiędzynaradawianie.

> Tip|Wskazówka: Czasami, możemy chcieć ustawić język docelowy jako język preferowany przez użytkownika 
(zdefiniowany w ustawieniach przeglądarki użytkownika). Aby tak zrobić, możemy pobrać 
preferowane przez użytkownika ID języka używając [CHttpRequest::preferredLanguage].

Tłumaczenie
-----------

Najbardziej pożądaną funkcjonalnością I18N jest tłumaczenie, włączając w to tłumaczenie 
komunikatów oraz tłumaczenie widoków. Pierwsze polega na tłumaczeniu treści komunikatu na pożądany 
język, drugie zaś na tłumaczeniu całego plik na dany język. 

Żądanie tłumaczenia zawiera obiekt, który ma zostać przetłumaczony, język źródłowy 
obiektu oraz język docelowy na który obiekt powinien zostać przetłumaczony. 
W Yii domyślnym językiem źródłowym jest [język źródłowy aplikacji|CApplication::sourceLanguage] 
zaś domyślnym językiem docelowym jest [język aplikacji|CApplication::language]. Jeśli język źródłowy oraz docelowy są 
takie same, tłumaczenie nie zachodzi.

### Tłumaczenie komunikatów

Tłumaczenie komunikatów odbywa się poprzez wywołanie metody [Yii::t()|YiiBase::t]. 
Metoda ta tłumaczy podany komunikat z [języka źródłowego|CApplication::sourceLanguage] na 
[język docelowy|CApplication::language].

Podczas tłumaczenia komunikatu powinna zostać określona jego kategoria, ponieważ
komunikat może zostać różnie przetłumaczony w zależności od różnych kategorii (kontekstów). 
Kategoria `yii` jest zarezerwowana dla komunikatów używanych przez kod źródłowy frameworku.

Komunikaty mogą posiadać symbol zastępczy (placeholder), który będzie zastąpiony przez
aktulną wartość parametru podczas wywoływania metody [Yii::t()|YiiBase::t]. Na przykład
następujące żądanie tłumaczenia komunikatu zastąpi symbol zastępczy `{alias}` 
w oryginalnym komunikacie aktualną wartością zmiennej alias.

~~~
[php]
Yii::t('app', 'Path alias "{alias}" is redefined.',
	array('{alias}'=>$alias))
~~~

> Note|Uwaga: Aby móc przetłumaczyć komunikat musi on być stałym łańcuchem znaków. 
Nie powinien on zawierać zmiennych, które mogą zmienić zawartość wiadomości 
(np. `"Invalid {$message} content."`). Należy używać symbolów zastępczych jeśli komunikat 
musi się zmieniać w zależności od parametrów.

Przetłumaczone komunikaty znajdują się w repozytorium nazywanym *źródłem komunikatów*.
Źródło komunikatów reprezentowane jest poprzez instancję klasy [CMessageSource] lub 
jej klas pochodnych. Podczas wywołania metody [Yii::t()|YiiBase::t], szuka ona komunikatu 
w źródle komunikatów i zwraca jego przetłumaczoną wersję jeśli taką znajdzie.

Yii dostarcza następujących typów źródeł komunikatów. Możesz również rozszerzyć 
klasę [CMessageSource] aby utworzyć swoje własne źródło komunikatów.

   - [CPhpMessageSource]: tłumaczenia komunikatów przechowywane są jako pary 
   klucz-wartość w tablicy PHP. Oryginalny komunikat jest kluczem a przetłumaczony wartością.
   Każda tablica reprezentuje tłumaczenie dla konkretnej kategorii komunikatów i przechowywana
   jest w oddzielnym pliku skryptu PHP, którego nazwa jest nazwą kategorii.
   Pliki z tłumaczeniami PHP dla tych samych języków przechowywane są w tym  
   samym folderze o nazwie takiej jak ID ustawień lokalnych. Katalogi te
   znajdują się w katalogu wskazanym przez zmienną [bazePath|CPhpMessageSource::basePath].

   - [CGettextMessageSource]: tłumaczenia komunikatów przechowywane są jako pliki w formacie [GNU
Gettext](http://www.gnu.org/software/gettext/).

   - [CDbMessageSource]: tłumaczenia komunikatów przechowywane są w tabeli bazy danych.
   Aby uzyskać więcej szczegółów spójrz w dokumentację API dla [CDbMessageSource].

Źródło komunikatów jest ładowane jako [komponent aplikacji](/doc/guide/basics.application#application-component).
Yii predefiniuje komponent o nazwie [messages|CApplication::messages] w celu przechowywania
komunikatów, które będą używane w aplikacji. Domyślnym typem źródła komunikatów jest 
[CPhpMessageSource] zaś ścieżka bazowa gdzie przechowywane są pliki z tłumaczeniami
to `protected/messages`.


Podsumowując, w celu korzystania z tłumaczenia komunikatów, wymagane są nastepujące kroki:

   1. Wywołanie w opdowiednim miejscu [Yii::t()|YiiBase::t];

   2. Utworzenie pliku tłumaczenia PHP wg wzorca `protected/messages/IDUstawieńLokalnych/NazwaKategorii.php`.
   Każdy z plików powinien zwracać po prostu tablicę tłumaczeń komunikatów. Zauważ, że 
   założyliśmy iż używamy domyślnej klasy [CPhpMessageSource] w celu przechowywania
   przetłumaczonych komunikatów.

   3. Skonfiguruj właściwości [CApplication::sourceLanguage] oraz [CApplication::language].

> Tip|Wskazówka: Narzędzie `yiic` może zostać zarządzać tłumaczeniami komunikatów
jeśli używamy [CPhpMessageSource] jako źródło komunikatów. Jego polecenie `message` 
może automatycznie wydobyć komunikaty, które powinny zostać przetłumaczone z wybranego pliku źródłowego
oraz jeśli jest to konieczne dołączyć je do istniejących tłumaczeń. Aby uzyskać więcej szczegółów 
dotyczących komendy `message`, uruchom polecenie `yiic help message`.

Używając klasy [CPhpMessageSource] do zarządzania źródłem 
komunikatów, możemy również, w specjalny sposób, zarządzać i używać komunikaty dla klas rozszerzeń 
(np. klas widżetów, modułów). W szczególności, jeśli komunikat należy do rozszerzenia, którego nazwa 
to `Xyz`, wtedy kategoria komunikatu może zostać zapisana w formacie `Xyz.categoryName`. 
Przyjmuje się, że odpowiadający komunikatowi plik to `BasePath/messages/IDjęzyja/categoryName.php`, 
gdzie `BasePath` wskazuje na katalog, który zawiera plik klasy rozszerzenia. 
Podczas używania metody `Yii::t()` do tłumaczenia komunikatów z rozrszerzeń, powinniśmy 
używać następującego formatu:

~~~
[php]
Yii::t('Xyz.categoryName', 'wiadomość do przetłumaczenia')
~~~

Yii posiada wsparcie dla [formatów alternatywnych|CChoiceFormat]. 
Alternatywny format odnosi się do wybierania tłumaczenia w zależności od podanej wartości numerycznej.
Na przykład w języku angielskim słowo 'book' oznaczające książkę może przyjmować formę liczby pojedynczej 
lub też formę liczby mnogiej, w zależności od ilości książek, gdy zaś w innych językach, 
słowo to może nie posiadać różnych form (tak jak w języku chińskim) albo też może mieć dużo 
bardziej skomplikowaną liczbę mnogą (tak jak w rosyjskim). Format alternatywny rozwiązuje
ten problem w prosty ale skuteczny sposób. Aby móc używać formatu alternatywnego, przetłumaczony
komunikat musi posiadać sekwencję par wyrażenie-komunikat rozdzielonych znakiem `|`,
tak jak pokazano poniżej:

~~~
[php]
'expr1#message1|expr2#message2|expr3#message3'
~~~

gdzie `exprN` odnosi się do poprawnego wyrażenia PHP, którego wynik zwraca wartość typu boolean,
która determinuje czy odpowiedni komunikat powinien zostać zwrócony. Jedynie jeden komunikat odpowiadający 
pierwszemu wyrażeniu, którego ewaluacja zakończyła się wynikiem true, 
zostanie zwrócony. Wyrażenie może zawierać specjalną zmienną o nazwie `n` (zauważ, że nie jest to `$n`),
która przejmie wartość liczby przekazaną jako pierwszy parametr komunikatu. Na przykład,
zakładając, ze tłumaczona wiadomość to: 

~~~
[php]
'n==1#one book|n>1#many books'
~~~

i że przekazujemy wartość liczbową 2 w w tablicy parametrów komunikatu podczas wywoływania
metody [Yii::t()|YiiBase::t], otrzymamy `many books` jako końcowy wynik tłumaczenia komunikatu:

~~~
[php]
Yii::t('app', 'n==1#one book|n>1#many books', array(1)));
//lub od wersji 1.1.6
Yii::t('app', 'n==1#one book|n>1#many books', 1));
~~~


W skróconej notacji, jeśli wyrażenie jest liczbą, będzie ono potraktowane jako `n==Liczba`.
Dlatego, powyższej tłumaczony komunikat, może również być zapisany jako:

~~~
[php]
'1#one book|n>1#many books'
~~~
	
### Format liczby mnogiej

Poczynając od wersji 1.1.6 alternatywny format liczby mnogiej bazujący na CLDR może być
używany z prostszą składnią. Jest to bardzo wygodne w przypadku języków
posiadająych złożone formy liczby mnogiej.


Reguła dla języka angielskiego dla powyższej liczby mnogiej jest zapisana w następujący sposób:

~~~
[php]
Yii::t('test', 'cucumber|cucumbers', 1);
Yii::t('test', 'cucumber|cucumbers', 2);
Yii::t('test', 'cucumber|cucumbers', 0);
~~~

Powyższy kod zwróci:

~~~
cucumber
cucumbers
cucumbers
~~~

Jeśli chcesz wstawić numer, możesz użyć następującego kodu:

~~~
[php]
echo Yii::t('test', '{n} cucumber|{n} cucumbers', 1);
~~~

Gdzie `{n}` jest specjalnym symbolem zastępczym przechowującym numer. Powyższy kod wydrukuje text `1 cucumber`.

Możesz również dodawać dodatkowe parametry:

~~~
[php]
Yii::t('test', '{username} has a cucumber|{username} has {n} cucumbers',
array(5, '{username}' => 'samdark'));
~~~

a nawet zastępować parametry numeryczne czymś innymi wartościami:

~~~
[php]
function convertNumber($number)
{
	// konwertuje numer do słowa
	return $number;
}

Yii::t('test', '{n} cucumber|{n} cucumbers',
array(5, '{n}' => convertNumber(5)));
~~~

Dla porównania w języku rosyjskim będzie to:
~~~
[php]
Yii::t('app', '{n} cucumber|{n} cucumbers', 62);
Yii::t('app', '{n} cucumber|{n} cucumbers', 1.5);
Yii::t('app', '{n} cucumber|{n} cucumbers', 1);
Yii::t('app', '{n} cucumber|{n} cucumbers', 7);
~~~

z przetłumaczonym komunikatem

~~~
[php]
'{n} cucumber|{n} cucumbers' => '{n} огурец|{n} огурца|{n} огурцов|{n} огурца',
~~~

co w rezultacie da nam

~~~
62 огурца
1.5 огурца
1 огурец
7 огурцов
~~~


> Info|Info: aby dowiedzieć się jak wiele wartości możesz wprowadzić oraz w jakiej kolejności
 powinny się one znajdować zajrzyj na stronę [reguł liczy mnogiej dla różnych języków](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html).

### Tłumaczenie pliku

Tłumaczenie plików dokonuje się poprzez wywołanie metody [CApplication::findLocalizedFile()].
Podając ścieżkę do pliku, który ma zostać przetłumaczony, metoda będzie szukała pliku o tej samej nazwie
w podkatalogu `LokalneId`. Jeśli znajdzie plik ścieżka do tego pliku zostanie zwrócona;
w przeciwnym przypadku zwrócona zostanie oryginalna ścieżka.

Tłumaczenie plików jest głównie używane podczas generowania widoku. Podczas wywoływania
którejś z metod generujących w kontrolerze albo w widżecie, plik zostanie przetłumaczony 
automatycznie. Na przykład, jeśli [językiem docelowym|CApplication::language] jest `zh_cn` 
a [językiem źródłowym|CApplication::sourceLanguage] jest `en_us`, generowanie widoku o nazwie
`edit` zakończy się poszukiwaniem następującego pliku widoku 
`protected/views/ControllerID/zh_cn/edit.php`. Jeśli plik zostanie znaleziony, to ta przetłumaczona 
wersja będzie używana do generowania widoku; w przeciwnym przypadku do generowania zostanie użyty plik
`protected/views/ControllerID/edit.php`.


Tłumaczenie plików może również być używane dla innych celów na przykład, do wyświetlania
przetłumaczonych obrazków czy też plików z danymi zależnych od ustawień lokalnych.

Formatowanie daty i czasu
------------------------

Data i czas posiadają często różne formaty w poszczególnych państwach czy też regionach.
Dlatego też zadanie formatowania daty i czasu polega na generowaniu ciągu reprezentującego
datę lub czas, który zgadza się z tym określonym w ustawienia lokalnych. Yii dostarcza
w tym celu klasę [CDateFormatter].

Każda instancja [CDateFormatter] jest powiązana z docelowymi ustawieniami lokalnymi. 
Aby uzyskać klasę formatującą powiązaną z docelowymi ustawieniami lokalnymi aplikacji 
możemy po prostu skorzystać z właściwości aplikacji [dateFormatter|CApplication::dateFormatter].

Klasa [CDateFormatter] dostarcza przede wszystkim dwóch metod do formatowania znacznika czasu (ang. timestamp) UNIX.

   - [format|CDateFormatter::format]: metoda ta formatuje podany znacznik czasowy UNIX 
   do łańcucha odpowiadającego spersonalizowanemu wzorcowi (np.`$dateFormatter->format('yyyy-MM-dd',$timestamp)`).

   - [formatDateTime|CDateFormatter::formatDateTime]: metoda ta formatuje dany znacznik czasu UNIX 
   do łańcucha odpowiadającemu wzorcowi predefiniowanemu w danych docelowych ustawień lokalnych 
   (np. `short` krótki format daty, `long` długi format czasu).

Formatowanie liczb
-----------------

Tak jak data i czas, liczby również mogą być formatowanie różnie w zależności od kraju 
czy też regionu. Formatowanie liczb obejmuje formatowanie dziesiętne, formatowanie walut 
oraz formatowanie procentów. Dla tych zadań Yii dostarcza klasy [CNumberFormatter].

Aby uzyskać klasę formatowania powiązaną z docelowymi ustawieniami lokalnymi aplikacji, 
możemy skorzystać z właściwości [numberFormatter|CApplication::numberFormatter] danej aplikacji.

Następujące metody są dostarczane przez klasę [CNumberFormatter] aby sformatować wartości całkowite
lub zmiennoprzeciwkową.  

   - [format|CNumberFormatter::format]: metoda ta formatuje podany numer do łańcucha uwzględniając 
   spersonalizowany wzorzec (np. `$numberFormatter->format('#,##0.00',$number)`).

   - [formatDecimal|CNumberFormatter::formatDecimal]: metoda ta formatuje podany numer przy użyciu wzorca dziesiętnego 
   z danych docelowych ustawień lokalnych.

   - [formatCurrency|CNumberFormatter::formatCurrency]: metoda ta formatuje podaną liczbę oraz kod waluty 
   używając wzorca walutowego predefiniowanego w danych docelowych ustawień lokalnych.

   - [formatPercentage|CNumberFormatter::formatPercentage]: metoda ta formatuje podaną liczbę używając 
   wzorca procentowego predefiniowanego w danych docelowych ustawień lokalnych.

<div class="revision">$Id: topics.i18n.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>