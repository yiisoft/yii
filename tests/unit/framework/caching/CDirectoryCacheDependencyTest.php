<?php

Yii::import('system.caching.dependencies.CDirectoryCacheDependency');

class CDirectoryCacheDependencyTest extends CTestCase
{
	public function testDirectoryName()
	{
		$directory=realpath(dirname(__FILE__).'/temp');
		$dependency=new CDirectoryCacheDependency($directory);
		$this->assertEquals($dependency->directory,$directory);

		$this->setExpectedException('CException');
		$dependency=new CDirectoryCacheDependency(dirname(__FILE__).'/temp2');
		$dependency->evaluateDependency();
	}

	public function testRecursiveLevel()
	{
		$directory=realpath(dirname(__FILE__).'/temp');
		$dependency=new CDirectoryCacheDependency(dirname(__FILE__).'/temp');
		$this->assertEquals($dependency->recursiveLevel,-1);
		$dependency->recursiveLevel=5;
		$this->assertEquals($dependency->recursiveLevel,5);
	}

	public function testHasChanged()
	{
		$tempFile=dirname(__FILE__).'/temp/foo.txt';
		@unlink($tempFile);
		$fw=fopen($tempFile,"w");
		fwrite($fw,"test");
		fclose($fw);
		clearstatcache();

		$dependency=new CDirectoryCacheDependency(dirname($tempFile));
		$dependency->evaluateDependency();
		$str=serialize($dependency);

		// test directory not changed
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

?>