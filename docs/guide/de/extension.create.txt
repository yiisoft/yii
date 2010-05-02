Erstellen von Erweiterungen
===========================

Da Erweiterungen auch von anderen verwendet werden sollen,
ist meist etwas mehr Aufwand bei ihrer Entwicklung nötig.
Hier zunächst einige grundsätzliche Richtlinien:

* Eine Erweiterung sollte in sich geschlossen sein. Das heisst, sie sollte
  möglichst wenig externe Abhängigkeiten aufweisen. Für einen Anwender wäre es
  sehr lästig, wenn er für eine Erweiterung erst zusätzliche Pakete, Klassen
  oder sonstige Dateien installieren müsste.
* Alle Dateien, die zu einer Erweiterung gehören, sollten unterhalb des
  Verzeichnisses mit dem Namen der Erweiterung abgelegt werden.
* Klassen in einer Erweiterung sollte ein Buchstabe (bzw. mehrere Buchstaben)
  vorangestellt sein, um Konflikte mit anderen Erweiterungen zu vermeiden.
* Eine Erweiterung sollte eine detaillierte Installationsanleitung und
  API-Dokumentation enthalten. So können Zeit und Aufwand für andere
  Entwickler minimiert werden, wenn sie die Erweiterung verwenden möchten.
* Eine Erweiterung sollte ein passende Lizenz verwenden. Wenn Ihre Erweiterung
  sowohl in Open-Source- als auch in Closed-Source-Projekten (Projekte mit
  einsehbarem bzw. nicht einsehbarem Quellcode) einsetzbar sein
  soll, könnten Sie Lizenzen wie BSD oder MIT in die engere Wahl ziehen. GPL
  allerdings nicht, da bei ihr auch jeglicher davon abgeleitete Code als
  Open Source zur Verfügung gestellt werden muss.

Im folgenden beschreiben wir jeweils, wie man Erweiterungen entsprechend den
Kategorien in der [Übersicht](/doc/guide/extension.overview) erstellt.
Das selbe gilt aber genauso für Erweiterungen, die Sie nur innerhalb Ihrer
Projekte verwenden möchten.

Anwendungskomponente
--------------------

Eine
[Anwendungskomponente](/doc/guide/basics.application#application-component)
sollte das Interface [IApplicationComponent] implementieren oder die Klasse
[CApplicationComponent] erweitern. Die Methode ist hierbei
[IApplicationComponent::init], in der die Komponente ihre Initialisierung
vornehmen kann. Diese Methode wird aufgerufen, nachdem die Komponente erstellt
und ihre Eigenschaftswerte (entsprechend der
[Anwendungskonfiguration](/doc/guide/basics.application#application-configuration)) 
gesetzt wurden.

Standardmäßig wird eine Komponente nur erstellt und initialisiert, wenn zum
ersten mal auf sie zugegriffen wird. Falls eine Anwendungskomponente
unmittelbar nach der Anwendungsinstanz erzeugt werden muss, sollte der
Anwender sie in der Eigenschaft [CApplication::preload] aufführen.

Behavior
--------

Um ein Behavior zu erstellen, muss das [IBehavior]-Interface implementiert
werden. Bequemerweise enthält Yii bereits die Basisklasse [CBehavior],
die dieses Interface implementiert und einige weitere Komfortfunktionen
anbietet. In Kindklassen muss man so nur noch jene Methoden verwirklichen,
die vom Behavior bereitgestellt werden sollen.

Wenn man Behaviors für [CModel] und [CActiveRecord] entwickeln möchte, kann
man auch [CModelBehavior] bzw. [CActiveRecordBehavior] erweitern. Diese
Basisklassen bieten zusätzliche, an [CModel] bzw. [CActiveRecord] angepasste
Features. Die [CActiveRecordBehavior]-Klasse implementiert z.B. eine Reihe von
Methoden, die auf die Events eines ActiveRecord-Objekts
reagieren. Eine Kindklasse kann diese Methoden überschreiben, um so
angepassten Code einzuschleusen, der im Lebenszyklus eines AR ausgeführt wird.

Der folgende Code zeigt ein Beispiel eines ActiveRecord-Behaviors. Wenn dieses
Behavior an ein AR-Objekt angebunden und dann dessen `save()`-Methode
aufgerufen wird, setzt es automatisch die Attribute `create_time` und
`update_time` auf den aktuellen Zeitstempel.

~~~
[php]
class TimestampBehavior extends CActiveRecordBehavior
{
	public function beforeSave($event)
	{
		if($this->owner->isNewRecord)
			$this->owner->create_time=time();
		else
			$this->owner->update_time=time();
	}
}
~~~


Widget
------

Ein [Widget](/doc/guide/basics.view#widget) sollte [CWidget] oder dessen
Kindklassen erweitern.

Am einfachsten erstellt man ein Widget, indem man ein vorhandenes Widget
erweitert und seine Methoden oder vorgegebenen Eigenschaften überschreibt. 
Wenn Sie z.B. schönere CSS-Stile für [CTabView] verwenden möchten, könnten Sie
dessen [CTabView::cssFile]-Eigenschaft konfigurieren. Sie könnten [CTabView]
aber auch wie im folgenden Beispiel erweitern, so dass diese Eigenschaft
beim Einsatz des Widgets nicht mehr angegeben werden muss.

~~~
[php]
class MyTabView extends CTabView
{
	public function init()
	{
		if($this->cssFile===null)
		{
			$file=dirname(__FILE__).DIRECTORY_SEPARATOR.'tabview.css';
			$this->cssFile=Yii::app()->getAssetManager()->publish($file);
		}
		parent::init();
	}
}
~~~

Hier überschreiben wir die Methode [CWidget::init] und weisen
[CTabView::cssFile] die URL für unsere neue CSS-Datei zu, falls diese
Eigenschaft noch nicht gesetzt war. Die neue CSS-Datei legen wir ebenfalls
im Verzeichnis der Klassendatei `MyTabView` ab, damit beide als
Erweiterung zusammengepackt werden können. Da die CSS-Datei vom Web aus nicht
zugänglich ist, wird sie als Asset veröffentlicht.

Um ein ganz neues Widget zu erstellen, muss man hauptsächlich zwei Methoden
implementieren: [CWidget::init] und [CWidget::run]. Erstere wird aufgerufen,
wenn man ein Widget mit `$this->beginWidget` verwendet, 
die Zweite, beim Aufruf von `$this->endWidget`. Möchte man den 
eingebetteten Inhalt zwischen den beiden Aufrufen abfangen und verarbeiten, 
kann man in [CWidget::init] eine [Ausgabepufferung](http://de3.php.net/manual/de/book.outcontrol.php) 
starten und die gepufferte Ausgabe dann in [CWidget::run] zur weiteren
Bearbeitung auslesen.

Mit einem Widget müssen oft auch CSS-, Javascript- oder anderen
Dateien in eine Seite eingebunden werden. Diese Dateien nennen wir *Assets* 
(sinngem.: Anlage, Zusatz), da sie am Ort der Widgetklasse abgelegt werden und 
normalerweise vom Web aus nicht erreichbar sind. Um diese Dateien zugänglich zu
machen, müssen sie mit dem [CWebApplication::assetManager] (sinngem.:
Anlagenverwalter) veröffentlicht werden, wie im obigen Beispiel gezeigt. Soll
eine veröffentlichte Datei außerdem in die aktuelle Seite eingebunden werden,
muss man sie mit [CClientScript] registrieren:

~~~
[php]
class MyWidget extends CWidget
{
	protected function registerClientScript()
	{
		// ...CSS- oder Javascript-Datei hier veröffentlichen...
		$cs=Yii::app()->clientScript;
		$cs->registerCssFile($cssFile);
		$cs->registerScriptFile($jsFile);
	}
}
~~~

Ein Widget kann auch seine eigenen Viewdateien verwenden. In diesem Fall
legt man ein Verzeichnis namens `views` im Ordner der Widgetklasse an, wo alle 
zugehörigen Viewdateien abgelegt werden.  Ähnlich wie im Controller kann man
dann im Widget `$this->render('ViewName')` benutzen, um einen dieser Views zu rendern.

Action
------

Eine [Action](/doc/guide/basics.controller#action) sollte [CAction] oder deren
Kindklassen erweitern. Die wichtigste zu implementierende Methode für eine
Action ist [IAction::run].

Filter
------
Ein [Filter](/doc/guide/basics.controller#filter) sollte von [CFilter] oder
dessen Kindklassen abgeleitet werden. Die beiden wichtigsten zu
implementierenden Methoden für einen Filter sind [CFilter::preFilter] und
[CFilter::postFilter]. Erstere wird aufgerufen, bevor eine Action ausgeführt
wird, letztere danach.

~~~
[php]
class MyFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// Logik, die vor dem Aufruf der Action ausgeführt wird
		return true; // false, falls die Action nicht ausgeführt werden soll
	}

	protected function postFilter($filterChain)
	{
		// Logik, die nach dem Aufruf der Action ausgeführt wird
	}
}
~~~

Der Parameter `$filterChain` (Filterkette) ist vom Typ [CFilterChain] und
enthält Informationen über die gerade zu filternde Action.


Controller
----------
Wenn ein [Controller](/doc/guide/basics.controller) als Erweiterung
veröffentlicht werden soll, sollte er [CExtController] statt [CController]
erweitern. Und zwar hauptsächlich deshalb, weil ein [CController] seine
Viewdateien unter `application.views.ControllerID` sucht, während
[CExtController] seine Viewdateien im Unterverzeichnis `views` des Ordners
erwartet, der auch die Klassendatei des Controllers enthält. Dadurch wird es
einfacher, den Controller weiterzugeben, da seine Viewdateien bei der
Klassendatei verbleiben.


Validator
---------
Ein Validator sollte [CValidator] erweitern und dessen Methode
[CValidator::validateAttribute] implementieren.

~~~
[php]
class MyValidator extends CValidator
{
	protected function validateAttribute($model,$attribute)
	{
		$value=$model->$attribute;
		if($value has error)
			$model->addError($attribute,$errorMessage);
	}
}
~~~

Konsolenbefehl
--------------
Ein [Konsolenbefehl](/doc/guide/topics.console) sollte [CConsoleCommand]
erweitern und dessen Methode [CConsoleCommand::run] implementieren. Zusätzlich
kann man auch [CConsoleCommand::getHelp] überschreiben, um einen Hilfetext
für den Befehl anzuzeigen.

~~~
[php]
class MyCommand extends CConsoleCommand
{
	public function run($args)
	{
		// $args ist ein Array mit den Kommandozeilenargumenten dieses Befehls
	}

	public function getHelp()
	{
		return 'Anleitung: Wie Sie diesen Befehl verwenden';
	}
}
~~~

Module
------
Für Details zur Erstellung von Modulen, beachten Sie bitte das Kapitel über
[Module](/doc/guide/basics.module#creating-module). 

Allgemeine sollte auch ein Modul in sich geschlossen sein. Sämtliche Dateien, 
die das Modul verwendet(wie z.B. CSS-, Javascript-, Bilddateien) sollten 
im Modul enthalten sein. Und sie sollten vom Modul veröffentlicht werden, 
damit sie vom Web aus erreichbar sind. 

Allgemeine Komponenten
----------------------
Eine allgemeine Erweiterungskomponente zu entwickeln, bedeutet nichts anderes,
als eine Klasse zu schreiben. Auch hier gilt: Die Komponente sollte in sich geschlossen sein, so
dass sie von anderen Entwicklern einfach eingesetzt werden kann.

<div class="revision">$Id: extension.create.txt 1423 2009-09-28 01:54:38Z qiang.xue $</div>
