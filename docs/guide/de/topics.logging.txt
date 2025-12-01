Protokollierung (Logging)
=========================

Yii bietet eine flexibles und erweiterbares Logfeature. Zu loggende 
(also zu protokollierende) Meldungen können einer
Logstufe (engl.: log level) und einer Kategorie zugeordnet werden. Mit diesen
Logstufen und Kategoriefiltern können bestimmte Meldungen jeweils
an verschiedene Ziele, wie z.B. Dateien, E-Mails, Browserfenster etc., 
weitergeleitet werden

Loggen von Meldungen
--------------------

Ein Logeintrag kann entweder mit [Yii::log] oder [Yii::trace] angelegt werden.
Der Unterschied zwischen beiden besteht darin, dass `Yii::trace` nur im
[Debug-Modus](/doc/guide/basics.entry#debug-mode) loggt.

~~~
[php]
Yii::log($message, $level, $category);
Yii::trace($message, $category);
~~~

Beim Loggen einer Meldung muss eine Kategorie und eine Stufe angegeben werden.
Die Kategorie ist ein String im Format `xxx.yyy.zzz`, ähnlich einem
[Pfadalias](/doc/guide/basics.namespace). Wenn eine Meldung zum
Beispiel in [CController] geloggt wird, könnte man die Kategorie
`system.web.CController` verwenden. Die Logstufe sollte einem der folgenden
Werte entpsrechen:

   - `trace`: Diese Stufe wird von [Yii::trace] verwendet. Sie dient zum
Nachverfolgen (engl.: trace) des Programmablaufs während der Entwicklungsphase.

   - `info`: dient zum Loggen allgemeiner Informationen.

   - `profile`: dient zur Performance-Analyse, siehe unten.

   - `warning`: dient für Warnhinweise.

   - `error`: dient für schwerwiegende Fehlermeldungen.

Routing von Logeinträgen
------------------------

Mit [Yii::log] oder [Yii::trace] geloggte Meldungen werden im Speicher
gehalten. Für gewöhnlich sollen diese im Browserfenster angezeigt oder in einem
beständigen Speicher wie Dateien oder E-Mails gespeichert werden. Dies nennt
man auch *Routing von Logmeldungen* (engl.: message routing), also das Weiterleiten
der Einträge an bestimmte Zielorte.

In Yii wird das Routing von einer
[CLogRouter]-Anwendungskomponente (sinngem.: Logmeldungs-Weiterleiter) übernommen.
Sie verwaltet eine Reihe sogenannter *Logrouten* (engl.: log routes).
Jede Logroute steht für ein einzelnes Log-Ziel. Meldungen, die
über eine Logroute geschickt werden, können nach Stufe und Kategorie
gefiltert werden.

Um das Logrouting verwenden zu können, muss die
[CLogRouter]-Komponente installiert und frühzeitig geladen (engl.: preload)
werden. Außerdem muss deren Eigenschaft [routes|CLogRouter::routes] (Routen) mit
den von uns gewünschten Logrouten konfiguriert werden. Hier ein Beispiel für
die nötige
[Anwendungskonfiguration](/doc/guide/basics.application#application-configuration):

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'trace, info',
					'categories'=>'system.*',
				),
				array(
					'class'=>'CEmailLogRoute',
					'levels'=>'error, warning',
					'emails'=>'admin@example.com',
				),
			),
		),
	),
)
~~~

In obigem Beispiel gibt es zwei Logrouten. Die erste ist
[CFileLogRoute], welche Logmeldungen in einer Datei im Runtime-Verzeichnis der
Anwendung speichert. Nur Meldungen, die der Stufe `trace` oder `info`
angehören und deren Kategorie mit `system.` beginnt, werden gespeichert. Die
zweite Route ist [CEmailLogRoute], die Logmeldungen an die angegebene
E-Mail-Adresse verschickt. Nur Meldungen mit der Stufe `error` oder
`warning` werden verschickt.

Die folgenden Logrouten stehen in Yii zur Verfügung:

   - [CDbLogRoute]: speichert Logmeldungen in einer Datenbanktabelle.
   - [CEmailLogRoute]: schickt Logmeldungen an die angegebenen E-Mail-Adressen.
   - [CFileLogRoute]: speichert Logmeldungen in eine Datei im
Runtime-Verzeichnis der Anwendung.
   - [CWebLogRoute]: zeigt Logmeldungen am Ende der aktuellen Webseite an.
   - [CProfileLogRoute]: zeigt Logmeldungen zur Performance-Analyse am Ende der
aktuellen Webseite an.

> Info: Das Logrouting wird am Ende des aktuellen Request-Zyklus beim
Auslösen des [onEndRequest|CApplication::onEndRequest]-Events durchgeführt. Um
die Bearbeitung des aktuellen Requests explizit zu beenden, rufen Sie
[CApplication::end()] statt `die()` oder `exit()` auf, da
[CApplication::end()] das [onEndRequest|CApplication::onEndRequest]-Event
auslöst und somit Logmeldungen ordnungsgemäß geloggt werden können.

Filtern von Logmeldungen
------------------------

Wie erwähnt, können Logeinträge nach ihrer Stufe und ihrer Kategorie gefiltert
werden, bevor sie an eine Logroute geleitet werden. Dies geschieht,
indem man die [levels|CLogRoute::levels]- und
[categories|CLogRoute::categories]-Eigenschaften der entsprechenden Route
setzt. Mehrere Stufen oder Kategorien sollten mit Kommas verbunden werden.

Da Logkategorien im Format `xxx.yyy.zzz` vorliegen, können man sie wie
eine Hierarchie von Kategorien behandeln. In diesem Fall könnte man sagen, `xxx`
ist die Elternkategorie von `xxx.yyy`, welche wiederum die Elternkategorie von
`xxx.yyy.zzz` bildet.  Man könnte daher `xxx.*` verwenden, um die Kategorie
`xxx` und all ihre Kind- und Enkelkategorien darzustellen.

Loggen von Kontextinformationen
-------------------------------

Auch zusätzliche Kontextinformationen, wie vordefinierte PHP-Variablen 
(z.B. `$_GET`, `$_SERVER`), die Session-ID
oder den Benutzernamen können mitgeloggt werden. Dies erreicht man, indem man
[CLogRoute::filter] auf einen passenden Logfilter weisen lässt.

Im Framework ist bereits der nützliche [CLogFilter] enthalten, der in den
meisten Fällen als ein solcher Logfilter verwendet werden kann. Standardmäßig
logt [CLogFilter] eine Logmeldung mit Variablen wie `$_GET`, `$_SERVER`, die
oft wertvolle Informationen zum Systemzustand enthalten. [CLogFilter] kann
auch so konfiguriert werden, dass allen zu loggenden Meldungen die
Session-ID, der Benutzername, etc. vorangestellt wird. Dadurch kann die Suche
nach bestimmten Einträgen bei einer globalen Suche beträchtlich vereinfacht
werden.

Die folgende Konfiguration zeigt, wie man das Loggen von Kontextinformationen
aktiviert. Beachten Sie, dass jede Logroute ihren eigenen Filter haben kann.
Standardmäßig verwendet eine Logroute keinen Filter.

~~~
[php]
array(
	......
	'preload'=>array('log'),
	'components'=>array(
		......
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error',
					'filter'=>'CLogFilter',
				),
				...andere Logrouten...
			),
		),
	),
)
~~~

Mit `Yii::trace` können auch Informationen aus dem
Aufrufstapel geloggt werden. Dieses Feature ist standardmäßig deaktiviert, da
es die Performance negativ beeinflusst. Um dieses Feature zu verwenden,
definieren Sie am Anfang Ihres Startscripts einfach die Konstante
`YII_TRACE_LEVEL` mit einem Wert größer als 0 (und zwar bevor Sie `yii.php`
einbinden). Yii hängt dann an jede Tracen-Eintrag den Dateinamen und die
Zeilennummer des Anwendungscode an. Die Zahl in `YII_TRACE_LEVEL` gibt an, wie
viele Ebenen des Aufrufstapels aufgezeichnet werden sollen. Diese Information kann speziell
in der Entwicklungsphase sehr nützlich sein, da es uns hilft, den genauen Ort,
an dem eine Meldung geloggt wurde, ausfindig zu machen.



Performance-Analyse
-------------------

Bei der Performance-Analyse(engl.: performance profiling) handelt es sich
um eine spezielle Logart. Mit ihr kann man die Zeit messen, die ein bestimmter
Codeblock zur Ausführung benötigt und so den leistungsmäßigen Flaschenhals
in einer Anwendung ausfindig machen.

Für die Performance-Analyse müssen die zu analysierenden Codeblöcke erst
identifiziert werden. Mit folgenden beiden Methoden können Anfang und Ende
jedes Blocks markiert werden:

~~~
[php]
Yii::beginProfile('blockID');
...Zu analysierender Codeblock...
Yii::endProfile('blockID');
~~~

wobei `blockID` eine eindeutige ID zur Identifizierung des Blocks
darstellt.

Beachten Sie, dass Codeblöcke sauber verschachtelt werden müssen. Das
bedeutet, dass sich zwei Blöcke nicht überschneiden dürfen. Sie müssen
entweder auf der selben Ebene nebeneinander liegen, oder vollständig von einem
anderen Codeblock umschlossen werden.

Um die Ergebnisse der Messung anzuzeigen, muss eine
[CLogRouter]-Komponente mit einer [CProfileLogRoute]-Logroute installiert werden.
Dies geht genauso wie beim normalen Logrouting. Die
[CProfileLogRoute]-Route zeigt das Ergebnis der Analyse am Ende der aktuellen
Seite an.


Analyse von SQL-Anweisungen
---------------------------

Performance-Analyse ist bei Datenbanken besonders sinnvoll,
da SQL-Anweisungen oft den größten leistungsmäßigen Flaschenhals einer Anwendung
darstellen. Man könnte die Zeit für die Ausführung einer SQL-Anweisung zwar
auch messen, indem man manuell `beginProfile`- und `endProfile`-Ausdrücke an
die passenden Stellen setzt. Es geht aber auch einfacher.

Wenn man [CDbConnection::enableProfiling] per Konfiguration auf true setzt, 
wird jede SQL-Anweisung gemessen. Mit der erwähnten [CProfileLogRoute]
können die Ergebnisse leicht aufgegliedert nach den einzelnen SQL-Anweisungen
und den dafür benötigten Zeiten angezeigt werden. Durch Aufruf von
[CDbConnection::getStats()] kann man die Gesamtzahl aller
SQL-Anweisungen sowie deren gesamte Ausführungszeit abfragen.

<div class="revision">$Id: topics.logging.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
