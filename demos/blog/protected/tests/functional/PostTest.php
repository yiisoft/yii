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

	public function testIndex()
	{
	    $this->open('');
	    // verify header title exists
	    $this->assertTextPresent('Yii Blog Demo');
	    // verify the sample post title exists
	    $this->assertTextPresent($this->posts['sample1']['title']);
	}

	public function testView()
	{
		$this->open('post/1/xyz');
	    // verify the sample post title exists
	    $this->assertTextPresent($this->posts['sample1']['title']);
	    // verify comment form exists
	    $this->assertTextPresent('Leave a Comment');
	}
}