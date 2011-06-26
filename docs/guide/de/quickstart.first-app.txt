Erstellen der ersten Yii-Anwendung
==================================

Um einen ersten Eindruck von Yii zu bekommen, demonstrieren wir zunächst, wie
man mit `yiic` (einem Kommandozeilenbefehl) eine Anwendung erstellt und mit
`Gii` (einem mächtigen, webbasierten Codegenerator) automatisch
verschiedene Codeteile generieren kann. Das Installationsverzeichnis von
Yii nennen wir im folgenden `YiiVerzeichnis`, das Webverzeichnis entsprechend
`WebVerzeichnis`.

Starten Sie `yiic` von der Kommandozeile wie folgt:

~~~
% YiiVerzeichnis/framework/yiic webapp WebVerzeichnis/testdrive
~~~

> Note|Hinweis: Wenn Sie `yiic` unter Mac OS, Linux or Unix starten, müssen Sie evtl.
> die Zugriffsrechte der `yiic`-Datei auf *ausführbar* setzen. Alternativ
> können sie den Befehl auch wie folgt aufrufen:
>
> ~~~
> % cd WebVerzeichnis
> % php YiiVerzeichnis/framework/yiic.php webapp testdrive
> ~~~

Damit wird im Verzeichnis `WebVerzeichnis/testdrive` die Grundstruktur einer
Anwendung angelegt. Diese Verzeichnisstruktur wird von fast allen Yii-Anwendungen
benötigt.

Bereits jetzt kann man die erzeugte Anwendung aufrufen. Und das, ohne auch nur
eine einzige Zeile Code geschrieben zu haben. Geben Sie dazu diese URL im
Browser ein:

~~~
http://hostname/testdrive/index.php
~~~

Wie Sie sehen, besteht die Anwendung aus vier Seiten: Der Startseite, der
About-Seite, der Kontakt- sowie der Anmeldeseite. Die Kontaktseite enthält ein
Kontaktformular für Anfragen an den Webmaster. Über die Anmeldeseite können
Besucher sich einloggen, um auch geschützte Inhalte zu erreichen. Weitere
Details sehen Sie auf diesen Screenshots:


![Startseite](first-app1.png)

![Kontaktseite](first-app2.png)

![Kontaktseite mit Eingabefehlern](first-app3.png)

![Kontaktseite im Erfolgsfall](first-app4.png)

![Anmeldeseite](first-app5.png)


Das folgende Diagramm zeigt die Verzeichnisstruktur der Testanwendung. Weitere
Erläuterungen dazu finden Sie in den [Konventionen](/doc/guide/basics.convention#directory).

~~~
testdrive/
   index.php                 Startscript der Anwendung
   index-test.php            Startscript für die Funktionstests
   assets/                   enthält veröffentlichte Zusatzdateien
   css/                      enthält CSS-Dateien
   images/                   enthält Bilddateien
   themes/                   enthält Themes
   protected/                enthält die geschützten Dateien dieser Anwendung
      yiic                   yiic-Script für die Kommandozeile unter Linux/Unix
      yiic.bat               yiic-Script für die Kommandozeile unter Windows
      yiic.php               PHP-Script für yiic
      commands/              enthält selbst erstellte 'yiic'-Kommandos
         shell/              enthält selbst erstellte 'yiic shell'-Kommandos
      components/            enthält wiederverwendbare Benutzerkomponenten
         Controller.php      die Basisklasse für alle Controller
         UserIdentity.php    die Klasse 'UserIdentity' (Benutzer-Identität) für die Authentifizierung
      config/                enthält Konfigurationsdateien
         console.php         die Konfiguration für Konsolenanwendungen
         main.php            die Konfiguration für Webanwendungen
         test.php            die Konfiguration für Funktionstests
      controllers/           enthält Controllerklassen
         SiteController.php  der Standardcontroller
      data/                  enthält die Beispieldatenbank
         schema.mysql.sql    das DB-Schema für MySQL
         schema.sqlite.sql   das DB-Schema für SQLite
         testdrive.db        die SQLite-Beispieldatenbank
      extensions/            enthält Erweiterungen von Drittanbietern
      messages/              enthält übersetzte Textmeldungen
      models/                enthält Modelklassen
         LoginForm.php       das Form-Model für die 'login'-Action
         ContactForm.php     das Form-Model für die 'contact'-Action
      runtime/               enthält temporäre Dateien
      tests/                 enthält Testscripts
      views/                 enthält Controller-Views und Layout-Dateien
         layouts/            enthält Views für das Layout
            main.php         das Standardlayout für alle Views
         site/               enthält Views für den SiteController
            pages/           enthält "statische" Seiten
                about.php    View für die 'about'-Seite
            contact.php      View für die 'contact'-Action
            error.php        View für die 'error'-Action (zur Anzeige externer Fehler)
            index.php        View für ide 'index'-Action
            login.php        View für die 'login'-Action
~~~

Verbindung zu einer Datenbank
-----------------------------

Da die meisten Webanwendungen mit einer Datenbank zusammenarbeiten, soll auch
unsere Testanwendung hier keine Ausnahmen bilden. Die dazu nötigen Informationen
werden in der Konfigurationsdatei in
`WebVerzeichnis/testdrive/protected/config/main.php` angegeben:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/testdrive.db',
		),
	),
	......
);
~~~

Damit wird Yii angewiesen, sich bei Bedarf mit der SQLite-Datenbank
`WebVerzeichnis/testdrive/protected/data/testdrive.db` zu verbinden. Beachten Sie,
dass die SQLite-Datenbank bereits in der Beispielanwendung enthalten ist, die
Sie gerade erstellt haben. Die Datenbank enthält nur die Tabelle `tbl_user`:

~~~
[sql]
CREATE TABLE tbl_user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

Falls Sie stattdessen eine MySQL-Datenbank einsetzen möchten, können Sie die
Schemadatei `WebVerzeichnis/testdrive/protected/data/schema.mysql.sql` verwenden, um
die Datenbank anzulegen.

> Note|Hinweis: Yii benötigt die PDO-Erweiterung in PHP inklusive der
> datenbankspezifischen PDO-Treiber, um auf eine Datenbank zugreifen zu können.
> Für die Testanwendung müssen also `php_pdo` und `php_pdo_sqlite` aktiviert
> sein.


Erstellen von CRUD-Operationen
------------------------------

Kommen wir nun zum angenehmen Teil: Wir möchten CRUD-Operationen (
für "*C*reate, *R*ead, *U*pdate, *D*elete", "Erstellen, Lesen, Aktualisieren, Löschen")
für die eben erstellte Tabelle `tbl_user` bereitstellen. Eine typische
Aufgabenstellung aus der Praxis. Statt den nötigen Code mühevoll selbst zu schreiben,
können Sie dazu den leistungsstarken Codegenerator `Gii` verwenden.

> Info|Info: Gii steht seit Version 1.1.2 zur Verfügung. In früheren Versionen
> wurde auch für die CRUD-Generierung der erwähnte `yiic`-Befehl verwendet.
> Weitere Details hierzu finden Sie unter [CRUD-Operationen mit der yiic shell
> erstellen](/doc/guide/quickstart.first-app-yiic).


### Gii konfigurieren

Um Gii zu verwenden, muss zunächst die
[Konfigurationsdatei](/doc/guide/basics.application#application-configuration)
in `WebRoot/testdrive/protected/config/main.php` angepasst werden:

~~~
[php]
return array(
	......
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Setzen Sie hier ein beliebiges Passwort ein',
		),
	),
);
~~~

Rufen Sie danach die URL `http://hostname/testdrive/index.php?r=gii` auf und
melden Sie sich mit dem eben konfigurierten Passwort an.


### Generieren des Usermodels

Wenn Sie nach der Anmeldung auf den Link `Model Generator` klicken, erscheint
folgende Seite:

![Model Generator](gii-model.png)

Tragen Sie in die Felder `Table Name` (Tabellenname) und `Model Class`
(Modelklasse) die Werte `tbl_user` und `User` ein und klicken Sie den Button
`Preview` um eine Vorschau des generierten Codes zu sehen.
Klicken Sie nun auf den `Generate`-Button, um die Datei `User.php` in
`protected/models` anzulegen. Wie wir später noch sehen werden, ermöglicht
diese Modelklasse `User` den objektorientierten Zugriff auf die
darunterliegende Datenbanktabelle.

### CRUD-Code generieren

Wurde die Modelklasse erstellt, können Sie den Code für die CRUD-Operationen
erzeugen. Rufen Sie dazu den `Crud Generator` in Gii auf:

![CRUD Generator](gii-crud.png)

Geben Sie `User` in das Feld `Model Class` und `user` (in Kleinbuchstaben) in
das Feld `Controller ID` ein. Auch hier können Sie zunächst `Preview` klicken,
bevor Sie die nötigen Codedateien mit `Generate` erstellen. Damit haben Sie
die CRUD-Generierung abgeschlossen.


### Aufruf der CRUD-Seiten

Sie können nun die Früchte Ihrer Arbeit genießen, indem sie folgende URL
aufrufen:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Sie sehen eine Liste aller Benutzer in der Tabelle `tbl_user`.

Klicken Sie auf den Link `Benutzer anlegen`. Falls Sie noch nicht angemeldet
sind, werden Sie zur Anmeldeseite weitergeleitet. Nach der
Anmeldung erscheint ein Eingabeformular, mit dem Sie einen neuen
Benutzer hinzufügen können. Füllen Sie das Formular aus, und klicken Sie
unten auf den Button `Erstellen`. Bei Eingabefehlern erhalten Sie einen
freundlichen Hinweis und der Datensatz wird nicht gespeichert.
Nach dem erfolgreichen Speichern sollte der neu angelegte Benutzer in der
Benutzerliste erscheinen.

Wiederholen Sie die obigen Schritte, und fügen Sie weitere Benutzer hinzu.
Vielleicht haben Sie schon bemerkt, dass automatisch eine
Seitenblätterung (engl.: pagination) angezeigt wird, sobald zu viele Einträge
für eine Seite vorhanden sind.

Wenn Sie sich nun mit `admin/admin` als Administrator anmelden, können Sie
hier Admin-Seite aufrufen:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Sie sehen eine übersichtliche Tabelle aller Benutzer. Zum Ändern der
Sortierung können Sie auf einen Spaltentitel klicken. Und auch hier wird eine
Seitenblätterung angezeigt, sobald einige Einträge vorhanden sind.

All dies haben Sie erreicht, ohne dafür eine einzige Codezeile
schreiben zu müssen!

![Administrationsseite für Benutzer](first-app6.png)

![Seite zum Erstellen eines neuen Benutzers](first-app7.png)

<div class="revision">$Id: quickstart.first-app.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>
