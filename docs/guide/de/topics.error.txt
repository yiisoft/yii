Fehlerbehandlung
================

Yii bietet ein vollständiges Framework zur Fehlerbehandlung, das auf dem
Exception-Mechanismus von PHP 5 basiert. Wenn eine Applikation erzeugt wird,
um einen eingehenden Benutzer-Request zu bearbeiten, registriert sie ihre
[handleError|CApplication::handleError]-Methode (sinngem.: behandle Fehler), 
um PHPs Warnungen und Hinweise zu verarbeiten. Außerdem registriert sie ihre
[handleException|CApplication::handleException]-Methode (sinngem.: behandle
Ausnahme), um nicht-abgefangene PHP-Exceptions zu bearbeiten. Wenn daher 
während der Ausführung der Anwendung 
eine PHP-Warnung, ein PHP-Hinweis oder eine nicht-abgefangene Exception
auftritt, wird eine dieser Fehlerroutinen die Kontrolle übernehmen und die
nötige Prozedur zur Bearbeitung des Fehlers einleiten.


> Tip|Tipp: Die Fehlerroutinen werden im Konstruktor der Anwendung durch
Aufruf der PHP-Funktionen 
[set_exception_handler](http://www.php.net/manual/de/function.set-exception-handler.php)
und [set_error_handler](http://www.php.net/manual/de/function.set-error-handler.php)
registriert. Wenn Sie nicht möchten, dass Yii sich um Fehler und Exceptions
kümmert, können Sie im [Startscript](/doc/guide/basics.entry) die Konstanten 
`YII_ENABLE_ERROR_HANDLER` und `YII_ENABLE_EXCEPTION_HANDLER` als false definieren.

Per Vorgabe löst [handleError|CApplication::handleError] (bzw.
[handleException|CApplication::handleException]) ein
[onError|CApplication::onError]-Event (bzw.
[onException|CApplication::onException]-Event) aus. Falls der Fehler (bzw. die
Exception) von keinem Eventhandler behandelt wird, ruft die Routine die
[errorHandler|CErrorHandler]-Anwendungskomponente zu Hilfe.

Auslösen von Exceptions
-----------------------

Das Auslösen einer Exception (Ausnahme) in Yii unterscheidet sich nicht vom
Auslösen einer normalen PHP-Exception. Exceptions werden bei Bedarf mit
folgender Syntax ausgelöst:

~~~
[php]
throw new ExceptionClass('ExceptionMeldung');
~~~

Yii definiert drei Exceptionklassen: [CException], [CDbException] und [CHttpException].
[CException] ist die allgemeine Exceptionklasse. [CDbException] steht für
Exceptions die bei Datenbankoperationen ausgelöst werden. [CHttpException]
ist eine Exception, die dem Besucher angezeigt werden soll. Sie hat
eine [statusCode|CHttpException::statusCode]-Eigenschaft, die für den
HTTP-Statuscode steht. Wie im folgenden gezeigt, bestimmt die Klasse
einer Exception darüber, wie diese angezeigt werden soll.

> Tip|Tipp: Durch das Auslösen einer [CHttpException]-Exception kann man sehr
einfach auf Fehlfunktionen aufmerksam machen. Falls ein Benutzer z.B. eine
falsche Beitrags-ID übergibt, kann man mit folgender Anweisung einen 404-Fehler 
(Seite nicht gefunden) ausgeben:
~~~
[php]
// Falls Beitrags-ID ungültig ist:
throw new CHttpException(404,'Der angegebene Beitrag wurde nicht gefunden.');
~~~

Anzeigen von Fehlern
--------------------

Wenn ein Fehler an die Anwendungskomponente [CErrorHandler] weitergeleitet
wird, sucht diese nach dem passenden View, um den Fehler anzuzeigen. Falls der
Fehler zur Anzeige für den Endanwender gedacht ist (wie z.B.
[CHttpException]), verwendet sie einen View namens `errorXXX`, wobei `XXX` für
den HTTP-Statuscode steht (z.B. 400, 404, 500). Falls es sich um einen
internen Fehler handelt und dieser nur Entwicklern angezeigt werden soll,
verwendet sie einen View namens `exception`. In letzterem Fall wird der
vollständige Aufrufstapel (engl.: call stack) sowie Informationen zur Zeile, 
in der der Fehler aufgetreten ist, angezeigt. 

> Info: Falls die Anwendung im
[Produktivmodus](/doc/guide/basics.entry#debug-mode) läuft, werden alle
(inkl. interne) Fehler mit dem View `errorXXX` angezeigt, da
der Aufrufstapel evtl. sensible Daten enthalten kann. In diesem Fall sollte der
Entwickler Fehlerprotokolle verwenden, um die wahre Ursache des Fehlers
herauszufinden.

[CErrorHandler] sucht in dieser Reihenfolge nach der entsprechenden 
View-Datei:

   1. `WebVerzeichnis/themes/ThemeName/views/system`: dies ist das Verzeichnis für
`system`-Views im gerade aktiven Theme.

   2. `WebVerzeichnis/protected/views/system`: dies ist das Standardverzeichnis
für `system`-Views einer Anwendung.

   3. `yii/framework/views`: dies ist das Standardverzeichnis für
`system`-Views, die das Yii-Framework bereitstellt.

Möchte man die Fehlerdarstellung anpassen, kann man im System-View-Verzeichnis
der Anwendung oder des Themes Dateien anlegen. Jede dieser View-Datei ist ein 
gewöhnliches PHP-Script, das
hauptsächlich aus HTML-Code besteht. Mehr Details finden Sie in den
vorgegebenen View-Dateien im `view`-Verzeichnis des Frameworks.

Fehlerbehandlung mit einer Action
---------------------------------

Fehler können auch über eine
[Controller-Action](/doc/guide/basics.controller#action) angezeigt werden.
Dazu konfiguriert man die Fehlerroute in der Anwendungskonfiguration wie
folgt:

~~~
[php]
return array(
	......
	'components'=>array(
		'errorHandler'=>array(
			'errorAction'=>'site/error',
		),
	),
);
~~~

Hier wird die [CErrorHandler::errorAction]-Eigenschaft auf die Route
`site/error` gesetzt, was der `error`-Action im `SiteController` entspricht.
Man kann natürlich auch eine andere Route verwenden.

Die `error`-Action kann einfach so aussehen:

~~~
[php]
public function actionError()
{
	if($error=Yii::app()->errorHandler->error)
		$this->render('error', $error);
}
~~~

In der Action holt man sich zunächst die detaillierte Fehlerinformationen aus
[CErrorHandler::error]. Falls diese nicht leer ist, wird der `error`-View
zusammen mit den Fehlerinformationen gerendert. Die von [CErrorHandler::error]
zurückgegebene Fehlerinformation ist ein Array mit den folgenden Feldern:

 * `code`: der HTTP-Statuscode (z.B. 403, 500);
 * `type`: der Fehlertyp (z.B. [CHttpException], `PHP Error`);
 * `message`: die Fehlermeldung;
 * `file`: der Name des PHP-Scripts, in dem der Fehler aufgetreten ist;
 * `line`: die Nummer der Zeile, in der der Fehler aufgetreten ist;
 * `trace`: der Aufrufstapel des Fehlers;
 * `source`: der Quelltextbereich, in dem der Fehler aufgetreten ist.

> Tip|Tipp: Die Prüfung, ob [CErrorHandler::error] leer ist oder nicht, wird
> durchgeführt, da die `error`-Action auch direkt von einem Endbenutzer
> aufgerufen werden könnte. In diesem Fall gibt es aber keinen Fehler. Da 
> das `$error`-Array direkt an den View übergeben wird, wird es automatisch in
> einzelne Variablen expandiert. Daher kann man im View die Variablen direkt
> als `$code`, `$type` usw. ansprechen.


Loggen von Meldungen
--------------------

Immer wenn ein Fehler auftritt, wird eine Meldung mit der Stufe `error`
geloggt. Falls der Fehler von einer PHP-Warnung oder einem PHP-Hinweis stammt,
wird die Meldung unter der Kategorie `php` geloggt. Stammt der Fehler von
einer nicht-abgefangenen Exception, lautet die Kategorie
`exception.ExceptionKlassenName` (bei [CHttpException] wird auch deren
[statusCode|CHttpException::statusCode] an die Kategorie angehängt). Man kann
somit die Verfahren zur [Protokollierung](/doc/guide/topics.logging) benutzen,
um Ausführungsfehler einer Anwendung zu überwachen.

<div class="revision">$Id: topics.error.txt 3374 2011-08-05 23:01:19Z alexander.makarow $</div>
