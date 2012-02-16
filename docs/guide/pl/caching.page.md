Buforowanie stron
============

Buforowanie stron odnosi się do buforowania zawartości całej strony. Buforowanie 
strony może występować w różnych miejscach. Na przykład poprzez wybór odpowiedniego
nagłówka strony, przeglądarka może buforować stronę wyświetlaną w ograniczonym 
czasie. Aplikacja sieciowa sama może również przechowywać zawartość strony w buforze.
W tym podpunkcie skupimy się na tym drugim przypadku.

Buforowanie strony może być rozpatrywane jako specjalny przypadek [buforowania 
fragmentarycznego](/doc/guide/caching.fragment). Ponieważ zawartość strony często
generowana jest poprzez zastosowanie układu do widoku, nie zadziała proste wywołanie
[beginCache()|CBaseController::beginCache] oraz 
[endCache()|CBaseController::endCache] w układzie. Powodem tego jest to, że układ 
jest stosowany wewnątrz metody [CController::render()] po tym jak zawartość widoku 
jest przetwarzana.

Aby zbuforować całą stronę, powinniśmy pominąć wywołanie akcji generującej zawartość
strony. Możemy użyć [COutputCache] jako [filtr](/doc/guide/basics.controller#filter) 
akcji aby wykonać to zadanie. Następujący kod pokazuje jak skonfigurować 
filtr buforu:

~~~
[php]
public function filters()
{
	return array(
		array(
			'COutputCache',
			'duration'=>100,
			'varyByParam'=>array('id'),
		),
	);
}
~~~

Powyższa konfiguracja filtru spowoduje, że filtr będzie zastosowany do wszystkich 
akcji w kontrolerze. Możemy ograniczyć go, do jednej lub więcej akcji tylko poprzez 
zastosowanie operatora dodawania. Więcej szczegółów można znaleźć w sekcji 
[filtry](/doc/guide/basics.controller#filter).

> Tip|Wskazówka: Możemy użyć klasę [COutputCache] jako filtr ponieważ dziedziczy ona 
po klasie [CFilterWidget], co oznacza, że jest ona zarówno widżetem oraz filtrem. 
W rzeczy samej, sposób w jaki widżet działa jest bardzo podobny do filtru: widżet
(filtr) rozpoczyna się zanim zamknięta zawartość (akcja) jest przetwarzana i widżet 
(filtr) kończy po tym jak zamknięta zawartość (akcja) jest przetwarzana.

<div class="revision">$Id: caching.page.txt 1014 2009-05-10 12:25:55Z qiang.xue $</div>