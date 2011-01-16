Unittests
=========

Da das Yii-Testframework auf [PHPUnit](http://www.phpunit.de/) basiert,
empfehlen wir Ihnen, sich zunächst die [Dokumenation von
PHPUnit](http://www.phpunit.de/manual/current/en/index.html) durchzulesen, um die
Grundsätze beim Schreiben eines Tests zu verstehen. Zusammengefasst gelten
diese Grundprinzipien für das Schreiben von Unittests in Yii:


 * Ein Unittest wird als Klasse `XyzTest` geschrieben, die [CTestCase] oder [CDbTestCase] erweitert, 
wobei `Xyz` für die zu testende Klasse steht. Um z.B. die Klasse `Post` zu
testen, würden wir den Unittest per Konvention `PostTest` nennen. Die
Basisklasse [CTestCase] dient für allgemeine Unittests, während [CDbTestCase]
zum Testen von [ActiveRecord](/doc/guide/database.ar)-Modelklassen geeignet
ist. Da beide von `PHPUnit_Framework_TestCase` abgeleitet sind, können wir
alle von dieser Klasse geerbten Methoden verwenden.

 * Die Unittestklasse wird per Konvention in einer PHP-Datei mit den Namen `XyzTest.php`
im Verzeichnis `protected/tests/unit` abgelegt.

 * Die Testklasse besteht im Wesentlichen aus Methoden die `testAbc` heißen,
wobei `Abc` oft für den Namen der zu testenden Klassenmethode steht.

 * Eine Testmethode enthält für gewöhnlich eine Reihe von Assert-Statements
(sinngem.: feststellen, versichern), z.B. `assertTrue` oder `assertEquals`,
die als Checkpunkte beim Prüfen der Zielklasse dienen.


Im folgenden beschreiben wir hauptsächlich, wie man Unittests für
[ActiveRecord](/doc/guide/database.ar)-Modelklassen schreibt. Wir leiten
unsere Testklassen von [CDbTestCase] ab, da sie die im letzten Abschnitt
vorgestellten Fixtures unterstützt.

Nehmen wir an, wir wollen die Modelklasse `Comment` aus dem
[Blog-Demo](http://www.yiiframework.com/demos/blog/) testen. Wir erstellen
also zunächst eine Klasse `CommentTest` und speichern diese unter
`protected/tests/unit/CommentTest.php`:

~~~
[php]
class CommentTest extends CDbTestCase
{
	public $fixtures=array(
		'posts'=>'Post',
		'comments'=>'Comment',
	);

	......
}
~~~

In dieser Klasse geben wir in der Variable `fixtures` die zu verwendenden
Fixtures als Array an. Das Array stellt eine Zuordnung von Fixturenamen auf
Modelklassen- oder Tabellennamen dar (z.B. von Fixturename `post` zur
Modelklasse `Post`). Beachten Sie, dass Tabellennamen ein Doppelpunkt
vorangestellt werden muss, um sie vom Namen der Modelklasse unterscheiden zu 
können (z.B. `:Post`). Wenn wir Modelklassennamen verwenden werden die
entsprechenden Tabellen als Fixturetabellen interpretiert. Wie bereits
erwähnt, werden Fixturetabellen jedesmal, wenn eine Testmethode ausgeführt
wird, auf einen fest definierten Zustand zurückgesetzt.

Fixturenamen erlauben den bequemen Zugriff auf Fixturedaten innerhalb von
Testmethoden. Hier ein typisches Anwendungsbeispiel:

~~~
[php]
// Alle Zeilen aus der Fixturetabelle 'Comment' zurückliefern
$comments = $this->comments;
// Zeile mit dem Alias 'sample1' aus der Fixturetabelle 'Post' zurückliefern
$post = $this->posts['sample1'];
// Die AR-Instanz, die der Fixturezeile 'sample1' entspricht zurückliefern
$post = $this->post('sample1');
~~~

> Note|Hinweis: Wenn ein Fixture über seinen Tabellennamen festgelegt wurde
(z.B. `'posts'=>':Post'`), kann die dritte Variante in obigem Beispiel nicht
verwendet werden, da wir keine Information darüber haben, mit welcher Modelklasse 
die Tabelle verbunden ist.

Als nächstes schreiben wir die Methode `testApprove` um die `approve`-Methode
in der Modelklasse `Comment` zu testen. Der Code ist sehr unkompliziert:
Zunächst fügen wir einen Kommentar mit dem Status pending ein. Dann
verifizieren wir, dass der Kommentar im Status pending ist, indem wir ihn aus
der Datenbank zurücklesen. Schließlich rufen wir die Methode `approve` auf und
prüfen, ob sich der Status wie erwartet verändert hat.

~~~
[php]
public function testApprove()
{
	// Kommentar mit Status 'pending' einfügen
	$comment=new Comment;
	$comment->setAttributes(array(
		'content'=>'comment 1',
		'status'=>Comment::STATUS_PENDING,
		'createTime'=>time(),
		'author'=>'me',
		'email'=>'me@example.com',
		'postId'=>$this->posts['sample1']['id'],
	),false);
	$this->assertTrue($comment->save(false));

	// Status 'pending' des Kommentars verifizieren
	$comment=Comment::model()->findByPk($comment->id);
	$this->assertTrue($comment instanceof Comment);
	$this->assertEquals(Comment::STATUS_PENDING,$comment->status);

	// approve() aufrufen und prüfen ob der Kommentar den Status 'approved' hat
	$comment->approve();
	$this->assertEquals(Comment::STATUS_APPROVED,$comment->status);
	$comment=Comment::model()->findByPk($comment->id);
	$this->assertEquals(Comment::STATUS_APPROVED,$comment->status);
}
~~~


<div class="revision">$Id: test.unit.txt 2841 2011-01-12 21:04:12Z alexander.makarow $</div>
