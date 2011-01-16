Entwickeln mit Yii
==================

Nachdem wir nun die Yii-Grundlagen beschrieben haben, wollen wir uns ansehen,
wie man mit Yii eine Anwendung entwickelt. Wir gehen davon aus, dass die
nötigen Voraussetzungen für Yii bereits geprüft wurden und ein Entwurf für die
Anwendung vorliegt.

   1. Erstellen der grundsätzlichen Verzeichnisstruktur. Am schnellsten geht
dies mit dem `yiic`-Befehl, wie im Kapitel
[Erstellen der ersten Yii-Anwendung](/doc/guide/quickstart.first-app)
beschrieben.

   2. Konfigurieren der [Applikation](/doc/guide/basics.application). Dazu
werden die Konfigurationsdateien angepasst. Eventuell müssen hier auch 
einige Anwendungskomponenten (z.B. die "user component", die Benutzerkomponente)
angelegt werden.

   3. Erstellen der jeweiligen [Model](/doc/guide/basics.model)-Klassen für
alle vorkommenden Daten. Die [ActiveRecord](/doc/guide/database.ar)-Klassen
für alle Datenbanktabellen können mit dem `Gii`-Werkzeug erstellt werden, das in den
Kapiteln [Automatische Codegenerierung](/doc/guide/topics.gii) und [Erstellen der ersten
Yii-Anwendung](/doc/guide/quickstart.first-app#implementing-crud-operations)
beschrieben wird.

   4. Erstellen der [Controller](/doc/guide/basics.controller)-Klassen 
für zusammengehörende Anfragen. Wie die einzelnen Anfragen zu einem Controller
gruppiert werden, hängt von den jeweiligen Anforderungen ab. In der Regel wird
für jede Modelklasse ein eigener Controller verwendet. Auch dieser Schritt kann mit dem
`yiic`-Befehl automatisiert werden.

   5. Implementieren von [Actions](/doc/guide/basics.controller#action) und
entsprechenden [Views](/doc/guide/basics.view). Dies macht den Großteil der
Entwicklungsarbeit aus.

   6. Konfigurieren der benötigten [Actionfilter](/doc/guide/basics.controller#filter) 
in den Controllern.

   7. Erstellen von [Themes](/doc/guide/topics.theming), falls dieses Feature
benötigt wird.

   8. Erstellen von Übersetzungen, falls
[Internationalisierung](/doc/guide/topics.i18n) erforderlich ist.

   9. Ausfindig machen von Daten und Views, die gecacht werden können und
Anwenden geeigneter [Caching](/doc/guide/caching.overview)-Techniken.

   10. Abschließende [Leistungsoptimierung](/doc/guide/topics.performance) und
Onlinestellung.

Für jeden der obigen Schritte kann es nötig sein, "Test cases" (sinngem:
automatisierte Funktionstests) zu erstellen und durchzuführen.

<div class="revision">$Id: basics.workflow.txt 2718 2010-12-07 15:17:04Z qiang.xue $</div>
