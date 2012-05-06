<?php
Yii::import('system.caching.dependencies.CFileCacheDependency');

class CCacheDependencyTest extends CTestCase
{
	public function testReusable()
	{
		$tempFile=dirname(__FILE__).'/../../runtime/foo.txt';
		@unlink($tempFile);
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test");
		fclose($fw);
		clearstatcache();
		$dependency = new MockFileCacheDependency($tempFile);
		$dependency->reuseDependentData = true;
		$dependency->evaluateDependency();
		$this->assertEquals(1,MockFileCacheDependency::$generateDependentDataCalled);
		// change file
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test again");
		fclose($fw);
		clearstatcache();
		$this->assertFalse($dependency->getHasChanged());
		$this->assertEquals(1,MockFileCacheDependency::$generateDependentDataCalled);
		$dependency2 = new MockDirectoryCacheDependency(dirname($tempFile));
		$dependency2->reuseDependentData = true;
		// change file
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test again");
		fclose($fw);
		clearstatcache();
		$this->assertTrue($dependency2->getHasChanged());
		$this->assertEquals(1,MockDirectoryCacheDependency::$generateDependentDataCalled);
		$dependency3 = new MockDirectoryCacheDependency(dirname($tempFile));
		$dependency3->reuseDependentData = true;
		$dependency3->evaluateDependency();
		$this->assertTrue($dependency3->getHasChanged());
		$this->assertEquals(1,MockDirectoryCacheDependency::$generateDependentDataCalled);
	}
}

class MockFileCacheDependency extends CFileCacheDependency
{
	public static $generateDependentDataCalled = 0;

	public function generateDependentData()
	{
		self::$generateDependentDataCalled++;
		return parent::generateDependentData();
	}

}

class MockDirectoryCacheDependency extends CDirectoryCacheDependency
{
	public static $generateDependentDataCalled = 0;

	public function generateDependentData()
	{
		self::$generateDependentDataCalled++;
		return parent::generateDependentData();
	}

}