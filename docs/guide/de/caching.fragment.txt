Cachen von Fragmenten
=====================

Unter einem Fragment versteht man im Zusammenhang mit Caching einen
Teilbereich einer Seite. Enthält eine Seite z.B. eine Übersicht der
Jahresverkäufe in einer Tabelle, kann man die Zeit zum Erzeugen dieser
Tabelle einsparen, indem man sie als Fragment im Cache speichert.

Um Fragmente zu cachen, ruft man
[CController::beginCache()|CBaseController::beginCache()] und
[CController::endCache()|CBaseController::endCache()] im View-Script eines
Controllers auf. Die beiden Methoden markieren Anfang und Ende des zu
cachenden Inhalts. Genau wie beim
[Datencaching](/doc/guide/caching.data) muss eine ID zur Identifizierung des
Fragments vergeben werden.

~~~
[php]
...Anderer HTML-Inhalt...
<?php if($this->beginCache($id)) { ?>
...Zu cachender Inhalt...
<?php $this->endCache(); } ?>
...Anderer HTML-Inhalt...
~~~

Falls in diesem Beispiel [beginCache()|CBaseController::beginCache()] false
zurückliefert, wird der gecachte Inhalt automatisch an dieser Stelle
eingefügt. Andernfalls wird der Inhalt innerhalb der `if`-Anweisung ausgeführt
und gecacht, wenn [endCache()|CBaseController::endCache()] aufgerufen wird.

Cache-Optionen
--------------

Die Cache-Optionen können angepasst werden, indem man als zweiten Parameter 
ein Array beim Aufruf von [beginCache()|CBaseController::beginCache()] angibt. 
Eigentlich sind die Methoden
[beginCache()|CBaseController::beginCache()]  und
[endCache()|CBaseController::endCache()] nur praktische Wrapper (
sinngem.: Hülle, Umschlag) für das [COutputCache]-Widget. Die verfügbaren 
Cache-Optionen entsprechen daher den Eigenschaften von [COutputCache].

### Cachedauer

Die wahrscheinlich am häufigsten verwendete Eigenschaft ist
[duration|COutputCache::duration] (Dauer), wodurch bestimmt wird, wie lange
der Inhalt im Cache gültig bleibt. Sie ähnelt dem Verfallszeit-Parameter
bei [CCache::set()]. Der folgende Code cacht den Seitenabschnitt für
mindestens eine Stunde:

~~~
[php]
...Anderer HTML-Inhalt...
<?php if($this->beginCache($id, array('duration'=>3600))) { ?>
...Zu cachender Inhalt...
<?php $this->endCache(); } ?>
...Anderer HTML-Inhalt...
~~~

Wenn man die Cachedauer nicht angibt, wird ein Vorgabewert von 60 verwendet,
was bedeutet, dass der gecachte Inhalt nach 60 Sekunden nicht mehr gültig
ist.

Seit Version 1.1.8 wird bereits gecachter Inhalt gelöscht, wenn man den Wert auf 0
setzt. Bei einem negativen Wert wird der Cache deaktiviert, aber gecachter
Inhalt bleibt erhalten. In früheren Versionen wurde der Cache sowohl bei 0 und
negativen Werten deaktiviert, ohne gecachten Inhalt zu verändern.

### Abhängigkeit

Wie beim Datencaching können auch bei Fragmenten Abhängigkeiten berücksichtigt
werden. So kann zum Beispiel der Inhalt eines
angezeigten Beitrags davon abhängen, ob der Beitrag verändert wurde.

Um eine Abhängigkeit anzugeben, setzt man die Option
[dependency|COutputCache::dependency] entweder auf ein Objekt, dass das
[ICacheDependency]-Interface implementiert oder auf ein Array das verwendet
werden kann, um ein Abhängigkeitsobjekt zu erzeugen. Der folgende Code gibt an, dass der
Inhalt des Fragments von einer Änderung des Werts in der Spalte `lastModified`
abhängt:

~~~
[php]
...Anderer HTML-Inhalt...
<?php if($this->beginCache($id, array('dependency'=>array(
		'class'=>'system.caching.dependencies.CDbCacheDependency',
		'sql'=>'SELECT MAX(lastModified) FROM Post')))) { ?>
...Zu cachender Inhalt...
<?php $this->endCache(); } ?>
...Anderer HTML-Inhalt...
~~~

### Variationen

Gecachter Inhalt kann abhängig von einem bestimmten Parameter variiert werden.
Ein Benutzerprofil kann zum Beispiel für verschiedene Benutzer unterschiedlich
aussehen. Um den Inhalt des Profils zu cachen, soll die gecachte Kopie
entsprechend der Benutzer-ID variieren. Das bedeuted im Wesentlichen, dass man
beim Aufruf von [beginCache()|CBaseController::beginCache()] unterschiedliche
IDs verwenden sollten. 

Statt vom Entwickler zu erwarten, IDs nach einem bestimmten Schema zu
variieren, hat [COutputCache] ein solches Feature schon eingebaut. Hier eine
Übersicht:

   - [varyByRoute|COutputCache::varyByRoute]: Falls true, wird der gecachte Inhalt entsprechend der 
[Route](/doc/guide/basics.controller#route) variiert. Dadurch führt jede
Kombination aus angefordertem Controller und Action zu einem anderen
Cache-Inhalt.

   - [varyBySession|COutputCache::varyBySession]: Falls true, wird der gecachte 
Inhalt entsprechend der session ID variiert.
Dadurch kann für jede Benutzer-Session unterschiedlicher Inhalt angezeigt
werden, der jeweils vom Cache geliefert wird.

   - [varyByParam|COutputCache::varyByParam]: Falls true, wird der gecachte Inhalt entsprechend den Werten der angegebenen
GET-Parameter variiert. Wenn eine Seite z.B. einen Beitrag entsprechend dem
GET-Parameter `id` anzeigt, können wir [varyByParam|COutputCache::varyByParam]
auf `array('id')` setzen, so dass der Inhalt jedes Beitrags gecacht wird.
Ohne diese Variation könnten wir nur einen einzelnen Beitrag cachen.

   - [varyByExpression|COutputCache::varyByExpression]: Indem diese Option auf
einen PHP-Ausdruck gesetzt wird, kann der geachte Inhalt entsprechend dem
Ergebnis dieses Ausdrucks variiert werden.

### Request-Typen

Manchmal möchte man Fragmente nur bei bestimmten Requests cachen.
Bei einer Seite, die ein Formular anzeigt, soll
dieses z.B. nur beim ersten Request (per GET-Request) gecacht werden. Bei allen
weiteren Anfragen (per POST-Request) soll das Formular nicht gecacht werden,
weil es evtl. Benutzereingaben enthält. Um dies zu erreichen, kann man 
[requestTypes|COutputCache::requestTypes] verwenden:

~~~
[php]
...Anderer HTML-Inhalt...
<?php if($this->beginCache($id, array('requestTypes'=>array('GET')))) { ?>
...Zu cachender Inhalt...
<?php $this->endCache(); } ?>
...Anderer HTML-Inhalt...
~~~

Verschachteltes Cachen
----------------------

Gecachte Fragmente können verschachtelt werden. Das bedeutet, dass
ein gecachter Abschnitt in einem größeren, ebenfalls gecachten
Seitenabschnitt eingebettet sein kann. Z.B. könnten Blogkommentare in einem inneren
Fragment gecacht werden und gemeinsam mit dem Blogbeitragsinhalt in einem
äußeren Fragment.

~~~
[php]
...other HTML content...
<?php if($this->beginCache($id1)) { ?>
...Äußerer zu cachender Inhalt...
	<?php if($this->beginCache($id2)) { ?>
	...Innerer zu cachender Inhalt...
	<?php $this->endCache(); } ?>
...Äußerer zu cachender Inhalt...
<?php $this->endCache(); } ?>
...Anderer HTML-Inhalt...
~~~

Auf die verschachtelten Caches können unterschiedliche Optionen angewendet
werden. Z.B. können oben der innere und äußere Cache jeweils
verschiedene Werte für die Cache-Dauer verwenden. Wenn die Daten im äußeren
Cache für ungültig erklärt werden, kann der innere immer noch ein gültiges
Fragment liefern. Allerdings gilt das nicht umgekehrt. Falls der äußere
Cache noch gültige Daten enthält, wird er immer die gecachte Kopie ausliefern,
selbst wenn die Daten des inneren Cache bereits abgelaufen sind.

<div class="revision">$Id: caching.fragment.txt 3315 2011-06-24 15:18:11Z qiang.xue $</div>
