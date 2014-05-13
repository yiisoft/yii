<?php

/**
 * Test for CWebUser
 *
 * tests are running in separate process to allow session management to work properly
 */
class CWebUserTest extends CTestCase
{
	public function setUp()
	{
		Yii::app()->setComponent('authManager', new CPhpAuthManager());

		/** @var CPhpAuthManager $auth */
		$auth = Yii::app()->authManager;
		$auth->createOperation('createPost');
		$auth->createOperation('deletePost');
		$auth->assign('createPost', 'admin');
	}

	public function tearDown()
	{
		Yii::app()->session->destroy();
		Yii::app()->setComponent('authManager', null);
	}

	public function booleanProvider()
	{
		return array(array(true), array(false));
	}

	/**
	 * @runInSeparateProcess
	 * @outputBuffering enabled
	 * @dataProvider booleanProvider
	 */
	public function testLoginLogout($destroySession)
	{
		$identity = new CUserIdentity('testUser', 'testPassword');

		$user = new CWebUser();
		$user->init();

		// be guest before login
		$this->assertTrue($user->isGuest);
		// do a login
		$this->assertTrue($user->login($identity));
		// don't be guest after login
		$this->assertFalse($user->isGuest);
		$this->assertEquals('testUser', $user->getId());
		$this->assertEquals('testUser', $user->getName());

		$user->logout($destroySession);

		// be guest after logout
		$this->assertNull($user->getId());
		$this->assertEquals($user->guestName, $user->getName());
	}

	/**
	 * @runInSeparateProcess
	 * @outputBuffering enabled
	 */
	public function testCheckAccess()
	{
		$identity = new CUserIdentity('admin', 'admin');
		Yii::app()->user->login($identity);

		$this->assertTrue(Yii::app()->user->checkAccess('createPost'));
		$this->assertFalse(Yii::app()->user->checkAccess('deletePost'));

		Yii::app()->user->logout();

		$this->assertFalse(Yii::app()->user->checkAccess('createPost'));
		$this->assertFalse(Yii::app()->user->checkAccess('deletePost'));
	}
}
