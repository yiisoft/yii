Unit Testing
============

Because the Yii testing framework is built on top of [PHPUnit](http://www.phpunit.de/), it is recommended that you go through the [PHPUnit documentation](http://www.phpunit.de/manual/current/en/index.html) first to get the basic understanding on how to write a unit test. We summarize in the following the basic principles of writing a unit test in Yii:

 * A unit test is written in terms of a class `XyzTest` which extends from [CTestCase] or [CDbTestCase], where `Xyz` stands for the class being tested. For example, to test the `Post` class, we would name the corresponding unit test as `PostTest` by convention. The base class [CTestCase] is meant for generic unit tests, while [CDbTestCase] is suitable for testing [active record](/doc/guide/database.ar) model classes. Because `PHPUnit_Framework_TestCase` is the ancestor class for both classes, we can use all methods inherited from this class.

 * The unit test class is saved in a PHP file named as `XyzTest.php`. By convention, the unit test file may be stored under the directory `protected/tests/unit`.

 * The test class mainly contains a set of test methods named as `testAbc`, where `Abc` is often the name of the class method to be tested.

 * A test method usually contains a sequence of assertion statements (e.g. `assertTrue`, `assertEquals`) which serve as checkpoints on validating the behavior of the target class.


In the following, we mainly describe how to write unit tests for [active record](/doc/guide/database.ar) model classes. We will extend our test classes from [CDbTestCase] because it provides the database fixture support that we introduced in the previous section.

Assume we want to test the `Comment` model class in the [blog demo](http://www.yiiframework.com/demos/blog/). We start by creating a class named `CommentTest` and saving it as `protected/tests/unit/CommentTest.php`:

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

In this class, we specify the `fixtures` member variable to be an array that specifies which fixtures will be used by this test. The array represents a  mapping from fixture names to model class names or fixture table names (e.g. from fixture name `posts` to model class `Post`). Note that when mapping to fixture table names, we should prefix the table name with a colon (e.g. `:Post`) to differentiate it from model class name. And when using model class names, the corresponding tables will be considered as fixture tables. As we described earlier, fixture tables will be reset to some known state each time when a test method is executed.

Fixture names allow us to access the fixture data in test methods in a convenient way. The following code shows its typical usage:

~~~
[php]
// return all rows in the 'Comment' fixture table
$comments = $this->comments;
// return the row whose alias is 'sample1' in the `Post` fixture table
$post = $this->posts['sample1'];
// return the AR instance representing the 'sample1' fixture data row
$post = $this->posts('sample1');
~~~

> Note: If a fixture is declared using its table name (e.g. `'posts'=>':Post'`), then the third usage in the above is not valid because we have no information about which model class the table is associated with.

Next, we write the `testApprove` method to test the `approve` method in the `Comment` model class. The code is very straightforward: we first insert a comment that is pending status; we then verify this comment is in pending status by retrieving it from database; and finally we call the `approve` method and verify the status is changed as expected.

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


<div class="revision">$Id$</div>