<?php

class CLoggerTest extends CTestCase
{
	public function testLog()
	{
		$logger=new CLogger;
		$logger->log('something','debug','application.test');
	}

	public function testGetLogs()
	{
		$logger=new CLogger();
		$logs=array(
			array('message1','debug','application.pages'),
			array('message2','info','application.config'),
			array('message3','info','application.pages'),
		);
		foreach($logs as $log)
			$logger->log($log[0],$log[1],$log[2]);

		$l=$logger->getLogs('debug');
		$this->assertSame(array_slice($l[0],0,3), $logs[0]);

		$l=$logger->getLogs('debug , Info');
		$this->assertSame(array_slice($l[0],0,3), $logs[0]);
		$this->assertSame(array_slice($l[1],0,3), $logs[1]);
		$this->assertSame(array_slice($l[2],0,3), $logs[2]);

		$l=$logger->getLogs('','application.config');
		$this->assertSame(array_slice($l[0],0,3), $logs[1]);

		$l=$logger->getLogs('','application.*');
		$this->assertSame(array_slice($l[0],0,3), $logs[0]);
		$this->assertSame(array_slice($l[1],0,3), $logs[1]);
		$this->assertSame(array_slice($l[2],0,3), $logs[2]);

		$l=$logger->getLogs('','application.config , Application.pages');
		$this->assertSame(array_slice($l[0],0,3), $logs[0]);
		$this->assertSame(array_slice($l[1],0,3), $logs[1]);
		$this->assertSame(array_slice($l[2],0,3), $logs[2]);

		$l=$logger->getLogs('info','application.config');
		$this->assertSame(array_slice($l[0],0,3), $logs[1]);

		$l=$logger->getLogs('info,debug','application.config');
		$this->assertSame(array_slice($l[0],0,3), $logs[1]);
	}
}
