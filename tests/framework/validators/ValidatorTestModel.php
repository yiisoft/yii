<?php
class ValidatorTestModel extends CFormModel
{
	public $email;
	public $url;

	public function rules()
	{
		return array(
			array('email', 'email', 'allowEmpty' => false, 'on' => 'CEmailValidatorTest'),
			array('url', 'url', 'allowEmpty' => false, 'on' => 'CUrlValidatorTest'),
		);
	}
}
