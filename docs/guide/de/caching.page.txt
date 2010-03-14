Cachen von Seiten
=================

Beim Cachen von Seiten wird der Inhalt einer ganzen Seite gecacht. Dies kann
an verschiedenen Stellen geschehen. Der Clientbrowser kann zum Beispiel
bereits betrachtete Seiten für einen begrenzten Zeitraum cachen, indem man
einen passenden Seitenheader wählt. Auch die Webanwendung selbst kann den
Inhalt einer Seite im Cache speichern. In diesem Kapitel sehen wir uns
letzeren Ansatz näher an.

Das Cachen einer Seite kann als Spezialfall des [Cachens von
Fragmenten](/doc/guide/caching.fragment) betrachtet werden. Da der
Inhalt einer Seite oft mittels Anwendung eines Layouts auf einen View 
erzeugt wird, funktioniert es nicht, wenn man einfach
[beginCache()|CBaseController::beginCache] und
[endCache()|CBaseController::endCache] im Layout aufruft. Das liegt daran, da
das Layout innerhalb von [CController::render()] erst angewendet wird, NACHDEM der
View-Inhalt bestimmt wurde.

Um eine ganze Seite zu cachen, sollte man die Ausführung derjenigen Action
überspringen, die den Seiteninhalt erzeut. Um dies zu erreichen, kann man [COutputCache] als
[Filter](/doc/guide/basics.controller#filter) für eine Action verwenden.
Der folgende Code zeigt, wie man den Cachefilter konfiguriert:

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

Bei obige Filterkonfiguration würde den Filter auf alle Actions des
Controllers anwenden. Über den Plus-Operator, kann man ihn
auf eine oder wenige Actions beschränken. Nähere Informationen hierzu finden
sich unter [Filter](/doc/guide/basics.controller#filter).

> Tip|Tipp: Man kann [COutputCache] deshalb als Filter verwenden, weil er
[CFilterWidget] erweitert, was bedeutet, dass er sowohl ein Widget, als auch ein
Filter ist. Tatsächlich ähneln sich die Funktionsweisen eines Widgets und
eines Filters: Ein Widget (Filter) beginnt, bevor der eingebettete Inhalt
(Action) ausgewertet wird und endet, nachdem dieser Inhalt (Action)
ausgewertet wurde.

<div class="revision">$Id: caching.page.txt 1014 2009-05-10 12:25:55Z qiang.xue $</div>
