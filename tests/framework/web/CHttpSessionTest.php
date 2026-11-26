<?php

Yii::import('system.web.CHttpSession');

/**
 * Simple test subclass that forces useCustomStorage = true via getter.
 */
class CustomStorageSession extends CHttpSession
{
	private $_data=array();

	public function getUseCustomStorage()
	{
		return true;
	}

	public function readSession($id)
	{
		return isset($this->_data[$id]) ? $this->_data[$id] : '';
	}

	public function validateSession($id)
	{
		return isset($this->_data[$id]);
	}

	public function writeSession($id,$data)
	{
		$this->_data[$id]=$data;
		return true;
	}
}

class CHttpSessionTest extends CTestCase {
	protected function checkProb($gcProb) {
		Yii::app()->session->gCProbability = $gcProb;
		$value = Yii::app()->session->gCProbability;
		$this->assertInternalType('float', $value);
		$this->assertLessThanOrEqual(1, $value);
		$this->assertGreaterThanOrEqual(0, $value);
		$this->assertLessThanOrEqual(1 / 21474836.47, abs($gcProb - $value));
	}

	/**
	 * @covers CHttpSession::getGCProbability
	 * @covers CHttpSession::setGCProbability
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testSetGet() {
		Yii::app()->setComponents(array('session' => array(
			'class' => 'CHttpSession',
			'cookieMode' => 'none',
			'savePath' => sys_get_temp_dir(),
			'sessionName' => 'CHttpSessionTest',
			'timeout' => 5,
		)));
		/** @var $sm CHttpSession */
		$this->checkProb(1);

		$this->checkProb(0);

		$gcProb = 1.0;
		while ($gcProb > 1 / 2147483647) {
			$this->checkProb($gcProb);
			$gcProb = $gcProb / 9;
		}
	}

	/**
	 * On PHP 8.4+, using custom storage should not trigger a
	 * session_set_save_handler() deprecation anymore.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testCustomStorageDoesNotTriggerSessionSetSaveHandlerDeprecationOnPhp84()
	{
		if(version_compare(PHP_VERSION, '8.4', '<'))
		{
			$this->markTestSkipped('session_set_save_handler() deprecation is PHP 8.4+ only.');
		}

		$deprecationTriggered=false;
		$session=null;

		set_error_handler(function ($errno, $errstr) use (&$deprecationTriggered)
		{
			if($errno === E_DEPRECATED && strpos($errstr, 'session_set_save_handler') !== false)
			{
				$deprecationTriggered=true;
			}
			return false;
		}, E_DEPRECATED);

		try
		{
			$session=new CustomStorageSession();
			$session->setCookieMode('none');
			$session->setSavePath(sys_get_temp_dir());
			$session->setSessionName('CHttpSessionPhp84Test');
			$session->setTimeout(5);

			$session->open();

			$this->assertNotSame('', session_id());
			$this->assertFalse($deprecationTriggered, 'session_set_save_handler() deprecation was triggered');
		} catch(Exception $e)
		{
			if($session !== null && session_id() !== '')
			{
				$session->close();
			}
			restore_error_handler();
			throw $e;
		}

		if($session !== null && session_id() !== '')
		{
			$session->close();
		}
		restore_error_handler();
	}

	public function testCustomStorageHandlerCreatesConfiguredSessionId()
	{
		if(version_compare(PHP_VERSION, '7.1', '<'))
		{
			$this->markTestSkipped('Custom session ID generation is used on PHP 7.1+ only.');
		}

		Yii::import('system.web.CHttpSessionIdHandler');

		$handler=new CHttpSessionIdHandler(new CustomStorageSession());
		$sessionId=$handler->create_sid();
		$length=(int)ini_get('session.sid_length');
		$bitsPerCharacter=(int)ini_get('session.sid_bits_per_character');
		if($length<1)
			$length=32;
		if($bitsPerCharacter<4 || $bitsPerCharacter>6)
			$bitsPerCharacter=4;
		$alphabet=substr('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,-',0,1 << $bitsPerCharacter);

		$this->assertSame($length,strlen($sessionId));
		$this->assertSame(strlen($sessionId),strspn($sessionId,$alphabet));
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testCustomStorageHandlerSupportsSessionIdCreation()
	{
		if(version_compare(PHP_VERSION, '7.4', '<'))
		{
			$this->markTestSkipped('Custom session ID validation is not reliable before PHP 7.4.');
		}

		Yii::import('system.web.CHttpSessionIdHandler');

		$handler=new CHttpSessionIdHandler(new CustomStorageSession());
		session_set_save_handler($handler, true);

		$this->assertTrue(session_start());
		$currentSessionId=session_id();
		$sessionId=session_create_id();

		$this->assertInternalType('string', $sessionId);
		$this->assertNotSame('', $sessionId);
		$this->assertFalse($handler->validateId($sessionId));

		$_SESSION['stored']=true;
		session_write_close();
		$this->assertTrue($handler->validateId($currentSessionId));
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testCustomStoragePreservesLegacySessionIdFormatOnPhp70()
	{
		if(version_compare(PHP_VERSION, '7.0', '<') || version_compare(PHP_VERSION, '7.1', '>='))
			$this->markTestSkipped('Legacy session hash settings are used on PHP 7.0 only.');

		ini_set('session.use_cookies', '0');
		ini_set('session.cache_limiter', '');
		ini_set('session.hash_function', '0');
		ini_set('session.hash_bits_per_character', '5');

		$session=new CustomStorageSession();
		$session->open();
		$this->assertSame(PHP_SESSION_ACTIVE, session_status(), var_export(error_get_last(), true));
		$id=session_id();
		$session->close();

		$this->assertSame(26, strlen($id));
		$this->assertSame(26, strspn($id, '0123456789abcdefghijklmnopqrstuv'));
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testCustomStorageStrictModePreservesEmptySessionAndReplacesUnknownId()
	{
		if(version_compare(PHP_VERSION, '7.1', '<'))
			$this->markTestSkipped('Storage-aware session ID validation is used on PHP 7.1+ only.');

		ini_set('session.use_strict_mode', '1');
		ini_set('session.use_cookies', '0');
		ini_set('session.cache_limiter', '');
		$session=new CustomStorageSession();
		$session->writeSession('existing-empty-session', '');
		$session->open();
		$session->close();

		session_id('existing-empty-session');
		$this->assertTrue(session_start());
		$this->assertSame('existing-empty-session', session_id());
		$this->assertSame(array(), $_SESSION);
		session_write_close();

		session_id('unknown-session');
		$this->assertTrue(session_start());
		$this->assertNotSame('unknown-session', session_id());
		$this->assertNotSame('', session_id());
		session_write_close();
	}
}
