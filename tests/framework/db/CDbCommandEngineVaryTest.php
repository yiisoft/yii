<?php

Yii::import('system.db.CDbConnection');

/**
 * Test case for "system.db.CDbCommand".
 * This test case checks functions and features, which implementation varies
 * from one database engine to another.
 *
 * @see CDbCommand
 */
class CDbCommandEngineVaryTest extends CTestCase
{
	/**
	 * @var array test db connections
	 */
	protected $dbConnections=array();

	public function setUp()
	{
		if(!extension_loaded('pdo'))
			$this->markTestSkipped('PDO extension is required.');
	}

	public function tearDown()
	{
		if(!empty($this->dbConnections))
			foreach($this->dbConnections as $dbConnection)
				if(is_object($dbConnection))
					$dbConnection->active=false;
	}

	/**
	 * @return array list of test db connections.
	 */
	protected function getTestDbConnections()
	{
		if(empty($this->dbConnections))
			$this->dbConnections=$this->generateTestDbConnections();
		return $this->dbConnections;
	}

	/**
	 * Generates list of test db connections in format driverName => CDbConnection instance
	 * If connection for specific driver can not be established its key will contain "false".
	 * @return array list of test db connections.
	 */
	protected function generateTestDbConnections()
	{
		$dbDrivers=array(
			'sqlite',
			'mysql',
		);
		$dbConnections=array();
		foreach($dbDrivers as $dbDriver)
		{
			if(!extension_loaded('pdo_mysql'))
				$dbConnections[$dbDriver]=false;

			// Create Connection:
			switch($dbDriver)
			{
				case 'sqlite':
					$dbConnection=new CDbConnection('sqlite::memory:');
					break;
				case 'mysql':
				default:
					$dbConnection=new CDbConnection($dbDriver.':host=127.0.0.1;dbname=yii','test','test');
			}

			// Open Connection:
			try
			{
				$dbConnection->active=true;
			}
			catch(Exception $e)
			{
				$dbConnections[$dbDriver]=false;
				continue;
			}

			// Clear Tables:
			$tables=array('comments','post_category','posts','categories','profiles','users','items','orders','types');
			switch($dbDriver)
			{
				case 'sqlite':
					break;
				case 'mysql':
				default:
				{
					foreach($tables as $table)
						$dbConnection->createCommand('DROP TABLE IF EXISTS '.$dbConnection->quoteTableName($table).' CASCADE')->execute();
				}
			}

			// Fill Up Database:
			switch($dbDriver)
			{
				case 'sqlite':
				case 'mysql':
				default:
					$sqls=file_get_contents(dirname(__FILE__)."/data/{$dbDriver}.sql");
					foreach(explode(';',$sqls) as $sql)
					{
						if(trim($sql)!=='')
							$dbConnection->createCommand($sql)->execute();
					}
			}

			$dbConnections[$dbDriver]=$dbConnection;
		}
		return $dbConnections;
	}

	/**
	 * Data provider for test database connections.
	 * @return array list of data in format array($driverName,$dbConnection)
	 */
	public function dataProviderDbConnections()
	{
		$data=array();
		$dbConnections=$this->getTestDbConnections();
		foreach($dbConnections as $driverName=>$dbConnection)
			$data[]=array($driverName,$dbConnection);
		return $data;
	}

	// Tests:

	/**
	 * @dataProvider dataProviderDbConnections
	 *
	 * @param string $driverName
	 * @param CDbConnection|false $dbConnection
	 */
	public function testInsertMultiple($driverName,$dbConnection)
	{
		if (!is_object($dbConnection))
			$this->markTestSkipped("Failed to connect '{$driverName}' test database.'");

		$multipleInsertCommand=$dbConnection->createCommand();

		$tableName='types';
		$columns=array(
			'int_col',
			'char_col',
			'float_col',
			'bool_col',
		);
		$values=array(
			array(
				1,
				'char_col_val_1',
				1.1,
				true,
			),
			array(
				2,
				'char_col_val_2',
				2.2,
				false,
			),
		);
		$multipleInsertCommand->insertMultiple($tableName,$columns,$values);

		$rows=$dbConnection->createCommand('SELECT * FROM '.$dbConnection->quoteTableName($tableName))->queryAll();

		$this->assertEquals(count($values),count($rows),'Records count miss matches!');
		foreach($rows as $rowIndex=>$row)
			foreach($row as $columnName=>$value)
			{
				$columnIndex=array_search($columnName,$columns,true);
				if($columnIndex==false)
					continue;
				$expectedValue=$values[$rowIndex][$columnIndex];
				$this->assertTrue($expectedValue==$value,"Value for column '{$columnName}' incorrect!");
			}
	}
}
