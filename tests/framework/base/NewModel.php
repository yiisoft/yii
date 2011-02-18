<?php

class NewModel extends CModel
{
	public $attr1;
	public $attr2;
	public $attr3;
	public $attr4;

	public function rules()
	{
		return array(
			array('attr2,attr1','numerical','max'=>5),
			array('attr1','required'),
			array('attr3', 'unsafe'),
		);
	}

	public function attributeNames()
	{
		return array('attr1','attr2');
	}
}