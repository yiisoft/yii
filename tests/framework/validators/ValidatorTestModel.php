<?php
class ValidatorTestModel extends CFormModel
{
	public $email;
	public $url;
	public $number1;
	public $number2;

	public function rules()
	{
		return array(
			array('email', 'email', 'allowEmpty' => false, 'on' => 'CEmailValidatorTest'),
			array('url', 'url', 'allowEmpty' => false, 'on' => 'CUrlValidatorTest'),
			array('number1', 'numerical', 'min' => 5, 'max' => 15, 'on' => 'CNumberValidatorTest'),
			array('number2', 'numerical', 'min' => 5, 'max' => 15, 'integerOnly' => true, 'on' => 'CNumberValidatorTest'),
		);
	}
}
