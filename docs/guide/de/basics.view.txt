View
====

Ein View ist ein PHP-Script, das hauptsächlich Elemente der
Benutzerschnittstelle erzeugt. Es kann durchaus PHP-Befehle
enthalten. Aber es wird dringend empfohlen,
diese Anweisungen sehr einfach zu halten und z.B. keine
Datenmodels zu verändern. Im Sinne einer Trennung von Logik und
Präsentation, sollten größere Logikblöcke besser im
Controller oder im Model untergebracht werden.

Der Name eines Views entspricht seinem Dateinamen.
Der View `edit` bezieht sich somit auf die Datei `edit.php`. Um
einen View zu rendern, rufen Sie [CController::render()] mit dem Namen des
Views auf. Die Methode sucht dann im Verzeichnis
`protected/views/ControllerID` nach der entsprechenden Viewdatei.

Innerhalb eines Views kann über `$this` auf die Controllerinstanz
zugegriffen werden. Mit `$this->propertyName` kann man so im View
jede Eigenschaft des Controllers beziehen. Man nennt das auch
`pull`-Verfahren (ziehen).

Der Controller kann die Daten auch in den View "schieben", also entsprechend
einen `push`-Ansatz (schieben) verfolgen:

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

Die Methode [render()|CController::render] extrahiert den
zweiten Array-Parameter in einzelne Variablen. Im Viewscript stehen
dann die lokalen Variablen `$var1` und `$var2` zur Verfügung.

Layout
------

Ein Layout ist ein spezieller View, der zum "Dekorieren" anderer Views verwendet
wird. Normalerweise besteht er aus den Teilen, die mehrere Views gemeinsam haben.
Ein Layout kann zum Beispiel Header- und
Footerabschnitt enthalten, und den Viewinhalt zwischen den beiden einbinden,

~~~
[php]
......header hier......
<?php echo $content; ?>
......footer hier......
~~~

wobei `$content` das Renderergebnis des eigentlichen Views enthält.

Ein Layout wird immer "übergestülpt", wenn ein View mit [render()|CController::render]
erstellt wird. Per Vorgabe wird dazu die Datei `protected/views/layouts/main.php` als Layout verwendet.
Über [CWebApplication::layout] oder [CController::layout] kann der Pfad
zum Layoutview angepasst werden. Möchte man einen View ohne Layout rendern, kann
man stattdessen [renderPartial()|CController::renderPartial] aufrufen.

Widget
------

Ein Widget (sinngem.: Dings) ist eine Instanz von [CWidget] oder
einer davon abgeleiteten Klasse. Widgets sind Komponenten, die praktisch
ausschließlich zur Anzeige dienen. Für gewöhnlich werden sie in
Views verwendet, um komplexe, in sich geschlossene Bedienelemente
zu erzeugen. Ein Kalenderwidget könnte z.B. ein
ausgefeiltes Kalender-Bedienelement rendern. Widgets tragen so wesentlich zur
Wiederverwendbarkeit von Seitenkomponenten bei.

Um ein Widget zu verwenden, gehen Sie wie folgt im View-Script vor:

~~~
[php]
<?php $this->beginWidget('pfad.zu.Widget-Klasse'); ?>
...Inhalt, der vom Widget erfasst werden kann...
<?php $this->endWidget(); ?>
~~~

oder

~~~
[php]
<?php $this->widget('pfad.zu.Widget-Klasse'); ?>
~~~

Die zweite Form wird verwendet, wenn das Widget keinen eingebetteten Inhalt
benötigt.

Auch Widgets können konfiguriert werden. Dazu übergibt man beim Aufruf von
[CBaseController::beginWidget] oder [CBaseController::widget] ein weiteres
Array mit den gewünschten Konfigurationsparametern.
Bei [CMaskedTextField] kann man so zum Beispiel die zu verwendende Maske
angeben. Wie üblich entsprechen die Schlüssel und Werte des Arrays den
Parameternamen und -werten des Widgets:

~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

Wenn Sie ein neues Widget erstellen möchten, erweitern Sie einfach [CWidget]
und überschreiben die Methoden [init()|CWidget::init] und [run()|CWidget::run].

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// Diese Methode wird bei CController::beginWidget() aufgerufen
	}

	public function run()
	{
		// Diese Methode wird bei CController::endWidget() aufgerufen
	}
}
~~~

Auch ein Widget kann, ähnlich einem Controller, eine eigene Viewdatei
verwenden. Standardmäig werden Widgetviews im Unterordner `views` des
Verzeichnisses gesucht, in dem die Widgetklasse abgelegt wurde. Wie im
Controller können diese Views mit [CWidget::render()] gerendert werden.
Der einzige Unterschied besteht darin, dass kein Layout auf einen Widgetview
angewendet wird. Außerdem bezieht sich `$this` in diesem View auf das
Widgetobjekt, nicht auf den Controller.

Systemview
-----------

Systemviews werden von Yii für die Anzeige von Fehler- und Loginformationen
verwendet. Fordert ein Besucher z.B. eine Route an, die nicht existiert, löst
Yii eine Exception aus, die den Fehler näher beschreibt. Für diese Exception
verwendet Yii einen speziellen Systemview.

Die Bezeichnung von Systemviews folgt einem Schema. Namen wie
`errorXXX` werden zur Anzeige von [CHttpException]s mit
HTTP-Fehlercode `XXX` verwendet. Eine [CHttpException] mit dem
Fehlercode 404, wird z.B. mit dem View `error404` angezeigt.

Yii stellt eine Reihe von Vorgabe-Systemviews bereit, die unter
`framework/views` zu finden sind. Man kann sie leicht anpassen, indem
man gleichnamige Viewdateien in `protected/views/system` anlegt.

sehr <div class="revision">$Id: basics.view.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
