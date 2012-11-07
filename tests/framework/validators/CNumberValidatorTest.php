<?php
require_once('ValidatorTestModel.php');

class CNumberValidatorTest extends CTestCase
{
	public function providerIssue1669()
	{
		return array(
			array('number1', '4.1', array('number1' => array('Number1 is too small (minimum is 5).'))),
			array('number1', 5, array()),
			array('number2', '15', array()),
			array('number2', 16, array('number2' => array('Number2 is too big (maximum is 15).'))),

			array('number1', 'a4', array('number1' => array('Number1 must be a number.'))),
			array('number1', 'b5', array('number1' => array('Number1 must be a number.'))),
			array('number2', 'c15', array('number2' => array('Number2 must be an integer.'))),
			array('number2', 'd16', array('number2' => array('Number2 must be an integer.'))),
		);
	}

	/**
	 * https://github.com/yiisoft/yii/issues/1669
	 * @dataProvider providerIssue1669
	 */
	public function testIssue1669($attribute, $number, $assertion)
	{
		$model = new ValidatorTestModel('CNumberValidatorTest');
		$model->$attribute = $number;
		$model->validate(array($attribute));
		$this->assertEquals($assertion, $model->getErrors());
	}
}
