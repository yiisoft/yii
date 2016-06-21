<?php

class CDbStatePersisterTest extends CTestCase
{
	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');
		// clean up runtime directory
		$app=new TestApplication;
		$app->reset();
	}

	public function testLoadSave()
	{
		$app=new TestApplication(array(
			'components'=>array(
				'db'=>array(
					'class' => 'CDbConnection',
					'connectionString' => 'mysql:host=127.0.0.1;port=3306;dbname=yii',
					'username' => 'test',
					'password' => 'test',
					'emulatePrepare' => true,
					'charset' => 'utf8',
					'enableParamLogging' => true,
				),
				'statePersister' => array(
					'class' => 'CDbStatePersister'
				)
			)
		));
		$sp=$app->statePersister;
		$data=array('123','456','a'=>443);
		$sp->save($data);
		$this->assertEquals($sp->load(),$data);
	}
}
