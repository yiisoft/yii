<?php

Yii::import('system.web.CHttpSession');

/**
 * Simple test subclass that forces useCustomStorage = true via getter.
 */
class CustomStorageSession extends CHttpSession
{
	public function getUseCustomStorage()
	{
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
}
