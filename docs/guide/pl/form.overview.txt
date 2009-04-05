Praca z formularzami
=================

Zbieranie danych od użytkownika poprzez formularze HTML jest jednym z głównych 
zadań podczas tworzenia aplikacji webowych. Poza zaprojektowaniem formularza, 
developer musi wypełnić formularz istniejącymi danymi lub też wartościami 
domyślnymi i zapisać te dane wejściowe w pamięci trwałej. Yii bardzo upraszcza 
ten przepływ informacji za pomocą swojej architektury MVC. 

Następujące kroki są zazwyczaj wymagane kiedy mamy do czynienia z formularzami w Yii:

   1. utworzenie klasy modelu reprezentującej pola danych, które będą zbierane.
   1. utworzenie kontrolera akcji wraz z kodem, który odpowiada za przesłanie formularza.
   1. utworzenie formularza w pliku widoku, który jest powiązany z kontrolerem akcji.

W następnych punktach opiszemy szczegółowo każdy z tych kroków. 

<div class="revision">$Id: form.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>