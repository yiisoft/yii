<?php

class NewModel extends CModel
{
	public $attr1;
	public $attr2;
	public $attr3;
	public $attr4;
	public $departement_name;
	public $firstName;
	public $LastName;

	public function rules()
	{
		return array(
			array('attr2,attr1','numerical','max'=>5),
			array('attr1','required'),
			array('attr2','required','when'=>array($this,'whenTest')),
			array('attr3', 'unsafe'),
		);
	}

	public function attributeNames()
	{
		return array('attr1','attr2');
	}

	public function whenTest($model, $attribute)
	{
		return false;
	}
}

class InvalidModel extends CModel
{
	public $username;

	public function rules()
	{
		return array(
			array('username'),
		);
	}

	public function attributeNames()
	{
		return array('username');
	}
}
