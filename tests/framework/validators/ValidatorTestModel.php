<?php
class ValidatorTestModel extends CFormModel
{
	public $email;

	public function rules()
	{
		return array(
			array('email', 'email', 'allowEmpty' => false),
		);
	}
}
