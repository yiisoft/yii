Model-View-Controller (MVC)
===========================

Yii implementiert das Model-View-Controller-Architekturmuster (MVC),
das im Bereich der Web-Programmierung breite Anwendung findet. MVC zielt auf
eine Trennung von Geschäftslogik und Benutzerschnittstelle ab, so dass
der Entwickler jeden dieser Bereiche bequem verändern kann, ohne den anderen
zu beeinflussen. In MVC werden die Information (die Daten) und die
Geschäftslogik durch das Model (Modell) repräsentiert. Der (auch "die") View
(Präsentation) enthält Elemente der Benutzerschnittstelle, wie z.B. Text oder
Formularelemente. Und der Controller (Steuerung) verwaltet die Kommunikation
zwischen Model und View.

Neben der MVC-Implementierung führt Yii außerdem einen Front-Controller namens
`Applikation` ein. Sie stellt die gekapselte Laufzeitumgebung für die Bearbeitung eines
Requests (Anfrage) dar. Die Applikation sammelt einige Informationen über den
Benutzer-Request und leitet ihn dann zur Bearbeitung an den passenden Controller weiter.

Das folgende Diagramm zeigt die statische Struktur einer Yii-Applikation:

![Statische Struktur einer Yii-Applikation](structure.png)


Ein typischer Ablauf
--------------------

Hier sehen wir den typischen Ablauf einer Yii-Anwendung beim
Bearbeiten eines einzelnen Requests:

![Typischer Ablauf einer Yii-Applikation](flow.png)

   1. Ein Benutzer schickt einen Request mit der URL
`http://www.example.com/index.php?r=post/show&id=1`
   2. Das Startscript erzeugt eine Instanz einer
[Applikation](/doc/guide/basics.application) und startet diese.
   3. Die Applikation fragt bei der [Anwendungskomponente](/doc/guide/basics.application#application-component)
`request` nach den Detailinformationen zum Request.
   4. Über die `urlManager`-Komponente ermittelt die Applikation den angeforderten
[Controller](/doc/guide/basics.controller). In diesem Beispiel ist der
Controller `post`. Es wird also die Klasse `PostController` verwendet. Die
Action ist `show`. Erst der Controller entscheidet, was diese Action bedeutet.
   5. Die Applikation erzeugt eine Instanz des angeforderten Controllers, damit
dieser den Request weiter bearbeitet. Der Controller stellt fest, dass die
Action `show` sich auf auf eine Methode namens `actionShow` in der
Controller-Klasse bezieht. Er instanziiert daraufhin die mit dieser Action
verbundenen Filter (z.B. Zugriffsschutz, Benchmark) und führt diese aus. Die Action wird
ausgeführt wenn dies von den Filtern erlaubt wird.
   6. Die Action liest das [Model](/doc/guide/basics.model) `Post` mit der ID
`1` aus der Datenbank
   7. Die Action rendert einen
[View](/doc/guide/basics.view) namens `show` mit dem `Post`-Model.
   8. Der View liest die Attribute des `Post`-Models und zeigt diese an.
   9. Der View führt einige [Widgets](/doc/guide/basics.view#widget) aus.
   10. Das Render-Ergebnis wird in ein
[Layout](/doc/guide/basics.view#layout) eingebettet.
   11. Die Action beendet das Rendern des Views und schickt das Ergebnis
zurück zum Browser.

<div class="revision">$Id: basics.mvc.txt 3321 2011-06-26 12:54:22Z mdomba $</div>
