<?php

class ScenariosTestModel extends CFormModel
{
	public $title;
	public $firstName;
	public $lastName;
	public $patronymic;
	public $nickName;

	public $login;
	public $password;

	public $birthday;

	public function rules()
	{
		return array(
			// scenario1
			array('title', 'required', 'on'=>'scenario1'),

			// scenario1 and scenario2
			array('firstName', 'required', 'off'=>'scenario3, scenario4'),

			// scenario1, scenario2 and scenario3
			array('lastName', 'required', 'on'=>array('scenario1', 'scenario2', 'scenario3')),

			// scenario1, scenario2 and scenario3
			array('patronymic', 'required', 'off'=>array('scenario4')),

			// scenario1 and scenario3
			array('nickName', 'required', 'on'=>array('scenario1', 'scenario2', 'scenario3'), 'off'=>'scenario2'),

			// scenario1, scenario2, scenario3 and scenario4
			array('login', 'required'),

			// useless rule
			array('password', 'required', 'on'=>'scenario1,scenario2,scenario3,scenario4',
				'off'=>array('scenario1', 'scenario2', 'scenario3', 'scenario4')),

			// scenario2
			array('birthday', 'required', 'on'=>'scenario2', 'off'=>'scenario3'),
		);
	}
}
