<?php
 
class CUniqueValidatorTest extends CTestCase
{
	/**
	 * @var CDbConnection test database connection.
	 */
	private $_connection;
	/**
	 * @var string test table name.
	 */
	private $_tableName = 'test_table';
	/**
	 * @var string test {@link CActiveRecord} model.
	 */
	private $_arModelName = 'TestUniqueModel';

	protected function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->_connection=new CDbConnection('sqlite::memory:');
		$this->_connection->active=true;
		$columns = array(
			'id'=>'pk',
			'name'=>'string',
		);
		$this->_connection->createCommand()->createTable($this->_tableName, $columns);

		CActiveRecord::$db=$this->_connection;
		$this->declareArModelClass();
	}

	protected function tearDown()
	{
		$this->_connection->createCommand()->dropTable($this->_tableName);
		$this->_connection->active=false;
	}

	/**
	 * Declares test active record class.
	 * @return boolean success.
	 */
	protected function declareArModelClass()
	{
		if (!class_exists($this->_arModelName,false))
		{
			$classDefinitionCode=<<<EOD
class {$this->_arModelName} extends CActiveRecord
{
	public static function model(\$className=__CLASS__)
	{
		return parent::model(\$className);
	}

	public function tableName()
	{
		return '{$this->_tableName}';
	}

	public function rules()
	{
		return array(
			array('name','unique','on'=>'simple'),
			array('name','unique','caseSensitive'=>true,'on'=>'case_sensitive'),
			array('name','unique','caseSensitive'=>false,'on'=>'not_case_sensitive'),
			array('name','unique','criteria'=>array('alias'=>'test_alias'),'on'=>'criteria'),
		);
	}
}
EOD;
			eval($classDefinitionCode);
		}
		return true;
	}

	public function testValidate()
	{
		$modelClassName = $this->_arModelName;
		$name = 'test_name';

		$model = new $modelClassName('simple');
		$model->name = $name;
		$this->assertTrue($model->validate(),'Unique value considered as invalid!');

		$model->save(false);
		$this->assertTrue($model->validate(),'Unique value keeper considered as invalid!');

		$anotherModel = new $modelClassName('simple');
		$anotherModel->name = $name;
		$this->assertFalse($anotherModel->validate(),'Duplicate entry of unique value considered as valid!');
	}

	/**
	 * @depends testValidate
	 */
	public function testValidateCaseSensitive()
	{
		$modelClassName = $this->_arModelName;
		$name = 'test_name';

		$initModel = new $modelClassName();
		$initModel->name = $name;
		$initModel->save(false);

		$caseSensitiveModel = new $modelClassName('case_sensitive');
		$caseSensitiveModel->name = $name;
		$this->assertFalse($caseSensitiveModel->validate(),'Validation breaks in case sensitive mode!');
		$caseSensitiveModel->name = strtoupper($name);
		$this->assertTrue($caseSensitiveModel->validate(),'Same value in other case considered as invalid!');

		$caseInsensitiveModel = new $modelClassName('not_case_sensitive');
		$caseInsensitiveModel->name = strtoupper($name);
		$this->assertFalse($caseInsensitiveModel->validate(),'Same value in other case considered as valid!');
	}

	/**
	 * @depends testValidate
	 */
	public function testValidateWithCriteria()
	{
		$modelClassName = $this->_arModelName;
		$name = 'test_name';

		$model = new $modelClassName('criteria');
		$model->name = $name;
		$this->assertTrue($model->validate(),'Unable to validate model with custom criteria!');
	}

	/**
	 * https://github.com/yiisoft/yii/issues/1955
	 */
	public function testArrayValue()
	{
		$modelClassName = $this->_arModelName;
		$model = new $modelClassName('simple');
		$model->name = array('test_name');
		$this->assertFalse($model->validate());
		$this->assertTrue($model->hasErrors('name'));
	}
}
