<?php
Yii::import('system.caching.dependencies.CChainedCacheDependency');
Yii::import('system.caching.dependencies.CReusableCacheDependency');
Yii::import('system.caching.dependencies.CFileCacheDependency');
Yii::import('system.caching.dependencies.CDirectoryCacheDependency');

class CReusableCacheDependencyTest extends CTestCase
{
	public function testHasChanged()
	{
		$tempFile=dirname(__FILE__).'/../../runtime/foo.txt';
		@unlink($tempFile);
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test");
		fclose($fw);
		clearstatcache();
		$dependency = new CReusableCacheDependency(array(
			new CFileCacheDependency($tempFile)
		));
		$dependency->evaluateDependency();
		// change file
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test again");
		fclose($fw);
		clearstatcache();
		$this->assertFalse($dependency->getHasChanged());

		$dependency2 = new CReusableCacheDependency(array(
					new CFileCacheDependency($tempFile),
					new CFileCacheDependency($tempFile),
				));

		// change file
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test again");
		fclose($fw);
		clearstatcache();
		$this->assertTrue($dependency2->getHasChanged());

		$dependency3 = new CReusableCacheDependency(array(
						new CFileCacheDependency($tempFile),
						new CFileCacheDependency($tempFile),
					));
		$dependency3->evaluateDependency();
		$this->assertTrue($dependency3->getHasChanged());
	}
}