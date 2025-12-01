<?php
class CPasswordHelperTest extends CTestCase
{
	public function testHashAndVerifyPassword()
	{
		$password = 'test123';
		$hash = CPasswordHelper::hashPassword($password);
		$this->assertTrue(CPasswordHelper::verifyPassword($password, $hash));
	}
}
