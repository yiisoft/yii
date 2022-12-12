<?php

class CMssqlCommandBuilderTest extends CTestCase
{
	/**
	 * @var CDbConnection
	 */
	private $db;

	/**
	 * Mock a CDbConection, with a CMssqlSchema and CMssqlTableSchema
	 */
	public function setUp()
	{
		/*
		 * Disable the constructor and mock open and getAttribute so that CDbConnection does not
		 * try to make a connection
		 */
		$this->db = $this->getMockBuilder(CDbConnection::class)
			->disableOriginalConstructor()
			->setMethods(['open', 'getAttribute', 'getServerVersion', 'getSchema'])
			->getMock();

		$schema = $this->getMockBuilder(CMssqlSchema::class)
			->setConstructorArgs([$this->db])
			->setMethods(['getTable'])
			->getMock();

		$tableMetaData = new CMssqlTableSchema();
		$tableMetaData->schemaName = 'posts';
		$tableMetaData->rawName = '[dbo].[posts]';
		$tableMetaData->primaryKey = 'id';

		$schema->method('getTable')->willReturn($tableMetaData);

		$this->db->method('getSchema')->willReturn($schema);
	}

	public function testCommandBuilderOldMssql()
	{
		$this->db->method('getServerVersion')->willReturn('10');

		$command = $this->createFindCommand([
			'select'=>'id, title',
			'order'=>'title',
			'limit'=>2,
			'offset'=>3
		]);

		$this->assertEquals('SELECT * FROM (SELECT TOP 2 * FROM (SELECT TOP 5 id, title FROM [dbo].[posts] [t] ORDER BY title) as [__inner__] ORDER BY title DESC) as [__outer__] ORDER BY title ASC', $command->text);

		$command = $this->createFindCommand([
			'limit'=>2,
			'offset'=>3
		]);

		$this->assertEquals('SELECT * FROM (SELECT TOP 2 * FROM (SELECT TOP 5 * FROM [dbo].[posts] [t] ORDER BY id) as [__inner__] ORDER BY id DESC) as [__outer__] ORDER BY id ASC', $command->text);
	}

	public function testCommandBuilderNewMssql()
	{
		$this->db->method('getServerVersion')->willReturn('11');

		$command = $this->createFindCommand([
			'select'=>'id, title',
			'order'=>'title',
			'limit'=>2,
			'offset'=>3
		]);

		$this->assertEquals('SELECT id, title FROM [dbo].[posts] [t] ORDER BY title OFFSET 3 ROWS FETCH NEXT 2 ROWS ONLY', $command->text);

		$command = $this->createFindCommand([
			'limit'=>2,
			'offset'=>3
		]);

		$this->assertEquals('SELECT * FROM [dbo].[posts] [t] ORDER BY id OFFSET 3 ROWS FETCH NEXT 2 ROWS ONLY', $command->text);
	}

	/**
	 * @param $criteria array
	 * @return CDbCommand
	 * @throws CDbException
	 */
	private function createFindCommand($criteria)
	{
		$schema = $this->db->getSchema();
		$table = $schema->getTable('posts');
		return $schema->commandBuilder->createFindCommand($table, new CDbCriteria($criteria));
	}
}
