<?php
require 'ValidatorTestModel.php';

class CEmailValidatorTest extends CTestCase
{
	public function testEmpty()
	{
		$model = new ValidatorTestModel();
		$model->validate(array('email'));
		$this->assertArrayHasKey('email', $model->getErrors());
	}

	public function testNumericEmail()
	{
		$emailValidator = new CEmailValidator();
		$result = $emailValidator->validateValue("5011@gmail.com");
		$this->assertTrue($result);
	}
}