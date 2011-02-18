<?php
Yii::import('system.db.CDbConnection');
Yii::import('system.db.ar.CActiveRecord');
require dirname(__FILE__).'/../data/CActiveRecordHasManyThroughModels.php';

/**
 * AR's HAS_MANY `through` option allows to use data from relation's binding
 * table.
 */
class CActiveRecordHasManyThroughTest extends CTestCase {
	private $_connection;

	public function setUp(){
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->_connection=new CDbConnection('sqlite::memory:');
		$this->_connection->active=true;
		$this->_connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/../data/CActiveRecordHasManyThroughTest.sql'));
		CActiveRecord::$db=$this->_connection;
	}

	public function tearDown(){
		if($this->db)
			$this->db->active=false;
	}

	public function testEager(){
		$user = TestUser::model()->with('groups')->findByPk(1);
		$result = array();
		foreach($user->groups as $group){
			foreach($group->usergroups as $usergroup){
				$result[] = array($user->username, $group->name, $usergroup->role);
			}
		}

		$this->assertEquals(array(
			array('Alexander', 'Yii', 'dev'),
			array('Alexander', 'Zii', 'user'),
		), $result);
	}

	public function testLazy(){
		$user = TestUser::model()->findByPk(1);

		$result = array();
		foreach($user->groups as $group){
			foreach($group->usergroups as $usergroup){
				$result[] = array($user->username, $group->name, $usergroup->role);
			}
		}

		$this->assertEquals(array(
			array('Alexander', 'Yii', 'dev'),
			array('Alexander', 'Zii', 'user'),
		), $result);
	}
}
