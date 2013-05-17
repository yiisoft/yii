Applikation
===========

Die Applikation (bzw. Anwendung) bildet die abgeschlossene Laufzeitumgebung
innerhalb der ein Request bearbeitet wird. Ihr Hauptzweck liegt darin, einige
Informationen über den vorliegenden Request zu sammeln und diesen dann zur
Bearbeitung an den richtigen Controller weiterzuleiten. Außerdem dient dieses
Objekt als zentraler Speicherort für Konfigurationsdaten der gesamten Anwendung.
Man nennt dieses Objekt auch `Front-Controller`.

Das Applikationsobjekt wird vom [Startscript](/doc/guide/basics.entry) als
Singleton erzeugt und kann daher an jeder Stelle über [Yii::app()|YiiBase::app]
abgerufen werden.

Konfiguration
-------------

Standardmäßig besteht eine Applikation aus einer Instanz der Klasse [CWebApplication].
Beim Instanziieren dieses Objekts wird in der Regel eine Konfigurationsdatei
(oder ein Array mit Konfigurationsdaten) übergeben, um
die nötigen Einstellungen an der Applikation vorzunehmen. Alternativ dazu
kann man auch eine Klasse von [CWebApplication] ableiten und die
Konfigurationsdaten dort "hardcoden" (also direkt im Klassenquelltext
hinterlegen).

Das Konfigurationsarray besteht aus Schlüssel-Wert-Paaren. Schlüssel und
Wert entsprechen den Eigenschaftsnamen und (Start-)Werten des
Applikationsobjekts. Mit dem folgenden Array wird z.B. die Eigenschaft
[name|CApplication::name] und
[defaultController|CWebApplication::defaultController] (Standardcontroller)
konfiguriert:

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

Für gewöhnlich wird die Konfiguration in einer eigenen PHP-Datei abgelegt
(z.B. `protected/config/main.php`). Dieses Script liefert das
Konfigurationsarray folgendermaßen zurück:

~~~
[php]
return array(...);
~~~

Der Name der Konfigurationsdatei kann als Parameter an den Konstruktor der
Applikation übergeben werden oder wie im folgenden Beispiel an
[Yii::createWebApplication()]. Für gewöhnlich geschieht dies im
[Startscript](/doc/guide/basics.entry):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|Tipp: Falls die Konfigurationsdaten sehr umfangreich oder kompliziert
> strukturiert sind, können sie auch auf mehrere Dateien aufgeteilt werden.
> In der eigentlichen Konfigurationsdatei können die einzelnen Abschnitte dann
> mit `include()` eingebunden und zu einem vollständigen Array zusammengeführt
> werden.

Anwendungsverzeichnis
---------------------

Im Anwendungsverzeichnis sind alle sicherheitsempfindlichen Dateien der
Applikation abgelegt. Per Voreinstellung ist dies der `protected`-Ordner
im Verzeichnis, das auch das Startscript enthält. Der Pfad zum
Anwendungsverzeichnis kann in der [Konfiguration](/doc/guide/basics.application#application-configuration) über
[basePath|CWebApplication::basePath] angepasst werden.

Sämtliche Inhalte in diesem Verzeichnis sollten vor Zugriff über das Web
geschützt werden. Beim [Apache HTTP-Server](http://httpd.apache.org/) erreicht
man das, indem eine `.htaccess`-Datei mit folgendem Inhalt in diesem Verzeichnis
abgelegt wird:

~~~
deny from all
~~~

Applikationskomponente
----------------------

Über die flexible Komponenten-Architektur kann der Funktionsumfang einer
Applikation einfach angepasst und erweitert werden. Die Anwendung verwaltet
eine Reihe von Komponenten, von denen jede eine spezielle Aufgabe übernimmt.
Die [CUrlManager]- und [CHttpRequest]-Komponenten dienen der Anwendung zum
Beispiel beim Auflösen eines Requests.

In der Konfiguration kann über das `components`-Array für jede dieser
Komponenten eingestellt werden, welche Klasse dafür verwendet werden soll und
mit welchen Werten die Eigenschaften der Komponente initialisiert werden soll.
Wir können z.B. für die `cache`-Komponente `CMemCache` mit mehreren
Memcache-Servern konfigurieren:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

Mit diesem Eintrag in `components` wird `cache` als Objekt der Klasse
`CMemCache` definiert und dessen `servers`-Eigenschaft beim Erstellen des
Objekts mit den angegebenen Parametern initialisiert.


Um auf eine dieser Anwendungskomponenten zuzugreifen, benutzen Sie
`Yii::app()->KomponentenID`, wobei `KomponentenID` sich auf die ID
der Komponente bezieht (z.B. `Yii::app()->cache`).

Eine Komponente kann auch deaktiviert werden, indem man bei ihr den
Schlüssel `enabled` als `false` konfiguriert. In diesem Fall wird `null`
zurückgeliefert, wenn man auf die Komponente zugreift.


> Tip|Tipp: Normalerweise werden diese Komponenten erst erzeugt, wenn
> zum ersten mal darauf zugegriffen wird. Wird eine Komponente während
> eines Requests also gar nicht verwendet, gibt es auch keine Instanz
> davon. Selbst bei vielen konfigurierten Komponenten bleibt
> die Gesamtperformance so evtl. unbeeinflusst, sofern immer nur ein Teil
> davon eingesetzt wird. Einige Komponenten müssen aber evtl. immer erstellt
> werden, ganz egal, ob sie verwendet werden oder nicht (z.B. CLogRouter).
> Das lässt sich erreichen, indem man die Komponenten-ID in der
> [preload|CApplication::preload]-Eigenschaft ein der Konfiguration angibt.


Kernkomponenten einer Anwendung
-------------------------------

Yii definiert bereits eine Reihe von Kernkomponenten für die üblichen
Aufgabengebiete einer Webanwendung vor. Die [request|CWebApplication::request]-Komponente
wird z.B. für die Auflösung eines Requests und die Abfrage von
Requestinformationen wie URL und Cookies verwendet. Indem man die
Eigenschaftswerte dieser Kernkomponenten anpasst, lässt sich das
verhalten einer Yii-Anwendung bereits in weiten Grenzen steuern.

Dies sind die Kernkomponenten, die eine [CWebApplication] standardmäßig vorbelegt:

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
verwaltet die Veröffentlichung privater Asset-Dateien (sinngem.:
Zusatzdateien).

   - [authManager|CWebApplication::authManager]: [CAuthManager] - verwaltet
die rollenbasierte Zugriffskontrolle (RBAC, engl.: role-based access control).

   - [cache|CApplication::cache]: [CCache] - stellt Funktionalität
zum Cachen von Daten bereit. Beachten Sie, dass Sie eine existierende Klasse (z.B.
[CMemCache], [CDbCache]) angeben müssen. Andernfalls wird null
zurückgeliefert, wenn Sie auf diese Komponente zugreifen.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
verwaltet Clientscripts (Javascripts und CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
stellt übersetzte Kernmeldungen für das Yii-Framework bereit.

   - [db|CApplication::db]: [CDbConnection] - stellt eine Datenbankverbindung
bereit.  Beachten Sie, dass sie deren
[connectionString|CDbConnection::connectionString]-Eigenschaft konfigurieren
müssen um diese Komponente zu verwenden.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - bearbeitet
nicht-abgefangene PHP-Fehler und -Exceptions.

   - [format|CApplication::format]: [CFormatter] - formatiert Datenwerte für
die Anzeige.

   - [messages|CApplication::messages]: [CPhpMessageSource] - stellt
übersetzte Textmeldungen für die Yii-Anwendung bereit.

   - [request|CWebApplication::request]: [CHttpRequest] - stellt Informationen
über den HTTP-Request bereit

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
stellt Sicherheitsdienste bereit, wie z.B. Hashing, Verschlüsselung.

   - [session|CWebApplication::session]: [CHttpSession] - stellt
sessionbezogene Funktionen zur Verfügung.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
stellt Methoden zur globalen beständigen Datenhaltung bereit.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - beinhaltet
Funktionen zur URL-Analyse und -Erstellung.

   - [user|CWebApplication::user]: [CWebUser] - repräsentiert die
Idenitätsinformationen des aktuellen Benutzers.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] -
verwaltet Themes.


Lebenszyklus einer Applikation
------------------------------

Beim Bearbeiten eines Requests durchläuft eine Anwendung diesen Zyklus:

   0. Vor-Initialisieren der Anwendung mit [CApplication::preinit()];

   1. Einrichten des Klassen-Autoloaders und der Fehlerbehandlung

   2. Registrieren der Kernkomponenten

   3. Laden der Konfiguration

   4. Initialisieren der Anwendung mit [CApplication::init()]
       - Registrieren von Behaviors
       - Laden von statischen Komponenten

   5. Auslösen des [onBeginRequest|CApplication::onBeginRequest]-Events

   6. Bearbeiten des Benutzer-Requests:
       - Informationen zum Request sammeln
       - Einen Controller instanziieren
       - Controller ausführen

   7. Auslösen des [onEndRequest|CApplication::onEndRequest]-Events

<div class="revision">$Id: basics.application.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
