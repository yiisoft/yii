<?php

class CFileHelperTest extends CTestCase
{
	/**
	 * @var string
	 */
	private $runtimePath;

	public function setUp()
	{
		$this->runtimePath=dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
			'runtime'.DIRECTORY_SEPARATOR.'CFileHelperTest'.DIRECTORY_SEPARATOR;
		if(!is_dir($this->runtimePath) && !(@mkdir($this->runtimePath)))
			$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');

		// create temporary testing data files
		$filesData=array(
			'mimeTypes1.php'=>"<?php return array('txa'=>'application/json','txb'=>'another/mime');",
			'mimeTypes2.php'=>"<?php return array('txt'=>'text/plain','txb'=>'another/mime2');",
		);
		foreach($filesData as $fileName=>$fileData)
			if(!(@file_put_contents($this->runtimePath.$fileName,$fileData)))
				$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
	}

	public function tearDown()
	{
		// clean up temporary testing data files
		foreach(array('mimeTypes1.php','mimeTypes2.php') as $fileName)
			if(is_file($this->runtimePath.$fileName))
				@unlink($this->runtimePath.$fileName);

		if(is_dir($this->runtimePath))
			@rmdir($this->runtimePath);
	}

	public function testGetMimeTypeByExtension()
	{
		// run everything ten times in one test action to be sure that caching inside
		// CFileHelper::getMimeTypeByExtension() is working the right way
		for($i=0; $i<10; $i++)
		{
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txa'));
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txb'));
			$this->assertEquals('text/plain',CFileHelper::getMimeTypeByExtension('test.txt'));

			$this->assertEquals('application/json',CFileHelper::getMimeTypeByExtension('test.txa',$this->runtimePath.'mimeTypes1.php'));
			$this->assertEquals('another/mime',CFileHelper::getMimeTypeByExtension('test.txb',$this->runtimePath.'mimeTypes1.php'));
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txt',$this->runtimePath.'mimeTypes1.php'));

			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txa',$this->runtimePath.'mimeTypes2.php'));
			$this->assertEquals('another/mime2',CFileHelper::getMimeTypeByExtension('test.txb',$this->runtimePath.'mimeTypes2.php'));
			$this->assertEquals('text/plain',CFileHelper::getMimeTypeByExtension('test.txt',$this->runtimePath.'mimeTypes2.php'));
		}
	}
}
