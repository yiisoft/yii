<?php

require_once(dirname(__FILE__).'/../db/data/models.php');

class CActiveDataProviderTest extends CTestCase
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

	public function testCountCriteria()
	{
		// 1
		$dataProvider=new CActiveDataProvider('Post',array(
			'criteria'=>array(
				'condition'=>'content LIKE "%content%"',
				'order'=>'create_time DESC',
				'with'=>array('author'),
			),
			'pagination'=>array(
				'pageSize'=>5,
			),
		));
		$this->assertSame($dataProvider->countCriteria,$dataProvider->criteria);
		$this->assertEquals(5,$dataProvider->getTotalItemCount(true));

		// 2
		$dataProvider->setCountCriteria(array(
			'condition'=>'content LIKE "%content 1%"',
		));
		$this->assertNotSame($dataProvider->countCriteria,$dataProvider->criteria);
		$this->assertEquals(1,$dataProvider->getTotalItemCount(true));

		// 3
		$dataProvider->setCountCriteria(array(
			'condition'=>'content LIKE "%content%"',
		));
		$this->assertNotSame($dataProvider->countCriteria,$dataProvider->criteria);
		$this->assertEquals(5,$dataProvider->getTotalItemCount(true));
	}
}
