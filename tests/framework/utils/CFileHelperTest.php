<?php
class CFileHelperTest extends CTestCase
{
	private $testDir;
	private $testMode = 0770;
	private $rootDir1 = "test1";
	private $rootDir2 = "test2";
	private $subDir = 'sub';
	private $file = 'testfile';

	protected function setUp()
	{
		parent::setUp();
		$this->testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . __CLASS__ . time();
		$this->createTestStruct($this->testDir);
	}

	protected function tearDown()
	{
		parent::tearDown();
		if (is_dir($this->testDir)) $this->rrmdir($this->testDir);
		clearstatcache();
	}

	function testCopyDirectory_subDir_modeShoudBe0775()
	{
		$src = $this->testDir . DIRECTORY_SEPARATOR . $this->rootDir1;
		$dst = $this->testDir . DIRECTORY_SEPARATOR . $this->rootDir2;
		CFileHelper::copyDirectory($src, $dst, array('newDirMode' => $this->testMode));

		$subDir2Mode = $this->getMode($dst . DIRECTORY_SEPARATOR . $this->subDir );
		$expectedMode = sprintf('%o', $this->testMode);
		$this->assertEquals($expectedMode, $subDir2Mode, "Subdir mode is not {$expectedMode}");
	}

	function testCopyDirectory_subDir_modeShoudBe0777()
	{
		$src = $this->testDir . DIRECTORY_SEPARATOR . $this->rootDir1;
		$dst = $this->testDir . DIRECTORY_SEPARATOR . $this->rootDir2;
		CFileHelper::copyDirectory($src, $dst);

		$subDir2Mode = $this->getMode($dst . DIRECTORY_SEPARATOR . $this->subDir );
		$expectedMode = sprintf('%o', 0777);
		$this->assertEquals($expectedMode, $subDir2Mode, "Subdir mode is not {$expectedMode}");
	}

	private function createTestStruct($testDir)
	{
		if (!mkdir($testDir)) $this->fail("mkdir of '{$testDir}' failed");

		$rootDir = $testDir . DIRECTORY_SEPARATOR . $this->rootDir1;
		mkdir($rootDir);

		$subDir = $testDir . DIRECTORY_SEPARATOR . $this->rootDir1 . DIRECTORY_SEPARATOR . $this->subDir;
		mkdir($subDir);

		$file = $testDir . DIRECTORY_SEPARATOR . $this->rootDir1 . DIRECTORY_SEPARATOR . $this->subDir . DIRECTORY_SEPARATOR . $this->file;
		file_put_contents($file, '12321312');
	}

	private function getMode($file)
	{
		return substr(sprintf('%o', fileperms($file)), -4);
	}

	private function rrmdir($dir)
	{
		if ($handle = opendir($dir))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != "..")
				{
					if (is_dir($dir . "/" . $entry) === true)
					{
						$this->rrmdir($dir . "/" . $entry);
					}
					else
					{
						unlink($dir . "/" . $entry);
					}
				}
			}
			closedir($handle);
			rmdir($dir);
		}
	}
}
