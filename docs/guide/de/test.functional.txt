Funktionstests
==============

Wir empfehlen Ihnen, sich zunächst die [Selenium
Dokumenation](http://seleniumhq.org/docs/) sowie die [PHPUnit
Dokumenation](http://www.phpunit.de/wiki/Documentation) durchzulesen. 
Zusammengefasst gelten diese Grundprinzipien für das Schreiben von
Funktionstests in Yii:

 * Genauso wie bei Unittests wird ein Funktionstest als Klasse `XyzTest`
geschrieben, die diesmal von [CWebTestCase] abgeleitet wird, wobei `Xyz` für
die zu testende Klasse steht. Da [CWebTestCase] die Klasse
`PHPUnit_Extensions_SeleniumTestCase` erweitert, können wir alle von dieser
Klasse geerbten Methoden verwenden.

 * Die Funktionstestklasse wird per Konvention in einer PHP-Date namens `XyzTest.php`
im Verzeichnis `protected/tests/functional` gespeichert.

 * Die Testklasse besteht im Wesentlichen aus einer Reihe von Methoden namens
`testAbc`, wobei `Abc` oft für den Namen eines zu testenden Features steht. Um
zum Beispiel das User-Login-Feature zu testen, könnte wir eine Testmethode
`testLogin` anlegen.

 * Eine Testmethode enthält für gewöhnlich eine Reihe von Anweisungen, die
Befehle an Selenium RC senden, um mit der zu testenden Webanwendung zu
interagieren. Sie enthält auch Assert-Statements um zu prüfen, dass die
Anwendung wie erwartet reagiert.

Bevor wir näher darauf eingehen, wie man einen Funktionstest schreibt, sehen
wir uns zunächst die Datei `WebTestCase.php` näher an, die von `yiic webapp`
erstellt wurde. In dieser Datei wird `WebTestCase` definiert, die man als
Basisklasse für alle Funktionstest verwenden kann.


~~~
[php]
define('TEST_BASE_URL','http://localhost/yii/demos/blog/index-test.php/');

class WebTestCase extends CWebTestCase
{
	/**
	 * Wird vor jeder Testmethode ausgeführt.
	 * Setzt die Basis-URL für die Testanwendung.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
	}

	......
}
~~~

Die Klasse `WebTestCase` setzt die Basis-URL der zu testenden Seiten. 
Später können wir daher in Testmethoden relative URLs für diese Seiten
verwenden.

Wir sollten auch beachten, dass wir für die Basis-URL unserer Tests
`index-test.php` statt `index.php` verwenden. Der einzige Unterschied zwischen
den beiden Dateien besteht darin, dass erstere `test.php` als
Konfigurationsdatei verwendet, letztere `main.php`.

Sehen wir uns nun an, wie wir das Feature zur Anzeige eines Beitrags im
[Blog-Demo](http://www.yiiframework.com/demos/blog) testen können. Zunächst
schreiben wir die Testklasse wie folgt (Beachten Sie, dass wir sie von WebTestCase
ableiten):

~~~
[php]
class PostTest extends WebTestCase
{
	public $fixtures=array(
		'posts'=>'Post',
	);

	public function testShow()
	{
		$this->open('post/1');
		// Prüfe, ob der Beispielbeitrag existiert
	    $this->assertTextPresent($this->posts['sample1']['title']);
		// Prüfe, ob das Kommentarformular vorhanden ist
	    $this->assertTextPresent('Leave a Comment');
	}

	......
}
~~~

Genau wie bei einem Unittest geben wir die für den Test zu verwendenden Fixtures an.
In diesem Fall soll das Fixture für `Post` verwendet werden. In der Methode
`testShow` weisen wir Selenium RC zuerst and, die URL `post/1` zu öffnen. Man
beachte, dass es sich hier um eine relative URL handelt. Die vollständige
URL wird mit der in der Basisklasse gesetzten Basis-URL gebildet (z.B.
`http://localhost/yii/demos/blog/index-test.php/post/1`). Wir prüfen dann, ob
der Titel des Beitrags `sample1` in der aktuellen Seite gefunden wurde. Und
wir stellen auch sicher, dass die Seite den Text `Leave a Comment` enthält.

> Tip|Tipp: Um einen Funktionstest durchzuführen muss zunächst der
> Selenium-RC-Server gestartet werden. Dazu wird in dessen
> Installationsverzeichnis folgender Befehl ausgeführt: 
> `java -jar selenium-server.jar`. 

<div class="revision">$Id: test.functional.txt 1662 2010-01-04 19:15:10Z qiang.xue $</div>
