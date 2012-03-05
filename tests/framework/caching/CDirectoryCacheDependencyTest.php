<?php

Yii::import('system.caching.dependencies.CDirectoryCacheDependency');

class CDirectoryCacheDependencyTest extends CTestCase
{
	private $testDir1;
	private $testDir2;

	public function __construct()
	{
		parent::__construct();
		Yii::app()->reset();
		$this->testDir1 = Yii::app()->getRuntimePath().'/CDirectoryCacheDependencyTest_temp1';
		@mkdir($this->testDir1);
		$this->testDir2 = Yii::app()->getRuntimePath().'/CDirectoryCacheDependencyTest_temp2';
	}

	public function testDirectoryName()
	{
		$directory=realpath($this->testDir1);
		$dependency=new CDirectoryCacheDependency($directory);
		$this->assertEquals($dependency->directory,$directory);

		$this->setExpectedException('CException');
		$dependency=new CDirectoryCacheDependency($this->testDir2);
		$dependency->evaluateDependency();
	}

	public function testRecursiveLevel()
	{
		$dependency=new CDirectoryCacheDependency($this->testDir1);
		$this->assertEquals($dependency->recursiveLevel,-1);
		$dependency->recursiveLevel=5;
		$this->assertEquals($dependency->recursiveLevel,5);
	}

	public function testHasChanged()
	{
		$tempFile=$this->testDir1.'/foo.txt';
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