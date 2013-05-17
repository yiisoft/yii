Sicherheit
==========

Schutz vor Cross-Site Scripting 
-------------------------------
Beim Cross-Site Scripting (auch als XSS bekannt) sammelt eine Webanwendung
böswillig Daten eines Benutzers. Hierzu schleusen Angreifer oft JavaScript,
VBScript, ActiveX, HTML oder Flash in eine anfällige Anwendung ein, um andere
Anwender zu überlisten und deren Daten zu sammeln. Eine schlecht geplante
Forumsoftware könnte z.B. in einem Beitrag alle Benutzereingaben ohne weitere
Prüfung anzeigen. Ein Angreifer könnte so ein kleines Stück bösartigen
JavaScript-Code in einen Beitrag einbauen, so dass dieser heimlich 
auf dem jeweiligen Rechner ausgeführt wird, wenn andere diesen Beitrag lesen.

Eine der wichtigsten Maßnahmen zum Schutz vor XSS-Angriffen besteht daher in
der Überprüfung von Benutzereingaben bevor diese angezeigt werden. Man kann
dazu zum Beispiel sämtliche Eingaben eines Benutzers HTML-codieren. Allerdings
kann dies in manchen Situationen nicht wünschenswert sein, da so alle
HTML-Tags deaktiviert werden.

Yii bedient sich daher der Arbeit des
[HTMLPurifier](http://htmlpurifier.org/)-Projekts und bietet Entwicklern eine
nützliche Komponente namens [CHtmlPurifier], die
[HTMLPurifier](http://htmlpurifier.org/) kapselt. Diese Komponente ist in der
Lage, sämtlichen bösartigen Code mittels einer sorgfältig geprüften, sicheren
und gleichzeitig toleranten Whitelist (sinngem.: Liste aller erlaubten
Begriffe) zu entfernen und sicherzustellen, dass der gefilterte Inhalt
standardkonform ist.

Die [CHtmlPurifier]-Komponente kann entweder als
[Widget](/doc/guide/basics.view#widget) oder als
[Filter](/doc/guide/basics.controller#filter) verwendet werden. Bei Einsatz
als Widget bereinigt [CHtmlPurifier] seinen eingebetteten Inhalt in einem
View. Hier ein Beispiel:

~~~
[php]
<?php $this->beginWidget('CHtmlPurifier'); ?>
...Ausgabe von Benutzereingaben...
<?php $this->endWidget(); ?>
~~~


Schutz vor Cross-site Request Forgery
-------------------------------------
Beim Cross-Site Request Forgery (CSRF) löst eine bösartige Website im Browser
eines Benutzers eine unbeabsichtigte Aktion auf einer vertrauenswürdigen
Website aus. Eine bösartige Site könnte zum Beispiel einen img-Tag enthalten,
dessen `src` auf eine Onlinebanking-Site verweist:
`http://beispiel.bank/buchung?transfer=10000&an=irgendjemand`. Falls ein
Anwender, der die Anmeldedaten zur Onlinebanking-Website in einem Cookie
gespeichert hat, die bösartige Site besucht, wird evtl. die Überweisung über
10000 EUR an irgendjemand ausgeführt. Im Gegensatz zum Cross-Site-Angriff, bei
dem das Vertrauen eines Anwenders in eine bestimmte Site ausgenutzt wird, wird
beim CSRF das Vertrauen einer Site gegenüber einem bestimmten Anwender
ausgenutzt.

Um CSRF-Angriffe zu verhindern, ist es wichtig, sich an die Regel zu halten,
dass `GET`-Requests nur zum Beziehen von Daten verwendet werden sollten, statt
Daten auf dem Server zu verändern. Und `POST`-Requests sollten einen
Zufallswert enhalten, der vom Server erkannt werden kann, um sicherzustellen,
dass ein Formular vom selben Ort aus abgeschickt wurde, an den auch das
Ergebnis zurückgeschickt wird. 

Yii implementiert einen CSRF-Schutzmechanismus um `POST`-basierte Angriffe
abzuwehren. Er basiert darauf, einen Zufallswert in einem Cookie zu speichern,
und dessen Wert mit einem per `POST`-Request gesendeten Wert zu vergleichen. 

Standardmäßig ist der CSRF-Schutz deaktiviert. Um ihn einzuschalten,
konfigurieren Sie die [CHttpRequest]-Komponente in der
[Anwendungskonfiguration](/doc/guide/basics.application#application-configuration)
wie folgt:

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCsrfValidation'=>true,
		),
	),
);
~~~

Verwenden Sie statt eines HTML-form-Tags den Aufruf [CHtml::form]. 
Die Methode [CHtml::form] sorgt dafür, dass der nötige
Zufallswert in einem Hidden-Feld eingefügt wird, so dass er zur
CSRF-Gültigkeitsprüfung mit zurückgesendet werden kann.


Schutz vor Cookie-Angriffen
---------------------------
Cookies vor Angriffen zu schützen ist äußerst wichtig, da Session-IDs für
gewöhnlich in Cookies gespeichert werden. Falls jemand die Session-ID
ergattern kann, besitzt er damit im Wesentlichen alle bedeutsamen Session-Informationen.

Es gibt einige Gegenmaßnahmen um Cookies vor einem Angriff zu schützen.

* Eine Anwendung kann SSL verwenden um einen sicheren Kommunikationskanal
aufzubauen und das Authentifizierungscookie nur über eine HTTPS-Verbindung zu
übertragen. Angreifer können so die Inhalte der übertragenen Cookies nicht
entschlüsseln. 
* Lassen Sie Sessions inkl. aller Cookies und Session-Token nach angemessener 
Zeit verfallen um die Wahrscheinlichkeit eines Angriffs zu verringern.
* Verhindern Sie Cross-Site Scripting, was zur Ausführung von schädlichem Code
im Browser des Anwenders führen kann und so dessen Cookies zugänglich macht.
* Prüf Sie Cookiedaten auf Gültigkeit und stellen Sie fest, ob diese verändert wurden.

Yii implementiert ein Schema zur Cookie-Überprüfung, das Cookies vor
Veränderung schützt. Falls aktiv, wird insbesondere eine HMAC-Prüfung der
Cookiewerte durchgeführt.

Die Cookie-Überprüfung ist standardmäßig nicht aktiviert. Um sie einzuschalten,
konfigurieren Sie die [CHttpRequest]-Komponente in der
[Anwendungskonfiguration](/doc/guide/basics.application#application-configuration)
wie folgt:

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCookieValidation'=>true,
		),
	),
);
~~~

Um die Cookie-Überprüfung anzuwenden, müssen wir über die
[cookies|CHttpRequest::cookies]-Collection auf Cookies zugreifen, statt diese
direkt über `$_COOKIES` anzusprechen:

~~~
[php]
// Cookie mit dem angegebenen Namen beziehen
$cookie=Yii::app()->request->cookies[$name];
$value=$cookie->value;
......
// Cookie senden
$cookie=new CHttpCookie($name,$value);
Yii::app()->request->cookies[$name]=$cookie;
~~~


<div class="revision">$Id: topics.security.txt 2535 2010-10-11 08:28:08Z mdomba $</div>
