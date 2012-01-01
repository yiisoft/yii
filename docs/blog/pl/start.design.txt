Ogólna koncepcja
==============

W oparciu o analizę wymagań, zdecydowaliśmy się używać następującej bazy danych w celu przechowywania trwałych danych dla naszej aplikacji blogowej:

 * Tabela `tbl_user` przechowuje informacje o użytkownikach, włączając w to ich nazwy oraz hasła. 
 * Tabela `tbl_post` przechowuje informacje o postach w blogu. Składa się ona przede wszystkim z następujących kolumn: 
 	 - `title` (tytuł): wymagany, tytuł wiadomości;
	 - `content` (zawartość): wymagana, zawartość treści wiadomości, zapisana w [formacie Markdown](http://daringfireball.net/projects/markdown/syntax);
	 - `status` (status): wymagany, status wiadomości, który może przyjmować następujące wartości:
		 * 1, oznacza iż wiadomość znajduje się w wersji roboczej i nie jest widoczna publicznie;
		 * 2, oznacza iż wiadomość jest upubliczniona;
		 * 3, oznacza iż wiadomość jest nieaktualna i nie jest widoczna na liście wiadomości (choć nadal dostępna)
	 - `tags` (otagowanie): opcjonalne, lista rozdzielonych przecinkami słów, kategoryzujących wiadomość.
 * Tabela `tbl_comment` przechowuje informacje o komentarzach do wiadomości. Każdy komentarz jest powiązany z wiadomością i przede wszystkim zawiera następujące kolumny:
	 - `author` (autor): wymagana, nazwa autora komentarza;
	 - `email` (email): wymagany, e-mail autora komentarza;
	 - `url` (strona WWW): opcjonalna, strona WWW autora komentarza;
	 - `content` (zawartość): wymagana, zawartość komentarza zapisanego w formacie tekstowym.
	 - `status` (status): wymagany, status komentarza determinujący czy komentarz został zatwierdzony (wartość 2) lub nie (wartość 1).
 * Tabela `tbl_tag` zawiera informację o częstotliwości występowania tagów, która jest potrzebna do zaimplementowania chmury tagów. Tabela ta zawiera przede wszystkim następujące kolumny:
 	 - `name` (nazwa): wymagana, unikalna nazwa tagu;
 	 - `frequency` (częstotliwość): wymagana, ilość występowań tagu w wiadomościach;
 * Tabela przeglądowa `tbl_lookup` zawiera ogólne, przeglądowe informacje. Jest to w istocie tabela pomiędzy wartościami liczbowymi a tekstami. Pierwsze są reprezentacją danych w naszym kodzie, drugie zaś odpowiadają tekstom prezentowanym użytkownikowi końcowemu. Na przykład, używamy wartości numerycznej 1 do reprezentowania wersji tymczasowej wiadomości a łańcucha znaków `Draft` w celu wyświetlenia tego statusu użytkownikowi końcowemu. Tabela ta zawiera przede wszystkim następujące kolumny:
 	 - `name` (nazwa): tekstowa reprezentacja pozycji danych, która zostanie wyświetlona użytkownikowi końcowemu; 
 	 - `code` (kod): wartość numeryczna, reprezentująca pozycję danych;
 	 - `type`: typ pozycji danych;
 	 - `position` (pozycja): względna kolejność pozycji danych pośród innych pozycji tego samego typu.	 


Następujący diagram (ER) relacji encji (ang. entity-relation diagram), pokazuje strukturę oraz relacje dla wyżej opisanych tabel.

![Diagram relacji encji dla bazy danych blogu](schema.png)


Wszystkie wyrażenia SQL odpowiadające powyższemu diagramowi ER, można znaleźć w [demonstracyjnym blogu](http://www.yiiframework.com/demos/blog/). Dla naszej instalacji Yii, można je odnaleźć w pliku `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql`.



> Info|Informacja: Nazwaliśmy wszystkie nasze tabele używając małych liter. Kierowaliśmy się tym, ponieważ różne DBMS często w różny sposób traktują wielkość liter a chcieliśmy uniknąć takich kłopotów. 
>
> Poprzedziliśmy wszystkie cztery tabele prefiksem `tbl_`. Służy to dwóm celom. Po pierwsze, prefiks wprowadza przestrzeń nazw do tych tabel w przypadku, gdy muszą one współistnieć z innymi tabelami w tej samej bazie danych, co zdarza się często w współdzielonych środowiskach hostujących, gdzie pojedyncza tabela jest używana przez wiele aplikacji. Po drugie, używanie prefiksów nazw tabel redukuje prawdopodobieństwo posiadania nazwy tabeli, która jest jednocześnie zarezerwowanym słowem kluczowym w DBMS.


Proces tworzenia naszej aplikacji podzieliliśmy na następujące kamienie milowe.

 * 1-szy kamień milowy: tworzenie prototypu sytemu blog, który powinien zawierać większość z wymaganych funkcjonalności.
 * 2-gi kamień milowy: dopracowywanie zarządzania wiadomościami obejmujące tworzenie, listowanie, wyświetlanie, aktualizowanie oraz usuwanie wiadomości.
 * 3-ci kamień milowy: dopracowywanie zarządzania komentarzami obejmujące tworzenie, listowanie, zatwierdzanie, aktualizowanie oraz usuwanie komentarzy do wiadomości.
 * 4-ty kamień milowy: implementacja portletów, zawierających menu użytkownika, logowanie, chmurkę tagów oraz najnowsze komentarze.
 * 5-ty kamień milowy: końcowe dopracowywanie aplikacji oraz jej uruchomienie.

<div class="revision">$Id: start.design.txt 3481 2011-12-13 03:31:15Z jefftulsa $</div>