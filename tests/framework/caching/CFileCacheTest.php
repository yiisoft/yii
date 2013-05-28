<?php

class CFileCacheTest extends CTestCase
{
	/**
	 * https://github.com/yiisoft/yii/issues/2435
	 */
	public function testEmbedExpiry()
	{
		$app=new TestApplication(array(
			'id'=>'testApp',
			'components'=>array(
				'cache'=>array('class'=>'CFileCache'),
			),
		));
		$app->reset();
		$cache=$app->cache;

		$cache->set('testKey1','testValue1',2);
		$files=glob(Yii::getPathOfAlias('application.runtime.cache').'/*.bin');
		$this->assertEquals(time()+2,filemtime($files[0]));

		$cache->set('testKey2','testValue2',2);
		sleep(1);
		$this->assertEquals('testValue2',$cache->get('testKey2'));

		$cache->set('testKey3','testValue3',2);
		sleep(3);
		$this->assertEquals(false,$cache->get('testKey2'));


		$app=new TestApplication(array(
			'id'=>'testApp',
			'components'=>array(
				'cache'=>array('class'=>'CFileCache','embedExpiry'=>true),
			),
		));
		$app->reset();
		$cache=$app->cache;

		$cache->set('testKey4','testValue4',2);
		$files=glob(Yii::getPathOfAlias('application.runtime.cache').'/*.bin');
		$this->assertEquals(time(),filemtime($files[0]));

		$cache->set('testKey5','testValue5',2);
		sleep(1);
		$this->assertEquals('testValue5',$cache->get('testKey5'));

		$cache->set('testKey6','testValue6',2);
		sleep(3);
		$this->assertEquals(false,$cache->get('testKey6'));
	}
}
