Authentifizierung und Autorisierung
===================================

Diese beiden Themen spielen dann eine Rolle, wenn einige Webseiten nur für
bestimmte Benutzer zugänglich sein sollen. Bei der *Authentifizierung* wird 
geprüft, ob jemand auch tatsächlich der ist, der er vorgibt zu sein. Meist
geschieht das per Benutzernamen und Passwort.  Man könnte aber auch eine andere
zur Identifizierung geeignete Methode verwenden, z.B. eine Chipkarte, 
den Fingerabdruck, etc. Über die *Autorisierung* wird festgestellt, ob die
identifizierte (also authentifizierte) Person auch tatsächlich berechtigt 
ist, bestimmte Ressourcen zu manipulieren. Normalerweise wird dazu geprüft, ob die
Person einer bestimmten Rolle zugeordnet ist, die Zugriff auf die Ressource
hat. 

Yii hat ein einfach anzuwendendes Authentifizierungs-/Autorisierungs-Framework
(Auth-Framework) eingebaut, das leicht an spezielle Bedürfnisse angepasst werden kann.

Zentraler Bestandteil dieses Frameworks ist eine in der Anwendung 
vordefinierte *Userkomponente* (Benutzerkomponente), die 
das [IWebUser]-Interface implementiert. Diese Userkomponente stellt die
beständigen Identitätsdaten des aktuellen Benutzers dar. 
Über `Yii::app()->user` kann von jeder Stelle aus darauf zugegriffen werden.

Mittels der Userkomponente kann man über [CWebUser::isGuest] prüfen, 
ob ein Benutzer angemeldet ist oder nicht. Man kann einen Benutzer mit
[login|CWebUser::login] und [logout|CWebUser::logout] an- bzw. abmelden oder
mit [CWebUser::checkAccess] prüfen, ob der Benutzer bestimmte
Operationen ausführen kann. Außerdem kann man den [eindeutigen
Namen|CWebUser::name] bzw. auch andere beständige Identitätsdaten des
Benutzers abfragen.


Definieren der Identitätsklasse
-------------------------------

Wie bereits erwähnt, geht es bei der Authentifizierung darum, die Identität
eines Benutzers zu prüfen. Bei einer typischen Webanwendung werden dazu meist
ein Benutzername und ein Passwort herangezogen. Es kann aber auch andere
Methoden geben, die entsprechend eine andere Implementierung erfordern. Damit
die Art der Authentifizierung geändert werden kann, führt das
Yii Auth-Framework eine Identitätsklasse (engl.: identity class) ein.

Eine Identitätsklasse enthält die eigentliche Logik zur Authentifizierung und
sollte das [IUserIdentity]-Interface implementieren. So können Klassen für 
verschiedene Identifizierungsmethoden (z.B. OpenID, LDAP, Twitter
OAuth oder Facebook Connect) angelegt werden. Um eine eigene Identitätsklasse
zu erstellen, empfiehlt es sich für den Anfang, [CUserIdentity] zu erweitern.
Das ist die Basisklasse für alle Methoden, die auf Benutzername und Passwort
basieren.

Im wesentlichen muss eine neue Identitätsklasse nur die Methode 
[IUserIdentity::authenticate] implementieren. Sie kapselt die Kernlogik
zur Authentifizierung. Daneben können noch weitere identitätsbezogene Daten
deklariert werden, die während einer Benutzersitzung beständig bereitgehalten
werden sollen.

#### Ein Beispiel

Im folgenden Beispiel zeigen wir, wie man eine Identitätsklasse zur
datenbankgestützten Authentifizierung verwendet. Ein Besucher wird dazu
Benutzernamen und Passwort in ein Anmeldeformular eingeben. Diese Daten werden
dann mittels [ActiveRecord](/doc/guide/database.ar) gegen eine Benutzertabelle
in der Datenbank geprüft. Das Beispiel demonstriert dabei gleich mehreres:

1. Wie man `authenticate()` für eine DB-gestützte Prüfung der Anmeldedaten implementiert.
2. Dass man die `CUserIdentity::getId()`-Methode überschreiben kann, um die Eigenschaft `_id` statt des Benutzernamens zurückzuliefern, wie das in der Basisimplementierung der Fall ist.
3. Wie man die Methode `setState()` ([CBaseUserIdentity::setState]) verwendet, um weitere Daten dauerhaft für spätere Requests zu speichern

~~~
[php]
class UserIdentity extends CUserIdentity
{
	private $_id;
	public function authenticate()
	{
		$record=User::model()->findByAttributes(array('username'=>$this->username));
		if($record===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($record->password!==md5($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$record->id;
			$this->setState('title', $record->title);
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
    }

	public function getId()
	{
		return $this->_id;
	}
}
~~~

Im nächsten Abschnitt zu An- und Abmeldung werden wir sehen, dass diese
Identitätsklasse an die login-Methode des Benutzerobjekts übergeben wird.
Sämtliche Informationen, die (per Aufruf von [CBaseUserIdentity::setState]) in
einem Status gespeichert wurden, werden dort in einem beständigen
Speicher, wie etwa der Session, abgelegt. Diese Daten stehen dann direkt als
Eigenschaften von [CWebUser] zur Verfügung. In unserem Beispiel haben wir den
Titel über `$this->setState('title', $record->title);` gespeichert. Wurde der
Anmeldeprozess durchlaufen, kann man daher die `title`-Information des aktuellen
Benutzers über `Yii::app()->user->title` abrufen.

> Info|Info: Standardmäßig verwendet [CWebUser] die Session als beständigen
Speicher für solche Identitätsdaten. Wenn die cookie-basierte Anmeldung aktiviert
wird (indem [CWebUser::allowAutoLogin] auf true gesetzt wurde), können
Identitätsdaten auch in einem Cookie gespeichert werden. Stellen Sie sicher,
dass Sie hier keine vertraulichen Informationen (z.B. Passwörter) speichern.


An- und Abmelden
----------------

Nachdem wir an einem Beispiel demonstriert haben, wie eine Identitätsklasse
erstellt wird, kann man diese nun zum einfachen An- und Abmelden verwenden.
Das folgende Beispiel zeigt, wie dies erreicht wird:

~~~
[php]
// Benutzer mit übergebenem Benutzernamen/Passwort anmelden
$identity=new UserIdentity($username,$password);
if($identity->authenticate())
	Yii::app()->user->login($identity);
else
	echo $identity->errorMessage;
......
// Aktuellen Benutzer abmelden
Yii::app()->user->logout();
~~~

Wir erzeugen hier eine neues UserIdentity-Objekt und übergeben die
Anmeldedaten (also die `$username`- und `$password`-Werte aus dem
Anmeldeformular) an den Konstruktor. Danach rufen wir einfach die
`authenticate()`-Methode auf. Falls erfolgreich, übergeben wir die
Identitätsinformationen an die Methode [CWebUser::login], die diese dann in
einem Permanentspeicher (standardmäßig der Sesssion) ablegt, um sie für
späteren Requests verfügbar zu machen. Schlägt die Authentifizierung fehl,
können wir über die `errorMessage`-Eigenschaft weitere Informationen dazu
abfragen.

Ob ein Benutzer erfolgreich authentifiziert wurde, kann in der gesamten
Anwendung einfach über `Yii::app()->user->isGuest` geprüft werden. Verwendet
man einen Permanentspeicher, wie die Session (Standard) und/oder ein Cookie
(wie im folgenden Beschrieben), um die Identitätsinformationen zu speichern,
kann der Anwender über mehrere aufeinanderfolgende Requests angemeldet
bleiben. In diesem Fall wird die Identitätsklasse bzw. der gesamte
Anmeldeprozess nicht bei jedem Request benötigt bzw. durchlaufen. Stattdessen
lädt CWebUser die Identitätsinformationen automatisch aus dem entsprechenden 
Permanentspeicher und verwendet sie um den Rückgabewert von
`Yii::app()->user->isGuest` zu bestimmen.


Cookie-basierte Anmeldung
--------------------------

Ein Benutzer wird automatisch wieder abgemeldet, wenn er für einen bestimmten
Zeitraum nicht aktiv war. Die Dauer hängt von der 
[Session-Konfiguration](http://de2.php.net/manual/de/session.configuration.php)
ab. Möchte man das ändern, kann man die Eigenschaft
[allowAutoLogin|CWebUser::allowAutoLogin] der Userkomponente auf true setzen
und eine Dauer als zweiten Parameter an die [CWebUser::login]-Methode übergeben. Der
Benutzer bleibt dann für die angegebene Zeit angemeldet, auch wenn das
Browserfenster geschlossen wird. Beachten Sie, dass der Benutzer dazu in
seinem Browser Cookies akzeptieren muss.

~~~
[php]
// Benutzer für 7 Tage angemeldet lassen. Stellen Sie sicher, 
// dass allowAutoLogin in der Userkomponente auf true gesetzt ist
Yii::app()->user->login($identity,3600*24*7);
~~~

Wie bereits erwähnt, werden Daten, die man über [CBaseUserIdentity::setState]
speichert, ebenfalls im Cookie abgelegt, falls man die cookie-basierte
Anmeldung verwendet. Wenn der Besucher das nächste mal angemeldet wird, werden
diese Daten aus dem Cookie ausgelesen und im Status über `Yii::app()->user`
bereitgestellt.

Auch wenn Yii Maßnahmen bereitstellt, um eine Veränderung dieser Cookiedaten
auf der Clientseite zu verhindern, empfehlen wir unbedingt, keine sensiblen
Daten als Status zu speichern. Stattdessen sollte man solche Daten auf der
Serverseite in einem Permanentspeicher (z.B. einer Datenbank) ablegen und bei
Bedarf wiederherstellen.

Für jede seriöse Webanwendung empfehlen wir außerdem folgende Strategie, um
die Sicherheit bei cookie-basierter Anmeldung zu verbessern:

* Zum Zeitpunkt, wenn ein Benutzer sich erfolgreich anmeldet, erzeugt man
einen zufälligen Schlüssel, der sowohl im Statuscookie als auch im
Permanentspeicher (z.B. Datenbank) auf dem Server gespeichert wird.

* Wenn der Benutzer bei einem späteren Request per Cookie angemeldet wird, überprüft man, ob die
beiden Schlüsselwerte übereinstimmen, bevor die Anmeldung durchgeführt wird.

* Wenn der Benutzer sich neu über das Anmeldeformular einloggt, muss ein neuer
Schlüssel erzeugt werden

Damit schließt man aus, dass der Besucher ein altes Statuscookie wiederverwendet,
dessen Statusinformationen aber schon nicht mehr gültig sind.

Zur Umsetzung dieser Strategie müssen folgende beiden Methoden überschrieben
werden:

* [CUserIdentity::authenticate()]: Hier wird die eigentliche Authentifizierung
durchgeführt. Wurde der Benutzer authentifiziert, sollte man hier einen neuen
Zufallsschlüssel erzeugen und diesen sowohl in der Datenbank als auch im
Identitätstatus (mit [CBaseUserIdentity::setState]) speichern.

* [CWebUser::beforeLogin()]: Diese Methode wird beim Anmelden eines
Benutzers aufgerufen. Hier sollte man den Schlüssel aus dem Statuscookie mit
dem in der Datenbank vergleichen.

Zugangskontrollfilter
---------------------

Mit dem Zugangskontrollfilter (engl.: access control filter) lässt sich prüfen, 
ob der aktuelle Benutzer die gewünschte Controlleraction ausführen darf. Die 
Prüfung erfolgt anhand des Benutzernamens, der IP-Adresse des Clients und dem 
Requesttyp. Dieser einfache Filter steht als Anwendungskomponente 
["accessControl"|CController::filterAccessControl] bereit.

> Tip|Tipp: Der Zugangskontrollfilter ist für einfache Fälle gedacht. 
Kompliziertere Berechtigungsregeln kann man mit der rollenbasierten
Zugriffskontrolle (RBAC) umsetzen, die wir im nächsten Abschnitt
behandeln werden.

Um diesen Filter in einem Controller zu verwenden, überschreibt man die
[CController::filters]-Methode wie folgt (siehe auch
[Filter](/doc/guide/basics.controller#filter) zu näheren Details über
die Anwendung von Filtern):

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
}
~~~

Mit dieser Funktion legt man fest, dass der
[Zugangskontrollfilter|CController::filterAccessControl] auf alle Actions des
`PostController`s angewendet werden soll. Die spezifischen Berechtigungen
werden in der Methode [CController::accessRules] angegeben, die man z.B. so
überschreiben kann:

~~~
[php]
class PostController extends CController
{
	......
	public function accessRules()
	{
		return array(
			array('deny',
				'actions'=>array('create', 'edit'),
				'users'=>array('?'),
			),
			array('allow',
				'actions'=>array('delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'actions'=>array('delete'),
				'users'=>array('*'),
			),
		);
	}
}
~~~

Hier werden drei Regeln in jeweils einem Array definiert. Das erste Element
eines solchen Arrays ist entweder `'allow'` (erlaube) oder `'deny'` (verbiete). Die
weiteren Name-Wert-Paare bestimmen, wann diese Regel gilt. Die Regeln
oben bedeuten der Reihe nach, dass die `create`- und `edit`-Actions nicht von
anonymen Benutzern aufgerufen werden können. Die `delete`-Action darf von Benutzern
mit der Rolle `admin` ausgeführt werden. Und die `delete`-Action darf von
keinem Benutzer ausgeführt werden.

Die Zugriffsregeln werden eine nach der anderen in der Reihenfolge ihrer
Definition geprüft. Die erste Regel, die vollständig auf den aktuellen 
Kontext (z.B. Benutzername, Rolle, IP-Adresse) zutrifft, bestimmt das
Ergebnis. Falls es sich um eine `allow`-Regel handelt, darf die
Action ausgeführt werden, liegt eine `deny`-Regel vor, darf sie nicht
ausgeführt werden. Passt keine der definierten Regeln auf den aktuellen 
Kontext, darf die Action ausgeführt werden.

> Tip|Tipp: Um sicherzustellen, dass eine Action nicht doch unter einem bestimmten
> Kontext ausgeführt werden kann, ist es hilfreich, eine immergültige
> `deny`-Regel ans Ende seiner Regeln zu setzen: 
> ~~~
> [php]
> return array(
>     // ... Andere Regeln ...
>     // Die folgende Regel verbietet die 'delete'-Action in jedem Kontext
>     array('deny',
>         'actions'=>array('delete'),
>     ),
> );
> ~~~
> Diese Regel ist nötig, da eine Action ausgeführt werden darf, falls keine
> anderslautende passende Regel gefunden wurde.

Eine Regel kann folgende Kontextparameter enthalten:

   - [actions|CAccessRule::actions]: Definiert, welche Actions diese Regel
betrifft. Dies sollte ein Array aus Action-IDs sein, Groß-/Kleinschreibung
spielt hierbei keine Rolle.

   - [controllers|CAccessRule::controllers]: Definiert, welche Controller
diese Regel betrifft. Dies sollte ein Array aus Controller-IDs sein,
Groß-/Kleinschreibung spielt hierbei keine Rolle.

   - [users|CAccessRule::users]: Definiert, welche Benutzer diese Regel
betrifft. Zur Prüfung wird der [Name|CWebUser::name] des aktuellen Benutzers
herangezogen, Groß-/Kleinschreibung spielt hierbei keine Rolle.
Hier können drei spezielle Zeichen verwendet werden:

	   - `*`: Jeder Benutzer, inkl. anonyme und authentifizierte Benutzer.
	   - `?`: Anonyme (nicht angemeldete) Benutzer.
	   - `@`: Authentifizierte (angemeldete) Benutzer.

   - [roles|CAccessRule::roles]: Definiert, welche Rollen diese Regel
betrifft. Dazu wird die [rollenbasierte
Zugriffskontrolle](/doc/guide/topics.auth#role-based-access-control) verwendet, die wir im nächsten
Abschnitt beschreiben werden. Konkret wird diese Regel angewendet, wenn
[CWebUser::checkAccess] für eine der Rollen true zurückliefert. Beachten Sie,
dass Sie Rollen vor allem in `allow`-Regeln einsetzen sollten, da eine Rolle
per Definition eine Erlaubnis darstellt, etwas bestimmtes zu tun. Und obwohl
wir hier den Begriff `roles` (Rollen) verwenden, kann der Wert tatsächlich
jedem beliebigen Autorisierungselement entsprechen, inklusive Rollen, Tätigkeiten und
Operationen.

   - [ips|CAccessRule::ips]: Definiert, welche Client-IP-Adressen diese
Regel betrifft. 

   - [verbs|CAccessRule::verbs]: Definiert, welche Requesttypen (z.B. 'GET',
'POST') diese Regel betrifft. Groß-/Kleinschreibung spielt hierbei keine
Rolle.

   - [expression|CAccessRule::expression]: Definiert einen PHP-Ausdruck,
dessen Wert darüber entscheidet, ob die Regel zutrifft oder nicht. Im Ausdruck
können Sie `$user` für `Yii::app()->user` verwenden.


Verhalten nach der Autorisierung
--------------------------------

Wurde eine Autorisierung verweigert, so ist der Benutzer nicht berechtigt, 
die gewünschte Action auszuführen und es passiert folgendes:

   - War der Benutzer nicht angemeldet und zeigt die Eigenschaft
[loginUrl|CWebUser::loginUrl] der Userkomponente auf die URL der Anmeldeseite,
so wird der Browser auf diese Seite umgeleitet. Beachten Sie, dass
[loginUrl|CWebUser::loginUrl] standardmäßig auf die Seite `site/login` zeigt.

   - In allen anderen Fällen wird eine HTTP-Exception mit dem Fehlercode 403 angezeigt.

[loginUrl|CWebUser::loginUrl] kann als relative oder absolute URL angegeben werden. 
Man kann auch ein Array konfigurieren, das dann an [CWebApplication::createUrl] 
übergeben wird, um damit eine URL zu erzeugen.
Das erste Element dieses Arrays sollte die [Route](/doc/guide/basics.controller#route) 
zur Anmeldeaction angeben. Der Rest kann aus Name-Wert-Paaren für GET-Parameter bestehen. 
Hier ein Beispiel:

~~~
[php]
array(
	......
	'components'=>array(
		'user'=>array(
			// Dies entspricht dem Vorgabewert
			'loginUrl'=>array('site/login'),
		),
	),
)
~~~

Wurde der Browser auf die Anmeldeseite umgeleitet und verläuft die Anmeldung
erfolgreich, kann man den Browser zurück zu der Seite schicken, bei der
die Autorisierung verweigert wurde. Die URL dieser Seite kann über die Eigenschaft 
[returnUrl|CWebUser::returnUrl] der Userkomponente abgerufen werden: 

~~~
[php]
Yii::app()->request->redirect(Yii::app()->user->returnUrl);
~~~

Rollenbasierte Zugriffskontrolle
--------------------------------

Die rollenbasierte Zugriffskontrolle (RBAC, engl.: role-based access control)
bietet eine einfache und trotzdem leistungsfähige zentralisierte
Zugriffssteuerung. Für weitere Ausführungen zum Vergleich von RBAC mit anderen
traditionellen Verfahren der Berechtigungsprüfung beachten Sie bitte auch den
entsprechenden [Wiki-Artikel](http://de.wikipedia.org/wiki/RBAC) (evtl. auch
in der ausührlicheren [englischen
Version](http://en.wikipedia.org/wiki/Role-based_access_control)).

Yii implementiert über seine
[authManager|CWebApplication::authManager]-Komponente ein hierarchisches 
RBAC-Schema. Im folgenden behandeln wir zunächst die Grundkonzepte dieses
Schemas. Danach beschreiben wir, wie man Autorisierungsdaten definiert und
schließlich wie man diese für die Berechtigungsprüfung verwendet.

### Übersicht

Einer der grundlegenden Begriffe bei RBAC mit Yii ist das
*Autorisierungselement* (engl.: authorization item). Ein Autorisierungselement
steht für die Erlaubnis, etwas bestimmtes zu tun (z.B. einen Blogeintrag anzulegen
oder Benutzer zu verwalten). Gemäß ihrer Beschaffenheit und dem anvisierten
Zielpublikum können Autorisierungselemente in *Operationen* (engl.:
operations), *Tätigkeiten* (engl.: tasks) und *Rollen* (engl.: roles) eingeteilt
werden. Eine Rolle besteht aus Tätigkeiten, eine Tätigkeit aus Operationen. Eine
Operation steht für eine atomare Berechtigung. 

In einem System kann es zum Beispiel eine Rolle `administrator` geben. Sie 
besteht aus den Tätigkeiten `Beiträge verwalten` und `Benutzer verwalten`. 
Die Tätigkeit `Benutzer verwalten` könnte aus den Operationen
`Benutzer anlegen`, `Benutzer aktualisieren` und `Benutzer löschen` bestehen. 
Um das System noch flexibler zu machen, erlaubt es Yii
sogar, dass eine Rolle aus weiteren Rollen oder Operationen, eine
Tätigkeit aus anderen Tätigkeiten und eine Operation aus anderen Operationen
besteht.

Ein Autorisierungselement wird eindeutig über seinen Namen identifiziert und
kann mit einer *Geschäftsregel* (engl.: business
rule) verbunden sein. Eine Geschäftsregel ist ein PHP-Schnippsel, das
ausgeführt wird, wenn die Berechtigung für das Element geprüft wird.
Liefert dieser Code true zurück, so ist der Benutzer zu diesem Element
berechtigt.  Definiert man zum Beispiel eine Operation `aktualisiereBeitrag`, 
kann man eine Geschäftsregel hinzufügen, die prüft, ob die ID des Benutzers 
mit derjenigen des Beitragsautors übereinstimmt, so dass nur der Autor selbst
berechtigt ist, seine Beiträge zu aktualisieren.

Durch den Einsatz von Autorisierungselementen kann man eine
*Autorisierungshierarchie* aufbauen. Ein Element `A` ist das Elternelement
eines anderen Elements `B` in der Hierarchie, wenn `A` aus `B` besteht (oder
anders ausgedrückt `A` die von `B` dargestellten Berechtigung(en) erbt).
Ein Element kann sowohl mehrere Kind- als auch mehrere Elternelemente
haben. Eine Autorisierungshierarchie ist daher eher ein Graph partieller 
Ordnung als eine Baumstruktur. In dieser Hierarchie stehen Rollen auf der
obersten Ebene, Operationen auf der untersten und Tätigkeiten zwischen diesen
beiden.

Wurde die Autorisierungshierarchie einmal erstellt, kann man Benutzer zu den 
Rollen in dieser Hierarchie hinzufügen. Ein Benutzer, dem eine Rolle
zugewiesen wurde, hat alle von dieser Rolle dargestellten Berechtigungen. Weist man 
einem Benutzer z.B. die Rolle `administrator` zu, hat er Administratorrechte,
was die Tätigkeiten `Beiträge verwalten` und `Benutzer verwalten` beinhaltet 
(sowie die zugehörigen Operationen wie `Benutzer anlegen`). 

Am einfachsten stellt sich die Anwendung dar: Möchte man in einer 
Controlleraction prüfen, ob der aktuelle Benutzer den Beitrag 
löschen kann, geht das so:

~~~
[php]
if(Yii::app()->user->checkAccess('löscheBeitrag'))
{
	// Beitrag löschen
}
~~~

Konfigurieren des Autorisierungsmanagers 
----------------------------------------

Bevor man eine Autorisierungshierarchie anlegen und den Zugriffsschutz
einsetzen kann, muss die [authManager|CWebApplication::authManager]-Komponente 
konfiguriert werden. Yii bietet hier zwei Typen an: [CPhpAuthManager] und [CDbAuthManager].
Der erste speichert die Autorisierungsdaten in einer PHP-Datei, der andere
in der Datenbank. Über die Klasse kann man einen dieser Typen, sowie dessen
Starteigenschaften konfigurieren:


~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'sqlite:pfad/zu/datei.db',
		),
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'connectionID'=>'db',
		),
	),
);
~~~

Jetzt kann man über `Yii::app()->authManager` auf den
[authManager|CWebApplication::authManager] zugreifen.

> Note|Hinweis: Wenn Sie Umlaute für die Bezeichnung Ihrer
Autorisierungselemente verwenden möchten, achten Sie bitte darauf, dass sie
die entsprechenden Tabellen mit UTF-8-Codierung anlegen und sie bei der
Konfiguration der Datenbankverbindung die Eigenschaft 
[CDbConnection::charset] ebenfalls auf `utf8` setzen.

Anlegen einer Autorisierungshierarchie
--------------------------------------

Das Anlegen einer Autorisierungshierarchie beinhaltet drei Schritte: 
Autorisierungselemente anlegen, zwischen diesen Elementen Beziehungen 
definieren und Benutzern Rollen zuweisen. Die
[authManager|CWebApplication::authManager]-Komponente bietet hierfür 
eine ganze Reihe von APIs.

Rufen Sie je nach Art des Elements eine der folgenden Methoden auf, um ein
Autorisierungselement zu erstellen:

   - [CAuthManager::createRole] (erzeugt Rolle)
   - [CAuthManager::createTask] (erzeugt Tätigkeit)
   - [CAuthManager::createOperation] (erzeugt Operation)

Hat man eine Reihe von Autorisierungselementen angelegt, kann man mit
den folgenden Methoden Beziehungen zwischen diesen Elementen definieren:

   - [CAuthManager::addItemChild] (definiert Eltern-Kind-Beziehung)
   - [CAuthManager::removeItemChild] (entfernt Eltern-Kind-Beziehung)
   - [CAuthItem::addChild] (definiert Kind-Beziehung von Elternelement aus)
   - [CAuthItem::removeChild] (entfernt Kind-Beziehung von Elternelement aus)

Um schließlich einzelnen Benutzern Rollen zuzuweisen, ruft man folgende Methoden
auf:

   - [CAuthManager::assign] (weist Rolle zu)
   - [CAuthManager::revoke] (entfernt zugewiesene Rolle)

Unten sehen Sie ein Beispiel, wie man mit dieser API eine
Autorisierungshierarchie aufbaut:

~~~
[php]
$auth=Yii::app()->authManager;

$auth->createOperation('erstelleBeitrag','Einen Beitrag erstellen');
$auth->createOperation('leseBeitrag','Einen Beitrag lesen');
$auth->createOperation('aktualisiereBeitrag','Einen Beitrag aktualisieren');
$auth->createOperation('löscheBeitrag','Einen Beitrag löschen');

$bizRule='return Yii::app()->user->id==$params["post"]->authID;';
$task=$auth->createTask('aktualisiereEigenenBeitrag','Einen eigenen Beitrag aktualisieren',$bizRule);
$task->addChild('aktualisiereBeitrag');

$role=$auth->createRole('leser');
$role->addChild('leseBeitrag');

$role=$auth->createRole('autor');
$role->addChild('leser');
$role->addChild('erstelleBeitrag');
$role->addChild('aktualisiereEigenenBeitrag');

$role=$auth->createRole('redakteur');
$role->addChild('leser');
$role->addChild('aktualisiereBeitrag');

$role=$auth->createRole('admin');
$role->addChild('redakteur');
$role->addChild('autor');
$role->addChild('löscheBeitrag');

$auth->assign('leser','leserA');
$auth->assign('autor','autorB');
$auth->assign('redakteur','redakteurC');
$auth->assign('admin','adminD');
~~~

Wurden diese Hierarchie einmalig erstellt, wird sie automatisch von der
[authManager|CWebApplication::authManager]-Komponente (also
z.B. [CPhpAuthManager] oder [CDbAuthManager]) geladen. Man muss obigen
Code also nur einmal ausführen, NICHT bei jeden Request.

> Info|Info: Das Verfahren oben mutet etwas umständlich an. Es soll
> aber lediglich das Prinzip demonstrieren. In der Regel wird der 
> Entwickler eine geeignete Verwaltungsschnittstelle entwerfen, mit
> der man Autorisierungshierarchien intuitiver erstellen kann.


Anwendung von Geschäftsregeln
-----------------------------

Erstellt man eine Autorisierungshierarchie, kann man eine Rolle, eine
Tätigkeit oder eine Operation mit einer sogenannten *Geschäftsregel* versehen.
Auch beim Zuweisen einer Rolle an einen Benutzer kann man eine solche
Geschäftsregel angeben. Eine Geschäftsregel ist ein PHP-Schnippsel, 
der während der Berechtigungsprüfung ausgeführt wird. In obigem Beispiel 
wurde bei der Tätigkeit `aktualisiereEigenenBeitrag`
eine solche Geschäftsregel definiert. Darin wird einfach geprüft,
ob die ID des aktuellen Benutzers mit der des Autors übereinstimmt.
Bei der Zugriffsprüfung wird der Beitrag (post) vom
Entwickler im Array `$params` übergeben.

### Berechtigungsprüfung 

Um die Berechtigungsprüfung durchzuführen, braucht man zunächst den Namen des
Autorisierungselements. Will man zum Beispiel zu testen, ob der aktuelle
Benutzer einen Beitrag erstellen kann, muss die Berechtigung zur Operation
`erstelleBeitrag` ermittelt werden. Dazu ruft man [CWebUser::checkAccess] 
wie folgt auf:

~~~
[php]
if(Yii::app()->user->checkAccess('erstelleBeitrag'))
{
	// Beitrag erstellen
}
~~~

Ist eine Geschäftsregel mit weiteren Parametern mit dem Element verbunden,
können diese ebenfalls mit übergeben werden. Um zum Beispiel zu prüfen, ob
ein Benutzer einen bestimmten Beitrag aktualisieren darf, würde man die
Beitragsdaten in `$params` übergeben:

~~~
[php]
$params=array('post'=>$post);
if(Yii::app()->user->checkAccess('aktualisiereEigenenBeitrag',$params))
{
	// Beitrag aktualisieren
}
~~~

### Verwenden von Standardrollen

In vielen Webanwendungen gibt es einige Rollen, denen praktisch alle Benutzer
zugewiesen werden sollen, um z.B. alle authentifizierten Benutzer mit
bestimmten Basisberechtigungen zu versehen. Man könnte also diesen Rollen
jeden einzelnen Benutzer zuwiesen, was allerdings relativ hohen Verwaltungsaufwand
bedeutet. Stattdessen kann man aber auch *Standardrollen* (engl.: default roles) verwenden.

Eine Standardrolle ist eine Rolle, die implizit allen Benutzern zugewiesen
wird und zwar authentifizierten Benutzern genauso, wie Gästen. Man muss ihnen
nicht explizit Benutzer zuweisen. Beim Aufruf von
[CWebUser::checkAccess] werden zunächst die Standardrollen überprüft, als ob
sie dem Benutzer zugewiesen worden wären.

Standardrollen müssen in der Eigenschaft [CAuthManager::defaultRoles]
deklariert werden. Die folgende Konfiguration legt zum Beispiel zwei
Standardrollen fest: `authentifiziert` und `gast`.

~~~
[php]
return array(
	'components'=>array(
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'defaultRoles'=>array('authentifiziert', 'gast'),
		),
	),
);
~~~

Da eine Standardrolle jedem Benutzer zugewiesen wird, wird sie normalerweise
mit einer Geschäftsregel verbunden, um festzustellen, ob die Rolle
wirklich auf den Benutzer zutrifft. Der folgende Code definiert zum Beispiel
zwei Rollen, "authentifiziert" und "gast", die letzendlich authentifizierten
Benutzern und Gästen entsprechend zugeordnet werden.

~~~
[php]
$bizRule='return !Yii::app()->user->isGuest;';
$auth->createRole('authentifiziert','Autentifizierte Benutzer', $bizRule);

$bizRule='return Yii::app()->user->isGuest;';
$auth->createRole('gast','Gast-Benutzer', $bizRule);
~~~

<div class="revision">$Id: topics.auth.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
