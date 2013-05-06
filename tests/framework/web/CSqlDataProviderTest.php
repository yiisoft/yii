<?php

class CSqlDataProviderTest extends CTestCase
{
	/**
	 * @var CDbConnection
	 */
	private $db;

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->db=new CDbConnection('sqlite::memory:');
		$this->db->active=true;
		$this->db->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/../db/data/sqlite.sql'));
		CActiveRecord::$db=$this->db;
	}

	public function tearDown()
	{
		$this->db->active=false;
	}

	/**
	 * https://github.com/yiisoft/yii/issues/2449
	 */
	public function testFetchKeysWithDbCommandSpecifiedAndPdoFetchObjEnabled()
	{
		$command1=$this->db->createCommand()->select('*')->from('posts')->setFetchMode(PDO::FETCH_ASSOC);
		$dataProvider1=new CSqlDataProvider($command1);
		$this->assertSame(array('1','2','3','4','5'),$dataProvider1->keys);

		$command2=$this->db->createCommand()->select('*')->from('posts')->setFetchMode(PDO::FETCH_OBJ);
		$dataProvider2=new CSqlDataProvider($command2);
		$this->assertSame(array('1','2','3','4','5'),$dataProvider2->keys);
	}
}
