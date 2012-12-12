<?php

class UserTest extends CDbTestCase
{
	public $fixtures=array(
		'users'=>'User',
	);

	public function testValidatePassword()
	{
		$this->assertTrue($this->users(0)->validatePassword('demo'));
		$this->assertFalse($this->users(0)->validatePassword('wrong'));

	}

	public function testChangePassword()
	{
		$user=$this->users(0);
		$user->password=$user->hashPassword('newpwd');
		$this->assertFalse($user->validatePassword('demo'));
		$this->assertTrue($user->validatePassword('newpwd'));

	}
}
