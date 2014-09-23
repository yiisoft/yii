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
		$sm->cryptAlgorithm='des';
		$key="\xA5\x94\x72\x26\x1F\xA3\x8A\x5B";
		$sm->setEncryptionKey($key);
		$this->assertEquals($key,$sm->getEncryptionKey());
	}

	/**
	 * @expectedException CException
	 */
	public function testUndersizedGlobalKey()
	{
		$sm=new CSecurityManager;
		$sm->cryptAlgorithm='des';
		$sm->setEncryptionKey('1');
	}

	/**
	 * @expectedException CException
	 */
	public function testUndersizedKey()
	{
		$sm=new CSecurityManager;
		$sm->cryptAlgorithm='des';
		$sm->encrypt('some data', '1');
	}

	/**
	 * @expectedException CException
	 */
	public function testOversizedGlobalKey()
	{
		$sm=new CSecurityManager;
		$sm->cryptAlgorithm='des';
		$sm->setEncryptionKey('123456789');
	}

	/**
	 * @expectedException CException
	 */
	public function testOversizedKey()
	{
		$sm=new CSecurityManager;
		$sm->cryptAlgorithm='des';
		$sm->encrypt('some data', '123456789');
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
		$sm->cryptAlgorithm='des';
		$sm->setEncryptionKey("\xAF\x84\x8F\xF2\xEE\x92\xDF\xA8");
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

	public function testGenerateRandomString()
	{
		$sm=new CSecurityManager;
		// loop to be sure always get the expected pattern.
		// student-t test that the distribution of chars is uniform would be nice.
		for ($i=1; $i<999; $i+=1){
			$ran=$sm->generateRandomString($i,false);
			$this->assertInternalType('string', $ran);
			$this->assertEquals(1, preg_match('{[a-zA-Z0-9_~]{' . $i . '}}', $ran));
		}
	}

	public function testGenerateRandomBytes()
	{
		$sm=new CSecurityManager;
		// any char is allowed so only string length is important
		$mbStrlen = function_exists('mb_strlen');
		for ($i=1; $i<255; $i+=1){
			$ran=$sm->generateRandomBytes($i,false);
			$this->assertInternalType('string', $ran);
			$this->assertEquals($i, $mbStrlen ? mb_strlen($ran, '8bit') : strlen($ran));
		}
	}

	/*
	 * Expected to fail on some systems!
	 */
	public function testGenerateRandomStringCS()
	{
		$sm=new CSecurityManager;
		// loop to be sure always get the expected pattern.
		// student-t test that the distribution of chars is uniform would be nice.
		for ($i=1; $i<999; $i+=1){
			$ran=$sm->generateRandomString($i,true);
			$this->assertInternalType('string', $ran);
			$this->assertEquals(1, preg_match('{[a-zA-Z0-9_~]{' . $i . '}}', $ran));
		}
	}

	/*
	 * Expected to fail on some systems!
	 */
	public function testGenerateRandomBytesCS()
	{
		$sm=new CSecurityManager;
		// any char is allowed so only string length is important
		$mbStrlen = function_exists('mb_strlen');
		for ($i=1; $i<255; $i+=1){
			$ran=$sm->generateRandomBytes($i,true);
			$this->assertInternalType('string', $ran);
			$this->assertEquals($i, $mbStrlen ? mb_strlen($ran, '8bit') : strlen($ran));
		}
	}

	public function dataProviderCompareStrings()
	{
		return array(
			array("",""),
			array(false,""),
			array(null,""),
			array(0,""),
			array(0.00,""),
			array("",null),
			array("",false),
			array("",0),
			array("","\0"),
			array("\0",""),
			array("\0","\0"),
			array("0","\0"),
			array(0,"\0"),
			array("user","User"),
			array("password","password"),
			array("password","passwordpassword"),
			array("password1","password"),
			array("password","password2"),
			array("","password"),
			array("password",""),
		);
	}

	/**
	 * @dataProvider dataProviderCompareStrings
	 *
	 * @param $expected
	 * @param $actual
	 */
	public function testCompareStrings($expected, $actual)
	{
		$sm=new CSecurityManager;
		$this->assertEquals(strcmp($expected,$actual)===0,$sm->compareString($expected,$actual));
	}
}
