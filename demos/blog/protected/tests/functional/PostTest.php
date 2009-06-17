<?php

class PostTest extends WebTestCase
{
	/**
	 * We use the 'Post' only for this test.
	 * @see CWebTestCase::fixtures
	 */
	public $fixtures=array(
		'posts'=>'Post',
	);

	public function testList()
	{
	    $this->open('');
	    // verify header title exists
	    $this->assertTextPresent('My Yii Blog');
	    // verify the sample post title exists
	    $this->assertTextPresent($this->posts['sample1']['title']);
	}

	public function testShow()
	{
		$this->open('post/1');
	    // verify the sample post title exists
	    $this->assertTextPresent($this->posts['sample1']['title']);
	    // verify comment form exists
	    $this->assertTextPresent('Leave a Comment');
	}

	public function testLogin()
	{
		$this->ensureLogout();
	    $this->open('');
	    // verify logout link is absent
		$this->assertTextNotPresent('Logout');

		// verify login failure
		$this->type('name=LoginForm[username]','demo');
		$this->clickAndWait("//input[@value='Login']");
		$this->assertTextPresent('Password cannot be blank.');

		// verify login successful
		$this->type('name=LoginForm[password]','demo');
		$this->clickAndWait("//input[@value='Login']");
		$this->assertTextPresent('Logout');

		// verify logout
		$this->clickAndWait("link=Logout");
		$this->assertTextNotPresent('Logout');
	}

	public function testCreate()
	{
		$this->ensureLogout();
	    $this->open('post/create');
	    // verify login required
	    $this->assertTextPresent('Login Required');

		// verify Create New Post link works
	    $this->open('');
		$this->assertTextNotPresent('Create New Post');
		$this->login();
		$this->assertTextPresent('Create New Post');
		$this->clickAndWait('link=Create New Post');

		// verify validation errors
		$this->assertElementPresent("name=Post[title]");
		$this->clickAndWait("//input[@value='Create']");
		$this->assertTextPresent('Title cannot be blank.');
		$this->assertTextPresent('Content cannot be blank.');
		$this->type('name=Post[title]','test post 2');
		$this->clickAndWait("//input[@value='Create']");
		$this->assertTextNotPresent('Title cannot be blank.');
		$this->assertTextPresent('Content cannot be blank.');

		// verify Create button works
		$this->type('name=Post[content]','test post 2 content');
		$this->clickAndWait("//input[@value='Create']");
		$this->assertTextNotPresent('Title cannot be blank.');
		$this->assertTextNotPresent('Content cannot be blank.');
		$this->assertTextPresent('test post 2');
		$this->assertTextPresent('Draft');

		// verify preview
		$this->clickAndWait('link=Create New Post');
		$this->assertSelected("name=Post[status]","Draft");
		$this->type('name=Post[title]','test post 3');
		$this->type('name=Post[content]','test post <script>abc</script> content');
		$this->select("name=Post[status]","value=1");
		$this->clickAndWait("//input[@value='Preview']");
		$this->assertSelected("name=Post[status]","Published");
		$this->assertTextPresent('test post content');
		$this->clickAndWait("//input[@value='Create']");
		$this->assertTextPresent('Published');

		$this->logout();

		// verify post 2 is not published while post 3 is
		$this->assertTextNotPresent('test post 2');
		$this->assertTextPresent('test post 3');
	}

	public function testUpdate()
	{
		$this->ensureLogout();
	    $this->open('post/update/1');
	    // verify login required
	    $this->assertTextPresent('Login Required');

		// verify Update link works
		$this->open('');
		$this->login();
	    $this->open('post/update/1');
		$this->assertElementPresent("name=Post[title]");
		$this->type('name=Post[title]','');
		$this->clickAndWait("//input[@value='Save']");
		$this->assertTextPresent('Title cannot be blank.');
		$this->assertTextNotPresent('Content cannot be blank.');

		$newTitle='test post 1a';
		$this->type('name=Post[title]',$newTitle);
		$this->clickAndWait("//input[@value='Save']");
		$this->assertTextPresent($newTitle);

		// verify changing status works
	    $this->open('post/update/1');
		$this->select("name=Post[status]","value=2");
		$this->clickAndWait("//input[@value='Save']");
		$this->assertTextPresent($newTitle);
		$this->assertTextPresent('Archived');

		// verify archived posts are not visible
		$this->logout();
		$this->assertTextNotPresent($newTitle);
	}

	public function testDelete()
	{
		$this->ensureLogout();
	    $this->open('post/delete/1');
	    // verify login required
	    $this->assertTextPresent('Login Required');

		// verify Update link works
		$this->open('post/1');
		$this->login();
		$this->assertTextPresent($this->posts['sample1']['title']);
		$this->chooseCancelOnNextConfirmation();
		$this->click('link=Delete');
		$this->assertConfirmationPresent("Are you sure to delete this post?");
		$this->getConfirmation(); // close the dialog

		$this->assertTextPresent($this->posts['sample1']['title']);
		$this->chooseOkOnNextConfirmation();
		$this->click('link=Delete');
		$this->assertConfirmationPresent("Are you sure to delete this post?");
		$this->getConfirmation(); // close the dialog
		$this->assertTextNotPresent($this->posts['sample1']['title']);
	}
}