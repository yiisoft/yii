<?php

Yii::import('system.db.CDbConnection');
Yii::import('system.db.schema.oci.COciSchema');

/**
 * Test case for system.db.schema.oci.CSqliteCommandBuilder
 * @see COciCommandBuilderTest
 */
class COciCommandBuilderTest extends CTestCase
{
	const DB_DSN_PREFIX='oci';
	const DB_HOST='127.0.0.1';
	const DB_PORT='1521';
	const DB_SERVICE='xe';
	const DB_USER='test';
	const DB_PASS='test';

	/**
	 * @var CDbConnection
	 */
	private $db;

	public function setUp()
	{
		if((!extension_loaded('oci8') && !extension_loaded('oci8_11g')) || !extension_loaded('pdo') || !extension_loaded('pdo_oci'))
			$this->markTestSkipped('PDO and OCI extensions are required.');

		$dsn=self::DB_DSN_PREFIX.':dbname='.self::DB_HOST.':'.self::DB_PORT.'/'.self::DB_SERVICE.';charset=UTF8';
		$schemaFilePath=realpath(dirname(__FILE__).'/../data/oci.sql');

		$this->db=new CDbConnection($dsn, self::DB_USER, self::DB_PASS);
		$this->db->charset='UTF8';

		try
		{
			$this->db->active=true;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			$this->markTestSkipped("Please read {$schemaFilePath} for details on setting up the test environment for OCI test case.");
		}

		$tables=array('comments', 'post_category', 'posts', 'categories', 'profiles', 'users', 'items', 'orders', 'types');

		// delete existing sequences
		foreach($tables as $table)
		{
			if($table==='post_category' || $table==='orders' || $table==='types')
				continue;
			$sequence=$table.'_id_sequence';
			$sql=<<<EOD
DECLARE c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_sequences WHERE sequence_name = '{$sequence}';
	IF c = 1 THEN EXECUTE IMMEDIATE 'DROP SEQUENCE "{$sequence}"'; END IF;
END;
EOD;
			$this->db->createCommand($sql)->execute();
		}

		// delete existing tables
		foreach($tables as $table)
		{
			$sql=<<<EOD
DECLARE c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = '{$table}';
	IF c = 1 THEN EXECUTE IMMEDIATE 'DROP TABLE "{$table}"'; END IF;
END;
EOD;
			$this->db->createCommand($sql)->execute();
		}

		$sqls='';
		foreach(explode("\n", file_get_contents($schemaFilePath)) as $line)
		{
			if(substr($line, 0, 2)==='--')
				continue;
			$sqls.=$line."\n";
		}
		foreach(array_filter(explode("\n\n", $sqls)) as $sql)
		{
			if(trim($sql)!=='')
			{
				if(mb_substr($sql, -4)!=='END;') // do not remove semicolons after BEGIN END blocks
					$sql=rtrim($sql, ';');
				$this->db->createCommand($sql)->execute();
			}
		}
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
