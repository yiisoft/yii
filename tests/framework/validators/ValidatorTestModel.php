<?php
class ValidatorTestModel extends CFormModel
{
	public $string1;
	public $string2;
	public $string3;

	public $email;

	public $url;

	public $number;

	public function rules()
	{
		return array(
			array('string1', 'length', 'min'=>10, 'tooShort'=>'Too short message.', 'allowEmpty'=>false,
				'on'=>'CStringValidatorTest'),
			array('string2', 'length', 'max'=>10, 'tooLong'=>'Too long message.', 'allowEmpty'=>false,
				'on'=>'CStringValidatorTest'),
			array('string3', 'length', 'is'=>10, 'message'=>'Error message.', 'allowEmpty'=>false,
				'on'=>'CStringValidatorTest'),

			array('email', 'email', 'allowEmpty'=>false, 'on'=>'CEmailValidatorTest'),

			array('url', 'url', 'allowEmpty'=>false, 'on'=>'CUrlValidatorTest'),

			array('number', 'numerical', 'min'=>5, 'max'=>15, 'integerOnly'=>true, 'on'=>'CNumberValidatorTest'),
		);
	}
}
