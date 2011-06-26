Tworzenie pierwszej aplikacji w Yii
===================================

W tym rozdziale pokażemy jak utworzyć Twoją pierwszą aplikacje, po to, abyś nabył 
niezbędnego doświadczenia w pracy z Yii. Aby utworzyć nową aplikację Yii 
użyjemy `yiic` (narzędzia linii poleceń) oraz `Gii` (potężnego, przeglądarkowego generatora kodu)
pozwalających na automatyczne generowanie kodu dla określonych zadań. Dla wygody przyjmijmy, że
`YiiRoot` jest katalogiem, w którym zainstalowano Yii, a `WebRoot` to główne miejsce na naszym serwerze.

Uruchom `yiic` z wiersza poleceń w następujący sposób:

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Uwaga: uruchamiając `yiic` na Mac OS, Linuksie lub Uniksie być może będziesz
> musiał zmienić uprawnienia pliku `yiic` tak, aby stał się on wykonywalnym.
> Alternatywnie możesz użyć tego narzędzia następująco:
>
> ~~~
> % cd WebRoot
> % php YiiRoot/framework/yiic.php webapp testdrive
> ~~~

Powyższe instrukcje utworzą szkielet aplikacji Yii w katalogu
`WebRoot/testdrive`, który posiada strukturę katalogów wymaganą przez 
większość aplikacji Yii.

Bez pisania nawet pojedynczej linijki kodu, możemy przetestować naszą pierwszą
aplikację Yii poprzez wpisanie następującego adresu URL w przeglądarce:

~~~
http://hostname/testdrive/index.php
~~~

Jak widać aplikacja składa się z czterech stron: strony domowej, o nas, kontaktowej
i logowania. Strona kontaktowa zawiera formularz kontaktowy, poprzez który użytkownik 
może wysłać swoje zapytanie do webmastera. Strona logowania pozwala zaś użytkownikowi 
na uwierzytelnienie, poprzedzającą dostęp do uprzywilejowanych zasobów.
Poniższe zrzuty ekranów prezentują więcej szczegółów.

![Strona domowa](first-app1.png)

![Strona kontaktowa](first-app2.png)

![Strona kontaktowa wyświetlająca błędy dla pól wejściowych](first-app3.png)

![Strona kontaktowa z potwierdzeniem wysłania formularza kontaktowego](first-app4.png)

![Strona logowania](first-app5.png)


Poniższy diagram prezentuje strukturę naszej aplikacji.
Aby dowiedzieć się więcej zajrzyj do działu [konwencje](/doc/guide/basics.convention#directory).

~~~
testdrive/
   index.php                 skrypt startowy aplikacji internetowej
   index-test.php            plik skryptu startowego dla testów funkcjonalnych   
   assets/                   zawiera opublikowane zasoby
   css/                      zawiera pliki CSS
   images/                   zawiera pliki obrazów
   themes/                   zawiera motywy aplikacji
   protected/                zawiera chronione pliki aplikacji
      yiic                   skrypt yiic dla systemów Linux/Unix
      yiic.bat               skrypt yiic dla systemu Windows
      yiic.php               skrypt PHP dla linii poleceń      
      commands/              zawiera spersonalizowane polecenia 'yiic'
         shell/              zawiera spersonalizowane polecenia 'yiic shell'
      components/            zawiera komponenty wielokrotnego użytku
         Controller.php      klasa bazowa dla wszystkich klas kontrolerów
         UserIdentity.php    klasa 'UserIdentity', służąca uwierzytelnieniu
      config/                zawiera pliki konfiguracyjne
         console.php         konfiguracja aplikacji konsolowej
         main.php            konfiguracja aplikacji webowej
         test.php            konfiguracja dla testów funkcjonalnych         
      controllers/           zawiera pliki klas kontrolerów
         SiteController.php  klasa domyślnego kontrolera
      data/                  zawiera przykładowe bazy danych
         schema.mysql.sql    przykładowy schemat bazy danych dla MySQL
         schema.sqlite.sql   przykładowy schemat bazy danych dla SQLite
         testdrive.db        plik przykładowej bazy danych w SQLite         
      extensions/            zawiera rozszerzenia firm trzecich
      messages/              zawiera przetłumaczone komunikaty
      models/                zawiera pliki klas modeli
         LoginForm.php       model formularza dla akcji logowania 'login'
         ContactForm.php     model formularza kontaktowego dla akcji 'contact'
      runtime/               zawiera tymczasowo generowane pliki
      tests/                 zawiera skrypty testów      
      views/                 zawiera pliki widoku i układu (ang. layout) kontrolera
         layouts/            zawiera pliki układów (ang. layout) dla widoków
            main.php         podstawowy widok dzielony przez wszystkie strony
            column1.php      układ dla stron jednokolumnowych
            column2.php      układ dla stron dwukolumnowych
         site/               zawiera pliki widoków dla kontrolera 'site'
         	pages/           zawiera "statyczne" strony
         	   about.php     widok dla strony "o nas" (ang. about)         
            contact.php      widok dla akcji 'contact'
            error.php        widok dla akcji 'error' (wyświetlającej zewnętrzne błędy)
            index.php        widok dla akcji 'index'
            login.php        widok dla akcji 'login'
~~~

Łączenie się z bazą danych
----------------------

Większość aplikacji webowych wykorzystuje bazy danych. Nasz aplikacja
testowa nie jest tu wyjątkiem. Aby użyć bazy danych musimy
poinformować aplikację jak ma się z nią połączyć. Jest to realizowane przez
plik konfiguracyjny `WebRoot/testdrive/protected/config/main.php` 
przedstawiony poniżej:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/testdrive.db',
		),
	),
	......
);
~~~

Powyższy kod instruuje Yii, że aplikacja powinna łączyć się z bazą danych SQLite
`WebRoot/testdrive/protected/data/testdrive.db` jeśli zajdzie taka potrzeba.
Zauważ, że baza danych SQLite jest już załączona do szkieletu aplikacji, który 
przed chwilą wygenerowaliśmy. Ta baza danych zawiera jedynie tabelę o nazwie `tbl_user`:

~~~
[sql]
CREATE TABLE tbl_user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

Jeśli chcesz używać bazy danych MySQL, powinieneś użyć załączonego pliku schematu MySQL  
`WebRoot/testdrive/protected/data/schema.mysql.sql` aby utworzyć bazę danych.

> Note|Uwaga: Aby używać funkcjonalności bazodanowych Yii, potrzebujemy udostępnić 
rozszerzenie PHP PDO oraz rozszerzenia PDO dla poszczególnych sterowników.
Dla aplikacji testowej, potrzebujemy włączyć oba rozszerzenia `php_pdo` oraz `php_pdo_sqlite`. 


Implementowanie operacji CRUD
-----------------------------

Ta część to czysta zabawa. Chcemy zaimplementować operacje CRUD (od ang. create - tworzenie,
read - odczyt, update - aktualizacji i delete - usuwanie) dla tabeli `tbl_user`, 
którą przed chwilą utworzyliśmy. Operacje te są powszechnie wymagane w typowych
aplikacjach. Zamiast męczyć się pisząc kod możemy skorzystać 
z `Gii` -- potężnego przeglądarkowego generatora kodu.

> Info|Info: Gii dostępne jest poczynając od wersji 1.1.2. Wcześniej mogliśmy używać wspomnianego narzędzia `yiic` w celu osiągnięcia tego samego efektu. Aby uzyskać więcej szczegółów na ten temat, 
przejdź do rozdziału [implementowanie operacji CRUD z Yii](/doc/guide/quickstart.first-app-yiic).


### Konfigurowanie Gii

Aby używać Gii musimy najpierw zmienić plik `WebRoot/testdrive/protected/config/main.php`, który określany mianem [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'tutaj wprowadź hasło',
		),
	),
);
~~~

Teraz możesz odwiedzić adres URL `http://hostname/testdrive/index.php?r=gii`. 
Zostaniemy tam poproszeni o wprowadzenia hasła, które powinno
zgadzać się z tym, które przed chwilą podaliśmy w konfiguracji aplikacji.

### Generowanie modelu użytkownika

Po zalogowaniu się, kliknij na link `Model Generator`. Spowoduje to przeniesienie nas do następującej strony generującej model.

![Generator modeli](gii-model.png)

W polu nazwy tabeli `Table Name`, wprowadź `tbl_user`. W polu klasy modelu `Model Class` wprowadź `User`. Następnie naciśnij przycisk podglądu `Preview`. Spowoduje to ukazanie się nam nowego pliku z kodem, który ma zostać wygenerowany. Teraz naciśnij przycisk generowania `Generate`. Nowy plik o nazwie `User.php` zostanie wygenerowany w katalogu `protected/models`. Wygenerowana w ten sposób klasa modelu `User` pozwoli nam porozumiewać się z tabelą bazy danych `tbl_user` w sposób obiektowy, co zostanie opisane w dalszej części przewodnika, 

### Generowanie kodu CRUD

Po utworzeniu pliku klasy modelu wygenerujemy kod który implementuje operacje CRUD na danych wejściowych użytkownika. Wybieramy,
pokazany poniżej, generator operacji CRUD `Crud Generator` w Gii:

![Generator operacji CRUD](gii-crud.png)

W polu modelu klasy `Model Class` wpisz `User`. W polu identyfikatora kontrolera `Controller ID` wpisz `user` (małymi literami). Następnie naciśnij przycisk podglądu `Preview` a następnie przycisk generowania `Generate`. Zakończyliśmy generowanie kodu CRUD.

### Dostęp do stron CRUD

Nacieszmy się teraz efektami naszej pracy otwierając następujący adres URL:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Spowoduje to wyświetlenie listy użytkowników z tabeli `tbl_user`.

Kliknij przycisk `Create User` znajdujący się na tej stronie. 
Zostaniesz przeniesiony do strony logowania, o ile wcześniej nie zalogowałeś się. 
Po zalogowaniu zobaczymy formularz wprowadzania danych, który pozwoli nam dodać nowego użytkownika.
Wypełnij formularz i kliknij przycisk `Create`. Jeżeli wystąpił jakikolwiek błąd
danych wejściowych, pojawi się zgrabny komunikat informujący o błędzie co ustrzeże nas 
przed zapisaniem niepoprawnych danych. Wracając do listy użytkowników powinniśmy zauważyć
nowo dodanego użytkownika.
	
Aby dodać kolejnych użytkowników powtórz powyższe kroki. Zauważ, że strona z listą
użytkowników podlega automatycznemu podziałowi na strony, jeśli w tabeli istnieje
zbyt wiele użytkowników by wyświetlić ich na jednej stronie.

Jeżeli zalogowaliśmy się jako administrator używając użytkownika/hasła `admin/admin`, 
możemy odwiedzić stronę administrowania użytkownikami pod adresem:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Powyższe wywołanie spowoduje pokazanie się nam wpisów z użytkownikami przy 
użyciu ładnego formatu tabelarycznego. Możemy klikać w nagłówki
komórek, aby uporządkować odpowiadającą mu kolumnę. Możemy klikać przyciski
w każdym wierszu danych w celu wyświetlenia, aktualizacji lub usunięcia 
odpowiadającego im wiersza danych. Możemy przeglądać pozostałe strony. Możemy
również filtrować i wyszukiwać interesujące nas dane.

Wszystkie te praktyczne funkcjonalności nie wymagały od nas napisania choćby
pojedynczej linijki kodu!

![Strona administracji użytkownikami](first-app6.png)

![Strona tworzenia nowego użytkownika](first-app7.png)


<div class="revision">$Id: quickstart.first-app.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>