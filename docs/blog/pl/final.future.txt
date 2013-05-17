Dalsze rozszerzanie
===================

Używanie tematów
-------------

Bez pisania dodatkowej linijki kodu nasz blog jest gotowy do używania [tematów](http://www.yiiframework.com/doc/guide/topics.theming). Aby użyć nowy temat musimy przede wszystkim utworzyć temat poprzez napisanie niestandardowego pliku widoku dla tematu. Na przykład, aby móc używać temat o nazwie `classic`, który używa innego układu strony utworzymy plik układu `/wwwroot/blog/themes/classic/views/layouts/column2.php`. Musimy również zmienić konfigurację aplikacji aby wskazać, że wybraliśmy temat `classic`:

~~~
[php]
return array(
	......
	'theme'=>'classic',
	......
);
~~~


Umiędzynaradawianie 
--------------------

Możemy również umiędzynarodowić nasz blog, tak, że każda jego strona może być wyświetlana w innym języku. Wymaga to przede wszystkim działania w dwóch aspektach. 

Po pierwsze, możemy utworzyć plik widoku w innym języku. Na przykład dla strony `index` kontrolera `PostController`, możemy utworzyć plik widoku `/wwwroot/blog/protected/views/post/zh_cn/index.php`. Jeśli aplikacja jest skonfigurowana do używania uproszczonego języka chińskiego (kod języka to `zh_cn`), Yii automatycznie użyje tego nowego pliku widoku zamiast oryginalnego.

Po drugie, możemy utworzyć tłumaczenia komunikatów generowanych przez kod. Tłumaczenie komunikatów powinno zostać zapisane jako pliki w katalogu `/wwwroot/blog/protected/messages`. Potrzebujemy również zmodyfikować kod, gdzie używane są łańcuchy znaków poprzez zamknięcie ich w wywołaniu metody `Yii::t()`.

Aby uzyskać więcej informacji o umiędzynaradawianiu, spójrz do [przewodnika](http://www.yiiframework.com/doc/guide/topics.i18n).


Polepszanie wydajności przy użyciu buforowania
--------------------------------

Chociaż framework Yii jest sam w sobie [bardzo wydajny](http://www.yiiframework.com/performance/), niekoniecznie prawdą jest, że aplikacja napisana w Yii jest wydajna. Jest kilka miejsc w naszym blogu, gdzie możemy zwiększyć wydajność. Na przykład, portlet chmurki tagów może być jednym z wąskich gardeł wydajnościowych ponieważ zawiera on złożone zapytanie do bazy danych oraz złożoną logikę PHP.

Możemy używać wyrafinowanych [funkcji buforowania](http://www.yiiframework.com/doc/guide/caching.overview) dostarczanych przez Yii do zwiększenia wydajności. Jednym z najbardziej użytecznych komponentów Yii jest [COutputCache], który buforuje części wyświetlanej strony, tak, że kod zajmujący się generowaniem fragmentu nie musi być wykonywany dla każdego żądania. Na przykład w pliku widoku `/wwwroot/blog/protected/views/layouts/main.php` możemy zamknąć portlet chmurki tagów w [COutputCache]:

~~~
[php]
<?php if($this->beginCache('tagCloud', array('duration'=>3600))) { ?>

	<?php $this->widget('TagCloud', array(
		'maxTags'=>Yii::app()->params['tagCloudCount'],
	)); ?>

<?php $this->endCache(); } ?>
~~~

W powyższym kodzie, dla każdego żądania, wyświetlanie chmurki tagów będzie dostarczane z bufora zamiast generowane w locie. Zbuforowana zawartość pozostanie ważna w buforze na okres 3600 sekund.


Dodawanie nowych funkcji
-------------------

Nasza aplikacja posiada jedynie kilka bardzo prostych funkcjonalności. Aby stać się kompletnym systemem blogowym potrzebne są nowe funkcje, na przykład, portlet kalendarza, powiadomienia przez email, kategoryzowanie wiadomości, portlet wiadomości zarchiwizowanych, itp. Pozostawimy implementację tych funkcji zainteresowanym czytelnikom

<div class="revision">$Id: final.future.txt 2017 2010-04-05 17:12:13Z alexander.makarow $</div>