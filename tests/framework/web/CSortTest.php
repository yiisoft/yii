<?php
/**
 * CSortTest
 */

Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');

class CSortTest extends CTestCase {
	private $db;

	public function setUp(){
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->db=new CDbConnection('sqlite::memory:');
		$this->db->active=true;
		$this->db->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/CSortTest.sql'));
		CActiveRecord::$db=$this->db;
	}

	public function tearDown(){
		$this->db->active=false;
	}

	/**
	 * Tests for acceptance of fields with dots in
	 * CSort::attributes.
	 *
	 * @return void
	 */
	function testGetDirectionsWithDots(){
		$_GET['sort'] = 'comments.id';

		$criteria = new CDbCriteria();
		$criteria->with = 'comments';

		$sort = new CSort('TestPost');
		$sort->attributes = array(
			'id',
			'comments.id' => array(
			  'asc'=>'comments.id',
			  'desc'=>'comments.id desc',
			),
		);
		$sort->applyOrder($criteria);
		$directions = $sort->getDirections();

		$this->assertTrue(isset($directions['comments.id']));
	}

	/**
	 * Tests for acceptance of arrays for asc/desc keys in
	 * CSort::attributes.
	 *
	 * @return void
	 */
	function testGetDirectionsWithArrays(){
		$_GET['sort'] = 'comments.id';

		$criteria = new CDbCriteria();
		$criteria->with = 'comments';

		$sort = new CSort('TestPost');
		$sort->attributes = array(
			'id',
			'comments.id' => array(
			  'asc'=>array('comments.id', 'id'),
			  'desc'=>array('comments.id desc', 'id desc'),
			),
		);
		$sort->applyOrder($criteria);
		$directions = $sort->getDirections();

		$this->assertEquals($criteria->order, 'comments.id, id');
	}
}


class TestPost extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'post';
    }

	public function relations() {
        return array(
           'comments'=>array(self::HAS_MANY, 'TestComment', 'post_id'),
        );
    }
}

class TestComment extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'comment';
    }

	public function relations() {
        return array(
           'post'=>array(self::BELONGS_TO, 'TestPost', 'post_id'),
        );
    }
}
