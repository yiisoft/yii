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
		// Behavior is slightly modified after patching CCacheDependency, but in my opinion, correctly.
		//
		// Before: after calling evaluateDependency(), hasChanged() would keep returning 'true',
		// as $_reusableData' also stored the result of the comparison.
		//
		// Now: since the dependency data is always compared to the stored value in $_reusableData, after calling evaluateDependency(),
		// hasChanged() will return 'false'.
		//$this->assertTrue($dependency3->getHasChanged());
		$this->assertFalse($dependency3->getHasChanged());
		$this->assertEquals(1,MockDirectoryCacheDependency::$generateDependentDataCalled);
	}

	/**
	 * @note This test requires CCacheDependency::$_reusableData to be made public.
	 */
	public function testReusableWithStoredDependencies()
	{
		$stateName = 'test';
		$storedValue = 500;
		Yii::app()->setGlobalState($stateName, $storedValue);
		$dependency = new CGlobalStateCacheDependency($stateName);
		$dependency->reuseDependentData = true;

		$fileCache = new CFileCache;
		$fileCache->set('foo', $storedValue, 0, $dependency);
		$fileCache->set('bar', $storedValue, 0, $dependency);

		$this->assertTrue($fileCache->get('foo')==$storedValue);

		/** NEW REQUEST **/
		CGlobalStateCacheDependency::$_reusableData=array();	// reset $_reusableData

		Yii::app()->setGlobalState($stateName, $storedValue+1);	// changing state should invalidate cache

		$this->assertFalse($fileCache->get('foo'));
		$fileCache->set('foo', $storedValue+1, 0, $dependency);	// update cache with new value

		/** NEW REQUEST **/
		CGlobalStateCacheDependency::$_reusableData=array();	// reset $_reusableData

		$this->assertTrue($fileCache->get('foo')==$storedValue+1);
		$this->assertFalse($fileCache->get('bar'));				// this should be invalidated
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