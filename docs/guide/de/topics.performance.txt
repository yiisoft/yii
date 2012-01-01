Performance-Optimierung
=======================

Die Performance (oder Leistungsfähigkeit) einer Webanwendung wird von vielen Faktoren
beeinflusst. Datenbankzugriffe, Operationen im Dateisystem und
Netzwerkbandbreiten sind alles potentielle Einflussfaktoren. Yii versucht in
jeder Hinsicht, die framework-bedingten Performanceeinbußen so gering wie
möglich zu halten. Trotzdem gibt es bei einer Anwendung immer noch viele 
Stellen, an denen Verbesserungen im Sinne einer erhöhten
Leistungsfähigkeit vorgenommen werden können.


Aktivieren der APC-Erweiterung
----------------------------

Der wahrscheinlich einfachste Weg, um die Gesamtperformance einer Anwendung zu
verbessern, ist die Aktivierung der
[APC-Erweiterung](http://www.php.net/manual/de/book.apc.php) in PHP. Die
Erweiterung cached und optimiert PHPs Zwischencode (engl.: intermediate code) 
und spart somit die Zeit, die bei jedem eingehenden Request für das 
Auswerten eines PHP-Scripts nötig wäre.

Ausschalten des Debug-Modus
---------------------------

Eine andere einfache Methode zur Verbesserung der Performance, besteht darin,
den Debug-Modus auszuschalten. Eine Yii-Anwendung läuft im Debug-Modus, wenn die
Konstante `YII_DEBUG` als true definiert ist. Der Debug-Modus ist in der
Entwicklungsphase sehr nützlich, beeinflusst aber die Performance, da
einige Komponenten in diesem Modus zusätzlichen Aufwand verursachen. So loggt
zum Beispiel der Logger zusätzliche Debug-Informationen mit jeder
geloggten Meldung.

Verwenden von `yiilite.php`
---------------------------

Falls die [APC-Erweiterung](http://www.php.net/manual/en/book.apc.php) von PHP
aktiviert wurde, können wir `yii.php` durch eine andere Startdatei namens
`yiilite.php` ersetzen, um die Leistungsfähigkeit einer Yii-Anwendung weiter
zu erhöhen.

Die Datei `yiilite.php` ist in jeder Yii-Version enthalten. In ihr sind einige 
häufig verwendete Klassendateien von Yii zusammengefasst. Sowohl Kommentare
als auch trace-Anweisungen wurden entfernt. Der Einsatz von `yiilite.php`
minimiert also die Anzahl einzubindender Dateien und verhindert die Ausführung
der trace-Anweisungen.

Beachten Sie, dass der Einsatz von `yiilite.php` ohne APC die
Performance sogar verringern kann, da `yiilite.php` einige Klassen
enthält, die nicht notwendigerweise bei jedem Request verwendet werden und
somit zusätzliche Zeit für deren Auswertung benötigt wird. In einigen
Serverkonfiguration wurde außerdem beobachtet, dass `yiilite.php` selbst mit
aktiviertem APC langsamer ist. Um zu entscheiden, ob
`yiilite.php` eingesetzt wird oder nicht, führt man am besten einen Benchmark
mit dem enthaltenen `hello world`-Demo durch.


Einsatz von Cache-Techniken
---------------------------

Wie im Kapitel [Caching](/doc/guide/caching.overview) beschrieben, bietet
Yii verschiedene Caching-Lösungen, die die Anwendungsperformance
bedeutend verbessern können. Falls das Erzeugen bestimmter Daten sehr viel
Zeit in Anspruch nimmt, können wir diese [Daten cachen](/doc/guide/caching.data) um
sie nicht jedesmal neu erzeugen zu müssen. Falls ein Bereich einer Seite
relativ statisch bleibt, können wir diesen [Seitenbereich
cachen](/doc/guide/caching.fragment), um ihn nicht so oft rendern zu müssen.
Falls ganze Webseiten relativ statisch bleiben, können wir ganze [Seiten
cachen](/doc/guide/caching.page), um die Kosten für das Rendern dieser Seiten
einzusparen.

Wenn eine Applikation von [ActiveRecords](/doc/guide/database.ar) gebraucht
macht, sollten wir das Schema-Caching aktivieren, um die Zeit für das Auswerten
des Datenbankschemas einzusparen. Dies erreichen wir, indem wir für die
Eigenschaft [CDbConnection::schemaCachingDuration] einen Wert größer als 0
konfigurieren.

Neben den Cache-Techniken auf Anwendungsebene, können wir auch Cache-Lösungen
auf Serverebene einsetzen, um die Performance einer Applikation weiter
zu erhöhen. Tatsächlich fällt das beschriebene Cachen mit
[APC](/doc/guide/topics.performance#enabling-apc-extension) in diese
Kategorie. Es gibt auch andere Servertechniken, wie den [Zend
Optimizer](http://www.zend.com/en/products/guard/zend-optimizer)
[eAccelerator](http://eaccelerator.net/) oder
[Squid](http://www.squid-cache.org/), um nur einige zu nennen.

Datenbankoptimierung
--------------------

Der entscheidende Flaschenhals in einer Anwendung liegt oft im Beziehen von
Daten aus einer Datenbank. Obwohl der Einsatz eines Caches den
Leistungseinbruch bereits lindern kann, löst dies das Problem noch nicht ganz.
Falls eine Datenbank riesige Datenmengen umfasst und die gecachten Daten
ungültig sind, kann das Beziehen der neuesten Daten unheimlich teuer werden,
wenn Datenbank und Abfragen nicht ordentlich geplant wurden.

Planen Sie ihre Datenbank-Indizes weise. Mit Indizierung können
`SELECT`-Abfragen wesentlich beschleunigt, `INSERT`-, `UPDATE`- und
`DELETE`-Anfragen jedoch verlangsamt werden.

Für komplexe Abfragen empfiehlt es sich, einen Datenbank-View dafür zu
erstellen, statt die Abfragen von PHP aus jedesmal erneut zu senden und das
DBMS wiederholt mit der Auswertung zu beauftragen.

Übertreiben Sie den Einsatz von [ActiveRecords](/doc/guide/database.ar) nicht. 
Obwohl [ActiveRecords](/doc/guide/database.ar) sich gut für die
Datenmodellierung in OOP eignen, verringern sie auch die Performance.
Dies liegt daran, dass für jede Zeile eines Abfrageergebnisses ein oder
mehrere Objekte erzeugt werden müssen. Für Datenintensive Anwendungen könnte
also der Einsatz von [DAO](/doc/guide/database.dao) oder der Datenbank-APIs
auf niedrigerer Stufe die bessere Wahl darstellen.

Verwenden Sie schließlich `LIMIT` in Ihren `SELECT`-Abfragen. Dadurch wird
verhindert, dass unermessliche Daten aus der Datenbank gelesen werden und den
Speicher aufbrauchen, der PHP zugewiesen wurde.

Minimieren von Scriptdateien
----------------------------

Komplexe Seiten müssen häufig viele externe JavaScript- und CSS-Dateien
einbinden. Da jede Datei einen weiteren Abfragezyklus zum Server hervorrufen
würde, sollten wir die Anzahl der Scriptdateien minimieren, indem wir sie in
wenigen Dateien zusammenfassen. Wir sollten auch in Betracht ziehen, die Größe 
jeder Scriptdatei zu verkleinern, um die Übertragungszeit über das Netzwerk zu
verringern. Für diese zwei Aufgaben gibt es viele Hilfsprogramme.

Für eine von Yii generierte Seite ist die Wahrscheinlichkeit sehr hoch, dass
von Komponenten einige Scriptdateien gerendert werden, die wir nicht verändern
möchten (z.B. von Yii-Kernkomponenten oder Komponten von Drittanbietern). Um
diese Dateien zu verkleinern, bedarf es zweier Schritte.

Als erstes legen wir die zu minimierenden Scripte über die Eigenschaft
[scriptMap|CClientScript::scriptMap] der
[clientScript|CWebApplication::clientScript]-Anwendungskomponente fest. Dies
kann entweder in der Anwendungskonfiguration oder per Code erfolgen. Zum
Beispiel:

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>'/js/all.js',
	'jquery.ajaxqueue.js'=>'/js/all.js',
	'jquery.metadata.js'=>'/js/all.js',
	......
);
~~~

Dieser Code bildet die JavaScript-Dateien auf die URL `/js/all.js` ab. Wenn
eine dieser Dateien von einer Komponente eingebunden werden muss, bindet Yii
(einmal) die URL, statt der einzelnen Scriptdateine ein.

Zweitens müssen wir ein Hilfsprogramm verwenden, um die JavaScript-Dateien zu
einer einzelnen Datei zusammenzufassen (und vielleicht auch zu komprimieren)
und unter `js/all.js` zu speichern.

Der selbe Trick gilt auch für CSS-Dateien.

Wir können die Ladezeit von Seiten auch mit Hilfe der [Google AJAX Libraries
API](http://code.google.com/apis/ajaxlibs/) verbessern. Wir können zum
Beispiel `jquery.js` von Google-Servern einbinden, statt von unserem eigenen
Server. Um dies zu erreichen, konfigurieren wir `scriptMap` wie folgt:

~~~
[php]
$cs=Yii::app()->clientScript;
$cs->scriptMap=array(
	'jquery.js'=>false,
	'jquery.ajaxqueue.js'=>false,
	'jquery.metadata.js'=>false,
	......
);
~~~

Indem wir diese Scriptdateien auf false abbilden, verhindern wir, dass Yii den
Code zum Einbinden dieser Dateien generiert. Stattdessen schreiben wir den
folgenden Code in unsere Seiten, um die Dateien explizit von Google
einzubinden.

~~~
[php]
<head>
<?php echo CGoogleApi::init(); ?>

<?php echo CHtml::script(
	CGoogleApi::load('jquery','1.3.2') . "\n" .
	CGoogleApi::load('jquery.ajaxqueue.js') . "\n" .
	CGoogleApi::load('jquery.metadata.js')
); ?>
......
</head>
~~~

<div class="revision">$Id: topics.performance.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
