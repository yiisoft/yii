<?php

class CFileHelperTest extends CTestCase
{
	public function setUp()
	{
		if(!is_dir(Yii::getPathOfAlias('application.runtime.CFileHelper')) &&
			!(@mkdir(Yii::getPathOfAlias('application.runtime.CFileHelper'))))
			$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');

		// create temporary testing data files
		$filesData=array(
			'mimeTypes1.php'=>"<?php return array('txa'=>'application/json','txb'=>'another/mime');",
			'mimeTypes2.php'=>"<?php return array('txt'=>'text/plain','txb'=>'another/mime2');",
		);
		foreach($filesData as $fileName=>$fileData)
			if(!(@file_put_contents(Yii::getPathOfAlias('application.runtime.CFileHelper').$fileName,$fileData)))
				$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
	}

	public function tearDown()
	{
		// clean up temporary testing data files
		foreach(array('mimeTypes1.php','mimeTypes2.php') as $fileName)
			if(is_file(Yii::getPathOfAlias('application.runtime.CFileHelper').$fileName))
				@unlink(Yii::getPathOfAlias('application.runtime.CFileHelper').$fileName);

		if(is_dir(Yii::getPathOfAlias('application.runtime.CFileHelper')))
			@rmdir(Yii::getPathOfAlias('application.runtime.CFileHelper'));
	}

	public function testGetMimeTypeByExtension()
	{
		$runtimePath=Yii::getPathOfAlias('application.runtime.CFileHelper');

		// run everything ten times in one test action to be sure that caching inside
		// CFileHelper::getMimeTypeByExtension() is working the right way
		for($i=0; $i<10; $i++)
		{
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txa'));
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txb'));
			$this->assertEquals('text/plain',CFileHelper::getMimeTypeByExtension('test.txt'));

			$this->assertEquals('application/json',CFileHelper::getMimeTypeByExtension('test.txa',$runtimePath.'mimeTypes1.php'));
			$this->assertEquals('another/mime',CFileHelper::getMimeTypeByExtension('test.txb',$runtimePath.'mimeTypes1.php'));
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txt',$runtimePath.'mimeTypes1.php'));

			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txa',$runtimePath.'mimeTypes2.php'));
			$this->assertEquals('another/mime2',CFileHelper::getMimeTypeByExtension('test.txb',$runtimePath.'mimeTypes2.php'));
			$this->assertEquals('text/plain',CFileHelper::getMimeTypeByExtension('test.txt',$runtimePath.'mimeTypes2.php'));
		}
	}
}
