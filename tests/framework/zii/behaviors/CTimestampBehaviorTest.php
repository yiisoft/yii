<?php

class CTimestampBehaviorTest extends CTestCase
{
	private $_connection;

	protected function setUp()
	{
		// pdo and pdo_sqlite extensions are obligatory
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		// open connection and create testing tables
		$this->_connection=new CDbConnection('sqlite::memory:');
		$this->_connection->active=true;
		$this->_connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/CTimestampBehaviorTest.sql'));
		CActiveRecord::$db=$this->_connection;
	}

	protected function tearDown()
	{
		// close connection
		$this->_connection->active=false;
	}

	public function testCreateAttribute()
	{
		// behavior changes created_at after inserting
		$model1=new CTimestampBehaviorTestActiveRecord;
		$model1->attachBehavior('timestampBehavior', array(
			'class'=>'zii.behaviors.CTimestampBehavior',
			'createAttribute'=>'created_at',
			'updateAttribute'=>null,
			'setUpdateOnCreate'=>false,
		));
		$model1->title='testing-row-1';
		$this->assertEquals(0, $model1->created_at);
		$saveTime=time();
		$model1->save();
		$this->assertEquals($saveTime, $model1->created_at, '', 2);

		// behavior changes created_at after inserting
		$model2=new CTimestampBehaviorTestActiveRecord;
		$model2->attachBehavior('timestampBehavior', array(
			'class'=>'zii.behaviors.CTimestampBehavior',
			'createAttribute'=>'created_at',
			'updateAttribute'=>null,
			'setUpdateOnCreate'=>false,
		));
		$model2->title='testing-row-2';
		$model2->created_at=123123;
		$this->assertEquals(123123, $model2->created_at);
		$saveTime=time();
		$model2->save();
		$this->assertEquals($saveTime, $model2->created_at, '', 2);

		// behavior does not changes created_at after inserting
		$model3=new CTimestampBehaviorTestActiveRecord;
		$model3->attachBehavior('timestampBehavior', array(
			'class'=>'zii.behaviors.CTimestampBehavior',
			'createAttribute'=>null,
			'updateAttribute'=>null,
			'setUpdateOnCreate'=>false,
		));
		$model3->title='testing-row-3';
		$model3->created_at=321321;
		$this->assertEquals(321321, $model3->created_at);
		$model3->save();
		$this->assertEquals(321321, $model3->created_at);
	}

	public function testUpdateAttribute()
	{
		// behavior changes updated_at after updating
		$model1=CTimestampBehaviorTestActiveRecord::model()->findByPk(1);
		$model1->attachBehavior('timestampBehavior', array(
			'class'=>'zii.behaviors.CTimestampBehavior',
			'createAttribute'=>null,
			'updateAttribute'=>'updated_at',
			'setUpdateOnCreate'=>false,
		));
		$this->assertEquals(1340529410, $model1->updated_at);
		$saveTime=time();
		$model1->save();
		$this->assertEquals($saveTime, $model1->updated_at, '', 2);

		// behavior changes updated_at after updating
		$model2=CTimestampBehaviorTestActiveRecord::model()->findByPk(2);
		$model2->attachBehavior('timestampBehavior', array(
			'class'=>'zii.behaviors.CTimestampBehavior',
			'createAttribute'=>null,
			'updateAttribute'=>'updated_at',
			'setUpdateOnCreate'=>true,
		));
		$this->assertEquals(1340529305, $model2->updated_at);
		$saveTime=time();
		$model2->save();
		$this->assertEquals($saveTime, $model2->updated_at, '', 2);

		// behavior does not changes updated_at after updating
		$model3=CTimestampBehaviorTestActiveRecord::model()->findByPk(3);
		$model3->attachBehavior('timestampBehavior', array(
			'class'=>'zii.behaviors.CTimestampBehavior',
			'createAttribute'=>null,
			'updateAttribute'=>null,
			'setUpdateOnCreate'=>false,
		));
		$this->assertEquals(1340529200, $model3->updated_at);
		$model3->save();
		$this->assertEquals(1340529200, $model3->updated_at);

		// behavior does not changes updated_at after inserting
		$model4=new CTimestampBehaviorTestActiveRecord;
		$model4->attachBehavior('timestampBehavior', array(
			'class'=>'zii.behaviors.CTimestampBehavior',
			'createAttribute'=>null,
			'updateAttribute'=>'updated_at',
			'setUpdateOnCreate'=>false,
		));
		$model4->title='testing-row-3';
		$model4->updated_at=321321321;
		$this->assertEquals(321321321, $model4->updated_at);
		$model4->save();
		$this->assertEquals(321321321, $model4->updated_at);

		// behavior changes updated_at after inserting
		$model5=new CTimestampBehaviorTestActiveRecord;
		$model5->attachBehavior('timestampBehavior', array(
			'class'=>'zii.behaviors.CTimestampBehavior',
			'createAttribute'=>null,
			'updateAttribute'=>'updated_at',
			'setUpdateOnCreate'=>true,
		));
		$model5->title='testing-row-3';
		$model5->updated_at=123123123;
		$this->assertEquals(123123123, $model5->updated_at);
		$saveTime=time();
		$model5->save();
		$this->assertEquals($saveTime, $model5->updated_at, '', 2);
	}
}

/**
 * @property integer $id
 * @property string $title
 * @property integer $created_at
 * @property integer $updated_at
 */
class CTimestampBehaviorTestActiveRecord extends CActiveRecord
{
	/**
	 * @return CTimestampBehaviorTestActiveRecord
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'table';
	}
}
