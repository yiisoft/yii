Konsolenanwendungen
===================

Konsolenanwendungen werden hauptsächlich für Offline-Arbeiten eingesetzt, 
z.B. zur Codegenerierung, um Suchindizes zu Erstellen oder auch um Emails zu
versenden. Auch für sie bietet Yii ein objektorientiertes Framework.
Damit können solche Anwendungen auf die selben Ressourcen (z.B. DB-Verbindungen) 
zugreifen, wie die Webapplikation.

Überblick
---------

Verschiedene Konsolenarbeiten werden in Yii jeweils durch einen 
[Befehl|CConsoleCommand] repräsentiert. Um einen Konsolenbefehl anzulegen, wird die Klasse
[CConsoleCommand] erweitert.

Hat man eine Webapplikation mit `yiic webapp` angelegt, so befinden sich zwei
Dateien im `protected`-Verzeichnis:

* `yiic`: Eine ausführbare Scriptdatei für Linux/Unix;
* `yiic.bat`: Eine Batch-Datei für Windows.

Gibt man in einem Kommandozeilenfenster 

~~~
cd protected
yiic help
~~~

ein, so wird eine Liste der verfügbaren Befehle angezeigt. Standardmäßig
enthält diese Liste Befehle des Yii-Frameworks (sog. **Systembefehle**) sowie
selbstgeschriebene Befehle, die auf eine bestimmte Anwendung zugeschnitten
wurden (sog. **Anwenderbefehle**).

Zu vielen Befehlen kann man eine Hilfe anzeigen

~~~
yiic help <Befehlsname>
~~~

oder ihn wie folgt ausführen:

~~~
yiic <Befehlsname> [Parameter...]
~~~



Befehle erstellen
-----------------

Konsolenbefehle werden in Form einer Klasse in dem Verzeichnis abgelegt, 
das unter [CConsoleApplication::commandPath] konfiguriert wurde. Standardmäßig
verweist dieser Pfad auf `protected/commands`.

Eine Klasse für einen Konsolenbefehl muss von [CConsoleCommand]
abgeleitetet werden. Der Name dieser Klasse muss dem Format `XyzCommand`
entsprechen, wobei `Xyz` dem großgeschriebenen Befehlsnamen entspricht. 
Für einen `sitemap`-Befehl müsste die Klasse also `SitemapCommand` heißen.
Bei der Bezeichnung von Konsolenbefehlen ist die Groß-/Kleinschreibung zu 
berücksichtigen.

> Tip|Tipp: Verwendet man [CConsoleApplication::commandMap], können 
> Kommandoklassen auch anderen Namenskonventionen folgen und an anderen Orten liegen.

Um einen neuen Befehl anzulegen, genügt es meistens, [CConsoleCommand::run()]
zu überschreiben oder eine bzw. mehrere Actions (siehe unten) zu erstellen.

Führt man einen Befehl aus, ruft die Konsolenanwendung dann [CConsoleCommand::run()] 
mit den übergebenen Parametern gemäß folgender Signatur auf:

~~~
[php]
public function run($args) { ... }
~~~

wobei `$args` die Aufrufparameter enthält.

Innerhalb eines Befehls kann das Anwendungsobjekt wie gewohnt über
`Yii::app()` angesprochen werden. Auch die konfigurierten Komponenten, wie
etwa die Datenbankverbindung, stehen wie bei einer Webanwendung zur Verfügung 
(z.B. `Yii::app()->db`).

> Info: Seit Version 1.1.1 ermöglicht Yii auch globale Befehle, die von
allen Yii-Anwendungen auf einem Server verwendet werden können. Dazu muss die
Umgebungsvariable `YII_CONSOLE_COMMANDS` auf ein existierendes Verzeichnis 
mit Befehlsklassen verweisen. Alle Befehle in diesem Verzeichnis
stehen dann bei jedem Aufruf von `yiic` zur Verfügung.


Actions eines Konsolenbefehls
-----------------------------

> Note|Hinweis: Actions stehen erst seit Version 1.1.5 in Konsolenbefehlen zur
> Verfügung.

Oft müssen an einen Konsolenbefehl weitere Parameter übergeben werden, manche
davon als Pflichtangaben, manche optional. Außerdem kann ein Befehl aus
verschiedene Teilaufgaben bestehen, die man auch einzeln aufrufen können soll.
Das lässt sich sehr einfach über (Befehls-)Actions bewerkstelligen.

Eine solche Befehlsaction entspricht in der Befehlsklasse einer Methode, 
deren Name dem Format `actionXyz` entspricht. `Xyz` entspricht dem
großgeschriebenen Actionnamen. Eine Methode `actionIndex` definiert demzufolge
eine Action namens `index`.

Um eine solche Action auszuführen, ruft man den Befehl in folgender Form auf:

~~~
yiic <Befehlsname> <Actionname> --Option1=Wert1 --Option2=Wert2
~~~

Die angegebenen Optionswerte werden als Aufrufparameter an die Actionmethode
übergeben. Der Wert der Option `xyz` wird dabei als `$xyz` an die Methode
gesendet. Bei dieser Befehlsklasse zum Beispiel,

~~~
[php]
class SitemapCommand extends CConsoleCommand
{
    public function actionIndex($typ, $limit=5) { ... }
    public function actionInit() { ... }
}
~~~

führen letztendlich alle folgenden Kommandozeilenbefehle zu einem Aufruf von `actionIndex('News',5)`:

~~~
yiic sitemap index --typ=News --limit=5

// $limit wird mit Vorgabewert belegt
yiic sitemap index --typ=News

// $limit wird mit Vorgabewert belegt
// Da 'index' die Standardaction ist, kann der Actioname auch weggelasssen werden
yiic sitemap --typ=News

// Die Reihenfolge der Optionen spielt keine Rolle
yiic sitemap index --limit=5 --typ=News
~~~

Wird eine Option ohne Wert angegeben (z.B. `--typ` statt `--typ=News`), so wird der
Parameter auf (boolean) `true` gesetzt.

> Note|Hinweis: Alternative Optionsformate wie z.B. `--typ News` oder `-t News` werden nicht unterstützt.

Ein Parameterwert kann als Array angegeben werden indem er in der Funktion als Array deklariert wird:

~~~
[php]
public function actionIndex(array $typen) { ... }
~~~

Um den Arraywert anzugeben, wird die entsprechende Option beim Aufruf einfach mehrfach aufgeführt:

~~~
yiic sitemap index --typen=News --typen=Article
~~~

Dieser Befehl wird in den Aufruf `actionIndex(array('News','Article'))` übersetzt.

Seit Version 1.1.6 unterstützt Yii auch "anonyme" Parameter, sowie globale
Optionen. 

Als "anonyme" Parameter bezeichnet man jene, die nicht obigem Optionsformat
entsprechen. Im Kommando `yiic sitemap index --limit=5 News` wäre `News` ein
solcher Parameter, während `limit` gemäß obiger Definition den Wert 5 annimmt.

Um solche Parameter verarbeiten zu können, muss eine Action ein Argument
namens `$args` wie folgt definieren:

~~~
[php]
public function actionIndex($limit=10, $args=array()) {...}
~~~

Im Array `$args` sind dann beim Aufruf alle angegebenen anonymen Parameter enthalten.

Eine globale Option bezeichnet jene Optionen, die von allen Actions in einer
Befehlsklasse verstanden werden. Man denke z.B. an eine Option `verbose`, mit
der man detaillierte Zusatzinformationen für jeden Befehl aktivieren können
soll. Man könnte dazu jeder Actionmethode einen Parameter $verbose verpassen.
Einfacher geht es aber, indem man ihn als **öffentliche Eigenschaft** der
Befehlsklasse hinzufügt und `verbose` so zu einer globalen Option macht:

~~~
[php]
class SitemapCommand extends CConsoleCommand
{
	public $verbose=false;
	public function actionIndex($type) {...}
}
~~~

Jetzt akzeptieren alle (Teil-)Befehle die zusätzliche Option `verbose`:

~~~
yiic sitemap index --verbose=1 --type=News
~~~

Anpassen von Konsolenanwendungen
--------------------------------

Wenn man eine Anwendung wie üblich mit `yiic webapp` erstellt hat, befindet
sich die Konfigurationsdatei für Konsolenanwendungen in
`protected/config/console.php`. Genau wie bei der Konfiguration für die
Webanwendung, liefert auch diese PHP-Datei ein Array mit den zu
konfigurierenden Eigenschaftswerten für das Anwendungsobjekt [CConsoleApplication]
zurück. 

Da auch Konsolenanwendungen meist auf die selben Komponenten wie die
Webanwendung zugreifen müssen (z.B. die DB-Verbindung), können diese wie folgt
konfiguriert werden:

~~~
[php]
return array(
	......
	'components'=>array(
		'db'=>array(
			......
		),
	),
);
~~~

Man sieht, das Format ähnelt der Konfiguration für die Webanwendung. Das liegt
daran, dass [CConsoleApplication] und [CWebApplication] von der selben
Basisklasse abgeleitet wurden.

<div class="revision">$Id: topics.console.txt 2867 2011-01-15 10:22:03Z haertl.mike $</div>
