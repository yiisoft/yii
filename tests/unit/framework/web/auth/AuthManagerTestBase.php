<?php

abstract class AuthManagerTestBase extends CTestCase
{
	protected $auth;

	public function testcreateAuthItem()
	{
		$type=CAuthItem::TYPE_TASK;
		$name='editUser';
		$description='edit a user';
		$bizRule='checkUserIdentity()';
		$data=array(1,2,3);
		$item=$this->auth->createAuthItem($name,$type,$description,$bizRule,$data);
		$this->assertTrue($item instanceof CAuthItem);
		$this->assertEquals($item->type,$type);
		$this->assertEquals($item->name,$name);
		$this->assertEquals($item->description,$description);
		$this->assertEquals($item->bizRule,$bizRule);
		$this->assertEquals($item->data,$data);

		// test shortcut
		$name2='createUser';
		$item2=$this->auth->createRole($name2,$description,$bizRule,$data);
		$this->assertEquals($item2->type,CAuthItem::TYPE_ROLE);

		// test adding an item with the same name
		$this->setExpectedException('CException');
		$this->auth->createAuthItem($name,$type,$description,$bizRule,$data);
	}

	public function testGetAuthItem()
	{
		$this->assertTrue($this->auth->getAuthItem('readPost') instanceof CAuthItem);
		$this->assertTrue($this->auth->getAuthItem('reader') instanceof CAuthItem);
		$this->assertNull($this->auth->getAuthItem('unknown'));
	}

	public function testRemoveAuthItem()
	{
		$this->assertTrue($this->auth->getAuthItem('updatePost') instanceof CAuthItem);
		$this->assertTrue($this->auth->removeAuthItem('updatePost'));
		$this->assertNull($this->auth->getAuthItem('updatePost'));
		$this->assertFalse($this->auth->removeAuthItem('updatePost'));
	}

	public function testChangeItemName()
	{
		$item=$this->auth->getAuthItem('readPost');
		$this->assertTrue($item instanceof CAuthItem);
		$this->assertTrue($this->auth->hasItemChild('reader','readPost'));
		$item->name='readPost2';
		$this->assertNull($this->auth->getAuthItem('readPost'));
		$this->assertEquals($this->auth->getAuthItem('readPost2'),$item);
		$this->assertFalse($this->auth->hasItemChild('reader','readPost'));
		$this->assertTrue($this->auth->hasItemChild('reader','readPost2'));
	}

	public function testAddItemChild()
	{
		$this->auth->addItemChild('createPost','updatePost');

		// test adding upper level item to lower one
		$this->setExpectedException('CException');
		$this->auth->addItemChild('readPost','reader');
	}

	public function testAddItemChild2()
	{
		// test adding inexistent items
		$this->setExpectedException('CException');
		$this->assertFalse($this->auth->addItemChild('createPost2','updatePost'));
	}

	public function testRemoveItemChild()
	{
		$this->assertTrue($this->auth->hasItemChild('reader','readPost'));
		$this->assertTrue($this->auth->removeItemChild('reader','readPost'));
		$this->assertFalse($this->auth->hasItemChild('reader','readPost'));
		$this->assertFalse($this->auth->removeItemChild('reader','readPost'));
	}

	public function testGetItemChildren()
	{
		$this->assertEquals(array(),$this->auth->getItemChildren('readPost'));
		$children=$this->auth->getItemChildren('author');
		$this->assertEquals(3,count($children));
		$this->assertTrue(reset($children) instanceof CAuthItem);
	}

	public function testAssign()
	{
		$auth=$this->auth->assign('createPost','new user','rule','data');
		$this->assertTrue($auth instanceof CAuthAssignment);
		$this->assertEquals($auth->userId,'new user');
		$this->assertEquals($auth->itemName,'createPost');
		$this->assertEquals($auth->bizRule,'rule');
		$this->assertEquals($auth->data,'data');

		$this->setExpectedException('CException');
		$this->auth->assign('createPost2','new user','rule','data');
	}

	public function testRevoke()
	{
		$this->assertTrue($this->auth->isAssigned('author','author B'));
		$auth=$this->auth->getAuthAssignment('author','author B');
		$this->assertTrue($auth instanceof CAuthAssignment);
		$this->assertTrue($this->auth->revoke('author','author B'));
		$this->assertFalse($this->auth->isAssigned('author','author B'));
		$this->assertFalse($this->auth->revoke('author','author B'));
	}

	public function testGetAuthAssignments()
	{
		$this->auth->assign('deletePost','author B');
		$auths=$this->auth->getAuthAssignments('author B');
		$this->assertEquals(2,count($auths));
		$this->assertTrue(reset($auths) instanceof CAuthAssignment);
	}

	public function testGetAuthItems()
	{
		$this->assertEquals(count($this->auth->getRoles()),4);
		$this->assertEquals(count($this->auth->getOperations()),4);
		$this->assertEquals(count($this->auth->getTasks()),1);
		$this->assertEquals(count($this->auth->getAuthItems()),9);

		$this->assertEquals(count($this->auth->getAuthItems(null,'author B')),1);
		$this->assertEquals(count($this->auth->getAuthItems(null,'author C')),0);
		$this->assertEquals(count($this->auth->getAuthItems(CAuthItem::TYPE_ROLE,'author B')),1);
		$this->assertEquals(count($this->auth->getAuthItems(CAuthItem::TYPE_OPERATION,'author B')),0);
	}

	public function testClearAll()
	{
		$this->auth->clearAll();
		$this->assertEquals(count($this->auth->getRoles()),0);
		$this->assertEquals(count($this->auth->getOperations()),0);
		$this->assertEquals(count($this->auth->getTasks()),0);
		$this->assertEquals(count($this->auth->getAuthItems()),0);
		$this->assertEquals(count($this->auth->getAuthAssignments('author B')),0);
	}

	public function testClearAuthAssignments()
	{
		$this->auth->clearAuthAssignments();
		$this->assertEquals(count($this->auth->getAuthAssignments('author B')),0);
	}

	public function testDetectLoop()
	{
		$this->setExpectedException('CException');
		$this->auth->addItemChild('readPost','readPost');
	}

	public function testExecuteBizRule()
	{
		$this->assertTrue($this->auth->executeBizRule(null,array(),null));
		$this->assertTrue($this->auth->executeBizRule('return 1==true;',array(),null));
		$this->assertTrue($this->auth->executeBizRule('return $params[0]==$params[1];',array(1,'1'),null));
		$this->assertFalse($this->auth->executeBizRule('invalid',array(),null));
	}

	public function testCheckAccess()
	{
		$results=array(
			'reader A'=>array(
				'createPost'=>false,
				'readPost'=>true,
				'updatePost'=>false,
				'updateOwnPost'=>false,
				'deletePost'=>false,
			),
			'author B'=>array(
				'createPost'=>true,
				'readPost'=>true,
				'updatePost'=>true,
				'updateOwnPost'=>true,
				'deletePost'=>false,
			),
			'editor C'=>array(
				'createPost'=>false,
				'readPost'=>true,
				'updatePost'=>true,
				'updateOwnPost'=>false,
				'deletePost'=>false,
			),
			'admin D'=>array(
				'createPost'=>true,
				'readPost'=>true,
				'updatePost'=>true,
				'updateOwnPost'=>false,
				'deletePost'=>true,
			),
		);

		$params=array('authorID'=>'author B');

		foreach(array('reader A','author B','editor C','admin D') as $user)
		{
			$params['userID']=$user;
			foreach(array('createPost','readPost','updatePost','updateOwnPost','deletePost') as $operation)
			{
				$result=$this->auth->checkAccess($operation,$user,$params);
				$this->assertEquals($results[$user][$operation],$result);
			}
		}
	}

	protected function prepareData()
	{
		$this->auth->createOperation('createPost','create a post');
		$this->auth->createOperation('readPost','read a post');
		$this->auth->createOperation('updatePost','update a post');
		$this->auth->createOperation('deletePost','delete a post');

		$task=$this->auth->createTask('updateOwnPost','update a post by author himself','return $params["authorID"]==$params["userID"];');
		$task->addChild('updatePost');

		$role=$this->auth->createRole('reader');
		$role->addChild('readPost');

		$role=$this->auth->createRole('author');
		$role->addChild('reader');
		$role->addChild('createPost');
		$role->addChild('updateOwnPost');

		$role=$this->auth->createRole('editor');
		$role->addChild('reader');
		$role->addChild('updatePost');

		$role=$this->auth->createRole('admin');
		$role->addChild('editor');
		$role->addChild('author');
		$role->addChild('deletePost');

		$this->auth->assign('reader','reader A');
		$this->auth->assign('author','author B');
		$this->auth->assign('editor','editor C');
		$this->auth->assign('admin','admin D');
	}
}
