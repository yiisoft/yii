Controller
==========

Ein `Controller` ist eine Instanz von [CController] oder einer davon
abgeleiteten Klasse. Er wird von der Applikation erzeugt, wenn ein Request
dafür vorliegt. Wird ein Controller gestartet,
führt er die angeforderte Action aus, die dann in der Regel die benötigten Models
einbindet und einen passenden View rendert (sinngem.: macht).
Die einfachste Form einer `Action` ist eine Methode im Controller,
deren Name mit `action` anfängt.

Jeder Controller hat eine Standardaction. Sie wird ausgeführt, wenn aus
dem Request nicht hervorgeht, welche Action ausgeführt werden soll.
Als Vorgabewert ist dafür die Action `index` definiert.
Überschreibt man [CController::defaultAction], kann die Standardaction verändert werden.

Der folgende Code definiert einen Controller `site` sowie die beiden Actions
`index` (die Standardaction) und `contact`.

~~~
[php]
class SiteController extends CController
{
	public function actionIndex()
	{
		// ...
	}

	public function actionContact()
	{
		// ...
	}
}
~~~

Route
-----

Controller und Action werden über ihre ID identifiziert. Eine Controller-ID
hat das Format `pfad/zu/xyz`, was dem Controller
`protected/controllers/pfad/zu/XyzController.php` entspricht. Das Kürzel
`xyz` sollte natürlich durch echte Namen ersetzt werden (z.B. entspricht `post`
der Datei `protected/controllers/PostController.php`). Die Action-ID
entspricht dem Namen einer Actionmethode, ohne das vorangestellte `action`.
Enthält ein Controller z.B. die Methode `actionEdit` ist die ID dieser Action
`edit`.

Um eine bestimmte Controlleraction aufzurufen, wird im Request eine sogenannte
`Route` angefordert. Sie setzt sich aus Controller- und Action-ID zusammen,
getrennt durch einen Schrägstrich (/). Die Route `post/edit` bezieht sich
somit auf die `edit`-Action im `PostController`. In der Voreinstellung
würde diese Action über die URL `http://hostname/index.php?r=post/edit` aufgerufen.

>Note|Hinweis: Normalerweise ist die Schreibweise (groß/klein) bei Routen von
>Bedeutung. Dies kann man aber deaktivieren, indem man
>[CUrlManager::caseSensitive] in der Konfiguration auf false setzt.
>Falls Groß-/Kleinschreibung aktiviert ist, stellen Sie bitte sicher, dass
>Verzeichnisnamen von Controllern sowie die Schlüssel in
>[controllerMap|CWebApplication::controllerMap] und
>[actionMap|CController::actions] klein geschrieben werden.

Eine Anwendung kann [Module](/doc/guide/basics.module) enthalten. Die Route zu
einer Controlleraction in einem Modul entspricht dem Format `modulID/controllerID/actionID`.
Detaillierte Informationen hierzu finden Sie im [Abschnitt über Module](/doc/guide/basics.module).


Instanziieren eines Controllers
-------------------------------

Beim Bearbeiten eines Requests erzeugt [CWebApplication] eine
Controllerinstanz. Die Applikation geht folgendermaßen vor, um
die Klassendatei zu einer gegebenen Controller-ID zu finden:

   - Ist [CWebApplication::catchAllRequest] gesetzt, wird dieser
Wert zum Suchen des Controllers verwendet und die Controller-ID im Request
ignoriert. Dies wird hauptsächlich verwendet, um die
Anwendung in den Wartungsmodus zu schalten und eine statische Hinweisseite
anzuzeigen.

   - Wenn die ID in [CWebApplication::controllerMap] enthalten ist, wird die
Controllerinstanz entsprechend dieser Konfiguration erstellt.

   - Liegt die ID im Format `'pfad/zu/xyz'` vor, wird von der
Controllerklasse `XyzController` in der Datei
`protected/controllers/pfad/zu/XyzController.php` ausgegangen. Die
Controller-ID `admin/user` würde zum Beispiel in die Controllerklasse
`UserController` in der Datei `protected/controllers/admin/UserController.php`
aufgelöst werden. Existiert die Klassendatei nicht, wird eine
404-[CHttpException] ausgelöst.

Falls [Module](/doc/guide/basics.module) verwendet werden, unterscheidet sich
der obige Prozess etwas. Die Anwendung prüft in
diesem Fall, ob sich die ID auf einen Controller eines Moduls bezieht. Falls
ja, erzeugt sie zunächst die Modulinstanz und danach die Controllerinstanz.

Action
------

Wie erwähnt, kann eine Action eine Controllermethode sein, deren
Name mit `action` beginnt. Stattdessen kann man auch die etwas
fortgeschrittenere Methode einer Actionklasse verwenden und den Controller
bitten, diese auf Anfrage zu instanziieren. Actions können so mehrfach
eingesetzt werden, was der Wiederverwendbarkeit zugute kommt.

Eine Actionklasse wird wie folgt definiert:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// Hier steht die Programmlogik der Action
	}
}
~~~

Damit der Controller die Action kennt, überschreibt man die
[actions()|CController::actions]-Methode einer Controller-Klasse:

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

Dieses Beispiel verwendet den Pfadalias
`application.controllers.post.UpdateAction`. Damit wird angegeben, dass sich die
Actionklasse in `protected/controllers/post/UpdateAction.php` befindet.

Indem man klassenbasierte Actions verwendet, kann man eine Anwendung
modular organisieren. So könnte man zur Ablage des Controllercodes z.B.
diese Verzeichnisstruktur verwenden:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~


### Binden von Actionparametern

Seit Version 1.1.4 unterstützt Yii das automatische Binden von
Actionparametern. Das bedeutet, dass eine Controlleraction Parameter
definieren kann, deren Werte automatisch entsprechend ihrem Namen aus
`$_GET` befüllt werden.

Nehmen wir zum besseren Verständnis an, ein `PostController` soll eine neue
Action `create` bekommen, die folgende zwei Parameter benötigt:

* `category`: Eine Integerzahl, die für die Kategorie-ID steht, unter der ein
neuer Beitrag angelegt werden soll;
* `language`: Eine Zeichenkette, die die Sprache des neuen Beitrags angibt.

Eine mögliche, allerdings relativ langweilige Umsetzung könnte die benötigten
`$_GET`-Parameter wie folgt auslesen:

~~~
[php]
class PostController extends CController
{
	public function actionCreate()
	{
		if(isset($_GET['category']))
			$category=(int)$_GET['category'];
		else
			throw new CHttpException(404,'invalid request');

		if(isset($_GET['language']))
			$language=$_GET['language'];
		else
			$language='en';

		// ... fun code starts here ...
	}
}
~~~

Verwendet man stattdessen das Actionparameter-Feature, vereinfacht sich diese
Aufgabe wie folgt:

~~~
[php]
class PostController extends CController
{
	public function actionCreate($category, $language='en')
	{
		$category=(int)$category;

		// ... fun code starts here ...
	}
}
~~~

Beachten Sie, dass die Actionmethode `actionCreate` jetzt zwei Aufrufparameter
erhalten hat. Deren Name muss exakt mit den in `$_GET` erwarteten Parametern
übereinstimmen. Für den `$language`-Parameter is außerdem der Vorgabewert `en`
definiert. Er wird verwendet, wenn kein `language`-Wert in `$_GET` enthalten
ist. Da für `$category` kein solcher Vorgabewert definiert wurde, wird
automatisch eine [CHttpException] mit Fehlercode 400 geworfen, falls dieser
Parameter nicht im Request übergeben wird.

Seit Version 1.1.5 kann Yii auch automatisch Parameter vom Typ Array erkennen.
Dazu verwendet man das Type Hinting Feature von PHP wie folgt:

~~~
[php]
class PostController extends CController
{
	public function actionCreate(array $categories)
	{
		// Yii stellt sicher, dass $categories ein Array ist
	}
}
~~~

Man gibt also in der Funktionsdeklaration das Schlüsselwort `array` vor
`$categories` an. Falls `$_GET['categories']` ein String ist, wird es
automatisch in ein Array (mit diesem String als einzigem Element) umgewandelt.

> Note|Hinweis: Falls ein Parameter ohne Angabe von `array` deklariert wurde,
> *muss* ein Skalarwert (also kein Arrray!) übergeben werden. Ist der
> Parameter in `$_GET` trotzdem ein Array, wird eine HTTP-Exception
> geworfen.

Seit Version 1.1.7 werden Parameter auch bei klassenbasierten Actions
gebunden. Wurde die `run()`-Methode einer Actionklasse mit Parametern
definiert, werden diese mit den Werten der entsprechenden GET-Parameter
belegt. Zum Beispiel:

~~~
[php]
class UpdateAction extends CAction
{
	public function run($id)
	{
		// $id ist mit $_GET['id'] vorbelegt
	}
}
~~~


Filter
------

Bei einem Filter handelt es sich um Code, der je nach Konfiguration vor
und/oder nach einer Action ausgeführt wird. Ein Filter könnte z.B. die
Zugriffskontroller übernehmen und sicherstellen, dass ein Benutzer
authentifiziert wurde, bevor eine Action ausgeführt wird. Mit einem
Performancefilter könnte man die benötigte Zeit für eine Action messen, etc.

Für eine Action können mehrere Filter definiert sein. Sie werden in der
Reihenfolge ausgeführt, in der sie in der Liste der Filter erscheinen. Ein
Filter kann verhindern, dass die Action oder die restlichen Filter ausgeführt
werden.

Ähnlich wie eine ACtion kann ein Filter eine Controllermethode sein, deren
Name mit `filter` beginnt. Die Methode `filterAccessControl` würde also z.B.
den Filter `accessControl` definieren. Eine Filtermethode muss dabei dieser
Signatur entsprechen:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// Rufen Sie $filterChain->run() auf, um mit der Filterung
	// fortzufahren bzw. die Action auszuführen.
}
~~~

`$filterChain` (Filterkette) ist eine Instanz vom Typ [CFilterChain].
Sie stellt die Liste der Filter dar, die mit der Action verbunden sind. Innerhalb
der Filtermethode kann `$filterChain->run()` aufgerufen werden, um mit der Filterung
fortzufahren, bzw. die Action auszuführen.

Ein Filter kann auch einen Instanz von [CFilter] oder einer davon abgeleiteten
Klasse sein. Der folgende Code definiert eine neue Filterklasse:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// Programmlogik, die vor der Action ausgeführt wird
		return true; // false, wenn die Action nicht ausgeführt werden soll
	}

	protected function postFilter($filterChain)
	{
		// Programmlogik, die nach der Action ausgeführt wird
	}
}
~~~

Um eine Action mit einem Filter zu versehen, muss die Methode
`CController::filters()` überschrieben werden. Die Methode sollte ein Array von
Filterkonfigurationen zurückliefern. Zum Beispiel

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

Hier werden zwei Filter definiert: `postOnly` und `PerformanceFilter`.
Der `postOnly`-Filter ist methodenbasiert (die entsprechende Filtermethode ist
bereits in [CController] definiert), während der `PerformanceFilter`
als Objekt vorliegt. Mit dem Pfadalias `application.filters.PerformanceFilter`
geben wir an, dass sich die Filterklasse in `protected/filters/PerformanceFilter.php`
befindet. Da ein Array für die Konfiguration von `PerformanceFilter` verwendet
wird, können damit auch gleich die Starteigenschaften des Filterobjekts
definiert werden.  Im Beispiel setzen wir die Eigenschaft `unit` auf `'second'`.

Durch Plus- und Minusoperatoren kann man bestimmen, auf welche Actions der
Filter angewendet werden soll und auf welche nicht. Oben wird der Filter
`postOnly` auf die Actions `edit` und `create` und `PerformanceFilter`
auf alle Actions AUSSER `edit` und `create` angewendet. Falls
weder Plus noch Minus in der Filterkonfiguration auftauchen, wird der Filter
auf alle Actions angewendet.

<div class="revision">$Id: basics.controller.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>
