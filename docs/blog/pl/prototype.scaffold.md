Scaffolding
===========

Tworzenie, czytanie, aktualizowanie oraz usuwanie (CRUD, od ang. create, read, update, delete) są czterema podstawowymi operacjami na obiektach danych  w aplikacji. Ponieważ zadanie implementacji operacji CRUD jest bardzo powszechne podczas tworzenia aplikacji sieciowych, Yii dostarcza kilku narzędzi generujących pod nazwą *Gii*, które pomogą nam zautomatyzować ten proces (zwany również *scaffoldingiem*).

> Note|Uwaga: Gii zostało udostępnione od wersji 1.1.2. Dla wcześniejszych wersji będziesz musiał używać [narzędzia powłoki yiic](http://www.yiiframework.com/doc/guide/quickstart.first-app-yiic) aby osiągnąć ten sam efekt.

W dalszej części opiszemy jak używać tego narzędzia do zaimplementowania operacji CRUD dla postów oraz komentarzy w naszym przykładowym blogu.

Instalowanie Gii
--------------

Na samym początku musimy zainstalować Gii. Otwórz plik `/wwwroot/blog/protected/config/main.php` i dodaj następujący kod:

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
			'password'=>'pick up a password here',
		),
	),
);
~~~

Powyższy kod instaluje moduł o nazwie `gii`, który umożliwia dostęp do modułu Gii poprzez odwiedzenie następującego adresu URL w przeglądarce:

~~~
http://www.example.com/blog/index.php?r=gii
~~~

Zostaniemy tam poproszeni o podanie hasła. Wprowadź hasło które zostało ustawione wcześniej w `/wwwroot/blog/protected/config/main.php`. Powinienieś zobaczyć stronę wyświetlającą wszystkie dostępne narzędzia generujące kod.

> Note|Uwaga: Powyższy kod powinien zostać usunięty gdy aplikacja działa na maszynie produkcyjnej. Narzędzia generowania kodu powinny być używanie jedynie na maszynie deweloperskiej.


Tworzenie modeli
---------------

Najpierw musimy utworzyć klasę [modelu](http://www.yiiframework.com/doc/guide/basics.model) dla każdej z naszych tabel bazodanowych. Klasy modeli pozwolą nam uzyskać dostęp do bazy danych w intuicyjny, obiektowo zorientowany sposób, co zobaczymy w dalszej części tego przewodnika.

Kliknij na linku `Model Generator` aby rozpocząć używanie narzędzia generowania modelu.

Na stronie `Model Generator` wprowadź `tbl_user` (nazwa tabeli z użytkownikami) w polu `Table Name`, prefiks `tbl_` w polu `Table Prefix` a następnie naciśnij przycisk podglądu `Preview`. Ukaże nam się tabelka z podglądem. Możemy kliknąć w link znajdujący się w tej tabelce aby podejrzeć kod, który zostanie wygenerowany. Jeśli wszystko jest w porządku, możemy nacisnąć przycisk generowania `Generate` w celu wygenerowania kodu i zapisania go w pliku.

> Info|Info: Ponieważ generator kodu potrzebuje zapisać wygenerowany kod do plików, wymaga się aby proces sieciowy posiadał pozwolenie na tworzenie i modyfikowanie odpowiednich plików. Upraszczając, możemy nadać procesowi sieciowemu uprawnienie do zapisywania całego katalogu `/wwwroot/blog`. Zauważ, że jest to potrzebne jedynie na maszynie deweloperskiej podczas używania `Gii`.

Powtórz tą samą procedurę dla każdej z pozostałych tabel bazodanowych: `tbl_post`, `tbl_comment`, `tbl_tag` oraz `tbl_lookup`.

> Tip|Wskazówka: Możemy również wprowadzić znak gwiazdki '\*' w polu nazwy tabeli `Table Name`. Spowoduje to wygenerowanie klasy modelu dla *każdej* tabeli za jednym zamachem.

Na tym etapie, będziemy mieli następujące, nowo utworzone pliki:

 * `models/User.php` zawiera klasę użytkownika `User`, która dziedziczy z klasy [CActiveRecord], którą można użyć aby uzyskać dostęp do tabeli bazy danych `tbl_user`;
 * `models/Post.php` zawiera klasę wiadomości `Post`, która dziedziczy z klasy [CActiveRecord], którą można użyć aby uzyskać dostęp do tabeli bazy danych `tbl_post`;
 * `models/Tag.php` zawiera klasę otagowania `Tag`, która dziedziczy z klasy [CActiveRecord], którą można użyć aby uzyskać dostęp do tabeli bazy danych `tbl_tag`;
 * `models/Comment.php` zawiera klasę komentarza `Comment`, która dziedziczy z klasy [CActiveRecord], którą można użyć aby uzyskać dostęp do tabeli bazy danych `tbl_comment`;
 * `models/Lookup.php` zawiera klasę `Lookup`, która dziedziczy z klasy [CActiveRecord], którą można użyć aby uzyskać dostęp do tabeli bazy danych `tbl_lookup`	 
 
 
Implementowanie operacji CRUD
----------------------------

Po utworzeniu klas modeli, możemy używać generatora CRUD `Crud Generator` w celu wygenerowania kodu implementującego operacje CRUD dla tych modeli. Zrobimy to dla modelu wiadomości `Post` oraz komentarza `Comment`.

Na stronie `Crud Generator`, wprowadź `Post` (nazwa klasy modelu wiadomości, którą właśnie utworzyliśmy) w polu klasy modelu `Model Class`, a następnie naciśnij przycisk podglądu `Preview`. Zobaczymy, że tym razem o wiele więcej plików zostanie wygenerowanych. Naciśnij przycisk `Generate` aby je wygenerować.

Powtórz tą samą procedurę dla modelu komentarza `Comment`.

Przyjrzyjmy się plikom wygenerowanym przez generator CRUD. Wszytkie one zostały wygenerowane w katalogu `/wwwroot/blog/protected`. Dla wygody grupujemy je w pliki [kontrolera](http://www.yiiframework.com/doc/guide/basics.controller) oraz pliki [widoku](http://www.yiiframework.com/doc/guide/basics.view):

 - plik kontrolera:
	 * `controllers/PostController.php` zawiera klasę `PostController`, która jest kontrolerem odpowiedzialnym za wszystkie operacje CRUD na wiadomościach;
	 * `controllers/CommentController.php` zawiera klasę `CommentController`, która jest kontrolerem odpowiedzialnym za wszystkie operacje CRUD na komentarzach;

 - plik widoku:
	 * `views/post/create.php` jest plikiem widoku, który reprezentuje formularz HTML do tworzenia nowej wiadomości;
	 * `views/post/update.php` jest plikiem widoku, który reprezentuje formularz HTML do aktualizowania istniejącej wiadomości;
	 * `views/post/view.php` jest plikiem widoku, który wyświetla szczegółowe informacje o wiadomości;
	 * `views/post/index.php` jest plikiem widoku, który wyświetla listę wiadomości;
	 * `views/post/admin.php` jest plikiem widoku, który wyświetla wiadomości w tabelce wraz z poleceniami administracyjnymi;
	 * `views/post/_form.php` jest plikiem częściowym widoku osadzonym w pliku `views/post/create.php` oraz `views/post/update.php`. Wyświetla on formularz HTML służący zbieraniu informacji o poście.
	 * `views/post/_view.php` jest plikiem częściowym widoku używanym w `views/post/index.php`. Wyświetla on skrócony widok pojedynczego postu.
	 * `views/post/_search.php` jest plikiem widoku częściowego używanym przez `views/post/admin.php`. Wyświetla on formularz wyszukiwania.
	 * podobny zestaw plików widoków został również wygenerowany dla komentarza.


Testowanie
-------

Możemy przetestować funkcjonalności zaimplementowane w kodzie, który przed chwilą wygenerowaliśmy korzystając z następujących adresów URL:

~~~
http://www.example.com/blog/index.php?r=post
http://www.example.com/blog/index.php?r=comment
~~~

Zauważ, że funkcjonalności wiadomości i komentarza zaimplementowane w wygenrowanym kodzie są zupełnie od siebie niezależne. Ponadto, podczas tworzenia nowej wiadomości bądź komentarza musimy wprowadzić informacje o autorze `author_id` oraz czasie utworzenia `create_time`, które w prawdziwych aplikacjach powinny być ustawiane programowo. Nie martw się. Poprawimy ten problem w następnych krokach milowych. Na razie, powinniśmy być zadowoleni ze względu na to, iż nasz prototyp zawiera już większość funkcjonalności, które musimy zaimplementować dla naszej aplikacji blogowej.

W celu lepszego zrozumienia jak powyższe pliki są używane, pokażemy przepływ zadań w aplikacji, które występują w naszej aplikacji podczas wyświetlania listy wiadomości:

 0. Użytkownik żąda adresu URL `http://www.example.com/blog/index.php?r=post`; 
 1. [Skrypt wejściowy](http://www.yiiframework.com/doc/guide/basics.entry) jest wykonywany przez serwer, w skrypcie tym tworzona jest i inicjalizowana instancja [aplikacji](http://www.yiiframework.com/doc/guide/basics.application) służąca do obsługi żądań;
 2. Aplikacja tworzy instancję kontrolera `PostController` i wywołuje go;
 3. Instancja `PostController` wykonuje żądaną akcję `index` poprzez wywołanie swojej metody `actionIndex()`. Zauważ, że `index` jest domyślną akcją i użytkownik nie określił akcji do wykonania w adresie URL; 
 4. Metoda `actionIndex()` odpytuje bazę danych, aby zwrócić listę najnowszych wiadomości;
 5. Metoda `actionIndex()` wyświetla widok `index` wypełniony danymi wiadomości.


<div class="revision">$Id: prototype.scaffold.txt 3332 2011-06-28 20:07:38Z alexander.makarow $</div>