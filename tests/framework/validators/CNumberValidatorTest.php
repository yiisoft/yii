<?php
require_once('ValidatorTestModel.php');

class CNumberValidatorTest extends CTestCase
{
	public function providerIssue1669()
	{
		return array(
			// boolean
			array(false, array('number' => array('Number must be a number.'))),
			array(true, array('number' => array('Number must be a number.'))),
			// integer
			array(20, array('number' => array('Number is too big (maximum is 15).'))),
			array(1, array('number' => array('Number is too small (minimum is 5).'))),
			// float
			array(20.5, array('number' => array('Number must be an integer.','Number is too big (maximum is 15).'))),
			array(1.5, array('number' => array('Number must be an integer.','Number is too small (minimum is 5).'))),
			// string
			array('20', array('number' => array('Number is too big (maximum is 15).'))),
			array('20.5', array('number' => array('Number must be an integer.','Number is too big (maximum is 15).'))),
			array('1', array('number' => array('Number is too small (minimum is 5).'))),
			array('1.5', array('number' => array('Number must be an integer.','Number is too small (minimum is 5).'))),
			array('abc', array('number' => array('Number must be a number.'))),
			array('a100', array('number' => array('Number must be a number.'))),
			// array
			array(array(1,2), array('number' => array('Number must be a number.'))),
			// object
			array((object)array('a'=>1,'b'=>2), array('number' => array('Number must be a number.'))),
		);
	}

	/**
	 * https://github.com/yiisoft/yii/issues/1669
	 * @dataProvider providerIssue1669
	 */
	public function testIssue1669($value, $assertion)
	{
		$model = new ValidatorTestModel('CNumberValidatorTest');
		$model->number = $value;
		$model->validate(array('number'));
		$this->assertSame($assertion, $model->getErrors());
	}
}
