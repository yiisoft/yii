<?php

require_once 'ModelMock.php';

/**
 * Class CCaptchaValidatorTest.
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 */
class CCaptchaValidatorTest extends CTestCase
{

	public function testInvalidInput() {
		Yii::app()->setController(new CCaptchaValidatorTestController('CCaptchaValidatorTest'));

		$rules = array(
			array('foo', 'captcha', 'allowEmpty' => false),
			array('bar', 'captcha', 'allowEmpty' => true),
		);

		$stub = $this->getMock('ModelMock', array('rules'));
		$stub->expects($this->any())
			->method('rules')
			->will($this->returnValue($rules));

		$stub->foo = null;
		$stub->bar = null;
		$this->assertFalse($stub->validate());

		$stub->foo = array();
		$stub->bar = array();
		$this->assertFalse($stub->validate());
	}
}

/**
 * Class CCaptchaValidatorTestController.
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 */
class CCaptchaValidatorTestController extends CController
{
	public function actions()
	{
		return array(
			'captcha' => 'CCaptchaAction',
		);
	}
}
