<?php

require_once(dirname(__FILE__).'/AuthManagerTestBase.php');

class CPhpAuthManagerTest extends AuthManagerTestBase
{
	public function setUp()
	{
		$authFile=Yii::app()->getRuntimePath().'/CPhpAuthManagerTest_auth.php';
		@unlink($authFile);
		$this->auth=new CPhpAuthManager;
		$this->auth->authFile=$authFile;
		$this->auth->init();
		$this->prepareData();
	}

	public function tearDown()
	{
		@unlink($this->auth->authFile);
	}

	public function testSaveLoad()
	{
		$this->auth->save();
		$this->auth->clearAll();
		$this->auth->load();
		$this->testCheckAccess();
	}
}
