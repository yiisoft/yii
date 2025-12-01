Model-Widok-Kontroler (ang. Model-View-Controller, MVC)
===========================

Yii implementuje wzorzec projektowy model-widok-kontroler (MVC), który jest 
powszechnie stosowany w programowaniu webowym. MVC ma na celu oddzielenie 
logiki biznesowej od interfejsu użytkownika, biorąc pod uwagę to, że developer 
może dzięki temu w łatwy sposób zmienić każdą część bez oddziaływania na inną.
W MVC model reprezentuje informacje (dane) oraz reguły biznesowe. Widok zawiera 
elementy interfejsu użytkownika takie jak tekst, formularze. Kontroler zarządza 
komunikacją pomiędzy modelem a widokiem.

Poza implementacją MVC, Yii wprowadza również front-controller, nazywany aplikacją, który hermetyzuje 
wykonywanie kontekstu przetwarzanego żądania. Aplikacja zbiera pewne informacje o żądaniu
użytkownika a następnie przekazuje je do odpowiedniego kontrolera w celu późniejszego przetworzenia.

Poniższy diagram pokazuje statyczną strukturę aplikacji napisanej w Yii:

![Statyczna struktura aplikacji napisanej w Yii](structure.png)


Typowe sterowanie kolejnością zadań (ang. A Typical Workflow)
------------------
Poniższy diagram pokazuje typowe sterowanie kolejnością zadań w aplikacji Yii podczas przetwarzania
żądania użytkownika:

![Typowe sterowanie kolejnością zadań dla aplikacji napisanej w Yii](flow.png)

   1. Użytkownik wysyła żądanie za pomocą URL `http://www.example.com/index.php?r=post/show&id=1`
   a serwer webowy przetwarza żądanie poprzez wykonanie skryptu rozruchowego 
   (ang. bootstrap script) `index.php`.
   2. Skrypt rozruchowy tworzy instancję [aplikacji](/doc/guide/basics.application)
   i uruchamia ją.
   3. Aplikacja zawiera szczegółowe informacje o żądaniu użytkownika pochodzące z 
   [komponentu aplikacji](/doc/guide/basics.application#application-component)
   o nazwie `request`.
   4. Za pomocą komponentu aplikacji `urlManager` aplikacja ustala żądany 
   [kontroler](/doc/guide/basics.controller) oraz [akcję ](/doc/guide/basics.controller#action). 
   W podanym przykładzie, kontrolerem jest `post`, który reprezentuje 
   klasę `PostController`; akcją jest `show`, której aktualne znaczenie jest determinowane
   przez kontroler.
   5. Aplikacja tworzy instancję żądanego kontrolera w celu przetworzania w późniejszym etapie
   żądania użytkownika. Kontroler ustala, iż akcja `show` odpowiada metodzie nazwanej `actionShow`
   w klasie kontrolera. Następnie tworzy on i wywołuje filtry (np. filtr kontroli dostępu, benchmarking) 
   powiązane z tą akcją. Akcja jest wykonywana, jeśli zezwalają na to filtry.
   6. Akcja odczytuje [model](/doc/guide/basics.model) `Post` z bazy danych o ID równym `1`.
   7. Akcja generuje [widok](/doc/guide/basics.view) o nazwie `show` wraz z modelem `Post`.
   8. Widok odczytuje i wyświetla atrybuty modelu `Post`.
   9. Widok wywołuje pewne [widżety](/doc/guide/basics.view#widget).
   10. Wynik generowania jest osadzany w [widoku](/doc/guide/basics.view#layout).
   11. Akcja kończy generowanie widoku i wyświetla rezultat użytkownikowi.


<div class="revision">$Id: basics.mvc.txt 3321 2011-06-26 12:54:22Z mdomba $</div>