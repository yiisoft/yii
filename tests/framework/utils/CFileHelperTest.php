<?php
class CFileHelperTest extends CTestCase
{
	private $testDir;
	private $testMode=0770;
	private $rootDir1="test1";
	private $rootDir2="test2";
	private $subDir='sub';
	private $file='testfile';

	protected function setUp()
	{
		$this->testDir=Yii::getPathOfAlias('application.runtime.CFileHelper');
		if(!is_dir($this->testDir) && !(@mkdir($this->testDir)))
			$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');

		// create temporary testing data files
		$filesData=array(
			'mimeTypes1.php'=>"<?php return array('txa'=>'application/json','txb'=>'another/mime');",
			'mimeTypes2.php'=>"<?php return array('txt'=>'text/plain','txb'=>'another/mime2');",
		);
		foreach($filesData as $fileName=>$fileData)
			if(!(@file_put_contents($this->testDir.$fileName,$fileData)))
				$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
	}

	protected function tearDown()
	{
		if (is_dir($this->testDir))
			$this->rrmdir($this->testDir);
	}

	public function testGetMimeTypeByExtension()
	{
		// run everything ten times in one test action to be sure that caching inside
		// CFileHelper::getMimeTypeByExtension() is working the right way
		for($i=0;$i<10;$i++)
		{
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txa'));
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txb'));
			$this->assertEquals('text/plain',CFileHelper::getMimeTypeByExtension('test.txt'));

			$this->assertEquals('application/json',CFileHelper::getMimeTypeByExtension('test.txa',$this->testDir.'mimeTypes1.php'));
			$this->assertEquals('another/mime',CFileHelper::getMimeTypeByExtension('test.txb',$this->testDir.'mimeTypes1.php'));
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txt',$this->testDir.'mimeTypes1.php'));

			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txa',$this->testDir.'mimeTypes2.php'));
			$this->assertEquals('another/mime2',CFileHelper::getMimeTypeByExtension('test.txb',$this->testDir.'mimeTypes2.php'));
			$this->assertEquals('text/plain',CFileHelper::getMimeTypeByExtension('test.txt',$this->testDir.'mimeTypes2.php'));
		}
	}

	public function testCopyDirectory_subDir_modeShoudBe0775()
	{
		if (substr(PHP_OS,0,3)=='WIN')
			$this->markTestSkipped("Can't reliably test it on Windows because fileperms() always return 0777.");

		$this->createTestStruct($this->testDir);
		$src=$this->testDir.DIRECTORY_SEPARATOR.$this->rootDir1;
		$dst=$this->testDir.DIRECTORY_SEPARATOR.$this->rootDir2;
		CFileHelper::copyDirectory($src,$dst,array('newDirMode'=>$this->testMode));

		$subDir2Mode=$this->getMode($dst.DIRECTORY_SEPARATOR.$this->subDir);
		$expectedMode=sprintf('%o',$this->testMode);
		$this->assertEquals($expectedMode,$subDir2Mode,"Subdir mode is not {$expectedMode}");
	}

	public function testCopyDirectory_subDir_modeShoudBe0777()
	{
		if (substr(PHP_OS,0,3)=='WIN')
			$this->markTestSkipped("Can't reliably test it on Windows because fileperms() always return 0777.");

		$this->createTestStruct($this->testDir);
		$src=$this->testDir.DIRECTORY_SEPARATOR.$this->rootDir1;
		$dst=$this->testDir.DIRECTORY_SEPARATOR.$this->rootDir2;
		CFileHelper::copyDirectory($src,$dst);

		$subDir2Mode=$this->getMode($dst.DIRECTORY_SEPARATOR.$this->subDir);
		$expectedMode=sprintf('%o',0777);
		$this->assertEquals($expectedMode,$subDir2Mode,"Subdir mode is not {$expectedMode}");
	}

	private function createTestStruct($testDir)
	{
		$rootDir=$testDir.DIRECTORY_SEPARATOR.$this->rootDir1;
		mkdir($rootDir);

		$subDir=$testDir.DIRECTORY_SEPARATOR.$this->rootDir1.DIRECTORY_SEPARATOR.$this->subDir;
		mkdir($subDir);

		$file=$testDir.DIRECTORY_SEPARATOR.$this->rootDir1.DIRECTORY_SEPARATOR.$this->subDir.DIRECTORY_SEPARATOR.$this->file;
		file_put_contents($file,'12321312');
	}

	private function getMode($file)
	{
		return substr(sprintf('%o',fileperms($file)),-4);
	}

	private function rrmdir($dir)
	{
		if($handle=opendir($dir))
		{
			while(false!==($entry=readdir($handle)))
			{
				if($entry!="." && $entry!="..")
				{
					if(is_dir($dir."/".$entry)===true)
						$this->rrmdir($dir."/".$entry);
					else
						unlink($dir."/".$entry);
				}
			}
			closedir($handle);
			rmdir($dir);
		}
	}
}
