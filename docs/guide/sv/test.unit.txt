Enhetstestning
==============

Eftersom Yii:s testningsramverk använder sig av [PHPUnit](http://www.phpunit.de/),
rekommenderas [PHPUnit-dokumentationen](http://www.phpunit.de/manual/current/en/index.html) för att ge 
en grundläggande förståelse för hur man skriver ett enhetstest. Vi sammanfattar följande 
grundläggande principer för hur man skriver ett enhetstest i Yii:

 * Ett enhetstest skrivs i form av en klass `XyzTest` som ärver från [CTestCase] 
 eller [CDbTestCase] och där `Xyz` står för klassen som skall testas.  För att 
 till exempel testa klassen `Post`behöver vi, enligt konvention, namnge det 
 motsvarande enhetstestet som `PostTest`. Basklassen [CTestCase] är tänkt att 
 användas för generella enhetstester, medan [CDbTestCase] passar för testning av
 [active record](/doc/guide/database.ar) modellklasser. Eftersom `PHPUnit_Framework_TestCase` är 
 förälderklass till båda klasserna, kan vi använda alla metoder som ärvs från densamma.

 * Enhetstestklassen sparas i en PHP-fil namngiven som `XyzTest.php`. Enligt 
 konvention sparas enhetstestfilen under katalogen `protected/tests/unit`.

 * Testklassen består i huvudsak av en uppsättning testmetoder namngivna som 
 `testAbc`, där `Abc` ofta är namnet på klassen som skall testas.

 * En testmetod innehåller vanligen en följd av assertion-satser (t.ex. 
 `assertTrue`, `assertEquals`) som tjänstgör som kontrollstationer vid validering 
 av målklassens beteende.


I det följande beskrivs i huvudsak hur man skriver enhetstester för [Active Record](/doc/guide/database.ar) 
modellklasser. Vi kommer att ärva våra testklasser från [CDbTestCase] eftersom 
denna erbjuder stöd för databasfixturer (som introducerades i föregående avsnitt).

Antag att vi vill testa modellklassen `Comment` i applikationen [blog demo](http://www.yiiframework.com/demos/blog/). 
Vi börjar med att skapa en klass och ger den namnet `CommentTest` samt sparar den som `protected/tests/unit/CommentTest.php`:

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

I denna klass specificerar vi medlemsvariabeln `fixtures` till att vara en array 
som specificerar vilka fixturer som kommer användas av detta test. Arrayen representerar 
en mappning mellan fixturnamn och modellklassnamn alternativt fixturtabellnamn (t.ex. 
från fixturnamnet `posts` till modellklassen `Post`). Märk att när mappningen sker till 
fixturtabellnamn sätter vi in ett kolon som prefix före tabellnamnet (t.ex. `:Post`) 
för att särskilja det från modellklassnamn. Vid användning av modelklassnamn kommer 
motsvarande tabeller att betraktas som fixturtabeller. Som tidigare beskrivits återställs 
fixturtabeller till något känt tillstånd varje gång en testmetod exekveras.

Fixturnamn ger tillgång till fixturdata från testmetoder på ett praktiskt sätt. Följande kod 
ger typiska användningsexempel:

~~~
[php]
// return all rows in the 'Comment' fixture table
$comments = $this->comments;
// return the row whose alias is 'sample1' in the `Post` fixture table
$post = $this->posts['sample1'];
// return the AR instance representing the 'sample1' fixture data row
$post = $this->posts('sample1');
~~~

> Note|Märk: Om en fixtur har deklarerats med sitt tabellnamn (t.ex. `'posts'=>':Post'`), 
är det tredje exemplet ovan inte giltigt eftersom vi inte har någon information om  
vilken modellklass tabellen är associerad till.

Nu skriver vi en metod `testApprove` för att testa metoden `approve` i modellklassen 
`Comment`. Koden är okomplicerad: först sätter vi in en kommentar som åsätts status 
Pending; därefter verifierar vi, genom att hämta tillbaka kommentaren från databasen, att 
den erhållit status Pending; slutligen anropar vi metoden `approve` och verifierar att 
status ändras som förväntat.

~~~
[php]
public function testApprove()
{
	// insert a comment in pending status
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

	// verify the comment is in pending status
	$comment=Comment::model()->findByPk($comment->id);
	$this->assertTrue($comment instanceof Comment);
	$this->assertEquals(Comment::STATUS_PENDING,$comment->status);

	// call approve() and verify the comment is in approved status
	$comment->approve();
	$this->assertEquals(Comment::STATUS_APPROVED,$comment->status);
	$comment=Comment::model()->findByPk($comment->id);
	$this->assertEquals(Comment::STATUS_APPROVED,$comment->status);
}
~~~


<div class="revision">$Id: test.unit.txt 2841 2011-01-12 21:04:12Z alexander.makarow $</div>