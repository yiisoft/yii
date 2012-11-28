<?php
/**
 * CTypeValidatorTest
 */
class CTypeValidatorTest extends CTestCase
{
	public function testInteger()
	{
		$validator=new CTypeValidator();
		$validator->type='integer';
		$this->assertTrue($validator->validateValue(42));
		$this->assertTrue($validator->validateValue(+42));
		$this->assertTrue($validator->validateValue(-42));
		$this->assertTrue($validator->validateValue('42'));
		$this->assertTrue($validator->validateValue('+42'));
		$this->assertTrue($validator->validateValue('-42'));
		$this->assertFalse($validator->validateValue('42 is a good number!'));
		$this->assertFalse($validator->validateValue(3.1415926));
		$this->assertFalse($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}

	public function testIntegerStrict()
	{
		$validator=new CTypeValidator();
		$validator->type='integer';
		$validator->strict=true;
		$this->assertTrue($validator->validateValue(42));
		$this->assertTrue($validator->validateValue(+42));
		$this->assertTrue($validator->validateValue(-42));
		$this->assertFalse($validator->validateValue('42'));
		$this->assertFalse($validator->validateValue('+42'));
		$this->assertFalse($validator->validateValue('-42'));
		$this->assertFalse($validator->validateValue('42 is a good number!'));
		$this->assertFalse($validator->validateValue(3.1415926));
		$this->assertFalse($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}

	public function testFloat()
	{
		$validator=new CTypeValidator();
		$validator->type='float';
		$this->assertTrue($validator->validateValue(42));
		$this->assertTrue($validator->validateValue(42.0));
		$this->assertTrue($validator->validateValue(+42.1));
		$this->assertTrue($validator->validateValue(-42.2));
		$this->assertTrue($validator->validateValue('42'));
		$this->assertTrue($validator->validateValue('42.1'));
		$this->assertTrue($validator->validateValue('+42.2'));
		$this->assertTrue($validator->validateValue('-42.3'));
		$this->assertFalse($validator->validateValue('42.3 is a good number!'));
		$this->assertFalse($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}

	public function testFloatStrict()
	{
		$validator=new CTypeValidator();
		$validator->type='float';
		$validator->strict=true;
		$this->assertFalse($validator->validateValue(42));
		$this->assertTrue($validator->validateValue(42.0));
		$this->assertTrue($validator->validateValue(+42.1));
		$this->assertTrue($validator->validateValue(-42.2));
		$this->assertFalse($validator->validateValue('42'));
		$this->assertFalse($validator->validateValue('42.1'));
		$this->assertFalse($validator->validateValue('+42.2'));
		$this->assertFalse($validator->validateValue('-42.3'));
		$this->assertFalse($validator->validateValue('42.3 is a good number!'));
		$this->assertFalse($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}

	public function testString()
	{
		$validator=new CTypeValidator();
		$validator->type='string';
		$this->assertFalse($validator->validateValue(42));
		$this->assertFalse($validator->validateValue(42.0));
		$this->assertTrue($validator->validateValue('42'));
		$this->assertTrue($validator->validateValue('42.3 is a good number!'));
		$this->assertFalse($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}

	public function testArray()
	{
		$validator=new CTypeValidator();
		$validator->type='array';
		$this->assertFalse($validator->validateValue(42));
		$this->assertFalse($validator->validateValue(42.0));
		$this->assertFalse($validator->validateValue('42.3 is a good number!'));
		$this->assertTrue($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}

	// TODO: the following three should be tested for actual pattern matching

	public function testDate()
	{
		$validator=new CTypeValidator();
		$validator->type='date';
		$this->assertFalse($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(42));
		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}

	public function testTime()
	{
		$validator=new CTypeValidator();
		$validator->type='time';
		$this->assertFalse($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(42));
		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}

	public function testDateTime()
	{
		$validator=new CTypeValidator();
		$validator->type='datetime';
		$this->assertFalse($validator->validateValue(array(13)));

		$this->assertFalse($validator->validateValue(42));
		$this->assertFalse($validator->validateValue(true));
		$this->assertFalse($validator->validateValue(false));
		$this->assertFalse($validator->validateValue(new stdClass()));
	}
}