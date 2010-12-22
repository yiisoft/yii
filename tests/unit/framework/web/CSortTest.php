<?php
/**
 * CSortTest
 */

Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');

class CSortTest extends CTestCase {
	private $dbPath;
	private $db;

	public function setUp(){
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		// put db into runtime
		$this->dbPath = dirname(__FILE__).'/CSortTest.sqlite';

		$pdo = new PDO('sqlite:'.$this->dbPath);
		$pdo->exec(file_get_contents(dirname(__FILE__).'/CSortTest.sql'));
		unset($pdo);

		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'db'=>array(
					'class'=>'system.db.CDbConnection',
					'connectionString'=>'sqlite:'.$this->dbPath,
				),
			),
		);
		$app=new TestApplication($config);
		$app->db->active=true;
		CActiveRecord::$db=$this->db=$app->db;
	}

	public function tearDown(){
		if($this->db)
			$this->db->active=false;

		// clean up db file
		unlink($this->dbPath);
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

		$sort = new CSort('Post');
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
}


class Post extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'post';
    }

	public function relations() {
        return array(
           'comments'=>array(self::HAS_MANY, 'Comment', 'post_id'),
        );
    }
}

class Comment extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'comment';
    }

	public function relations() {
        return array(
           'post'=>array(self::BELONGS_TO, 'Post', 'post_id'),
        );
    }
}