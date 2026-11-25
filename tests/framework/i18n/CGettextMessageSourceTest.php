<?php

class CGettextMessageSourceTest extends CTestCase
{
	private $_directory;

	public function setUp()
	{
		parent::setUp();
		$this->_directory=tempnam(sys_get_temp_dir(),'yii-gettext-');
		unlink($this->_directory);
		mkdir($this->_directory);
		mkdir($this->_directory.'/de');
		Yii::app()->setComponent('gettextTestCache',array(
			'class'=>'CFileCache',
			'cachePath'=>$this->_directory.'/cache',
		));
	}

	public function tearDown()
	{
		Yii::app()->setComponent('gettextTestCache',null);
		CFileHelper::removeDirectory($this->_directory);
		parent::tearDown();
	}

	public function testPoTranslationBypassesOldDecodedCache()
	{
		$file=$this->_directory.'/de/messages.po';
		file_put_contents($file,"msgctxt \"context\"\nmsgid \"source\"\nmsgstr \"literal\\\\n\"\n");
		$mtime=filemtime($file);
		$cache=Yii::app()->getComponent('gettextTestCache');
		$legacyKey=CGettextMessageSource::CACHE_KEY_PREFIX.$file.'.context';
		$legacyData=serialize(array('source'=>"literal\n"));
		$this->assertTrue($cache->set($legacyKey,$legacyData,3600,new CFileCacheDependency($file)));
		$this->assertSame($legacyData,$cache->get($legacyKey));

		$source=$this->createSource(false);
		$this->assertSame('literal\n',$source->translate('context','source','de'));
		$this->assertSame($mtime,filemtime($file));
		$this->assertSame($legacyData,$cache->get($legacyKey));

		// A fresh source reuses the corrected cache even when the file contents change
		// without changing the timestamp on which the cache dependency is based.
		file_put_contents($file,"msgctxt \"context\"\nmsgid \"source\"\nmsgstr \"changed\"\n");
		touch($file,$mtime);
		clearstatcache();
		$source=$this->createSource(false);
		$this->assertSame('literal\n',$source->translate('context','source','de'));
	}

	public function testMoTranslationReusesExistingCache()
	{
		$file=$this->_directory.'/de/messages.mo';
		$moFile=new CGettextMoFile();
		$moFile->save($file,array('context'.chr(4).'source'=>'File translation'));
		$cache=Yii::app()->getComponent('gettextTestCache');
		$key=CGettextMessageSource::CACHE_KEY_PREFIX.$file.'.context';
		$this->assertTrue($cache->set($key,serialize(array('source'=>'Cached translation')),3600,new CFileCacheDependency($file)));
		$source=$this->createSource(true);
		$this->assertSame('Cached translation',$source->translate('context','source','de'));
	}

	private function createSource($useMoFile)
	{
		$source=new CGettextMessageSource();
		$source->basePath=$this->_directory;
		$source->useMoFile=$useMoFile;
		$source->cachingDuration=3600;
		$source->cacheID='gettextTestCache';
		$source->forceTranslation=true;
		$source->init();
		return $source;
	}
}
