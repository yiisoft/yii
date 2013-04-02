<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.schema.sqlite.CSqliteSchema');

/**
 * Test case for system.db.schema.sqlite.CSqliteCommandBuilder
 * @see CSqliteCommandBuilder
 */
class CSqliteCommandBuilderTest extends CTestCase
{
	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->db=new CDbConnection('sqlite::memory:');
		$this->db->active=true;
		$this->db->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/../data/sqlite.sql'));
	}

	public function tearDown()
	{
		$this->db->active=false;
	}

	public function testMultipleInsert()
	{
		$builder=$this->db->getSchema()->getCommandBuilder();
		$tableName='types';
		$data=array(
			array(
				'int_col'=>1,
				'char_col'=>'char_col_1',
				'char_col2'=>'char_col_2_1',
				'float_col'=>1.1,
				'bool_col'=>true,
			),
			array(
				'int_col'=>2,
				'char_col'=>'char_col_2',
				'float_col'=>2.2,
				'bool_col'=>false,
			),
		);
		$command=$builder->createMultipleInsertCommand($tableName,$data);
		$command->execute();

		$rows=$builder->dbConnection->createCommand('SELECT * FROM '.$builder->dbConnection->quoteTableName($tableName))->queryAll();

		$this->assertEquals(count($data),count($rows),'Records count miss matches!');
		foreach($rows as $rowIndex=>$row)
			foreach($row as $columnName=>$value)
			{
				$columnIndex=array_search($columnName,$data[$rowIndex],true);
				if($columnIndex==false)
					continue;
				$expectedValue=$data[$rowIndex][$columnIndex];
				$this->assertTrue($expectedValue==$value,"Value for column '{$columnName}' incorrect!");
			}
	}
}
