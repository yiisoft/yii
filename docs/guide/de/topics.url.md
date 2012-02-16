URL-Management
==============

Für ein umfassendes URL-Management sind zwei Dinge zu berücksichtigen. Zum
einen muss die Anwendung bei einem eingehenden Benutzer-Request die vorliegende
URL *auswerten*, also in verständliche Parameter übersetzen. Zum anderen muss
man mit der Anwendung auch solche URLs *erzeugen* können. Bei einer
Yii-Applikation werden diese Aufgaben von [CUrlManager] übernommen.


Erstellen von URLs
------------------

Obwohl man in Views statische URLs verwenden kann, bleibt man meist flexibler,
wenn man sie dynamisch erzeugt:

~~~
[php]
$url=$this->createUrl($route,$params);
~~~

wobei `$this` sich auf die Controller-Instanz bezieht. `$route` gibt die
[Route](/doc/guide/basics.controller#route) des Requests an und `$params` eine
Liste von `GET`-Parametern, die an die URL angehängt werden sollen.

Standardmässig werden mit [createUrl|CController::createUrl] erstellte URLs im
sogenannten `get`-Format erzeugt. Für die Werte `$route='post/read'` und
`$params=array('id'=>100)` würden man zum Beispiel die folgende URL erhalten:

~~~
/index.php?r=post/read&id=100
~~~

wobei Parameter im Anfragestring (der Teil hinter dem ?) als Liste von `Name=Wert` Elementen
enthalten sind, die durch eine UND-Zeichen (&) getrennt werden. Der Parameter
`r` gibt die angeforderte [Route](/doc/guide/basics.controller#route) an.
Dieses URL-Format ist nicht sehr anwenderfreundlich, da es etliche
Sonderzeichen enthält.

Mit dem sogenannten `path`-Format (Pfad-Format) kann man die obige URL etwas
sauberer und selbsterklärender machen. Es entfernt den Anfragestring
und bringt die GET-Parameter in der Pfadangabe der URL unter:

~~~
/index.php/post/read/id/100
~~~

Um das URL-Format zu ändern, muss man die Anwendungskomponente
[urlManager|CWebApplication::urlManager] so konfigurieren, dass
[createUrl|CController::createUrl] automatisch das neue Format verwendet
und die Anwendung die neuen URLs auch richtig interpretiert:

~~~
[php]
array(
    ......
    'components'=>array(
        ......
        'urlManager'=>array(
            'urlFormat'=>'path',
        ),
    ),
);
~~~

Beachten Sie, dass man die Klasse für die
[urlManager|CWebApplication::urlManager]-Komponente nicht angeben muss, da
sie von [CWebApplication] bereits mit dem Wert [CUrlManager] vorbelegt wurde.

> Tip|Tipp: URLs die mit [createUrl|CController::createUrl] erzeugt werden,
sind relativ. Um absolute URLs zu erhalten, kann man ihnen entweder
`Yii::app()->request->hostInfo` voranstellen, oder
[createAbsoluteUrl|CController::createAbsoluteUrl] aufrufen.

Benutzerfreundliche URLs
------------------------

Verwendet man das URL-Format `path`, können URLs noch anwenderfreundlicher
gemacht werden, indem man einige URL-Regeln definiert. Damit kann man
dann kurze URLs wie `/post/100` statt der langen `/index.php/post/read/id/100`
erzeugen. URL-Regeln werden von [CUrlManager] sowohl zum Erstellen als auch zum
Auswerten von URLs verwendet.

Um URL-Regeln anzugeben, wird die Eigenschaft [rules|CUrlManager::rules]
der [urlManager|CWebApplication::urlManager]-Komponente konfigurieren:

~~~
[php]
array(
    ......
    'components'=>array(
        ......
        'urlManager'=>array(
            'urlFormat'=>'path',
            'rules'=>array(
                'pattern1'=>'route1',
                'pattern2'=>'route2',
                'pattern3'=>'route3',
            ),
        ),
    ),
);
~~~

Die Regeln werden als Array von Suchmuster-Routen-Paaren angegeben, bei denen
jeder Eintrag einer einzelnen Regel entspricht. Das Suchmuster (engl.:
pattern) einer Regel ist ein String, der zum Auffinden der Pfadangaben in
einer URL verwendet wird. Die [Route](/doc/guide/basics.controller#route)
sollte einer gültigen Controller-Route entsprechen.

Um für eine Regel weitere Optionen anzugeben, kann man stattdessen auch dieses
Format verwenden:

~~~
[php]
'pattern1'=>array('route1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

Seit Version 1.1.7 kann man auch mehrere Regeln für das selbe Muster wie folgt
definieren:

~~~
[php]
array('route1', 'pattern'=>'pattern1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

Hier die möglichen Optionen für jede Regel:

   - [pattern|CUrlRule::pattern]: Das Suchmuster, dem erzeugte, bzw. zu
analysierende URLs entpsrechen müssen. Seit Version 1.1.7 verfügbar.

   - [urlSuffix|CUrlRule::urlSuffix]: Die Endung (Suffix) speziell für diese
   Regel. Standardmäßig null, was bedeutet, dass der Wert von
   [CUrlManager::urlSuffix] verwendet wird.

   - [caseSensitive|CUrlRule::caseSensitive]: Ob bei dieser Regel
   Groß-/Kleinschreibung beachtet werden soll. Standardmäßig null, was
   bedeutet, dass die Einstellung in [CUrlManager::caseSensitive] verwendet
   wird.

   - [defaultParams|CUrlRule::defaultParams]: Die GET-Parameter (Name=>Wert)
    für diese Regel. Wenn diese Regel für einen eingehenden Request verwendet
    wird, werden die hier angegebenen Werte nach $_GET eingeschleust.

   - [matchValue|CUrlRule::matchValue]: Ob die GET-Parameter beim Erzeugen
   einer URL mit den entsprechenden Submustern übereinstimmen müssen.
   Standardmäßig null, was bedeutet, dass der Wert von
   [CUrlManager::matchValue] verwendet wird. Wird dieser Option auf false
   gesetzt, wird diese Regel zum Erzeugen der URL verwendet, falls Route und
   Parameternamen mit den gegebenen Werten übereinstimmen. Ist die Option true
   dann müssen die gegebenen Parameterwerte auch mit den entsprechenden
   Submusterwerten übereinstimmen. Beachten Sie, dass letzteres die
   Geschwindigkeit negativ beeinflussen kann.

   - [verb|CUrlRule::verb]: Das HTTP-Verb (z.B. `GET`, `POST`, `DELETE`) für
    das diese Regel gültig sein soll. Der Vorgabewert null bedeutet, dass die
    Regel für alle HTTP-Verben gilt. Soll eine Regel auf mehrere Verben zutreffen,
    müssen diese mit Komma getrennt werden. Beim Auswerten eines Requests wird eine Regel
    übersprungen, wenn das aktuelle Verb nicht mit dem/den angegebenen Verb(en) übereinstimmt.
    Diese Option wird nur beim Auswerten verwendet und ist für REST-Support nützlich.
    Sie steht seit Version 1.1.7 zur Verfügung.

   - [parsingOnly|CUrlRule::parsingOnly]: ob diese Regel nur zum Auswerten
   eines Requests verwendet werden soll. Vorgabewert is `false`, wodurch eine Regel
   sowohl zum Auswerten als auch zum Erstellen von URLs verwendet wird.
   Diese Option steht seit Version 1.1.7 zur Verfügung.


Verwenden von benannten Parametern
----------------------------------

Eine Regel kann mit einigen GET-Parametern verknüpft werden. Diese
GET-Parameter erscheinen innerhalb der Regel in diesem Format:

~~~
&lt;ParamName:ParamMuster&gt;
~~~

wobei `ParamName` den Namen des GET-Parameters angibt und das optionale
`ParamMuster` einen regulären Ausdruck, der für das Auffinden dieses
Parameters verwendet werden soll. Falls `ParamMuster` nicht angegeben wird,
bedeutet das, dass alle Zeichen außer dem Schrägstrich `/` als Parameterwert
verwendet werden.  Beim Erstellen einer URL werden diese
Parameterplatzhalter durch die entsprechenden Parameterwerte ersetzt. Und beim
Auswerten einer URL werden die entsprechenden GET-Parameter mit den gefundenen
Werten gefüllt.

Die folgenden drei Beispiele sollen verdeutlichen, wie URL-Regeln
funktionieren:

~~~
[php]
array(
    'posts'=>'post/list',
    'post/<id:\d+>'=>'post/read',
    'post/<year:\d{4}>/<title>'=>'post/read',
)
~~~

   - Ruft man `$this->createUrl('post/list')` auf, erzeugt dies
`/index.php/posts`. Die erste Regel wird angewendet.

   - `$this->createUrl('post/read',array('id'=>100))` erzeugt
`/index.php/post/100`. Die zweite Regel wird angewendet.

   - `$this->createUrl('post/read',array('year'=>2008,
'title'=>'a sample post'))` erzeugt
`/index.php/post/2008/a%20sample%20post`. Die dritte Regel wird angewendet.

   - `$this->createUrl('post/read')` liefert
`/index.php/post/read`. Keine der Regeln wird angewendet.

Zusammenfassend kann man sagen, dass beim Aufruf von
[createUrl|CController::createUrl] anhand der übergebenen Route- und GET-Parameter
entschieden wird, welche Regel zum Einsatz kommt. Eine Regel
wird dann zur Erzeugung der URL verwendet, wenn jeder Parameter aus der Regel
in den an [createUrl|CController::createUrl] übergebenen GET-Parametern
vorgefunden wird und außerdem die Route der Regel mit der übergebenen
Route übereinstimmt.

Wenn an [createUrl|CController::createUrl] mehr GET-Parameter übergeben
wurden, als in einer Regel vorkommen, tauchen die zusätzlichen Parameter im
Anfragestring auf. Ruft man zum Beispiel
`$this->createUrl('post/read', array('id'=>100, 'year'=>2008))` auf, liefert
dies `/index.php/post/100?year=2008`. Um diese zusätzlichen Parameter
in der Pfadangabe erscheinen zu lassen, kann man `/*` an eine Regel
anhängen. Mit der Regel `post/<id:\d+>/*` wird die URL dadurch zu
`/index.php/post/100/year/2008`.


Wie erwähnt dienen URL-Regeln auch zum Auswerten von angeforderten URLs.
Normalerweise ist das der umgekehrte Fall zum Erstellen einer URL. Wenn ein
Anwender zum Beispiel `/index.php/post/100` anfordert, kommt die zweite der
obigen Regeln zum Einsatz. Sie löst die Route zu `post/read` und die
GET-Parameter zu `array('id'=>100)` (erreichbar über `$_GET`) auf.

> Note|Hinweis: Der Einsatz von URL-Regeln verringert die Performance
einer Anwendung. Das liegt daran, dass [CUrlManager] beim Auswerten einer URL
für jede Regel prüft, ob sie auf die Anfrage passt, bis eine passende
Regel gefunden wurde. Je mehr Regeln definiert wurden, desto größer ist die
Auswirkung auf die Performance . Eine Webanwendung mit hohem
Traffic-Aufkommen sollte daher die Anzahl ihrer URL-Regeln minimieren.


Parametrisierte Routen
----------------------

Man kann benannte Parameter auch im Routen-Teil einer Regel
ansprechen. Dadurch kann die Regel je nach Suchkriterium auf mehrere Routen
angewendet werden. Es kann auch helfen, die Anzahl der benötigten Regeln in
einer Anwendung zu minimieren und dadurch die Gesamtperformance zu
steigern.

Hier ein Beispiel, wie Routen mit benannten Parametern parametrisiert werden:

~~~
[php]
array(
    '<_c:(post|comment)>/<id:\d+>/<_a:(create|update|delete)>' => '<_c>/<_a>',
    '<_c:(post|comment)>/<id:\d+>' => '<_c>/read',
    '<_c:(post|comment)>s' => '<_c>/list',
)
~~~

Hier gibt es zwei benannte Parameter im Route-Teil der Regel:
`_c` und `_a`. Ersterer gilt, wenn die Controller-ID entweder `post` oder
`comment`ist, während der zweite auf die Action-IDs `create`, `update` oder
`delete` passt. Sie können die Parameter anders benennen, solange sie nicht in
Konflikt mit anderen GET-Parametern in der URL geraten.

Verwendet man diese Regeln, wird die URL `/index.php/post/123/create` in
die Route `post/create` mit den GET-Parametern `id=123` übersetzt. Und bei
gegebener Route `comment/list` mit dem GET-Parameter `page=2` wird die
URL `/index.php/comments?page=2` erzeugt.


Parametrisierte Hostnamen
-------------------------

Auch der Hostname kann in URL-Regeln verwendet werden.
Teile des Hostnamens können extrahiert und in GET-Parameter überführt werden.
Die URL `http://admin.example.com/en/profile` kann zum Beispiel in die
GET-Parameter `user=admin` und `lang=en` ausgewertet werden. Regeln mit
Hostnamen können andererseits genauso zum Erzeugen von URLs mit parametrisierten Hostnamen
verwendet werden.

Um parametrisierte Hostnamen zu verwenden, definieren Sie einfach URL-Regeln
mit Host-Informationen. Zum Beispiel:

~~~
[php]
array(
    'http://<user:\w+>.example.com/<lang:\w+>/profile' => 'user/profile',
)
~~~

Dieses Beispiel legt fest, dass der erste Teil des Hostnamen als `user`- und
der erste Teil des Pfades als `lang`-Parameter verwendet werden soll. Die
Regel verweist auf die `user/profile`-Route.

Beachten Sie, dass [CUrlManager::showScriptName] keine Wirkung hat, wenn eine
URL über eine Regel mit parametrisierten Hostnamen erzeugt wird.

Falls die Anwendung in einem Unterverzeichnis Ihres WWW-Stammverzeichnisses
abgelegt wurde, sollte dessen Name nicht mit in der entsprechenden Regel
auftauchen. Liegt die Anwendung z.B. unter
`http://www.example.com/sandbox/blog`, sollte immer noch die selbe Regel wie
oben ohne den Unterordner `sandbox/blog` verwendet werden.


Verbergen von `index.php`
-------------------------

Möchte man die URL noch mehr bereinigen, kann man auch den Namen des
Startscripts `index.php` verbergen. Dazu muss sowohl der Webserver als auch
die [urlManager|CWebApplication::urlManager]-Komponente konfiguriert werden.

Zunächst muss der Webserver so konfiguriert werden, dass er auch URLs ohne
Startscript an dieses weiterleitet  Im Falle
des [Apache HTTP-Servers](http://httpd.apache.org/) erreichen man das, indem
man die Rewrite-Engine (sinngem.: Umschreibemaschine) einschaltet und einige
Rewrite-Rules (sinngem.: Umschreiberegeln) definieren. Hierzu kann man eine
Datei `/wwwroot/blog/.htaccess` mit folgendem Inhalt anlegen. Beachten Sie,
dass der selbe Inhalt auch direkt in der Apache-Konfiguration in einem
`Directory`-Element für `/wwwroot/blog` abgelegt werden kann.

~~~
RewriteEngine on

# Verwende Verzeichnis oder Datei, wenn sie vorhanden sind
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Leite andernfalls auf index.php um
RewriteRule . index.php
~~~

Nun setzt man noch die Eigenschaft [showScriptName|CUrlManager::showScriptName]
in der [urlManager|CWebApplication::urlManager]-Komponente auf `false`.

Beim Aufruf von `$this->createUrl('post/read', array('id'=>100))` erhält man
jetzt die URL `/post/100`. Und noch wichtiger: Diese URL wird auch korrekt von
unserer Anwendung erkannt.


URL-Endung vortäuschen
----------------------

Indem man Endungen an eine URL anhängt, kann man zum Beispiel die URL `/post/100` zu
`/post/100.html` machen. Das sieht dann noch mehr wie eine URL zu
einer statischen Webseite aus. Setzen Sie dazu einfach die gewünschte Endung
über die Eigenschaft [urlSuffix|CUrlManager::urlSuffix] der
[urlManager|CWebApplication::urlManager]-Komponente.

Eigene URL-Rule Klassen
-----------------------

> Note|Hinweis: Diese Klassen werden seit Version 1.1.8 unterstützt.

Normalerweise werden alle URL-Regeln, die man in [CUrlManager] definiert durch
ein [CUrlRule]-Objekt dargestellt. Es kümmert sich darum, einen Request
aufzulösen und neue URLs zu erstellen - jeweils basierend auf der angegebenen
Regel. [CUrlRule] ist zwar bereits flexibel genug, um fast alle URL-Formate
umzusetzen. Trotzdem kann es vorkommen, dass man darüberhinausgehende
spezielle Features benötigt.

Nehmen wir als Beispiel die Webseite eines Autohändlers. Das URL-Format dort 
solle dem Muster `/Hersteller/Modell` entsprechen, wobei `Hersteller` und
`Modell` Werten aus der Datenbank entsprechen müssen. [CUrlRule] hilft hier
nicht weiter, da es nur statische reguläre Ausdrücke ohne Bezug zur Datenbank
kennt.

Man kann daher eine eigene Klasse als URL-Regel schreiben, die von
[CBaseUrlRule] abgeleitet wird. Diese Klasse kann man dann in einer oder
mehreren URL-Regeln verwenden. In obigem Fall würde man die Regeln wie folgt
definieren:


~~~
[php]
array(
	// gewöhnliche Regel, die '/' auf 'site/index' verweist
	'' => 'site/index',

	// gewöhnliche Regel, die '/login' auf 'site/login' verweist
	'<action:(login|logout|about)>' => 'site/<action>',

	// eigene Regel um '/Hersteller/Modell' zu bearbeiten
	array(
	    'class' => 'application.components.AutoUrlRule',
	    'connectionID' => 'db',
	),

	// gewöhnliche Regel für 'person/update' usw.
	'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
),
~~~

Die hier angegebene Klasse `AutoUrlRule` kann zum Beispiel so aussehen:

~~~
[php]
class CarUrlRule extends CBaseUrlRule
{
	public $connectionID = 'db';

	public function createUrl($manager,$route,$params,$ampersand)
	{
		if ($route==='auto/index')
		{
			if (isset($params['hersteller'], $params['modell']))
				return $params['hersteller'] . '/' . $params['modell'];
			else if (isset($params['hersteller']))
				return $params['hersteller'];
		}
		return false;  // URL passt nicht, Regel nicht betroffen
	}

	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
	{
		if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches))
		{
			// Überprüfe $matches[1] und $matches[3], ob sie einem
			// Hersteller und einem Modell in der Datenbank entsprechen.
			// Falls ja, setze $_GET['hersteller'] und $_GET['modell']
			// und gib 'car/index' als Returnwert zurück
		}
		return false;  // URL passt nicht, Regel nicht betroffen
	}
}
~~~

Die angepasste URL-Klasse muss zwei abstrakte Methoden implementieren, die
in [CBaseUrlRule] vorgegeben wurden:

* [createUrl()|CBaseUrlRule::createUrl()]
* [parseUrl()|CBaseUrlRule::parseUrl()]

Neben diesem typischen Anwendungsbeispiel eignen sich angepasste URL-Regel Klassen
noch für viele andere Zwecke. Eine solche Klasse könnte zum Beispiel den
Erstell- und Auswertungsprozess von URLs mitloggen. Das könnte während der
Entwicklungsphase sehr nützlich sein. Oder man könnte eine spezielle
404-Fehlerseite anzeigen, falls alle anderen URL-Regeln nicht zutreffen. Diese
Regel müsste dann als letzte Regel definiert werden.

<div class="revision">$Id: topics.url.txt 3329 2011-06-28 08:31:35Z mdomba $</div>
