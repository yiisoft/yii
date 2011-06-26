Automatisierte Codegenerierung
==============================

Seit Version 1.1.2 ist Yii mit dem webbasierten Codegenerator *Gii*
ausgestattet. Er ersetzt das frühere `yiic shell`, das an der Kommandozeile
verwendet wurde. In diesem Abschnitt erklären wir, wie man Gii verwendet bzw.
erweitert, um noch ergiebiger Entwickeln zu können.

Gii verwenden
-------------

Gii ist ein Modul und kann daher nur innerhalb einer bestehenden Yii-Anwendung
eingesetzt werden. Um Gii zu verwenden, muss zunächst die Konfiguration wie
folgt angepasst werden:

~~~
[php]
return array(
	......
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Setzen Sie hier ein Passwort ein',
			// 'ipFilters'=>array(...eine Liste von IPs...),
			// 'newFileMode'=>0666,
			// 'newDirMode'=>0777,
		),
	),
);
~~~

Damit wird ein Modul `gii` mit der Klasse [GiiModule] konfiguriert und das
Passwort für den Zugriff gesetzt.

Aus Sicherheitsgründen erlaubt Gii standardmäßig nur den Zugriff von
Localhost. Soll Gii auch von anderen vertrauenswürdigen Rechnern aus
verwendet werden, kann dazu die Eigenschaft [GiiModule::ipFilters] angepasst
werden.

Da Gii neue Codedateien in der bestehenden Anwendung erzeugen soll, muss
sichergestellt werden, dass der Webserverprozess auch die nötigen
Schreibrechte dazu besitzt. Mit [GiiModule::newFileMode] und
[GiiModule::newDirMode] können die Zugriffsrechte für neu erstellte
Dateien definiert werden.

> Note|Hinweis: Gii ist in erster Linie ein Entwicklungswerkzeug. Es sollte
> daher nur auf der Entwicklungsmaschine installiert werden. Da man damit
> PHP-Scripte innerhalb einer Anwendung erzeugen kann, sollte ein besonderes
> Augenmerk auf die Sicherheitsmaßnahmen von Gii gelegt werden (z.B. Passwort,
> IP-Filter).

Angenommen, die bestehende Anwendung liegt unter
`http://hostname/pfad/zu/index.php`, so kann Gii nun über
die URL `http://hostname/pfad/zu/index.php?r=gii` aufgerufen werden.

Falls die Anwendung das `path`-Format für URLs verwendet (siehe
[URL-Management](/doc/guide/topics.url)), ist Gii über die URL 
`http://hostname/pfad/zu/index.php/gii` erreichbar. Eventuell muss auch folgende
URL-Regel an den Anfang bestehender Regeln gesetzt werden:

~~~
[php]
'components'=>array(
	......
	'urlManager'=>array(
		'urlFormat'=>'path',
		'rules'=>array(
			'gii'=>'gii',
			'gii/<controller:\w+>'=>'gii/<controller>',
			'gii/<controller:\w+>/<action:\w+>'=>'gii/<controller>/<action>',
			...bestehende Regeln...
		),
	),
)
~~~

Gii enthält bereits eine Reihe von Codegeneratoren für verschiedene Codetypen.
Der Controllergenerator erzeugt z.B. eine Controllerklasse inklusive einiger
Viewscripte, der Modelgenerator kann ActiveRecord-Klassen für
Datenbanktabellen generieren.

Ein Generator wird prinzipiell wie folgt verwendet:

1. Generatorseite aufrufen
2. Parameter für die Generierung eintragen, z.B. die Modul-ID beim Modulgenerator.
3. `Preview`-Button klicken, um die Liste der zu generierenden Dateien und
deren Inhalt (per Klick) anzuzeigen.
4. `Generate`-Button klicken, um die Dateien tatsächlich anzulegen
5. Protokoll der Codegenerierung untersuchen


Gii erweitern
-------------

Obwohl die mitgelieferten Codegeneratorn bereits sehr leistungsstarken Code
erzeugen, kann man diese dem eigenen Geschmack und Bedarf anpassen bzw. durch neue
Generatoren erweitern. Zum Beispiel wenn der generierte Code dem eigenen
Programmierstil entsprechen oder auch mehrere Sprachen unterstützen soll. 
Gii macht solche Anpassungen sehr einfach.

Gii kann auf zwei Arten erweitert werden: Entweder man passt die Codevorlagen
(engl.: templates) der bestehenden Codegeneratoren an oder man schreibt sich gleich einen neuen 
Codegenerator.

###Struktur eines Codegenerators

Die Dateien eines Codegenerators liegen in einem Verzeichnis mit dem Namen des
Generators. Für gewöhnlich enthält ein Generator:

~~~
model/                       Stammverzeichnis des Modelgenerators
   ModelCode.php             Codemodel für die Codegenerierung
   ModelGenerator.php        Controller für die Codegenerierung
   views/                    Viewscripte des Generators
      index.php              Standard-Viewscript
   templates/                Sets von Codevorlagen
      default/               'Default'- (bzw. Standard-)Vorlagenset
         model.php           Codevorlage zum Generieren einer Modelklasse
~~~

###Suchpfad des Generators

Gii sucht in den per [GiiModule::generatorPaths] konfigurierten Verzeichnissen
nach verfügbaren Generatoren. Diese Eigenschaft kann in der
Anwendungskonfiguraion angepasst werden:

~~~
[php]
return array(
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'generatorPaths'=>array(
				'application.gii',   // Ein Pfadalias
			),
		),
	),
);
~~~

Damit wird Gii angewiesen, zusätzlich zu seinem Standardverzeichnis in
`system.gii.generators` auch in `application.gii` nach Generatoren zu suchen.

Falls in mehreren Suchpfaden der selbe Generatorname auftaucht, wird
derjeninge aus dem zuerst in [GiiModule::generatorPaths] definierten
Verzeichnis verwendet.

###Anpassen von Codevorlagen

Das ist die einfachste und gängigste Variante, wie man Gii erweitern kann. Wir
zeigen das am besten an einem Beispiel. Nehmen wir dazu an, Sie würden
gerne den erzeugten Code des Modelgenerators anpassen.

Legen Sie dazu zunächst das Verzeichnis
`protected/gii/model/templates/kompakt` an. Mit `model` zeigen wir an, dass
der standardmäßige Modelgenerator *überschrieben* werden soll und mit
`templates/kompakt` wird ein neues Vorlagenset namens `kompakt` definiert.

Fügen Sie in der Konfiguration wie oben gezeigt `application.gii` zu
[GiiModule::generatorPaths] hinzu.

Wenn Sie jetzt die Codegeneratorseite aufrufen und auf `Code Template`
klicken, sollte `kompakt` in der Auswahlliste aufgeführt werden. Wenn Sie diesen
Eintrag auswählen, erscheint allerdings zunächst noch ein Fehler. Es fehlt ja auch
noch die Codevorlagendatei in unserem Vorlagenset `kompakt`.

Kopieren Sie also die Datei
`framework/gii/generators/model/templates/default/model.php` nach
`protected/gii/model/templates/kompakt`. Wenn Sie es jetzt nochmal probieren,
ist der Fehler verschwunden. Allerdings sieht der generierte Code immer noch
genauso aus wie vorher.

Jetzt geht es an die eigentliche Anpassung. Öffnen Sie dazu die Datei
`protected/gii/model/templates/compact/model.php`. Wie erwähnt wird diese
Datei wie ein Viewscript verwendet und kann daher PHP-Ausdrücke und -Befehle
enthalten. Ändern wir also die `attributeLabels()`-Methode einmal so ab, dass
`Yii::t()` zur Übersetzung der Labels verwendet wird:

~~~
[php]
public function attributeLabels()
{
	return array(
<?php foreach($labels as $name=>$label): ?>
			<?php echo "'$name' => Yii::t('application', '$label'),\n"; ?>
<?php endforeach; ?>
	);
}
~~~

In jeder Codevorlage können einige vordefinierte Variablen verwendet werden,
wie etwa `$labels` im Beispiel. Diese Variablen werden vom entsprechenden
Codegenerator bereitgestellt und können sich je nach Generator unterscheiden.
Lesen Sie daher die Beschreibung in den Standardvorlagen genau durch.


###Neue Generatoren erstellen

In diesem Abschnitt zeigen wir, wie Sie einen neuen Generator für
eine Widgetklasse erstellen können.

Legen Sie dazu zuerst das Verzeichnis `protected/gii/widget` an. Folgende
Dateien werden dort abgelegt:

* `WidgetGenerator.php`: Die Controllerklasse `WidgetGenerator`. Sie bildet den Startpunkt des Widgetgenerators.
* `WidgetCode.php`: Enthält die Modelklasse `WidgetCode` mit der Hauptlogik des Generators.
* `views/index.php`: Das Viewscript mit dem Formular des Codegenerators
* `templates/default/widget.php`: Die Standardvorlage für die erzeugte Widgetklasse.


#### `WidgetGenerator.php` anlegen

Die Datei `WidgetGenerator.php` ist sehr einfach und enthält nur diesen Code.

~~~
[php]
class WidgetGenerator extends CCodeGenerator
{
	public $codeModel='application.gii.widget.WidgetCode';
}
~~~

Der Generator soll also die Modelklasse `application.gii.widget.WidgetCode`
verwenden. `WidgetGenerator` erweitert [CCodeGenerator]. Darin sind viele
Funktionen und Controlleractions zur Koordinierung der Codegenerierung
enthalten.


#### `WidgetCode.php` anlegen

Diese Datei enthält die Modelklasse `WidgetCode` mit der eigentlichen Logik,
um eine Widgetklasse aus den Benutzerangaben zu erzeugen. In diesem Beispiel
nehmen wir an, dass der Benutzer nur den Klassennamen des Widgets eingeben
kann:

~~~
[php]
class WidgetCode extends CCodeModel
{
	public $className;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('className', 'required'),
			array('className', 'match', 'pattern'=>'/^\w+$/'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'className'=>'Klassenname des Widgets',
		));
	}

	public function prepare()
	{
		$path=Yii::getPathOfAlias('application.components.' . $this->className) . '.php';
		$code=$this->render($this->templatepath.'/widget.php');

		$this->files[]=new CCodeFile($path, $code);
	}
}
~~~

Die Klasse `WidgetCode` erweitert [CCodeModel]. Wie in einer normalen
Modelklasse können `rules()` und `attributeLabels()` definiert werden, um
Benutzereingaben zu Validieren bzw. die Formularlabels anzupassen. Beachten
Sie, dass array_merge() verwendet wird, da [CCodeModel] bereits einige Regeln
und Label festlegt.

Die `prepare()`-Methode bereitet den zu generierenden Code vor, indem sie eine
Liste von [CCodeFile]-Objekten anlegt. Jedes dieser Objekte steht für eine zu
erstellende Codedatei. In unserem Fall ist das nur eine Widgetdatei, die in
`protected/components` erstellt werden soll. Der eigentliche Code dieser Datei 
wird mit [CCodeFile::render] erzeugt. Sie bindet die Codevorlage als
PHP-Script ein und liefert den dort ausgegebenen Inhalt zurück.



#### `views/index.php` erstellen

Nachdem Controller (`WidgetGenerator`) und Model (`WidgetCode`) bereitstehen,
ist es an der Zeit, das Viewscript `views/index.php` anzulegen:

~~~
[php]
<h1>Widget Generator</h1>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'className'); ?>
		<?php echo $form->textField($model,'className',array('size'=>65)); ?>
		<div class="tooltip">
			Der Klassenname darf nur Buchstaben/Zahlen enthalten.
		</div>
		<?php echo $form->error($model,'className'); ?>
	</div>

<?php $this->endWidget(); ?>
~~~

Es zeigt ein Formular mit Hilfe des [CCodeForm]-Widgets an, das ein
Eingabefeld für das Attribut `className` in `WidgetCode` enthält. 

Bei diesem Formular kann man sich zwei nützliche Features von [CCodeForm]
zunutze machen: Tooltips und die "sticky"-Option (klebrig) für Eingabefelder.

Wenn Sie den Codegenerator schon ausprobiert haben, werden Sie bemerkt haben,
dass ein kleiner Hinweis (Tooltip) neben dem Eingabefeld erscheint, sobald sie
den Fokus darauf setzen. Dazu müssen Sie den Hinweis nur in ein `div` mit
der CSS-Klasse `tooltip` einfügen.

Einige Eingabefelder sollen eventuell auch gleich den letzten eingegebenen
Wert enthalten, damit dieser nicht jedesmal neu eingetippt werden muss, wenn
der Codegenerator ausgeführt wird. Ein Beispiel dafür ist das Eingabefeld für
den Namen der Controllerklasse im mitgelieferten Controllergenerator. Diese
"sticky"-Felder werden zunächst als markierter statischer Text angezeigt. Sobald
man sie anklickt, verwandeln sie sich in ein Eingabefeld.

Um ein Feld als "sticky" zu definieren sind zwei Sachen zu beachten:

Zunächst muss das entsprechende Attribut mit der Validierungsregel `sticky`
versehen werden. Im Controllergenerator werden so z.B. die Attribute
`baseClass` und `action` als "sticky" markiert:

~~~
[php]
public function rules()
{
	return array_merge(parent::rules(), array(
		......
		array('baseClass, actions', 'sticky'),
	));
}
~~~

Außerdem muss man dem `div`, der das Eingabefeld umschließt, die Klasse
`sticky` hinzufügen:

~~~
[php]
<div class="row sticky">
	...hier steht das Eingabefeld...
</div>
~~~

#### `templates/default/widget.php` anlegen

Schließlich fehlt noch die Codevorlage `templates/default/widget.php`. Wie
bereits beschrieben, handelt es sich dabei um eine Viewdatei, die
PHP-Ausdrücke und -Befehle enthalten kann. In dieser Vorlage kann mit `$this`
auf das entsprechende Codemodelobjekct zugegriffen werden. In unserem Beispiel
ist das das `WidgetModel`-Objekt. Der eingegebene Klassenname kann daher mit
`$this->className` bezogen werden:

~~~
[php]
<?php echo '<?php'; ?>

class <?php echo $this->className; ?> extends CWidget
{
	public function run()
	{

	}
}
~~~

Damit ist der neue Codegenerator fertig und man kann ihn über die URL
`http://hostname/path/to/index.php?r=gii/widget` aufrufen.

<div class="revision">$Id: topics.gii.txt 3223 2011-05-17 23:02:50Z alexander.makarow $</div>
