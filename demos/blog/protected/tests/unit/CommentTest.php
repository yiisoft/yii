<?php

class CommentTest extends CDbTestCase
{
	/**
	 * We use both 'Post' and 'Comment' fixtures.
	 * @see CWebTestCase::fixtures
	 */
	public $fixtures=array(
		'posts'=>'Post',
		'comments'=>'Comment',
	);

	public function testFindRecentComments()
	{
		$this->assertEquals(array(), Comment::model()->findRecentComments());

		$comment=new Comment;
		$comment->setAttributes(array(
			'content'=>'comment 1',
			'status'=>Comment::STATUS_APPROVED,
			'create_time'=>time(),
			'author'=>'me',
			'email'=>'me@example.com',
			'post_id'=>$this->posts['sample1']['id'],
		),false);
		$this->assertTrue($comment->save(false));
		$this->assertEquals(1,$comment->id);

		$comments=Comment::model()->findRecentComments();
		$this->assertEquals(1,count($comments));
		$this->assertEquals($comment->attributes, $comments[0]->attributes);
	}

	public function testApprove()
	{
		$comment=new Comment;
		$comment->setAttributes(array(
			'content'=>'comment 1',
			'status'=>Comment::STATUS_PENDING,
			'create_time'=>time(),
			'author'=>'me',
			'email'=>'me@example.com',
			'post_id'=>$this->posts['sample1']['id'],
		),false);
		$this->assertTrue($comment->save(false));

		$comment=Comment::model()->findByPk($comment->id);
		$this->assertTrue($comment instanceof Comment);
		$this->assertEquals(Comment::STATUS_PENDING,$comment->status);

		$comment->approve();
		$this->assertEquals(Comment::STATUS_APPROVED,$comment->status);
		$comment=Comment::model()->findByPk($comment->id);
		$this->assertEquals(Comment::STATUS_APPROVED,$comment->status);
	}
}