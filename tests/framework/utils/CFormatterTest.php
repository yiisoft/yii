<?php

class CFormatterTest extends CTestCase
{
	public $formatter;

	public function setUp()
	{
		$this->formatter =new CFormatter();
	}

	public function testDate()
	{	
		$this->assertEquals(
				'2009/01/03',
				$this->formatter->format(1230952187,'date')
		);
		$this->assertEquals(
				'2009/01/03',
				$this->formatter->format(1230952187,array('date'))
		);		
		$this->assertEquals(
				'03.01.2009',
				$this->formatter->format(1230952187,array('date', 'dateFormat' => 'd.m.Y'))
		);
	}

	public function testTime()
	{
		$this->assertEquals(
				'04:09:47 AM',
				$this->formatter->format(1230952187,'time')
		);
		$this->assertEquals(
				'04:09:47 AM',
				$this->formatter->format(1230952187,array('time'))
		);
		$this->assertEquals(
				'04.09',
				$this->formatter->format(1230952187,array('time', 'timeFormat' => 'h.i'))
		);
	}
	
	public function testDateTime()
	{	
		$this->assertEquals(
				'2009/01/03 04:09:47 AM',
				$this->formatter->format(1230952187,'datetime')
		);
		$this->assertEquals(
				'2009/01/03 04:09:47 AM',
				$this->formatter->format(1230952187,array('datetime'))
		);	
		$this->assertEquals(
				'03.01.2009 04:09:47',
				$this->formatter->format(1230952187,array('datetime', 'datetimeFormat' => 'd.m.Y h:i:s'))
		);
	}
	
	public function testNumber()
	{	
		$this->assertEquals(
				'123,456,789',
				$this->formatter->format(123456789.1234,'number')
		);
		$this->assertEquals(
				'123,456,789',
				$this->formatter->format(123456789.1234,array('number'))
		);
		$this->assertEquals(
				'123.456.789',
				$this->formatter->format(123456789.1234,array('number', 'thousandSeparator'=>'.'))
		);		
		$this->assertEquals(
				'123,12',
				$this->formatter->format(123.1234,array('number', 'decimals'=>2, 'decimalSeparator'=>','))
		);
		$this->assertEquals(
				'123.456.789,12',
				$this->formatter->format(123456789.1234,array('number', 'decimals'=>2, 'decimalSeparator'=>',', 'thousandSeparator'=>'.'))
		);
	}
}
