Konfiguracja testu
==================

Zautomatyzowane testy wykonywanie są wielokrotnie. Aby upewnić się, że proces testowania jest powtarzalny, chcielibyśmy uruchamiać testy w pewnym znanym stanie zwanym *fixture* (konfiguracją testu). Na przykład, aby przetestować funkcjonalność tworzenia postu w aplikacji blogowej, za każdym razem kiedy uruchamiamy testy, tabele zawierające odpowiednie dane o postach (np. tabela postu - `Post`, komentarzy - `Comment`) powinna zostać przywrócona do pewnego stałego stanu. [Dokumentacja PHPUnit](http://www.phpunit.de/manual/current/en/fixtures.html) dobrze opisuje tworzenie ogólnej konfiguracji testu. W tym rozdziale, opiszemy przede wszystkim jak utworzyć konfigurację testu bazodanowego, tak jak w opisanym powyżej przykładzie.

Ustawianie konfiguracji testu bazodanowego jest najprawdopodobniej jedną z najbardziej czasochłonnych czynności w testowaniu aplikacji internegowej korzystającej z bazy danych. Yii dostarcza komponent aplikacji [CDbFixtureManager] aby złagodzić ten problem. Wykonuje on następujące czynności podczas uruchamiana zestawów testów:

 * Przed uruchomieniem wszystkich testów, resetuje wszystkie tabele biorące udział w testach do znanych stanów.
 * Przed uruchomieniem pojedynczej metody testu, resetuje określone tabele biorące udział w teście do znanego stanu.
 * Podczas wykonywania metody testu, umożliwia dostęp do wierszy danych, które mają związek z konfiguracją testu.

W celu użycia manadżera [CDbFixtureManager], konfigurujemy go w [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration) w następujący sposób:

~~~
[php]
return array(
	'components'=>array(
		'fixture'=>array(
			'class'=>'system.test.CDbFixtureManager',
		),
	),
);
~~~

Następnie dostarczamy dane konfiguracji testu w katalogu `protected/tests/fixtures`. Katalog ten można zmienić poprzez skonfigurowanie właściwości [CDbFixtureManager::basePath] w konfiguracji aplikacji. Dane konfiguracji testu są zorganizowane jako kolekcja plików nazywanych plikami konfiguracji testów (ang. fixture files). Każdy plik konfiguracji testy zwraca tablicę reprezentującą inicjalne dane danych dla poszczególnych tabel. Nazwa pliku jest zgodna z nazwą tabeli. Poniżej znajduje się przykład pliku z danymi dla tabeli `Post` zachowanej w pliku `Post.php`:

~~~
[php]
<?php
return array(
	'sample1'=>array(
		'title'=>'test post 1',
		'content'=>'test post content 1',
		'createTime'=>1230952187,
		'authorId'=>1,
	),
	'sample2'=>array(
		'title'=>'test post 2',
		'content'=>'test post content 2',
		'createTime'=>1230952287,
		'authorId'=>1,
	),
);
~~~

Jak możemy zauwazyc, dwa wiersze danych sa zwracane w powyższym kodzie. Każdu wiersz jest reprezentowany jako asocjacyjna tablica, której klucze są nazwami kolumn i których wartości są odpowiadającymi im wartościami kolumn. Dodatkowo, każdy wiersz jest indeksowany poprzez łańcuch znaków (np. `sample1`, `sample2`) nazywany  *aliasem wiersza* (ang. row alias). Później, podczas pisania skryptów testów, możemy wygodnie odwołać się do wiersza przez jego alias. Opiszemy to szczegółowo w następnym rozdziale.

Z pewnością zauważyłeś, że nie określamy wartości kolumny `id` w powyższej konfiguracji testu. Dzieje się tak, ze względu na to, że kolumna `id` określona jest jako samoprzyrastający (ang. auto-incremental) klucz główny, którego wartość będzie wypełniona, jeśli wstawimy nowy wiersz.

Jesli odwołujemy się do menadżera [CDbFixtureManager] po raz pierwszy, przeglądnie on każdy plik konfiguracji testu i użyje go do zresetowania odpowiadającej mu tabeli. Resetuje on tabele poprzez okrojenie tabeli, zresetowanie sekwencji wartości samoprzyrastającego klucza głównego tabeli a następnie poprzez wstawienie wierszy danych pochodzących z pliku do tabeli.

Czasami, nie chcemy zresetować każdej tabeli, która posiada konfigurację testu, zanim nie wykonamy zestawu testów, ze względu na to, że resetowanie zbyt wielu konfiguracji testu zabiera zbyt wiele czasu. W takim przypadku, możemy napisać skrypt PHP, który wykona inicjalizację w niestandardowy sposób. Taki skrypt powinien być zapisay w pliku o nazwie `init.php` w tym samym katalogu, który zawiera pozostałe pliki konfiguracji testów. Gdy menadżer [CDbFixtureManager] wykryje istnienie takiego skryptu, wykona go, zamiast resetować każdą tabelę.

Możliwe też, że nie chcemy domyślnego sposobu resetowania tabeli, np. obcinania i wstawiania do niej danych konfiuracji testu. Jeśli jest tak w naszym przypadku, możemy napisać inicjalizacyjny skrypt, dla określonego pliku konfiguracji testu. Skrypt musi posiadać nazwę zgodną z tabelą zakończoną łańcuchem `.init.php`. Na przykład, inicjalizacyjnym skryptem dla tabeli `Post` będzie `Post.init.php`. Gdy menedżer [CDbFixtureManager] widzi ten skrypt, wykona go zamiast używania domyślnego sposobu resetowania tabeli.

> Tip|Wskazówka: Posiadanie wieli plików konfiguracji testu może zwiększyć drastycznie czas testowania. Z tych powodów, powinieneś jedynie tworzyć pliki konfiguracji testu dla tych tabel, których zawartość może ulec zmianie podczas testu. Tabele, które służy wyłącznie do przeglądania, nie zmieniają się i dlatego nie potrzebują pliku konfiguracji testu.

Następnie dwa rozdziały opiszą jak używać konfiguracji testu zarządzanej przez [CDbFixtureManager] w testach jednostkowych i funkcjonalnych.

<div class="revision">$Id: test.fixture.txt 3039 2011-03-09 19:48:15Z qiang.xue $</div>