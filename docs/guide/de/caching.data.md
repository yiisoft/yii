Cachen von Daten
================

Beim Datencaching weden PHP-Variablen im Cache gespeichert bzw. von dort ausgelesen.
Die wichtigsten beiden Methoden dafür sind in der Basisklasse [CCache]
definiert: [set()|CCache::set] und [get()|CCache::get].

Eine Variable kann unter einer eindeutigen ID mit [set()|CCache::set] im Cache
gespeichert werden:

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

Die gecachten Daten verbleiben für immer im Cache, außer sie werden aufgrund
bestimmter Cacherichtlinien entfernt (z.B. wenn der Cachespeicher voll ist und
alte Daten daher entfernt werden). Über einen weiteren Parameter kann dieses
Verhalten geändert werden, so dass die Daten nach einer bestimmten Zeitspanne
verfallen:

~~~
[php]
// Daten für max. 30 Sekunden um Cache halten
Yii::app()->cache->set($id, $value, 30);
~~~

Mit [get()|CCache:get] kann man später (im gleichen oder einem späteren
Request) über die ID die Daten wieder aus dem Cache auslesen. Wird hier
`false` zurückgegeben, ist der Wert nicht im Cache verfügbar. Evtl. sollte man
ihn dann neu anlegen:

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
	// $value neu generieren und für spätere Zwecke im Cache 
	// speichern, da der Wert nicht im Cache gefunden wurde
	// Yii::app()->cache->set($id,$value);
}
~~~

Es ist zu beachten, dass jede gespeicherte Variable eine eindeutige ID erhält. 
Allerdings muss diese ID NICHT zwischen verschiedenen Anwendungen auf dem
selben Server eindeutig sein. Die Cachekomponente kann die IDs
unterschiedlicher Anwendungen unterscheiden.

Einige Cachespeicher, wie MemCache oder APC, unterstützen die Abfrage mehrerer
gespeicherter Werte auf einmal. Durch Verwendung von [mget()|CCache::mget] 
kann so der Overhead beim Abrufen von gecachten Daten reduziert werden.
Wird dieses Feature nicht vom Cachespeicher unterstützt, wird es von
[mget()|CCache::mget] simuliert.

Mit [delete()|CCache::delete] wird ein einzelner Cacheeintrag gelöscht. Mit 
[flush()|CCache::flush] kann der Cache komplett geleert werden. 
Beim Aufruf von [flush()|CCache::flush] sollten Sie jedoch vorsichtig sein,
da diesmal dabei auch alle gecachten Daten von anderen Anwendungen entfernt
werden. 

> Tip|Tipp: Da [CCache] das Interface `ArrayAccess` implementiert, kann eine
> Cache-Komponente wie ein Array verwendet werden. Hier einige Beispiele:
> ~~~
> [php]
> $cache=Yii::app()->cache;
> $cache['var1']=$value1;  // äquivalent zu: $cache->set('var1',$value1);
> $value2=$cache['var2'];  // äquivalent zu: $value2=$cache->get('var2');
> ~~~

Cachen mit Abhängigkeit
-----------------------

Außer über die Verfallszeit kann die Gültigkeit eines Cacheeintrags auch von
anderen Bedingungen abhängig sein. Cacht  man zum Beispiel den Inhalt einer
Datei, sollten die Cachedaten ungültig bzw. aktualisiert werden, sobald die
Datei geändert wird.

Eine solche Abhängigkeit (engl.: dependency) wird durch eine Instanz vom Typ 
[CCacheDependency] oder deren Kindklasse repräsentiert. Beim Aufruf von 
[set()|CCache::set] kann ein solches Objekt zusammen mit den zu cachenden
Daten übergeben werden.

~~~
[php]
// Der Wert verfällt in 30 Sekunden. Er kann auch schon eher verfallen
// wenn die abhängige Datei verändert wird
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('DateiName'));
~~~

Wird `$value` mit [get()|CCache::get] ausgelesen, wird die Abhängigkeit
ausgewertet.  Falls es dort eine Änderung gab, wird false
zurückgeliefert. In diesem Fall müssen die Daten neu generiert werden.

Folgende Cacheabhängigkeiten stehen bereit:

   - [CFileCacheDependency]: Ändert sich bei einem neuen Änderungszeitpunkt
   der Datei.

   - [CDirectoryCacheDependency]: Ändert sich, wenn eine der Dateien im
Verzeichnis (oder Unterverzeichnis davon) verändert wurde.

   - [CDbCacheDependency]: Ändert sich, wenn das Ergebnis der SQL-Abfrage verändert.

   - [CGlobalStateCacheDependency]: Ändert sich, wenn der Wert des angegebenen 
globalen Status sich verändert hat. Ein globaler Status ist eine Variable, deren 
Wert über mehrere Requests und Sessions hinweg beständig bleibt. Er wird über 
[CApplication::setGlobalState()] gesetzt.

   - [CChainedCacheDependency]: Ändert sich, wenn eine der Abhängigkeiten in
der Kette eine Änderung anzeigt.

   - [CExpressionDependency]: Ändert sich, wenn der Wert des angegebenen PHP-Ausdrucks sich ändert.

Cachen von Abfragen
-------------------

Seit Version 1.1.7 kann Yii auch Abfragen cachen. Der Abfragecache basiert
auf einem Datencache um die Ergebnisse einer Datenbankabfrage zu cachen.
Dadurch kann später die Zeit zum Ausführen der Abfrage eingespart werden, weil
das Ergebnis direkt aus dem Cache geliefert wird.

>Info|Info: Einige DBMS (z.B. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html)
>haben einen solchen Abfragecache bereits serverseitig eingebaut. Unser
>Abfragecache ist allerdings flexibler und - zumindest potentiell -
>effizienter.

### Aktivieren des Abfragecaches

Zum Aktivieren des Abfragecaches muss [CDbConnection::queryCacheID] auf die
ID einer gültigen Cache-Komponente verweisen (Vorgabewert ist `cache`).

### Verwendung des Abfragencaches mit DAO

Um den Abfragecache zu verwenden, ruft man die Methode
[CDbConnection::cache()] auf, wenn eine DB-Abfrage durchgeführt wird:

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
~~~

Yii prüft hier zunächst, ob im Cache bereits ein noch gültiges Ergebnis für
die SQL-Abfrage vorliegt. Dazu werden folgende drei Bedingungen sichergestellt:

- dass sich im Cache ein Eintrag mit dem SQL-Ausdruck als Schlüssel befindet.
- dass der Eintrag nicht verfallen ist (weniger als 1000 Sekunden seit dem
Speichern im Cache vergangen sind)
- dass die Abhängigkeit sich nicht verändert hat (der größte Wert für
`update_time` ist immer noch der selbe wie beim Schreiben in den Cache).

Wenn alle diese Bedingungen erfüllt sind, wird das Abfrageergebnis direkt aus
dem Cache geliefert. Andernfalls wird der SQL-Ausdruck an die Datenbank
geschickt, das Ergebnis im Cache abgelegt und zurückgegeben.

###Verwendung des Abfragecaches mit ActiveRecord

Man kann den Abfragecache auch mit [Active Record](/doc/guide/database.ar)
verwenden. Dazu ruft man analog [CActiveRecord::cache()] wie folgt auf:

~~~
[php]
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$posts = Post::model()->cache(1000, $dependency)->findAll();
// relational AR query
$posts = Post::model()->cache(1000, $dependency)->with('author')->findAll();
~~~

Die `cache()`-Methode ist in diesem Fall eine Abkürzung für [CDbConnection::cache()].
Intern versucht Yii beim Ausführen des SQL-Ausdrucks den Abfragecache wie im
letzten Absatz beschrieben anzuwenden.

###Cachen mehrfacher Abfragen

Normalerweise markiert ein Aufruf von `cache` (in [CDbConnection] oder [CActiveRecord]),
dass die nächste SQL-Abfrage gecacht werden soll. Sämtlichen weiteren
SQL-Abfragen werden NICHT gecacht, bis `cache()` erneut aufgerufen wird:

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');

$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
// Abfragencache wird NICHT verwendet:
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

Übergibt man an `cache()` den zusätzlichen Parameter `$queryCount`, kann man
damit erzwingen, dass mehrere darauffolgende Abfragen gecacht werden. Im
nächsten Beispiel wird `cache()` angewiesen, die nächsten zwei Abfragen zu
cachen:

~~~
[php]
// ...
$rows = Yii::app()->db->cache(1000, $dependency, 2)->createCommand($sql)->queryAll();
// Abfragencache WIRD verwendet:
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

Wie bekannt kann es vorkommen, dass bei relationalen AR-Abfragen manchmal
mehrere SQL-Abfragen ausgeführt werden (was man in den
[Logmeldungen](/doc/guide/topics.logging) kontrollieren kann). Der folgende
Code führt zum Beispiel zu zwei Abfragen, sofern `Beitrag` und `Kommentar` mit
`HAS_MANY` verknüpft sind:

- Es holt sich zunächst die ersten 20 Beiträge
- und dann die Kommentare für die erhaltenen Beiträge

~~~
[php]
$beitraege= Beitrag::model()->with('kommmentare')->findAll(array(
	'limit'=>20,
));
~~~

Verwendet man den Abfragencache wie folgt, wird lediglich die erste Abfrage
gecacht:

~~~
[php]
$beitraege= Beitrag::model()->cache(1000, $dependency)->with('kommentare')->findAll(array(
	'limit'=>20,
));
~~~

Damit die Ergebnisse von beiden DB-Abfragen im Cache landen, muss die Anzahl
der zu cachenden Abfragen über den zusätzlichen Parameter angegeben werden:

~~~
[php]
$beitraege= Beitrag::model()->cache(1000, $dependency, 2)->with('kommentare')->findAll(array(
	'limit'=>20,
));
~~~

### Beschränkungen

Der Abfragecache funktioniert nicht mit Ergebnissen, die Ressource-Handles
enthalten. Verwendet man z.B. den Spaltentyp `BLOB`, liefern einige DBMS ein
Ressource-Handle als Daten zurück.

Einige Cache-Speicher begrenzen außerdem die Größe der ablegbaren Daten.
Bei Memcache liegt diese Größe bei 1MB. Übersteigt ein Abfrageergebnis diese
Größe, kann es nicht gecacht werden.

<div class="revision">$Id: caching.data.txt 3125 2011-03-25 17:05:31Z qiang.xue $</div>
