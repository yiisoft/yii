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

	/**
	 * https://github.com/yiisoft/yii/issues/2845
	 */
	public function testNonStrict()
	{
		$comparisons = array(
			array(1, true),
			array('1', true),
			array(' 1', true),
			array('1 ', true),
			array(2, false),
			array(12, false),
		);

		foreach ($comparisons as $comparison) {
			$model = new ValidatorTestModel('CRangeValidatorTest');
			$model->string3 = $comparison[0];
			$model->validate(array('string3'));

			if ($comparison[1]) {
				$this->assertArrayNotHasKey('string3', $model->getErrors(), var_export($comparison[0], true) . ' should be valid but it is not.');
			} else {
				$this->assertArrayHasKey('string3', $model->getErrors(), var_export($comparison[0], true) . ' should not be valid but it is.');
			}
		}
	}
}
