Verwenden von Erweiterungen
===========================

Um eine Erweiterung (engl.: Extension) einzusetzen, sind normalerweise
folgende Schritte erforderlich:

  1. Erweiterung aus dem [Extension
  repository](http://www.yiiframework.com/extensions/) von Yii herunterladen
  2. Ins Unterverzeichnis `extensions/xyz` des 
  [Anwendungsverzeichnisses](/doc/guide/basics.application#application-base-directory)
  entpacken, wobei `xyz` für den Namen der Erweiterung steht.
  3. Importieren, Konfigurieren und Verwenden der Erweiterung

Jede Erweiterung hat einen eindeutigen Namen, um sie klar von anderen
Erweiterungen zu unterscheiden. Heißt eine Erweiterung `xyz`, kann
man über den Pfadalias `ext.xyz` von überallher auf Dateien dieser Erweiterung
zugreifen.

Jede Erweiterung muss anders importiert, konfiguriert bzw. angewendet werden.
Wir zeigen hier einige typische Anwendungsfälle für die in der 
[Übersicht](/doc/guide/extension.overview) beschriebenen Kategorien.

Zii Erweiterungen
-----------------

Bevor wir auf andere Erweiterungen eingehen, möchten wir an dieser Stelle kurz
die Zii-Erweiterungsbibliothek vorstellen. Dabei handelt es sich um eine Reihe von
Erweiterungen, die vom Yii-Entwicklerteam erstellt wurden und die seit Version
1.1.0 in jedem Release enthalten sind. 

Um eine Zii-Erweiterung zu verwenden, muss die entsprechende Klasse mit einem
Pfadalias der Form `zii.pfad.zur.Klasse` referenziert werden. Der Rootalias
`zii` wird hierbei von Yii bereits vorbelegt. Er bezieht sich auf das
Basisverzeichnis der Zii-Bibliothek. Ein [CGridView] würde man
z.B. so einsetzen:

~~~
[php]
$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
));
~~~

Anwendungskomponente
--------------------

Um eine [Anwendungskomponente](/doc/guide/basics.application#application-component) zu
verwenden, muss in der [Konfiguration](/doc/guide/basics.application#application-configuration) 
ein Eintrag in der `components`-Eigenschaft hinzugefügt werden:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'ext.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // Andere Konfigurationen für Komponenten
    ),
);
~~~

Nun kann in der gesamten Anwendung mit `Yii::app()->xyz` auf die
Komponente zugegriffen werden. Sie wird `lazy` ("faul", also beim ersten
Zugriff darauf) erzeugt, es sei denn, sie wird in der Eigenschaft
`preload` aufgeführt.


Behavior
--------

[Behavior](/doc/guide/basics.component#component-behavior) können mit allen
möglichen Komponenten verwendet werden. Dazu muss das Behavior zunächst
an die gewünschte Komponente angebunden werden. Danach kann man 
Behaviormethoden über die Komponente aufrufen:

~~~
[php]
// $name ist ein eindeutiger Bezeichner des Behaviors in der Komponente
$component->attachBehavior($name,$behavior);
// test() ist eine Methode des Behaviors
$component->test();
~~~

Statt über die `attachBehavior`-Methode wird ein Behavior in der Regel meist 
per [Konfiguration](/doc/guide/basics.application#application-configuration) 
an eine Komponente angebunden, wie hier am Beispiel einer
[Anwendungskomponente](/doc/guide/basics.application#application-component) 
gezeigt:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'behaviors'=>array(
				'xyz'=>array(
					'class'=>'ext.xyz.XyzBehavior',
					'property1'=>'value1',
					'property2'=>'value2',
				),
			),
		),
		//....
	),
);
~~~

Damit wird das Behavoir `xyz` and die `db`-Komponente angebunden.
Dies wird durch Eigenschaft `behaviors` mögliche, die in [CApplicationComponent] 
definiert ist. Übergibt man in dieser Eigenschaft eine Liste von
Behaviorkonfigurationen, bindet die Komponente die entsprechenden
Behaviors beim Initialisieren an.

Da [CController]-, [CFormModel]- und [CActiveModel]-Klassen in der Regel
erweitert werden müssen, können Behavior dort durch Überschreiben der
`behaviors()`-Methode angebunden werden. Beim Initialisieren hängt die Klasse
dann die darin definierten Behaviors automatisch an:

~~~
[php]
public function behaviors()
{
	return array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzBehavior',
			'property1'=>'value1',
			'property2'=>'value2',
		),
	);
}
~~~

Widget
------

[Widgets](/doc/guide/basics.view#widget) werden hauptsächlich in
[Views](/doc/guide/basics.view) verwendet. Eine Widget-Klasse `XyzClass` aus
der `xyz`-Erweiterung kann im View wie folgt verwendet werden:

~~~
[php]
// Widget ohne eingebetteten Inhalt
<?php $this->widget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// Widget mit eingebettetem Inhalt:
<?php $this->beginWidget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...Eingebetteter Inhalt des Widgets...

<?php $this->endWidget(); ?>
~~~

Action
------

Mit [Actions](/doc/guide/basics.controller#action) reagiert ein
[Controller](/doc/guide/basics.controller) auf Requests. Um eine
Actionklasse `XyzClass` aus der Erweiterung `xyz` in einem
Controller zu verwenden, überschreibt man in diesem die Methode
[CController::actions]:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// Andere Actions
		);
	}
}
~~~

Die Action kann dann über die [Route](/doc/guide/basics.controller#route)
`test/xyz` aufgerufen werden.

Filter
------

[Filter](/doc/guide/basics.controller#filter) werden ebenfalls von 
einem [Controller](/doc/guide/basics.controller) verwendet. Sie übernehmen
hauptsächlich die Vor- und Nachbearbeitung eines User-Requests, wenn dieser
von einer [Action](/doc/guide/basics.controller#action) behandelt wird.
Um eine Filterklasse `XyzClass` aus der Erweiterung `xyz` in einem Controller
zu verwenden, überschreibt man die Methode [CController::filters]:

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// Andere Filter
		);
	}
}
~~~

Man kann hierbei auch Plus- und Minusoperatoren verwenden, um den Filter
nur auf bestimmte Actions anzuwenden. Näheres hierzu finden Sie in der 
Dokumentation von [CController].

Controller
----------

Ein [Controller](/doc/guide/basics.controller) stellt eine Reihe von Actions
bereit, die von Benutzern aufgerufen werden können. Um eine
Controllererweiterung zu verwenden, muss man in der 
[Anwendungskonfiguration](/doc/guide/basics.application#application-configuration) 
die Eigenschaft [CWebApplication::controllerMap] anpassen:

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// Andere Controller
	),
);
~~~

Eine Action `a` in diesem Controller kann dann über die
[Route](/doc/guide/basics.controller#route) `xyz/a` aufgerufen werden.

Validator
---------

Validatoren werden hauptsächlich in 
[Modelklassen](/doc/guide/basics.model) (also Ablegern von
[CFormModel] oder [CActiveRecord]) verwendet.
Um einen Validator `XyzClass` aus der Erweiterung `xyz` einzusetzen,
überschreibt man in der abgeleiteten Modelklasse die Methode [CModel::rules]:

~~~
[php]
class MyModel extends CActiveRecord // oder CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// Andere Prüfregeln
		);
	}
}
~~~

Konsolenbefehl
--------------

Eine [Konsolenerweiterung](/doc/guide/topics.console) 
fügt dem `yiic`-Befehl in der Regel ein weiteres Kommando hinzu. 
Indem man die Konfiguration der Konslenanwendung anpasst, kann man 
dann z.B. den Befehl `XyzClass` aus der Erweiterung `xyz` verwenden:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// Andere Kommandos
	),
);
~~~

Für den `yiic`-Befehl steht nun das Kommand `xyz` zur Verfügung.

> Note|Hinweis: Eine Konsolenanwendung verwendet normalerweise eine andere
Konfigurationsdatei als die Webanwendung. Wenn eine Applikation mit dem Befehl
`yiic webapp` erstellt wurde, liegt die Konfigurationsdatei für die
Konsolenanwendung in `protected/yiic` in `protected/config/console.php`, die
der Webanwendung in `protected/config/main.php`.


Module
------

Bitte beachten Sie das Kapitel über
[Module](/doc/guide/basics.module#using-module), um mehr über den Einsatz von
Modulen zu erfahren.

Allgemeine Komponenten
----------------------

Um eine allgemeine [Komponente](/doc/guide/basics.component) einzusetzen,
muss zunächst die Klassendatei eingebunden werden:

~~~
Yii::import('ext.xyz.XyzClass');
~~~

Danach kann man entweder eine Instanz dieser Klasse erzeugen, Eigenschaften
belegen und Methoden aufrufen. Oder man kann die Klasse erweitern und neue
Kindklassen davon erstellen.

<div class="revision">$Id: extension.use.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
