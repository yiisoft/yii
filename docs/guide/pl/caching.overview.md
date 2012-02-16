Buforowanie (ang. Caching)
=======

Buforowanie jest tanim i efektywnym sposobem zwiększenia wydajności aplikacji sieciowej.
Poprzez przechowywanie danych statycznych w buforze (ang. cache) oraz dostarczaniu 
ich wtedy gdy są wymagane, oszczędzamy czas potrzebny do wygenerowania danych.  

Korzystanie z buforowania w Yii obejmuje głównie konfigurowanie oraz używanie 
komponentu cache aplikacji. Następująca konfiguracja aplikacji określa komponent 
cache, który używa memcache wraz z dwoma serwerami buforującymi.

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'system.caching.CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
);
~~~

W czasie działania aplikacji, dostęp do komponentu cache można uzyskać poprzez `Yii::app()->cache`.

Yii dostarcza różnych komponentów cache, które mogą przechowywać dane za pomocą różnych mediów.
Na przykład, komponent [CMemCache] hermetyzuje rozszerzenie PHP memcache i używa pamięci 
jako medium do przechowywania cache'u; komponent [CApcCache] hermetyzuje rozszerzenie APC dla PHP; 
natomiast komponent [CDbCache] przechowuje buforowane dane w bazie danych. Poniżej znajduje 
się podsumowanie dostępnych komponentów cache:

   - [CMemCache]: używa [rozszerzenia memcache](http://www.php.net/manual/en/book.memcache.php) dla PHP.

   - [CApcCache]: używa [rozszerzenia APC](http://www.php.net/manual/en/book.apc.php) dla PHP.

   - [CXCache]: używa [rozszerzenia XCache](http://xcache.lighttpd.net/) dla PHP.

   - [CEAcceleratorCache]: używa [rozszerzenia EAccelerator](http://eaccelerator.net/).

   - [CDbCache]: używa bazy danych do przechowywania danych zbuforowanych. Domyślnie, 
   utworzy i będzie używać bazy danych SQLite3 w katalogu runtime. Możesz bezpośrednio 
   określić bazę danych, którą będzie to rozszerzenie używało poprzez ustawienie 
   właściwości [connectionID|CDbCache::connectionID],
   
   - [CZendDataCache]: używa [Zend Data Cache](http://files.zend.com/help/Zend-Server-Community-Edition/data_cache_component.htm)
   jako medium buforowania.
   
   - [CFileCache]: używa plików do przechowywania buforowanych danych. Komponent ten jest przydatny 
   w szczególności do buforowania dużych porcji danych (takich jak strony).
   
   - [CDummyCache]: reprezentuje imitację buforowania (ang. dummy cache), która w praktyce nie buforuje. 
   Celem tego komponentu jest uproszczenie kodu, który sprawdzi dostępność bufora. 
   Na przykład, podczas tworzenia lub gdy serwer nie posiada aktualnie wsparcia dla buforowania, 
   możemy użyć tego komponentu buforowania. W momencie, gdy wsparcie dla buforowania będzie udostępnione,
   możemy go przełączyć na używanie odpowiedniego komponentu buforującego. W obu przypadkach,
   możemy używać tego samego kodu `Yii::app()->cache->get($key)` aby próbować pobrać część danych 
   bez martwienia się, że `Yii::app()->cache` może mieć wartość `null`.

> Tip|Wskazówka: Ponieważ wszystkie te komponenty dziedziczą z tej samej klasy bazowej 
[CCache], można je zamienić na inną metodę buforowania bez modyfikowania kodu, który 
używa buforowania.

Buforowania można używać na różnych poziomach. Na najniższym poziomie używamy buforowania
do zachowania pojedynczej porcji danych takich jak zmienna i nazywamy to 
*buforowaniem danych* (ang. data caching). Na następnym poziomie, zachowujemy 
w buforze fragment strony, który jest generowany przez część skryptu widoku. Na najwyższym 
poziomie, zachowujemy całą stronę w buforze i dostarczamy ją z bufora wtedy gdy zajdzie 
taka potrzeba.

W następnych dwóch podpunktach opowiemy jak używać buforowania na tych poziomach.

> Note|Uwaga: Z definicji, bufor jest ulotnym nośnikiem. Nie sprawdza on istnienia 
buforowanych danych nawet jeśli nie wygasa. Dlatego też, nie używaj buforu jako 
miejsca stałego składowania (np. nie używaj buforu do przechowywania danych sesji).

<div class="revision">$Id: caching.overview.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>