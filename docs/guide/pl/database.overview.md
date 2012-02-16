Praca z bazami danych
=====================

Yii dostarcza silnego wsparcia dla baz danych. 

Stworzone w oparciu o PDO (PHP Data Objects) 
rozszerzenie Yii Data Access Objects (DAO) umożliwia dostęp do różnych systemów 
zarządzania bazami danych (DBMS) za pomocą jednego ujednoliconego interfejsu. 
Aplikacje stworzone z użyciem Yii DAO można w łatwy sposób zastąpić innym systemem zarządzania 
bazami danych (DBMS) bez potrzeby modyfikowania kodu. 

Konstruktor zapytań Yii daje możliwość budowania zapytań SQL w sposób 
zorientowany obiektowo, bo pozwala zredukować ryzyko ataków typu "SQL injection".

Wzorzec Yii Aktywny Rekord (AR), 
został zaimplementowany przy użyciu powszechnie przyjętego mapowania obiektowo-relacyjnego 
(ORM - Object-Relational Mapping), które upraszcza oprogramowywanie dostępu do baz danych. 
Poprzez reprezentację tabeli jako definicję klasy a wiersza danych jako instancji klasy, 
Yii AR eliminuje powtarzające się zadania, polegające na pisaniu tych zapytań SQL, 
które służą wykonywaniu operacji CRUD (create - tworzenie, read - czytanie, 
update - aktualizacja oraz delete - usuwanie).

Pomimo iż udostępnione w Yii funkcjonalności bazodanowe mogą poradzić sobie niemal z wszystkimi 
zadaniami związanymi z bazami danych, wciąż możesz używać własnych bibliotek do obsługi 
baz danych w twoich aplikacjach napisanych z użyciem Yii. Jest faktem, iż framework Yii
został przemyślanie zaprojektowany, tak by można było używać innych zewnętrznych bibliotek.

<div class="revision">$Id: database.overview.txt 2666 2010-11-17 19:56:48Z qiang.xue $</div>