Themes
======

Themes (sinngem.: Thema, Leitmotiv) stellen eine Methode dar, das Aussehen einer Webseite
systematisch anzupassen. Durch das Anwenden eines Themes kann man das komplette
Erscheinungsbild einer Webanwendung praktisch auf Knopfdruck grundlegend
verändern. 

Jedes Theme wird in Yii von einem Verzeichnis dargestellt, das View-, Layout-
und andere relevante Ressourcedateien, wie Bilder, CSS- und Javascript-Dateien 
enthält. Der Name eines Themes entspricht dem Verzeichnisnamen. Alle Themes
befinden sich unterhalb von `WebVerzeichnis/themes`. Es kann jeweils immer nur ein Theme
aktiv sein.

> Tip|Tipp: Das Standardverzeichnis für Themes kann auch an einem anderen Ort
als `WebVerzeichnis/themes` liegen. Dazu konfiguriert man einfach die beiden
Eigenschaften [basePath|CThemeManager::basePath] (Basispfad) und
[baseUrl|CThemeManager::baseUrl] (Basis-URL) der
[themeManager|CWebApplication::themeManager]-Anwendungskomponente.


Verwenden von Themes
--------------------

Um ein Theme anzuwenden, setzt man die Eigenschaft [theme|CWebApplication::theme] 
der Webanwendung auf den Namen des gewünschten Themes. Dies kann
entweder in der
[Anwendungskonfiguration](/doc/guide/basics.application#application-configuration)
oder während der Laufzeit in einer Controller-Action geschehen. 

> Note|Hinweis: Beim Namen eines Themes spielt die Groß-/Kleinschreibung eine
> Rolle. Wenn man ein Theme konfiguriert, das es gar nicht gibt,
> liefert `Yii::app()->theme` den Wert `null` zurück. 



Erstellen eines Themes
----------------------

Die Inhalte eines Themeverzeichnisses sollten genau wie im 
[Anwendungsverzeichnis](/doc/guide/basics.application#application-base-directory)
abgelegt werden. Alle View-Dateien müssen sich zum Beispiel in `views`,
Layout-Dateien in `views/layout` und System-View-Dateien unter `views/system`
befinden. Möchte man z.B. den View `create` für `PostController` durch einen
View des Themes `classic` ersetzen, sollte die neue Datei unter 
`WebVerzeichnis/themes/classic/views/post/create.php` abgelegt werden.

Für die View-Dateien eines Controllers, der in einem
[Modul](/doc/guide/basics.module) enthalten ist, sollte die entsprechende
View-Datei des Themes ebenfalls unterhalb des `views`-Verzeichnisses abgelegt
werden. Wenn der genannte `PostController` zum Beispiel in einem Modul namens
`forum` enthalten ist, sollte die View-Datei für `create` unter
`WebVerzeichnis/themes/classic/views/forum/post/create.php` abgespeichert
werden. Falls das
`forum`-Modul selbst wiederum als verschachteltes Modul in einem Modul namens 
`support` enthalten ist, sollte die View-Datei unter
`WebVerzeichnis/themes/classic/views/support/forum/post/create.php` liegen.

> Note|Hinweis: Da das `views`-Verzeichnis sicherheitskritische Daten enthalten
könnte, sollten Sie dafür sorgen, dass es nicht vom Web aus zugänglich ist. 

Beim Aufruf von [render|CController::render] oder
[renderPartial|CController::renderPartial] zum Anzeigen eines Views,
werden die entsprechenden View- und Layout-Dateien im Verzeichnis des gerade 
aktiven Themes gesucht und, falls dort vorgefunden, zum Rendern verwendet.
Falls nicht, wird in den üblichen Verzeichnissen gesucht, wie mit
[viewPath|CController::viewPath] und [layoutPath|CWebApplication::layoutPath]
vorgegeben. 

> Tip|Tipp: Innerhalb eines Theme-Views muss evtl. öfter auf andere
> Ressource-Dateien des Theme verlinkt werden. Zum Beispiel um eine
> Bilddatei im Ordner `images` des Themes anzuzeigen. Mit Hilfe der
> Eigenschaft [baseUrl|CTheme::baseUrl] des aktuellen Themes kann man die
> Bild-URL wie folgt zusammenbauen:
>
> ~~~
> [php]
> Yii::app()->theme->baseUrl . '/images/BildName.gif'
> ~~~

Nachfolgend ein Beispiel für die Verzeichnisstruktur einer Anwendung mit den
beiden Themes `basic` und `fancy`.

~~~
WebVerzeichnis/
	assets
	protected/
		.htaccess
		components/
		controllers/
		models/
		views/
			layouts/
				main.php
			site/
				index.php
	themes/
		basic/
			views/
				.htaccess
				layouts/
					main.php
				site/
					index.php
		fancy/
			views/
				.htaccess
				layouts/
					main.php
				site/
					index.php
~~~

Konfiguriert man die Anwendung so:

~~~
[php]
return array(
	'theme'=>'basic',
	......
);
~~~

wird das Theme `basic` verwendet. Das bedeutet, dass das Layout in
`themes/basic/views/layouts` der Index-View in `themes/basics/views/site` 
verwendet wird. Falls Yii dort keine Viewdatei findet, verwendet es die
Datei in `protected/views`.


Themes für Widgets
------------------

Ab Version 1.1.5 können Themes auch für Widget-Views verwendet werden. Rendert
man einen Widgetview mit [CWidget::render()], sucht Yii auch im
Themeverzeichnis nach einer entsprechenden Datei.

Möchte man den View `xyz` eines Widgets `Foo` in ein Theme einbeziehen, so
legt man zunächst einen Ordner `Foo` (dem Namen des Widgets) im aktiven
Themeverzeichnis an. Falls die Widgetklasse Namespaces (seit PHP 5.3.0)
verwendet, wie z.B. `\app\widgets\Foo`, sollte dieser Ordner `app_widgets_Foo`
heißen. Man ersetzt in diesem Fall also die Namespace-Separatoren mit einem
Unterstrich.

Jetzt legt man die Viewdatei `xyz.php` in diesem Verzeichnis ab. Falls das
aktuelle Theme `basic`ist, sollte man also nun eine Datei 
`themes/basic/views/Foo/xyz.php` haben, die dann vom Widget
verwendet wird, um den ursprünglichen View zu ersetzen.


Widgets global anpassen
-----------------------

> Note|Hinweis: Dieses Feature steht seit Version 1.1.3 zur Verfügung.

Beim Arbeiten mit Widgets aus Yii oder von anderen Anbietern muss man diese
häufig an spezifische Bedürfnisse anpassen. Etwa wenn der Wert von
[CLinkPager::maxButtonCount] nicht 10 (Standard), sondern 5 sein soll. 
Man kann diesen Startwert zwar beim Aufruf von [CBaseController::widget]
setzen, allerdings muss das dann wiederholt an jeder Stelle geschehen, an der
[CLinkPager] verwendet wird.

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
    'maxButtonCount'=>5,
    'cssFile'=>false,
));
~~~

Nutzt man das Feature für die globale Anpassung von Widgets, kann man diese Startwerte
auch zentral in der Konfiguration setzen. Spezifische Widget-Anpassungen
lassen sich so wesentlich einfacher verwalten.

Dazu muss eine [widgetFactory|CWebApplication::widgetFactory] wie folgt
konfiguriert werden:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'widgets'=>array(
                'CLinkPager'=>array(
                    'maxButtonCount'=>5,
                    'cssFile'=>false,
                ),
                'CJuiDatePicker'=>array(
                    'language'=>'ru',
                ),
            ),
        ),
    ),
);
~~~

Damit werden die Starteigenschaften von [CLinkPager] und [CJuiDatePicker] über die
[CWidgetFactory::widgets]-Eigenschaft bestimmt. Wie Sie sehen, wird dazu ein
Array mit dem Namen der Widgetklasse als Schlüssel und ein Array mit
Startwerten als Wert verwendet.

Jedesmal wenn nun ein [CLinkPager] in einem View erstellt wird, werden die
obigen Eigenschaftswerte zugewiesen. Der Code dafür reduziert sich somit auf:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
));
~~~

Die Eigenschaften können bei Bedarf immer noch überschrieben werden. Soll zum
Beispiel in einem speziellen View `maxButtonCount` stattdessen auf 2 gesetzt
werden, kann das wie folgt erreicht werden:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
	'maxButtonCount'=>2,
));
~~~


Skin
----

Während man mit Themes das Aussehen von Views schnell verändern können,
erlauben Skins (sinngem.: Haut, Verkleidung), das Aussehen von
[Widgets](/doc/guide/basics.view#widget) systematisch anzupassen.

Eine Skin ist ein Array von Name-Wert-Paaren, die zum Initalisieren der
Eigenschaften eines Widgets verwendet werden können. Eine Skin gehört dabei zu
einer Widgetklasse. Und eine Widgetklasse kann mehrere Skins haben, die über
ihren Namen identifiziert werden können. So kann es zum Beispiel eine Skin
für das [CLinkPager]-Widget namens `classic` geben.

Um Skins verwenden zu können, richtet man in der Anwendungskonfiguration die
Komponente [widgetFactory|CWebApplication::widgetFactory] (sinngem.: Widget-Fabrik) 
ein:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'enableSkin'=>true,
        ),
    ),
);
~~~

Bitte beachten Sie, dass man Skins für Widgets vor Version 1.1.3 so
konfigurieren musste:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'class'=>'CWidgetFactory',
        ),
    ),
);
~~~

Danach müssen die benötigten Skins erstellt werden. Alle Skins, die zu einem bestimmten
Widget gehören, werden gemeinsam in einem PHP-Script mit dem Namen der
Widgetklasse gespeichert. Skindateien werden standardmäßig unter
`protected/views/skins` abgelegt. Wenn Sie dieses Verzeichnis ändern möchten,
können Sie die Eigenschaft `skinPath` der Komponente `widgetFactory` anpassen.
Man kann in diesem Verzeichnis zum Beispiel die Datei `CLinkPager.php` mit
folgendem Inhalt anlegen:

~~~
[php]
<?php
return array(
    'default'=>array(
        'nextPageLabel'=>'&gt;&gt;',
        'prevPageLabel'=>'&lt;&lt;',
    ),
    'classic'=>array(
        'header'=>'',
        'maxButtonCount'=>5,
    ),
);
~~~

Damit werden zwei Skins für das [CLinkPager]-Widget definiert: `default` und
`classic`. Erstere wird für alle [CLinkPager]-Widgets verwendet, bei denen
keine explizite `skin`-Eigenschaft gesetzt wurde. Die zweite wird für jene
[CLinkPager] verwendet, bei denen `skin` auf `classic` gesetzt wurde.
Der erste Pager im folgenden Beispiel verwendet also die `default`-Skin, der
Zweite die Skin `classic`:

~~~
[php]
<?php $this->widget('CLinkPager'); ?>

<?php $this->widget('CLinkPager', array('skin'=>'classic')); ?>
~~~

Eigenschaften, die direkt beim Anzeigen eines Widgets übergeben werden haben
Vorrang vor den in der Skin definierten Eigenschaften. Der folgende
Viewcode erzeugt zum Beispiel einen Pager mit den Starteigenschaften
`array('header'=>'', 'maxButtonCount'=>6, 'cssFile'=>false)`, also den
überlagerten Eigenschaften aus dem View und der `classic`-Skin.

~~~
[php]
<?php $this->widget('CLinkPager', array(
    'skin'=>'classic',
    'maxButtonCount'=>6,
    'cssFile'=>false,
)); ?>
~~~

Beachten Sie, dass der Einsatz von Skins keine Themes voraussetzt. Ist
allerdings ein Theme aktiv, sucht Yii auch im Verzeichnis `skins` im
View-Verzeichnis des Themes nach Skins (z.B.
`WebVerzeichnis/themes/classic/views/skins`). Falls sowohl im Theme als auch
im Hauptverzeichnis eine Skindatei mit dem gleichen Namen existiert, hat die
Theme-Skin Vorrang.

Falls ein Widget eine Skin verwendet, die es nicht gibt, erzeugt Yii das
Widget wie gewohnt ohne Fehler.

> Info|Info: Die Verwendung von Skins kann die Performance negativ beeinflussen,
> da Yii beim ersten Einsatz eines Widgets nach Skindateien suchen muss.

Skins ähneln dem Feature zur globalen Anpassung von Widgets. Die wesentlichen
Unterschiede sind:

   - Eine Skin ist eher zur Anpassung der Darstellungseigenschaften gedacht
   - Ein Widget kan mehrere Skins haben
   - Eine Skin kann mit einem Theme versehen werden
   - Skins beeinflußen die Leistung mehr, als Widgets global anzupassen

<div class="revision">$Id: topics.theming.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
