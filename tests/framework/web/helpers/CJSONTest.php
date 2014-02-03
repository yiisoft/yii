<?php
Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');

require_once(dirname(__FILE__).'/../../db/data/models.php');

/**
 * CJSON Test
 */
class CJSONTest extends CTestCase {
	private $db;

	protected function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->db=new CDbConnection('sqlite::memory:');
		$this->db->active=true;
		$this->db->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/../../db/data/sqlite.sql'));
		CActiveRecord::$db=$this->db;
	}

	protected function tearDown()
	{
		$this->db->active=false;
	}

	/**
	 * native json_encode can't do it
	 * @return void
	 */
	public function testEncodeSingleAR(){
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
	public function testEncodeMultipleARs(){
		$posts=Post::model()->findAllByPk(array(1, 2));
		$this->assertEquals(
			'[{"id":"1","title":"post 1","create_time":"100000","author_id":"1","content":"content 1"},{"id":"2","title":"post 2","create_time":"100001","author_id":"2","content":"content 2"}]',
			CJSON::encode($posts)
		);
	}

	public function testEncodeSimple(){
		$this->assertEquals("true", CJSON::encode(true));
		$this->assertEquals("false", CJSON::encode(false));
		$this->assertEquals("null", CJSON::encode(null));
		$this->assertEquals("123", CJSON::encode(123));
		$this->assertEquals("123.12", CJSON::encode(123.12));
		$this->assertEquals('"test\\\\me"', CJSON::encode('test\me'));
	}

	public function testEncodeArray(){
		$objArr = array('a' => 'b');
		$arrArr = array('a', 'b');
		$mixedArr = array('c', 'a' => 'b');
		$nestedArr = array('a', 'b' => array('a', 'b' => 'c'));

		$this->assertEquals('{"a":"b"}', CJSON::encode($objArr));
		$this->assertEquals('["a","b"]', CJSON::encode($arrArr));
		$this->assertEquals('{"0":"c","a":"b"}', CJSON::encode($mixedArr));
		$this->assertEquals('{"0":"a","b":{"0":"a","b":"c"}}', CJSON::encode($nestedArr));
	}

	public function testDecode(){
		$this->assertEquals(array('c', 'a' => 'b'), CJSON::decode('{"0":"c","a":"b"}'));
		$this->assertEquals(array('a', 'b'), CJSON::decode('["a","b"]'));
		$this->assertEquals(array('a', 'b' => array('a', 'b' => 'c')), CJSON::decode('{"0":"a","b":{"0":"a","b":"c"}}'));
	}

	public function testJsonSerializable()
    {
        if(!interface_exists('JsonSerializable'))
            $this->markTestSkipped('JsonSerializable interface is required.');

        $className = get_class($this).'_JsonSerializable';
        $classCode = <<<EOL
class $className implements JsonSerializable{
	public function jsonSerialize()
	{
		return 'test';
	}
}
EOL;
		eval($classCode);
		$object = new $className();
		$this->assertEquals(CJSON::encode($object), json_encode($object));
    }


}
