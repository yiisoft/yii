<?php
Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');

if(!defined('SRC_DB_FILE'))
	define('SRC_DB_FILE',dirname(__FILE__).'/../../db/data/source.db');
if(!defined('TEST_DB_FILE'))
	define('TEST_DB_FILE',dirname(__FILE__).'/../../db/data/test.db');

require_once(dirname(__FILE__).'/../../db/data/models.php');

/**
 * CJSON Test
 */
class CJSONTest extends CTestCase {
	private $db;

	function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');
		copy(SRC_DB_FILE,TEST_DB_FILE);

		$config=array(
			'basePath'=>dirname(__FILE__),
			'components'=>array(
				'db'=>array(
					'class'=>'system.db.CDbConnection',
					'connectionString'=>'sqlite:'.TEST_DB_FILE,
				),
			),
		);
		$app=new TestApplication($config);
		$app->db->active=true;
		CActiveRecord::$db=$this->db=$app->db;
	}

	function tearDown()
	{
		if($this->db)
			$this->db->active=false;
	}

	/**
	 * native json_encode can't do it
	 * @return void
	 */
	function testEncodeSingleAR(){
		$post = Post::model()->findByPk(1);
		$this->assertEquals(
			'{"id":"1","title":"post 1","create_time":"100000","author_id":"1","content":"content 1"}',
			CJSON::encode($post)
		);
	}

	/**
	 * native json_encode can't do it
	 * @return void
	 */
	function testEncodeMultipleARs(){
		$posts=Post::model()->findAllByPk(array(1, 2));
		$this->assertEquals(
			'[{"id":"1","title":"post 1","create_time":"100000","author_id":"1","content":"content 1"},{"id":"2","title":"post 2","create_time":"100001","author_id":"2","content":"content 2"}]',
			CJSON::encode($posts)
		);
	}

	function testEncodeSimple(){
		$this->assertEquals("true", CJSON::encode(true));
		$this->assertEquals("false", CJSON::encode(false));
		$this->assertEquals("null", CJSON::encode(null));
		$this->assertEquals("123", CJSON::encode(123));
		$this->assertEquals("123.12", CJSON::encode(123.12));
		$this->assertEquals('"test\\\\me"', CJSON::encode('test\me'));
	}

	function testEncodeArray(){
		$objArr = array('a' => 'b');
		$arrArr = array('a', 'b');
		$mixedArr = array('c', 'a' => 'b');
		$nestedArr = array('a', 'b' => array('a', 'b' => 'c'));

		$this->assertEquals('{"a":"b"}', CJSON::encode($objArr));
		$this->assertEquals('["a","b"]', CJSON::encode($arrArr));
		$this->assertEquals('{"0":"c","a":"b"}', CJSON::encode($mixedArr));
		$this->assertEquals('{"0":"a","b":{"0":"a","b":"c"}}', CJSON::encode($nestedArr));
	}

	function testDecode(){
		$this->assertEquals(array('c', 'a' => 'b'), CJSON::decode('{"0":"c","a":"b"}'));
		$this->assertEquals(array('a', 'b'), CJSON::decode('["a","b"]'));
		$this->assertEquals(array('a', 'b' => array('a', 'b' => 'c')), CJSON::decode('{"0":"a","b":{"0":"a","b":"c"}}'));
	}
}