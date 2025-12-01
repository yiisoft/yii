Najlepsze praktyki w MVC
==================

Chociaż MVC (model-widok-kontroler) jest wzorcem znanym prawie przez każdego programistę, sposób jego zastosowania w rzeczywistej aplikacji wciąż umyka wielu ludziom. Główną ideą MVC jest **"możliwość ponownego użycia kodu oraz oddzielenie warstw"** (ang. code reusability and separation of concerns). W tej części poradnika opiszemy kilka ogólnych wskazówek dotyczących tego jak lepiej podążać ideą MVC podczas procesu tworzenia aplikacji.

Aby lepiej wytłumaczyć te wskazówki, załóżmy, że aplikacja zawiera kilka podaplikacji, takich jak:

* front end: strona upubliczniona dla zwykłego użytkownika;
* back end: strona udostępniająca funkcje administracyjne służące zarządzaniu aplikacją. Z reguły z dostępem dla personelu adminstrującego;
* konsola (ang. console): aplikacja składająca się z poleceń konsoli wywoływanych w oknie terminala lub też zaplanowanych zadań wspierających całą aplikację;
* API sieciowe (ang. Web API): dostarcza interfejsów systemom zewnętrznym (ang. third parties) w celu integracji z aplikacją. 

Podaplikacje mogą być zaimplementowane jako [moduły](/doc/guide/basics.module), lub też jako aplikacje Yii, które współdzielą pewien kod z innymi podaplikacjami. 


Model
-----

[Modele](/doc/guide/basics.model) stanowią podstawową strukturę danych aplikacji sieciowej. Modele często są współdzielone pomiędzy różnymi podaplikacjami aplikacji sieciowej. Na przykład, model logowania do systemu `LoginForm` moze być używany zarówno w front endzie jak i back endzie aplikacji; zaś model `News` możeby być używany w konsoli poleceń oraz w front/back endzie aplikacji. Dlatego też, modele

* powinny zawierać właściwości reprezentujace pewne dane;

* powinny zawierać logikę biznesową (np. zasady sprawdzania poprawności) w celu zapewnienia, iż reprezentowane dane spełniają założenia projektowe;

* mogą zawierać kod manipulujący danymi. Na przykład model `SearchForm`, który poza reprezentowaniem danych wejściowych służących do wyszukiwania może zawierać metodę `search` implementującą aktualne wyszukiwanie.

Czasami stosowanie poprzednio przedstawionej zasady może spowodować, że nasz model będzie zbyt obszerny, gdyż będzie zawierał zbyt wiele kodu w jednej klasie. Może to spowodować, że modelem cieżko będzie zarządzać jeśli zawiera on kod służący różnym celom. Na przykład, model `News` może zawierać metodę o nazwie `getDeletedNews`, która jest używana jedynie przez back end. Dla większych aplikacji, następująca strategia może zostać użyta w celu zwiększenia łatwości zarządzania modelami:

* zdefiniuj nową klasę modelu `NewsBase`, która to zawiera kod współdzielony przez różne podaplikacje (np. front end, back end);

* w każdej podaplikacji, zdefiniuj model `News` poprzez rozszerzenie z `NewsBase`. Umieść cały kod właściwy tej podaplikacji w tym modelu.

Zatem jeśli mielibyśmy zastosować tą strategię w naszym przykładzie, dodalibyśmy model `News` zawierający jedynie metodę `getLatestNews` do aplikacji frontendowej oraz dodalibyśmy następny model `News`, zawierający jedynie metodę `getDeletedNews`, do aplikacji backendowej.

Ogólnie rzecz biorąc, modele nie powinny zawierać logiki, która bezpośrednio ma do czynienia z użytkownikiem końcowym. Precyzując, model:

* nie powinien używać zmiennych `$_GET`, `$_POST`, lub innych tym podobnym zmiennych, które są powiązane z żądaniem użytkownika. Zapamiętaj, że model może zostać użyty przez zupełnie inną podaplikację (np. testy jednostkowe, WEB API), które to mogą nie używać tych zmiennych w celu reprezentacji żądania użytkownika. Zmienne te, odnoszące się do żądania użytkownika końcowego powinny być obsługiwane przez kontroler.

* powinien unikać osadzania kodu HTML lub też kodu prezentacji. Ponieważ ten ostatni rożni się w zależności od zapotrzebowań użytkownika końcowego. Lepiej w tym celu używać widoków. 


Widok
----

[Widoki](/doc/guide/basics.view) są odpowiedzialne za reprezentowanie modeli w formacie pożądanym przez użytkowników końcowych. Ogólnie rzecz biorąc widoki:

* powinny zawierać przede wszystkim kod prezentacyjny, taki jak HTML, czy też prosty kod PHP przeglądający, formatujący i wyświetlający dane;

* powinny unikać zawierania kodu, który wywołuje bezpośrednio zapytania bazodanowe. Tego typu kod lepiej jest umieścić w modelach.

* powinny unikać bezpośredniego używania zmiennych `$_GET`, `$_POST` lub innych podobnych, które reprezentują żądanie użytkownika końcowego. Jest to zadanie dla kontrolera. Widok powinien się skupiać na wyświetlaniu i układzie danych dostarczonych do niego przez kontroler i/lub model nie zaś bezpośrednio na próbach dostępu do zmiennych z żądania czy też bazie danych.

* mogą mieć bezpośredni dostęp do właściwości i metod kontrolera oraz modeli. Jednakże, powinien on być wykorzystywany jedynie w celach prezentacyjnych.


Widoki mogą być wielokrotnie używane na różnorakie sposoby:

* układy: wspólne obszary prezentacji (np. nagłówek strony, stopka) mogą zostać umieszczone w widoku układu.

* częściowe widoki: używaj częściowych widoków (widoki które nie są udekorowane przez układy) w celu ponownego wykorzystania kodu prezentacyjnego. Na przykład, w Gii używamy częściowego widoku `_form.php` do wygenerowania formularza do wprowadzania danych zarówno dla stron tworzących jak i aktualizujących model.

* widżety: jeśli do zaprezentowania częściowego widoku potrzeba dużej ilości logiki, widok częściowy może zostać zamieniony w widżet, w którym to plik z jego klasą jest najlepszym miejscem na zawarcie tej logiki. Dla widżetów, które generują wiele znaczników HTML, lepiej jest użyć konkretnych plików widoków dla tego widżetu aby zawrzeć te znaczniki.

* klasy pomocnicze: w widokach często istnieje zapotrzebowanie na pewne fragmenty kodu wykonujące drobne zadania, takie jak formatowanie danych czy też generowanie tagów HTML. Zamiast umieszczać ten kod bezpośrednio w plikach widoku, lepszym podejściem jest umieścić go w klasie pomocniczej widoku. Następnie, wystarczy jedynie użyć tej klasy w widoku. Yii zawiera przykład takiego podejścia. To potężna klasa pomocnicza [CHtml], która potrafi utworzyć powszechnie używany kod HTML. Klasy pomocnicze można umieścić w [automatycznie ładowanych katalogach](/doc/guide/basics.namespace), w taki sposób, że nie będą one wymagały jawnego dołączania w przypadku ich używania.


Kontroler
----------

[Kontrolery](/doc/guide/basics.controller) są klejem łączącym modele, widoki oraz inne komponenty w działającą aplikację. Kontrolery odpowiedzialne są za bezpośrednie zajmowanie się żądaniami użytkownika końcowego. Dlatego też kontrolery:

* mogą posiadać dostęp do `$_GET`, `$_POST` oraz innych zmiennych PHP które reprezentują żądanie użytkownika;

* mogą tworzyć instancje modeli oraz zarządzać ich cyklem życia. Na przykład, w typowym użyciu akcji update (aktualizacji), kontroler może najpierw utworzyć instancję modelu; następnie wypełnić ją danymi pochodzącymi z danych wypełnionych przez użytkownika i przekazanych przez zmienną `$_POST`; na koniec, po prawidłowym zapisaniu modelu, kontroler może przekierować przeglądarkę użytkownika do strony wyświetlającej szczegóły modelu. Zauważ, że aktualna implementacja zapisywania modelu powinna znajdować się w modelu a nie w kontrolerze.

* powinny unikać osadzania instrukcji SQL, które lepiej trzymać w modelu.

* powinny unikać osadzania jakiegokolwiek HTML-u oraz wszystkich pozostałych znaczników służących prezentacji. Lepiej przechowywać je w widokach.


W dobrze zaprojektowanej aplikacji MVC, kontrolery są często bardzo małe, zawierają prawdopodobnie jedynie kilkadziesiąt linijek kodu zaś modele są bardzo duże, gdyż zawierają dużo kodu odpowiedzialnego za reprezentację i manipulację danymi. Dzieje się tak ponieważ struktura i logika biznesowa reprezentowana przez modele jest bardzo typowa dla konkretnej aplikacji i musi być bardzo dostosowana aby sprostać wymaganiom stawianym przez aplikację; ponieważ logika kontrolera często naśladuje podobne wzorce pomiędzy aplikacjami może zostać uproszczona poprzez używanie frameworku czy też klas bazowych.


<div class="revision">$Id: basics.best-practices.txt 2795 2010-12-31 00:22:33Z alexander.makarow $</div>