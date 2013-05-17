Caching
=======

Mit Caching (sinngem.: Zwischenspeicherung) kann man die Performance einer
Webanwendung auf günstige und wirkungsvolle Weise erhöhen. Speichert man
relativ statische Daten im Cache und liefert sie bei Anfragen direkt von
dort zurück, spart man sich die Zeit, diese Daten erneut aufzubereiten.

In Yii steht dafür eine eigene Cachekomponente in der Anwendung zur Verfügung.
Man kann diese Komponente z.B. für den Einsatz mit zwei Memcacheservern
konfigurieren:

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

Aus der Anwendung heraus wird dann über `Yii::app()->cache` auf diese
Komponente zugegriffen.

Yii bietet verschiedene Cachekomponenten, um Cachedaten auf
unterschiedlichsten Medien zu speichern. Die [CMemCache]-Komponente zum
Beispiel, kapselt die memcache-Erweiterung von PHP um Daten direkt im 
Speicher zu cachen. Die [CDbCache]-Komponente speichert gecachte Daten 
in einer Datenbank. Folgende Cachekomponenten stehen zur Verfügung:

   - [CMemCache]: Verwendet die
	 [memcache-Erweiterung](http://www.php.net/manual/en/book.memcache.php)
von PHP.

   - [CApcCache]: Verwendet die
[APC-Erweiterung](http://www.php.net/manual/en/book.apc.php) von PHP

   - [CXCache]: Verwendet die
[XCache-Erweiterung](http://xcache.lighttpd.net/) von PHP.

   - [CEAcceleratorCache]: Verwendet die
[EAccelerator-Erweiterung](http://eaccelerator.net/) für PHP.

   - [CDbCache]: Verwendet eine Datenbanktabelle zum Speichern gecachter
Daten. Standardmäßig wird eine SQLite3-Datenbank im runtime-Verzeichnis
angelegt und verwendet. Man kann auch explizit eine Datenbank angeben, indem
man [connectionID|CDbCache::connectionID] konfiguriert.

   - [CZendDataCache]: Verwendet den [Zend Data Cache](http://files.zend.com/help/Zend-Server-Community-Edition/data_cache_component.htm) als Cache-Medium.

   - [CFileCache]: Verwendet Dateien um gecachte Daten zu speichern. Dies
eignet sich besonders zum Cachen umfangreicher Daten (z.B. ganzer Seiten).

   - [CDummyCache]: stellt einen Dummy-Cache bereit, der überhaupt nicht
cacht. Diese Komponente bietet sich für die Entwicklungsphase an, damit nicht
dauernd geprüft werden muss, ob eine Cachekomponente bereit steht. Stattdessen
kann man z.B. `Yii::app()->cache->get($key)` aufrufen, ohne Gefahr zu laufen,
dass `Yii::app()->cache` null ist.  Im Livebetrieb wird dann die eigentliche 
Cachekomponente eingestellt.

> Tip|Tipp: Da alle Cachekomponenten von der selben Basisklasse [CCache]
abgeleitet sind, kann auf eine andere Cache-Art umgestellt werden, ohne den
Code anpassen zu müssen.

Caching kann auf unterschiedlichen Ebenen eingesetzt werden. Auf unterster
Ebene verwendet man den Cache zum Speichern von Daten, wie z.B. einer
Variable. Man spricht dann auch von *Datencaching* (engl.: data caching). 
Geht man eine Ebene höher, könnte man auch ganze Fragmente bzw.
Seitenabschnitte, die von einem Viewteil generiert wurden, im Cache ablegen.
Im Maximalfall wird gleich eine ganze Seite im Cache gespeichert und bei
Anfragen direkt von dort ausgeliefert.

In den nächsten Abschnitten gehen wir auf jeden dieser Fälle näher ein.

> Note|Hinweis: Per Definition ist ein Cache ein vergängliches Speichermedium.
Es ist also niemals garantiert, dass ein bestimmter Wert im Cache existiert -
selbst wenn dieser niemals verfallen sollte. Sie sollten daher keine
beständige Daten im Cache speichern (also z.B. keine Sessiondaten).

<div class="revision">$Id: caching.overview.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
