<?php

Yii::import('system.db.schema.CDbColumnSchema');

/**
 * Test case for "system.db.schema.CDbColumnSchema"
 * @see CDbColumnSchema
 */
class CDbColumnSchemaTest extends CTestCase
{
	public function providerExtractType()
	{
		return array(
			array('int', 'integer'),
			array('integer', 'integer'),
			array('bool', 'boolean'),
			array('boolean', 'boolean'),
			array('real', 'double'),
			array('float', 'double'),
			array('double', 'double'),
			array('unsigned int', 'string'),
			array('varchar', 'string'),
			array('varchar(255)', 'string'),
			array('text', 'string'),
		);
	}

	/**
	 * @dataProvider providerExtractType
	 *
	 * @param string $dbType raw type
	 * @param string $expectedDbType expected exact type
	 */
	public function testExtractType($dbType, $expectedDbType)
	{
		$columnSchema = new CDbColumnSchema();

		$columnSchema->init($dbType, null);

		$this->assertEquals($expectedDbType,$columnSchema->type,"Wrong type extraction from '{$dbType}'");
	}

	public function providerExtractLimit()
	{
		return array(
			array('varchar(255)', 255, 255, null),
			array('float(3,2)', 3, 3, 2),
			array('text', null, null, null),
		);
	}

	/**
	 * @dataProvider providerExtractLimit
	 *
	 * @param string $dbType raw type
	 * @param int|null $expectedSize expected size
	 * @param int|null $expectedPrecision expected precision
	 * @param int|null $expectedScale expected scale
	 */
	public function testExtractLimit($dbType, $expectedSize, $expectedPrecision, $expectedScale)
	{
		$columnSchema = new CDbColumnSchema();

		$columnSchema->init($dbType, null);
		$this->assertEquals($expectedSize,$columnSchema->size,"Wrong extraction of size from '{$dbType}'");
		$this->assertEquals($expectedPrecision,$columnSchema->precision,"Wrong extraction of precision from '{$dbType}'");
		$this->assertEquals($expectedScale,$columnSchema->scale,"Wrong extraction of scake from '{$dbType}'");
	}

	public function providerTypecast() {
		return array(
			array('string', 'some string', 'some string'),
			array('string', 123, '123'),
			array('string', 5.7, '5.7'),
			array('boolean', true, true),
			array('boolean', false, false),
			array('boolean', 1, true),
			array('boolean', 0, false),
			array('boolean', '', false),
			array('boolean', 'not empty', true),
			array('integer', '123', 123),
			array('integer', ' 123 ', 123),
			array('integer', 'some string', 0),
			array('integer', 'abc123', 0),
			array('double', '123', '123'),
			array('double', '5.2', '5.2'),
			array('double', 5.2, 5.2),
			array('double', ' 5.2 ', '5.2'),
			/* @see https://github.com/yiisoft/yii/issues/2206 */
			array('integer', '123abc', 0),
			array('double', 'abc5.2', 0),
			array('double', '5.2abc', 0),
		);
	}

	/**
	 * @dataProvider providerTypecast
	 *
	 * @param string $type column type
	 * @param mixed $value test value
	 * @param mixed $expectedTypecastResult expected typecast result
	 */
	public function testTypecast($type, $value, $expectedTypecastResult)
	{
		$columnSchema = new CDbColumnSchema();
		$columnSchema->type = $type;

		$this->assertTrue($expectedTypecastResult===$columnSchema->typecast($value),'Wrong typecast for "'.CVarDumper::dumpAsString($value).'"');
	}

	public function providerExtractDefault()
	{
		return array(
			array('integer', '123'),
			array('string', 'some string'),
		);
	}

	/**
	 * @depends testTypecast
	 * @dataProvider providerExtractDefault
	 *
	 * @param string $dbType raw type
	 * @param mixed $defaultValue raw default value
	 */
	public function testExtractDefault($dbType, $defaultValue)
	{
		$columnSchema = new CDbColumnSchema();

		$columnSchema->init($dbType, $defaultValue);

		$this->assertEquals($columnSchema->typecast($defaultValue), $columnSchema->defaultValue);
	}
}
