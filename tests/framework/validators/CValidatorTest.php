<?php

require_once 'ModelMock.php';
require_once 'ScenariosTestModel.php';

class CValidatorTest extends CTestCase
{
	public function test()
	{

	}

	public function testScenarios()
	{
		// scenario1
		// fields should be validated: title, firstName, lastName, nickName, patronymic, nickName, login
		$scenario1TestModel=new ScenariosTestModel('scenario1');
		$scenario1TestModel->validate();

		$errors=$scenario1TestModel->getErrors();
		$this->assertCount(6, $errors);
		$this->assertArrayHasKey('title', $errors);
		$this->assertArrayHasKey('firstName', $errors);
		$this->assertArrayHasKey('lastName', $errors);
		$this->assertArrayHasKey('nickName', $errors);
		$this->assertArrayHasKey('patronymic', $errors);
		$this->assertArrayHasKey('login', $errors);


		// scenario2
		// fields should be validated: firstName, lastName, patronymic, login, birthday
		$scenario2TestModel=new ScenariosTestModel('scenario2');
		$scenario2TestModel->validate();

		$errors=$scenario2TestModel->getErrors();
		$this->assertCount(5, $errors);
		$this->assertArrayHasKey('firstName', $errors);
		$this->assertArrayHasKey('lastName', $errors);
		$this->assertArrayHasKey('patronymic', $errors);
		$this->assertArrayHasKey('login', $errors);
		$this->assertArrayHasKey('birthday', $errors);


		// scenario3
		// fields should be validated: lastName, patronymic, nickName, login
		$scenario3TestModel=new ScenariosTestModel('scenario3');
		$scenario3TestModel->validate();

		$errors=$scenario3TestModel->getErrors();
		$this->assertCount(4, $errors);
		$this->assertArrayHasKey('lastName', $errors);
		$this->assertArrayHasKey('patronymic', $errors);
		$this->assertArrayHasKey('nickName', $errors);
		$this->assertArrayHasKey('login', $errors);


		// scenario4
		// fields should be validated: login
		$scenario4TestModel=new ScenariosTestModel('scenario4');
		$scenario4TestModel->validate();

		$errors=$scenario4TestModel->getErrors();
		$this->assertCount(1, $errors);
		$this->assertArrayHasKey('login', $errors);
	}
}
