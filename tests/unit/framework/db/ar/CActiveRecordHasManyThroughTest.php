<?php
Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');
require dirname(__FILE__).'/CActiveRecordHasManyThroughModels.php';

/**
 * AR's HAS_MANY `through` option allows to use data from relation's binding
 * table.
 */
class CActiveRecordHasManyThroughTest extends CTestCase {
	private $dbPath;
	private $db;

	public function setUp(){
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		// put db into runtime
		$this->dbPath = dirname(dirname(dirname(dirname(__FILE__)))).'/assets/CActiveRecordHasManyThroughTest.sqlite';
		
		$db = new SQLiteDatabase($this->dbPath);
		$db->query(file_get_contents(dirname(__FILE__).'/CActiveRecordHasManyThroughTest.sql'));
		unset($db);

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

	public function testEager(){
		$user = User::model()->with('groups')->findByPk(1);
		$result = array();
		foreach($user->groups as $group){
			foreach($group->usergroups as $usergroup){
				$result[] = array($user->usename, $group->name, $usergroup->role);
			}
		}

		$this->assertEquals(array(
			array('Alexander', 'Yii', 'dev'),
			array('Alexander', 'Zii', 'user'),
		), $result);
	}

	public function testLazy(){
		$user = User::model()->findByPk(1);

		$result = array();
		foreach($user->groups as $group){
			foreach($group->usergroups as $usergroup){
				$result[] = array($user->usename, $group->name, $usergroup->role);
			}
		}

		$this->assertEquals(array(
			array('Alexander', 'Yii', 'dev'),
			array('Alexander', 'Zii', 'user'),
		), $result);
	}
}
