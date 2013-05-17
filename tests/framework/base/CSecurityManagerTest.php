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
		$sm->hashAlgorithm=$mode;
		$this->assertEquals($mode,$sm->hashAlgorithm);
	}

	public function testValidateData()
	{
		$sm=new CSecurityManager;
		$sm->validationKey='123456';
		$sm->hashAlgorithm='SHA1';
		$data='this is raw data';
		$hashedData=$sm->hashData($data);
		$this->assertEquals($data,$sm->validateData($hashedData));
		$hashedData[3]='c'; // tamper the data
		$this->assertTrue($sm->validateData($hashedData)===false);

		$sm->hashAlgorithm='MD5';
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

	public function providerComputeHMAC()
	{
		return array(
			array(
				'7638cbf5b66f451a5dab87fa26f45025fa661f82',
				'data1',
				'123456',
				'sha1',
			),
			array(
				'7e9a30dd2e3c568499a0786ca776d29ee9fb00f6',
				'data2',
				'123456',
				'SHA1',
			),
			array(
				'289beb389d31d327eb87fd8f102970d1',
				'data1',
				'123456',
				'md5',
			),
			array(
				'4fb0dd0081ce2681f479d42ec8db5537',
				'data2',
				'123456',
				'MD5',
			),
		);
	}

	/**
	 * @dataProvider providerComputeHMAC
	 */
	public function testComputeHMAC($assertion,$data,$key,$hashAlgorithm)
	{
		$sm1=new CSecurityManager;
		$sm1->validationKey=$key;
		$sm1->hashAlgorithm=$hashAlgorithm;
		$this->assertEquals($assertion,$sm1->computeHMAC($data));

		$sm2=new CSecurityManager;
		$this->assertEquals($assertion,$sm2->computeHMAC($data,$key,$hashAlgorithm));
	}
}
