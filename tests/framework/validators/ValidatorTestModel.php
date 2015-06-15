<?php
class ValidatorTestModel extends CFormModel
{
	public $string1;
	public $string2;
	public $string3;

	public $email;

	public $url;

	public $number;

	public $username;
	public $address;

	public $uploaded_file;

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

			array('username', 'required', 'trim' => false, 'on' => 'CRequiredValidatorTest'),
			array('address', 'required', 'on' => 'CRequiredValidatorTest'),

			array('string1', 'in', 'allowEmpty' => false, 'range' => array(0,1,7,13), 'on' => 'CRangeValidatorTest'),
			array('string2', 'in', 'allowEmpty' => false, 'range' => array('',1,7,13), 'on' => 'CRangeValidatorTest'),
			array('string3', 'in', 'allowEmpty' => false, 'range' => array(1), 'on' => 'CRangeValidatorTest', 'strict' => false),

			array('uploaded_file', 'file', 'on' => 'CFileValidatorTest'),
		);
	}
}
