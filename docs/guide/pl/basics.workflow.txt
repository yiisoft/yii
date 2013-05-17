Przebieg dewelopmentu
====================

Mając opisane podstawy koncepcji Yii, pokażemy całościowy przebieg tworzenia aplikacji
przy użyciu Yii. Przebieg ten zakłada, że sprawdziliśmy wymagania stawiane Yii
tak samo, jak że przeprowadziliśmy analizę projektową dla aplikacji. 

   1. Tworzenie szkieletu struktury katalogowej. Narzędzie `yiic` opisane 
   w [Pierwszej aplikacji w Yii ](/doc/guide/quickstart.first-app) może zostać użyte, 
   aby przyśpieszyć ten krok.

   2. Konfigurowanie [aplikacji](/doc/guide/basics.application). Robimy to poprzez 
   modyfikowanie pliku konfiguracji aplikacji. Krok ten może również wymagać napisania 
   pewnych komponentów aplikacji (np. komponentu użytkownika).

   3. Tworzenie klasy [modelu](/doc/guide/basics.model) dla każdego typu danych, 
   którym będziemy zarządzać. Narzędzie `Gii` opisane w [Tworzeniu pierwszej aplikacji Yii](doc/guide/quickstart.first-app#implementing-crud-operations)
   oraz w [Automatycznym generowaniu kodu](/doc/guide/topics.gii) może zostać użyte do automatycznego
   wygenerowania klasy [rekordu aktywnego](//doc/guide/database.ar) dla każdej z interesujących nas
   tabel bazodanowych.

   4. Tworzenie klasy [kontrolera](/doc/guide/basics.controller) dla każdego typu 
   żądania użytkownika. Jak sklasyfikować żądania użytkownika w zależności od wymagań?
   Uogólniając, jeśli model klasy musi być udostępniony użytkownikowi, powinien 
   posiadać odpowiadającą mu klasę kontrolera. Narzędzie `Gii` pomoże również zautomatyzować
   ten krok.

   5. Implementowanie [akcji](/doc/guide/basics.controller#action) oraz odpowiadających 
   im [widoków](/doc/guide/basics.view). To jest miejsce gdzie prawdziwa praca
   musi zostać wykonana.

   6. Konfigurowanie odpowiednich [filtrów](/doc/guide/basics.controller#filter) 
   akcji w klasach kontrolera.

   7. Tworzenie [tematów](/doc/guide/topics.theming) jeśli korzystanie z tematów
   jest wymagane.

   8. Tworzenie tłumaczeń komunikatów jeśli [internacjonalizacja](/doc/guide/topics.i18n) 
   jest wymagana.

   9. Rozpoznawanie danych oraz widoków, które mogą być buforowane oraz stosowanie odpowiednich 
   technik [buforowania](/doc/guide/caching.overview).

   10. Końcowy [tuning](/doc/guide/topics.performance) oraz wdrożenie.

Dla każdego z powyższych kroków, może być konieczne stworzenie przypadków testowych 
i sprawdzenie ich.

<div class="revision">$Id: basics.workflow.txt 2718 2010-12-07 15:17:04Z qiang.xue $</div>