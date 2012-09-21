<?php

class CFileHelperTest extends CTestCase
{
	public function testGetMimeTypeByExtension()
	{
		// run everything ten times in one test action to be sure that caching inside
		// CFileHelper::getMimeTypeByExtension() is working the right way
		for($i=0; $i<10; $i++)
		{
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txa'));
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txb'));
			$this->assertEquals('text/plain',CFileHelper::getMimeTypeByExtension('test.txt'));

			$path=dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'mimeTypes1.php';
			$this->assertEquals('application/json',CFileHelper::getMimeTypeByExtension('test.txa',$path));
			$this->assertEquals('another/mime',CFileHelper::getMimeTypeByExtension('test.txb',$path));
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txt',$path));

			$path=dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'mimeTypes2.php';
			$this->assertNull(CFileHelper::getMimeTypeByExtension('test.txa',$path));
			$this->assertEquals('another/mime2',CFileHelper::getMimeTypeByExtension('test.txb',$path));
			$this->assertEquals('text/plain',CFileHelper::getMimeTypeByExtension('test.txt',$path));
		}
	}
}
