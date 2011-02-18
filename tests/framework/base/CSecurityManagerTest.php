<?php

class CSecurityManagerTest extends CTestCase
{
	public function setUp()
	{
		// clean up runtime directory
		$app=new TestApplication;
		$app->reset();
	}

	public function testValidationKey()
	{
		$sm=new CSecurityManager;
		$key='123456';
		$sm->validationKey=$key;
		$this->assertEquals($key,$sm->validationKey);

		$app=new TestApplication;
		$key=$app->securityManager->validationKey;
		$app->saveGlobalState();
		$app2=new TestApplication;
		$this->assertEquals($app2->securityManager->validationKey,$key);
	}

	public function testEncryptionKey()
	{
		$sm=new CSecurityManager;
		$key='123456';
		$sm->encryptionKey=$key;
		$this->assertEquals($key,$sm->encryptionKey);

		$app=new TestApplication;
		$key=$app->securityManager->encryptionKey;
		$app->saveGlobalState();
		$app2=new TestApplication;
		$this->assertEquals($app2->securityManager->encryptionKey,$key);
	}

	public function testValidation()
	{
		$sm=new CSecurityManager;
		$mode='SHA1';
		$sm->validation=$mode;
		$this->assertEquals($mode,$sm->validation);
	}

	public function testValidateData()
	{
		$sm=new CSecurityManager;
		$sm->validationKey='123456';
		$sm->validation='SHA1';
		$data='this is raw data';
		$hashedData=$sm->hashData($data);
		$this->assertEquals($data,$sm->validateData($hashedData));
		$hashedData[3]='c'; // tamper the data
		$this->assertTrue($sm->validateData($hashedData)===false);

		$sm->validation='MD5';
		$data='this is raw data';
		$hashedData=$sm->hashData($data);
		$this->assertEquals($data,$sm->validateData($hashedData));
		$hashedData[3]='c'; // tamper the data
		$this->assertTrue($sm->validateData($hashedData)===false);
	}

	public function testEncryptData()
	{
		if(!extension_loaded('mcrypt'))
			$this->markTestSkipped('mcrypt extension is required to test encrypt feature.');
		$sm=new CSecurityManager;
		$sm->encryptionKey='123456';
		$data='this is raw data';
		$encryptedData=$sm->encrypt($data);
		$this->assertTrue($data!==$encryptedData);
		$data2=$sm->decrypt($encryptedData);
		$this->assertEquals($data,$data2);
	}
}
