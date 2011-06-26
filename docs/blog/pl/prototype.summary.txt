Podsumowanie
=======

Osiągnęliśmy pierwszy kamień milowy. Podsumujmy zatem co już zrobiliśmy:

 1. Zidentyfikowaliśmy wymagania, które muszą zostać spełnione;
 2. Zainstalowaliśmy framework Yii;
 3. Utworzyliśmy szkielet aplikacji;
 4. Zaprojektowaliśmy i utworzyliśmy bazę danych dla naszego bloga;
 5. Zmodyfikowaliśmy konfigurację aplikacji poprzez dodanie połączenia z bazą danych;
 6. Wygenerowaliśmy kod, który implementuje podstawowe operacje CRUD zarówno dla wiadomości jak i komentarzy;
 7. Zmodyfikowaliśmy metodę uwierzytelniania tak aby korzystała z tabeli `tbl_user`.

Dla większości nowych projektów najwięcej czasu zostanie poświęconego dla kroku 1 oraz 2 dla tego kamienia milowego.

Chociaż kod wygenerowany przez narzędzie `gii` implementuje funkcjonalnie w pełni operacje CRUD dla tabeli bazy danych, często musi on być modyfikowany w poszczególnych aplikacjach. Z tego powodu w następnych dwóch krokach milowych, praca którą wykonamy skupi się na dostosowywaniu wygenerowanego kodu CRUD dla wiadomości i komentarzy w taki sposób aby spełnić nasze początkowe wymagania.

Ogólnie rzecz biorąc, najpierw modyfikujemy klasę [modelu](http://www.yiiframework.com/doc/guide/basics.model) poprzez dodawanie odpowiednich reguł [sprawdzania poprawności](http://www.yiiframework.com/doc/guide/form.model#declaring-validation-rules) i deklarowania [obiektów relacyjnych](http://www.yiiframework.com/doc/guide/database.arr#declaring-relationship). Następnie modyfikujemy [akcje kontrolera](http://www.yiiframework.com/doc/guide/basics.controller) oraz kod [widoku](http://www.yiiframework.com/doc/guide/basics.view) dla każdej z poszczególnych operacji CRUD.


<div class="revision">$Id: prototype.summary.txt 2333 2010-08-24 21:11:55Z mdomba $</div>