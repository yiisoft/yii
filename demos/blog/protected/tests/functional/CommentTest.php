<?php

class CommentTest extends WebTestCase
{
	public $fixtures=array(
		'comments'=>'Comment',
	);

	public function testShow()
	{
		$this->open('?r=comment/view&id=1');
	}

	public function testCreate()
	{
		$this->open('?r=comment/create');
	}

	public function testUpdate()
	{
		$this->open('?r=comment/update&id=1');
	}

	public function testDelete()
	{
		$this->open('?r=comment/view&id=1');
	}

	public function testList()
	{
		$this->open('?r=comment/index');
	}

	public function testAdmin()
	{
		$this->open('?r=comment/admin');
	}
}
