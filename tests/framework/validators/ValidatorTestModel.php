<?php
class ValidatorTestModel extends CFormModel
{
	public $email;
	public $url;
	public $username;
	public $address;

	public function rules()
	{
		return array(
			array('email', 'email', 'allowEmpty' => false, 'on' => 'CEmailValidatorTest'),
			array('url', 'url', 'allowEmpty' => false, 'on' => 'CUrlValidatorTest'),
			array('username', 'required', 'trim' => false, 'on' => 'CRequiredValidatorTest'),
			array('address', 'required', 'on' => 'CRequiredValidatorTest'),
		);
	}
}
