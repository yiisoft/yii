Cachning
========

Cachning är ett kostnadseffektivt sätt att förbättra prestanda hos en 
webbapplikation. Genom att lagra relativt statiska data i cacheminne och 
leverera dem därifrån på begäran, sparas den tid som skulle gått åt till 
generering av data.

Användande av cacheminne i Yii innebär i huvudsak konfigurering och åtkomst till 
en cache-applikationskomponent. Följande applikationskonfiguration specificerar 
en cachekomponent som använder memcache med två cacheservrar.

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

När applikationen är igång kan cachekomponenten nås via `Yii::app()->cache`.

Yii erbjuder olika cachekomponenter som kan lagra cachedata på olika 
media. Till exempel, [CMemCache]-komponenten kapslar in PHP:s memcache-tillägg 
och använder minne som medium för cachelagring; [CApcCache]-komponenten kapslar 
in PHP:s APC-tillägg; och [CDbCache]-komponenten cachelagrar data i en databas. 
Här följer en sammanfattning av de tillgängliga cachekomponenterna:

   - [CMemCache]: använder PHP:s [memcache-tillägg](http://www.php.net/manual/en/book.memcache.php).

   - [CApcCache]: använder PHP:s [APC-tillägg](http://www.php.net/manual/en/book.apc.php).

   - [CXCache]: använder PHP:s [XCache-tillägg](http://xcache.lighttpd.net/).

   - [CEAcceleratorCache]: använder PHP:s [EAccelerator-tillägg](http://eaccelerator.net/).

   - [CDbCache]: använder en databastabell för cachelagring av  data. Som 
   standard skapas en SQLite3-databas i runtime-katalogen. Det går att explicit 
   ange en databas CDbCache skall använda genom att sätta dess 
   [connectionID|CDbCache::connectionID]-property.

   - [CZendDataCache]: använder [Zend Data Cache](http://files.zend.com/help/Zend-Server-Community-Edition/data_cache_component.htm) som underliggande cachningsmedium. 

   - [CFileCache]: använder filer för cachelagring. Detta är speciellt passande 
   för cachning av stora block av data (t. ex. sidor).

   - [CDummyCache]: utgör en null-cache som inte lagrar någonting alls. Syftet
med denna komponent är att förenkla kod som behöver undersöka cache-tillgänglighet.
Till exempel, under utveckling eller om servern saknar stöd för aktuell cachning, kan
denna cachningskomponent användas. När aktuellt cachningsstöd blir aktivt, kan detta
i stället användas. I bägge fallen kan samma kod, `Yii::app()->cache->get($key)` användas 
till att hämta ett stycke data utan risk att `Yii::app()->cache` skulle kunna vara `null`. 

> Tip|Tips: Eftersom alla dessa cachekomponenter utvidgar samma basklass [CCache], 
kan man byta till en annan typ av cache utan att modifiera kod som använder sig 
av cache.

Cachning kan användas på varierande nivåer. På den lägsta nivån kan vi använda 
cache för att lagra ett enstaka stycke data, som en variabel. Detta benämns 
*data-cachning*. På nästa nivå, kan ett sidfragment cachelagras, dvs innehåll 
som delar av ett vyskript genererat. På den högsta nivån kan vi cachelagra en 
hel sida och leverera den från cache efter behov.

I några av de följande underavsnitten kommer utförligt att behandlas hur cachning 
används på respektive nivåer.

> Note|Märk: Cache är per definition ett flyktigt lagringsmedium. Det garanterar inte 
existensen av cachelagrat data, även sådant vars giltighetstid inte löpt ut. Av denna 
anledning, använd inte cache för icke-flyktig lagring (t.ex. använd inte cache 
till att lagra sessionsdata).

<div class="revision">$Id: caching.overview.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>