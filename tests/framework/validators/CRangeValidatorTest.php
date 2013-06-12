<?php
require_once('ValidatorTestModel.php');

class CRangeValidatorTest extends CTestCase
{
	public function testEmpty()
	{
		$model = new ValidatorTestModel('CRangeValidatorTest');
		$model->string1 = '';
		$model->string2 = '';
		$model->validate(array('string1','string2'));
		$this->assertArrayHasKey('string1', $model->getErrors());
		$this->assertArrayNotHasKey('string2', $model->getErrors());
	}

	public function testZeroString()
	{
		$model = new ValidatorTestModel('CRangeValidatorTest');
		$model->string1 = '0';
		$model->string2 = '0';
		$model->validate(array('string1','string2'));
		$this->assertArrayNotHasKey('string1', $model->getErrors());
		$this->assertArrayHasKey('string2', $model->getErrors());
	}

	public function testZeroNumber()
	{
		$model = new ValidatorTestModel('CRangeValidatorTest');
		$model->string1 = 0;
		$model->string2 = 0;
		$model->validate(array('string1','string2'));
		$this->assertArrayNotHasKey('string1', $model->getErrors());
		$this->assertArrayHasKey('string2', $model->getErrors());
	}
}
