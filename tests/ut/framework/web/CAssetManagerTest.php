<?php

Yii::import('system.web.CAssetManager');

class CAssetManagerTest extends CTestCase
{
	public function testBasePath()
	{
		$am=new CAssetManager;
		$this->assertTrue($am->basePath===null);
		$am->basePath=dirname(__FILE__);
		$this->assertTrue($am->basePath===dirname(__FILE__));

		$am2=new CAssetManager;
		$app=new TestWebApplication(array('basePath'=>YII_UT_PATH));
		$app->reset();
		$am2->init($app);
		$this->assertEquals($am2->basePath,$app->basePath.DIRECTORY_SEPARATOR.'assets');
	}

	public function testBaseUrl()
	{
		$_SERVER['SCRIPT_NAME']='http://localhost/test/index.php';
		$app=new TestWebApplication(array('basePath'=>YII_UT_PATH));
		$am=new CAssetManager;
		$this->assertTrue($am->baseUrl===null);
		$am->init($app);
		$this->assertEquals($am->baseUrl,'http://localhost/test/assets');
	}

	public function testPublishFile()
	{
		$_SERVER['SCRIPT_NAME']='http://localhost/test/index.php';
		$app=new TestWebApplication(array('basePath'=>YII_UT_PATH));
		$app->reset();
		$am=new CAssetManager;
		$am->init($app);
		$path1=$am->getPublishedPath(__FILE__);
		clearstatcache();
		$this->assertFalse(is_file($path1));
		$url=$am->publish(__FILE__);
		$path2=$am->getPublishedPath(__FILE__);
		$this->assertEquals($path1,$path2);
		clearstatcache();
		$this->assertTrue(is_file($path1));
		$this->assertEquals(basename($path1),basename(__FILE__));
		$this->assertEquals($url,$am->baseUrl.'/'.basename(dirname($path2)).'/'.basename($path2));
	}
}
