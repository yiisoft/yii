<?php

class PostTest extends CDbTestCase
{
	/**
	 * We use both 'Post' and 'Comment' fixtures.
	 * @see CWebTestCase::fixtures
	 */
	public $fixtures=array(
		'posts'=>'Post',
		'comments'=>'Comment',
	);
}