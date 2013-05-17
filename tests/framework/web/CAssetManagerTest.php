<?php

Yii::import('system.web.CAssetManager');

class CAssetManagerTest extends CTestCase
{
	public function testBasePath()
	{
		$am2=new CAssetManager;
		$app=new TestApplication;
		$app->reset();
		$am2->init($app);
		$this->assertEquals($am2->basePath,$app->basePath.DIRECTORY_SEPARATOR.'assets');
	}

	public function testBaseUrl()
	{
		$app=new TestApplication;
		$app->request->baseUrl='/test';
		$am=new CAssetManager;
		$am->init($app);
		$this->assertEquals($am->baseUrl,'/test/assets');
	}

	public function testPublishFile()
	{
		$app=new TestApplication;
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
