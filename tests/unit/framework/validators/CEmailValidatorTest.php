<?php
require 'ValidatorTestModel.php';

class CEmailValidatorTest extends CTestCase
{
	function testEmpty()
	{
		$model = new ValidatorTestModel();
		$model->validate(array('email'));
		$this->assertArrayHasKey('email', $model->getErrors());
	}
}