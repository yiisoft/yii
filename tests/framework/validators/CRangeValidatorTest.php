<?php
require_once('ValidatorTestModel.php');

class CRangeValidatorTest extends CTestCase
{
	public function testEmpty()
	{
		$model = new ValidatorTestModel('CRangeValidatorTest');
		$model->number = '';
		$model->validate(array('number'));
		$this->assertArrayHasKey('number', $model->getErrors());
	}

	public function testZeroString()
	{
		$model = new ValidatorTestModel('CRangeValidatorTest');
		$model->number = '0';
		$model->validate(array('number'));
		$this->assertArrayNotHasKey('number', $model->getErrors());
	}

	public function testZeroNumber()
	{
		$model = new ValidatorTestModel('CRangeValidatorTest');
		$model->number = 0;
		$model->validate(array('number'));
		$this->assertArrayNotHasKey('number', $model->getErrors());
	}
}
