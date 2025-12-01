<?php

Yii::import('system.caching.dependencies.CFileCacheDependency');

class CFileCacheDependencyTest extends CTestCase
{
	public function testFileName()
	{
		$dependency=new CFileCacheDependency(__FILE__);
		$this->assertEquals($dependency->fileName,__FILE__);
		$dependency->evaluateDependency();
		$this->assertEquals($dependency->dependentData,filemtime(__FILE__));

		$dependency=new CFileCacheDependency(dirname(__FILE__).'/foo.txt');
		$dependency->evaluateDependency();
		$this->assertFalse($dependency->dependentData);
	}

	public function testHasChanged()
	{
		$tempFile=Yii::app()->getRuntimePath().'/CFileCacheDependencyTest_foo.txt';
		@unlink($tempFile);
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test");
		fclose($fw);
		clearstatcache();

		$dependency=new CFileCacheDependency($tempFile);
		$dependency->evaluateDependency();
		$str=serialize($dependency);

		// test file not changed
		sleep(2);
		$dependency=unserialize($str);
		$this->assertFalse($dependency->hasChanged);

		// change file
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test again");
		fclose($fw);
		clearstatcache();

		// test file changed
		sleep(2);
		$dependency->evaluateDependency();
		$dependency=unserialize($str);
		$this->assertTrue($dependency->hasChanged);

		@unlink($tempFile);
	}
}