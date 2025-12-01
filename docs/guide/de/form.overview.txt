Arbeiten mit Formularen
=======================

Eine der wesentlichen Aufgaben von Webanwendungen besteht im Auswerten von
HTML-Formularen. Der Entwickler muss hierzu Formulare erstellen, diese mit
vorhandenen Werten oder Vorgabewerten befüllen, eingegebene Daten überprüfen,
entsprechende Fehlermeldungen ausgeben und die Daten schließlich in einem
Permanentspeicher ablegen. Durch die MVC-Architektur von Yii wird dieser ganze
Prozess stark vereinfacht.

Der typische Ablauf beim Arbeiten mit Formularen sieht in Yii so aus:

   1. Anlegen einer Modelklasse für die zu erfassenden Daten
   2. Implementieren einer Controlleraction, die das abgeschickte Formular
      verarbeitet
   3. Erstellen des entsprechenden Formularviews für diese Action

In den nächsten Abschnitten gehen wir näher auf die einzelnen Schritte ein.

<div class="revision">$Id: form.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>
