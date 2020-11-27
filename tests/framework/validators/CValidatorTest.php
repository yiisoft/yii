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
		$this->assertEquals(6, count($errors));
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
		$this->assertEquals(5, count($errors));
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
		$this->assertEquals(4, count($errors));
		$this->assertArrayHasKey('lastName', $errors);
		$this->assertArrayHasKey('patronymic', $errors);
		$this->assertArrayHasKey('nickName', $errors);
		$this->assertArrayHasKey('login', $errors);


		// scenario4
		// fields should be validated: login
		$scenario4TestModel=new ScenariosTestModel('scenario4');
		$scenario4TestModel->validate();

		$errors=$scenario4TestModel->getErrors();
		$this->assertEquals(1, count($errors));
		$this->assertArrayHasKey('login', $errors);
	}

	public function testMultipleScenarios()
	{
		// scenario2 + scenario3
		// fields should be validated: firstName, lastName, patronymic, login, birthday, nickName
		$scenarios = array('scenario2', 'scenario3');
		$scenario23TestModel=new ScenariosTestModel($scenarios);

		$this->assertEquals($scenarios[0], $scenario23TestModel->getScenario());
		$this->assertEquals($scenarios, $scenario23TestModel->getScenarios());

		$scenario23TestModel->validate();

		$errors=$scenario23TestModel->getErrors();
		$this->assertEquals(6, count($errors));
		$this->assertArrayHasKey('firstName', $errors);
		$this->assertArrayHasKey('lastName', $errors);
		$this->assertArrayHasKey('patronymic', $errors);
		$this->assertArrayHasKey('login', $errors);
		$this->assertArrayHasKey('birthday', $errors);
		$this->assertArrayHasKey('nickName', $errors);

		$scenario23TestModel->flushScenarios();

		$this->assertEquals('', $scenario23TestModel->getScenario());
		$this->assertEquals(array(), $scenario23TestModel->getScenarios());

		// scenario4
		// fields should be validated: login
		$scenario23TestModel->appendScenario('scenario4');
		$scenario23TestModel->validate();

		$errors=$scenario23TestModel->getErrors();
		$this->assertEquals(1, count($errors));
		$this->assertArrayHasKey('login', $errors);

		$scenario23TestModel->removeScenario('scenario1');

		$this->assertEquals('scenario4', $scenario23TestModel->getScenario());
		$this->assertEquals(array('scenario4'), $scenario23TestModel->getScenarios());

		$scenario23TestModel->removeScenario('scenario4');

		$this->assertEquals('', $scenario23TestModel->getScenario());
		$this->assertEquals(array(), $scenario23TestModel->getScenarios());
	}
}
