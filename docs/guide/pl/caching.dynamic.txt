Zawartość dynamiczna
===============
Używając [buforowania fragmentarycznego](/doc/guide/caching.fragment) lub [buforowania
stron](/doc/guide/caching.page) często spotykamy się z sytuacją, gdzie cała porcja 
wyjścia jest relatywnie statyczna za wyjątkiem jednego lub kilku miejsc. 
Na przykład, strona pomocy może wyświetlać statyczną pomoc wraz z nazwą użytkownika 
aktualnie zalogowanego, wyświetlaną na górze strony.

Aby rozwiązać ten problem, możemy uzmiennić zawartość bufora w zależności od 
użytkownika, jednakże jest to bardzo duża strata naszej cennej przestrzeni bufora,
ponieważ większość zawartości, poza nazwą użytkownika jest taka sama.  Możemy również
podzielić  stronę na kilka części i buforować je osobno, ale to komplikuje nasz 
widok i czyni nasz kod bardziej skomplikowanym. Lepszym podejściem jest używanie 
funkcjonalności *dynamicznej zawartości* (ang. dynamic content) dostarczanej 
przez [CController].

Dynamiczna zawartość oznacza część wyjścia, która nie powinna być buforowana
nawet jeśli jest ona zawarta wewnątrz buforowania fragmentarycznego. Aby uczynić
zawartość dynamiczną przez cały czas, musi być ona generowana za każdym razem, nawet
jeśli ta zamknięta zawartość została dostarczona przez bufor. Z tego powodu, potrzebujemy
aby dynamiczna zawartość była generowana przez jakąś metodę bądź funkcję. 

Wywołujemy [CController::renderDynamic()] do wstawiania dynamicznej zawartości 
w wybranym miejscu:

~~~
[php]
...pozostała zawartość HTML...
<?php if($this->beginCache($id)) { ?>
...część zawartości, która będzie buforowana...
	<?php $this->renderDynamic($callback); ?>
...część zawartości, która będzie buforowana...
<?php $this->endCache(); } ?>
...pozostała zawartość HTML...
~~~

Powyżej, `$callback` odpowiada poprawnej funkcji tylko callback w PHP. Może to być 
łańcuch znaków odpowiadający nazwie metody w klasie aktualnego kontrolera lub też
globalna funkcja. Może to być również tablica wskazująca na metodę klasy. Każdy dodatkowy
parametr metody [renderDynamic()|CController::renderDynamic()] będzie przekazany 
do funkcji callback. Callback powinien zwrócić dynamiczną zawartość zamiast ją wyświetlać.

<div class="revision">$Id: caching.dynamic.txt 163 2008-11-05 12:51:48Z weizhuo $</div>