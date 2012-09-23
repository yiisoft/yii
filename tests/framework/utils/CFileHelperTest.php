<?php

class CFileHelperTest extends CTestCase
{
	public function testGetMimeTypeByExtension()
	{
		// create temporary testing data files
		$runtimePath=dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR;
		if(!(@file_put_contents($runtimePath.'mimeTypes1.php',"<?php return array('txa'=>'application/json','txb'=>'another/mime');")))
			$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
		if(!(@file_put_contents($runtimePath.'mimeTypes2.php',"<?php return array('txt'=>'text/plain','txb'=>'another/mime2');")))
			$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');

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
